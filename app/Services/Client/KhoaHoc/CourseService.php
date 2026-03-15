<?php

namespace App\Services\Client\KhoaHoc;

use App\Contracts\Client\KhoaHoc\CourseServiceInterface;
use App\Models\Course\DanhMucKhoaHoc;
use App\Models\Course\KhoaHoc;
use App\Models\Education\BuoiHoc;
use App\Models\Education\DangKyLopHoc;
use App\Models\Education\DangKyLopHocPhuPhi;
use App\Models\Education\LopHoc;
use App\Models\Education\LopHocChinhSachGia;
use App\Models\Education\LopHocPhuPhi;
use App\Models\Finance\HoaDon;
use App\Models\Auth\TaiKhoan;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourseService implements CourseServiceInterface
{
    public function getList(Request $request): array
    {
        $visibleStatuses = [
            LopHoc::TRANG_THAI_SAP_MO,
            LopHoc::TRANG_THAI_DANG_TUYEN_SINH,
        ];

        $tree = DanhMucKhoaHoc::with(['childrenRecursive' => fn($q) => $q->where('trangThai', 1)->ordered()])
            ->whereNull('parent_id')->where('trangThai', 1)->withCount('khoaHocs')->ordered()->get();

        $activeSlug = $request->input('category');
        $searchQ = $request->input('q');
        $sortBy = $request->input('sort', 'newest');
        $activeDanhMuc = null;
        $activeIds = [];

        $query = KhoaHoc::where('trangThai', 1)
            ->whereHas('lopHoc', fn($q) => $q->whereIn('trangThai', $visibleStatuses))
            ->withCount([
            'lopHoc as openClassCount' => fn($q) => $q->whereIn('trangThai', $visibleStatuses),
        ]);

        if ($activeSlug) {
            $dm = DanhMucKhoaHoc::with('childrenRecursive')->where('slug', $activeSlug)->first();
            if ($dm) {
                $activeDanhMuc = $dm;
                $query->whereIn('danhMucId', $dm->allDescendantIds());
                // Build ancestor IDs để sidebar biết node nào cần mở
                $node = $dm;
                while ($node) {
                    $activeIds[] = $node->danhMucId;
                    $node = $node->parent_id ?DanhMucKhoaHoc::find($node->parent_id) : null;
                }
            }
        }

        if ($searchQ) {
            $query->where(fn($q) => $q->where('tenKhoaHoc', 'like', "%{$searchQ}%")->orWhere('moTa', 'like', "%{$searchQ}%"));
        }

        match ($sortBy) {
                'name_asc' => $query->orderBy('tenKhoaHoc', 'asc'),
                'name_desc' => $query->orderBy('tenKhoaHoc', 'desc'),
                default => $query->orderBy('khoaHocId', 'desc'),
            };

        return [
            'tree' => $tree,
            'listCourses' => $query->with('danhMuc')->paginate(9)->withQueryString(),
            'activeSlug' => $activeSlug,
            'activeDanhMuc' => $activeDanhMuc,
            'searchQ' => $searchQ,
            'sortBy' => $sortBy,
            'activeIds' => $activeIds,
        ];
    }

    public function getDetail(string $slug): array
    {
        $visibleStatuses = [
            LopHoc::TRANG_THAI_SAP_MO,
            LopHoc::TRANG_THAI_DANG_TUYEN_SINH,
        ];

        $course = KhoaHoc::where('slug', $slug)->with([
            'danhMuc',
            'lopHoc' => fn($q) => $q->whereIn('trangThai', $visibleStatuses)->with([
        'coSo.tinhThanh',
        'phongHoc',
        'taiKhoan.hoSoNguoiDung',
        'chinhSachGia',
        'dangKyLopHocs',
        ]),
        ])->firstOrFail();

        $coSos = $course->lopHoc->filter(fn($l) => $l->coSo !== null)->map(fn($l) => $l->coSo)->unique('coSoId')->values();

        $upcomingClass = $course->lopHoc
            ->filter(fn($l) => in_array((int)$l->trangThai, $visibleStatuses) && $l->ngayBatDau !== null)
            ->sortBy('ngayBatDau')->first();

        $relatedCourses = KhoaHoc::where('danhMucId', $course->danhMucId)
            ->where('khoaHocId', '!=', $course->khoaHocId)->where('trangThai', 1)
            ->with('danhMuc', 'lopHoc')->take(4)->get();

        return compact('course', 'relatedCourses', 'coSos', 'upcomingClass');
    }

    public function getClassDetail(string $slug, string $slugLopHoc): array
    {
        $class = LopHoc::where('slug', $slugLopHoc)->with([
            'khoaHoc.danhMuc', 'coSo.tinhThanh', 'phongHoc',
            'taiKhoan.hoSoNguoiDung', 'chinhSachGia.dotThus', 'phuPhis', 'dangKyLopHocs',
        ])->firstOrFail();

        if ($class->khoaHoc->slug !== $slug) {
            abort(404);
        }

        return ['class' => $class];
    }

    public function getConfirmRegistrationData(string $slug, string $slugLopHoc): array
    {
        $user = Auth::user();
        if (!$user instanceof TaiKhoan) {
            throw new \RuntimeException('Vui lòng đăng nhập để đăng ký lớp học.');
        }
        $class = LopHoc::where('slug', $slugLopHoc)->with(['buoiHocs.caHoc', 'dangKyLopHocs', 'chinhSachGia.dotThus', 'phuPhis', 'khoaHoc'])->firstOrFail();

        $validation = $this->validateClassRegistration($user, $class);
        if ($validation !== true) {
            throw new \RuntimeException($validation);
        }

        if (!$class->hasValidPricingPolicy()) {
            throw new \RuntimeException('Lớp học chưa có thông tin học phí. Vui lòng liên hệ trung tâm để được tư vấn.');
        }

        return ['class' => $class, 'user' => $user];
    }

    public function processRegistration(Request $request, string $slug, string $slugLopHoc): void
    {
        $user = Auth::user();
        if (!$user instanceof TaiKhoan) {
            throw new \RuntimeException('Vui lòng đăng nhập để đăng ký lớp học.');
        }
        $class = LopHoc::where('slug', $slugLopHoc)->with(['chinhSachGia.dotThus', 'phuPhis', 'khoaHoc', 'dangKyLopHocs', 'buoiHocs.caHoc', 'coSo'])->firstOrFail();

        $validation = $this->validateClassRegistration($user, $class);
        if ($validation !== true) {
            throw new \RuntimeException($validation);
        }

        $request->validate(['payment_method' => 'required|in:1,2,3'], ['payment_method.required' => 'Vui lòng chọn hình thức thanh toán']);

        try {
            DB::transaction(function () use ($user, $request, $slugLopHoc) {
                $class = LopHoc::where('slug', $slugLopHoc)->lockForUpdate()->firstOrFail();
                $class->load(['chinhSachGia.dotThus', 'phuPhis', 'khoaHoc', 'dangKyLopHocs', 'buoiHocs.caHoc', 'coSo']);

                $validation = $this->validateClassRegistration($user, $class);
                if ($validation !== true) {
                    throw new \RuntimeException($validation);
                }

                $pricingPolicy = $class->chinhSachGia;
                $registrationDate = now();

                if (!$pricingPolicy || !$pricingPolicy->isActive() || (float) $pricingPolicy->hocPhiNiemYet <= 0) {
                    throw new \RuntimeException('Lớp học chưa có thông tin học phí. Không thể tạo hóa đơn.');
                }

                if ((int) $pricingPolicy->loaiThu === LopHocChinhSachGia::LOAI_THU_THEO_THANG) {
                    throw new \RuntimeException('Hệ thống hiện chưa hỗ trợ billing theo tháng cho lớp học này.');
                }

                $tongTien = (float) $pricingPolicy->hocPhiNiemYet;
                $registration = DangKyLopHoc::create([
                    'taiKhoanId' => $user->taiKhoanId,
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
                            'taiKhoanId' => $user->taiKhoanId,
                            'nguoiLapId' => null,
                            'dangKyLopHocId' => $registration->dangKyLopHocId,
                            'lopHocDotThuId' => $dotThu->lopHocDotThuId,
                            'nguonThu' => HoaDon::NGUON_THU_HOC_PHI,
                            'phuongThucThanhToan' => $request->payment_method,
                            'loaiHoaDon' => HoaDon::LOAI_DANG_KY_MOI,
                            'coSoId' => $class->coSoId,
                            'trangThai' => HoaDon::TRANG_THAI_CHUA_TT,
                            'ghiChu' => 'Đăng ký lớp ' . $class->tenLopHoc . ' - ' . $dotThu->tenDotThu,
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
                        'taiKhoanId' => $user->taiKhoanId,
                        'nguoiLapId' => null,
                        'dangKyLopHocId' => $registration->dangKyLopHocId,
                        'nguonThu' => HoaDon::NGUON_THU_HOC_PHI,
                        'phuongThucThanhToan' => $request->payment_method,
                        'loaiHoaDon' => HoaDon::LOAI_DANG_KY_MOI,
                        'coSoId' => $class->coSoId,
                        'trangThai' => HoaDon::TRANG_THAI_CHUA_TT,
                        'ghiChu' => 'Đăng ký lớp ' . $class->tenLopHoc . ' - Khóa ' . ($class->khoaHoc->tenKhoaHoc ?? ''),
                    ]);
                }

                $registration->update([
                    'ngayHetHanGiuCho' => $this->resolveHoldExpiry($holdDueDates, $registrationDate),
                ]);

                $defaultSupplementalFees = $class->phuPhis
                    ->filter(fn (LopHocPhuPhi $phuPhi) => $phuPhi->isActive() && $phuPhi->isDefaultApplied())
                    ->values();

                foreach ($defaultSupplementalFees as $phuPhi) {
                    $snapshot = DangKyLopHocPhuPhi::create([
                        'dangKyLopHocId' => $registration->dangKyLopHocId,
                        'lopHocPhuPhiId' => $phuPhi->lopHocPhuPhiId,
                        'tenKhoanThuSnapshot' => $phuPhi->tenKhoanThu,
                        'nhomPhiSnapshot' => $phuPhi->nhomPhi,
                        'soTienSnapshot' => $phuPhi->soTien,
                        'hanThanhToan' => $this->resolveActualDueDate($phuPhi->hanThanhToanMau, $registrationDate),
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
                        'taiKhoanId' => $user->taiKhoanId,
                        'nguoiLapId' => null,
                        'dangKyLopHocId' => $registration->dangKyLopHocId,
                        'dangKyLopHocPhuPhiId' => $snapshot->dangKyLopHocPhuPhiId,
                        'nguonThu' => HoaDon::NGUON_THU_PHU_PHI,
                        'phuongThucThanhToan' => $request->payment_method,
                        'loaiHoaDon' => HoaDon::LOAI_KHAC,
                        'coSoId' => $class->coSoId,
                        'trangThai' => HoaDon::TRANG_THAI_CHUA_TT,
                        'ghiChu' => 'Khoản bổ sung: ' . $snapshot->tenKhoanThuSnapshot . ' - Lớp ' . $class->tenLopHoc,
                    ]);
                }
            }, 3);
        } catch (QueryException $exception) {
            $errorInfo = $exception->errorInfo;
            $isDuplicateRegistration = ($errorInfo[1] ?? null) === 1062
                && str_contains($exception->getMessage(), 'uq_dangkylophoc_student_class');

            if ($isDuplicateRegistration) {
                throw new \RuntimeException('Bạn đã đăng ký lớp học này rồi.');
            }

            throw $exception;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE
    // ─────────────────────────────────────────────────────────────────────────

    private function validateClassRegistration(TaiKhoan $user, LopHoc $class): bool|string
    {
        if (!$class->isOpenForRegistration())
            return 'Lớp học hiện không nhận đăng ký.';

        $pricingPolicy = $class->chinhSachGia;
        if (!$pricingPolicy || !$pricingPolicy->isActive() || (float) $pricingPolicy->hocPhiNiemYet <= 0) {
            return 'Lớp học chưa được cấu hình học phí hợp lệ.';
        }

        if ((int) $pricingPolicy->loaiThu === LopHocChinhSachGia::LOAI_THU_THEO_THANG) {
            return 'Lớp học đang dùng chính sách thu theo tháng, hiện chưa được hỗ trợ trên hệ thống.';
        }

        if ($class->soHocVienToiDa !== null) {
            $currentStudents = $class->dangKyLopHocs->filter(fn(DangKyLopHoc $r) => $r->blocksSeat())->count();
            if ($currentStudents >= (int)$class->soHocVienToiDa) {
                return 'Lớp học đã đủ sĩ số (' . $currentStudents . '/' . (int)$class->soHocVienToiDa . ' học viên).';
            }
        }

        if (DangKyLopHoc::where('taiKhoanId', $user->taiKhoanId)->where('lopHocId', $class->lopHocId)->blockingSeat()->exists()) {
            return 'Bạn đã đăng ký lớp học này rồi.';
        }

        $activeRegistrations = DangKyLopHoc::where('taiKhoanId', $user->taiKhoanId)->blockingSeat()->with('lopHoc.buoiHocs.caHoc')->get();

        $newSessions = $class->buoiHocs->reject(fn($s) => in_array((int)$s->trangThai, [BuoiHoc::TRANG_THAI_DA_HUY, BuoiHoc::TRANG_THAI_DOI_LICH], true))->values();

        // Tầng 1: so sánh từng buổi cụ thể nếu lớp mới có buổi học
        if ($newSessions->count() > 0) {
            foreach ($activeRegistrations as $reg) {
                $ec = $reg->lopHoc;
                if (!$ec || $ec->isCancelled() || $ec->isCompleted() || $ec->isSapMo())
                    continue;
                $existingSessions = $ec->buoiHocs->reject(fn($s) => in_array((int)$s->trangThai, [BuoiHoc::TRANG_THAI_DA_HUY, BuoiHoc::TRANG_THAI_DOI_LICH], true))->values();
                foreach ($existingSessions as $es) {
                    foreach ($newSessions as $ns) {
                        if ($es->ngayHoc !== $ns->ngayHoc)
                            continue;
                        $s1 = strtotime($es->caHoc->gioBatDau);
                        $e1 = strtotime($es->caHoc->gioKetThuc);
                        $s2 = strtotime($ns->caHoc->gioBatDau);
                        $e2 = strtotime($ns->caHoc->gioKetThuc);
                        if ($s1 < $e2 && $s2 < $e1) {
                            $d = Carbon::parse($ns->ngayHoc)->format('d/m/Y');
                            $t = $ns->caHoc->gioBatDau . '-' . $ns->caHoc->gioKetThuc;
                            return "Lịch học bị trùng với lớp {$ec->tenLopHoc} vào ngày {$d} ({$t}).";
                        }
                    }
                }
            }
            return true;
        }

        // Tầng 2: Fallback so sánh lịch tổng quát
        if (!$class->lichHoc || !$class->ngayBatDau || !$class->ngayKetThuc || !$class->caHocId)
            return true;
        $newDays = array_map('trim', explode(',', $class->lichHoc));
        $newCaId = (int)$class->caHocId;
        $newStart = Carbon::parse($class->ngayBatDau);
        $newEnd = Carbon::parse($class->ngayKetThuc);

        foreach ($activeRegistrations as $reg) {
            $ec = $reg->lopHoc;
            if (!$ec || $ec->isCancelled() || $ec->isCompleted())
                continue;
            if (!$ec->lichHoc || !$ec->ngayBatDau || !$ec->ngayKetThuc || !$ec->caHocId)
                continue;
            if ((int)$ec->caHocId !== $newCaId)
                continue;
            $exStart = Carbon::parse($ec->ngayBatDau);
            $exEnd = Carbon::parse($ec->ngayKetThuc);
            if ($newStart->gt($exEnd) || $newEnd->lt($exStart))
                continue;
            $common = array_intersect($newDays, array_map('trim', explode(',', $ec->lichHoc)));
            if (!empty($common)) {
                $labels = ['2' => 'Thứ 2', '3' => 'Thứ 3', '4' => 'Thứ 4', '5' => 'Thứ 5', '6' => 'Thứ 6', '7' => 'Thứ 7', 'CN' => 'Chủ nhật'];
                $str = implode(', ', array_map(fn($d) => $labels[$d] ?? $d, $common));
                return "Lịch học bị trùng với lớp {$ec->tenLopHoc} ({$str}, cùng ca học, từ {$exStart->format('d/m/Y')} đến {$exEnd->format('d/m/Y')}).";
            }
        }

        return true;
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
}
