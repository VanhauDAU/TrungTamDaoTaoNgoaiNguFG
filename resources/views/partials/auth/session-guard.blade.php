<script>
    (() => {
        const config = {
            context: @json($sessionGuardContext ?? null),
            statusUrl: @json(route('auth.session-status')),
            logoutButtonId: @json($sessionGuardLogoutButtonId ?? null),
            logoutFormId: @json($sessionGuardLogoutFormId ?? null),
            staleTitle: @json($sessionGuardStaleTitle ?? 'Phiên đăng nhập đã thay đổi'),
            checkIntervalMs: 15000,
            networkErrorMessage: 'Không thể xác minh phiên hiện tại. Vui lòng tải lại trang trước khi tiếp tục.',
            logoutConfirmText: 'Bạn có chắc muốn đăng xuất khỏi hệ thống?',
        };

        if (!config.context) {
            return;
        }

        const state = {
            inflight: null,
            redirecting: false,
        };

        const syncCsrfToken = (token) => {
            if (!token) {
                return;
            }

            document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', token);
            document.querySelectorAll('input[name="_token"]').forEach((input) => {
                input.value = token;
            });

            window.NB_CSRF = token;
        };

        const redirectToSafeLocation = (status) => {
            if (state.redirecting) {
                return;
            }

            state.redirecting = true;

            const finish = () => {
                if (status?.redirectUrl) {
                    window.location.assign(status.redirectUrl);
                    return;
                }

                window.location.reload();
            };

            const message = status?.message || 'Phiên đăng nhập hiện tại không còn hợp lệ.';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: config.staleTitle,
                    text: message,
                    confirmButtonText: 'Tiếp tục',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                }).then(finish);
                return;
            }

            window.alert(message);
            finish();
        };

        const fetchSessionStatus = async () => {
            const url = new URL(config.statusUrl, window.location.origin);
            url.searchParams.set('context', config.context);

            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                cache: 'no-store',
            });

            if (!response.ok) {
                throw new Error(`Session status request failed with status ${response.status}`);
            }

            return response.json();
        };

        const checkSession = async ({ handleInvalid = true } = {}) => {
            if (!state.inflight) {
                state.inflight = fetchSessionStatus()
                    .catch((error) => {
                        console.warn('[portal-session-guard]', error);
                        return null;
                    })
                    .finally(() => {
                        state.inflight = null;
                    });
            }

            const status = await state.inflight;

            if (status?.allowed) {
                syncCsrfToken(status.csrfToken);
            }

            if (handleInvalid && status && !status.allowed) {
                redirectToSafeLocation(status);
            }

            return status;
        };

        const bindLogoutButton = () => {
            const button = document.getElementById(config.logoutButtonId || '');
            const form = document.getElementById(config.logoutFormId || '');

            if (!button || !form) {
                return;
            }

            button.addEventListener('click', async (event) => {
                event.preventDefault();

                const submitLogout = async () => {
                    const status = await checkSession({ handleInvalid: false });

                    if (!status) {
                        if (typeof Swal !== 'undefined') {
                            await Swal.fire({
                                icon: 'error',
                                title: 'Không thể xác minh phiên',
                                text: config.networkErrorMessage,
                                confirmButtonText: 'Tải lại trang',
                            });
                        } else {
                            window.alert(config.networkErrorMessage);
                        }

                        window.location.reload();
                        return;
                    }

                    if (!status.allowed) {
                        redirectToSafeLocation(status);
                        return;
                    }

                    form.submit();
                };

                if (typeof Swal !== 'undefined') {
                    const result = await Swal.fire({
                        title: 'Đăng xuất?',
                        text: config.logoutConfirmText,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-sign-out-alt me-1"></i> Đăng xuất',
                        cancelButtonText: 'Hủy',
                        confirmButtonColor: '#e31e24',
                        cancelButtonColor: '#6c757d',
                        reverseButtons: true,
                        focusCancel: true,
                    });

                    if (result.isConfirmed) {
                        await submitLogout();
                    }

                    return;
                }

                if (window.confirm(config.logoutConfirmText)) {
                    await submitLogout();
                }
            });
        };

        const registerVisibilityChecks = () => {
            window.addEventListener('focus', () => {
                if (!state.redirecting) {
                    void checkSession();
                }
            });

            window.addEventListener('pageshow', () => {
                if (!state.redirecting) {
                    void checkSession();
                }
            });

            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible' && !state.redirecting) {
                    void checkSession();
                }
            });

            window.setInterval(() => {
                if (document.visibilityState === 'visible' && !state.redirecting) {
                    void checkSession();
                }
            }, config.checkIntervalMs);
        };

        bindLogoutButton();
        registerVisibilityChecks();
        void checkSession();

        window.PortalSessionGuard = {
            checkSession,
        };
    })();
</script>
