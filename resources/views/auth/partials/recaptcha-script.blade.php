@if (!empty($recaptchaEnabled) && !empty($recaptchaAction))
    <input type="hidden" name="recaptcha_token" id="{{ $formId ?? 'auth-form' }}-recaptcha-token">
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById(@json($formId ?? 'auth-form'));
            const tokenInput = document.getElementById(@json(($formId ?? 'auth-form') . '-recaptcha-token'));
            let submitting = false;

            if (!form || !tokenInput || typeof grecaptcha === 'undefined') {
                return;
            }

            const refreshToken = function(callback) {
                grecaptcha.ready(function() {
                    grecaptcha.execute(@json(config('services.recaptcha.site_key')), {
                        action: @json($recaptchaAction)
                    }).then(function(token) {
                        tokenInput.value = token;
                        if (typeof callback === 'function') {
                            callback();
                        }
                    });
                });
            };

            refreshToken();
            form.addEventListener('submit', function(event) {
                if (submitting) {
                    return;
                }

                if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
                    return;
                }

                event.preventDefault();
                refreshToken(function() {
                    submitting = true;
                    HTMLFormElement.prototype.submit.call(form);
                });
            });
        });
    </script>
@endif
