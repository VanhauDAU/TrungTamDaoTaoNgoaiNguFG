<?php

namespace App\Services\Admin\HocVien;

use App\Contracts\Admin\HocVien\DangKyHocServiceInterface;
use App\Models\Auth\TaiKhoan;
use App\Models\Education\BuoiHoc;
use App\Models\Education\DangKyLopHoc;
use App\Models\Education\DangKyLopHocPhuPhi;
use App\Models\Education\LopHoc;
use App\Models\Education\LopHocChinhSachGia;
use App\Models\Education\LopHocPhuPhi;
use App\Models\Finance\HoaDon;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DangKyHocService implements DangKyHocServiceInterface
{
    public function getList(Request $request): array
    {
        $query = DangKyLopHoc::with([
            'taiKhoan.hoSoNguoiDung',
            'lopHoc.khoaHoc',
            'lopHoc.coSo',
            'hoaDons',
        ]);

        if ($search = trim((string) $request->get('q'))) {
            $query->where(function (Builder $builder) use ($search) {
                $builder->whereHas('taiKhoan', function (Builder $studentQuery) use ($search) {
                    $studentQuery->where('email', 'like', "%{$search}%")
                        ->orWhere('taiKhoan', 'like', "%{$search}%")
                        ->orWhereHas('hoSoNguoiDung', fn (Builder $profileQuery) => $profileQuery
                            ->where('hoTen', 'like', "%{$search}%")
                            ->orWhere('soDienThoai', 'like', "%{$search}%"));
                })->orWhereHas('lopHoc', function (Builder $classQuery) use ($search) {
                    $classQuery->where('tenLopHoc', 'like', "%{$search}%")
                        ->orWhereHas('khoaHoc', fn (Builder $courseQuery) => $courseQuery->where('tenKhoaHoc', 'like', "%{$search}%"));
                });
            });
        }

        if ($request->filled('trangThai') && $request->get('trangThai') !== '') {
            $query->where('trangThai', (int) $request->get('trangThai'));
        }

        if ($request->filled('lopHocId')) {
            $query->where('lopHocId', (int) $request->get('lopHocId'));
        }

        $registrations = $query
            ->orderByDesc('ngayDangKy')
            ->orderByDesc('dangKyLopHocId')
            ->paginate(15)
            ->withQueryString();

        $allItems = (clone $query)->get();

        return [
            'registrations' => $registrations,
            'statusOptions' => DangKyLopHoc::trangThaiOptions(),
            'lopHocs' => $this->availableClassesQuery()->get(),
            'paymentMethods' => HoaDon::paymentMethodLabels(),
            'summary' => [
                'total' => $allItems->count(),
                'pending' => $allItems->where('trangThai', DangKyLopHoc::TRANG_THAI_CHO_THANH_TOAN)->count(),
                'active' => $allItems->filter(fn (DangKyLopHoc $registration) => $registration->blocksSeat())->count(),
                'holdsExpired' => $allItems->filter(fn (DangKyLopHoc $registration) => $registration->isHoldExpired())->count(),
            ],
        ];
    }

    public function getCreateFormData(): array
    {
        return [
            'students' => $this->availableStudentsQuery()->get(),
            'classes' => $this->availableClassesQuery()->get(),
            'paymentMethods' => HoaDon::paymentMethodLabels(),
        ];
    }

    public function store(Request $request): DangKyLopHoc
    {
        $data = $request->validate([
            'taiKhoanId' => 'required|integer|exists:taikhoan,taiKhoanId',
            'lopHocId' => 'required|integer|exists:lophoc,lopHocId',
            'payment_method' => 'required|in:1,2,3',
        ], [
            'taiKhoanId.required' => 'Vui lòng chọn học viên.',
            'lopHocId.required' => 'Vui lòng chọn lớp học.',
            'payment_method.required' => 'Vui lòng chọn hình thức thanh toán.',
        ]);

        $student = $this->resolveStudent((int) $data['taiKhoanId']);
        $staffId = Auth::id();

        try {
            return DB::transaction(function () use ($student, $data, $staffId) {
                $class = LopHoc::whereKey((int) $data['lopHocId'])->lockForUpdate()->firstOrFail();
                $class->load(['chinhSachGia.dotThus', 'phuPhis', 'khoaHoc', 'dangKyLopHocs', 'buoiHocs.caHoc', 'coSo']);

                $validation = $this->validateRegistration($student, $class);
                if ($validation !== true) {
                    throw ValidationException::withMessages(['lopHocId' => $validation]);
                }

                return $this->createRegistrationRecord($student, $class, (int) $data['payment_method'], $staffId, 'Đăng ký tại quầy');
            }, 3);
        } catch (QueryException $exception) {
            $this->throwIfDuplicateRegistration($exception);
            throw $exception;
        }
    }

    public function confirm(int $id): DangKyLopHoc
    {
        return DB::transaction(function () use ($id) {
            $registration = $this->findRegistrationForUpdate($id);

            if ($registration->isCancelled() || $registration->isCompleted() || $registration->isOnLeave()) {
                throw ValidationException::withMessages([
                    'registration' => 'Chỉ có thể xác nhận đăng ký đang chờ thanh toán hoặc tạm dừng nợ học phí.',
                ]);
            }

            if ($registration->lopHoc && ($registration->lopHoc->isCancelled() || $registration->lopHoc->isCompleted())) {
                throw ValidationException::withMessages([
                    'registration' => 'Không thể xác nhận vì lớp học không còn ở trạng thái vận hành.',
                ]);
            }

            $registration->recalculatePaymentStatus();
            $registration->refresh();

            if (!in_array((int) $registration->trangThai, [
                DangKyLopHoc::TRANG_THAI_DA_XAC_NHAN,
                DangKyLopHoc::TRANG_THAI_DANG_HOC,
            ], true)) {
                throw ValidationException::withMessages([
                    'registration' => 'Chỉ có thể xác nhận khi học viên đã thanh toán đủ học phí chính.',
                ]);
            }

            return $registration;
        });
    }

    public function cancel(int $id): DangKyLopHoc
    {
        return DB::transaction(function () use ($id) {
            $registration = $this->findRegistrationForUpdate($id);

            if ($registration->isCompleted()) {
                throw ValidationException::withMessages([
                    'registration' => 'Không thể hủy đăng ký đã hoàn thành.',
                ]);
            }

            if ($registration->isCancelled()) {
                return $registration;
            }

            if ((float) $registration->hoaDons->sum('daTra') > 0) {
                throw ValidationException::withMessages([
                    'registration' => 'Đăng ký đã phát sinh thu tiền. Hãy xử lý hoàn tiền/điều chỉnh trước khi hủy.',
                ]);
            }

            $registration->update([
                'trangThai' => DangKyLopHoc::TRANG_THAI_HUY,
                'ngayHetHanGiuCho' => null,
            ]);

            $this->appendSystemNoteToInvoices($registration, 'Đăng ký đã được hủy bởi quản trị viên.');

            return $registration->fresh(['taiKhoan.hoSoNguoiDung', 'lopHoc.khoaHoc', 'lopHoc.coSo', 'hoaDons']);
        });
    }

    public function hold(int $id): DangKyLopHoc
    {
        return DB::transaction(function () use ($id) {
            $registration = $this->findRegistrationForUpdate($id);

            if (!in_array((int) $registration->trangThai, [
                DangKyLopHoc::TRANG_THAI_DA_XAC_NHAN,
                DangKyLopHoc::TRANG_THAI_DANG_HOC,
                DangKyLopHoc::TRANG_THAI_TAM_DUNG_NO_HOC_PHI,
            ], true)) {
                throw ValidationException::withMessages([
                    'registration' => 'Chỉ có thể bảo lưu đăng ký đang học, đã xác nhận hoặc đang bị tạm dừng nợ học phí.',
                ]);
            }

            $registration->update([
                'trangThai' => DangKyLopHoc::TRANG_THAI_BAO_LUU,
                'ngayHetHanGiuCho' => null,
            ]);

            return $registration->fresh(['taiKhoan.hoSoNguoiDung', 'lopHoc.khoaHoc', 'lopHoc.coSo', 'hoaDons']);
        });
    }

    public function restore(int $id): DangKyLopHoc
    {
        return DB::transaction(function () use ($id) {
            $registration = $this->findRegistrationForUpdate($id);

            if (!in_array((int) $registration->trangThai, [
                DangKyLopHoc::TRANG_THAI_HUY,
                DangKyLopHoc::TRANG_THAI_BAO_LUU,
            ], true)) {
                throw ValidationException::withMessages([
                    'registration' => 'Chỉ có thể khôi phục đăng ký đã hủy hoặc đang bảo lưu.',
                ]);
            }

            if (!$registration->lopHoc || $registration->lopHoc->isCancelled() || $registration->lopHoc->isCompleted()) {
                throw ValidationException::withMessages([
                    'registration' => 'Không thể khôi phục vì lớp học không còn ở trạng thái vận hành.',
                ]);
            }

            $registration->lopHoc->loadMissing(['chinhSachGia.dotThus', 'dangKyLopHocs', 'buoiHocs.caHoc']);
            $validation = $this->validateRegistration($registration->taiKhoan, $registration->lopHoc, $registration->dangKyLopHocId);
            if ($validation !== true) {
                throw ValidationException::withMessages(['registration' => $validation]);
            }

            $registration->update([
                'trangThai' => DangKyLopHoc::TRANG_THAI_CHO_THANH_TOAN,
            ]);

            $this->syncHoldExpiryForRegistration($registration);
            $registration->recalculatePaymentStatus();

            return $registration->fresh(['taiKhoan.hoSoNguoiDung', 'lopHoc.khoaHoc', 'lopHoc.coSo', 'hoaDons']);
        });
    }

    public function transfer(Request $request, int $id): DangKyLopHoc
    {
        $data = $request->validate([
            'targetLopHocId' => 'required|integer|exists:lophoc,lopHocId',
            'payment_method' => 'required|in:1,2,3',
        ], [
            'targetLopHocId.required' => 'Vui lòng chọn lớp đích để chuyển.',
            'payment_method.required' => 'Vui lòng chọn hình thức thanh toán cho đăng ký mới.',
        ]);

        $staffId = Auth::id();

        try {
            return DB::transaction(function () use ($id, $data, $staffId) {
                $sourceRegistration = $this->findRegistrationForUpdate($id);

                if ($sourceRegistration->isCancelled() || $sourceRegistration->isCompleted()) {
                    throw ValidationException::withMessages([
                        'registration' => 'Không thể chuyển lớp cho đăng ký đã hủy hoặc đã hoàn thành.',
                    ]);
                }

                if ((float) $sourceRegistration->hoaDons->sum('daTra') > 0) {
                    throw ValidationException::withMessages([
                        'registration' => 'Đăng ký đã phát sinh thu tiền. Chỉ được chuyển lớp trước khi có thanh toán.',
                    ]);
                }

                $targetClass = LopHoc::whereKey((int) $data['targetLopHocId'])->lockForUpdate()->firstOrFail();
                if ((int) $targetClass->lopHocId === (int) $sourceRegistration->lopHocId) {
                    throw ValidationException::withMessages([
                        'targetLopHocId' => 'Lớp đích phải khác lớp hiện tại.',
                    ]);
                }

                $targetClass->load(['chinhSachGia.dotThus', 'phuPhis', 'khoaHoc', 'dangKyLopHocs', 'buoiHocs.caHoc', 'coSo']);
                $validation = $this->validateRegistration($sourceRegistration->taiKhoan, $targetClass, $sourceRegistration->dangKyLopHocId);
                if ($validation !== true) {
                    throw ValidationException::withMessages(['targetLopHocId' => $validation]);
                }

                $sourceRegistration->update([
                    'trangThai' => DangKyLopHoc::TRANG_THAI_HUY,
                    'ngayHetHanGiuCho' => null,
                ]);

                $this->appendSystemNoteToInvoices(
                    $sourceRegistration,
                    'Đăng ký đã được điều chuyển sang lớp ' . $targetClass->tenLopHoc . '.'
                );

                return $this->createRegistrationRecord(
                    $sourceRegistration->taiKhoan,
                    $targetClass,
                    (int) $data['payment_method'],
                    $staffId,
                    'Điều chuyển từ lớp ' . ($sourceRegistration->lopHoc?->tenLopHoc ?? 'không xác định')
                );
            }, 3);
        } catch (QueryException $exception) {
            $this->throwIfDuplicateRegistration($exception);
            throw $exception;
        }
    }

    private function availableStudentsQuery(): Builder
    {
        return TaiKhoan::with('hoSoNguoiDung')
            ->where('role', TaiKhoan::ROLE_HOC_VIEN)
            ->where('trangThai', 1)
            ->orderBy('taiKhoan')
            ->orderBy('taiKhoanId');
    }

    private function availableClassesQuery(): Builder
    {
        return LopHoc::with(['khoaHoc', 'coSo'])
            ->where('trangThai', LopHoc::TRANG_THAI_DANG_TUYEN_SINH)
            ->orderBy('ngayBatDau')
            ->orderBy('tenLopHoc');
    }

    private function resolveStudent(int $studentId): TaiKhoan
    {
        $student = TaiKhoan::with('hoSoNguoiDung')->findOrFail($studentId);

        if ((int) $student->role !== TaiKhoan::ROLE_HOC_VIEN || (int) $student->trangThai !== 1) {
            throw ValidationException::withMessages([
                'taiKhoanId' => 'Chỉ có thể tạo đăng ký cho học viên đang hoạt động.',
            ]);
        }

        return $student;
    }

    private function findRegistrationForUpdate(int $id): DangKyLopHoc
    {
        return DangKyLopHoc::whereKey($id)
            ->lockForUpdate()
            ->with([
                'taiKhoan.hoSoNguoiDung',
                'lopHoc.khoaHoc',
                'lopHoc.coSo',
                'lopHoc.chinhSachGia.dotThus',
                'lopHoc.phuPhis',
                'lopHoc.dangKyLopHocs',
                'lopHoc.buoiHocs.caHoc',
                'hoaDons.lopHocDotThu',
            ])
            ->firstOrFail();
    }

    private function validateRegistration(TaiKhoan $student, LopHoc $class, ?int $ignoreRegistrationId = null): bool|string
    {
        if (!$class->isOpenForRegistration()) {
            return 'Lớp học hiện không nhận đăng ký.';
        }

        $pricingPolicy = $class->chinhSachGia;
        if (!$pricingPolicy || !$pricingPolicy->isActive() || (float) $pricingPolicy->hocPhiNiemYet <= 0) {
            return 'Lớp học chưa được cấu hình học phí hợp lệ.';
        }

        if ((int) $pricingPolicy->loaiThu === LopHocChinhSachGia::LOAI_THU_THEO_THANG) {
            return 'Hệ thống hiện chưa hỗ trợ billing theo tháng cho lớp học này.';
        }

        $existingRegistration = DangKyLopHoc::where('taiKhoanId', $student->taiKhoanId)
            ->where('lopHocId', $class->lopHocId)
            ->when($ignoreRegistrationId !== null, fn (Builder $query) => $query->where('dangKyLopHocId', '!=', $ignoreRegistrationId))
            ->first();

        if ($existingRegistration) {
            if ($existingRegistration->blocksSeat()) {
                return 'Học viên đã có đăng ký hiệu lực ở lớp học này.';
            }

            return 'Đã tồn tại lịch sử đăng ký cho học viên ở lớp này. Hãy dùng chức năng khôi phục thay vì tạo mới.';
        }

        if ($class->soHocVienToiDa !== null) {
            $currentStudents = $class->dangKyLopHocs
                ->when($ignoreRegistrationId !== null, fn ($collection) => $collection->where('dangKyLopHocId', '!=', $ignoreRegistrationId))
                ->filter(fn (DangKyLopHoc $registration) => $registration->blocksSeat())
                ->count();

            if ($currentStudents >= (int) $class->soHocVienToiDa) {
                return 'Lớp học đã đủ sĩ số (' . $currentStudents . '/' . (int) $class->soHocVienToiDa . ' học viên).';
            }
        }

        $activeRegistrations = DangKyLopHoc::where('taiKhoanId', $student->taiKhoanId)
            ->when($ignoreRegistrationId !== null, fn (Builder $query) => $query->where('dangKyLopHocId', '!=', $ignoreRegistrationId))
            ->blockingSeat()
            ->with('lopHoc.buoiHocs.caHoc')
            ->get();

        $newSessions = $class->buoiHocs
            ->reject(fn ($session) => in_array((int) $session->trangThai, [BuoiHoc::TRANG_THAI_DA_HUY, BuoiHoc::TRANG_THAI_DOI_LICH], true))
            ->values();

        if ($newSessions->count() > 0) {
            foreach ($activeRegistrations as $registration) {
                $existingClass = $registration->lopHoc;
                if (!$existingClass || $existingClass->isCancelled() || $existingClass->isCompleted() || $existingClass->isSapMo()) {
                    continue;
                }

                $existingSessions = $existingClass->buoiHocs
                    ->reject(fn ($session) => in_array((int) $session->trangThai, [BuoiHoc::TRANG_THAI_DA_HUY, BuoiHoc::TRANG_THAI_DOI_LICH], true))
                    ->values();

                foreach ($existingSessions as $existingSession) {
                    foreach ($newSessions as $newSession) {
                        if ($existingSession->ngayHoc !== $newSession->ngayHoc) {
                            continue;
                        }

                        $startOne = strtotime($existingSession->caHoc->gioBatDau);
                        $endOne = strtotime($existingSession->caHoc->gioKetThuc);
                        $startTwo = strtotime($newSession->caHoc->gioBatDau);
                        $endTwo = strtotime($newSession->caHoc->gioKetThuc);

                        if ($startOne < $endTwo && $startTwo < $endOne) {
                            $dateLabel = Carbon::parse($newSession->ngayHoc)->format('d/m/Y');
                            $timeLabel = $newSession->caHoc->gioBatDau . '-' . $newSession->caHoc->gioKetThuc;

                            return "Lịch học bị trùng với lớp {$existingClass->tenLopHoc} vào ngày {$dateLabel} ({$timeLabel}).";
                        }
                    }
                }
            }

            return true;
        }

        if (!$class->lichHoc || !$class->ngayBatDau || !$class->ngayKetThuc || !$class->caHocId) {
            return true;
        }

        $newDays = array_map('trim', explode(',', $class->lichHoc));
        $newCaId = (int) $class->caHocId;
        $newStart = Carbon::parse($class->ngayBatDau);
        $newEnd = Carbon::parse($class->ngayKetThuc);

        foreach ($activeRegistrations as $registration) {
            $existingClass = $registration->lopHoc;
            if (!$existingClass || $existingClass->isCancelled() || $existingClass->isCompleted()) {
                continue;
            }

            if (!$existingClass->lichHoc || !$existingClass->ngayBatDau || !$existingClass->ngayKetThuc || !$existingClass->caHocId) {
                continue;
            }

            if ((int) $existingClass->caHocId !== $newCaId) {
                continue;
            }

            $existingStart = Carbon::parse($existingClass->ngayBatDau);
            $existingEnd = Carbon::parse($existingClass->ngayKetThuc);

            if ($newStart->gt($existingEnd) || $newEnd->lt($existingStart)) {
                continue;
            }

            $commonDays = array_intersect($newDays, array_map('trim', explode(',', $existingClass->lichHoc)));
            if (!empty($commonDays)) {
                $labels = ['2' => 'Thứ 2', '3' => 'Thứ 3', '4' => 'Thứ 4', '5' => 'Thứ 5', '6' => 'Thứ 6', '7' => 'Thứ 7', 'CN' => 'Chủ nhật'];
                $daysLabel = implode(', ', array_map(fn ($day) => $labels[$day] ?? $day, $commonDays));

                return "Lịch học bị trùng với lớp {$existingClass->tenLopHoc} ({$daysLabel}, cùng ca học, từ {$existingStart->format('d/m/Y')} đến {$existingEnd->format('d/m/Y')}).";
            }
        }

        return true;
    }

    private function createRegistrationRecord(
        TaiKhoan $student,
        LopHoc $class,
        int $paymentMethod,
        ?int $staffId = null,
        ?string $notePrefix = null
    ): DangKyLopHoc {
        $pricingPolicy = $class->chinhSachGia;
        $registrationDate = now();

        if (!$pricingPolicy || !$pricingPolicy->isActive() || (float) $pricingPolicy->hocPhiNiemYet <= 0) {
            throw ValidationException::withMessages([
                'lopHocId' => 'Lớp học chưa có thông tin học phí hợp lệ.',
            ]);
        }

        if ((int) $pricingPolicy->loaiThu === LopHocChinhSachGia::LOAI_THU_THEO_THANG) {
            throw ValidationException::withMessages([
                'lopHocId' => 'Hệ thống hiện chưa hỗ trợ billing theo tháng cho lớp học này.',
            ]);
        }

        $tongTien = (float) $pricingPolicy->hocPhiNiemYet;
        $registration = DangKyLopHoc::create([
            'taiKhoanId' => $student->taiKhoanId,
            'lopHocId' => $class->lopHocId,
            'lopHocChinhSachGiaId' => $pricingPolicy->lopHocChinhSachGiaId,
            'loaiThuSnapshot' => $pricingPolicy->loaiThu,
            'hocPhiNiemYetSnapshot' => $tongTien,
            'giamGiaSnapshot' => 0,
            'hocPhiPhaiThuSnapshot' => $tongTien,
            'soBuoiCamKetSnapshot' => $pricingPolicy->soBuoiCamKetHieuDung,
            'ghiChuGiaSnapshot' => $pricingPolicy->ghiChuChinhSach,
            'ngayDangKy' => $registrationDate,
            'ngayHetHanGiuCho' => null,
            'trangThai' => DangKyLopHoc::TRANG_THAI_CHO_THANH_TOAN,
        ]);

        $dotThus = $pricingPolicy->dotThus
            ->where('trangThai', 1)
            ->sortBy('thuTu')
            ->values();

        $holdDueDates = [];

        if ((int) $pricingPolicy->loaiThu === LopHocChinhSachGia::LOAI_THU_THEO_DOT && $dotThus->isNotEmpty()) {
            foreach ($dotThus as $dotThu) {
                $dueDate = $this->resolveActualDueDate($dotThu->hanThanhToan, $registrationDate);
                $holdDueDates[] = $dueDate;

                HoaDon::create([
                    'maHoaDon' => HoaDon::generateMaHoaDon(),
                    'ngayLap' => $registrationDate,
                    'ngayHetHan' => $dueDate,
                    'tongTien' => (float) $dotThu->soTien,
                    'giamGia' => 0,
                    'thue' => 0,
                    'tongTienSauThue' => (float) $dotThu->soTien,
                    'daTra' => 0,
                    'taiKhoanId' => $student->taiKhoanId,
                    'nguoiLapId' => $staffId,
                    'dangKyLopHocId' => $registration->dangKyLopHocId,
                    'lopHocDotThuId' => $dotThu->lopHocDotThuId,
                    'nguonThu' => HoaDon::NGUON_THU_HOC_PHI,
                    'phuongThucThanhToan' => $paymentMethod,
                    'loaiHoaDon' => HoaDon::LOAI_DANG_KY_MOI,
                    'coSoId' => $class->coSoId,
                    'trangThai' => HoaDon::TRANG_THAI_CHUA_TT,
                    'ghiChu' => trim(($notePrefix ? $notePrefix . ' | ' : '') . 'Đăng ký lớp ' . $class->tenLopHoc . ' - ' . $dotThu->tenDotThu),
                ]);
            }
        } else {
            $dueDate = $this->resolveActualDueDate($pricingPolicy->hanThanhToanHocPhi, $registrationDate);
            $holdDueDates[] = $dueDate;

            HoaDon::create([
                'maHoaDon' => HoaDon::generateMaHoaDon(),
                'ngayLap' => $registrationDate,
                'ngayHetHan' => $dueDate,
                'tongTien' => $tongTien,
                'giamGia' => 0,
                'thue' => 0,
                'tongTienSauThue' => $tongTien,
                'daTra' => 0,
                'taiKhoanId' => $student->taiKhoanId,
                'nguoiLapId' => $staffId,
                'dangKyLopHocId' => $registration->dangKyLopHocId,
                'nguonThu' => HoaDon::NGUON_THU_HOC_PHI,
                'phuongThucThanhToan' => $paymentMethod,
                'loaiHoaDon' => HoaDon::LOAI_DANG_KY_MOI,
                'coSoId' => $class->coSoId,
                'trangThai' => HoaDon::TRANG_THAI_CHUA_TT,
                'ghiChu' => trim(($notePrefix ? $notePrefix . ' | ' : '') . 'Đăng ký lớp ' . $class->tenLopHoc . ' - Khóa ' . ($class->khoaHoc->tenKhoaHoc ?? '')),
            ]);
        }

        $registration->update([
            'ngayHetHanGiuCho' => $this->resolveHoldExpiry($holdDueDates, $registrationDate),
        ]);

        $defaultSupplementalFees = $class->phuPhis
            ->filter(fn (LopHocPhuPhi $supplementalFee) => $supplementalFee->isActive() && $supplementalFee->isDefaultApplied())
            ->values();

        foreach ($defaultSupplementalFees as $supplementalFee) {
            $snapshot = DangKyLopHocPhuPhi::create([
                'dangKyLopHocId' => $registration->dangKyLopHocId,
                'lopHocPhuPhiId' => $supplementalFee->lopHocPhuPhiId,
                'tenKhoanThuSnapshot' => $supplementalFee->tenKhoanThu,
                'nhomPhiSnapshot' => $supplementalFee->nhomPhi,
                'soTienSnapshot' => $supplementalFee->soTien,
                'hanThanhToan' => $this->resolveActualDueDate($supplementalFee->hanThanhToanMau, $registrationDate),
                'trangThai' => DangKyLopHocPhuPhi::TRANG_THAI_HIEU_LUC,
                'ngayApDung' => $registrationDate,
            ]);

            HoaDon::create([
                'maHoaDon' => HoaDon::generateMaHoaDon(),
                'ngayLap' => $registrationDate,
                'ngayHetHan' => $snapshot->hanThanhToan,
                'tongTien' => (float) $snapshot->soTienSnapshot,
                'giamGia' => 0,
                'thue' => 0,
                'tongTienSauThue' => (float) $snapshot->soTienSnapshot,
                'daTra' => 0,
                'taiKhoanId' => $student->taiKhoanId,
                'nguoiLapId' => $staffId,
                'dangKyLopHocId' => $registration->dangKyLopHocId,
                'dangKyLopHocPhuPhiId' => $snapshot->dangKyLopHocPhuPhiId,
                'nguonThu' => HoaDon::NGUON_THU_PHU_PHI,
                'phuongThucThanhToan' => $paymentMethod,
                'loaiHoaDon' => HoaDon::LOAI_KHAC,
                'coSoId' => $class->coSoId,
                'trangThai' => HoaDon::TRANG_THAI_CHUA_TT,
                'ghiChu' => trim(($notePrefix ? $notePrefix . ' | ' : '') . 'Khoản bổ sung: ' . $snapshot->tenKhoanThuSnapshot . ' - Lớp ' . $class->tenLopHoc),
            ]);
        }

        return $registration->fresh([
            'taiKhoan.hoSoNguoiDung',
            'lopHoc.khoaHoc',
            'lopHoc.coSo',
            'hoaDons.lopHocDotThu',
        ]);
    }

    private function syncHoldExpiryForRegistration(DangKyLopHoc $registration): void
    {
        $registration->loadMissing('hoaDons');

        if (!$registration->isPendingPayment()) {
            $registration->update(['ngayHetHanGiuCho' => null]);
            return;
        }

        $holdDueDates = $registration->hoaDons
            ->where('nguonThu', HoaDon::NGUON_THU_HOC_PHI)
            ->whereIn('trangThai', [HoaDon::TRANG_THAI_CHUA_TT, HoaDon::TRANG_THAI_MOT_PHAN])
            ->pluck('ngayHetHan')
            ->filter()
            ->values()
            ->all();

        $registration->update([
            'ngayHetHanGiuCho' => $this->resolveHoldExpiry(
                $holdDueDates,
                $registration->ngayDangKy ? Carbon::parse($registration->ngayDangKy) : now()
            ),
        ]);
    }

    private function appendSystemNoteToInvoices(DangKyLopHoc $registration, string $note): void
    {
        foreach ($registration->hoaDons as $invoice) {
            $currentNote = trim((string) $invoice->ghiChu);
            if (str_contains($currentNote, $note)) {
                continue;
            }

            $invoice->update([
                'ghiChu' => trim($currentNote !== '' ? $currentNote . ' | ' . $note : $note),
            ]);
        }
    }

    private function resolveActualDueDate($templateDate, Carbon $registrationDate): ?string
    {
        if (empty($templateDate)) {
            return null;
        }

        $template = Carbon::parse($templateDate)->startOfDay();
        $registered = $registrationDate->copy()->startOfDay();

        return ($template->lt($registered) ? $registered : $template)->toDateString();
    }

    private function resolveHoldExpiry(array $dueDates, Carbon $registrationDate): Carbon
    {
        $normalizedDueDates = collect($dueDates)
            ->filter()
            ->map(fn ($dueDate) => Carbon::parse($dueDate)->endOfDay())
            ->sort()
            ->values();

        if ($normalizedDueDates->isNotEmpty()) {
            return $normalizedDueDates->first();
        }

        return $registrationDate->copy()->addDays(3)->endOfDay();
    }

    private function throwIfDuplicateRegistration(QueryException $exception): void
    {
        $errorInfo = $exception->errorInfo;
        $isDuplicateRegistration = ($errorInfo[1] ?? null) === 1062
            && str_contains($exception->getMessage(), 'uq_dangkylophoc_student_class');

        if ($isDuplicateRegistration) {
            throw ValidationException::withMessages([
                'registration' => 'Đã tồn tại đăng ký cho học viên ở lớp này. Hãy kiểm tra và dùng khôi phục nếu cần.',
            ]);
        }
    }
}
