<aside class="sidebar" id="sidebar">
    <a href="{{ route('teacher.dashboard') }}" class="sidebar-brand">
        <img src="{{ asset('assets/images/logo.png') }}" alt="Logo">
        <span>Five Genius</span>
    </a>

    <nav class="sidebar-nav">
        <div class="sidebar-section">Teacher Portal</div>
        <div class="nav-group open">
            <div class="nav-group-header">
                <i class="fas fa-chalkboard-teacher"></i> <span>Không gian giảng dạy</span>
                <i class="fas fa-chevron-right"></i>
            </div>
            <div class="nav-sub">
                <a href="{{ route('teacher.dashboard') }}" class="nav-sub-item {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('teacher.profile') }}" class="nav-sub-item {{ request()->routeIs('teacher.profile') ? 'active' : '' }}">Hồ sơ</a>
                <a href="{{ route('teacher.classes.index') }}" class="nav-sub-item {{ request()->routeIs('teacher.classes.*') ? 'active' : '' }}">Lớp học của tôi</a>
                <a href="{{ route('teacher.schedule.index') }}" class="nav-sub-item {{ request()->routeIs('teacher.schedule.*') ? 'active' : '' }}">Lịch dạy</a>
                <a href="{{ route('teacher.notifications.index') }}" class="nav-sub-item {{ request()->routeIs('teacher.notifications.*') ? 'active' : '' }}">Thông báo</a>
                <a href="{{ route('teacher.materials.index') }}" class="nav-sub-item {{ request()->routeIs('teacher.materials.*') ? 'active' : '' }}">Tài liệu</a>
                <a href="{{ route('teacher.evaluations.index') }}" class="nav-sub-item {{ request()->routeIs('teacher.evaluations.*') ? 'active' : '' }}">Nhận xét</a>
                <a href="{{ route('teacher.attendance.index') }}" class="nav-sub-item {{ request()->routeIs('teacher.attendance.*') ? 'active' : '' }}">Điểm danh</a>
            </div>
        </div>
    </nav>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">{{ strtoupper(substr(auth()->user()->taiKhoan, 0, 1)) }}</div>
            <div class="user-info">
                <div class="user-name">{{ auth()->user()->hoSoNguoiDung?->hoTen ?? auth()->user()->taiKhoan }}</div>
                <div class="user-role">{{ auth()->user()->getRoleLabel() }}</div>
            </div>
            <form id="internal-logout-form" action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="button" class="btn-logout" id="btn-logout-internal" title="Đăng xuất">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>
    </div>
</aside>
