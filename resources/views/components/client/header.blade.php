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
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <i class="fas fa-bars text-dark"></i>
            </button>

            {{-- MENU CHÍNH --}}
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-4">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home.index') ? 'active' : '' }}" href="{{route('home.index')}}">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Về chúng tôi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Khóa học</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home.blog.index') ? 'active' : '' }}" href="{{ route('home.blog.index') }}">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home.lienhe.index') ? 'active' : '' }}" href="{{route('home.lienhe.index')}}">Liên hệ</a>
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

                    <div class="dropdown notification-hover-wrapper">
                        <button class="icon-btn-modern position-relative" type="button">
                            <i class="fas fa-bell noti-bell-icon"></i>
                            <span class="noti-badge-wrapper">
                                <span class="noti-ping"></span>
                                <span class="noti-dot"></span>
                            </span>
                        </button>
                        
                        <div class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 p-0 overflow-hidden noti-dropdown-custom">
                            <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">

                                <h6 class="mb-0 fw-bold">Thông báo</h6>
                                <a href="#" class="text-primary small text-decoration-none">Đánh dấu đã đọc</a>
                            </div>
                            <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                                <a href="#" class="list-group-item list-group-item-action p-3 border-0 noti-item">
                                    <div class="d-flex gap-3">
                                        <div class="icon-circle bg-primary-soft">
                                            <i class="fas fa-bullhorn text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="mb-1 small fw-semibold">Lớp **IELTS Intensive** sẽ bắt đầu vào tối nay!</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted"><i class="far fa-clock me-1"></i>10 phút trước</small>
                                                <span class="unread-indicator"></span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                </div>
                            <div class="p-2 border-top text-center bg-light">
                                <a href="#" class="text-primary small fw-bold text-decoration-none">Xem tất cả thông báo</a>
                            </div>
                        </div>
                    </div>

                    {{-- USER / AUTH --}}
                    @auth
                        <div class="dropdown">
                            <a class="user-pill d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                                <img src="{{ Auth::user()->avatar ?? asset('images/user-default.png') }}" class="rounded-circle shadow-sm" width="38" height="38">
                                <span class="fw-semibold d-none d-md-block">{{ Auth::user()->name }}</span>
                                <i class="fas fa-chevron-down small opacity-50 ms-1"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-3 rounded-4">
                                <li><a class="dropdown-item py-2" href="#"><i class="far fa-user-circle me-2 text-primary"></i> Hồ sơ</a></li>
                                <li><a class="dropdown-item py-2" href="#"><i class="fas fa-graduation-cap me-2 text-primary"></i> Khóa học của tôi</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="dropdown-item text-danger py-2"><i class="fas fa-power-off me-2"></i> Đăng xuất</button>
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