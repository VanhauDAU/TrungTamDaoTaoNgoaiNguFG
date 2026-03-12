<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Concerns\ValidatesRecaptcha;
use App\Http\Controllers\Controller;
use App\Models\Auth\HoSoNguoiDung;
use App\Models\Auth\TaiKhoan;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;
    use ValidatesRecaptcha;

    protected $redirectTo = '/email/verify';

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        return view('auth.register', [
            'googleRoute' => filled(config('services.google.client_id')) && filled(config('services.google.client_secret'))
                ? route('auth.google.redirect')
                : null,
            'recaptchaEnabled' => $this->recaptchaEnabled(),
            'recaptchaAction' => 'student_register',
        ]);
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:taikhoan,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.unique' => 'Email này đã được đăng ký, vui lòng dùng email khác.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);
    }

    public function register(Request $request)
    {
        $this->validateRecaptcha($request, 'student_register');

        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        return $this->registered($request, $user)
            ?: redirect()->route('verification.notice')
                ->with('success', 'Đăng ký thành công. Vui lòng kiểm tra email để xác thực tài khoản.');
    }

    protected function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $taiKhoan = TaiKhoan::create([
                'taiKhoan' => TaiKhoan::generateTemporaryUsername(TaiKhoan::ROLE_HOC_VIEN),
                'email' => $data['email'],
                'matKhau' => Hash::make($data['password']),
                'role' => TaiKhoan::ROLE_HOC_VIEN,
                'trangThai' => 1,
                'phaiDoiMatKhau' => 0,
                'auth_provider' => 'local',
                'email_verified_at' => null,
                'lastLogin' => null,
            ]);

            $taiKhoan->assignSystemUsername();

            HoSoNguoiDung::create([
                'taiKhoanId' => $taiKhoan->taiKhoanId,
                'hoTen' => $data['name'],
            ]);

            return $taiKhoan;
        });
    }
}
