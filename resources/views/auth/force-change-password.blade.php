@extends('layouts.auth')

@section('title', 'Đổi mật khẩu - Five Genius')
@section('stylesheet')
    <style>
        :root {
            --primary-green: #10454f;
            --primary-red: #e31e24;
            --accent-teal: #27c4b5;
            --text-gray: #636e72;
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

        .fs-36 {
            font-size: 2.2rem;
        }

        .ls-1 {
            letter-spacing: 1px;
        }

        /* Page Layout */
        .page_force_pw {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
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
        .img_side {
            height: 100vh;
            width: 100%;
            padding: 30px;
            background-color: #fff;
        }

        .img_side img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 40px;
            box-shadow: 0 25px 50px -12px rgba(16, 69, 79, 0.25);
            transition: transform 0.5s ease;
        }

        .img_side img:hover {
            transform: scale(1.02);
        }

        /* Form Container */
        .page_force_pw .row.gx-0 {
            height: 100vh;
        }

        .page_force_pw .container-fluid {
            padding: 0;
        }

        /* Form Elements */
        .form-control {
            padding: 13px 20px;
            background-color: #f2f6f7;
            border: 1px solid transparent;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            color: var(--primary-green);
        }

        .form-control:focus {
            background-color: #fff;
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

        /* Password Box */
        .password_box {
            position: relative;
        }

        .password_box .show_text {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-gray);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Alert box */
        .alert-warning-custom {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            border: none;
            border-left: 4px solid #ff9800;
            border-radius: 12px;
            padding: 16px 20px;
            color: #e65100;
        }

        .alert-warning-custom i {
            font-size: 1.2rem;
        }

        /* Responsive */
        @media (max-width: 991px) {
            .img_side {
                display: none;
            }

            .page_force_pw .row.gx-0>div:first-child {
                min-height: 100vh;
                background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            }

            .fs-36 {
                font-size: 1.8rem;
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
    <div class="page_force_pw">
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
                    <div class="h-100 d-flex flex-column justify-content-center">
                        <div class="row justify-content-center">
                            <div class="col-lg-8 col-xl-6 px-4">
                                <h3 class="fs-36 ff-title text-center cl-green mb-3">
                                    <i class="fas fa-shield-alt me-2"></i>Đổi mật khẩu
                                </h3>

                                <div class="alert alert-warning-custom mb-4">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Bắt buộc đổi mật khẩu!</strong><br>
                                    Đây là lần đăng nhập đầu tiên của bạn. Vui lòng đặt mật khẩu mới để bảo mật tài khoản.
                                </div>

                                @if ($errors->any())
                                    <div class="alert alert-danger py-2 mb-3" style="font-size:0.88rem">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        @foreach ($errors->all() as $error)
                                            {{ $error }}<br>
                                        @endforeach
                                    </div>
                                @endif

                                <form action="{{ route('force-change-password.process') }}" method="POST">
                                    @csrf

                                    {{-- Mật khẩu mới --}}
                                    <div class="password_box mb-3">
                                        <input type="password" name="new_password" id="new_password"
                                            class="form-control @error('new_password') is-invalid @enderror"
                                            placeholder="Mật khẩu mới (tối thiểu 8 ký tự)" required autofocus>
                                        <div class="show_text" onclick="togglePassword('new_password', 'toggleIcon1')">
                                            <i class="fa fa-eye-slash" id="toggleIcon1"></i>
                                        </div>
                                    </div>

                                    {{-- Xác nhận mật khẩu mới --}}
                                    <div class="password_box mb-3">
                                        <input type="password" name="new_password_confirmation"
                                            id="new_password_confirmation" class="form-control"
                                            placeholder="Xác nhận mật khẩu mới" required>
                                        <div class="show_text"
                                            onclick="togglePassword('new_password_confirmation', 'toggleIcon2')">
                                            <i class="fa fa-eye-slash" id="toggleIcon2"></i>
                                        </div>
                                    </div>

                                    {{-- Submit --}}
                                    <button type="submit" class="btn btn-red d-block text-center w-100 mt-4 mb-3 ls-1">
                                        <i class="fas fa-key me-2"></i>Đổi mật khẩu & Tiếp tục
                                    </button>
                                </form>

                                <div class="text-center">
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-link cl-green ff-title ls-1"
                                            style="text-decoration: none;">
                                            <i class="fas fa-sign-out-alt me-1"></i> Đăng xuất
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Image Column --}}
                <div class="col-lg-6">
                    <div class="img_side">
                        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1200" class="img-fluid"
                            alt="Force Change Password Image">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function togglePassword(inputId, iconId) {
            var x = document.getElementById(inputId);
            var icon = document.getElementById(iconId);
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
    </script>
@endsection