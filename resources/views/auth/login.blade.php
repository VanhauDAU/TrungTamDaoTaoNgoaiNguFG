@extends('layouts.auth')

@section('title', ($portalTitle ?? 'Đăng nhập') . ' - Five Genius')
@section('stylesheet')
    <style>
        /* ===== LOGIN PAGE CSS ===== */

        :root {
            --primary-green: #10454f;
            --primary-red: #e31e24;
            --accent-teal: #27c4b5;
            --text-gray: #636e72;
        }

        @font-face {
            font-family: 'TitleFont';
            src: url('https://fonts.googleapis.com/css2?family=Montserrat:wght@700&display=swap');
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #fff;
            margin: 0;
            padding: 0;
        }

        .cl-green {
            color: var(--primary-green) !important;
        }

        .ff-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
        }

        .fs-48 {
            font-size: 3rem;
        }

        .ls-1 {
            letter-spacing: 1px;
        }

        /* Page Layout */
        .page_login {
            position: relative;
            min-height: 100vh;
        }

        .logo_abs {
            position: absolute;
            top: 30px;
            left: 40px;
            z-index: 10;
        }

        .logo_abs img {
            height: 60px;
        }

        /* Side Image */
        .img_login {
            height: 100vh;
            width: 100%;
            padding: 30px;
            /* Tạo khoảng cách để ảnh nổi hẳn lên */
            background-color: #fff;
        }

        .img_login img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 40px;
            /* Bo góc lớn cho cảm giác mềm mại, tự nhiên */
            box-shadow: 0 25px 50px -12px rgba(16, 69, 79, 0.25);
            /* Bóng đổ đổ sâu và dịu, tông xanh nhẹ của brand */
            transition: transform 0.5s ease;
        }

        .img_login img:hover {
            transform: scale(1.02);
            /* Hiệu ứng nổi nhẹ khi di chuột vào */
        }

        /* Form Container */
        .page_login .row.gx-0 {
            height: 100vh;
        }

        .page_login .container-fluid {
            padding: 0;
        }

        .auth-form-shell {
            position: relative;
            z-index: 2;
        }

        /* Form Elements */
        .form-control {
            padding: 13px 20px;
            background-color: #f2f6f7;
            /* Màu nền mới theo yêu cầu */
            border: 1px solid transparent;
            /* Loại bỏ viền thô */
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            color: var(--primary-green);
        }

        .form-control:focus {
            background-color: #fff;
            /* Khi gõ thì nền trắng lại cho dễ nhìn */
            border-color: var(--accent-teal);
            box-shadow: 0 10px 20px rgba(39, 196, 181, 0.1);
        }

        .btn-red {
            background-color: var(--primary-red);
            color: #fff;
            padding: 12px 30px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
        }

        .btn-red:hover {
            background-color: #c4191f;
            transform: translateY(-2px);
            color: #fff;
            box-shadow: 0 5px 15px rgba(227, 30, 36, 0.3);
        }
        .btn-red:disabled {
            background-color: #e2e8f0; /* Màu nền xám nhạt chuyên nghiệp */
            color: #94a3b8; /* Chữ xám đậm hơn nền một chút */
            cursor: not-allowed;
            box-shadow: none; /* Bỏ bóng đổ */
            transform: none; /* Không nhảy nút khi hover */
            pointer-events: auto; /* Bắt buộc để hiện con trỏ not-allowed */
        }

        /* Password Box */
        .password_box {
            position: relative;
        }

        .password_box .show_text {
            position: absolute;
            right: 20px;
            /* Điều chỉnh lại vị trí để cân đối */
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-gray);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Remember Box */
        .remember_box {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .remember_box input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .remember_box label {
            cursor: pointer;
            font-size: 0.9rem;
        }

                .portal-selector {
            display: flex;
            background: #f1f5f9;
            border-radius: 99px;
            padding: 6px;
            margin-bottom: 2rem;
            gap: 4px;
        }

        .portal-tab {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 8px;
            color: var(--text-gray);
            font-weight: 600;
            font-size: 0.95rem;
            border-radius: 99px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .portal-tab:hover {
            color: var(--primary-green) !important;
            background: rgba(16, 69, 79, 0.05);
        }

        .portal-tab.active {
            background: #fff;
            color: var(--primary-green) !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .portal-tab i {
            font-size: 1.1em;
        }

        /* Link Style */
        a {
            transition: all 0.3s ease;
            text-decoration: none;
        }

        a:hover {
            color: var(--primary-red) !important;
        }

                .social-login-divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
        }

        .social-login-divider::before,
        .social-login-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e1e8ed;
        }

        .social-login-divider span {
            padding: 0 10px;
            color: #8c98a4;
            font-size: 0.85rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 12px 16px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            text-decoration: none;
            margin-bottom: 10px;
            border: 1px solid transparent;
        }

        .btn-google {
            background-color: #fff;
            color: #3c4043;
            border-color: #dadce0;
        }

        .btn-google:hover {
            background-color: #f8f9fa;
            border-color: #d2e3fc;
            box-shadow: 0 1px 3px rgba(60,64,67,0.1);
        }

        .btn-google img {
            width: 20px;
        }

        .btn-facebook {
            background-color: #1877F2;
            color: #fff;
        }

        .btn-facebook:hover {
            background-color: #166fe5;
            color: #fff !important;
        }

        .btn-facebook i {
            font-size: 1.2rem;
        }

        .btn-apple {
            background-color: #000;
            color: #fff;
        }

        .btn-apple:hover {
            background-color: #1a1a1a;
            color: #fff !important;
        }

        .btn-apple i {
            font-size: 1.25rem;
            margin-bottom: 2px;
        }
        /* Responsive */
        @media (max-width: 991px) {
            .img_login {
                display: none;
            }

            .page_login .row.gx-0>div:first-child {
                min-height: 100vh;
                background: linear-gradient(135deg, #fff 0%, #f1f5f9 100%);
            }
                font-size: 2.2rem;
            }

            .logo_abs {
                left: 20px;
                top: 20px;
            }

            .logo_abs img {
                height: 40px;
            }

            }
    </style>
@endsection
@section('content')
    <div class="page_login">
        @php
            $portalLinks = collect([
                [
                    'label' => 'Đăng nhập học viên',
                    'short_label' => 'Học viên',
                    'description' => 'Lớp học, hồ sơ, thanh toán và bài tập.',
                    'icon' => 'fas fa-user-graduate',
                    'url' => route('login'),
                    'active' => ($portal ?? 'student') === 'student',
                ],
                [
                    'label' => 'Đăng nhập giảng viên',
                    'short_label' => 'Giảng viên',
                    'description' => 'Lịch dạy, lớp phụ trách và tài liệu giảng dạy.',
                    'icon' => 'fas fa-chalkboard-teacher',
                    'url' => route('teacher.login'),
                    'active' => ($portal ?? 'student') === 'teacher',
                ],
                [
                    'label' => 'Đăng nhập nhân viên',
                    'short_label' => 'Nhân viên',
                    'description' => 'Quản trị đào tạo, vận hành và nghiệp vụ nội bộ.',
                    'icon' => 'fas fa-briefcase',
                    'url' => route('staff.login'),
                    'active' => ($portal ?? 'student') === 'staff',
                ],
            ]);
            $currentPortalLink = $portalLinks->firstWhere('active', true) ?? $portalLinks->first();
        @endphp
        {{-- Logo --}}
        <div class="logo_abs">
            <a href="{{ route('home.index') }}" class="logo_site">
                <img src="{{ asset('assets/images/logo.png') }}" class="img-fluid" alt="Five Genius Logo">
            </a>
        </div>

        <div class="container-fluid">
            <div class="row gx-0">
                {{-- Form Column --}}
                <div class="col-lg-6">
                    <div class="auth-form-shell h-100 d-flex flex-column justify-content-center">
                        <div class="row justify-content-center">
                            <div class="col-lg-8 col-xl-6 px-4">
                                <form id="loginform" name="loginform" class="needs-validation" novalidate data-joi-schema="login"
                                    action="{{ $submitRoute ?? route('login') }}" method="POST">
                                    @csrf
                                    <h3 class="fs-48 ff-title text-center cl-green mb-lg-3 mb-2">{{ $portalTitle ?? 'Đăng nhập' }}</h3>
                                    <div class="portal-selector mt-3 mb-4">
                                        @foreach ($portalLinks as $link)
                                            <a href="{{ $link['url'] }}" class="portal-tab {{ $link['active'] ? 'active' : '' }}" title="{{ $link['description'] }}">
                                                <i class="{{ $link['icon'] }}"></i>
                                                <span>{{ $link['short_label'] }}</span>
                                            </a>
                                        @endforeach
                                    </div>

                                    @php
                                        $lockoutUntil = session('lockout_until', 0);
                                        $lockoutMessage = session('lockout_message');
                                        $currentTime = time();
                                        $isLockedOut = $lockoutUntil > $currentTime;
                                        // Tính sẵn số giây còn lại từ server
                                        $remainingSeconds = $isLockedOut ? ($lockoutUntil - $currentTime) : 0;
                                    @endphp

                                    @if ($isLockedOut)
                                        {{-- Lockout countdown alert --}}
                                        <div class="alert mb-3 py-3" id="lockoutAlert" data-remaining="{{ $remainingSeconds }}" style="
                                            background: linear-gradient(135deg, #fff0f0 0%, #ffe0e0 100%);
                                            border: none;
                                            border-left: 4px solid var(--primary-red);
                                            border-radius: 12px;
                                            font-size: 0.9rem;
                                            color: #c0392b;
                                        ">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="fas fa-lock me-2" style="font-size:1.1rem"></i>
                                                <strong>Đăng nhập bị tạm khóa</strong>
                                            </div>
                                            <div>{{ $lockoutMessage ?: 'Phát hiện nhiều lần đăng nhập sai liên tiếp. Vui lòng thử lại sau:' }}</div>
                                           
                                            <div class="text-center mt-2">
                                                <span id="lockoutCountdown" style="
                                                    font-size: 1.6rem;
                                                    font-weight: 700;
                                                    font-family: 'Montserrat', monospace;
                                                    color: var(--primary-red);
                                                    letter-spacing: 2px;
                                                ">--:--</span>
                                            </div>
                                        </div>
                                    @elseif ($errors->any() && !$errors->has('taiKhoan') && !$errors->has('password'))
                                        <div class="alert alert-danger py-2 mb-3" style="font-size:0.88rem">
                                            <i class="fas fa-exclamation-circle me-1"></i> {{ $errors->first() }}
                                        </div>
                                    @endif

                                    {{-- Tài khoản --}}
                                    <input type="hidden" name="login_attempt" value="1">
                                    <div class="mb-3">
                                        <input type="text" id="taiKhoan" name="taiKhoan"
                                            class="form-control @if(!$isLockedOut) @error('taiKhoan') is-invalid @enderror @endif"
                                            value="{{ old('taiKhoan') }}" required autocomplete="username" autofocus
                                            placeholder="{{ match ($portal ?? 'student') {
                                                'teacher' => 'Email hoặc mã giảng viên',
                                                'staff' => 'Email hoặc mã nhân viên',
                                                default => 'Tài khoản hoặc email',
                                            } }}">
                                        @if (!$isLockedOut)
                                            @error('taiKhoan')
                                                <div class="invalid-feedback d-block text-danger mt-1">
                                                    <i class="fas fa-exclamation-triangle me-1"></i> {{ $message }}
                                                </div>
                                            @enderror
                                        @endif
                                    </div>

                                    {{-- Password --}}
                                    <div class="password_box mb-3">
                                        <input type="password" name="password" id="password"
                                            class="form-control @error('password') is-invalid @enderror"
                                            autocomplete="current-password" required
                                            placeholder="Mật khẩu (tối thiểu 8 ký tự)">
                                        <div class="show_text" onclick="togglePassword()">
                                            <i class="fa fa-eye-slash" id="toggleIcon"></i>
                                        </div>
                                        @error('password')
                                            <div class="invalid-feedback d-block text-danger mt-1 fw-bold"
                                                style="font-size: 0.9em;">
                                                <i class="fas fa-exclamation-triangle me-1"></i> {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    {{-- Remember Me --}}
                                    <div class="remember_box mb-3">
                                        <input type="checkbox" name="remember" id="remember"
                                            {{ old('remember') ? 'checked' : '' }}>
                                        <label class="cl-green ff-title ls-1 mb-0" for="remember">Ghi nhớ đăng nhập</label>
                                    </div>

                                    

                                    {{-- Submit --}}
                                    <button type="submit"
                                        class="btn btn-red d-block text-center w-100 mt-3 mb-lg-4 mb-2 ls-1">Đăng
                                        nhập</button>
@if (($portal ?? 'student') === 'student')
                                        <div class="social-login-divider">
                                            <span>Hoặc</span>
                                        </div>

                                        @if (!empty($googleRoute))
                                            <a href="{{ $googleRoute }}" class="social-btn btn-google">
                                                <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google">
                                                Đăng nhập bằng Google
                                            </a>
                                        @endif
                                        
                                        {{-- Uncomment các route dưới đây khi có chức năng thật --}}
                                        {{-- @if (!empty($facebookRoute)) --}}
                                            <a href="#" class="social-btn btn-facebook">
                                                <i class="fab fa-facebook-f"></i>
                                                Đăng nhập bằng Facebook
                                            </a>
                                        {{-- @endif --}}

                                        {{-- @if (!empty($appleRoute)) --}}
                                            <a href="#" class="social-btn btn-apple">
                                                <i class="fab fa-apple"></i>
                                                Đăng nhập bằng Apple
                                            </a>
                                        {{-- @endif --}}
                                    @endif
                                    {{-- Links --}}
                                    <div class="d-flex flex-column flex-sm-row justify-content-center align-items-center gap-3 mt-4 text-center">
                                        @if (Route::has('password.request'))
                                            <a href="{{ route('password.request') }}" class="text-muted text-decoration-none" style="font-size: 0.95rem; font-weight: 500;">
                                                <i class="fas fa-key me-1"></i> Quên mật khẩu?
                                            </a>
                                        @endif
                                        
                                        @if (Route::has('password.request') && !empty($registerRoute))
                                            <span class="d-none d-sm-inline text-muted" style="opacity: 0.5;">|</span>
                                        @endif

                                        @if (!empty($registerRoute))
                                            <div style="font-size: 0.95rem;">
                                                Chưa có tài khoản? 
                                                <a href="{{ $registerRoute }}" class="cl-green ff-title text-decoration-none ms-1">Đăng ký ngay</a>
                                            </div>
                                        @endif
                                    </div>

                                </form>
                                @include('auth.partials.recaptcha-script', [
                                    'formId' => 'loginform',
                                    'recaptchaEnabled' => $recaptchaEnabled ?? false,
                                    'recaptchaAction' => $recaptchaAction ?? null,
                                ])
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Image Column --}}
                <div class="col-lg-6">
                    <div class="img_login">
                        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1200" class="img-fluid"
                            alt="Login Image">
                    </div>
                </div>
            </div>
    </div>
@endsection

@section('script')
    <script>
        function togglePassword() {
            var x = document.getElementById("password");
            var icon = document.getElementById("toggleIcon");
            if (x.type === "password") {
                x.type = "text";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            } else {
                x.type = "password";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            }
        }

        // ── Lockout Countdown Timer (Realtime & Safe) ──
        document.addEventListener("DOMContentLoaded", function() {
            var alertBox = document.getElementById('lockoutAlert');
            if (!alertBox) return; // Nếu không có alert thì bỏ qua

            // Lấy số giây còn lại do Server tính toán
            var remaining = parseInt(alertBox.getAttribute('data-remaining'), 10);
            if (isNaN(remaining) || remaining <= 0) return;

            var el = document.getElementById('lockoutCountdown');
            var submitBtn = document.querySelector('#loginform button[type="submit"]');

            // Disable nút đăng nhập ban đầu
            if (submitBtn) {
                submitBtn.disabled = true;
            }

            function pad(n) { return n < 10 ? '0' + n : n; }

            // Hàm cập nhật giao diện
            function updateDisplay(sec) {
                var m = Math.floor(sec / 60);
                var s = sec % 60;
                el.textContent = pad(m) + ':' + pad(s);
            }

            // Gọi ngay lần đầu tiên để không bị giật lag chữ --:-- trong 1 giây đầu
            updateDisplay(remaining);

            // Bắt đầu đếm ngược realtime mỗi giây
            var timer = setInterval(function() {
                remaining--;
                
                if (remaining <= 0) {
                    clearInterval(timer); // Dừng bộ đếm
                    el.textContent = '00:00';
                    
                    // Mở khóa nút submit
                    if (submitBtn) {
                        submitBtn.disabled = false;
                    }
                    
                    // Hiệu ứng mờ dần và ẩn thông báo
                    alertBox.style.transition = 'opacity 0.5s ease';
                    alertBox.style.opacity = '0';
                    setTimeout(function() { alertBox.style.display = 'none'; }, 500);
                    return;
                }
                
                updateDisplay(remaining);
            }, 1000);
        });

    </script>
@endsection
