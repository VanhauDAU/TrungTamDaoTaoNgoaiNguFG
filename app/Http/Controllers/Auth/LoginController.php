<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Concerns\ValidatesRecaptcha;
use App\Http\Controllers\Controller;
use App\Models\Auth\NhatKyDangNhap;
use App\Models\Auth\TaiKhoan;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers {
        login as protected traitLogin;
    }
    use ValidatesRecaptcha;

    protected const MAX_ATTEMPTS = 5;
    protected const LOCKOUT_MINUTES = 15;
    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest')->except('logout', 'showForceChangePassword', 'processForceChangePassword');
        $this->middleware('auth')->only('logout', 'showForceChangePassword', 'processForceChangePassword');
    }

    public function showLoginForm()
    {
        return view('auth.login', $this->loginViewData('student'));
    }

    public function showAdminLoginForm()
    {
        return view('auth.login', $this->loginViewData('admin'));
    }

    public function login(Request $request)
    {
        $request->attributes->set('login_portal', 'student');

        return $this->traitLogin($request);
    }

    public function adminLogin(Request $request)
    {
        $request->attributes->set('login_portal', 'admin');

        return $this->traitLogin($request);
    }

    public function username(): string
    {
        return 'taiKhoan';
    }

    protected function loginViewData(string $portal): array
    {
        return [
            'portal' => $portal,
            'portalTitle' => $portal === 'admin' ? 'Đăng nhập nhân sự' : 'Đăng nhập',
            'submitRoute' => $portal === 'admin' ? route('admin.login.submit') : route('login'),
            'alternateRoute' => $portal === 'admin' ? route('login') : route('admin.login'),
            'alternateLabel' => $portal === 'admin' ? 'Đăng nhập học viên' : 'Đăng nhập nhân sự',
            'registerRoute' => $portal === 'student' ? route('register') : null,
            'googleRoute' => $portal === 'student'
                && filled(config('services.google.client_id'))
                && filled(config('services.google.client_secret'))
                    ? route('auth.google.redirect')
                    : null,
            'recaptchaAction' => $portal === 'student' ? 'student_login' : null,
            'recaptchaEnabled' => $portal === 'student' && $this->recaptchaEnabled(),
        ];
    }

    protected function authenticated(Request $request, $user)
    {
        if (!$user instanceof TaiKhoan) {
            return redirect()->route('login');
        }

        NhatKyDangNhap::ghiLog(
            (string) $request->input($this->username(), ''),
            $request->ip(),
            true,
            $request->userAgent()
        );

        session()->forget('lockout_until');

        $user->forceFill(['lastLogin' => now()])->save();

        if ($user->phaiDoiMatKhau == 1) {
            return redirect()->route('force-change-password');
        }

        if ($user->role === TaiKhoan::ROLE_HOC_VIEN && !$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')
                ->with('warning', 'Vui lòng xác thực email trước khi vào khu vực học viên.');
        }

        if ($user->isStaff()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('home.student.index');
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string|min:8',
        ], [
            $this->username() . '.required' => 'Vui lòng nhập Email hoặc Tài khoản',
            'password.required' => 'Vui lòng nhập Mật khẩu',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự',
        ]);

        if ($this->loginPortal($request) === 'student') {
            $this->validateRecaptcha($request, 'student_login');
        }
    }

    protected function credentials(Request $request)
    {
        $loginInput = trim((string) $request->input($this->username()));
        $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'taiKhoan';

        return [
            $field => $loginInput,
            'password' => $request->input('password'),
        ];
    }

    public function attemptLogin(Request $request)
    {
        $loginInput = trim((string) $request->input($this->username()));
        $ip = $request->ip();
        $soLanSai = NhatKyDangNhap::soLanThatBaiGanDay($loginInput, $ip, self::LOCKOUT_MINUTES);

        if ($soLanSai >= self::MAX_ATTEMPTS) {
            $thoiDiemCuoi = NhatKyDangNhap::thoiDiemThatBaiCuoi($loginInput, $ip);
            $hetHan = $thoiDiemCuoi?->copy()->addMinutes(self::LOCKOUT_MINUTES);
            $giayConLai = $hetHan ? (int) max(0, now()->diffInSeconds($hetHan, false)) : 0;

            if ($giayConLai > 0) {
                $this->lockoutResponse($giayConLai);
            }
        }

        $remember = $request->boolean('remember');

        if (!$this->guard()->attempt($this->credentials($request), $remember)) {
            return false;
        }

        $user = $this->guard()->user();

        if (!$user instanceof TaiKhoan || !$this->matchesPortal($user, $this->loginPortal($request))) {
            $this->guard()->logout();

            return false;
        }

        return true;
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        NhatKyDangNhap::ghiLog(
            $request->input($this->username()),
            $request->ip(),
            false,
            $request->userAgent()
        );

        $loginInput = trim((string) $request->input($this->username()));
        $ip = $request->ip();
        $soLanSai = NhatKyDangNhap::soLanThatBaiGanDay($loginInput, $ip, self::LOCKOUT_MINUTES);
        $conLai = self::MAX_ATTEMPTS - $soLanSai;

        if ($conLai <= 0) {
            $thoiDiemCuoi = NhatKyDangNhap::thoiDiemThatBaiCuoi($loginInput, $ip);
            $hetHan = $thoiDiemCuoi?->copy()->addMinutes(self::LOCKOUT_MINUTES);
            $giayConLai = $hetHan ? (int) max(1, now()->diffInSeconds($hetHan, false)) : 1;

            $this->lockoutResponse($giayConLai);
        }

        $message = $this->loginPortal($request) === 'admin'
            ? "Tài khoản nhân sự hoặc mật khẩu không chính xác. Bạn còn {$conLai} lần thử."
            : "Tài khoản, email hoặc mật khẩu không chính xác. Bạn còn {$conLai} lần thử.";

        throw ValidationException::withMessages([
            $this->username() => [$message],
        ]);
    }

    protected function lockoutResponse(int $giayConLai)
    {
        session()->put('lockout_until', now()->addSeconds($giayConLai)->timestamp);

        throw ValidationException::withMessages([
            'lockout' => ['locked'],
        ]);
    }

    protected function maxAttempts(): int
    {
        return self::MAX_ATTEMPTS;
    }

    public function showForceChangePassword()
    {
        if ($this->currentUser()->phaiDoiMatKhau != 1) {
            return redirect('/');
        }

        return view('auth.force-change-password');
    }

    public function processForceChangePassword(Request $request)
    {
        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'new_password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        $user = $this->currentUser();
        $user->update([
            'matKhau' => Hash::make($request->new_password),
            'phaiDoiMatKhau' => 0,
        ]);
        $user->rotateRememberToken();

        if ($user->isStaff()) {
            return redirect()->route('admin.dashboard')
                ->with('success', 'Đổi mật khẩu thành công! Chào mừng bạn đến hệ thống.');
        }

        if ($user->role === TaiKhoan::ROLE_HOC_VIEN && !$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')
                ->with('success', 'Mật khẩu đã được cập nhật. Vui lòng xác thực email để tiếp tục.');
        }

        return redirect()->route('home.student.index')
            ->with('success', 'Đổi mật khẩu thành công! Chào mừng bạn đến hệ thống.');
    }

    public function logout(Request $request)
    {
        $currentUser = Auth::user();
        $redirectRoute = $currentUser instanceof TaiKhoan && $currentUser->isStaff() ? 'admin.login' : 'login';

        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect(route($redirectRoute));
    }

    private function loginPortal(Request $request): string
    {
        return (string) $request->attributes->get('login_portal', 'student');
    }

    private function matchesPortal(TaiKhoan $user, string $portal): bool
    {
        return $portal === 'admin'
            ? $user->isStaff()
            : $user->role === TaiKhoan::ROLE_HOC_VIEN;
    }

    private function currentUser(): TaiKhoan
    {
        $user = Auth::user();

        abort_unless($user instanceof TaiKhoan, 403);

        return $user;
    }
}
