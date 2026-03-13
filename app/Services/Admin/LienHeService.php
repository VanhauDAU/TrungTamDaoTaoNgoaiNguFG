<?php

namespace App\Services\Admin;

use App\Contracts\Admin\LienHeServiceInterface;
use App\Models\Auth\TaiKhoan;
use App\Models\Interaction\LienHe;
use App\Models\Interaction\LienHeLichSu;
use App\Models\Interaction\LienHePhanHoi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LienHeService implements LienHeServiceInterface
{
    public function getList(Request $request): array
    {
        $query = LienHe::query()->with('nguoiPhuTrach.hoSoNguoiDung');
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn($sub) => $sub->where('hoTen', 'LIKE', "%{$q}%")->orWhere('email', 'LIKE', "%{$q}%")->orWhere('soDienThoai', 'LIKE', "%{$q}%")->orWhere('tieuDe', 'LIKE', "%{$q}%"));
        }
        if ($request->filled('trangThai'))      $query->where('trangThai', $request->trangThai);
        if ($request->filled('loaiLienHe'))     $query->where('loaiLienHe', $request->loaiLienHe);
        if ($request->filled('nguoiPhuTrachId'))$query->where('nguoiPhuTrachId', $request->nguoiPhuTrachId);
        $orderBy = $request->get('orderBy', 'lienHeId');
        $dir     = $request->get('dir', 'desc');
        $allowed = ['lienHeId', 'hoTen', 'email', 'created_at', 'trangThai', 'loaiLienHe'];
        if (in_array(strtolower($orderBy), $allowed) && in_array($dir, ['asc', 'desc'])) $query->orderBy($orderBy, $dir);

        return [
            'lienHes'      => $query->paginate(15)->withQueryString(),
            'tongSo'       => LienHe::count(),
            'chuaXuLy'     => LienHe::where('trangThai', 0)->count(),
            'dangXuLy'     => LienHe::where('trangThai', 1)->count(),
            'daXuLy'       => LienHe::where('trangThai', 2)->count(),
            'daTuChoi'     => LienHe::where('trangThai', 3)->count(),
            'tongXoa'      => LienHe::onlyTrashed()->count(),
            'statLoai'     => LienHe::selectRaw('loaiLienHe, count(*) as so_luong')->groupBy('loaiLienHe')->pluck('so_luong', 'loaiLienHe')->toArray(),
            'nhanVienList' => TaiKhoan::with('hoSoNguoiDung')->select('taikhoan.taiKhoanId', 'taikhoan.taiKhoan')->whereIn('role', [1, 2, 3])->orderBy('taikhoan.taiKhoan')->get(),
        ];
    }

    public function getDetail(string $id): array
    {
        return [
            'lienHe'       => LienHe::with(['lichSu', 'phanHoi', 'nguoiPhuTrach'])->findOrFail($id),
            'nhanVienList' => TaiKhoan::with('hoSoNguoiDung')->select('taikhoan.taiKhoanId', 'taikhoan.taiKhoan')->whereIn('role', [1, 2, 3])->orderBy('taikhoan.taiKhoan')->get(),
        ];
    }

    public function update(Request $request, string $id): LienHe
    {
        $request->validate([
            'trangThai'   => 'required|in:0,1,2,3',
            'loaiLienHe'  => 'nullable|in:tu_van,ho_tro,khieu_nai,khac',
            'ghiChuNoiBo' => 'nullable|string|max:5000',
        ]);
        $lienHe = LienHe::findOrFail($id);

        if ((int)$request->trangThai !== (int)$lienHe->trangThai) {
            $oldLabel = LienHe::TRANG_THAI_LABELS[$lienHe->trangThai] ?? $lienHe->trangThai;
            $newLabel = LienHe::TRANG_THAI_LABELS[$request->trangThai] ?? $request->trangThai;
            $this->ghiLichSu($lienHe->lienHeId, 'cap_nhat_trang_thai', "Chuyển từ \"{$oldLabel}\" sang \"{$newLabel}\"", $oldLabel, $newLabel);
            if ($request->trangThai == 2) $lienHe->thoiGianXuLy = now();
        }
        if ($request->filled('loaiLienHe') && $request->loaiLienHe !== $lienHe->loaiLienHe) {
            $oldLabel = LienHe::LOAI_LABELS[$lienHe->loaiLienHe] ?? $lienHe->loaiLienHe;
            $newLabel = LienHe::LOAI_LABELS[$request->loaiLienHe] ?? $request->loaiLienHe;
            $this->ghiLichSu($lienHe->lienHeId, 'cap_nhat_loai', "Đổi loại từ \"{$oldLabel}\" sang \"{$newLabel}\"", $oldLabel, $newLabel);
        }
        if ($request->filled('ghiChuNoiBo') && $request->ghiChuNoiBo !== $lienHe->ghiChuNoiBo) {
            $this->ghiLichSu($lienHe->lienHeId, 'ghi_chu', 'Cập nhật ghi chú nội bộ');
        }
        $lienHe->fill($request->only(['trangThai', 'loaiLienHe', 'ghiChuNoiBo']));
        $lienHe->save();
        return $lienHe;
    }

    public function assign(Request $request, string $id): array
    {
        $request->validate(['nguoiPhuTrachId' => 'nullable|exists:taikhoan,taiKhoanId']);
        $lienHe = LienHe::findOrFail($id);
        $oldPhuTrach = $lienHe->nguoiPhuTrach?->hoSoNguoiDung?->hoTen ?? 'Chưa gán';
        $newUser     = $request->nguoiPhuTrachId ? TaiKhoan::find($request->nguoiPhuTrachId) : null;
        $newPhuTrach = $newUser?->hoSoNguoiDung?->hoTen ?? 'Bỏ gán';
        $lienHe->nguoiPhuTrachId = $request->nguoiPhuTrachId;
        $lienHe->save();
        $this->ghiLichSu($lienHe->lienHeId, 'gan_phu_trach', "Gán phụ trách từ \"{$oldPhuTrach}\" sang \"{$newPhuTrach}\"", $oldPhuTrach, $newPhuTrach);
        return ['success' => true, 'message' => $newUser ? "Đã gán phụ trách: {$newPhuTrach}" : 'Đã bỏ gán phụ trách.', 'tenPhuTrach' => $newPhuTrach];
    }

    public function storeReply(Request $request, string $id): array
    {
        $request->validate(['noiDung' => 'required|string|max:5000', 'loai' => 'nullable|in:noi_bo,email']);
        $lienHe = LienHe::findOrFail($id);
        $user   = Auth::user();
        $loai   = $request->input('loai', 'noi_bo');
        $phanHoi = LienHePhanHoi::create([
            'lienHeId'    => $lienHe->lienHeId,
            'noiDung'     => $request->noiDung,
            'loai'        => $loai,
            'nguoiGuiId'  => $user?->taiKhoanId,
            'tenNguoiGui' => $user?->hoSoNguoiDung?->hoTen ?? $user?->taiKhoan ?? 'Admin',
            'daGuiEmail'  => false,
        ]);
        $hanhDong = ($loai === 'email') ? 'gui_email' : 'phan_hoi';
        $this->ghiLichSu($lienHe->lienHeId, $hanhDong, Str::limit($request->noiDung, 100));
        return [
            'success' => true,
            'phanHoi' => [
                'id'               => $phanHoi->phanHoiId,
                'noiDung'          => $phanHoi->noiDung,
                'loai'             => $phanHoi->loai,
                'tenNguoiGui'      => $phanHoi->tenNguoiGui,
                'thoiGian'         => $phanHoi->created_at->format('d/m/Y H:i'),
                'thoiGianRelative' => $phanHoi->created_at->diffForHumans(),
            ],
        ];
    }

    public function destroy(string $id): void
    {
        $lienHe = LienHe::findOrFail($id);
        $this->ghiLichSu($lienHe->lienHeId, 'xoa_mem', 'Chuyển vào thùng rác');
        $lienHe->delete();
    }

    public function getTrash(Request $request): array
    {
        $query = LienHe::onlyTrashed();
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(fn($sub) => $sub->where('hoTen', 'LIKE', "%{$q}%")->orWhere('email', 'LIKE', "%{$q}%")->orWhere('soDienThoai', 'LIKE', "%{$q}%")->orWhere('tieuDe', 'LIKE', "%{$q}%"));
        }
        return [
            'lienHes' => $query->orderBy('deleted_at', 'desc')->paginate(15)->withQueryString(),
            'tongXoa' => LienHe::onlyTrashed()->count(),
        ];
    }

    public function bulkDestroy(Request $request): int
    {
        $ids = array_filter(explode(',', $request->input('ids', '')));
        if (empty($ids)) return 0;
        LienHe::whereIn('lienHeId', $ids)->each(function ($lh) {
            $this->ghiLichSu($lh->lienHeId, 'xoa_mem', 'Xóa hàng loạt');
            $lh->delete();
        });
        return count($ids);
    }

    public function bulkUpdateStatus(Request $request): int
    {
        $request->validate(['trangThai' => 'required|in:0,1,2,3']);
        $ids = array_filter(explode(',', $request->input('ids', '')));
        if (empty($ids)) return 0;
        $newLabel = LienHe::TRANG_THAI_LABELS[$request->trangThai] ?? $request->trangThai;
        LienHe::whereIn('lienHeId', $ids)->each(function ($lh) use ($request, $newLabel) {
            $oldLabel = LienHe::TRANG_THAI_LABELS[$lh->trangThai] ?? $lh->trangThai;
            $this->ghiLichSu($lh->lienHeId, 'cap_nhat_trang_thai', "Cập nhật hàng loạt: \"{$oldLabel}\" → \"{$newLabel}\"", $oldLabel, $newLabel);
        });
        LienHe::whereIn('lienHeId', $ids)->update(['trangThai' => $request->trangThai]);
        return count($ids);
    }

    public function restore(string $id): void
    {
        $lienHe = LienHe::onlyTrashed()->findOrFail($id);
        $lienHe->restore();
        $this->ghiLichSu($lienHe->lienHeId, 'khoi_phuc', 'Khôi phục từ thùng rác');
    }

    private function ghiLichSu(int $lienHeId, string $hanhDong, ?string $noiDung = null, ?string $giaTriCu = null, ?string $giaTriMoi = null): void
    {
        $user = Auth::user();
        LienHeLichSu::create([
            'lienHeId'         => $lienHeId,
            'hanhDong'         => $hanhDong,
            'noiDung'          => $noiDung,
            'giaTriCu'         => $giaTriCu,
            'giaTriMoi'        => $giaTriMoi,
            'nguoiThucHienId'  => $user?->taiKhoanId,
            'tenNguoiThucHien' => $user?->hoSoNguoiDung?->hoTen ?? $user?->taiKhoan ?? 'Hệ thống',
            'created_at'       => now(),
        ]);
    }
}
