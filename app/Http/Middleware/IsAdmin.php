<?php

namespace App\Http\Middleware;

use App\Contracts\Auth\LoginServiceInterface;
use App\Models\Auth\TaiKhoan;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function __construct(
        private readonly LoginServiceInterface $loginService
    ) {
    }

    /**
     * Chỉ cho phép nhân sự (giáo viên, nhân viên, admin) truy cập khu vực /admin.
     * role: 0 = học viên, 1 = giáo viên, 2 = nhân viên, 3 = admin
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('staff.login');
        }

        $user = $request->user();

        if (!$user instanceof TaiKhoan) {
            return redirect()->route('staff.login');
        }

        if (!$user->isStaff()) {
            $message = 'Phiên đăng nhập nội bộ không còn hợp lệ vì trình duyệt hiện đang dùng cổng học viên ở tab khác.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'reason' => 'portal_mismatch',
                    'redirect_url' => route($this->loginService->landingRouteForUser($user)),
                ], 403);
            }

            return redirect()
                ->route($this->loginService->landingRouteForUser($user))
                ->with('warning', $message);
        }

        return $next($request);
    }
}
