<script>
    (() => {
        const addButton = document.querySelector('[data-add-detail]');
        const detailLines = document.querySelector('[data-detail-lines]');

        addButton?.addEventListener('click', () => {
            const firstRow = detailLines?.querySelector('.staff-detail-row');
            if (!detailLines || !firstRow) {
                return;
            }

            const clone = firstRow.cloneNode(true);
            clone.querySelectorAll('input, select').forEach((element) => {
                element.value = '';
            });
            detailLines.appendChild(clone);
        });

        detailLines?.addEventListener('click', (event) => {
            const removeButton = event.target.closest('[data-remove-detail]');
            if (!removeButton) {
                return;
            }

            const rows = detailLines.querySelectorAll('.staff-detail-row');
            if (rows.length <= 1) {
                rows[0]?.querySelectorAll('input, select').forEach((element) => {
                    element.value = '';
                });
                return;
            }

            removeButton.closest('.staff-detail-row')?.remove();
        });
    })();

    @if ((int) $role === \App\Models\Auth\TaiKhoan::ROLE_GIAO_VIEN)
        (() => {
            const allBranches = @json($coSosData);
            const selectedProvince = @json((string) ($selectedTinhThanhId ?? ''));
            const selectedWard = @json((string) ($selectedPhuongXa ?? ''));
            const selectedBranchId = @json((string) ($selectedCoSoId ?? ''));
            const provinceSelect = document.getElementById('selectedTinhThanhId');
            const wardSelect = document.getElementById('selectedPhuongXa');
            const branchSelect = document.getElementById('selectedCoSo');
            const hiddenBranchInput = document.getElementById('coSoId');
            const preview = document.getElementById('staff-cascade-preview-text');

            const updatePreview = () => {
                const branch = allBranches.find(item => String(item.coSoId) === String(hiddenBranchInput?.value || ''));

                if (!preview) {
                    return;
                }

                preview.textContent = branch
                    ? `${branch.tenCoSo} - ${[branch.diaChi, branch.tenPhuongXa].filter(Boolean).join(', ')}`
                    : 'Chưa chọn cơ sở làm việc.';
            };

            const fillBranches = (provinceId, wardCode, branchId = '') => {
                if (!branchSelect || !hiddenBranchInput) {
                    return;
                }

                const items = allBranches.filter(item =>
                    String(item.tinhThanhId) === String(provinceId) && String(item.maPhuongXa || '') === String(wardCode || '')
                );

                branchSelect.innerHTML = '<option value="">Chọn cơ sở</option>';
                items.forEach((branch) => {
                    const option = document.createElement('option');
                    option.value = branch.coSoId;
                    option.textContent = `${branch.tenCoSo} - ${[branch.diaChi, branch.tenPhuongXa].filter(Boolean).join(', ')}`;
                    if (String(branch.coSoId) === String(branchId)) {
                        option.selected = true;
                        hiddenBranchInput.value = branch.coSoId;
                    }
                    branchSelect.appendChild(option);
                });

                if (!items.length) {
                    hiddenBranchInput.value = '';
                }

                updatePreview();
            };

            const fillWards = (provinceId, wardCode = '', branchId = '') => {
                if (!wardSelect) {
                    return;
                }

                const items = allBranches.filter(item => String(item.tinhThanhId) === String(provinceId));
                const uniqueWards = [];
                const wardMap = new Map();

                items.forEach((branch) => {
                    if (!branch.maPhuongXa) {
                        return;
                    }

                    if (!wardMap.has(branch.maPhuongXa)) {
                        wardMap.set(branch.maPhuongXa, branch.tenPhuongXa);
                        uniqueWards.push({
                            code: branch.maPhuongXa,
                            label: branch.tenPhuongXa || branch.maPhuongXa,
                        });
                    }
                });

                wardSelect.innerHTML = '<option value="">Chọn phường / xã</option>';
                uniqueWards.forEach((ward) => {
                    const option = document.createElement('option');
                    option.value = ward.code;
                    option.textContent = ward.label;
                    if (String(ward.code) === String(wardCode)) {
                        option.selected = true;
                    }
                    wardSelect.appendChild(option);
                });

                fillBranches(provinceId, wardCode, branchId);
            };

            provinceSelect?.addEventListener('change', () => {
                hiddenBranchInput.value = '';
                fillWards(provinceSelect.value);
            });

            wardSelect?.addEventListener('change', () => {
                hiddenBranchInput.value = '';
                fillBranches(provinceSelect?.value, wardSelect.value);
            });

            branchSelect?.addEventListener('change', () => {
                if (hiddenBranchInput) {
                    hiddenBranchInput.value = branchSelect.value;
                }
                updatePreview();
            });

            if (provinceSelect && selectedProvince) {
                provinceSelect.value = selectedProvince;
                fillWards(selectedProvince, selectedWard, selectedBranchId);
            } else {
                updatePreview();
            }
        })();
    @endif
</script>
