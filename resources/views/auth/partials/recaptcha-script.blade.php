@if (!empty($recaptchaEnabled) && !empty($recaptchaAction))
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById(@json($formId ?? 'auth-form'));
            let submitting = false;

            if (!form || typeof grecaptcha === 'undefined') {
                return;
            }

            let tokenInput = document.getElementById(@json(($formId ?? 'auth-form') . '-recaptcha-token'));

            if (!tokenInput) {
                tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = 'recaptcha_token';
                tokenInput.id = @json(($formId ?? 'auth-form') . '-recaptcha-token');
                form.appendChild(tokenInput);
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

                if (event.defaultPrevented) {
                    return;
                }

                if (window.FiveGeniusValidation && !window.FiveGeniusValidation.validateForm(form)) {
                    event.preventDefault();
                    event.stopPropagation();
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
