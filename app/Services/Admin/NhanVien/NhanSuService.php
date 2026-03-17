<?php

namespace App\Services\Admin\NhanVien;

use App\Contracts\Admin\NhanVien\NhanSuServiceInterface;
use App\Models\Auth\HoSoNguoiDung;
use App\Models\Auth\NhanSu;
use App\Models\Auth\NhomQuyen;
use App\Models\Auth\TaiKhoan;
use App\Models\Facility\CoSoDaoTao;
use App\Models\Facility\TinhThanh;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class NhanSuService implements NhanSuServiceInterface
{
    public function getList(Request $request, string $role): array
    {
        $query = TaiKhoan::with(['hoSoNguoiDung', 'nhanSu'])->where('role', $role);

        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('taiKhoan', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('hoSoNguoiDung', fn($q2) => $q2->where('hoTen', 'like', "%{$search}%")->orWhere('soDienThoai', 'like', "%{$search}%"))
                  ->orWhereHas('nhanSu', fn($q2) => $q2->where('chuyenMon', 'like', "%{$search}%")->orWhere('chucVu', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('trangThai') && $request->trangThai !== '') {
            $query->where('trangThai', $request->trangThai);
        }

        $orderBy = $request->get('orderBy', 'taiKhoanId');
        $dir     = $request->get('dir', 'desc');
        if (in_array($orderBy, ['taiKhoanId', 'email', 'lastLogin'])) {
            $query->orderBy($orderBy, $dir === 'asc' ? 'asc' : 'desc');
        }

        return [
            'items'        => $query->paginate(15)->withQueryString(),
            'tongSo'       => TaiKhoan::where('role', $role)->count(),
            'dangHoatDong' => TaiKhoan::where('role', $role)->where('trangThai', 1)->count(),
            'thangNay'     => TaiKhoan::where('role', $role)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];
    }

    public function getTrashList(Request $request, string $role): array
    {
        $query = TaiKhoan::onlyTrashed()->with('hoSoNguoiDung')->where('role', $role);

        if ($search = $request->q) {
            $query->where(function ($q) use ($search) {
                $q->where('taiKhoan', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('hoSoNguoiDung', fn($q2) => $q2->where('hoTen', 'like', "%{$search}%"));
            });
        }

        return [
            'items'   => $query->orderByDesc('deleted_at')->paginate(15)->withQueryString(),
            'tongXoa' => TaiKhoan::onlyTrashed()->where('role', $role)->count(),
        ];
    }

    public function getCreateFormData(): array
    {
        $coSos = CoSoDaoTao::with('tinhThanh')->where('trangThai', 1)->orderBy('tenCoSo')->get();

        return [
            'coSos'      => $coSos,
            'coSosData'  => $coSos->map(fn($c) => [
                'coSoId'      => $c->coSoId,
                'tenCoSo'     => $c->tenCoSo,
                'diaChi'      => $c->diaChi,
                'tinhThanhId' => $c->tinhThanhId,
            ])->values()->toArray(),
            'tinhThanhs' => TinhThanh::whereHas('coSoDaoTao', fn($q) => $q->where('trangThai', 1))->orderBy('tenTinhThanh')->get(),
        ];
    }

    public function store(Request $request, string $role): TaiKhoan
    {
        return DB::transaction(function () use ($request, $role) {
            $tenNhom = $role === TaiKhoan::ROLE_GIAO_VIEN ? 'giáo viên' : 'nhân viên';
            $nhom    = NhomQuyen::where('tenNhom', 'like', "%{$tenNhom}%")->first();

            $taiKhoan = TaiKhoan::create([
                'taiKhoan'          => TaiKhoan::generateTemporaryUsername($role),
                'email'             => $request->email,
                'matKhau'           => Hash::make($request->matKhau),
                'role'              => $role,
                'trangThai'         => 1,
                'phaiDoiMatKhau'    => 1,
                'nhomQuyenId'       => $nhom?->nhomQuyenId,
                'auth_provider'     => 'local',
                'email_verified_at' => now(),
            ]);

            $taiKhoan->assignSystemUsername();

            HoSoNguoiDung::create([
                'taiKhoanId'  => $taiKhoan->taiKhoanId,
                'hoTen'       => $request->hoTen,
                'soDienThoai' => $request->soDienThoai,
                'zalo'        => $request->zalo,
                'ngaySinh'    => $request->ngaySinh ?: null,
                'gioiTinh'    => $request->gioiTinh,
                'diaChi'      => $request->diaChi,
                'cccd'        => $request->cccd,
                'ghiChu'      => $request->ghiChu,
            ]);

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

            return $taiKhoan;
        });
    }

    public function findByUsername(string $taiKhoan, string $role): TaiKhoan
    {
        return TaiKhoan::with(['hoSoNguoiDung', 'nhanSu'])
            ->where('role', $role)
            ->where('taiKhoan', $taiKhoan)
            ->firstOrFail();
    }

    public function update(Request $request, TaiKhoan $nhanSu): void
    {
        DB::transaction(function () use ($request, $nhanSu) {
            $taiKhoanUpdate = [];
            if ($request->filled('matKhau')) {
                $taiKhoanUpdate['matKhau'] = Hash::make($request->matKhau);
            }
            if ($request->has('email')) {
                $taiKhoanUpdate['email'] = $request->email;
            }
            if ($request->has('trangThai')) {
                $taiKhoanUpdate['trangThai'] = $request->trangThai;
            }
            if (!empty($taiKhoanUpdate)) {
                $nhanSu->update($taiKhoanUpdate);
            }

            $nhanSu->hoSoNguoiDung()->update([
                'hoTen'       => $request->hoTen,
                'soDienThoai' => $request->soDienThoai,
                'zalo'        => $request->zalo,
                'ngaySinh'    => $request->ngaySinh ?: null,
                'gioiTinh'    => $request->gioiTinh,
                'diaChi'      => $request->diaChi,
                'cccd'        => $request->cccd,
                'ghiChu'      => $request->ghiChu,
            ]);

            $nhanSu->nhanSu()->update([
                'chucVu'      => $request->chucVu,
                'chuyenMon'   => $request->chuyenMon,
                'bangCap'     => $request->bangCap,
                'hocVi'       => $request->hocVi,
                'loaiHopDong' => $request->loaiHopDong,
                'ngayVaoLam'  => $request->ngayVaoLam ?: null,
                'coSoId'      => $request->coSoId,
            ]);
        });
    }

    public function destroy(string $taiKhoan, string $role): string
    {
        $nhanSu = $this->findByUsername($taiKhoan, $role);
        $hoTen = $nhanSu->hoSoNguoiDung->hoTen ?? $nhanSu->taiKhoan;

        // Bổ sung logic kiểm tra dữ liệu liên kết trước khi xóa mềm
        $roleInt = (int) $role;
        if ($roleInt === TaiKhoan::ROLE_GIAO_VIEN) {
            $hasLopHoc = \App\Models\Education\LopHoc::where('taiKhoanId', $nhanSu->taiKhoanId)
                ->whereIn('trangThai', [
                    \App\Models\Education\LopHoc::TRANG_THAI_SAP_MO,
                    \App\Models\Education\LopHoc::TRANG_THAI_DANG_TUYEN_SINH,
                    \App\Models\Education\LopHoc::TRANG_THAI_CHOT_DANH_SACH,
                    \App\Models\Education\LopHoc::TRANG_THAI_DANG_HOC,
                ])->exists();

            if ($hasLopHoc) {
                throw new \Exception("Không thể xóa: Giáo viên này đang có lớp học đang hoạt động!");
            }

            $hasBuoiHoc = \App\Models\Education\BuoiHoc::where('taiKhoanId', $nhanSu->taiKhoanId)
                ->whereIn('trangThai', [
                    \App\Models\Education\BuoiHoc::TRANG_THAI_SAP_DIEN_RA,
                    \App\Models\Education\BuoiHoc::TRANG_THAI_DANG_DIEN_RA,
                ])->exists();

            if ($hasBuoiHoc) {
                throw new \Exception("Không thể xóa: Giáo viên này đang có ca dạy/buổi học sắp hoặc đang diễn ra!");
            }
        } elseif ($roleInt === TaiKhoan::ROLE_NHAN_VIEN || $roleInt === TaiKhoan::ROLE_ADMIN) {
            $hasHoaDon = \App\Models\Finance\HoaDon::where('nguoiLapId', $nhanSu->taiKhoanId)->exists();
            if ($hasHoaDon) {
                throw new \Exception("Không thể xóa: Nhân viên này đã từng lập hóa đơn trên hệ thống!");
            }

            $hasPhieuThu = \App\Models\Finance\PhieuThu::where('nguoiDuyetId', $nhanSu->taiKhoanId)->exists();
            if ($hasPhieuThu) {
                throw new \Exception("Không thể xóa: Nhân viên này đã từng duyệt phiếu thu trên hệ thống!");
            }
        }

        DB::transaction(function () use ($nhanSu) {
            // Soft delete
            $nhanSu->delete();
        });

        return $hoTen;
    }

    public function restore(string $taiKhoan, string $role): string
    {
        $nhanSu = TaiKhoan::onlyTrashed()
            ->where('taiKhoan', $taiKhoan)
            ->where('role', $role)
            ->firstOrFail();

        $hoTen = $nhanSu->hoSoNguoiDung->hoTen ?? $nhanSu->taiKhoan;

        DB::transaction(function () use ($nhanSu) {
            $nhanSu->restore();
        });

        return $hoTen;
    }
}