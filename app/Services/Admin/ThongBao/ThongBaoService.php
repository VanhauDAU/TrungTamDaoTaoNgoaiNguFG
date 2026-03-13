<?php

namespace App\Services\Admin\ThongBao;

use App\Models\Interaction\ThongBao;
use App\Models\Interaction\ThongBaoNguoiDung;
use App\Models\Auth\TaiKhoan;
use App\Models\Education\LopHoc;
use App\Models\Education\DangKyLopHoc;
use App\Models\Course\KhoaHoc;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Contracts\Admin\ThongBao\ThongBaoServiceInterface;

class ThongBaoService implements ThongBaoServiceInterface
{
    /**
     * Gửi thông báo đi: tạo records trong thongbaonguoidung
     * theo đối tượng được chỉ định trong ThongBao.
     */
    public function guiThongBao(ThongBao $tb): int
    {
        $nguoiNhanIds = $this->layDanhSachNguoiNhan(
            $tb->doiTuongGui,
            $tb->doiTuongId,
            $tb->nguoiGuiId
        );

        if ($nguoiNhanIds->isEmpty()) {
            return 0;
        }

        // Bulk insert để hiệu quả hơn
        $now = Carbon::now();
        $records = $nguoiNhanIds->map(fn($id) => [
        'thongBaoId' => $tb->thongBaoId,
        'taiKhoanId' => $id,
        'daDoc' => false,
        'ngayDoc' => null,
        'created_at' => $now,
        'updated_at' => $now,
        ])->toArray();

        // Chunk để tránh quá giới hạn SQL
        foreach (array_chunk($records, 500) as $chunk) {
            DB::table('thongbaonguoidung')->insertOrIgnore($chunk);
        }

        return count($records);
    }

    /**
     * Preview danh sách người nhận (dùng cho AJAX preview trước khi gửi)
     */
    public function previewNguoiNhan(int $doiTuongGui, ?int $doiTuongId, ?int $nguoiGuiId = null): Collection
    {
        return $this->layDanhSachNguoiNhan($doiTuongGui, $doiTuongId, $nguoiGuiId)
            ->map(function ($id) {
            $user = TaiKhoan::with('hoSoNguoiDung', 'nhanSu')
                ->find($id);
            if (!$user)
                return null;
            $hoTen = $user->hoSoNguoiDung->hoTen
                ?? $user->nhanSu->hoTen
                ?? $user->taiKhoan;
            return [
                'taiKhoanId' => $user->taiKhoanId,
                'taiKhoan' => $user->taiKhoan,
                'hoTen' => $hoTen,
                'email' => $user->email,
                'role' => $user->getRoleLabel(),
            ];
        })
            ->filter()
            ->values();
    }

    /**
     * Lấy số thông báo chưa đọc của một tài khoản
     */
    public function getUnreadCount(int $taiKhoanId): int
    {
        return ThongBaoNguoiDung::where('taiKhoanId', $taiKhoanId)
            ->where('daDoc', false)
            ->whereHas('thongBao', fn($q) => $q->whereNull('deleted_at'))
            ->count();
    }

    /**
     * Lấy danh sách thông báo gần đây (cho dropdown bell)
     */
    public function getRecentNotifications(int $taiKhoanId, int $limit = 8): Collection
    {
        return ThongBaoNguoiDung::with(['thongBao.nguoiGui.hoSoNguoiDung', 'thongBao.nguoiGui.nhanSu'])
            ->where('taiKhoanId', $taiKhoanId)
            ->whereHas('thongBao', fn($q) => $q->whereNull('deleted_at'))
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
            $tb = $item->thongBao;
            if (!$tb)
                return null;
            $nguoiGui = $tb->nguoiGui;
            $tenNguoiGui = $nguoiGui
                ? ($nguoiGui->hoSoNguoiDung->hoTen ?? $nguoiGui->nhanSu->hoTen ?? $nguoiGui->taiKhoan)
                : 'Hệ thống';
            return [
                'thongBaoNguoiDungId' => $item->thongBaoNguoiDungId,
                'thongBaoId' => $tb->thongBaoId,
                'tieuDe' => $tb->tieuDe,
                'tomTat' => mb_substr(strip_tags($tb->noiDung), 0, 80) . '...',
                'nguoiGui' => $tenNguoiGui,
                'loaiGui' => $tb->loaiGui,
                'uuTien' => $tb->uuTien,
                'daDoc' => $item->daDoc,
                'ngayGui' => $tb->ngayGui ?? $tb->created_at,
                'badgeClass' => $tb->getLoaiBadgeClass(),
            ];
        })
            ->filter()
            ->values();
    }

    /**
     * Đánh dấu tất cả thông báo của user là đã đọc
     */
    public function markAllRead(int $taiKhoanId): int
    {
        return ThongBaoNguoiDung::where('taiKhoanId', $taiKhoanId)
            ->where('daDoc', false)
            ->whereHas('thongBao', fn($q) => $q->whereNull('deleted_at'))
            ->update([
            'daDoc' => true,
            'ngayDoc' => Carbon::now(),
        ]);
    }

    /**
     * Đánh dấu 1 thông báo đã đọc
     */
    public function markAsRead(int $thongBaoId, int $taiKhoanId): bool
    {
        $record = ThongBaoNguoiDung::where('thongBaoId', $thongBaoId)
            ->where('taiKhoanId', $taiKhoanId)
            ->whereHas('thongBao', fn($q) => $q->whereNull('deleted_at'))
            ->first();

        if (!$record || $record->daDoc)
            return false;

        $record->update([
            'daDoc' => true,
            'ngayDoc' => Carbon::now(),
        ]);
        return true;
    }

    /**
     * Đánh dấu 1 thông báo là chưa đọc
     */
    public function markAsUnread(int $thongBaoId, int $taiKhoanId): bool
    {
        $record = ThongBaoNguoiDung::where('thongBaoId', $thongBaoId)
            ->where('taiKhoanId', $taiKhoanId)
            ->whereHas('thongBao', fn($q) => $q->whereNull('deleted_at'))
            ->first();

        if (!$record || !$record->daDoc)
            return false;

        $record->update([
            'daDoc' => false,
            'ngayDoc' => null,
        ]);

        return true;
    }

    // ── Private helpers ────────────────────────────────────

    private function layDanhSachNguoiNhan(int $doiTuongGui, ?int $doiTuongId, ?int $loaiTruId = null): Collection
    {
        switch ($doiTuongGui) {
            case ThongBao::DOI_TUONG_TAT_CA:
                // Tất cả user có trangThai = 1, trừ người gửi
                $query = TaiKhoan::where('trangThai', 1);
                if ($loaiTruId) {
                    $query->where('taiKhoanId', '!=', $loaiTruId);
                }
                return $query->pluck('taiKhoanId');

            case ThongBao::DOI_TUONG_THEO_LOP:
                // Học viên đã đăng ký lớp học đó
                if (!$doiTuongId)
                    return collect();
                return DangKyLopHoc::where('lopHocId', $doiTuongId)
                    ->whereIn('trangThai', [
                    DangKyLopHoc::TRANG_THAI_DA_XAC_NHAN,
                    DangKyLopHoc::TRANG_THAI_DANG_HOC,
                    DangKyLopHoc::TRANG_THAI_TAM_DUNG_NO_HOC_PHI,
                    DangKyLopHoc::TRANG_THAI_BAO_LUU,
                ])
                    ->pluck('taiKhoanId')
                    ->unique();

            case ThongBao::DOI_TUONG_THEO_KHOA:
                // Học viên đăng ký bất kỳ lớp nào thuộc khóa học đó
                if (!$doiTuongId)
                    return collect();
                $lopHocIds = LopHoc::where('khoaHocId', $doiTuongId)->pluck('lopHocId');
                return DangKyLopHoc::whereIn('lopHocId', $lopHocIds)
                    ->whereIn('trangThai', [
                    DangKyLopHoc::TRANG_THAI_DA_XAC_NHAN,
                    DangKyLopHoc::TRANG_THAI_DANG_HOC,
                    DangKyLopHoc::TRANG_THAI_TAM_DUNG_NO_HOC_PHI,
                    DangKyLopHoc::TRANG_THAI_BAO_LUU,
                ])
                    ->pluck('taiKhoanId')
                    ->unique();

            case ThongBao::DOI_TUONG_CA_NHAN:
                // Chỉ 1 người
                if (!$doiTuongId)
                    return collect();
                return collect([$doiTuongId]);

            case ThongBao::DOI_TUONG_THEO_ROLE:
                // Theo role: doiTuongId = role value (0,1,2,3)
                if ($doiTuongId === null)
                    return collect();
                $query = TaiKhoan::where('trangThai', 1)
                    ->where('role', $doiTuongId);
                if ($loaiTruId) {
                    $query->where('taiKhoanId', '!=', $loaiTruId);
                }
                return $query->pluck('taiKhoanId');

            case ThongBao::DOI_TUONG_THEO_CO_SO:
                // Theo cơ sở: lấy giáo viên + nhân viên đang làm việc tại cơ sở
                if ($doiTuongId === null)
                    return collect();
                $query = TaiKhoan::where('trangThai', 1)
                    ->whereIn('role', [TaiKhoan::ROLE_GIAO_VIEN, TaiKhoan::ROLE_NHAN_VIEN])
                    ->whereHas('nhanSu', fn($q) => $q->where('coSoId', $doiTuongId));
                if ($loaiTruId) {
                    $query->where('taiKhoanId', '!=', $loaiTruId);
                }
                return $query->pluck('taiKhoanId');

            default:
                return collect();
        }
    }
}