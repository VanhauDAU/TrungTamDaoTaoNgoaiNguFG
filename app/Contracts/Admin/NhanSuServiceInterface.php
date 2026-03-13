<?php

namespace App\Contracts\Admin;

use App\Models\Auth\TaiKhoan;
use Illuminate\Http\Request;

interface NhanSuServiceInterface
{
    /**
     * Lấy danh sách nhân sự theo role với filter, search, phân trang.
     * $role = TaiKhoan::ROLE_GIAO_VIEN | ROLE_NHAN_VIEN
     */
    public function getList(Request $request, string $role): array;

    /**
     * Lấy danh sách nhân sự đã xóa mềm theo role.
     */
    public function getTrashList(Request $request, string $role): array;

    /**
     * Lấy dữ liệu form tạo mới (cơ sở, tỉnh thành…).
     */
    public function getCreateFormData(): array;

    /**
     * Tạo tài khoản nhân sự (TaiKhoan + HoSoNguoiDung + NhanSu) trong transaction.
     */
    public function store(Request $request, string $role): TaiKhoan;

    /**
     * Lấy thông tin chi tiết nhân sự theo tên đăng nhập và role.
     */
    public function findByUsername(string $taiKhoan, string $role): TaiKhoan;

    /**
     * Cập nhật thông tin nhân sự.
     */
    public function update(Request $request, TaiKhoan $nhanSu): void;

    /**
     * Xóa mềm nhân sự.
     */
    public function destroy(string $taiKhoan, string $role): string;

    /**
     * Khôi phục nhân sự đã xóa mềm.
     */
    public function restore(string $taiKhoan, string $role): string;
}
