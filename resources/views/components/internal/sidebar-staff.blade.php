<aside class="sidebar" id="sidebar">
    <a href="{{ route('staff.dashboard') }}" class="sidebar-brand">
        <img src="{{ asset('assets/images/logo.png') }}" alt="Logo">
        <span>Five Genius</span>
    </a>

    <nav class="sidebar-nav">
        <div class="sidebar-section">Staff Portal</div>
        <div class="nav-group open">
            <div class="nav-group-header">
                <i class="fas fa-briefcase"></i> <span>Vận hành đào tạo</span>
                <i class="fas fa-chevron-right"></i>
            </div>
            <div class="nav-sub">
                <a href="{{ route('staff.dashboard') }}" class="nav-sub-item {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('staff.hoc-vien.index') }}" class="nav-sub-item {{ request()->routeIs('staff.hoc-vien.*') ? 'active' : '' }}">Học viên</a>
                <a href="{{ route('staff.dang-ky.index') }}" class="nav-sub-item {{ request()->routeIs('staff.dang-ky.*') ? 'active' : '' }}">Đăng ký học</a>
                <a href="{{ route('staff.lop-hoc.index') }}" class="nav-sub-item {{ request()->routeIs('staff.lop-hoc.*', 'staff.buoi-hoc.*') ? 'active' : '' }}">Lớp học</a>
                <a href="{{ route('staff.hoa-don.index') }}" class="nav-sub-item {{ request()->routeIs('staff.hoa-don.*') ? 'active' : '' }}">Hóa đơn & phiếu thu</a>
                <a href="{{ route('staff.notifications.index') }}" class="nav-sub-item {{ request()->routeIs('staff.notifications.*') ? 'active' : '' }}">Thông báo</a>
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
