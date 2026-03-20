<?php

namespace App\Services\Auth;

use App\Contracts\Auth\LoginServiceInterface;
use App\Models\Auth\NhatKyDangNhap;
use App\Models\Auth\TaiKhoan;
use App\Services\Auth\DeviceSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

class LoginService implements LoginServiceInterface
{
    protected const FIRST_LOCKOUT_ATTEMPTS = 5;
    protected const FIRST_LOCKOUT_MINUTES = 1;
    protected const LOCKOUT_INCREMENT_MINUTES = 5;
    protected const FAILURE_STREAK_RESET_HOURS = 24;

    public function __construct(
        protected DeviceSessionService $deviceSessionService
    ) {
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VIEW DATA
    // ─────────────────────────────────────────────────────────────────────────

    public function getLoginViewData(string $portal): array
    {
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
            'portalTitle' => 'Đăng nhập',
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
            'recaptchaEnabled' => $portal === 'student' && $this->isRecaptchaEnabled(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AUTHENTICATION
    // ─────────────────────────────────────────────────────────────────────────

    public function attemptLogin(Request $request, string $portal): bool
    {
        $loginInput = trim((string) $request->input('taiKhoan'));
        $ip = $request->ip();
        $soLanSai = $this->consecutiveFailedAttempts($loginInput, $ip);
        $lockoutMin = $this->lockoutMinutesForFailures($soLanSai);

        if ($lockoutMin > 0) {
            $thoiDiemCuoi = NhatKyDangNhap::thoiDiemThatBaiCuoi($loginInput, $ip);
            $hetHan = $thoiDiemCuoi?->copy()->addMinutes($lockoutMin);
            $giayConLai = $hetHan ? (int) max(0, now()->diffInSeconds($hetHan, false)) : 0;

            if ($giayConLai > 0) {
                $this->lockoutResponse($giayConLai, $this->lockoutMessage($soLanSai, $lockoutMin));
            }
        }

        $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'taiKhoan';
        $user = TaiKhoan::query()->where($field, $loginInput)->first();

        if (
            $user instanceof TaiKhoan
            && (int) $user->trangThai !== 1
            && Hash::check((string) $request->input('password'), (string) $user->matKhau)
        ) {
            throw ValidationException::withMessages([
                'taiKhoan' => ['Tài khoản của bạn đã bị khóa. Vui lòng liên hệ trung tâm để được hỗ trợ.'],
            ]);
        }

        $credentials = [$field => $loginInput, 'password' => $request->input('password'), 'trangThai' => 1];

        if (!Auth::guard()->attempt($credentials, $request->boolean('remember'))) {
            return false;
        }

        $user = Auth::guard()->user();

        if (!$user instanceof TaiKhoan || !$this->matchesPortal($user, $portal)) {
            Auth::guard()->logout();
            return false;
        }

        return true;
    }

    public function handleAuthenticated(Request $request, TaiKhoan $user, string $portal): RedirectResponse
    {
        NhatKyDangNhap::ghiLog(
            (string) $request->input('taiKhoan', ''),
            $request->ip(),
            true,
            $request->userAgent()
        );

        session()->forget(['lockout_until', 'lockout_message']);
        $request->session()->put([
            'auth_portal' => $portal,
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

    public function handleFailedLogin(Request $request, string $portal): void
    {
        NhatKyDangNhap::ghiLog(
            $request->input('taiKhoan'),
            $request->ip(),
            false,
            $request->userAgent()
        );

        $loginInput = trim((string) $request->input('taiKhoan'));
        $ip = $request->ip();
        $soLanSai = $this->consecutiveFailedAttempts($loginInput, $ip);
        $lockoutMin = $this->lockoutMinutesForFailures($soLanSai);

        if ($lockoutMin > 0) {
            $thoiDiemCuoi = NhatKyDangNhap::thoiDiemThatBaiCuoi($loginInput, $ip);
            $hetHan = $thoiDiemCuoi?->copy()->addMinutes($lockoutMin);
            $giayConLai = $hetHan ? (int) max(1, now()->diffInSeconds($hetHan, false)) : 1;

            $this->lockoutResponse($giayConLai, $this->lockoutMessage($soLanSai, $lockoutMin));
        }

        $conLai = max(0, self::FIRST_LOCKOUT_ATTEMPTS - $soLanSai);
        $message = match ($portal) {
            'teacher' => "Tài khoản giảng viên hoặc mật khẩu không chính xác. Bạn còn {$conLai} lần thử.",
            'staff', 'admin' => "Tài khoản nhân viên hoặc mật khẩu không chính xác. Bạn còn {$conLai} lần thử.",
            default => "Tài khoản, email hoặc mật khẩu không chính xác. Bạn còn {$conLai} lần thử.",
        };

        throw ValidationException::withMessages(['taiKhoan' => [$message]]);
    }

    public function lockoutResponse(int $remainingSeconds, string $message): never
    {
        session()->put([
            'lockout_until' => now()->addSeconds($remainingSeconds)->timestamp,
            'lockout_message' => $message,
        ]);

        throw ValidationException::withMessages(['lockout' => ['locked']]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LOGOUT
    // ─────────────────────────────────────────────────────────────────────────

    public function logout(Request $request): string
    {
        $currentUser = Auth::user();
        $redirectRoute = $currentUser instanceof TaiKhoan
            ? $this->logoutRedirectRouteFor($currentUser)
            : 'login';

        if ($currentUser instanceof TaiKhoan) {
            $this->deviceSessionService->revokeSessionById(
                $currentUser,
                (string) $request->session()->getId(),
                'logout_current',
                $request
            );
        }

        Auth::guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $redirectRoute;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FORCE PASSWORD CHANGE
    // ─────────────────────────────────────────────────────────────────────────

    public function processForceChangePassword(Request $request, TaiKhoan $user): RedirectResponse
    {
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

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    public function matchesPortal(TaiKhoan $user, string $portal): bool
    {
        return match ($portal) {
            'teacher' => $user->role === TaiKhoan::ROLE_GIAO_VIEN,
            'staff' => in_array($user->role, [TaiKhoan::ROLE_NHAN_VIEN, TaiKhoan::ROLE_ADMIN], true),
            default => $user->role === TaiKhoan::ROLE_HOC_VIEN,
        };
    }
    public function staffDashboardRouteFor(TaiKhoan $user): string
    {
        if ($user->role === TaiKhoan::ROLE_GIAO_VIEN && Route::has('teacher.dashboard')) {
            return 'teacher.dashboard';
        }

        if (in_array($user->role, [TaiKhoan::ROLE_NHAN_VIEN, TaiKhoan::ROLE_ADMIN], true) && Route::has('staff.dashboard')) {
            return 'staff.dashboard';
        }

        return 'admin.dashboard';
    }

    public function logoutRedirectRouteFor(TaiKhoan $user): string
    {
        return match ($user->role) {
            TaiKhoan::ROLE_GIAO_VIEN => 'teacher.login',
            TaiKhoan::ROLE_NHAN_VIEN, TaiKhoan::ROLE_ADMIN => 'staff.login',
            default => 'login',
        };
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

    private function isRecaptchaEnabled(): bool
    {
        return (bool) config('services.recaptcha.enabled')
            && filled(config('services.recaptcha.secret_key'))
            && filled(config('services.recaptcha.site_key'));
    }
}
