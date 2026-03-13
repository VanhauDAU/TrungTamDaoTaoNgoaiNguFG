<?php

namespace App\Contracts\Admin;

use App\Models\Course\KhoaHoc;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

interface KhoaHocServiceInterface
{
    /**
     * Lấy danh sách khóa học có lọc, tìm kiếm, phân trang.
     */
    public function getList(Request $request): array;

    /**
     * Lấy dữ liệu form tạo mới khóa học.
     */
    public function getCreateFormData(): array;

    /**
     * Lấy chi tiết một khóa học theo slug.
     */
    public function getDetail(string $slug): array;

    /**
     * Lấy dữ liệu form chỉnh sửa khóa học.
     */
    public function getEditFormData(string $slug): array;

    /**
     * Validate và tạo mới khóa học (kèm upload ảnh).
     */
    public function store(Request $request): KhoaHoc;

    /**
     * Validate và cập nhật khóa học (kèm upload/xóa ảnh).
     */
    public function update(Request $request, string $slug): KhoaHoc;

    /**
     * Xóa mềm khóa học (chỉ cho phép nếu không còn lớp hoc đang hoạt động).
     */
    public function destroy(string $slug): string;

    /**
     * Khôi phục khóa học đã xóa mềm.
     */
    public function restore(string $slug): KhoaHoc;
}
