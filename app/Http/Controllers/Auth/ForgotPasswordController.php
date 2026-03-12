<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Concerns\ValidatesRecaptcha;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails {
        sendResetLinkEmail as protected traitSendResetLinkEmail;
    }
    use ValidatesRecaptcha;

    public function showLinkRequestForm()
    {
        return view('auth.passwords.email', [
            'recaptchaEnabled' => $this->recaptchaEnabled(),
            'recaptchaAction' => 'forgot_password',
        ]);
    }

    public function sendResetLinkEmail(Request $request)
    {
        $this->validateRecaptcha($request, 'forgot_password');

        return $this->traitSendResetLinkEmail($request);
    }
}
