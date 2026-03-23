<?php

namespace App\Http\Middleware;

use App\Contracts\Auth\LoginServiceInterface;
use App\Models\Auth\TaiKhoan;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentEmailIsVerified
{
    public function __construct(
        private readonly LoginServiceInterface $loginService
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user instanceof TaiKhoan) {
            return $next($request);
        }

        if ((int) $user->role !== TaiKhoan::ROLE_HOC_VIEN) {
            $message = 'Phiên học viên không còn hợp lệ vì trình duyệt hiện đang dùng cổng nội bộ ở tab khác.';

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

        if ($user->hasVerifiedEmail()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Vui lòng xác thực email trước khi tiếp tục.',
            ], 403);
        }

        return redirect()->route('verification.notice');
    }
}
