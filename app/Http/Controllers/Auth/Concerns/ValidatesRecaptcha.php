<?php

namespace App\Http\Controllers\Auth\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        $errorCodes = isset($payload['error-codes']) && is_array($payload['error-codes'])
            ? $payload['error-codes']
            : [];

        $isValid = $response->successful()
            && ($payload['success'] ?? false)
            && ($expectedAction === null || $expectedAction === $action)
            && $score >= (float) config('services.recaptcha.min_score', 0.5);

        if (!$isValid) {
            Log::warning('reCAPTCHA verification failed', [
                'action_expected' => $action,
                'action_received' => $expectedAction,
                'score' => $score,
                'error_codes' => $errorCodes,
                'response_success' => $response->successful(),
                'host' => $request->getHost(),
                'ip' => $request->ip(),
            ]);

            $message = 'Xác minh reCAPTCHA không hợp lệ. Vui lòng thử lại.';

            if (app()->isLocal() || config('app.debug')) {
                if ($errorCodes !== []) {
                    $message .= ' Lý do: ' . implode(', ', $errorCodes) . '.';
                } elseif ($expectedAction !== null && $expectedAction !== $action) {
                    $message .= " Action trả về là '{$expectedAction}', mong đợi '{$action}'.";
                } else {
                    $message .= " Score hiện tại là {$score}.";
                }
            }

            throw ValidationException::withMessages([
                'recaptcha' => [$message],
            ]);
        }
    }
}
