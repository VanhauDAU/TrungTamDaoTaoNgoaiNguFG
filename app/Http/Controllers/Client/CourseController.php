<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Auth\TaiKhoan;
use App\Models\Course\DanhMucKhoaHoc;
use App\Models\Course\KhoaHoc;
use App\Models\Education\LopHoc;
use App\Models\Education\DangKyLopHoc;
use App\Models\Education\BuoiHoc;
use App\Models\Finance\HoaDon;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        // Cây danh mục: roots + children đệ quy vô hạn cấp
        $tree = DanhMucKhoaHoc::with(['childrenRecursive' => function ($q) {
            $q->where('trangThai', 1)->ordered();
        }])
        ->whereNull('parent_id')
        ->where('trangThai', 1)
        ->withCount('khoaHocs')
        ->ordered()
        ->get();

        // Filters
        $activeSlug   = $request->input('category');
        $searchQ      = $request->input('q');
        $sortBy       = $request->input('sort', 'newest');
        $activeDanhMuc = null;

        $query = KhoaHoc::where('trangThai', 1)->whereHas('lopHoc');

        // Filter theo danh mục (bao gồm tất cả con đệ quy)
        if ($activeSlug) {
            $dm = DanhMucKhoaHoc::with('childrenRecursive')->where('slug', $activeSlug)->first();
            if ($dm) {
                $activeDanhMuc = $dm;
                $ids = $dm->allDescendantIds();
                $query->whereIn('danhMucId', $ids);
            }
        }

        // Tìm kiếm theo tên
        if ($searchQ) {
            $query->where(function ($q) use ($searchQ) {
                $q->where('tenKhoaHoc', 'like', "%{$searchQ}%")
                  ->orWhere('moTa', 'like', "%{$searchQ}%");
            });
        }

        // Sắp xếp
        match ($sortBy) {
            'name_asc'  => $query->orderBy('tenKhoaHoc', 'asc'),
            'name_desc' => $query->orderBy('tenKhoaHoc', 'desc'),
            default     => $query->orderBy('khoaHocId', 'desc'),
        };

        $listCourses = $query->with('danhMuc')->paginate(9)->withQueryString();

        // Compute ancestor IDs để partial sidebar biết node nào cần mở
        $activeIds = [];
        if ($activeDanhMuc) {
            // Lấy id của chính nó + tất cả tổ tiên
            $dm = $activeDanhMuc;
            while ($dm) {
                $activeIds[] = $dm->danhMucId;
                $dm = $dm->parent_id ? DanhMucKhoaHoc::find($dm->parent_id) : null;
            }
        }

        return view('clients.khoa-hoc.index', compact(
            'tree', 'listCourses', 'activeSlug', 'activeDanhMuc', 'searchQ', 'sortBy', 'activeIds'
        ));
    }
    public function show($slug)
    {
        $course = KhoaHoc::where('slug', $slug)
            ->with([
                'danhMuc',
                'lopHoc.coSo.tinhThanh',  // Load cơ sở và tỉnh thành
                'lopHoc.phongHoc',
                'lopHoc.taiKhoan.hoSoNguoiDung',
                'lopHoc.dangKyLopHocs',
                'hocPhis'
            ])
            ->firstOrFail();

        // Lấy danh sách cơ sở duy nhất có lớp học trong khóa này
        $coSos = $course->lopHoc
            ->filter(fn($lop) => $lop->coSo !== null)
            ->map(fn($lop) => $lop->coSo)
            ->unique('coSoId')
            ->values();

        // Lớp sắp khai giảng gần nhất (sắp mở hoặc đang tuyển sinh, có ngày bắt đầu)
        $upcomingClass = $course->lopHoc
            ->filter(fn($lop) => in_array((int) $lop->trangThai, [
                LopHoc::TRANG_THAI_SAP_MO,
                LopHoc::TRANG_THAI_DANG_TUYEN_SINH,
            ]) && $lop->ngayBatDau !== null)
            ->sortBy('ngayBatDau')
            ->first();

        // Lấy khóa học liên quan cùng loại, khác khóa hiện tại
        $relatedCourses = KhoaHoc::where('danhMucId', $course->danhMucId)
            ->where('khoaHocId', '!=', $course->khoaHocId)
            ->where('trangThai', 1)
            ->with('danhMuc', 'lopHoc')
            ->take(4)
            ->get();

        return view('clients.khoa-hoc.show', compact('course', 'relatedCourses', 'coSos', 'upcomingClass'));
    }
    public function showClass($slug, $slugLopHoc)
    {

        $class = LopHoc::where('slug', $slugLopHoc)
            ->with([
                'khoaHoc.danhMuc',
                'coSo.tinhThanh',
                'phongHoc',
                'taiKhoan.hoSoNguoiDung',
                'hocPhi',
                'dangKyLopHocs'
            ])
            ->firstOrFail();

        if ($class->khoaHoc->slug !== $slug) {
            abort(404);
        }

        return view('clients.lop-hoc.show', compact('class'));
    }

    public function confirmRegistration(Request $request, $slug, $slugLopHoc)
    {
        // 1. Check Login
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để đăng ký khóa học.');
        }

        $user = auth()->user();

        // 2. Chỉ học viên (role = 0) mới được đăng ký
        if ($user->role !== TaiKhoan::ROLE_HOC_VIEN) {
            return redirect()->route('home.classes.show', ['slug' => $slug, 'slugLopHoc' => $slugLopHoc])
                ->with('error', 'Chỉ học viên mới có thể đăng ký lớp học.');
        }

        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')
                ->with('warning', 'Vui lòng xác thực email trước khi đăng ký lớp học.');
        }

        // 3. Get Class Info
        $class = LopHoc::where('slug', $slugLopHoc)
            ->with(['buoiHocs.caHoc', 'dangKyLopHocs', 'hocPhi', 'khoaHoc'])
            ->firstOrFail();

        // 4. Validation Logic (Reusable)
        $validation = $this->validateClassRegistration($user, $class);
        if ($validation !== true) {
            return redirect()->route('home.classes.show', ['slug' => $class->khoaHoc->slug, 'slugLopHoc' => $class->slug])
                ->with('error', $validation);
        }

        // 5. Kiểm tra lớp có gói học phí chưa
        if (!$class->hocPhi || $class->hocPhi->tongHocPhi <= 0) {
            return redirect()->route('home.classes.show', ['slug' => $class->khoaHoc->slug, 'slugLopHoc' => $class->slug])
                ->with('error', 'Lớp học chưa có thông tin học phí. Vui lòng liên hệ trung tâm để được tư vấn.');
        }

        return view('clients.lop-hoc.checkout', compact('class', 'user'));
    }

    public function processRegistration(Request $request, $slug, $slugLopHoc)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Chỉ học viên (role = 0) mới được đăng ký
        if ($user->role !== TaiKhoan::ROLE_HOC_VIEN) {
            return redirect()->route('home.classes.show', ['slug' => $slug, 'slugLopHoc' => $slugLopHoc])
                ->with('error', 'Chỉ học viên mới có thể đăng ký lớp học.');
        }

        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')
                ->with('warning', 'Vui lòng xác thực email trước khi đăng ký lớp học.');
        }

        $class = LopHoc::where('slug', $slugLopHoc)->with(['hocPhi', 'khoaHoc', 'dangKyLopHocs', 'buoiHocs.caHoc', 'coSo'])->firstOrFail();
        // Re-validate
        $validation = $this->validateClassRegistration($user, $class);
        if ($validation !== true) {
            return redirect()->route('home.classes.show', ['slug' => $class->khoaHoc->slug, 'slugLopHoc' => $class->slug])
                ->with('error', $validation);
        }

        // Validate Payment Method
        $request->validate([
            'payment_method' => 'required|in:1,2,3',
        ], [
            'payment_method.required' => 'Vui lòng chọn hình thức thanh toán',
        ]);

        try {
            DB::beginTransaction();

            // 1. Create Registration
            $registration = DangKyLopHoc::create([
                'taiKhoanId' => $user->taiKhoanId,
                'lopHocId' => $class->lopHocId,
                'ngayDangKy' => now(),
                'trangThai' => DangKyLopHoc::TRANG_THAI_CHO_THANH_TOAN,
            ]);

            // 2. Create Invoice — chuẩn hệ thống chuyên nghiệp
            if (!$class->hocPhi || $class->hocPhi->tongHocPhi <= 0) {
                throw new \Exception('Lớp học chưa có thông tin học phí. Không thể tạo hóa đơn.');
            }

            $tongTien = $class->hocPhi->tongHocPhi;
            $giamGia = 0;
            $thue = 0;
            $tongTienSauThue = $tongTien - $giamGia + ($tongTien - $giamGia) * $thue / 100;

            $invoice = HoaDon::create([
                'maHoaDon' => HoaDon::generateMaHoaDon(),
                'ngayLap' => now(),
                'ngayHetHan' => now()->addDays(30),
                'tongTien' => $tongTien,
                'giamGia' => $giamGia,
                'thue' => $thue,
                'tongTienSauThue' => $tongTienSauThue,
                'daTra' => 0,
                'taiKhoanId' => $user->taiKhoanId,
                'nguoiLapId' => null, // Tự đăng ký (không có admin lập)
                'dangKyLopHocId' => $registration->dangKyLopHocId,
                'phuongThucThanhToan' => $request->payment_method,
                'loaiHoaDon' => HoaDon::LOAI_DANG_KY_MOI,
                'coSoId' => $class->coSoId,
                'trangThai' => HoaDon::TRANG_THAI_CHUA_TT,
                'ghiChu' => 'Đăng ký lớp ' . $class->tenLopHoc
                    . ' - Khóa ' . ($class->khoaHoc->tenKhoaHoc ?? ''),
            ]);

            DB::commit();

            return redirect()->route('home.classes.show', ['slug' => $class->khoaHoc->slug, 'slugLopHoc' => $class->slug])
                ->with('success', 'Đăng ký thành công! Vui lòng hoàn tất thanh toán để giữ chỗ.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Kiểm tra hợp lệ trước khi đăng ký lớp học.
     * Trả về true nếu hợp lệ, hoặc chuỗi thông báo lỗi nếu không.
     *
     * Các kiểm tra (theo thứ tự):
     *  1. Trạng thái lớp phải là "Đang tuyển sinh"
     *  2. Sĩ số tối đa (nếu có đặt) chưa đầy
     *  3. Học viên chưa đăng ký lớp này
     *  4a. Tầng 1 - Nếu lớp mới đã có buổi học: so sánh từng buổi cụ thể
     *  4b. Tầng 2 - Fallback: so sánh lịch tổng quát (thứ + ca + khoảng ngày)
     */
    private function validateClassRegistration($user, $class): bool|string
    {
        if (!$class->isOpenForRegistration()) {
            return 'Lớp học hiện không nhận đăng ký.';
        }

        if ($class->soHocVienToiDa !== null) {
            $currentStudents = $class->dangKyLopHocs
                ->filter(fn (DangKyLopHoc $registration) => $registration->blocksSeat())
                ->count();
            if ($currentStudents >= (int) $class->soHocVienToiDa) {
                return 'Lớp học đã đủ sĩ số (' . $currentStudents . '/' . (int) $class->soHocVienToiDa . ' học viên).';
            }
        }

        $isRegistered = DangKyLopHoc::where('taiKhoanId', $user->taiKhoanId)
            ->where('lopHocId', $class->lopHocId)
            ->blockingSeat()
            ->exists();

        if ($isRegistered) {
            return 'Bạn đã đăng ký lớp học này rồi.';
        }

        $activeRegistrations = DangKyLopHoc::where('taiKhoanId', $user->taiKhoanId)
            ->blockingSeat()
            ->with('lopHoc.buoiHocs.caHoc')
            ->get();

        $newSessions = $class->buoiHocs->reject(function ($session) {
            return in_array((int) $session->trangThai, [
                BuoiHoc::TRANG_THAI_DA_HUY,
                BuoiHoc::TRANG_THAI_DOI_LICH,
            ], true);
        })->values();

        // TẦNG 1: Nếu lớp mới đã có buổi học -> so sánh từng buổi cụ thể
        if ($newSessions && $newSessions->count() > 0) {
            foreach ($activeRegistrations as $reg) {
                $existingClass = $reg->lopHoc;
                if (!$existingClass || $existingClass->isCancelled() || $existingClass->isCompleted() || $existingClass->isSapMo()) {
                    continue;
                }

                $existingSessions = $existingClass->buoiHocs->reject(function ($session) {
                    return in_array((int) $session->trangThai, [
                        BuoiHoc::TRANG_THAI_DA_HUY,
                        BuoiHoc::TRANG_THAI_DOI_LICH,
                    ], true);
                })->values();
                foreach ($existingSessions as $existingSession) {
                    foreach ($newSessions as $newSession) {
                        if ($existingSession->ngayHoc !== $newSession->ngayHoc) {
                            continue;
                        }
                        $s1 = strtotime($existingSession->caHoc->gioBatDau);
                        $e1 = strtotime($existingSession->caHoc->gioKetThuc);
                        $s2 = strtotime($newSession->caHoc->gioBatDau);
                        $e2 = strtotime($newSession->caHoc->gioKetThuc);

                        if ($s1 < $e2 && $s2 < $e1) {
                            $conflictDate = \Carbon\Carbon::parse($newSession->ngayHoc)->format('d/m/Y');
                            $conflictTime = $newSession->caHoc->gioBatDau . '-' . $newSession->caHoc->gioKetThuc;
                            return "Lịch học bị trùng với lớp {$existingClass->tenLopHoc} "
                                . "vào ngày {$conflictDate} ({$conflictTime}).";
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
        $newStart = \Carbon\Carbon::parse($class->ngayBatDau);
        $newEnd = \Carbon\Carbon::parse($class->ngayKetThuc);

        foreach ($activeRegistrations as $reg) {
            $existingClass = $reg->lopHoc;

            if (!$existingClass || $existingClass->isCancelled() || $existingClass->isCompleted()) {
                continue;
            }

            if (
                !$existingClass->lichHoc || !$existingClass->ngayBatDau ||
                !$existingClass->ngayKetThuc || !$existingClass->caHocId
            ) {
                continue;
            }

            if ((int) $existingClass->caHocId !== $newCaId) {
                continue;
            }

            $exStart = \Carbon\Carbon::parse($existingClass->ngayBatDau);
            $exEnd = \Carbon\Carbon::parse($existingClass->ngayKetThuc);

            if ($newStart->gt($exEnd) || $newEnd->lt($exStart)) {
                continue;
            }

            $existingDays = array_map('trim', explode(',', $existingClass->lichHoc));
            $commonDays = array_intersect($newDays, $existingDays);

            if (!empty($commonDays)) {
                $thuLabels = [
                    '2' => 'Thứ 2',
                    '3' => 'Thứ 3',
                    '4' => 'Thứ 4',
                    '5' => 'Thứ 5',
                    '6' => 'Thứ 6',
                    '7' => 'Thứ 7',
                    'CN' => 'Chủ nhật',
                ];
                $commonStr = implode(', ', array_map(fn($d) => $thuLabels[$d] ?? $d, $commonDays));
                $exStartStr = $exStart->format('d/m/Y');
                $exEndStr = $exEnd->format('d/m/Y');

                return "Lịch học bị trùng với lớp {$existingClass->tenLopHoc} "
                    . "({$commonStr}, cùng ca học, từ {$exStartStr} đến {$exEndStr}).";
            }
        }

        return true;
    }
}
