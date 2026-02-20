<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Dùng trường 'taiKhoan' để đăng nhập thay vì 'email'
     */
    public function username()
    {
        return 'taiKhoan';
    }

    /**
     * Phân hướng sau khi đăng nhập thành công theo role:
     * 0 = Học viên  → /hoc-vien
     * 1 = Giáo viên → /admin/dashboard
     * 2 = Nhân viên → /admin/dashboard
     * 3 = Admin     → /admin/dashboard
     */
    protected function authenticated(Request $request, $user)
    {
        // Cập nhật thời gian đăng nhập cuối
        $user->lastLogin = now();
        $user->save();

        if ($user->isStaff()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('home.student.index');
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password'        => 'required|string|min:8',
        ], [
            $this->username() . '.required' => 'Vui lòng nhập Tài khoản',
            'password.required'             => 'Vui lòng nhập Mật khẩu',
            'password.min'                  => 'Mật khẩu phải có ít nhất 8 ký tự',
        ]);
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        throw \Illuminate\Validation\ValidationException::withMessages([
            $this->username() => ['Thông tin đăng nhập không chính xác.'],
        ]);
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect(route('login'));
    }
}
