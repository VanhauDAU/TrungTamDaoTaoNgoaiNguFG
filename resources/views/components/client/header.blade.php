<header class="client-header fixed-top transition-all">
    <nav class="navbar navbar-expand-lg py-3">
        <div class="container-fluid px-lg-5 px-3">

            {{-- LOGO --}}
            <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                <div class="logo-wrapper me-2">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" height="48">
                </div>
            </a>

            {{-- TOGGLE MOBILE --}}
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#mainNavbar">
                <i class="fas fa-bars text-dark"></i>
            </button>

            {{-- MENU CHÍNH --}}
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-4">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home.index') ? 'active' : '' }}"
                            href="{{ route('home.index') }}">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home.about.index') ? 'active' : '' }}"
                            href="{{ route('home.about.index') }}">Về chúng tôi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home.courses.index') ? 'active' : '' }}"
                            href="{{ route('home.courses.index') }}">Khóa học</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home.blog.index') ? 'active' : '' }}"
                            href="{{ route('home.blog.index') }}">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home.contact.index') ? 'active' : '' }}"
                            href="{{ route('home.contact.index') }}">Liên hệ</a>
                    </li>
                </ul>

                {{-- BÊN PHẢI --}}
                <div class="d-flex align-items-center gap-3">

                    {{-- SEARCH GỌN GÀNG --}}
                    <div class="search-box-modern d-none d-xl-block">
                        <form class="position-relative">
                            <input type="text" class="search-input" placeholder="Bạn muốn học gì?">
                            <i class="fas fa-search search-icon"></i>
                        </form>
                    </div>

                    <div class="dropdown notification-hover-wrapper position-relative">
                        <button class="icon-btn-modern position-relative" type="button" id="client-bell-btn"
                            data-bs-toggle="dropdown" aria-expanded="false" aria-label="Thông báo">
                            <i class="fas fa-bell noti-bell-icon"></i>
                            {{-- Badge số thông báo chưa đọc (JS controlled) --}}
                            <span class="noti-badge-count" id="client-bell-badge"></span>
                        </button>

                        {{-- Dropdown panel --}}
                        <div class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 p-0 overflow-hidden noti-dropdown-custom"
                            id="client-bell-dropdown" style="width:360px; min-width:360px; margin-top:.5rem;">

                            {{-- Header --}}
                            <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fas fa-bell me-1" style="color:#6366f1;"></i> Thông báo
                                </h6>
                                @auth
                                    <button id="client-mark-all-btn"
                                        class="text-primary small border-0 bg-transparent fw-semibold p-0"
                                        style="cursor:pointer;" onclick="markAllRead()">
                                        <i class="fas fa-check-double me-1"></i>Đọc tất cả
                                    </button>
                                @endauth
                            </div>

                            {{-- List (populated by JS) --}}
                            <div class="list-group list-group-flush" id="client-bell-list"
                                style="max-height:380px; overflow-y:auto;">
                                <div style="text-align:center;padding:1.5rem;color:#9ca3af;">
                                    <i class="fas fa-spinner fa-spin me-1"></i> Đang tải…
                                </div>
                            </div>

                            {{-- Footer --}}
                            @auth
                                <div class="p-2 border-top text-center bg-light">
                                    <a href="{{ route('home.thong-bao.index') }}"
                                        class="text-primary small fw-bold text-decoration-none">
                                        Xem tất cả thông báo →
                                    </a>
                                </div>
                            @else
                                <div class="p-2 border-top text-center bg-light">
                                    <a href="{{ route('login') }}" class="text-primary small fw-bold text-decoration-none">
                                        Đăng nhập để xem thông báo
                                    </a>
                                </div>
                            @endauth
                        </div>
                    </div>


                    {{-- USER / AUTH --}}
                    @auth
                        <div class="dropdown">
                            <a class="user-pill d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                                @if (Auth::user()->hoSoNguoiDung && Auth::user()->hoSoNguoiDung->anhDaiDien)
                                    <img src="{{ asset('storage/' . Auth::user()->hoSoNguoiDung->anhDaiDien) }}"
                                        class="rounded-circle shadow-sm" width="38" height="38"
                                        style="object-fit: cover;">
                                @else
                                    <img src="{{ asset('assets/images/user-default.png') }}"
                                        class="rounded-circle shadow-sm" width="38" height="38">
                                @endif
                                <span
                                    class="fw-semibold d-none d-md-block">{{ Auth::user()->hoSoNguoiDung->hoTen ?? Auth::user()->name }}</span>
                                <i class="fas fa-chevron-down small opacity-50 ms-1"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-user border-0 shadow-lg rounded-4">
                                <li><a class="dropdown-item py-2" href="{{ route('home.student.index') }}"><i
                                            class="far fa-user-circle me-2 text-primary"></i>Tài khoản cá nhân</a></li>
                                <li><a class="dropdown-item py-2" href="#"><i
                                            class="fas fa-graduation-cap me-2 text-primary"></i> Khóa học của tôi</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="dropdown-item text-danger py-2"><i class="fas fa-power-off me-2"></i>
                                            Đăng xuất</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <div class="d-flex gap-2">
                            <a href="{{ route('login') }}" class="btn btn-login fw-bold">Đăng nhập</a>
                            <a href="{{ route('register') }}" class="btn btn-primary-genius px-4">Đăng ký</a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
</header>
