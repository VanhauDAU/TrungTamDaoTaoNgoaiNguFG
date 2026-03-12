<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auth\TaiKhoan;
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
        $user->rotateRememberToken();

        $this->guard()->login($user);
    }
}
