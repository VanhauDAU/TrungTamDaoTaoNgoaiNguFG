<?php

namespace App\Http\Middleware;

use App\Models\Auth\TaiKhoan;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user instanceof TaiKhoan || (int) $user->trangThai === 1) {
            return $next($request);
        }

        Auth::guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Tài khoản của bạn đã bị khóa.',
            ], 403);
        }

        return redirect()->route($this->redirectRouteFor($user))
            ->withErrors([
                'taiKhoan' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ trung tâm để được hỗ trợ.',
            ]);
    }

    private function redirectRouteFor(TaiKhoan $user): string
    {
        return match ((int) $user->role) {
            TaiKhoan::ROLE_GIAO_VIEN => 'teacher.login',
            TaiKhoan::ROLE_NHAN_VIEN,
            TaiKhoan::ROLE_ADMIN => 'staff.login',
            default => 'login',
        };
    }
}
