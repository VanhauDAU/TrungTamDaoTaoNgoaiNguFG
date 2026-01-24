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

    @yield('script')
</body>
</html>
