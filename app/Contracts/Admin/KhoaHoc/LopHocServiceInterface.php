<?php

namespace App\Contracts\Admin\KhoaHoc;

use App\Models\Education\LopHoc;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

interface LopHocServiceInterface
{
    /**
     * Lấy danh sách lớp học có lọc, tìm kiếm, phân trang.
     */
    public function getList(Request $request): array;

    /**
     * Lấy danh sách lớp đã xóa mềm (trash).
     */
    public function getTrashList(Request $request): array;

    /**
     * Lấy dữ liệu cho form tạo mới lớp học.
     */
    public function getCreateFormData(Request $request): array;

    /**
     * Lấy dữ liệu chi tiết một lớp học theo slug.
     */
    public function getDetail(string $slug): array;

    /**
     * Lấy dữ liệu cho form chỉnh sửa lớp học.
     */
    public function getEditFormData(string $slug): array;

    /**
     * Validate và tạo mới lớp học, sinh slug + mã lớp.
     */
    public function store(Request $request): LopHoc;

    /**
     * Validate và cập nhật thông tin lớp học.
     */
    public function update(Request $request, string $slug): LopHoc;

    /**
     * Cập nhật nhanh trạng thái lớp học bằng AJAX.
     */
    public function updateStatus(string $slug, int $trangThai): LopHoc;

    /**
     * Xóa mềm lớp học (soft delete).
     */
    public function destroy(string $slug): string;

    /**
     * Khôi phục lớp học đã xóa mềm.
     */
    public function restore(string $slug): LopHoc;

    /**
     * Đồng bộ trạng thái đăng ký học viên theo trạng thái lớp.
     */
    public function syncRegistrationStatuses(LopHoc $lopHoc): void;

    /**
     * API: Lấy phòng học theo cơ sở.
     */
    public function getPhongByCoso(int $coSoId): Collection;

    /**
     * API: Lấy giáo viên theo cơ sở (phân nhóm cùng cơ sở / khác cơ sở).
     */
    public function getGiaoVienByCoso(int $coSoId): array;
}
