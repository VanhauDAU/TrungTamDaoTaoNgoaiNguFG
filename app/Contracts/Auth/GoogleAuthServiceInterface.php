<?php

namespace App\Contracts\Auth;

use App\Models\Auth\TaiKhoan;
use Illuminate\Http\Request;

interface GoogleAuthServiceInterface
{
    /**
     * Kiểm tra Google OAuth đã được cấu hình chưa.
     */
    public function isConfigured(): bool;

    /**
     * Build URL redirect đến Google OAuth, lưu state vào session.
     * Trả về URL redirect string.
     */
    public function getRedirectUrl(Request $request): string;

    /**
     * Xử lý callback từ Google:
     *  - Validate state
     *  - Exchange code lấy access token
     *  - Lấy thông tin user từ Google
     *  - Tạo hoặc cập nhật TaiKhoan
     * Trả về TaiKhoan.
     */
    public function handleCallback(Request $request): TaiKhoan;

    /**
     * Chuẩn hoá dữ liệu user trả về từ Google response.
     *
     * @param  mixed $payload
     * @return array{email:string, name:string, picture:?string, sub:?string, email_verified:bool}
     */
    public function normalizeGoogleUser(mixed $payload): array;

    /**
     * Tìm hoặc tạo mới TaiKhoan từ thông tin Google User.
     *
     * @param  array{email:string, name:string, picture:?string, sub:?string, email_verified:bool} $googleUser
     */
    public function findOrCreateUser(array $googleUser): TaiKhoan;
}
