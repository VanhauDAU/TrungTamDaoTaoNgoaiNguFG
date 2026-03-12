<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auth\HoSoNguoiDung;
use App\Models\Auth\NhatKyDangNhap;
use App\Models\Auth\TaiKhoan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleLoginController extends Controller
{
    public function redirect(Request $request)
    {
        if (!$this->configured()) {
            return redirect()->route('login')
                ->withErrors(['google' => 'Google đăng nhập chưa được cấu hình trên hệ thống.']);
        }

        $state = Str::random(40);
        $request->session()->put('google_oauth_state', $state);

        $query = http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => route('auth.google.callback'),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ]);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    }

    public function callback(Request $request)
    {
        if (!$this->configured()) {
            return redirect()->route('login')
                ->withErrors(['google' => 'Google đăng nhập chưa được cấu hình trên hệ thống.']);
        }

        if ($request->filled('error')) {
            return redirect()->route('login')
                ->withErrors(['google' => 'Đăng nhập Google đã bị hủy hoặc không thành công.']);
        }

        if (!$request->filled('code') || $request->input('state') !== $request->session()->pull('google_oauth_state')) {
            return redirect()->route('login')
                ->withErrors(['google' => 'Phiên đăng nhập Google không hợp lệ. Vui lòng thử lại.']);
        }

        $tokenResponse = Http::asForm()
            ->timeout(15)
            ->post('https://oauth2.googleapis.com/token', [
                'code' => $request->input('code'),
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => route('auth.google.callback'),
                'grant_type' => 'authorization_code',
            ]);

        if (!$tokenResponse->successful()) {
            return redirect()->route('login')
                ->withErrors(['google' => 'Không thể xác thực với Google. Vui lòng thử lại.']);
        }

        $accessToken = $tokenResponse->json('access_token');

        $profileResponse = Http::withToken($accessToken)
            ->timeout(15)
            ->get('https://www.googleapis.com/oauth2/v3/userinfo');

        if (!$profileResponse->successful()) {
            return redirect()->route('login')
                ->withErrors(['google' => 'Không lấy được thông tin tài khoản Google.']);
        }

        $googleUser = $profileResponse->json();

        if (!($googleUser['email_verified'] ?? false) || empty($googleUser['email'])) {
            return redirect()->route('login')
                ->withErrors(['google' => 'Tài khoản Google chưa có email xác thực hợp lệ.']);
        }

        $existing = TaiKhoan::query()
            ->where(function ($query) use ($googleUser) {
                if (!empty($googleUser['sub'])) {
                    $query->where('google_id', $googleUser['sub'])
                        ->orWhere('email', $googleUser['email']);
                    return;
                }

                $query->where('email', $googleUser['email']);
            })
            ->first();

        if ($existing && $existing->isStaff()) {
            return redirect()->route('login')
                ->withErrors(['google' => 'Đăng nhập Google chỉ áp dụng cho tài khoản học viên.']);
        }

        $taiKhoan = DB::transaction(function () use ($googleUser, $existing) {
            if ($existing instanceof TaiKhoan) {
                $existing->loadMissing('hoSoNguoiDung');

                $existing->forceFill([
                    'google_id' => $googleUser['sub'] ?? $existing->google_id,
                    'google_avatar' => $googleUser['picture'] ?? $existing->google_avatar,
                    'email_verified_at' => $existing->email_verified_at ?? now(),
                ])->save();

                $existing->hoSoNguoiDung()->updateOrCreate(
                    ['taiKhoanId' => $existing->taiKhoanId],
                    ['hoTen' => $existing->hoSoNguoiDung->hoTen ?? ($googleUser['name'] ?? $googleUser['email'])]
                );

                return $existing;
            }

            $taiKhoan = TaiKhoan::create([
                'taiKhoan' => TaiKhoan::generateTemporaryUsername(TaiKhoan::ROLE_HOC_VIEN),
                'email' => $googleUser['email'],
                'matKhau' => Hash::make(Str::random(32)),
                'role' => TaiKhoan::ROLE_HOC_VIEN,
                'trangThai' => 1,
                'phaiDoiMatKhau' => 0,
                'auth_provider' => 'google',
                'google_id' => $googleUser['sub'] ?? null,
                'google_avatar' => $googleUser['picture'] ?? null,
                'email_verified_at' => now(),
            ]);

            $taiKhoan->assignSystemUsername();

            HoSoNguoiDung::create([
                'taiKhoanId' => $taiKhoan->taiKhoanId,
                'hoTen' => $googleUser['name'] ?? $googleUser['email'],
                'anhDaiDien' => $googleUser['picture'] ?? null,
            ]);

            return $taiKhoan;
        });

        Auth::login($taiKhoan, true);
        $taiKhoan->forceFill(['lastLogin' => now()])->save();
        NhatKyDangNhap::ghiLog($taiKhoan->email, $request->ip(), true, $request->userAgent());

        if ($taiKhoan->phaiDoiMatKhau == 1) {
            return redirect()->route('force-change-password')
                ->with('success', 'Đã liên kết Google thành công. Vui lòng đặt mật khẩu mới để hoàn tất.');
        }

        return redirect()->route('home.student.index')
            ->with('success', 'Đăng nhập Google thành công.');
    }

    private function configured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));
    }
}
