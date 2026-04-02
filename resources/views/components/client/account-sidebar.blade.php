<div class="col-lg-3">
    <div class="account-sidebar">
        @php
            $isTuitionActive = request()->routeIs('home.student.tuition.*') || request()->routeIs('home.student.invoices*');
        @endphp

        <div class="sidebar-user-info">
            <div class="user-avatar-wrapper">
                <img src="{{ Auth::user()->getAvatarUrl() }}" alt="Avatar" class="sidebar-avatar" data-avatar-image>
            </div>
            <h3 class="user-name">{{ Auth::user()->hoSoNguoiDung->hoTen ?? Auth::user()->name }}</h3>
            <p class="user-email">{{ Auth::user()->email }}</p>
            <p class="user-email">{{ Auth::user()->getAuthProviderLabel() }}</p>
        </div>

        <ul class="sidebar-menu">
            <li class="menu-item">
                <a href="{{ route('home.student.index') }}"
                    class="menu-link {{ request()->routeIs('home.student.index') ? 'active' : '' }}">
                    <i class="fas fa-user-circle"></i>
                    <span>Thông tin cá nhân</span>
                </a>
            </li>
            <li class="menu-item menu-group">
                <details class="menu-accordion" @if ($isTuitionActive) open @endif>
                    <summary class="menu-link menu-link--toggle {{ $isTuitionActive ? 'active' : '' }}">
                        <span class="menu-link__main">
                            <i class="fas fa-wallet"></i>
                            <span>Học phí</span>
                        </span>
                        <i class="fas fa-chevron-down menu-link__caret"></i>
                    </summary>
                    <div class="menu-submenu">
                        <a href="{{ route('home.student.tuition.debts') }}"
                            class="menu-sublink {{ request()->routeIs('home.student.tuition.debts') || request()->routeIs('home.student.invoices*') ? 'active' : '' }}">
                            <i class="fas fa-search-dollar"></i>
                            <span>Tra cứu công nợ</span>
                        </a>
                        <a href="{{ route('home.student.tuition.receipts') }}"
                            class="menu-sublink {{ request()->routeIs('home.student.tuition.receipts') ? 'active' : '' }}">
                            <i class="fas fa-receipt"></i>
                            <span>Phiếu thu tổng hợp</span>
                        </a>
                        <a href="{{ route('home.student.tuition.payments') }}"
                            class="menu-sublink {{ request()->routeIs('home.student.tuition.payments') ? 'active' : '' }}">
                            <i class="fas fa-credit-card"></i>
                            <span>Thanh toán trực tuyến</span>
                        </a>
                    </div>
                </details>
            </li>
            <li class="menu-item">
                <a href="{{ route('home.student.classes') }}"
                    class="menu-link {{ request()->routeIs('home.student.classes') ? 'active' : '' }}">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Lớp học của tôi</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="{{ route('home.student.chat') }}"
                    class="menu-link {{ request()->routeIs('home.student.chat') ? 'active' : '' }}">
                    <i class="fas fa-comments"></i>
                    <span>Chat lớp học</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="{{ route('home.thong-bao.index') }}"
                    class="menu-link {{ request()->routeIs('home.thong-bao.*') ? 'active' : '' }}">
                    <i class="fas fa-bell"></i>
                    <span>Thông báo</span>
                    <span class="menu-badge" id="sidebar-nb-badge"></span>
                </a>
            </li>
            <li class="menu-item">
                <a href="{{ route('home.student.schedule') }}"
                    class="menu-link {{ request()->routeIs('home.student.schedule') ? 'active' : '' }}">

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
            <li class="menu-item">
                <a href="{{ route('home.student.devices') }}"
                    class="menu-link {{ request()->routeIs('home.student.devices*') ? 'active' : '' }}">
                    <i class="fas fa-laptop-house"></i>
                    <span>Thiết bị đã đăng nhập</span>
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
