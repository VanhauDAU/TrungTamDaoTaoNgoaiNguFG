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

        if ($user->role !== TaiKhoan::ROLE_HOC_VIEN || $user->hasVerifiedEmail()) {
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
