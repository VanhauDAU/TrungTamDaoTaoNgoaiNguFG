document.addEventListener('DOMContentLoaded', function () {

    /* =========================
       1. Sticky header on scroll
    ========================= */
    const header = document.querySelector('.client-header');

    window.addEventListener('scroll', () => {
        if (window.scrollY > 30) {
            header.classList.add('header-scrolled');
        } else {
            header.classList.remove('header-scrolled');
        }
    });

    /* =====================================
       2. Dropdown hover (Desktop only)
    ===================================== */
    if (window.innerWidth >= 992) {
        document.querySelectorAll('.dropdown').forEach(dropdown => {
            dropdown.addEventListener('mouseenter', () => {
                const toggle = dropdown.querySelector('[data-bs-toggle="dropdown"]');
                const menu = dropdown.querySelector('.dropdown-menu');
                if (toggle && menu) {
                    bootstrap.Dropdown.getOrCreateInstance(toggle).show();
                }
            });

            dropdown.addEventListener('mouseleave', () => {
                const toggle = dropdown.querySelector('[data-bs-toggle="dropdown"]');
                if (toggle) {
                    bootstrap.Dropdown.getOrCreateInstance(toggle).hide();
                }
            });
        });
    }

    /* =====================================
       3. Notification click toggle
    ===================================== */
    const notifyBtn = document.querySelector('.icon-btn-modern');

    if (notifyBtn) {
        notifyBtn.addEventListener('click', function (e) {
            e.stopPropagation();
        });

        document.addEventListener('click', function () {
            const dropdown = bootstrap.Dropdown.getInstance(notifyBtn);
            if (dropdown) dropdown.hide();
        });
    }

    /* =====================================
       4. Search box animation
    ===================================== */
    const searchInput = document.querySelector('.search-input');

    if (searchInput) {
        searchInput.addEventListener('focus', () => {
            searchInput.closest('form').classList.add('search-active');
        });

        searchInput.addEventListener('blur', () => {
            searchInput.closest('form').classList.remove('search-active');
        });
    }

});
