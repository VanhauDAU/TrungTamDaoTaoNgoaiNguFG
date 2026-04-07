<script>
    document.querySelector('[data-copy-credentials]')?.addEventListener('click', async () => {
        const username = document.getElementById('handover-username')?.textContent?.trim();
        const password = document.getElementById('handover-password')?.textContent?.trim();

        if (!username || !password || !navigator.clipboard) {
            return;
        }

        await navigator.clipboard.writeText(`Username: ${username}\nPassword: ${password}`);
    });

    // ===== AVATAR AUTO-SUBMIT =====
    const avatarInput = document.getElementById('avatar-file-input');
    if (avatarInput) {
        avatarInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;

            // Kiểm tra dung lượng tối đa 2MB
            if (file.size > 2 * 1024 * 1024) {
                alert('Ảnh quá lớn! Vui lòng chọn ảnh nhỏ hơn 2MB.');
                this.value = '';
                return;
            }

            // Preview ngay lập tức trước khi upload
            const wrapper  = document.querySelector('.avatar-wrapper');
            const existing = wrapper.querySelector('.avatar-img, .avatar-initials');
            const reader   = new FileReader();
            reader.onload = (e) => {
                if (existing) existing.remove();
                const img = document.createElement('img');
                img.src       = e.target.result;
                img.className = 'avatar-img';
                img.alt       = 'Ảnh đại diện';
                wrapper.prepend(img);
            };
            reader.readAsDataURL(file);

            // Tự động submit form
            document.getElementById('avatar-upload-form').submit();
        });
    }
</script>
