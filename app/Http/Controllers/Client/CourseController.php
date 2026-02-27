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

    /**
     * Kiểm tra hợp lệ trước khi đăng ký lớp học.
     *
     * Tr\u1ea3 v\u1ec1 true n\u1ebfu h\u1ee3p l\u1ec7, ho\u1eb7c chu\u1ed7i th\u00f4ng b\u00e1o l\u1ed7i n\u1ebfu kh\u00f4ng.
     *
     * C\u00e1c ki\u1ec3m tra theo th\u1ee9 t\u1ef1:
     *  1. Tr\u1ea1ng th\u00e1i l\u1edbp ph\u1ea3i l\u00e0 "Đang m\u1edf \u0111\u0103ng k\u00fd" (trangThai = 1)
     *  2. S\u0129 s\u1ed1 t\u1ed1i \u0111a (n\u1ebfu c\u00f3 \u0111\u1eb7t) ch\u01b0a \u0111\u1ea7y — ch\u1ec9 \u0111\u1ebfm \u0111\u0103ng k\u00fd c\u00f2n hi\u1ec7u l\u1ef1c (trangThai 1,2)
     *  3. H\u1ecdc vi\u00ean ch\u01b0a \u0111\u0103ng k\u00fd l\u1edbp n\u00e0y
     *  4a. T\u1ea7ng 1 — N\u1ebfu l\u1edbp m\u1edbi \u0111\u00e3 c\u00f3 bu\u1ed5i h\u1ecdc: so s\u00e1nh t\u1eebng bu\u1ed5i c\u1ee5 th\u1ec3
     *  4b. T\u1ea7ng 2 — Fallback: so s\u00e1nh l\u1ecbch t\u1ed5ng qu\u00e1t (th\u1ee9 + ca + kho\u1ea3ng ng\u00e0y)
     */
    private function validateClassRegistration($user, $class): bool|string
    {
        // ────────────────────────────────────────────────────────────
        // 1. TR\u1ea0NG TH\u00c1I L\u1edaP
        // ────────────────────────────────────────────────────────────
        if ((int) $class->trangThai !== 1) {
            return 'L\u1edbp h\u1ecdc hi\u1ec7n kh\u00f4ng nh\u1eadn \u0111\u0103ng k\u00fd.';
        }

        // ────────────────────────────────────────────────────────────
        // 2. S\u0128 S\u1ed0 T\u1ed0I \u0110A
        //    Ch\u1ec9 \u0111\u1ebfm \u0111\u0103ng k\u00fd c\u00f2n hi\u1ec7u l\u1ef1c: 1 = ch\u1edd thanh to\u00e1n, 2 = \u0111\u00e3 x\u00e1c nh\u1eadn
        // ────────────────────────────────────────────────────────────
        if ($class->soHocVienToiDa !== null) {
            $currentStudents = $class->dangKyLopHocs
                ->whereIn('trangThai', [1, 2])
                ->count();
            if ($currentStudents >= (int) $class->soHocVienToiDa) {
                return 'L\u1edbp h\u1ecdc \u0111\u00e3 \u0111\u1ee7 s\u0129 s\u1ed1 (' . $currentStudents . '/' . (int)$class->soHocVienToiDa . ' h\u1ecdc vi\u00ean).';
            }
        }

        // ────────────────────────────────────────────────────────────
        // 3. KI\u1ec2M TRA TR\u00d9NG L\u1edaP
        // ────────────────────────────────────────────────────────────
        $alreadyRegistered = DangKyLopHoc::where('taiKhoanId', $user->taiKhoanId)
            ->where('lopHocId', $class->lopHocId)
            ->whereIn('trangThai', [1, 2])
            ->exists();

        if ($alreadyRegistered) {
            return 'B\u1ea1n \u0111\u00e3 \u0111\u0103ng k\u00fd l\u1edbp h\u1ecdc n\u00e0y r\u1ed3i.';
        }

        // ────────────────────────────────────────────────────────────
        // L\u1ea5y c\u00e1c l\u1edbp \u0111ang ho\u1ea1t \u0111\u1ed9ng c\u1ee7a h\u1ecdc vi\u00ean
        // Lo\u1ea1i b\u1ecf: trangThai = 2 (\u0111\u00e3 \u0111\u00f3ng), 3 (\u0111\u00e3 h\u1ee7y), kh\u00f4ng c\u00f3 l\u1edbp
        // ────────────────────────────────────────────────────────────
        $activeRegistrations = DangKyLopHoc::where('taiKhoanId', $user->taiKhoanId)
            ->whereIn('trangThai', [1, 2]) // ch\u1edd thanh to\u00e1n + \u0111\u00e3 x\u00e1c nh\u1eadn
            ->with([
                'lopHoc' => function ($q) {
                    // Ch\u1ec9 l\u1ea5y l\u1edbp \u0111ang ho\u1ea1t \u0111\u1ed9ng (Đang m\u1edf = 1, Đang h\u1ecdc = 4)
                    $q->whereIn('trangThai', [1, 4])
                      ->with('buoiHocs.caHoc', 'caHoc');
                },
            ])
            ->get()
            ->filter(fn($reg) => $reg->lopHoc !== null); // lo\u1ea1i null (b\u1ecb x\u00f3a)

        // ────────────────────────────────────────────────────────────
        // 4a. T\u1ea6NG 1: KI\u1ec2M TRA T\u1eea\u0041 B\u1ee4\u1ed2I H\u1eccc C\u1ee4 TH\u1ec2
        //     \u00c1p d\u1ee5ng khi l\u1edbp m\u1edbi \u0111\u00e3 c\u00f3 \u00edt nh\u1ea5t 1 bu\u1ed5i h\u1ecdc
        // ────────────────────────────────────────────────────────────
        $newSessions = $class->buoiHocs
            ->filter(fn($bh) => $bh->caHoc !== null && !$bh->daHoanThanh);

        if ($newSessions->isNotEmpty()) {
            foreach ($activeRegistrations as $reg) {
                $existingClass = $reg->lopHoc;

                $existingSessions = $existingClass->buoiHocs
                    ->filter(fn($bh) => $bh->caHoc !== null && !$bh->daHoanThanh);

                foreach ($existingSessions as $existingSession) {
                    foreach ($newSessions as $newSession) {
                        // Ph\u1ea3i c\u00f9ng ng\u00e0y
                        if ($existingSession->ngayHoc !== $newSession->ngayHoc) {
                            continue;
                        }

                        // So s\u00e1nh th\u1eddi gian giao thoa
                        $s1 = strtotime($existingSession->caHoc->gioBatDau);
                        $e1 = strtotime($existingSession->caHoc->gioKetThuc);
                        $s2 = strtotime($newSession->caHoc->gioBatDau);
                        $e2 = strtotime($newSession->caHoc->gioKetThuc);

                        if ($s1 < $e2 && $s2 < $e1) {
                            $conflictDate = \Carbon\Carbon::parse($newSession->ngayHoc)->format('d/m/Y');
                            $conflictTime = $newSession->caHoc->gioBatDau . '–' . $newSession->caHoc->gioKetThuc;
                            return "L\u1ecbch h\u1ecdc b\u1ecb tr\u00f9ng v\u1edbi l\u1edbp \u00ab{$existingClass->tenLopHoc}\u00bb "
                                . "v\u00e0o ng\u00e0y {$conflictDate} ({$conflictTime}).";
                        }
                    }
                }
            }

            // Kh\u00f4ng tr\u00f9ng gi\u1edd c\u1ee5 th\u1ec3 → h\u1ee3p l\u1ec7
            return true;
        }

        // ────────────────────────────────────────────────────────────
        // 4b. T\u1ea6NG 2: FALLBACK — KI\u1ec2M TRA L\u1ecaCH T\u1ed4NG QU\u00c1T
        //     D\u00f9ng khi l\u1edbp m\u1edbi CH\u01af\u0041 c\u00f3 bu\u1ed5i h\u1ecdc n\u00e0o
        //     So s\u00e1nh: th\u1ee9 trong tu\u1ea7n (lichHoc) + caHocId + kho\u1ea3ng ng\u00e0y
        // ────────────────────────────────────────────────────────────
        if (!$class->lichHoc || !$class->ngayBatDau || !$class->ngayKetThuc || !$class->caHocId) {
            // Kh\u00f4ng \u0111\u1ee7 th\u00f4ng tin \u0111\u1ec3 so s\u00e1nh → b\u1ecf qua
            return true;
        }

        $newDays   = array_map('trim', explode(',', $class->lichHoc));   // ['2','4','6']
        $newCaId   = (int) $class->caHocId;
        $newStart  = \Carbon\Carbon::parse($class->ngayBatDau);
        $newEnd    = \Carbon\Carbon::parse($class->ngayKetThuc);

        // Map th\u1ee9 ti\u1ebfng Vi\u1ec7t → dayOfWeek PHP (0=Sun)
        $thuToDow = ['2'=>1,'3'=>2,'4'=>3,'5'=>4,'6'=>5,'7'=>6,'CN'=>0];

        foreach ($activeRegistrations as $reg) {
            $existingClass = $reg->lopHoc;

            if (!$existingClass->lichHoc || !$existingClass->ngayBatDau ||
                !$existingClass->ngayKetThuc || !$existingClass->caHocId) {
                continue;
            }

            // Ki\u1ec3m tra c\u00f9ng ca h\u1ecdc
            if ((int) $existingClass->caHocId !== $newCaId) {
                continue;
            }

            // Ki\u1ec3m tra kho\u1ea3ng ng\u00e0y giao nhau
            $exStart = \Carbon\Carbon::parse($existingClass->ngayBatDau);
            $exEnd   = \Carbon\Carbon::parse($existingClass->ngayKetThuc);

            if ($newStart->gt($exEnd) || $newEnd->lt($exStart)) {
                // Hai kh\u00f3a h\u1ecdc kh\u00f4ng giao nhau v\u1ec1 th\u1eddi gian
                continue;
            }

            // Ki\u1ec3m tra c\u00f3 th\u1ee9 n\u00e0o tr\u00f9ng nhau kh\u00f4ng
            $existingDays = array_map('trim', explode(',', $existingClass->lichHoc));
            $commonDays   = array_intersect($newDays, $existingDays);

            if (!empty($commonDays)) {
                $thuLabels = ['2'=>'Th\u1ee9 2','3'=>'Th\u1ee9 3','4'=>'Th\u1ee9 4',
                              '5'=>'Th\u1ee9 5','6'=>'Th\u1ee9 6','7'=>'Th\u1ee9 7','CN'=>'Ch\u1ee7 nh\u1eadt'];
                $commonStr = implode(', ', array_map(
                    fn($d) => $thuLabels[$d] ?? $d,
                    $commonDays
                ));

                $exStartStr = $exStart->format('d/m/Y');
                $exEndStr   = $exEnd->format('d/m/Y');

                return "L\u1ecbch h\u1ecdc b\u1ecb tr\u00f9ng v\u1edbi l\u1edbp \u00ab{$existingClass->tenLopHoc}\u00bb "
                    . "({$commonStr}, c\u00f9ng ca h\u1ecdc, t\u1eeb {$exStartStr} \u0111\u1ebfn {$exEndStr}).";
            }
        }

        return true;
    }
}
