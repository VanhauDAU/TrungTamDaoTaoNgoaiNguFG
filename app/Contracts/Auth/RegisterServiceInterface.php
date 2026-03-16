<?php

namespace App\Contracts\Auth;

use App\Models\Auth\TaiKhoan;
use Illuminate\Http\Request;

interface RegisterServiceInterface
{
    /**
     * Trả về dữ liệu view cho trang đăng ký.
     */
    public function getRegisterViewData(): array;

    /**
     * Validate dữ liệu đăng ký, ném ValidationException nếu không hợp lệ.
     */
    public function validate(array $data): void;

    /**
     * Tạo tài khoản học viên mới (TaiKhoan + HoSoNguoiDung) trong transaction.
     */
    public function create(array $data): TaiKhoan;

    /**
     * Kiểm tra email có thể dùng để đăng ký hay không.
     *
     * @return array{status:string,message:string}
     */
    public function checkEmailAvailability(?string $email): array;

    /**
     * Xử lý luồng đăng ký hoàn chỉnh: validate → create → login → redirect.
     */
    public function register(Request $request): \Illuminate\Http\RedirectResponse;
}
