<?php

namespace App\Contracts\Admin\HocVien;

use App\Models\Auth\TaiKhoan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

interface HocVienServiceInterface
{
    /**
     * Lấy danh sách học viên với filter, search, phân trang.
     */
    public function getList(Request $request): array;

    /**
     * Build query danh sách học viên (dùng cho cả index và export).
     */
    public function buildIndexQuery(Request $request): Builder;

    /**
     * Lấy danh sách học viên đã xóa mềm.
     */
    public function getTrashList(Request $request): array;

    /**
     * Tạo học viên mới (TaiKhoan + HoSoNguoiDung) trong transaction.
     */
    public function store(Request $request): TaiKhoan;

    /**
     * Lấy học viên theo tên đăng nhập.
     */
    public function findByUsername(string $taiKhoan): TaiKhoan;

    /**
     * Cập nhật thông tin học viên.
     */
    public function update(Request $request, TaiKhoan $hocVien): void;

    /**
     * Cập nhật ảnh đại diện học viên (admin).
     * Trả về URL ảnh mới sau khi upload.
     */
    public function updateAvatar(Request $request, TaiKhoan $hocVien): string;

    /**
     * Xóa mềm học viên.
     */
    public function destroy(string $taiKhoan): string;

    /**
     * Khôi phục học viên đã xóa mềm.
     */
    public function restore(string $taiKhoan): string;
}