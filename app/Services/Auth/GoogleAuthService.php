<?php

namespace App\Services\Auth;

use App\Contracts\Auth\GoogleAuthServiceInterface;
use App\Models\Auth\HoSoNguoiDung;
use App\Models\Auth\TaiKhoan;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class GoogleAuthService implements GoogleAuthServiceInterface
{
    public function isConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));
    }

    public function getRedirectUrl(Request $request): string
    {
        $state = Str::random(40);
        $request->session()->put('google_oauth_state', $state);

        $query = http_build_query([
            'client_id'     => config('services.google.client_id'),
            'redirect_uri'  => route('auth.google.callback'),
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'state'         => $state,
            'access_type'   => 'online',
            'prompt'        => 'select_account',
        ]);

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . $query;
    }

    public function handleCallback(Request $request): TaiKhoan
    {
        // Validate state
        $state = (string) $request->session()->pull('google_oauth_state', '');
        if (!$request->filled('code') || (string) $request->input('state', '') !== $state) {
            throw new RuntimeException('Phiên đăng nhập Google không hợp lệ. Vui lòng thử lại.');
        }

        // Exchange code → access token
        /** @var HttpResponse $tokenResponse */
        $tokenResponse = Http::asForm()
            ->timeout(15)
            ->post('https://oauth2.googleapis.com/token', [
                'code'          => $request->input('code'),
                'client_id'     => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri'  => route('auth.google.callback'),
                'grant_type'    => 'authorization_code',
            ]);

        if (!$tokenResponse->successful()) {
            throw new RuntimeException('Không thể xác thực với Google. Vui lòng thử lại.');
        }

        $accessToken = $tokenResponse->json('access_token');
        if (!is_string($accessToken) || $accessToken === '') {
            throw new RuntimeException('Google không trả về access token hợp lệ.');
        }

        // Fetch user profile
        /** @var HttpResponse $profileResponse */
        $profileResponse = Http::withToken($accessToken)
            ->timeout(15)
            ->get('https://www.googleapis.com/oauth2/v3/userinfo');

        if (!$profileResponse->successful()) {
            throw new RuntimeException('Không lấy được thông tin tài khoản Google.');
        }

        $googleUser = $this->normalizeGoogleUser($profileResponse->json());

        if (!$googleUser['email_verified'] || $googleUser['email'] === '') {
            throw new RuntimeException('Tài khoản Google chưa có email xác thực hợp lệ.');
        }

        return $this->findOrCreateUser($googleUser);
    }

    public function normalizeGoogleUser(mixed $payload): array
    {
        $data = is_array($payload) ? $payload : [];

        return [
            'email'          => isset($data['email']) && is_string($data['email']) ? $data['email'] : '',
            'name'           => isset($data['name']) && is_string($data['name']) && $data['name'] !== '' ? $data['name'] : ($data['email'] ?? ''),
            'picture'        => isset($data['picture']) && is_string($data['picture']) ? $data['picture'] : null,
            'sub'            => isset($data['sub']) && is_string($data['sub']) ? $data['sub'] : null,
            'email_verified' => (bool) ($data['email_verified'] ?? false),
        ];
    }

    public function findOrCreateUser(array $googleUser): TaiKhoan
    {
        $existing = TaiKhoan::query()
            ->where(function ($query) use ($googleUser) {
                if ($googleUser['sub'] !== null && $googleUser['sub'] !== '') {
                    $query->where('google_id', $googleUser['sub'])
                          ->orWhere('email', $googleUser['email']);
                    return;
                }
                $query->where('email', $googleUser['email']);
            })
            ->first();

        if ($existing && $existing->isStaff()) {
            throw new RuntimeException('Đăng nhập Google chỉ áp dụng cho tài khoản học viên.');
        }

        if ($existing && (int) $existing->trangThai !== 1) {
            throw new RuntimeException('Tài khoản của bạn đã bị khóa. Vui lòng liên hệ trung tâm để được hỗ trợ.');
        }

        return DB::transaction(function () use ($googleUser, $existing) {
            if ($existing instanceof TaiKhoan) {
                $existing->loadMissing('hoSoNguoiDung');

                $existing->forceFill([
                    'google_id'         => $googleUser['sub'] ?? $existing->google_id,
                    'google_avatar'     => $googleUser['picture'] ?? $existing->google_avatar,
                    'email_verified_at' => $existing->email_verified_at ?? now(),
                ])->save();

                $existing->hoSoNguoiDung()->updateOrCreate(
                    ['taiKhoanId' => $existing->taiKhoanId],
                    ['hoTen'      => $existing->hoSoNguoiDung?->hoTen ?? $googleUser['name']]
                );

                return $existing;
            }

            // Tạo mới
            $taiKhoan = TaiKhoan::create([
                'taiKhoan'          => TaiKhoan::generateTemporaryUsername(TaiKhoan::ROLE_HOC_VIEN),
                'email'             => $googleUser['email'],
                'matKhau'           => Hash::make(Str::random(32)),
                'role'              => TaiKhoan::ROLE_HOC_VIEN,
                'trangThai'         => 1,
                'phaiDoiMatKhau'    => 0,
                'auth_provider'     => 'google',
                'google_id'         => $googleUser['sub'],
                'google_avatar'     => $googleUser['picture'],
                'email_verified_at' => now(),
            ]);

            $taiKhoan->assignSystemUsername();

            HoSoNguoiDung::create([
                'taiKhoanId' => $taiKhoan->taiKhoanId,
                'hoTen'      => $googleUser['name'],
            ]);

            return $taiKhoan;
        });
    }
}
