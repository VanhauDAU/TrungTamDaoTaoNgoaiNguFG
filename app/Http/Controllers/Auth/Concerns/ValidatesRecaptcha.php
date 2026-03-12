<?php

namespace App\Http\Controllers\Auth\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

trait ValidatesRecaptcha
{
    protected function recaptchaEnabled(): bool
    {
        return (bool) config('services.recaptcha.enabled')
            && filled(config('services.recaptcha.site_key'))
            && filled(config('services.recaptcha.secret_key'));
    }

    protected function validateRecaptcha(Request $request, string $action): void
    {
        if (!$this->recaptchaEnabled()) {
            return;
        }

        $request->validate([
            'recaptcha_token' => ['required', 'string'],
        ], [
            'recaptcha_token.required' => 'Phiên xác minh reCAPTCHA đã hết hạn. Vui lòng thử lại.',
        ]);

        $response = Http::asForm()
            ->timeout(10)
            ->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('services.recaptcha.secret_key'),
                'response' => $request->input('recaptcha_token'),
                'remoteip' => $request->ip(),
            ]);

        $payload = $response->json();
        $score = (float) ($payload['score'] ?? 0);
        $expectedAction = $payload['action'] ?? null;

        $isValid = $response->successful()
            && ($payload['success'] ?? false)
            && ($expectedAction === null || $expectedAction === $action)
            && $score >= (float) config('services.recaptcha.min_score', 0.5);

        if (!$isValid) {
            throw ValidationException::withMessages([
                'recaptcha' => ['Xác minh reCAPTCHA không hợp lệ. Vui lòng thử lại.'],
            ]);
        }
    }
}
