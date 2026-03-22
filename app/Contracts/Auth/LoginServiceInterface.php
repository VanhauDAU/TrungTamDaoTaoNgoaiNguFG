<?php

namespace App\Contracts\Auth;

use App\Models\Auth\TaiKhoan;
use Illuminate\Http\Request;

interface LoginServiceInterface
{
    /**
     * Trả về dữ liệu view cho trang đăng nhập theo portal.
     */
    public function getLoginViewData(string $portal): array;

    /**
     * Kiểm tra lockout và thực hiện xác thực đăng nhập.
     * Trả về true nếu thành công, throws ValidationException nếu thất bại.
     */
    public function attemptLogin(Request $request, string $portal): bool;

    /**
     * Xử lý sau khi đăng nhập thành công: ghi log, session, redirect.
     */
    public function handleAuthenticated(Request $request, TaiKhoan $user, string $portal): \Illuminate\Http\RedirectResponse;

    /**
     * Xử lý khi đăng nhập thất bại: ghi log, tính lockout.
     */
    public function handleFailedLogin(Request $request, string $portal): void;

    /**
     * Hiển thị màn hình khóa tài khoản và ghi session lockout.
     */
    public function lockoutResponse(int $remainingSeconds, string $message): never;

    /**
     * Đăng xuất và huỷ session hiện tại.
     */
    public function logout(Request $request): string;

    /**
     * Xử lý đổi mật khẩu bắt buộc.
     */
    public function processForceChangePassword(Request $request, TaiKhoan $user): \Illuminate\Http\RedirectResponse;

    /**
     * Kiểm tra user có thuộc đúng portal không.
     */
    public function matchesPortal(TaiKhoan $user, string $portal): bool;

    /**
     * Lấy route dashboard phù hợp cho staff.
     */
    public function staffDashboardRouteFor(TaiKhoan $user): string;

    /**
     * Lấy route redirect sau khi logout theo role.
     */
    public function logoutRedirectRouteFor(TaiKhoan $user): string;

    /**
     * Lấy route landing phù hợp với portal hiện tại của user.
     */
    public function landingRouteForUser(TaiKhoan $user): string;

    /**
     * Trả về trạng thái phiên hiện tại cho portal guard trên giao diện.
     */
    public function getSessionStatus(Request $request, string $expectedContext): array;
}
