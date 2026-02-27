<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Course\LoaiKhoaHoc;
use App\Models\Course\KhoaHoc;
use App\Models\Education\LopHoc;
use App\Models\Education\DangKyLopHoc;
use App\Models\Education\BuoiHoc;
use App\Models\Finance\HoaDon;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $listTypeCourses = LoaiKhoaHoc::all();
        
        // Tạo query builder với điều kiện cơ bản
        $query = KhoaHoc::where('trangThai', 1);
        
        // Lọc theo category nếu có
        if ($request->has('category')) {
            $categorySlug = $request->input('category');
            $query->whereHas('loaiKhoaHoc', function($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }
        
        // Lấy danh sách khóa học có ít nhất 1 lớp học với pagination và giữ query parameters
        $listCourses = $query->with('loaiKhoaHoc')->whereHas('lopHoc')->paginate(6)->withQueryString();
        
        return view('clients.courses.index', compact('listTypeCourses', 'listCourses'));
    }
    public function show($slug)
    {
        $course = KhoaHoc::where('slug', $slug)
            ->with([
                'loaiKhoaHoc', 
                'lopHoc.coSo.tinhThanh',  // Load cơ sở và tỉnh thành
                'lopHoc.phongHoc',
                'lopHoc.taiKhoan',
                'hocPhis'
            ])
            ->first();
        
        // Lấy 3 khóa học liên quan cùng loại, khác khóa hiện tại
        $relatedCourses = KhoaHoc::where('loaiKhoaHocId', $course->loaiKhoaHocId)
            ->where('khoaHocId', '!=', $course->khoaHocId)
            ->where('trangThai', 1)
            ->with('loaiKhoaHoc', 'lopHoc')
            ->take(4)
            ->get();
        
        return view('clients.courses.show', compact('course', 'relatedCourses'));
    }
    public function showClass($slug, $slugLopHoc)
    {
        
        $class = LopHoc::where('slug', $slugLopHoc)
            ->with([
                'khoaHoc.loaiKhoaHoc', // Để lấy breadcrumb
                'coSo.tinhThanh',      // Địa điểm
                'phongHoc',            // Phòng học
                'taiKhoan.hoSoNguoiDung', // Giảng viên
                'hocPhi',               // Học phí
                'dangKyLopHocs'
            ])
            ->firstOrFail();

        if ($class->khoaHoc->slug !== $slug) {
            abort(404);
        }

        return view('clients.classes.show', compact('class'));
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

        return view('clients.classes.checkout', compact('class', 'user'));
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

    private function validateClassRegistration($user, $class)
    {
        // Check class is open for registration
        if ($class->trangThai !== 1) {
            return 'Lớp học hiện không nhận đăng ký.';
        }

        // Check Capacity
        $currentStudents = $class->dangKyLopHocs->where('trangThai', '!=', 0)->count();
        if ($currentStudents >= $class->soHocVienToiDa) {
            return 'Lớp học đã đủ sĩ số.';
        }

        // Check Duplicate
        $isRegistered = DangKyLopHoc::where('taiKhoanId', $user->taiKhoanId) 
            ->where('lopHocId', $class->lopHocId)
            ->whereIn('trangThai', [1, 2]) 
            ->exists();

        if ($isRegistered) {
            return 'Bạn đã đăng ký lớp học này rồi.';
        }

        // Check Schedule Conflict
        $userActiveRegistrations = DangKyLopHoc::where('taiKhoanId', $user->taiKhoanId)
            ->whereIn('trangThai', [1, 2])
            ->with('lopHoc.buoiHocs.caHoc')
            ->get();

        $newClassSchedule = $class->buoiHocs;

        foreach ($userActiveRegistrations as $reg) {
            $existingClass = $reg->lopHoc;
            if ($existingClass->trangThai == 3 || $existingClass->trangThai == 0) continue;

            foreach ($existingClass->buoiHocs as $existingSession) {
                foreach ($newClassSchedule as $newSession) {
                    if ($existingSession->ngayHoc == $newSession->ngayHoc) {
                        $start1 = strtotime($existingSession->caHoc->gioBatDau);
                        $end1 = strtotime($existingSession->caHoc->gioKetThuc);
                        $start2 = strtotime($newSession->caHoc->gioBatDau);
                        $end2 = strtotime($newSession->caHoc->gioKetThuc);

                        if ($start1 < $end2 && $start2 < $end1) {
                            return 'Lịch học bị trùng với lớp ' . $existingClass->tenLopHoc . ' vào ngày ' . date('d/m/Y', strtotime($newSession->ngayHoc));
                        }
                    }
                }
            }
        }
        return true;
    }
}
