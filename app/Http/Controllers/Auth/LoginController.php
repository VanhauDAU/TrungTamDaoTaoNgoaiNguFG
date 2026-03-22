<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\Auth\LoginServiceInterface;
use App\Http\Controllers\Auth\Concerns\ValidatesRecaptcha;
use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
use App\Services\Auth\LoginService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers {
        login as protected traitLogin;
    }
    use ValidatesRecaptcha;

    protected $redirectTo = '/';

    public function __construct(
        protected LoginServiceInterface $loginService
    ) {
        $this->middleware('guest')->except('logout', 'showForceChangePassword', 'processForceChangePassword', 'sessionStatus');
        $this->middleware('auth')->only('logout', 'showForceChangePassword', 'processForceChangePassword');
        $this->middleware('throttle:auth-login')->only('login', 'adminLogin', 'teacherLogin', 'staffLogin');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SHOW FORM
    // ─────────────────────────────────────────────────────────────────────────

    public function showLoginForm()
    {
        return view('auth.login', $this->loginService->getLoginViewData('student'));
    }

    public function showAdminLoginForm()
    {
        return redirect()->route('staff.login');
    }

    public function showTeacherLoginForm()
    {
        return view('auth.login', $this->loginService->getLoginViewData('teacher'));
    }

    public function showStaffLoginForm()
    {
        return view('auth.login', $this->loginService->getLoginViewData('staff'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LOGIN
    // ─────────────────────────────────────────────────────────────────────────

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

    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password'        => 'required|string|min:8',
        ], [
            $this->username() . '.required' => 'Vui lòng nhập Email hoặc Tài khoản',
            'password.required'             => 'Vui lòng nhập Mật khẩu',
            'password.min'                  => 'Mật khẩu phải có ít nhất 8 ký tự',
        ]);

        if ($this->loginPortal($request) === 'student') {
            $this->validateRecaptcha($request, 'student_login');
        }
    }

    protected function credentials(Request $request)
    {
        $loginInput = trim((string) $request->input($this->username()));
        $field      = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'taiKhoan';

        return [
            $field     => $loginInput,
            'password' => $request->input('password'),
        ];
    }

    public function attemptLogin(Request $request)
    {
        return $this->loginService->attemptLogin($request, $this->loginPortal($request));
    }

    protected function authenticated(Request $request, $user)
    {
        if (!$user instanceof TaiKhoan) {
            return redirect()->route('login');
        }

        return $this->loginService->handleAuthenticated($request, $user, $this->loginPortal($request));
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        $this->loginService->handleFailedLogin($request, $this->loginPortal($request));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FORCE PASSWORD CHANGE
    // ─────────────────────────────────────────────────────────────────────────

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
            'new_password.required'  => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min'       => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'new_password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        return $this->loginService->processForceChangePassword($request, $this->currentUser());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LOGOUT
    // ─────────────────────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        $redirectRoute = $this->loginService->logout($request);

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect(route($redirectRoute));
    }

    public function sessionStatus(Request $request): JsonResponse
    {
        $data = $request->validate([
            'context' => ['required', 'in:student,staff'],
        ]);

        return response()
            ->json($this->loginService->getSessionStatus($request, (string) $data['context']))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function loginPortal(Request $request): string
    {
        return (string) $request->attributes->get('login_portal', 'student');
    }

    private function currentUser(): TaiKhoan
    {
        $user = Auth::user();
        abort_unless($user instanceof TaiKhoan, 403);
        return $user;
    }
}
