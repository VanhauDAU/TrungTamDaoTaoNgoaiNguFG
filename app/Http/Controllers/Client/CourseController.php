<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $listTypeCourses = DanhMucKhoaHoc::all();

        // Tạo query builder với điều kiện cơ bản
        $query = KhoaHoc::where('trangThai', 1);

        // Lọc theo category nếu có
        if ($request->has('category')) {
            $categorySlug = $request->input('category');
            $query->whereHas('danhMuc', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Lấy danh sách khóa học có ít nhất 1 lớp học với pagination và giữ query parameters
        $listCourses = $query->with('danhMuc')->whereHas('lopHoc')->paginate(6)->withQueryString();

        return view('clients.khoa-hoc.index', compact('listTypeCourses', 'listCourses'));
    }
    public function show($slug)
    {
        $course = KhoaHoc::where('slug', $slug)
            ->with([
                'danhMuc',
                'lopHoc.coSo.tinhThanh',  // Load cơ sở và tỉnh thành
                'lopHoc.phongHoc',
                'lopHoc.taiKhoan',
                'hocPhis'
            ])
            ->first();

        // Lấy 3 khóa học liên quan cùng loại, khác khóa hiện tại
        $relatedCourses = KhoaHoc::where('danhMucId', $course->danhMucId)
            ->where('khoaHocId', '!=', $course->khoaHocId)
            ->where('trangThai', 1)
            ->with('danhMuc', 'lopHoc')
            ->take(4)
            ->get();

        return view('clients.khoa-hoc.show', compact('course', 'relatedCourses'));
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
        if ($user->role !== \App\Models\Auth\TaiKhoan::ROLE_HOC_VIEN) {
            return redirect()->route('home.classes.show', ['slug' => $slug, 'slugLopHoc' => $slugLopHoc])
                ->with('error', 'Chỉ học viên mới có thể đăng ký lớp học.');
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

        return view('clients.lop-hoc.checkout', compact('class', 'user'));
    }

    public function processRegistration(Request $request, $slug, $slugLopHoc)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Chỉ học viên (role = 0) mới được đăng ký
        if ($user->role !== \App\Models\Auth\TaiKhoan::ROLE_HOC_VIEN) {
            return redirect()->route('home.classes.show', ['slug' => $slug, 'slugLopHoc' => $slugLopHoc])
                ->with('error', 'Chỉ học viên mới có thể đăng ký lớp học.');
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
                'trangThai' => 1 // 1: Chờ thanh toán
            ]);
            // 2. Create Invoice
            // tongHocPhi = soBuoi × donGia (tổng tiền học viên phải đóng)
            $tongTien = $class->hocPhi ? $class->hocPhi->tongHocPhi : 0;
            $invoice = HoaDon::create([
                'ngayLap' => now(),
                'tongTien' => $tongTien,
                'daTra' => 0,
                'taiKhoanId' => $user->taiKhoanId,
                'dangKyLopHocId' => $registration->dangKyLopHocId,
                // 1: tiền mặt, 2: chuyển khoản, 3: vnpay
                'phuongThucThanhToan' => $request->payment_method,
                'coSoId' => $class->coSoId,
                'trangThai' => 0, // 0: Chưa thanh toán
                'ghiChu' => 'Đăng ký lớp ' . $class->tenLopHoc
            ]);
            // kiểm tra lưu hóa đơn thành công không
            if (!$invoice) {
                DB::rollBack();
                return back()->with('error', 'Có lỗi xảy ra: Không thể tạo hóa đơn');
            }
            DB::commit();
            // Todo: Handle Online Payment redirect here

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
     *  1. Trạng thái lớp phải là "Đang mở đăng ký" (trangThai = 1)
     *  2. Sĩ số tối đa (nếu có đặt) chưa đầy
     *  3. Học viên chưa đăng ký lớp này
     *  4a. Tầng 1 - Nếu lớp mới đã có buổi học: so sánh từng buổi cụ thể
     *  4b. Tầng 2 - Fallback: so sánh lịch tổng quát (thứ + ca + khoảng ngày)
     */
    private function validateClassRegistration($user, $class): bool|string
    {
        // 1. Trạng thái lớp phải là "Đang mở đăng ký" (trangThai = 1)
        if ((int) $class->trangThai !== 1) {
            return 'Lớp học hiện không nhận đăng ký.';
        }

        // 2. Sĩ số tối đa - chỉ đếm đăng ký còn hiệu lực: 1=chờ thanh toán, 2=đã xác nhận
        if ($class->soHocVienToiDa !== null) {
            $currentStudents = $class->dangKyLopHocs
                ->whereIn('trangThai', [1, 2])
                ->count();
            if ($currentStudents >= (int) $class->soHocVienToiDa) {
                return 'Lớp học đã đủ sĩ số (' . $currentStudents . '/' . (int) $class->soHocVienToiDa . ' học viên).';
            }
        }

        // 3. Kiểm tra đăng ký trùng
        $isRegistered = DangKyLopHoc::where('taiKhoanId', $user->taiKhoanId)
            ->where('lopHocId', $class->lopHocId)
            ->whereIn('trangThai', [1, 2])
            ->exists();

        if ($isRegistered) {
            return 'Bạn đã đăng ký lớp học này rồi.';
        }

        // 4. Kiểm tra trùng lịch học
        // Lấy các lớp đang hoạt động của học viên (chờ thanh toán + đã xác nhận)
        $activeRegistrations = DangKyLopHoc::where('taiKhoanId', $user->taiKhoanId)
            ->whereIn('trangThai', [1, 2])
            ->with('lopHoc.buoiHocs.caHoc')
            ->get();

        $newSessions = $class->buoiHocs;

        // TẦNG 1: Nếu lớp mới đã có buổi học -> so sánh từng buổi cụ thể
        if ($newSessions && $newSessions->count() > 0) {
            foreach ($activeRegistrations as $reg) {
                $existingClass = $reg->lopHoc;
                if ($existingClass->trangThai == 3 || $existingClass->trangThai == 0)
                    continue;

                $existingSessions = $existingClass->buoiHocs;
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

        // TẦNG 2: fallback - kiểm tra lịch tổng quát
        // Dùng khi lớp mới chưa có buổi học nào
        if (!$class->lichHoc || !$class->ngayBatDau || !$class->ngayKetThuc || !$class->caHocId) {
            return true;
        }

        $newDays = array_map('trim', explode(',', $class->lichHoc));
        $newCaId = (int) $class->caHocId;
        $newStart = \Carbon\Carbon::parse($class->ngayBatDau);
        $newEnd = \Carbon\Carbon::parse($class->ngayKetThuc);

        foreach ($activeRegistrations as $reg) {
            $existingClass = $reg->lopHoc;

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
