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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/layout.css') }}">
    @vite(['resources/js/app.js'])

    <style>
        /* ── BELL DROPDOWN ──────────────────────────────────────── */
        .bell-wrapper {
            position: relative;
            display: inline-flex;
        }

        .bell-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ef4444;
            color: #fff;
            font-size: .62rem;
            font-weight: 700;
            min-width: 18px;
            height: 18px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            border: 2px solid #fff;
            animation: bellPop .3s ease;
            display: none;
        }

        @keyframes bellPop {
            0% {
                transform: scale(0)
            }

            80% {
                transform: scale(1.2)
            }

            100% {
                transform: scale(1)
            }
        }

        .bell-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: -12px;
            width: 360px;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .18);
            border: 1px solid #e5e7eb;
            z-index: 9999;
            display: none;
            overflow: hidden;
            animation: dropIn .2s ease;
        }

        @keyframes dropIn {
            from {
                opacity: 0;
                transform: translateY(-8px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        .bell-dropdown.open {
            display: block;
        }

        .bd-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .9rem 1.1rem .75rem;
            border-bottom: 1px solid #f3f4f6;
        }

        .bd-title {
            font-size: .9rem;
            font-weight: 700;
            color: #111827;
        }

        .bd-mark-all {
            font-size: .75rem;
            color: #6366f1;
            font-weight: 600;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
        }

        .bd-mark-all:hover {
            text-decoration: underline;
        }

        .bd-list {
            max-height: 320px;
            overflow-y: auto;
        }

        .bd-item {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            padding: .8rem 1.1rem;
            border-bottom: 1px solid #f9fafb;
            cursor: pointer;
            transition: background .15s;
            text-decoration: none;
            position: relative;
        }

        .bd-item:hover {
            background: #fafafa;
        }

        .bd-item.unread {
            background: #f5f3ff;
        }

        .bd-item.unread::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: #6366f1;
            border-radius: 0 2px 2px 0;
        }

        .bd-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .9rem;
        }

        .bd-icon.he-thong {
            background: #f3f4f6;
            color: #6b7280;
        }

        .bd-icon.hoc-tap {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .bd-icon.tai-chinh {
            background: #d1fae5;
            color: #065f46;
        }

        .bd-icon.su-kien {
            background: #fef3c7;
            color: #92400e;
        }

        .bd-icon.khan-cap {
            background: #fee2e2;
            color: #991b1b;
        }

        .bd-text .bd-tb-title {
            font-size: .82rem;
            font-weight: 600;
            color: #111827;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 240px;
        }

        .bd-text .bd-tb-preview {
            font-size: .75rem;
            color: #9ca3af;
            margin-top: .1rem;
        }

        .bd-text .bd-tb-time {
            font-size: .7rem;
            color: #c4b5fd;
            margin-top: .2rem;
        }

        .bd-item.unread .bd-tb-title {
            color: #4c1d95;
        }

        .bd-footer {
            padding: .7rem 1.1rem;
            border-top: 1px solid #f3f4f6;
            text-align: center;
        }

        .bd-footer a {
            font-size: .8rem;
            font-weight: 600;
            color: #6366f1;
            text-decoration: none;
        }

        .bd-footer a:hover {
            text-decoration: underline;
        }

        .bd-empty {
            text-align: center;
            padding: 2rem 1rem;
            color: #9ca3af;
            font-size: .85rem;
        }

        .bd-loading {
            text-align: center;
            padding: 1.5rem;
            color: #9ca3af;
        }

        /* ── SIDEBAR BADGE ──────────────────────────────────────── */
        .sidebar-badge {
            background: #ef4444;
            color: #fff;
            font-size: .6rem;
            font-weight: 700;
            min-width: 16px;
            height: 16px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            margin-left: auto;
            flex-shrink: 0;
        }

        .nav-sub-item {
            display: flex;
            align-items: center;
        }
    </style>

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

                {{-- Bell dropdown --}}
                <div class="bell-wrapper" id="bellWrapper">
                    <button class="topbar-icon" id="bellBtn" title="Thông báo"
                        style="position:relative;background:none;border:none;cursor:pointer;">
                        <i class="fas fa-bell"></i>
                        <span class="bell-badge" id="bellBadge"></span>
                    </button>
                    <div class="bell-dropdown" id="bellDropdown">
                        <div class="bd-header">
                            <div class="bd-title"><i class="fas fa-bell me-1" style="color:#6366f1;"></i> Thông báo
                            </div>
                            <button class="bd-mark-all" id="markAllBtn"><i class="fas fa-check-double me-1"></i>Đọc tất
                                cả</button>
                        </div>
                        <div class="bd-list" id="bdList">
                            <div class="bd-loading"><i class="fas fa-spinner fa-spin me-1"></i> Đang tải…</div>
                        </div>
                        <div class="bd-footer">
                            <a href="{{ route('admin.thong-bao.index') }}">Xem tất cả thông báo →</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="page-content">
            @yield('content')
        </main>

    </div>

    {{-- Modals (rendered outside flex layout for proper Bootstrap positioning) --}}
    @yield('modal')

    {{-- Bootstrap JS (cho alert dismiss + dropdown) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('partials.auth.session-guard', [
        'sessionGuardContext' => 'staff',
        'sessionGuardLogoutButtonId' => 'btn-logout-admin',
        'sessionGuardLogoutFormId' => 'admin-logout-form',
        'sessionGuardStaleTitle' => 'Phiên nội bộ đã thay đổi',
    ])

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

        // Xử lý Tree-view Sidebar
        document.querySelectorAll('.nav-group-header').forEach(header => {
            header.addEventListener('click', () => {
                const group = header.parentElement;
                const isOpen = group.classList.contains('open');

                // Accordion: chỉ mở 1 nhóm để tránh sidebar quá dài
                document.querySelectorAll('.nav-group').forEach(g => {
                    if (g !== group) g.classList.remove('open');
                });

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

        // Toggle nhóm con "Nghiệp vụ nâng cao" trong menu đào tạo
        document.querySelectorAll('.nav-sub-toggle').forEach(toggle => {
            toggle.addEventListener('click', () => {
                const targetSelector = toggle.getAttribute('data-target');
                const target = targetSelector ? document.querySelector(targetSelector) : null;
                if (!target) return;

                const nextOpen = !toggle.classList.contains('open');
                toggle.classList.toggle('open', nextOpen);
                target.classList.toggle('open', nextOpen);
            });
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
            link.addEventListener('click', function (e) {
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
            form.addEventListener('submit', function (e) {
                // Đợi một chút để các script validation khác (như JOI) chạy trước
                // Nếu form không submit (nghĩa là validation thất bại), không hiện loader
                setTimeout(() => {
                    if (!e.defaultPrevented) {
                        showLoader();
                    }
                }, 10);
            });
        });

        // Ẩn loader khi trang load xong
        window.addEventListener('load', hideLoader);

        // Đề phòng user back lại bằng browser thì tắt loader (safari bfcache)
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                hideLoader();
            }
        });

        // ── BELL DROPDOWN ──────────────────────────────────────────
        const bellBtn = document.getElementById('bellBtn');
        const bellDropdown = document.getElementById('bellDropdown');
        const bellBadge = document.getElementById('bellBadge');
        const bdList = document.getElementById('bdList');
        const markAllBtn = document.getElementById('markAllBtn');
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

        const loaiIconMap = {
            0: {
                cls: 'he-thong',
                icon: 'fa-cog'
            },
            1: {
                cls: 'hoc-tap',
                icon: 'fa-graduation-cap'
            },
            2: {
                cls: 'tai-chinh',
                icon: 'fa-wallet'
            },
            3: {
                cls: 'su-kien',
                icon: 'fa-calendar-alt'
            },
            4: {
                cls: 'khan-cap',
                icon: 'fa-exclamation-triangle'
            },
        };

        function timeAgo(dateStr) {
            const d = new Date(dateStr);
            const now = new Date();
            const diff = Math.floor((now - d) / 1000);
            if (diff < 60) return 'vừa xong';
            if (diff < 3600) return Math.floor(diff / 60) + ' phút trước';
            if (diff < 86400) return Math.floor(diff / 3600) + ' giờ trước';
            return Math.floor(diff / 86400) + ' ngày trước';
        }

        async function refreshBell() {
            try {
                const resp = await fetch('{{ route('admin.api.thong-bao.dropdown') }}');
                const data = await resp.json();

                // Update badge
                if (data.unreadCount > 0) {
                    bellBadge.textContent = data.unreadCount > 99 ? '99+' : data.unreadCount;
                    bellBadge.style.display = 'flex';
                } else {
                    bellBadge.style.display = 'none';
                }

                // Render list
                if (!data.notifications.length) {
                    bdList.innerHTML =
                        '<div class="bd-empty"><i class="fas fa-bell-slash" style="font-size:2rem;color:#d1d5db;display:block;margin-bottom:.5rem;"></i>Không có thông báo mới</div>';
                    return;
                }

                bdList.innerHTML = data.notifications.map(n => {
                    const map = loaiIconMap[n.loaiGui] ?? loaiIconMap[0];
                    const time = n.ngayGui ? timeAgo(n.ngayGui) : '';
                    return `<a href="/admin/thong-bao/${n.thongBaoId}" class="bd-item ${n.daDoc ? '' : 'unread'}"
                                onclick="markRead(event, ${n.thongBaoId}, ${n.thongBaoNguoiDungId}, this)">
                        <div class="bd-icon ${map.cls}"><i class="fas ${map.icon}"></i></div>
                        <div class="bd-text" style="flex:1;min-width:0;">
                            <div class="bd-tb-title">${n.tieuDe}</div>
                            <div class="bd-tb-preview">${n.tomTat}</div>
                            <div class="bd-tb-time">${time}</div>
                        </div>
                    </a>`;
                }).join('');
            } catch (e) {
                console.error('Bell refresh error', e);
            }
        }

        // Toggle dropdown
        if (bellBtn) {
            bellBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                bellDropdown.classList.toggle('open');
                if (bellDropdown.classList.contains('open')) refreshBell();
            });
        }

        // Close on outside click
        document.addEventListener('click', function (e) {
            if (!bellDropdown?.contains(e.target) && e.target !== bellBtn) {
                bellDropdown?.classList.remove('open');
            }
        });

        // Mark one as read
        async function markRead(e, thongBaoId, pivotId, el) {
            if (!el.classList.contains('unread')) return;
            // Don't prevent navigation, just fire async
            el.classList.remove('unread');
            el.querySelector('.bd-item::before');
            fetch(`/admin/api/thong-bao/${thongBaoId}/da-doc`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN
                }
            }).then(() => refreshBell());
        }
        window.markRead = markRead;

        // Mark all read
        if (markAllBtn) {
            markAllBtn.addEventListener('click', async function () {
                await fetch('{{ route('admin.api.thong-bao.mark-all-read') }}', {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    }
                });
                refreshBell();
                Toast.fire({
                    icon: 'success',
                    title: 'Đã đọc tất cả thông báo'
                });
            });
        }

        // Initial load badge (silently)
        refreshBell();
        // Auto-refresh every 60s
        setInterval(refreshBell, 60000);
    </script>

    @yield('script')
</body>

</html>
