<?php

namespace App\Services\Admin;

use App\Contracts\Admin\NhanSuServiceInterface;
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
            if ($request->filled('matKhau')) {
                $nhanSu->update(['matKhau' => Hash::make($request->matKhau)]);
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
        $user  = TaiKhoan::where('role', $role)->where('taiKhoan', $taiKhoan)->firstOrFail();
        $hoTen = $user->hoSoNguoiDung->hoTen ?? $user->taiKhoan;
        $user->delete();
        return $hoTen;
    }

    public function restore(string $taiKhoan, string $role): string
    {
        $user  = TaiKhoan::onlyTrashed()->where('role', $role)->where('taiKhoan', $taiKhoan)->firstOrFail();
        $hoTen = $user->hoSoNguoiDung->hoTen ?? $user->taiKhoan;
        $user->restore();
        return $hoTen;
    }
}
