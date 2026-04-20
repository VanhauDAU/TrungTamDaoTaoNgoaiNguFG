<aside class="sidebar" id="sidebar">
    <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
        <img src="{{ asset('assets/images/logo.png') }}" alt="Logo">
        <span>Five Genius</span>
    </a>

    <nav class="sidebar-nav">
        <div class="sidebar-section">Admin Portal</div>
        <div class="nav-group open">
            <div class="nav-group-header">
                <i class="fas fa-shield-halved"></i> <span>Quản trị hệ thống</span>
                <i class="fas fa-chevron-right"></i>
            </div>
            <div class="nav-sub">
                <a href="{{ route('admin.dashboard') }}" class="nav-sub-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('admin.tai-khoan.index') }}" class="nav-sub-item {{ request()->routeIs('admin.tai-khoan.*') ? 'active' : '' }}">Tài khoản hệ thống</a>
                <a href="{{ route('admin.cau-hinh.index') }}" class="nav-sub-item {{ request()->routeIs('admin.cau-hinh.*') ? 'active' : '' }}">Cấu hình</a>
            </div>
        </div>

        <div class="sidebar-section">Dữ liệu nền</div>
        <div class="nav-group {{ request()->routeIs('admin.giao-vien.*', 'admin.nhan-vien.*', 'admin.nhan-su.*') ? 'open' : '' }}">
            <div class="nav-group-header">
                <i class="fas fa-users-cog"></i> <span>Nhân sự</span>
                <i class="fas fa-chevron-right"></i>
            </div>
            <div class="nav-sub">
                <a href="{{ route('admin.giao-vien.index') }}" class="nav-sub-item {{ request()->routeIs('admin.giao-vien.*') ? 'active' : '' }}">Giáo viên</a>
                <a href="{{ route('admin.nhan-vien.index') }}" class="nav-sub-item {{ request()->routeIs('admin.nhan-vien.*') ? 'active' : '' }}">Nhân viên</a>
                <a href="{{ route('admin.nhan-su.mau-quy-dinh.index') }}" class="nav-sub-item {{ request()->routeIs('admin.nhan-su.mau-quy-dinh.*') ? 'active' : '' }}">Mẫu quy định</a>
            </div>
        </div>

        <div class="nav-group {{ request()->routeIs('admin.co-so.*', 'admin.phong-hoc.*', 'admin.ca-hoc.*') ? 'open' : '' }}">
            <div class="nav-group-header">
                <i class="fas fa-building"></i> <span>Cơ sở đào tạo</span>
                <i class="fas fa-chevron-right"></i>
            </div>
            <div class="nav-sub">
                <a href="{{ route('admin.co-so.index') }}" class="nav-sub-item {{ request()->routeIs('admin.co-so.*') ? 'active' : '' }}">Cơ sở</a>
                <a href="{{ route('admin.ca-hoc.index') }}" class="nav-sub-item {{ request()->routeIs('admin.ca-hoc.*') ? 'active' : '' }}">Ca học</a>
            </div>
        </div>

        <div class="nav-group {{ request()->routeIs('admin.danh-muc-khoa-hoc.*', 'admin.khoa-hoc.*') ? 'open' : '' }}">
            <div class="nav-group-header">
                <i class="fas fa-book-open"></i> <span>Khóa học</span>
                <i class="fas fa-chevron-right"></i>
            </div>
            <div class="nav-sub">
                <a href="{{ route('admin.danh-muc-khoa-hoc.index') }}" class="nav-sub-item {{ request()->routeIs('admin.danh-muc-khoa-hoc.*') ? 'active' : '' }}">Danh mục khóa học</a>
                <a href="{{ route('admin.khoa-hoc.index') }}" class="nav-sub-item {{ request()->routeIs('admin.khoa-hoc.*') ? 'active' : '' }}">Khóa học</a>
            </div>
        </div>

        <div class="nav-group {{ request()->routeIs('admin.bai-viet.*', 'admin.danh-muc-bai-viet.*', 'admin.thong-bao.*', 'admin.lien-he.*') ? 'open' : '' }}">
            <div class="nav-group-header">
                <i class="fas fa-layer-group"></i> <span>Nội dung</span>
                <i class="fas fa-chevron-right"></i>
            </div>
            <div class="nav-sub">
                <a href="{{ route('admin.danh-muc-bai-viet.index') }}" class="nav-sub-item {{ request()->routeIs('admin.danh-muc-bai-viet.*') ? 'active' : '' }}">Danh mục bài viết</a>
                <a href="{{ route('admin.bai-viet.index') }}" class="nav-sub-item {{ request()->routeIs('admin.bai-viet.*') ? 'active' : '' }}">Bài viết</a>
                <a href="{{ route('admin.thong-bao.index') }}" class="nav-sub-item {{ request()->routeIs('admin.thong-bao.*') ? 'active' : '' }}">Thông báo</a>
                <a href="{{ route('admin.lien-he.index') }}" class="nav-sub-item {{ request()->routeIs('admin.lien-he.*') ? 'active' : '' }}">Liên hệ</a>
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
