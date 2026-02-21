<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị') — {{ config('app.name', 'Five Genius') }}</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('assets/admin/css/layout.css') }}">

    @yield('stylesheet')
</head>

<body>
    {{-- Global Admin Page Loader --}}
    <div id="admin-global-loader" class="admin-global-loader">
        <div class="loader-content">
            <div class="loader-spinner"></div>
            <p class="loader-text">Đang tải...</p>
        </div>
    </div>

    {{-- ──────────────────────── SIDEBAR ──────────────────────── --}}
    <x-admin.sidebar />

    {{-- ──────────────────────── MAIN ──────────────────────── --}}
    <div class="main-wrapper">
        {{-- Topbar --}}
        <header class="topbar">
            <button class="topbar-icon d-lg-none" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div>
                <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
                <div class="topbar-breadcrumb">@yield('breadcrumb', 'Trang chủ quản trị')</div>
            </div>
            <div class="topbar-right">
                <a href="{{ route('home.index') }}" class="topbar-icon" title="Xem trang khách hàng" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                </a>
                <a href="#" class="topbar-icon" title="Thông báo">
                    <i class="fas fa-bell"></i>
                </a>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="page-content">
            @yield('content')
        </main>

    </div>

    {{-- Bootstrap JS (cho alert dismiss + dropdown) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // ── SWEETALERT2 TOAST CONFIG ──────────────────────────
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // Trigger toasts from session flash
        @if (session('success'))
            Toast.fire({
                icon: 'success',
                title: '{{ session('success') }}'
            });
        @endif

        @if (session('error'))
            Toast.fire({
                icon: 'error',
                title: '{{ session('error') }}'
            });
        @endif

        @if (session('warning'))
            Toast.fire({
                icon: 'warning',
                title: '{{ session('warning') }}'
            });
        @endif

        // Toggle sidebar trên mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('open'));
        }

        // Xác nhận đăng xuất bằng SweetAlert2
        document.getElementById('btn-logout-admin')?.addEventListener('click', function() {
            Swal.fire({
                title: 'Đăng xuất?',
                text: 'Bạn có chắc muốn đăng xuất khỏi hệ thống?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-sign-out-alt me-1"></i> Đăng xuất',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#e31e24',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusCancel: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('admin-logout-form').submit();
                }
            });
        });

        // Xử lý Tree-view Sidebar
        document.querySelectorAll('.nav-group-header').forEach(header => {
            header.addEventListener('click', () => {
                const group = header.parentElement;
                const isOpen = group.classList.contains('open');

                // Đóng các group khác (optional - accordion style)
                // document.querySelectorAll('.nav-group').forEach(g => g.classList.remove('open'));

                if (!isOpen) {
                    group.classList.add('open');
                } else {
                    group.classList.remove('open');
                }
            });
        });

        // Tự động mở group chứa link active
        document.addEventListener('DOMContentLoaded', () => {
            const activeLink = document.querySelector('.nav-sub-item.active');
            if (activeLink) {
                const group = activeLink.closest('.nav-group');
                if (group) group.classList.add('open');
            }
        });

        // ── GLOBAL LOADER ──────────────────────────
        const globalLoader = document.getElementById('admin-global-loader');
        let loaderTimeout;

        function showLoader() {
            if (globalLoader) {
                globalLoader.classList.add('active');
                // Auto hide after 10s fallback
                loaderTimeout = setTimeout(() => hideLoader(), 10000);
            }
        }

        function hideLoader() {
            if (globalLoader) {
                globalLoader.classList.remove('active');
                clearTimeout(loaderTimeout);
            }
        }

        // Hiện loader khi user click link chuyển trang (trừ những link có thuộc tính target="_blank" hoặc href "#")
        document.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                const target = this.getAttribute('target');
                if (href && href !== '#' && !href.startsWith('javascript:') && target !== '_blank' && !e
                    .ctrlKey && !e.metaKey) {
                    showLoader();
                }
            });
        });

        // Hiện loader khi submit form truyền thống (không phải ajax)
        document.querySelectorAll('form:not(.ajax-form)').forEach(form => {
            form.addEventListener('submit', function() {
                showLoader();
            });
        });

        // Ẩn loader khi trang load xong
        window.addEventListener('load', hideLoader);

        // Đề phòng user back lại bằng browser thì tắt loader (safari bfcache)
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                hideLoader();
            }
        });
    </script>

    @yield('script')
</body>

</html>
