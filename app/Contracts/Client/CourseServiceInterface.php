<?php

namespace App\Contracts\Client;

use Illuminate\Http\Request;

interface CourseServiceInterface
{
    /**
     * Lấy dữ liệu trang danh sách khóa học (cây danh mục, filter, phân trang).
     */
    public function getList(Request $request): array;

    /**
     * Lấy chi tiết một khóa học theo slug.
     */
    public function getDetail(string $slug): array;

    /**
     * Lấy chi tiết một lớp học theo slugKhoaHoc + slugLopHoc.
     */
    public function getClassDetail(string $slug, string $slugLopHoc): array;

    /**
     * Hiển thị trang xác nhận đăng ký lớp học.
     * Trả về data để render view, hoặc throw/redirect nếu không hợp lệ.
     */
    public function getConfirmRegistrationData(string $slug, string $slugLopHoc): array;

    /**
     * Xử lý đăng ký lớp học (tạo DangKyLopHoc + HoaDon trong transaction).
     */
    public function processRegistration(Request $request, string $slug, string $slugLopHoc): void;
}
