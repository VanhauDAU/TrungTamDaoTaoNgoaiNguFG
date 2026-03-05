<?php

namespace App\Http\Controllers\Admin\LienHe;

use App\Http\Controllers\Controller;
use App\Models\Interaction\LienHe;
use App\Models\Interaction\LienHeLichSu;
use App\Models\Interaction\LienHePhanHoi;
use App\Models\Auth\TaiKhoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LienHeController extends Controller
{
    // ─── Helper: ghi lịch sử ──────────────────────────────────────────────────

    private function ghiLichSu(
        int $lienHeId,
        string $hanhDong,
        ?string $noiDung  = null,
        ?string $giaTriCu = null,
        ?string $giaTriMoi = null
    ): void {
        $user = Auth::user();
        LienHeLichSu::create([
            'lienHeId'          => $lienHeId,
            'hanhDong'          => $hanhDong,
            'noiDung'           => $noiDung,
            'giaTriCu'          => $giaTriCu,
            'giaTriMoi'         => $giaTriMoi,
            'nguoiThucHienId'   => $user?->taiKhoanId,
            'tenNguoiThucHien'  => $user?->hoSoNguoiDung?->hoTen ?? $user?->taiKhoan ?? 'Hệ thống',
            'created_at'        => now(),
        ]);
    }

    // ─── index ────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = LienHe::query()->with('nguoiPhuTrach.hoSoNguoiDung');

        // Search
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('hoTen', 'LIKE', "%{$q}%")
                    ->orWhere('email', 'LIKE', "%{$q}%")
                    ->orWhere('soDienThoai', 'LIKE', "%{$q}%")
                    ->orWhere('tieuDe', 'LIKE', "%{$q}%");
            });
        }

        // Filter: trạng thái
        if ($request->filled('trangThai')) {
            $query->where('trangThai', $request->trangThai);
        }

        // Filter: loại liên hệ
        if ($request->filled('loaiLienHe')) {
            $query->where('loaiLienHe', $request->loaiLienHe);
        }

        // Filter: người phụ trách
        if ($request->filled('nguoiPhuTrachId')) {
            $query->where('nguoiPhuTrachId', $request->nguoiPhuTrachId);
        }

        // Sorting
        $orderBy = $request->get('orderBy', 'lienHeId');
        $dir     = $request->get('dir', 'desc');
        $allowedSort = ['lienHeId', 'hoTen', 'email', 'created_at', 'trangThai', 'loaiLienHe'];
        if (in_array(strtolower($orderBy), $allowedSort) && in_array($dir, ['asc', 'desc'])) {
            $query->orderBy($orderBy, $dir);
        }

        $lienHes = $query->paginate(15)->withQueryString();

        // ── Stats ──
        $tongSo     = LienHe::count();
        $chuaXuLy   = LienHe::where('trangThai', 0)->count();
        $dangXuLy   = LienHe::where('trangThai', 1)->count();
        $daXuLy     = LienHe::where('trangThai', 2)->count();
        $daTuChoi   = LienHe::where('trangThai', 3)->count();
        $tongXoa    = LienHe::onlyTrashed()->count();

        // Stats theo loại
        $statLoai = LienHe::selectRaw('loaiLienHe, count(*) as so_luong')
                          ->groupBy('loaiLienHe')
                          ->pluck('so_luong', 'loaiLienHe')
                          ->toArray();

        // Danh sách nhân viên/admin (cho filter người phụ trách)
        $nhanVienList = TaiKhoan::with('hoSoNguoiDung')
                            ->select('taikhoan.taiKhoanId', 'taikhoan.taiKhoan')
                            ->whereIn('role', [1, 2, 3]) // giáo viên, nhân viên, admin
                            ->orderBy('taikhoan.taiKhoan')
                            ->get();

        return view('admin.lien-he.index', compact(
            'lienHes', 'tongSo', 'chuaXuLy', 'dangXuLy', 'daXuLy', 'daTuChoi', 'tongXoa',
            'statLoai', 'nhanVienList'
        ));
    }

    // ─── show ─────────────────────────────────────────────────────────────────

    public function show(string $id)
    {
        $lienHe = LienHe::with([
            'lichSu',
            'phanHoi',
            'nguoiPhuTrach',
        ])->findOrFail($id);

        // Danh sách nhân viên để gán phụ trách
        $nhanVienList = TaiKhoan::with('hoSoNguoiDung')
                            ->select('taikhoan.taiKhoanId', 'taikhoan.taiKhoan')
                            ->whereIn('role', [1, 2, 3])
                            ->orderBy('taikhoan.taiKhoan')
                            ->get();

        return view('admin.lien-he.show', compact('lienHe', 'nhanVienList'));
    }

    // ─── update ───────────────────────────────────────────────────────────────

    public function update(Request $request, string $id)
    {
        $request->validate([
            'trangThai'   => 'required|in:0,1,2,3',
            'loaiLienHe'  => 'nullable|in:tu_van,ho_tro,khieu_nai,khac',
            'ghiChuNoiBo' => 'nullable|string|max:5000',
        ]);

        $lienHe = LienHe::findOrFail($id);
        $changes = [];

        // Trạng thái
        if ((int)$request->trangThai !== (int)$lienHe->trangThai) {
            $oldLabel = LienHe::TRANG_THAI_LABELS[$lienHe->trangThai] ?? $lienHe->trangThai;
            $newLabel = LienHe::TRANG_THAI_LABELS[$request->trangThai] ?? $request->trangThai;

            $this->ghiLichSu(
                $lienHe->lienHeId,
                'cap_nhat_trang_thai',
                "Chuyển từ \"{$oldLabel}\" sang \"{$newLabel}\"",
                $oldLabel,
                $newLabel
            );

            // Ghi thời gian xử lý khi hoàn tất
            if ($request->trangThai == 2) {
                $lienHe->thoiGianXuLy = now();
            }
            $changes[] = 'trangThai';
        }

        // Loại liên hệ
        if ($request->filled('loaiLienHe') && $request->loaiLienHe !== $lienHe->loaiLienHe) {
            $oldLabel = LienHe::LOAI_LABELS[$lienHe->loaiLienHe] ?? $lienHe->loaiLienHe;
            $newLabel = LienHe::LOAI_LABELS[$request->loaiLienHe] ?? $request->loaiLienHe;

            $this->ghiLichSu(
                $lienHe->lienHeId,
                'cap_nhat_loai',
                "Đổi loại từ \"{$oldLabel}\" sang \"{$newLabel}\"",
                $oldLabel,
                $newLabel
            );
            $changes[] = 'loaiLienHe';
        }

        // Ghi chú nội bộ
        if ($request->filled('ghiChuNoiBo') && $request->ghiChuNoiBo !== $lienHe->ghiChuNoiBo) {
            $this->ghiLichSu($lienHe->lienHeId, 'ghi_chu', 'Cập nhật ghi chú nội bộ');
            $changes[] = 'ghiChuNoiBo';
        }

        $lienHe->fill($request->only(['trangThai', 'loaiLienHe', 'ghiChuNoiBo']));
        $lienHe->save();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Đã cập nhật thành công.']);
        }

        return redirect()->route('admin.lien-he.show', $id)
                         ->with('success', 'Cập nhật liên hệ thành công.');
    }

    // ─── assign (gán người phụ trách) ─────────────────────────────────────────

    public function assign(Request $request, string $id)
    {
        $request->validate([
            'nguoiPhuTrachId' => 'nullable|exists:taikhoan,taiKhoanId',
        ]);

        $lienHe = LienHe::findOrFail($id);

        $oldPhuTrach = $lienHe->nguoiPhuTrach?->hoSoNguoiDung?->hoTen ?? 'Chưa gán';
        $newUser     = $request->nguoiPhuTrachId
            ? TaiKhoan::find($request->nguoiPhuTrachId)
            : null;
        $newPhuTrach = $newUser?->hoSoNguoiDung?->hoTen ?? 'Bỏ gán';

        $lienHe->nguoiPhuTrachId = $request->nguoiPhuTrachId;
        $lienHe->save();

        $this->ghiLichSu(
            $lienHe->lienHeId,
            'gan_phu_trach',
            "Gán phụ trách từ \"{$oldPhuTrach}\" sang \"{$newPhuTrach}\"",
            $oldPhuTrach,
            $newPhuTrach
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success'   => true,
                'message'   => $newUser ? "Đã gán phụ trách: {$newPhuTrach}" : 'Đã bỏ gán phụ trách.',
                'tenPhuTrach' => $newPhuTrach,
            ]);
        }

        return redirect()->route('admin.lien-he.show', $id)
                         ->with('success', 'Đã cập nhật người phụ trách.');
    }

    // ─── storeReply (phản hồi nội bộ) ─────────────────────────────────────────

    public function storeReply(Request $request, string $id)
    {
        $request->validate([
            'noiDung' => 'required|string|max:5000',
            'loai'    => 'nullable|in:noi_bo,email',
        ]);

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
        $this->ghiLichSu(
            $lienHe->lienHeId,
            $hanhDong,
            \Illuminate\Support\Str::limit($request->noiDung, 100)
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success'  => true,
                'phanHoi'  => [
                    'id'          => $phanHoi->phanHoiId,
                    'noiDung'     => $phanHoi->noiDung,
                    'loai'        => $phanHoi->loai,
                    'tenNguoiGui' => $phanHoi->tenNguoiGui,
                    'thoiGian'    => $phanHoi->created_at->format('d/m/Y H:i'),
                    'thoiGianRelative' => $phanHoi->created_at->diffForHumans(),
                ],
            ]);
        }

        return redirect()->route('admin.lien-he.show', $id)
                         ->with('success', 'Đã thêm phản hồi nội bộ.');
    }

    // ─── destroy ──────────────────────────────────────────────────────────────

    public function destroy(string $id)
    {
        $lienHe = LienHe::findOrFail($id);
        $this->ghiLichSu($lienHe->lienHeId, 'xoa_mem', 'Chuyển vào thùng rác');
        $lienHe->delete();

        return redirect()->back()->with('success', 'Đã chuyển liên hệ vào thùng rác.');
    }

    // ─── trash ────────────────────────────────────────────────────────────────

    public function trash(Request $request)
    {
        $query = LienHe::onlyTrashed();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('hoTen', 'LIKE', "%{$q}%")
                    ->orWhere('email', 'LIKE', "%{$q}%")
                    ->orWhere('soDienThoai', 'LIKE', "%{$q}%")
                    ->orWhere('tieuDe', 'LIKE', "%{$q}%");
            });
        }

        $query->orderBy('deleted_at', 'desc');
        $lienHes = $query->paginate(15)->withQueryString();
        $tongXoa = LienHe::onlyTrashed()->count();

        return view('admin.lien-he.trash', compact('lienHes', 'tongXoa'));
    }

    // ─── bulkDestroy ──────────────────────────────────────────────────────────

    public function bulkDestroy(Request $request)
    {
        $ids = array_filter(explode(',', $request->input('ids', '')));
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Chưa chọn liên hệ nào để xóa.');
        }

        LienHe::whereIn('lienHeId', $ids)->each(function ($lh) {
            $this->ghiLichSu($lh->lienHeId, 'xoa_mem', 'Xóa hàng loạt');
            $lh->delete();
        });

        return redirect()->back()->with('success', 'Đã chuyển ' . count($ids) . ' liên hệ vào thùng rác.');
    }

    // ─── bulkUpdateStatus ─────────────────────────────────────────────────────

    public function bulkUpdateStatus(Request $request)
    {
        $request->validate(['trangThai' => 'required|in:0,1,2,3']);

        $ids = array_filter(explode(',', $request->input('ids', '')));
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Chưa chọn liên hệ nào.');
        }

        $newLabel = LienHe::TRANG_THAI_LABELS[$request->trangThai] ?? $request->trangThai;

        LienHe::whereIn('lienHeId', $ids)->each(function ($lh) use ($request, $newLabel) {
            $oldLabel = LienHe::TRANG_THAI_LABELS[$lh->trangThai] ?? $lh->trangThai;
            $this->ghiLichSu(
                $lh->lienHeId,
                'cap_nhat_trang_thai',
                "Cập nhật hàng loạt: \"{$oldLabel}\" → \"{$newLabel}\"",
                $oldLabel,
                $newLabel
            );
        });

        LienHe::whereIn('lienHeId', $ids)->update(['trangThai' => $request->trangThai]);

        return redirect()->back()->with('success', "Đã chuyển " . count($ids) . " liên hệ sang \"{$newLabel}\".");
    }

    // ─── restore ──────────────────────────────────────────────────────────────

    public function restore(string $id)
    {
        $lienHe = LienHe::onlyTrashed()->findOrFail($id);
        $lienHe->restore();
        $this->ghiLichSu($lienHe->lienHeId, 'khoi_phuc', 'Khôi phục từ thùng rác');

        return redirect()->back()->with('success', 'Đã khôi phục liên hệ thành công.');
    }
}
