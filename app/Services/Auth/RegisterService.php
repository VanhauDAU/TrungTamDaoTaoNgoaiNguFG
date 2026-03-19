<?php

namespace App\Services\Auth;

use App\Contracts\Auth\RegisterServiceInterface;
use App\Models\Auth\HoSoNguoiDung;
use App\Models\Auth\TaiKhoan;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Throwable;

class RegisterService implements RegisterServiceInterface
{
    public function getRegisterViewData(): array
    {
        return [
            'googleRoute'      => filled(config('services.google.client_id')) && filled(config('services.google.client_secret'))
                ? route('auth.google.redirect')
                : null,
            'recaptchaEnabled' => $this->isRecaptchaEnabled(),
            'recaptchaAction'  => 'student_register',
        ];
    }

    public function validate(array $data): void
    {
        $validator = Validator::make($data, [
            'name'     => ['required', 'string', 'max:255', 'regex:/^[^0-9]*$/'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:taikhoan,email'],
            'phone'    => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required'     => 'Vui lòng nhập họ và tên.',
            'name.regex'        => 'Họ và tên không được chứa chữ số.',
            'email.required'    => 'Vui lòng nhập email.',
            'email.unique'      => 'Email này đã được đăng ký, vui lòng dùng email khác.',
            'phone.required'    => 'Vui lòng nhập số điện thoại.',
            'phone.regex'       => 'Số điện thoại không hợp lệ (phải có 10 chữ số).',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min'      => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed'=> 'Xác nhận mật khẩu không khớp.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    public function create(array $data): TaiKhoan
    {
        return DB::transaction(function () use ($data) {
            $taiKhoan = TaiKhoan::create([
                'taiKhoan'         => TaiKhoan::generateTemporaryUsername(TaiKhoan::ROLE_HOC_VIEN),
                'email'            => $data['email'],
                'matKhau'          => Hash::make($data['password']),
                'role'             => TaiKhoan::ROLE_HOC_VIEN,
                'trangThai'        => 1,
                'phaiDoiMatKhau'   => 0,
                'auth_provider'    => 'local',
                'email_verified_at'=> null,
                'lastLogin'        => null,
            ]);

            $taiKhoan->assignSystemUsername();

            HoSoNguoiDung::create([
                'taiKhoanId'  => $taiKhoan->taiKhoanId,
                'hoTen'       => $data['name'],
                'soDienThoai' => $data['phone'],
            ]);

            return $taiKhoan;
        });
    }

    public function checkEmailAvailability(?string $email): array
    {
        $normalizedEmail = Str::lower(trim((string) $email));

        if ($normalizedEmail === '' || !filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL)) {
            return [
                'status' => 'invalid',
                'message' => 'Vui lòng nhập địa chỉ email hợp lệ.',
            ];
        }

        $cacheStore = (string) config('auth.register_email_check_cache_store', 'redis_fallback');
        $cacheTtl = max(5, (int) config('auth.register_email_check_cache_ttl', 60));
        $cacheKey = 'auth:register:email-check:' . sha1($normalizedEmail);

        try {
            return Cache::store($cacheStore)->remember($cacheKey, now()->addSeconds($cacheTtl), function () use ($normalizedEmail) {
                return $this->resolveEmailAvailabilityPayload($normalizedEmail);
            });
        } catch (Throwable) {
            return $this->resolveEmailAvailabilityPayload($normalizedEmail);
        }
    }

    public function register(Request $request): RedirectResponse
    {
        $this->validate($request->all());

        $user = $this->create($request->all());

        event(new Registered($user));

        Auth::guard()->login($user);

        return redirect()->route('verification.notice')
            ->with('success', 'Đăng ký thành công. Vui lòng kiểm tra email để xác thực tài khoản.');
    }

    private function isRecaptchaEnabled(): bool
    {
        return filled(config('services.recaptcha.secret_key'));
    }

    private function resolveEmailAvailabilityPayload(string $normalizedEmail): array
    {
        $exists = TaiKhoan::query()
            ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->exists();

        if ($exists) {
            return [
                'status' => 'taken',
                'message' => 'Email này đã được sử dụng.',
            ];
        }

        return [
            'status' => 'available',
            'message' => 'Email này có thể sử dụng.',
        ];
    }
}
