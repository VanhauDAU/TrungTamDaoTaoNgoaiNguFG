<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    protected bool $skipAutoLoginAfterReset = false;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/';

    protected function resetPassword($user, $password)
    {
        if (!$user instanceof TaiKhoan) {
            return;
        }

        $user->forceFill([
            'matKhau' => Hash::make($password),
            'phaiDoiMatKhau' => 0,
        ])->save();
        $user->rotateRememberToken('password_reset');

        if ((int) $user->trangThai !== 1) {
            $this->skipAutoLoginAfterReset = true;
            return;
        }

        $this->guard()->login($user);
    }

    protected function sendResetResponse(Request $request, $response)
    {
        if ($this->skipAutoLoginAfterReset) {
            return redirect()->route('login')
                ->with('status', 'Mật khẩu đã được đặt lại nhưng tài khoản hiện đang bị khóa.');
        }

        return redirect($this->redirectPath())
            ->with('status', trans($response));
    }
}
