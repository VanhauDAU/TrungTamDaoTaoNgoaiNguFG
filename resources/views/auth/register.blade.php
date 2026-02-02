@extends('layouts.auth')

@section('title', 'Đăng ký - Five Genius')
@section('stylesheet')
    <style>
        /* ===== REGISTER PAGE CSS ===== */

        :root {
            --primary-green: #10454f;
            --primary-red: #e31e24;
            --accent-teal: #27c4b5;
            --text-gray: #636e72;
        }

        @font-face {
            font-family: 'TitleFont';
            /* Giả sử dùng font title chung */
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

        .form-control.is-invalid {
            border-color: var(--primary-red);
            background-color: #fff5f5;
        }

        .form-control.is-invalid:focus {
            border-color: var(--primary-red);
            box-shadow: 0 10px 20px rgba(227, 30, 36, 0.1);
        }

        /* Invalid Feedback */
        .invalid-feedback {
            color: var(--primary-red);
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: block;
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
            /* Điều chỉnh lại vị trí để cân đối */
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-gray);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Link Style */
        a {
            transition: all 0.3s ease;
            text-decoration: none;
        }

        a:hover {
            color: var(--primary-red) !important;
        }

        /* Alert Styling */
        .alert-danger {
            background-color: #fff5f5;
            border: 1px solid #ffd6d6;
            border-radius: 10px;
            color: var(--primary-red);
            padding: 12px 20px;
        }

        /* Social Login Buttons */
        .social-login-buttons {
            display: flex;
            flex-direction: row;
            gap: 10px;
        }

        .btn-social {
            flex: 1;
            padding: 12px 15px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-google {
            background-color: #fff;
            color: #333;
            border: 1.5px solid #ddd;
        }

        .btn-google:hover {
            background-color: #f8f9fa;
            border-color: #ccc;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-google i {
            color: #ea4335;
            font-size: 1.1rem;
        }

        .btn-facebook {
            background-color: #1877f2;
            color: #fff;
            border: 1.5px solid #1877f2;
        }

        .btn-facebook:hover {
            background-color: #0d65d9;
            border-color: #0d65d9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(24, 119, 242, 0.3);
        }

        .btn-facebook i {
            font-size: 1.1rem;
        }

        /* Terms Text */
        .text-muted {
            color: var(--text-gray) !important;
        }

        .text-muted a {
            font-weight: 600;
            text-decoration: underline;
        }

        .text-muted a:hover {
            color: var(--primary-red) !important;
        }


        /* Responsive */
        @media (max-width: 991px) {
            .img_login {
                display: none;
            }

            .page_login .row.gx-0>div:first-child {
                min-height: 100vh;
                background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            }

            .fs-48 {
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
                                <form id="registerform" name="registerform" class="needs-validation" novalidate
                                    action="{{ route('register') }}" method="POST">
                                    @csrf
                                    <h3 class="fs-48 ff-title text-center cl-green mb-lg-3 mb-2">Đăng ký</h3>
                                    <div class="text-center mb-4">
                                        Đã có tài khoản? <a href="{{ route('login') }}" class="ff-title cl-green">Đăng nhập
                                            ngay!</a>
                                    </div>

                                    {{-- Error Display --}}
                                    @if ($errors->any())
                                        <div class="alert alert-danger fs-12 mb-3">
                                            @foreach ($errors->all() as $error)
                                                <div>{{ $error }}</div>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Name --}}
                                    <div class="mb-3">
                                        <input type="text" id="name" name="name"
                                            class="form-control @error('name') is-invalid @enderror"
                                            value="{{ old('name') }}" required autocomplete="name" autofocus
                                            placeholder="Họ và tên">
                                        @error('name')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    {{-- Email --}}
                                    <div class="mb-3">
                                        <input type="email" id="email" name="email"
                                            class="form-control @error('email') is-invalid @enderror"
                                            value="{{ old('email') }}" required autocomplete="email" placeholder="Email">
                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    {{-- Password --}}
                                    <div class="password_box mb-3">
                                        <input type="password" name="password" id="password"
                                            class="form-control @error('password') is-invalid @enderror"
                                            autocomplete="new-password" required placeholder="Mật khẩu">
                                        <div class="show_text" onclick="togglePassword()">
                                            <i class="fa fa-eye-slash" id="toggleIcon"></i>
                                        </div>
                                        @error('password')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>

                                    {{-- Confirm Password --}}
                                    <div class="password_box mb-3">
                                        <input type="password" name="password_confirmation" id="password_confirmation"
                                            class="form-control" autocomplete="new-password" required
                                            placeholder="Xác nhận mật khẩu">
                                        <div class="show_text" onclick="togglePasswordConfirm()">
                                            <i class="fa fa-eye-slash" id="toggleIconConfirm"></i>
                                        </div>
                                    </div>

                                    {{-- Submit --}}
                                    <button type="submit" class="btn btn-red d-block text-center w-100 mt-3 mb-3 ls-1">Đăng
                                        ký</button>

                                    {{-- Social Login Buttons --}}
                                    <div class="social-login-buttons mb-3">
                                        <button type="button" class="btn btn-social btn-google">
                                            <i class="fab fa-google"></i> Google
                                        </button>
                                        <button type="button" class="btn btn-social btn-facebook">
                                            <i class="fab fa-facebook-f"></i> Facebook
                                        </button>
                                    </div>

                                    {{-- Terms of Service --}}
                                    <div class="text-center mb-2">
                                        <small class="text-muted">
                                            Việc bạn tiếp tục sử dụng trang web này đồng nghĩa với việc bạn đồng ý với
                                            <a href="#" class="cl-green">điều khoản sử dụng</a> của chúng tôi
                                        </small>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Image Column --}}
                <div class="col-lg-6">
                    <div class="img_login">
                        <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=1200" class="img-fluid"
                            alt="Register Image">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        // Toggle password visibility for main password field
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

        // Toggle password visibility for confirmation field
        function togglePasswordConfirm() {
            var x = document.getElementById("password_confirmation");
            var icon = document.getElementById("toggleIconConfirm");
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

        // Bootstrap validation
        (function() {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function(form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
@endsection
