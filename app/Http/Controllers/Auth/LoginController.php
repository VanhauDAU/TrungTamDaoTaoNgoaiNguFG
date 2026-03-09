<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auth\NhatKyDangNhap;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

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

    /** Số lần đăng nhập sai tối đa trước khi bị khóa */
    protected const MAX_ATTEMPTS = 5;

    /** Thời gian khóa (phút) */
    protected const LOCKOUT_MINUTES = 15;

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
        $this->middleware('guest')->except('logout', 'showForceChangePassword', 'processForceChangePassword');
        $this->middleware('auth')->only('logout', 'showForceChangePassword', 'processForceChangePassword');
    }

    /**
     * Dùng trường 'taiKhoan' để đăng nhập thay vì 'email'
     */
    public function username()
    {
        return 'taiKhoan';
    }

    /**
     * Phân hướng sau khi đăng nhập thành công theo role.
     * Ghi nhận log thành công + kiểm tra đổi mật khẩu bắt buộc.
     */
    protected function authenticated(Request $request, $user)
    {
        // Ghi log đăng nhập thành công
        NhatKyDangNhap::ghiLog(
            $request->input($this->username()),
            $request->ip(),
            true,
            $request->userAgent()
        );

        // Xóa lockout khỏi session khi đăng nhập thành công
        session()->forget('lockout_until');

        // Cập nhật thời gian đăng nhập cuối
        $user->lastLogin = now();
        $user->save();

        // Nếu phải đổi mật khẩu → redirect sang trang bắt buộc đổi
        if ($user->phaiDoiMatKhau == 1) {
            return redirect()->route('force-change-password');
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
    }

    protected function credentials(Request $request)
    {
        $loginInput = $request->input($this->username());

        // Kiểm tra xem chuỗi nhập vào có định dạng email không
        $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'taiKhoan';

        return [
            $field => $loginInput,
            'password' => $request->input('password')
        ];
    }

    /**
     * Override attemptLogin: kiểm tra lockout trước khi thử đăng nhập.
     */
    public function attemptLogin(Request $request)
    {
        $loginInput = $request->input($this->username());
        $ip = $request->ip();

        // Đếm số lần thất bại gần đây
        $soLanSai = NhatKyDangNhap::soLanThatBaiGanDay($loginInput, $ip, self::LOCKOUT_MINUTES);

        if ($soLanSai >= self::MAX_ATTEMPTS) {
            // Lấy thời điểm thất bại cuối cùng để tính thời gian còn lại
            $thoiDiemCuoi = NhatKyDangNhap::thoiDiemThatBaiCuoi($loginInput, $ip);
            $hetHan = $thoiDiemCuoi->copy()->addMinutes(self::LOCKOUT_MINUTES);
            $giayConLai = (int) max(0, now()->diffInSeconds($hetHan, false));

            if ($giayConLai > 0) {
                $this->lockoutResponse($giayConLai);
            }
        }

        return $this->guard()->attempt(
            $this->credentials($request),
            $request->boolean('remember')
        );
    }

    /**
     * Ghi log thất bại trước khi gửi response lỗi.
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        // Ghi log đăng nhập thất bại
        NhatKyDangNhap::ghiLog(
            $request->input($this->username()),
            $request->ip(),
            false,
            $request->userAgent()
        );

        // Đếm lại số lần sai sau khi ghi log
        $loginInput = $request->input($this->username());
        $ip = $request->ip();
        $soLanSai = NhatKyDangNhap::soLanThatBaiGanDay($loginInput, $ip, self::LOCKOUT_MINUTES);
        $conLai = self::MAX_ATTEMPTS - $soLanSai;

        if ($conLai <= 0) {
            $thoiDiemCuoi = NhatKyDangNhap::thoiDiemThatBaiCuoi($loginInput, $ip);
            $hetHan = $thoiDiemCuoi->copy()->addMinutes(self::LOCKOUT_MINUTES);
            $giayConLai = (int) max(1, now()->diffInSeconds($hetHan, false));

            $this->lockoutResponse($giayConLai);
        }

        throw \Illuminate\Validation\ValidationException::withMessages([
            $this->username() => [
                "Tài khoản, email hoặc mật khẩu không chính xác. Bạn còn {$conLai} lần thử."
            ],
        ]);
    }

    /**
     * Format giây thành "X phút Y giây".
     */
    protected function formatThoiGian(int $giay): string
    {
        $phut = intdiv($giay, 60);
        $giayDu = $giay % 60;

        if ($phut > 0 && $giayDu > 0) {
            return "{$phut} phút {$giayDu} giây";
        } elseif ($phut > 0) {
            return "{$phut} phút";
        }
        return "{$giayDu} giây";
    }

    /**
     * Trả response lockout — lưu lockout_until vào session + redirect back.
     */
    protected function lockoutResponse(int $giayConLai)
    {
        // Lưu thời điểm hết lockout (Unix timestamp) — persist qua redirect
        session()->put('lockout_until', now()->addSeconds($giayConLai)->timestamp);

        throw \Illuminate\Validation\ValidationException::withMessages([
            'lockout' => ['locked'],
        ]);
    }

    /**
     * Trả về số lần tối đa cho phép đăng nhập sai.
     */
    protected function maxAttempts(): int
    {
        return self::MAX_ATTEMPTS;
    }

    // ═══════════════════════════════════════════════════════════════
    // ĐỔI MẬT KHẨU BẮT BUỘC (lần đầu đăng nhập)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Hiển thị form đổi mật khẩu bắt buộc.
     */
    public function showForceChangePassword()
    {
        if (auth()->user()->phaiDoiMatKhau != 1) {
            return redirect('/');
        }
        return view('auth.force-change-password');
    }

    /**
     * Xử lý đổi mật khẩu bắt buộc.
     */
    public function processForceChangePassword(Request $request)
    {
        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'new_password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        $user = auth()->user();
        $user->update([
            'matKhau' => Hash::make($request->new_password),
            'phaiDoiMatKhau' => 0,
        ]);

        // Redirect theo role
        if ($user->isStaff()) {
            return redirect()->route('admin.dashboard')
                ->with('success', 'Đổi mật khẩu thành công! Chào mừng bạn đến hệ thống.');
        }
        return redirect()->route('home.student.index')
            ->with('success', 'Đổi mật khẩu thành công! Chào mừng bạn đến hệ thống.');
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
