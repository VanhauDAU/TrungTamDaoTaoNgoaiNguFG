@extends('layouts.auth')

@section('title', 'Đăng nhập - Five Genius')

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
                            <form id="loginform" name="loginform" class="needs-validation" novalidate action="{{ route('login') }}" method="POST">
                                @csrf
                                <h3 class="fs-48 ff-title text-center cl-green mb-lg-3 mb-2">Đăng nhập</h3>
                                <div class="text-center mb-4">
                                    Chưa có tài khoản? <a href="{{ route('register') }}" class="ff-title cl-green">Đăng ký ngay!</a>
                                </div>

                                {{-- Error Display --}}
                                @if ($errors->any())
                                    <div class="alert alert-danger fs-12 mb-3">
                                        @foreach ($errors->all() as $error)
                                            <div>{{ $error }}</div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Email --}}
                                <div class="mb-3">
                                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="Email">
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                {{-- Password --}}
                                <div class="password_box mb-3">
                                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" autocomplete="current-password" required placeholder="Mật khẩu">
                                    <div class="show_text" onclick="togglePassword()">
                                        <i class="fa fa-eye-slash" id="toggleIcon"></i>
                                    </div>
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                {{-- Remember Me --}}
                                <div class="remember_box mb-3">
                                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="cl-green ff-title ls-1 mb-0" for="remember">Ghi nhớ đăng nhập</label>
                                </div>

                                {{-- Submit --}}
                                <button type="submit" class="btn btn-red d-block text-center w-100 mt-3 mb-lg-4 mb-2 ls-1">Đăng nhập</button>

                                {{-- Forgot Password --}}
                                <div class="text-center mb-2">
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="cl-green ff-title ls-1">Quên mật khẩu?</a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Image Column --}}
            <div class="col-lg-6">
                <div class="img_login">
                    <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1200" class="img-fluid" alt="Login Image">
                </div>
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

    // Bootstrap validation
    (function () {
      'use strict'
      var forms = document.querySelectorAll('.needs-validation')
      Array.prototype.slice.call(forms)
        .forEach(function (form) {
          form.addEventListener('submit', function (event) {
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
