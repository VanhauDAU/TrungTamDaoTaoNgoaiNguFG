<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Concerns\ValidatesRecaptcha;
use App\Http\Controllers\Controller;
use App\Models\Auth\NhatKyDangNhap;
use App\Models\Auth\TaiKhoan;
use App\Services\Auth\DeviceSessionService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers {
        login as protected traitLogin;
    }
    use ValidatesRecaptcha;

    protected const FIRST_LOCKOUT_ATTEMPTS = 5;
    protected const FIRST_LOCKOUT_MINUTES = 1;
    protected const LOCKOUT_INCREMENT_MINUTES = 5;
    protected const FAILURE_STREAK_RESET_HOURS = 24;
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
        return redirect()->route('staff.login');
    }

    public function showTeacherLoginForm()
    {
        return view('auth.login', $this->loginViewData('teacher'));
    }

    public function showStaffLoginForm()
    {
        return view('auth.login', $this->loginViewData('staff'));
    }

    public function login(Request $request)
    {
        $request->attributes->set('login_portal', 'student');

        return $this->traitLogin($request);
    }

    public function adminLogin(Request $request)
    {
        $request->attributes->set('login_portal', 'staff');

        return $this->traitLogin($request);
    }

    public function teacherLogin(Request $request)
    {
        $request->attributes->set('login_portal', 'teacher');

        return $this->traitLogin($request);
    }

    public function staffLogin(Request $request)
    {
        $request->attributes->set('login_portal', 'staff');

        return $this->traitLogin($request);
    }

    public function username(): string
    {
        return 'taiKhoan';
    }

    protected function loginViewData(string $portal): array
    {
        $portalTitle = match ($portal) {
            default => 'Đăng nhập',
        };

        $submitRoute = match ($portal) {
            'teacher' => route('teacher.login.submit'),
            'staff' => route('staff.login.submit'),
            default => route('login'),
        };

        $alternateRoute = match ($portal) {
            'teacher' => route('staff.login'),
            'staff' => route('teacher.login'),
            default => route('staff.login'),
        };

        $alternateLabel = match ($portal) {
            'teacher' => 'Đăng nhập nhân viên',
            'staff' => 'Đăng nhập giảng viên',
            default => 'Đăng nhập nhân viên',
        };

        $secondaryAlternateRoute = match ($portal) {
            'student' => route('teacher.login'),
            default => route('login'),
        };

        $secondaryAlternateLabel = match ($portal) {
            'student' => 'Đăng nhập giảng viên',
            default => 'Đăng nhập học viên',
        };

        return [
            'portal' => $portal,
            'portalTitle' => $portalTitle,
            'submitRoute' => $submitRoute,
            'alternateRoute' => $alternateRoute,
            'alternateLabel' => $alternateLabel,
            'secondaryAlternateRoute' => $secondaryAlternateRoute,
            'secondaryAlternateLabel' => $secondaryAlternateLabel,
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

        session()->forget(['lockout_until', 'lockout_message']);
        $request->session()->put([
            'auth_portal' => $this->loginPortal($request),
            'auth_login_method' => 'password',
            'auth_remembered' => $request->boolean('remember'),
        ]);

        $user->forceFill(['lastLogin' => now()])->save();

        if ($user->phaiDoiMatKhau == 1) {
            return redirect()->route('force-change-password');
        }

        if ($user->role === TaiKhoan::ROLE_HOC_VIEN && !$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')
                ->with('warning', 'Vui lòng xác thực email trước khi vào khu vực học viên.');
        }

        if ($user->isStaff()) {
            return redirect()->route($this->staffDashboardRouteFor($user));
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
        $soLanSai = $this->consecutiveFailedAttempts($loginInput, $ip);
        $lockoutMinutes = $this->lockoutMinutesForFailures($soLanSai);

        if ($lockoutMinutes > 0) {
            $thoiDiemCuoi = NhatKyDangNhap::thoiDiemThatBaiCuoi($loginInput, $ip);
            $hetHan = $thoiDiemCuoi?->copy()->addMinutes($lockoutMinutes);
            $giayConLai = $hetHan ? (int) max(0, now()->diffInSeconds($hetHan, false)) : 0;

            if ($giayConLai > 0) {
                $this->lockoutResponse($giayConLai, $this->lockoutMessage($soLanSai, $lockoutMinutes));
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
        $soLanSai = $this->consecutiveFailedAttempts($loginInput, $ip);
        $lockoutMinutes = $this->lockoutMinutesForFailures($soLanSai);

        if ($lockoutMinutes > 0) {
            $thoiDiemCuoi = NhatKyDangNhap::thoiDiemThatBaiCuoi($loginInput, $ip);
            $hetHan = $thoiDiemCuoi?->copy()->addMinutes($lockoutMinutes);
            $giayConLai = $hetHan ? (int) max(1, now()->diffInSeconds($hetHan, false)) : 1;

            $this->lockoutResponse($giayConLai, $this->lockoutMessage($soLanSai, $lockoutMinutes));
        }

        $conLai = max(0, self::FIRST_LOCKOUT_ATTEMPTS - $soLanSai);

        $message = match ($this->loginPortal($request)) {
            'teacher' => "Tài khoản giảng viên hoặc mật khẩu không chính xác. Bạn còn {$conLai} lần thử.",
            'staff', 'admin' => "Tài khoản nhân viên hoặc admin hoặc mật khẩu không chính xác. Bạn còn {$conLai} lần thử.",
            default => "Tài khoản, email hoặc mật khẩu không chính xác. Bạn còn {$conLai} lần thử.",
        };

        throw ValidationException::withMessages([
            $this->username() => [$message],
        ]);
    }

    protected function lockoutResponse(int $giayConLai, string $message)
    {
        session()->put([
            'lockout_until' => now()->addSeconds($giayConLai)->timestamp,
            'lockout_message' => $message,
        ]);

        throw ValidationException::withMessages([
            'lockout' => ['locked'],
        ]);
    }

    protected function maxAttempts(): int
    {
        return self::FIRST_LOCKOUT_ATTEMPTS;
    }

    private function consecutiveFailedAttempts(string $loginInput, string $ip): int
    {
        return NhatKyDangNhap::soLanThatBaiLienTiep(
            $loginInput,
            $ip,
            self::FAILURE_STREAK_RESET_HOURS
        );
    }

    private function lockoutMinutesForFailures(int $failures): int
    {
        if ($failures < self::FIRST_LOCKOUT_ATTEMPTS) {
            return 0;
        }

        if ($failures === self::FIRST_LOCKOUT_ATTEMPTS) {
            return self::FIRST_LOCKOUT_MINUTES;
        }

        return ($failures - self::FIRST_LOCKOUT_ATTEMPTS) * self::LOCKOUT_INCREMENT_MINUTES;
    }

    private function lockoutMessage(int $failures, int $minutes): string
    {
        if ($failures === self::FIRST_LOCKOUT_ATTEMPTS) {
            return "Bạn đã nhập sai {$failures} lần liên tiếp. Tài khoản bị tạm khóa 1 phút.";
        }

        return "Bạn đã nhập sai {$failures} lần liên tiếp. Tài khoản bị tạm khóa {$minutes} phút. Mỗi lần sai tiếp theo thời gian khóa sẽ tăng thêm 5 phút cho đến khi đăng nhập thành công.";
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
        $user->rotateRememberToken('force_password_change', (string) $request->session()->getId());

        if ($user->isStaff()) {
            return redirect()->route($this->staffDashboardRouteFor($user))
                ->with('success', 'Đổi mật khẩu thành công! Chào mừng bạn đến hệ thống.');
        }

        if ($user->role === TaiKhoan::ROLE_HOC_VIEN && !$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')
                ->with('success', 'Mật khẩu đã được cập nhật. Vui lòng xác thực email để tiếp tục.');
        }

        return redirect()->route('home.student.index')
            ->with('success', 'Đổi mật khẩu thành công! Chào mừng bạn đến hệ thống.');
    }

    public function logout(Request $request, DeviceSessionService $deviceSessionService)
    {
        $currentUser = Auth::user();
        $redirectRoute = $currentUser instanceof TaiKhoan
            ? $this->logoutRedirectRouteFor($currentUser)
            : 'login';

        if ($currentUser instanceof TaiKhoan) {
            $deviceSessionService->revokeSessionById(
                $currentUser,
                (string) $request->session()->getId(),
                'logout_current',
                $request
            );
        }

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
        return match ($portal) {
            'teacher' => $user->role === TaiKhoan::ROLE_GIAO_VIEN,
            'staff' => in_array($user->role, [TaiKhoan::ROLE_NHAN_VIEN, TaiKhoan::ROLE_ADMIN], true),
            default => $user->role === TaiKhoan::ROLE_HOC_VIEN,
        };
    }

    private function currentUser(): TaiKhoan
    {
        $user = Auth::user();

        abort_unless($user instanceof TaiKhoan, 403);

        return $user;
    }

    private function staffDashboardRouteFor(TaiKhoan $user): string
    {
        if ($user->role === TaiKhoan::ROLE_GIAO_VIEN && Route::has('teacher.dashboard')) {
            return 'teacher.dashboard';
        }

        if (in_array($user->role, [TaiKhoan::ROLE_NHAN_VIEN, TaiKhoan::ROLE_ADMIN], true) && Route::has('staff.dashboard')) {
            return 'staff.dashboard';
        }

        return 'admin.dashboard';
    }

    private function logoutRedirectRouteFor(TaiKhoan $user): string
    {
        return match ($user->role) {
            TaiKhoan::ROLE_GIAO_VIEN => 'teacher.login',
            TaiKhoan::ROLE_NHAN_VIEN,
            TaiKhoan::ROLE_ADMIN => 'staff.login',
            default => 'login',
        };
    }
}
