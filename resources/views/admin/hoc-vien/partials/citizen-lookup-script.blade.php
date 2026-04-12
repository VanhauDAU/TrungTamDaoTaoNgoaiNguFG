<script>
    (() => {
        const cccdInput = document.getElementById('cccd');
        const fullNameInput = document.getElementById('hoTen');
        const statusBox = document.getElementById('citizen-lookup-status');

        if (!cccdInput || !fullNameInput || !statusBox) {
            return;
        }

        const endpoint = @json(route('admin.hoc-vien.lookup-citizen'));
        const csrfToken = @json(csrf_token());
        let debounceTimer = null;
        let currentController = null;
        let lastLookupKey = null;

        const normalizeCccd = (value) => String(value || '').replace(/\D/g, '');
        const normalizeName = (value) => String(value || '').trim().replace(/\s+/g, ' ');

        const setStatus = (variant, message, extra = '') => {
            statusBox.className = `citizen-lookup-status is-${variant}`;
            statusBox.innerHTML = `${message}${extra ? `<div class="citizen-lookup-status__meta">${extra}</div>` : ''}`;
        };

        const resetStatus = () => {
            statusBox.className = 'citizen-lookup-status';
            statusBox.innerHTML = '';
        };

        const canLookup = () => {
            const cccd = normalizeCccd(cccdInput.value);
            const fullName = normalizeName(fullNameInput.value);

            if (!cccd && !fullName) {
                resetStatus();
                return false;
            }

            if (fullName.length < 2) {
                setStatus('hint', 'Nhập họ tên để hệ thống đối chiếu CCCD.');
                return false;
            }

            if (cccd.length === 0) {
                setStatus('hint', 'Nhập CCCD/CMND để bắt đầu đối chiếu.');
                return false;
            }

            if (![9, 12].includes(cccd.length)) {
                setStatus('hint', 'CCCD/CMND cần đúng 9 hoặc 12 chữ số để đối chiếu.');
                return false;
            }

            return true;
        };

        const performLookup = async () => {
            if (!canLookup()) {
                lastLookupKey = null;
                if (currentController) {
                    currentController.abort();
                    currentController = null;
                }
                return;
            }

            const cccd = normalizeCccd(cccdInput.value);
            const hoTen = normalizeName(fullNameInput.value);
            const lookupKey = `${cccd}::${hoTen}`;

            if (lookupKey === lastLookupKey) {
                return;
            }

            lastLookupKey = lookupKey;

            if (currentController) {
                currentController.abort();
            }

            currentController = new AbortController();
            setStatus('loading', 'Đang đối chiếu CCCD...');

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        cccd,
                        hoTen,
                    }),
                    signal: currentController.signal,
                });

                const payload = await response.json();

                if (!response.ok) {
                    const errorMessage = payload?.message || Object.values(payload?.errors || {})?.flat()?.[0] || 'Không thể đối chiếu CCCD lúc này.';
                    setStatus('error', errorMessage);
                    return;
                }

                const lookupName = payload?.data?.name ? `Kết quả trả về: ${payload.data.name}` : '';

                switch (payload.status) {
                    case 'matched':
                        setStatus('success', payload.message, lookupName);
                        break;
                    case 'mismatched':
                        setStatus('warning', payload.message, lookupName);
                        break;
                    case 'not_found':
                        setStatus('warning', payload.message);
                        break;
                    case 'rate_limited':
                        setStatus('error', payload.message);
                        break;
                    default:
                        setStatus('error', payload.message || 'Không thể đối chiếu CCCD lúc này.');
                        break;
                }
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                setStatus('error', 'Không thể kết nối dịch vụ đối chiếu CCCD.');
            } finally {
                currentController = null;
            }
        };

        const scheduleLookup = () => {
            clearTimeout(debounceTimer);
            debounceTimer = window.setTimeout(performLookup, 500);
        };

        cccdInput.addEventListener('input', scheduleLookup);
        fullNameInput.addEventListener('input', scheduleLookup);

        if (normalizeCccd(cccdInput.value) && normalizeName(fullNameInput.value)) {
            scheduleLookup();
        }
    })();
</script>
