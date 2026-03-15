<?php

namespace App\Http\Middleware;

use App\Models\Auth\TaiKhoan;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user instanceof TaiKhoan) {
            return $next($request);
        }

        if ((int) $user->role !== TaiKhoan::ROLE_HOC_VIEN) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Chỉ học viên mới được truy cập khu vực này.',
                ], 403);
            }

            abort(403, 'Chỉ học viên mới được truy cập khu vực này.');
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
