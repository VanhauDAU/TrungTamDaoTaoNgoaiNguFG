@extends('layouts.auth')

@section('title', 'Đăng ký - Five Genius')

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
