<script>
    document.querySelector('[data-copy-credentials]')?.addEventListener('click', async () => {
        const username = document.getElementById('handover-username')?.textContent?.trim();
        const password = document.getElementById('handover-password')?.textContent?.trim();

        if (!username || !password || !navigator.clipboard) {
            return;
        }

        await navigator.clipboard.writeText(`Username: ${username}\nPassword: ${password}`);
    });

</script>
