@extends('layouts.auth')

@section('title', 'Xác thực email - Five Genius')

@section('content')
    <div class="page_login" style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f6fbfb;padding:24px;">
        <div style="max-width:560px;width:100%;background:#fff;border-radius:24px;padding:36px;box-shadow:0 24px 48px rgba(16,69,79,.12);">
            <div class="text-center mb-4">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Five Genius Logo" style="height:56px">
            </div>

            <h1 style="font-size:2rem;font-weight:700;color:#10454f;text-align:center;margin-bottom:12px;">Xác thực email</h1>
            <p style="text-align:center;color:#5c6b73;margin-bottom:24px;">
                Chúng tôi đã gửi liên kết xác thực đến email của bạn. Vui lòng mở email và bấm vào liên kết để kích hoạt tài khoản học viên.
            </p>

            @if (session('resent'))
                <div class="alert alert-success">
                    Liên kết xác thực mới đã được gửi tới email của bạn.
                </div>
            @endif

            @if (session('warning'))
                <div class="alert alert-warning">
                    {{ session('warning') }}
                </div>
            @endif

            <form method="POST" action="{{ route('verification.resend') }}" class="d-grid gap-2">
                @csrf
                <button type="submit" class="btn btn-danger" style="padding:12px 20px;border-radius:12px;">
                    Gửi lại email xác thực
                </button>
                <a href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    class="btn btn-outline-secondary" style="padding:12px 20px;border-radius:12px;">
                    Đăng xuất
                </a>
            </form>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </div>
@endsection
