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

    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login');
        }

        $user = $request->user();

        if (!$user instanceof TaiKhoan) {
            return redirect()->route('admin.login');
        }

        $actualPortal = $this->loginService->activePortalForUser($request, $user);

        if (!$this->loginService->matchesPortal($user, 'admin') || $actualPortal !== 'admin') {
            $message = 'Phiên đăng nhập quản trị không còn hợp lệ vì trình duyệt hiện đang dùng cổng khác ở tab khác.';

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
