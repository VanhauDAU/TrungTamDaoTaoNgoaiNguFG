<?php

namespace App\Contracts;

use App\Models\Interaction\ThongBao;
use Illuminate\Support\Collection;

interface ThongBaoServiceInterface
{
    /** Gửi thông báo đến người nhận theo đối tượng, trả về số người nhận */
    public function guiThongBao(ThongBao $tb): int;

    /** Preview danh sách người nhận (dùng cho AJAX preview) */
    public function previewNguoiNhan(int $doiTuongGui, ?int $doiTuongId, ?int $nguoiGuiId = null): Collection;

    /** Đếm số thông báo chưa đọc của một tài khoản */
    public function getUnreadCount(int $taiKhoanId): int;

    /** Lấy danh sách thông báo gần đây cho dropdown bell */
    public function getRecentNotifications(int $taiKhoanId, int $limit = 8): Collection;

    /** Đánh dấu tất cả đã đọc, trả về số bản ghi cập nhật */
    public function markAllRead(int $taiKhoanId): int;

    /** Đánh dấu một thông báo đã đọc */
    public function markAsRead(int $thongBaoId, int $taiKhoanId): bool;

    /** Đánh dấu một thông báo chưa đọc */
    public function markAsUnread(int $thongBaoId, int $taiKhoanId): bool;
}
