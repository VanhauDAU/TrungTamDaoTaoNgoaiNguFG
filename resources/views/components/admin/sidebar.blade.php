<aside class="sidebar" id="sidebar">
    {{-- Brand --}}
    <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
        <img src="{{ asset('assets/images/logo.png') }}" alt="Logo">
        <span>Five Genius</span>
    </a>

    {{-- Navigation --}}
    <nav class="sidebar-nav">

        {{-- 1. Nhóm Tổng Quan (Analytics) --}}
        <div class="sidebar-section">Analytics</div>
        <div class="nav-group {{ Request::is('admin/dashboard*') ? 'open' : '' }}">
            <div class="nav-group-header">
                <i class="fas fa-chart-line"></i> <span>Tổng quan</span>
                <i class="fas fa-chevron-right"></i>
            </div>
            <div class="nav-sub">
                <a href="{{ route('admin.dashboard') }}"
                    class="nav-sub-item {{ Request::is('admin/dashboard') ? 'active' : '' }}">
                    Dashboard
                </a>
            </div>
        </div>

        {{-- 2. Quản Lý Đào Tạo (Academic Management) --}}
        <div class="sidebar-section">Academic Management</div>
        <div
            class="nav-group {{ Request::is('admin/khoa-hoc*', 'admin/lop-hoc*', 'admin/lich-hoc*', 'admin/ky-thi*') ? 'open' : '' }}">
            <div class="nav-group-header">
                <i class="fas fa-graduation-cap"></i> <span>Quản lý đào tạo</span>
                <i class="fas fa-chevron-right"></i>
            </div>
            <div class="nav-sub">
                @if (auth()->user()->canDo('khoa_hoc', 'xem'))
                    <a href="#" class="nav-sub-item {{ Request::is('admin/khoa-hoc*') ? 'active' : '' }}">
                        Khóa Học
                    </a>
                @endif
                @if (auth()->user()->canDo('lop_hoc', 'xem'))
                    <a href="#" class="nav-sub-item {{ Request::is('admin/lop-hoc*') ? 'active' : '' }}">
                        Lớp Học
                    </a>
                @endif
                <a href="#" class="nav-sub-item {{ Request::is('admin/lich-hoc*') ? 'active' : '' }}">
                    Lịch Học & Điểm Danh
                </a>
                <a href="#" class="nav-sub-item {{ Request::is('admin/ky-thi*') ? 'active' : '' }}">
                    Kỳ Thi & Điểm Số
                </a>
            </div>
        </div>

        {{-- 3. Quản Lý Học Viên (Student Management) --}}
        <div class="sidebar-section">Student Management</div>
        <div class="nav-group {{ Request::is('admin/hoc-vien*', 'admin/dang-ky*', 'admin/phan-hoi*') ? 'open' : '' }}">
            <div class="nav-group-header">
                <i class="fas fa-user-graduate"></i> <span>Quản lý học viên</span>
                <i class="fas fa-chevron-right"></i>
            </div>
            <div class="nav-sub">
                @if (auth()->user()->canDo('hoc_vien', 'xem'))
                    <a href="{{ route('admin.hoc-vien.index') }}"
                        class="nav-sub-item {{ Request::is('admin/hoc-vien*') ? 'active' : '' }}">
                        Danh Sách Học Viên
                    </a>
                @endif
                @if (auth()->user()->canDo('dang_ky', 'xem'))
                    <a href="#" class="nav-sub-item {{ Request::is('admin/dang-ky*') ? 'active' : '' }}">
                        Đăng Ký Học
                    </a>
                @endif
                <a href="#" class="nav-sub-item {{ Request::is('admin/phan-hoi*') ? 'active' : '' }}">
                    Chăm Sóc & Phản Hồi
                </a>
            </div>
        </div>

        {{-- 4. Quản Lý Nhân Sự (Staff & HR) --}}
        <div class="sidebar-section">Staff & HR Management</div>
        <div class="nav-group {{ Request::is('admin/giao-vien*', 'admin/nhan-vien*', 'admin/ho-so*') ? 'open' : '' }}">
            <div class="nav-group-header">
                <i class="fas fa-users-cog"></i> <span>Quản lý nhân sự</span>
                <i class="fas fa-chevron-right"></i>
            </div>
            <div class="nav-sub">
                @if (auth()->user()->canDo('giao_vien', 'xem'))
                    <a href="{{ route('admin.giao-vien.index') }}"
                        class="nav-sub-item {{ Request::is('admin/giao-vien*') ? 'active' : '' }}">
                        Giáo Viên
                    </a>
                @endif
                @if (auth()->user()->canDo('nhan_vien', 'xem'))
                    <a href="#" class="nav-sub-item {{ Request::is('admin/nhan-vien*') ? 'active' : '' }}">
                        Nhân Viên
                    </a>
                @endif
                <a href="#" class="nav-sub-item {{ Request::is('admin/ho-so*') ? 'active' : '' }}">
                    Chỉnh Sửa Hồ Sơ
                </a>
            </div>
        </div>

        {{-- 5. Quản Lý Tài Chính (Finance Management) --}}
        <div class="sidebar-section">Finance Management</div>
        <div class="nav-group {{ Request::is('admin/tai-chinh*', 'admin/luong*', 'admin/hoc-phi*') ? 'open' : '' }}">
            <div class="nav-group-header">
                <i class="fas fa-wallet"></i> <span>Quản lý tài chính</span>
                <i class="fas fa-chevron-right"></i>
            </div>
            <div class="nav-sub">
                @if (auth()->user()->canDo('tai_chinh', 'xem'))
                    <a href="#" class="nav-sub-item {{ Request::is('admin/tai-chinh*') ? 'active' : '' }}">
                        Hóa Đơn & Phiếu Thu
                    </a>
                @endif
                <a href="#" class="nav-sub-item {{ Request::is('admin/luong*') ? 'active' : '' }}">
                    Quản Lý Lương
                </a>
                <a href="#" class="nav-sub-item {{ Request::is('admin/hoc-phi*') ? 'active' : '' }}">
                    Cấu Hình Học Phí
                </a>
            </div>
        </div>

        {{-- 6. Nội Dung & Tương Tác (CMS & Interaction) --}}
        <div class="sidebar-section">CMS & Interaction</div>
        <div class="nav-group {{ Request::is('admin/tin-tuc*', 'admin/thong-bao*', 'admin/lien-he*') ? 'open' : '' }}">
            <div class="nav-group-header">
                <i class="fas fa-newspaper"></i> <span>Nội dung & Tương tác</span>
                <i class="fas fa-chevron-right"></i>
            </div>
            <div class="nav-sub">
                <a href="#" class="nav-sub-item {{ Request::is('admin/tin-tuc*') ? 'active' : '' }}">
                    Tin Tức / Blog
                </a>
                <a href="#" class="nav-sub-item {{ Request::is('admin/thong-bao*') ? 'active' : '' }}">
                    Thông Báo
                </a>
                <a href="#" class="nav-sub-item {{ Request::is('admin/lien-he*') ? 'active' : '' }}">
                    Liên Hệ (Leads)
                </a>
            </div>
        </div>

        {{-- 7. Cấu Hình Hệ Thống (System Settings) --}}
        @if (auth()->user()->isAdmin())
            <div class="sidebar-section">System Settings</div>
            <div
                class="nav-group {{ Request::is('admin/phan-quyen*', 'admin/tai-khoan*', 'admin/co-so*', 'admin/cai-dat*') ? 'open' : '' }}">
                <div class="nav-group-header">
                    <i class="fas fa-cogs"></i> <span>Cấu hình hệ thống</span>
                    <i class="fas fa-chevron-right"></i>
                </div>
                <div class="nav-sub">
                    <a href="{{ route('admin.phan-quyen.index') }}"
                        class="nav-sub-item {{ Request::is('admin/phan-quyen*') ? 'active' : '' }}">
                        Phân Quyền (Roles)
                    </a>
                    @if (auth()->user()->canDo('tai_khoan', 'xem'))
                        <a href="#" class="nav-sub-item {{ Request::is('admin/tai-khoan*') ? 'active' : '' }}">
                            Tài Khoản
                        </a>
                    @endif
                    <a href="{{ route('admin.co-so.index') }}"
                        class="nav-sub-item {{ Request::is('admin/co-so*') ? 'active' : '' }}">
                        Cơ Sở & Phòng Học
                    </a>
                    @if (auth()->user()->canDo('cai_dat', 'xem'))
                        <a href="#" class="nav-sub-item {{ Request::is('admin/cai-dat*') ? 'active' : '' }}">
                            Cài Đặt Chung
                        </a>
                    @endif
                </div>
            </div>
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
            <form id="admin-logout-form" action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="button" class="btn-logout" id="btn-logout-admin" title="Đăng xuất">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>
    </div>
</aside>
