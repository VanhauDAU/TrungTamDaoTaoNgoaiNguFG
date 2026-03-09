<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceChangePassword
{
    /**
     * Nếu user đã đăng nhập và phaiDoiMatKhau == 1
     * → redirect bắt buộc đến trang đổi mật khẩu.
     * Bỏ qua các route: logout, force-change-password.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->phaiDoiMatKhau == 1) {
            // Cho phép các route không cần chặn
            $allowedRoutes = [
                'force-change-password',
                'force-change-password.process',
                'logout',
            ];

            if (!in_array($request->route()?->getName(), $allowedRoutes)) {
                return redirect()->route('force-change-password');
            }
        }

        return $next($request);
    }
}
