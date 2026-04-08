<script>
    // Copy credentials logic
    document.querySelector('[data-copy-credentials]')?.addEventListener('click', async () => {
        const username = document.getElementById('handover-username')?.textContent?.trim();
        const password = document.getElementById('handover-password')?.textContent?.trim();

        if (!username || !password || !navigator.clipboard) {
            return;
        }

        try {
            await navigator.clipboard.writeText(`Username: ${username}\nPassword: ${password}`);
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Đã sao chép tài khoản',
                showConfirmButton: false,
                timer: 2000
            });
        } catch (err) {
            console.error('Failed to copy: ', err);
        }
    });
</script>