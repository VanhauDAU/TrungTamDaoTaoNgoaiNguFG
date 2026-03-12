<!DOCTYPE html>
<html lang="vi">

<head>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @include('partials.client.head')
    @yield('stylesheet')
</head>

@php
    $isChatPage = Route::is('home.student.chat');
@endphp

<body class="@yield('body_class') {{ $isChatPage ? 'client-chat-mode' : '' }}">
    {{-- Loading Screen --}}
    <div id="page-loader" class="page-loader">
        <div class="loader-content">
            <div class="loader-spinner"></div>
            <p class="loader-text">Đang tải...</p>
        </div>
    </div>
    {{-- Header --}}
    @include('components.client.header')
    {{-- Nội dung --}}
    @yield('content')
    {{-- Footer --}}
    @unless ($isChatPage)
        @include('components.client.footer')
    @endunless
    @unless (Route::is('login') || Route::is('register') || $isChatPage)
        @include('components.client.sticky-contact')
    @endunless
    @include('partials.client.script')
    @vite(['resources/js/app.js'])
    @yield('script')
    @unless ($isChatPage)
        @include('components.client.floating-contact')
    @endunless
</body>

</html>
