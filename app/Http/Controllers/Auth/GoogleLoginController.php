<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\Auth\GoogleAuthServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Auth\NhatKyDangNhap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class GoogleLoginController extends Controller
{
    public function __construct(
        protected GoogleAuthServiceInterface $googleAuthService
    ) {}

    public function redirect(Request $request)
    {
        if (!$this->googleAuthService->isConfigured()) {
            return redirect()->route('login')
                ->withErrors(['google' => 'Google đăng nhập chưa được cấu hình trên hệ thống.']);
        }

        $url = $this->googleAuthService->getRedirectUrl($request);

        return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        if (!$this->googleAuthService->isConfigured()) {
            return redirect()->route('login')
                ->withErrors(['google' => 'Google đăng nhập chưa được cấu hình trên hệ thống.']);
        }

        if ($request->filled('error')) {
            return redirect()->route('login')
                ->withErrors(['google' => 'Đăng nhập Google đã bị hủy hoặc không thành công.']);
        }

        try {
            $taiKhoan = $this->googleAuthService->handleCallback($request);
        } catch (RuntimeException $e) {
            return redirect()->route('login')
                ->withErrors(['google' => $e->getMessage()]);
        }

        Auth::login($taiKhoan, true);
        $request->session()->regenerate();
        $request->session()->put([
            'auth_portal'       => 'student',
            'auth_login_method' => 'google',
            'auth_remembered'   => true,
        ]);

        $taiKhoan->forceFill(['lastLogin' => now()])->save();
        NhatKyDangNhap::ghiLog($taiKhoan->email, $request->ip(), true, $request->userAgent());

        if ($taiKhoan->phaiDoiMatKhau == 1) {
            return redirect()->route('force-change-password')
                ->with('success', 'Đã liên kết Google thành công. Vui lòng đặt mật khẩu mới để hoàn tất.');
        }

        return redirect()->route('home.student.index')
            ->with('success', 'Đăng nhập Google thành công.');
    }
}
