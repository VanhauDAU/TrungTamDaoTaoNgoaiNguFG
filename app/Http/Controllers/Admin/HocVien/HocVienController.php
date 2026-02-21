<?php

namespace App\Http\Controllers\Admin\HocVien;

use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
use App\Models\Auth\HoSoNguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class HocVienController extends Controller
{
    /** Danh sách học viên */
    public function index(Request $request)
    {
        $query = TaiKhoan::with(['hoSoNguoiDung', 'dangKyLopHocs'])
            ->where('role', TaiKhoan::ROLE_HOC_VIEN);

        // ── Tìm kiếm (tên, email, sđt) ──────────────────────
        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('taiKhoan', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('hoSoNguoiDung', function ($q2) use ($search) {
                      $q2->where('hoTen', 'like', "%{$search}%")
                         ->orWhere('soDienThoai', 'like', "%{$search}%");
                  });
            });
        }

        // ── Lọc trạng thái ──────────────────────────────────
        if ($request->filled('trangThai') && $request->trangThai !== '') {
            $query->where('trangThai', $request->trangThai);
        }

        // ── Sắp xếp ─────────────────────────────────────────
        $orderBy = $request->get('orderBy', 'taiKhoanId');
        $dir     = $request->get('dir', 'desc');
        if (in_array($orderBy, ['taiKhoanId', 'email', 'lastLogin'])) {
            $query->orderBy($orderBy, $dir === 'asc' ? 'asc' : 'desc');
        }

        $hocViens = $query->paginate(15)->withQueryString();

        // ── Thống kê nhanh ──────────────────────────────────
        $tongSo      = TaiKhoan::where('role', TaiKhoan::ROLE_HOC_VIEN)->count();
        $dangHoatDong = TaiKhoan::where('role', TaiKhoan::ROLE_HOC_VIEN)->where('trangThai', 1)->count();
        $thangNay    = TaiKhoan::where('role', TaiKhoan::ROLE_HOC_VIEN)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return view('admin.hoc-vien.index', compact(
            'hocViens',
            'tongSo',
            'dangHoatDong',
            'thangNay'
        ));
    }

    /** Form thêm học viên mới */
    public function create()
    {
        return view('admin.hoc-vien.create');
    }

    /** Lưu học viên mới */
    public function store(Request $request)
    {
        $request->validate([
            'taiKhoan'      => 'required|string|max:50',
            'email'         => 'required|email|max:100|unique:taikhoan,email',
            'matKhau'       => 'required|string|min:8|confirmed',
            'hoTen'         => 'required|string|max:100',
            'soDienThoai'   => 'nullable|string|max:20',
            'zalo'          => 'nullable|string|max:20',
            'ngaySinh'      => 'nullable|date',
            'gioiTinh'      => 'nullable|in:0,1,2',
            'diaChi'        => 'nullable|string|max:255',
            'cccd'          => 'nullable|string|max:20|unique:hosonguoidung,cccd',
            'nguoiGiamHo'   => 'nullable|string|max:100',
            'sdtGuardian'   => 'nullable|string|max:20',
            'moiQuanHe'     => 'nullable|string|max:50',
            'trinhDoHienTai'=> 'nullable|string|max:30',
            'ngonNguMucTieu'=> 'nullable|string|max:50',
            'nguonBietDen'  => 'nullable|string|max:50',
            'ghiChu'        => 'nullable|string',
        ], [
            'taiKhoan.required'  => 'Vui lòng nhập tên đăng nhập.',
            'email.required'     => 'Vui lòng nhập email.',
            'email.unique'       => 'Email đã được sử dụng.',
            'matKhau.required'   => 'Vui lòng nhập mật khẩu.',
            'matKhau.min'        => 'Mật khẩu phải ít nhất 8 ký tự.',
            'matKhau.confirmed'  => 'Xác nhận mật khẩu không khớp.',
            'hoTen.required'     => 'Vui lòng nhập họ và tên.',
            'cccd.unique'        => 'CCCD/CMND này đã được đăng ký.',
        ]);

        DB::transaction(function () use ($request) {
            // 1. Đảm bảo tên đăng nhập không trùng
            $tenDangNhap = $this->generateUniqueUsername($request->taiKhoan);

            // 2. Tạo tài khoản
            $taiKhoan = TaiKhoan::create([
                'taiKhoan'  => $tenDangNhap,
                'email'     => $request->email,
                'matKhau'   => Hash::make($request->matKhau),
                'role'      => TaiKhoan::ROLE_HOC_VIEN,
                'trangThai' => 1,
            ]);

            // 2. Tạo hồ sơ học viên
            HoSoNguoiDung::create([
                'taiKhoanId'    => $taiKhoan->taiKhoanId,
                'hoTen'         => $request->hoTen,
                'soDienThoai'   => $request->soDienThoai,
                'zalo'          => $request->zalo,
                'ngaySinh'      => $request->ngaySinh ?: null,
                'gioiTinh'      => $request->gioiTinh,
                'diaChi'        => $request->diaChi,
                'cccd'          => $request->cccd,
                'nguoiGiamHo'   => $request->nguoiGiamHo,
                'sdtGuardian'   => $request->sdtGuardian,
                'moiQuanHe'     => $request->moiQuanHe,
                'trinhDoHienTai'=> $request->trinhDoHienTai,
                'ngonNguMucTieu'=> $request->ngonNguMucTieu,
                'nguonBietDen'  => $request->nguonBietDen,
                'ghiChu'        => $request->ghiChu,
            ]);
        });

        return redirect()->route('admin.hoc-vien.index')
            ->with('success', 'Đã tạo học viên «' . $request->hoTen . '» thành công.');
    }

    /**
     * Tạo tên đăng nhập duy nhất:
     * User_123456 → nếu trùng → User_123456_1 → User_123456_2 ...
     */
    private function generateUniqueUsername(string $base): string
    {
        $candidate = $base;
        $counter   = 1;

        while (TaiKhoan::where('taiKhoan', $candidate)->exists()) {
            $candidate = $base . '_' . $counter;
            $counter++;
        }

        return $candidate;
    }
}
