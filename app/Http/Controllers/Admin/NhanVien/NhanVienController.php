<?php

namespace App\Http\Controllers\Admin\NhanVien;

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

class NhanVienController extends Controller
{
    /** Danh sách nhân viên */
    public function index(Request $request)
    {
        $query = TaiKhoan::with(['hoSoNguoiDung', 'nhanSu'])
            ->where('role', TaiKhoan::ROLE_NHAN_VIEN);

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
        $dir = $request->get('dir', 'desc');
        if (in_array($orderBy, ['taiKhoanId', 'email', 'lastLogin'])) {
            $query->orderBy($orderBy, $dir === 'asc' ? 'asc' : 'desc');
        }

        $nhanViens = $query->paginate(15)->withQueryString();

        // ── Thống kê nhanh ──────────────────────────────────
        $tongSo = TaiKhoan::where('role', TaiKhoan::ROLE_NHAN_VIEN)->count();
        $dangHoatDong = TaiKhoan::where('role', TaiKhoan::ROLE_NHAN_VIEN)->where('trangThai', 1)->count();
        $thangNay = TaiKhoan::where('role', TaiKhoan::ROLE_NHAN_VIEN)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return view('admin.nhan-vien.index', compact(
            'nhanViens',
            'tongSo',
            'dangHoatDong',
            'thangNay'
        ));
    }

    /** Form thêm nhân viên mới */
    public function create()
    {
        $coSos = CoSoDaoTao::with('tinhThanh')
            ->where('trangThai', 1)
            ->orderBy('tenCoSo')
            ->get();

        $tinhThanhs = TinhThanh::whereHas('coSoDaoTao', fn($q) => $q->where('trangThai', 1))
            ->orderBy('tenTinhThanh')
            ->get();

        return view('admin.nhan-vien.create', compact('coSos', 'tinhThanhs'));
    }

    /** Lưu nhân viên mới */
    public function store(Request $request)
    {
        $request->validate([
            'taiKhoan' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:taikhoan,email',
            'matKhau' => 'required|string|min:8|confirmed',
            'hoTen' => 'required|string|max:100',
            'soDienThoai' => 'nullable|string|max:20',
            'zalo' => 'nullable|string|max:20',
            'ngaySinh' => 'nullable|date',
            'gioiTinh' => 'nullable|in:0,1,2',
            'diaChi' => 'nullable|string|max:255',
            'cccd' => 'nullable|string|max:20|unique:hosonguoidung,cccd',
            'chucVu' => 'nullable|string|max:50',
            'chuyenMon' => 'nullable|string|max:50',
            'bangCap' => 'nullable|string|max:50',
            'hocVi' => 'nullable|string|max:50',
            'loaiHopDong' => 'nullable|string|max:50',
            'ngayVaoLam' => 'nullable|date',
            'coSoId' => 'required|exists:cosodaotao,coSoId',
            'ghiChu' => 'nullable|string',
        ], [
            'taiKhoan.required' => 'Vui lòng nhập tên đăng nhập.',
            'email.required' => 'Vui lòng nhập email.',
            'email.unique' => 'Email đã được sử dụng.',
            'matKhau.required' => 'Vui lòng nhập mật khẩu.',
            'matKhau.min' => 'Mật khẩu phải ít nhất 8 ký tự.',
            'matKhau.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'hoTen.required' => 'Vui lòng nhập họ và tên.',
            'cccd.unique' => 'CCCD/CMND này đã được đăng ký.',
            'coSoId.required' => 'Vui lòng chọn cơ sở làm việc.',
            'coSoId.exists' => 'Cơ sở làm việc không hợp lệ.',
        ]);

        DB::transaction(function () use ($request) {
            $tenDangNhap = $this->generateUniqueUsername($request->taiKhoan);

            // ── Tìm nhóm quyền "Nhân viên" ──────────────────────
            $nhomNV = NhomQuyen::where('tenNhom', 'like', '%nhân viên%')
                ->orWhere('tenNhom', 'like', '%nhan vien%')
                ->first();

            // ── Tạo tài khoản ────────────────────────────────────
            $taiKhoan = TaiKhoan::create([
                'taiKhoan' => $tenDangNhap,
                'email' => $request->email,
                'matKhau' => Hash::make($request->matKhau),
                'role' => TaiKhoan::ROLE_NHAN_VIEN,
                'trangThai' => 1,
                'phaiDoiMatKhau' => 1,
                'nhomQuyenId' => $nhomNV?->nhomQuyenId,
            ]);

            // ── Tạo hồ sơ người dùng ────────────────────────────
            HoSoNguoiDung::create([
                'taiKhoanId' => $taiKhoan->taiKhoanId,
                'hoTen' => $request->hoTen,
                'soDienThoai' => $request->soDienThoai,
                'zalo' => $request->zalo,
                'ngaySinh' => $request->ngaySinh ?: null,
                'gioiTinh' => $request->gioiTinh,
                'diaChi' => $request->diaChi,
                'cccd' => $request->cccd,
                'ghiChu' => $request->ghiChu,
            ]);

            // ── Tạo bản ghi nhân sự ─────────────────────────────
            NhanSu::create([
                'taiKhoanId' => $taiKhoan->taiKhoanId,
                'chucVu' => $request->chucVu,
                'chuyenMon' => $request->chuyenMon,
                'bangCap' => $request->bangCap,
                'hocVi' => $request->hocVi,
                'loaiHopDong' => $request->loaiHopDong,
                'ngayVaoLam' => $request->ngayVaoLam ?: now()->toDateString(),
                'coSoId' => $request->coSoId,
                'trangThai' => 1,
            ]);
        });

        return redirect()->route('admin.nhan-vien.index')
            ->with('success', 'Đã tạo nhân viên «' . $request->hoTen . '» thành công.');
    }

    /**
     * Tạo tên đăng nhập duy nhất
     */
    private function generateUniqueUsername(string $base): string
    {
        $candidate = $base;
        $counter = 1;

        while (TaiKhoan::where('taiKhoan', $candidate)->exists()) {
            $candidate = $base . '_' . $counter;
            $counter++;
        }

        return $candidate;
    }

    /** Form chỉnh sửa nhân viên */
    public function edit(string $taiKhoan)
    {
        $nhanVien = TaiKhoan::with(['hoSoNguoiDung', 'nhanSu'])
            ->where('role', TaiKhoan::ROLE_NHAN_VIEN)
            ->where('taiKhoan', $taiKhoan)
            ->firstOrFail();

        return view('admin.nhan-vien.edit', compact('nhanVien'));
    }

    /** Cập nhật nhân viên */
    public function update(Request $request, string $taiKhoan)
    {
        $nhanVien = TaiKhoan::with(['hoSoNguoiDung', 'nhanSu'])
            ->where('role', TaiKhoan::ROLE_NHAN_VIEN)
            ->where('taiKhoan', $taiKhoan)
            ->firstOrFail();

        $request->validate([
            'matKhau' => 'nullable|string|min:8|confirmed',
            'hoTen' => 'required|string|max:100',
            'soDienThoai' => 'nullable|string|max:20',
            'zalo' => 'nullable|string|max:20',
            'ngaySinh' => 'nullable|date',
            'gioiTinh' => 'nullable|in:0,1,2',
            'dioChi' => 'nullable|string|max:255',
            'cccd' => 'nullable|string|max:20|unique:hosonguoidung,cccd,' . $nhanVien->taiKhoanId . ',taiKhoanId',
            'chucVu' => 'nullable|string|max:50',
            'chuyenMon' => 'nullable|string|max:50',
            'bangCap' => 'nullable|string|max:50',
            'hocVi' => 'nullable|string|max:50',
            'loaiHopDong' => 'nullable|string|max:50',
            'ngayVaoLam' => 'nullable|date',
            'coSoId' => 'required|exists:cosodaotao,coSoId',
            'ghiChu' => 'nullable|string',
        ], [
            'matKhau.min' => 'Mật khẩu phải ít nhất 8 ký tự.',
            'matKhau.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'hoTen.required' => 'Vui lòng nhập họ và tên.',
            'cccd.unique' => 'CCCD/CMND này đã được đăng ký.',
            'coSoId.required' => 'Vui lòng chọn cơ sở làm việc.',
            'coSoId.exists' => 'Cơ sở làm việc không hợp lệ.',
        ]);

        DB::transaction(function () use ($request, $nhanVien) {
            // Update mật khẩu nếu có nhập
            if ($request->filled('matKhau')) {
                $nhanVien->update([
                    'matKhau' => Hash::make($request->matKhau)
                ]);
            }

            // Update hồ sơ
            $nhanVien->hoSoNguoiDung()->update([
                'hoTen' => $request->hoTen,
                'soDienThoai' => $request->soDienThoai,
                'zalo' => $request->zalo,
                'ngaySinh' => $request->ngaySinh ?: null,
                'gioiTinh' => $request->gioiTinh,
                'diaChi' => $request->diaChi,
                'cccd' => $request->cccd,
                'ghiChu' => $request->ghiChu,
            ]);

            // Update nhân sự
            $nhanVien->nhanSu()->update([
                'chucVu' => $request->chucVu,
                'chuyenMon' => $request->chuyenMon,
                'bangCap' => $request->bangCap,
                'hocVi' => $request->hocVi,
                'loaiHopDong' => $request->loaiHopDong,
                'ngayVaoLam' => $request->ngayVaoLam ?: null,
                'coSoId' => $request->coSoId,
            ]);
        });

        return redirect()->route('admin.nhan-vien.index')
            ->with('success', 'Đã cập nhật nhân viên «' . $request->hoTen . '» thành công.');
    }

    /** Xóa mềm nhân viên */
    public function destroy(string $taiKhoan)
    {
        $nhanVien = TaiKhoan::where('role', TaiKhoan::ROLE_NHAN_VIEN)
            ->where('taiKhoan', $taiKhoan)
            ->firstOrFail();
        $hoTen = $nhanVien->hoSoNguoiDung->hoTen ?? $nhanVien->taiKhoan;

        $nhanVien->delete();

        return redirect()->route('admin.nhan-vien.index')
            ->with('success', "Đã xóa nhân viên «{$hoTen}».");
    }

    /** Thùng rác */
    public function trash(Request $request)
    {
        $query = TaiKhoan::onlyTrashed()
            ->with('hoSoNguoiDung')
            ->where('role', TaiKhoan::ROLE_NHAN_VIEN);

        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('taiKhoan', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('hoSoNguoiDung', fn($q2) => $q2->where('hoTen', 'like', "%{$search}%"));
            });
        }

        $nhanViens = $query->orderByDesc('deleted_at')->paginate(15)->withQueryString();
        $tongXoa = TaiKhoan::onlyTrashed()->where('role', TaiKhoan::ROLE_NHAN_VIEN)->count();

        return view('admin.nhan-vien.trash', compact('nhanViens', 'tongXoa'));
    }

    /** Khôi phục nhân viên đã xóa mềm */
    public function restore(string $taiKhoan)
    {
        $nhanVien = TaiKhoan::onlyTrashed()
            ->where('role', TaiKhoan::ROLE_NHAN_VIEN)
            ->where('taiKhoan', $taiKhoan)
            ->firstOrFail();

        $hoTen = $nhanVien->hoSoNguoiDung->hoTen ?? $nhanVien->taiKhoan;
        $nhanVien->restore();

        return redirect()->route('admin.nhan-vien.trash')
            ->with('success', "Đã khôi phục nhân viên «{$hoTen}» thành công.");
    }
}
