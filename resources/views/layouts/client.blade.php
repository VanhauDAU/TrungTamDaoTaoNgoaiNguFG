<!DOCTYPE html>
<html lang="vi">
<head>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @include('partials.client.head')
</head>
<body>
    {{-- Header --}}
    @include('components.client.header')
    {{-- Nội dung --}}
    @yield('content')
    {{-- Footer --}}
    @include('components.client.footer')
    @unless(Route::is('login') || Route::is('register'))
        @include('components.client.sticky_contact')
    @endunless
    @yield('script')
    @include('partials.client.script')
    @include('components.client.floating-contact')
</body>
</html>
