<?php

namespace App\Contracts\Admin\NhanVien;

use App\Data\Admin\NhanVien\CreatedStaffAccountResult;
use App\Models\Auth\TaiKhoan;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

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
    public function getCreateFormData(string $role): array;

    /**
     * Lấy dữ liệu form chỉnh sửa.
     */
    public function getEditFormData(TaiKhoan $taiKhoan, string $role): array;

    /**
     * Tạo tài khoản nhân sự (TaiKhoan + HoSoNguoiDung + NhanSu) trong transaction.
     */
    public function store(Request $request, string $role): CreatedStaffAccountResult;

    /**
     * Lấy thông tin chi tiết nhân sự theo tên đăng nhập và role.
     */
    public function findByUsername(string $taiKhoan, string $role): TaiKhoan;

    /**
     * Lấy dữ liệu hồ sơ nhân sự chi tiết.
     */
    public function getProfileData(TaiKhoan $taiKhoan, string $role, ?string $handoverToken = null): array;

    /**
     * Cập nhật thông tin nhân sự.
     */
    public function update(Request $request, TaiKhoan $nhanSu): void;

    /**
     * Upload tài liệu nhân sự.
     */
    public function uploadDocument(Request $request, TaiKhoan $taiKhoan): void;

    /**
     * Tải tài liệu nhân sự private.
     */
    public function downloadDocument(TaiKhoan $taiKhoan, int $documentId): BinaryFileResponse;

    /**
     * Lưu trữ phiên bản tài liệu cũ.
     */
    public function archiveDocument(TaiKhoan $taiKhoan, int $documentId): void;

    /**
     * Lưu gói lương hoặc thay gói lương active.
     */
    public function saveSalaryPackage(Request $request, TaiKhoan $taiKhoan): void;

    /**
     * Tải phiếu bàn giao tài khoản từ token ngắn hạn.
     */
    public function downloadHandoverPdf(TaiKhoan $taiKhoan, string $role, string $token): Response;

    /**
     * Tải hồ sơ nhân sự đã chuẩn hóa.
     */
    public function downloadProfilePdf(TaiKhoan $taiKhoan, string $role): Response;

    /**
     * Tạo nội dung file hồ sơ nhân sự cho job export nền.
     *
     * @return array{content:string,mime:string,filename:string}
     */
    public function buildProfilePdfArtifact(TaiKhoan $taiKhoan, string $role): array;

    /**
     * Xóa mềm nhân sự.
     */
    public function destroy(string $taiKhoan, string $role): string;

    /**
     * Khôi phục nhân sự đã xóa mềm.
     */
    public function restore(string $taiKhoan, string $role): string;
}
