<?php

namespace App\Http\Controllers\Admin\GiaoVien;

use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
use App\Models\Auth\HoSoNguoiDung;
use App\Models\Auth\NhanSu;
use App\Models\Auth\NhomQuyen;
use App\Models\Facility\CoSoDaoTao;
use App\Models\Facility\TinhThanh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class GiaoVienController extends Controller
{
    /** Danh sách giáo viên */
    public function index(Request $request)
    {
        $query = TaiKhoan::with(['hoSoNguoiDung', 'nhanSu'])
            ->where('role', TaiKhoan::ROLE_GIAO_VIEN);

        // ── Tìm kiếm (tên, email, sđt) ──────────────────────
        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('taiKhoan', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('hoSoNguoiDung', function ($q2) use ($search) {
                      $q2->where('hoTen', 'like', "%{$search}%")
                         ->orWhere('soDienThoai', 'like', "%{$search}%");
                  })
                  ->orWhereHas('nhanSu', function ($q2) use ($search) {
                      $q2->where('chuyenMon', 'like', "%{$search}%")
                         ->orWhere('chucVu', 'like', "%{$search}%");
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

        $giaoViens = $query->paginate(15)->withQueryString();

        // ── Thống kê nhanh ──────────────────────────────────
        $tongSo       = TaiKhoan::where('role', TaiKhoan::ROLE_GIAO_VIEN)->count();
        $dangHoatDong = TaiKhoan::where('role', TaiKhoan::ROLE_GIAO_VIEN)->where('trangThai', 1)->count();
        $thangNay     = TaiKhoan::where('role', TaiKhoan::ROLE_GIAO_VIEN)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return view('admin.giao-vien.index', compact(
            'giaoViens',
            'tongSo',
            'dangHoatDong',
            'thangNay'
        ));
    }

    /** Form thêm giáo viên mới */
    public function create()
    {
        $coSos = CoSoDaoTao::with('tinhThanh')
            ->where('trangThai', 1)
            ->orderBy('tenCoSo')
            ->get();

        $tinhThanhs = TinhThanh::whereHas('coSoDaoTao', fn($q) => $q->where('trangThai', 1))
            ->orderBy('tenTinhThanh')
            ->get();

        // Pre-map to plain array so Blade @json() doesn't choke on complex closures
        $coSosData = $coSos->map(function ($c) {
            return [
                'coSoId'      => $c->coSoId,
                'tenCoSo'     => $c->tenCoSo,
                'diaChi'      => $c->diaChi,
                'tenPhuongXa' => $c->tenPhuongXa,
                'tinhThanhId' => $c->tinhThanhId,
                'maPhuongXa'  => $c->maPhuongXa,
            ];
        })->values()->toArray();

        return view('admin.giao-vien.create', compact('coSos', 'coSosData', 'tinhThanhs'));
    }

    /** Lưu giáo viên mới */
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
            'chucVu'        => 'nullable|string|max:50',
            'chuyenMon'     => 'nullable|string|max:50',
            'bangCap'       => 'nullable|string|max:50',
            'hocVi'         => 'nullable|string|max:50',
            'loaiHopDong'   => 'nullable|string|max:50',
            'ngayVaoLam'    => 'nullable|date',
            'coSoId'        => 'required|exists:cosodaotao,coSoId',
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
            'coSoId.required'    => 'Vui lòng chọn cơ sở làm việc.',
            'coSoId.exists'      => 'Cơ sở làm việc không hợp lệ.',
        ]);

        DB::transaction(function () use ($request) {
            $tenDangNhap = $this->generateUniqueUsername($request->taiKhoan);

            // ── Tìm nhóm quyền "Giáo viên" ──────────────────────
            $nhomGV = NhomQuyen::where('tenNhom', 'like', '%giáo viên%')
                ->orWhere('tenNhom', 'like', '%giao vien%')
                ->first();

            // ── Tạo tài khoản ────────────────────────────────────
            $taiKhoan = TaiKhoan::create([
                'taiKhoan'    => $tenDangNhap,
                'email'       => $request->email,
                'matKhau'     => Hash::make($request->matKhau),
                'role'        => TaiKhoan::ROLE_GIAO_VIEN,
                'trangThai'   => 1,
                'nhomQuyenId' => $nhomGV?->nhomQuyenId,
            ]);

            // ── Tạo hồ sơ người dùng ────────────────────────────
            HoSoNguoiDung::create([
                'taiKhoanId'    => $taiKhoan->taiKhoanId,
                'hoTen'         => $request->hoTen,
                'soDienThoai'   => $request->soDienThoai,
                'zalo'          => $request->zalo,
                'ngaySinh'      => $request->ngaySinh ?: null,
                'gioiTinh'      => $request->gioiTinh,
                'diaChi'        => $request->diaChi,
                'cccd'          => $request->cccd,
                'ghiChu'        => $request->ghiChu,
            ]);

            // ── Tạo bản ghi nhân sự ─────────────────────────────
            NhanSu::create([
                'taiKhoanId'  => $taiKhoan->taiKhoanId,
                'chucVu'      => $request->chucVu,
                'chuyenMon'   => $request->chuyenMon,
                'bangCap'     => $request->bangCap,
                'hocVi'       => $request->hocVi,
                'loaiHopDong' => $request->loaiHopDong,
                'ngayVaoLam'  => $request->ngayVaoLam ?: now()->toDateString(),
                'coSoId'      => $request->coSoId,
                'trangThai'   => 1,
            ]);
        });

        return redirect()->route('admin.giao-vien.index')
            ->with('success', 'Đã tạo giáo viên «' . $request->hoTen . '» thành công.');
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

    /** Form chỉnh sửa giáo viên */
    public function edit(string $taiKhoan)
    {
        $giaoVien = TaiKhoan::with(['hoSoNguoiDung', 'nhanSu'])
            ->where('role', TaiKhoan::ROLE_GIAO_VIEN)
            ->where('taiKhoan', $taiKhoan)
            ->firstOrFail();

        return view('admin.giao-vien.edit', compact('giaoVien'));
    }

    /** Cập nhật giáo viên */
    public function update(Request $request, string $taiKhoan)
    {
        // TODO: implement update
        return redirect()->route('admin.giao-vien.index');
    }

    /** Xóa mềm giáo viên */
    public function destroy(string $taiKhoan)
    {
        $giaoVien = TaiKhoan::where('role', TaiKhoan::ROLE_GIAO_VIEN)
            ->where('taiKhoan', $taiKhoan)
            ->firstOrFail();
        $hoTen = $giaoVien->hoSoNguoiDung->hoTen ?? $giaoVien->taiKhoan;

        $giaoVien->delete();

        return redirect()->route('admin.giao-vien.index')
            ->with('success', "Đã xóa giáo viên «{$hoTen}».");
    }

    /** Thùng rác */
    public function trash(Request $request)
    {
        $query = TaiKhoan::onlyTrashed()
            ->with('hoSoNguoiDung')
            ->where('role', TaiKhoan::ROLE_GIAO_VIEN);

        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('taiKhoan', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('hoSoNguoiDung', fn($q2) => $q2->where('hoTen', 'like', "%{$search}%"));
            });
        }

        $giaoViens = $query->orderByDesc('deleted_at')->paginate(15)->withQueryString();
        $tongXoa   = TaiKhoan::onlyTrashed()->where('role', TaiKhoan::ROLE_GIAO_VIEN)->count();

        return view('admin.giao-vien.trash', compact('giaoViens', 'tongXoa'));
    }

    /** Khôi phục giáo viên đã xóa mềm */
    public function restore(string $taiKhoan)
    {
        $giaoVien = TaiKhoan::onlyTrashed()
            ->where('role', TaiKhoan::ROLE_GIAO_VIEN)
            ->where('taiKhoan', $taiKhoan)
            ->firstOrFail();

        $hoTen = $giaoVien->hoSoNguoiDung->hoTen ?? $giaoVien->taiKhoan;
        $giaoVien->restore();

        return redirect()->route('admin.giao-vien.trash')
            ->with('success', "Đã khôi phục giáo viên «{$hoTen}» thành công.");
    }
}
