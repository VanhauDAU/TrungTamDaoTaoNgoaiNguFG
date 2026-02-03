@extends('layouts.auth')
@section('title', 'Quên Mật Khẩu - Five Genius')
@section('stylesheet')
    <style>
        /* ===== RESET PASSWORD PAGE CSS (Matching Register Style) ===== */

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
            background-color: #fff;
        }

        .img_login img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 40px;
            box-shadow: 0 25px 50px -12px rgba(16, 69, 79, 0.25);
            transition: transform 0.5s ease;
        }

        .img_login img:hover {
            transform: scale(1.02);
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
                            <div class="col-lg-8 col-xl-7 px-4">
                                <form method="POST" action="{{ route('password.email') }}" class="needs-validation">
                                    @csrf
                                    <h3 class="fs-48 ff-title text-center cl-green mb-lg-3 mb-2">Quên mật khẩu?</h3>
                                    <p class="text-center text-muted mb-4">
                                        Nhập email của bạn để khôi phục mật khẩu.
                                    </p>

                                    @if (session('status'))
                                        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                                            {{ session('status') }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                                aria-label="Close"></button>
                                        </div>
                                    @endif

                                    {{-- Email --}}
                                    <div class="mb-3">
                                        <input id="email" type="email"
                                            class="form-control @error('email') is-invalid @enderror" name="email"
                                            value="{{ old('email') }}" required autocomplete="email" autofocus
                                            placeholder="Địa chỉ Email">
                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>



                                    {{-- Submit --}}
                                    <button type="submit" class="btn btn-red d-block text-center w-100 mt-4 mb-3 ls-1">
                                        Đặt Lại Mật Khẩu
                                    </button>

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
                            alt="Reset Password Image">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
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
