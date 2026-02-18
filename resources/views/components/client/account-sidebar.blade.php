<div class="col-lg-3">
    <div class="account-sidebar">
        <div class="sidebar-user-info">
            <div class="user-avatar-wrapper">
                @if (Auth::user()->hoSoNguoiDung && Auth::user()->hoSoNguoiDung->anhDaiDien)
                    <img src="{{ asset('storage/avatars/' . Auth::user()->hoSoNguoiDung->anhDaiDien) }}" alt="Avatar"
                        class="sidebar-avatar">
                @else
                    <img src="{{ asset('assets/images/user-default.png') }}" alt="Avatar" class="sidebar-avatar">
                @endif
            </div>
            <h3 class="user-name">{{ Auth::user()->hoSoNguoiDung->hoTen ?? Auth::user()->name }}</h3>
            <p class="user-email">{{ Auth::user()->email }}</p>
        </div>

        <ul class="sidebar-menu">
            <li class="menu-item">
                <a href="{{ route('home.student.index') }}"
                    class="menu-link {{ request()->routeIs('home.student.index') ? 'active' : '' }}">
                    <i class="fas fa-user-circle"></i>
                    <span>Thông tin cá nhân</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="{{ route('home.student.invoices') }}"
                    class="menu-link {{ request()->routeIs('home.student.invoices*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice"></i>
                    <span>Hóa đơn thanh toán</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="{{ route('home.student.classes') }}"
                    class="menu-link {{ request()->routeIs('home.student.classes') ? 'active' : '' }}">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Lớp học của tôi</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="menu-link">
                    <i class="far fa-calendar-alt"></i>
                    <span>Lịch học</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="{{ route('home.student.change-password') }}"
                    class="menu-link {{ request()->routeIs('home.student.change-password') ? 'active' : '' }}">
                    <i class="fas fa-lock"></i>
                    <span>Đổi mật khẩu</span>
                </a>
            </li>
            <li class="menu-item mt-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="menu-link w-100 border-0 bg-transparent text-danger">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Đăng xuất</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>
</div>
