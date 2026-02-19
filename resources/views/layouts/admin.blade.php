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

    <style>
        :root {
            --sidebar-w: 260px;
            --sidebar-bg: #0f1923;
            --sidebar-hover: #1e2d3d;
            --sidebar-active: #10454f;
            --accent: #27c4b5;
            --accent-red: #e31e24;
            --topbar-h: 64px;
            --text-muted: #8899a6;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f4f8;
            color: #1a2b3c;
            display: flex;
            min-height: 100vh;
        }

        /* ── SIDEBAR ─────────────────────────────── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            height: var(--topbar-h);
            display: flex;
            align-items: center;
            padding: 0 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            gap: 12px;
            text-decoration: none;
        }

        .sidebar-brand img {
            height: 36px;
        }

        .sidebar-brand span {
            color: #fff;
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: 0.5px;
        }

        .sidebar-section {
            padding: 20px 16px 8px;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 1.5px;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .sidebar-nav {
            flex: 1;
            padding: 8px 0;
            overflow-y: auto;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 20px;
            color: #b0bec5;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 8px;
            margin: 2px 10px;
            transition: all 0.2s ease;
            position: relative;
        }

        .nav-item i {
            width: 18px;
            text-align: center;
            font-size: 0.9rem;
        }

        .nav-item:hover {
            background: var(--sidebar-hover);
            color: #fff;
        }

        .nav-item.active {
            background: var(--sidebar-active);
            color: var(--accent);
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 20%;
            bottom: 20%;
            width: 3px;
            background: var(--accent);
            border-radius: 0 3px 3px 0;
            margin-left: -10px;
        }

        .nav-badge {
            margin-left: auto;
            background: var(--accent-red);
            color: #fff;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 20px;
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.04);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #fff;
            font-size: 0.8rem;
            flex-shrink: 0;
        }

        .user-info {
            flex: 1;
            overflow: hidden;
        }

        .user-name {
            color: #fff;
            font-size: 0.8rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-role {
            color: var(--text-muted);
            font-size: 0.7rem;
        }

        .btn-logout {
            color: var(--text-muted);
            cursor: pointer;
            transition: color 0.2s;
            background: none;
            border: none;
            font-size: 0.9rem;
        }

        .btn-logout:hover {
            color: var(--accent-red);
        }

        /* ── MAIN CONTENT ────────────────────────── */
        .main-wrapper {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── TOPBAR ──────────────────────────────── */
        .topbar {
            height: var(--topbar-h);
            background: #fff;
            border-bottom: 1px solid #e8edf2;
            display: flex;
            align-items: center;
            padding: 0 28px;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        }

        .topbar-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1a2b3c;
        }

        .topbar-breadcrumb {
            font-size: 0.8rem;
            color: #8899a6;
        }

        .topbar-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f4f8;
            color: #4a5568;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            border: none;
        }

        .topbar-icon:hover {
            background: #e2e8f0;
            color: #1a2b3c;
        }

        /* ── PAGE CONTENT ────────────────────────── */
        .page-content {
            flex: 1;
            padding: 28px;
        }

        /* ── ALERTS ──────────────────────────────── */
        .alert-stack {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-width: 300px;
        }

        /* ── RESPONSIVE ──────────────────────────── */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-wrapper {
                margin-left: 0;
            }
        }
    </style>

    @yield('stylesheet')
</head>

<body>

    {{-- ──────────────────────── SIDEBAR ──────────────────────── --}}
    <aside class="sidebar" id="sidebar">
        {{-- Brand --}}
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
            <img src="{{ asset('assets/images/logo.png') }}" alt="Logo">
            <span>Five Genius</span>
        </a>

        {{-- Navigation --}}
        <nav class="sidebar-nav">
            <div class="sidebar-section">Tổng quan</div>
            <a href="{{ route('admin.dashboard') }}"
                class="nav-item {{ Request::is('admin/dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-pie"></i> Dashboard
            </a>

            <div class="sidebar-section">Học vụ</div>
            <a href="#" class="nav-item {{ Request::is('admin/khoa-hoc*') ? 'active' : '' }}">
                <i class="fas fa-book-open"></i> Khoá học
            </a>
            <a href="#" class="nav-item {{ Request::is('admin/lop-hoc*') ? 'active' : '' }}">
                <i class="fas fa-chalkboard-teacher"></i> Lớp học
            </a>
            <a href="#" class="nav-item {{ Request::is('admin/hoc-vien*') ? 'active' : '' }}">
                <i class="fas fa-user-graduate"></i> Học viên
            </a>
            <a href="#" class="nav-item {{ Request::is('admin/giao-vien*') ? 'active' : '' }}">
                <i class="fas fa-chalkboard"></i> Giáo viên
            </a>

            <div class="sidebar-section">Vận hành</div>
            <a href="#" class="nav-item {{ Request::is('admin/nhan-vien*') ? 'active' : '' }}">
                <i class="fas fa-users-cog"></i> Nhân viên
            </a>
            <a href="#" class="nav-item {{ Request::is('admin/tai-chinh*') ? 'active' : '' }}">
                <i class="fas fa-wallet"></i> Tài chính
            </a>
            <a href="#" class="nav-item {{ Request::is('admin/dang-ky*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i> Đăng ký học
            </a>

            @if (auth()->user()->isAdmin())
                <div class="sidebar-section">Hệ thống</div>
                <a href="#" class="nav-item {{ Request::is('admin/tai-khoan*') ? 'active' : '' }}">
                    <i class="fas fa-user-shield"></i> Tài khoản
                </a>
                <a href="#" class="nav-item {{ Request::is('admin/cai-dat*') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i> Cài đặt
                </a>
            @endif
        </nav>

        {{-- User Footer --}}
        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar">
                    {{ strtoupper(substr(auth()->user()->hoSoNguoiDung->hoTen ?? auth()->user()->taiKhoan, 0, 1)) }}
                </div>
                <div class="user-info">
                    <div class="user-name">
                        {{ auth()->user()->hoSoNguoiDung->hoTen ?? (auth()->user()->nhanSu->hoTen ?? auth()->user()->taiKhoan) }}
                    </div>
                    <div class="user-role">{{ auth()->user()->getRoleLabel() }}</div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-logout" title="Đăng xuất">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

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

        {{-- Flash messages --}}
        <div class="alert-stack">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>

        {{-- Page Content --}}
        <main class="page-content">
            @yield('content')
        </main>

    </div>

    {{-- Bootstrap JS (cho alert dismiss + dropdown) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle sidebar trên mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('open'));
        }

        // Auto dismiss alert sau 4 giây
        document.querySelectorAll('.alert').forEach(el => {
            setTimeout(() => {
                el.classList.remove('show');
                setTimeout(() => el.remove(), 300);
            }, 4000);
        });
    </script>

    @yield('script')
</body>

</html>
