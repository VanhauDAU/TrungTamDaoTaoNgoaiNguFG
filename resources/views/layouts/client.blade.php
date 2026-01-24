<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.client.head')
</head>
<body>
    {{-- @include('partials.loading')
    @include('partials.floating_button')
    @include('clients.blocks.header') --}}
    <div class="container">
        @yield('content')
    </div>
    {{-- @include('clients.blocks.footer') --}}
    @if(!Auth::check())
        <div id="toastContainer" class="position-fixed bottom-0 start-0 p-3" style="z-index: 9999;"></div>
    @endif
</body>
{{-- @include('partials.clients.scripts') --}}
@yield('script')
</html>
