<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\Auth\RegisterServiceInterface;
use App\Http\Controllers\Auth\Concerns\ValidatesRecaptcha;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    use RegistersUsers;
    use ValidatesRecaptcha;

    protected $redirectTo = '/email/verify';

    public function __construct(
        protected RegisterServiceInterface $registerService
    ) {
        $this->middleware('guest');
        $this->middleware('throttle:auth-register')->only('register');
        $this->middleware('throttle:auth-email-check')->only('checkEmail');
    }

    public function showRegistrationForm()
    {
        return view('auth.register', $this->registerService->getRegisterViewData());
    }

    public function register(Request $request)
    {
        $this->validateRecaptcha($request, 'student_register');
        return $this->registerService->register($request);
    }

    public function checkEmail(Request $request): JsonResponse
    {
        return response()->json(
            $this->registerService->checkEmailAvailability($request->query('email'))
        );
    }
}
