<!DOCTYPE html>
<html lang="vi">
<head>
    @include('partials.client.head')
</head>
<body>

    {{-- Header --}}
    @include('components.header')

    {{-- Nội dung --}}
    @yield('content')

    {{-- Footer --}}
    {{-- @include('clients.blocks.footer') --}}
    @unless(Route::is('login') || Route::is('register'))
        @include('components.sticky_contact')
    @endunless
    @yield('script')
    @include('partials.client.script')
</body>
</html>
