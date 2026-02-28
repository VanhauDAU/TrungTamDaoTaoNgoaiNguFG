<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="{{ asset('assets/client/js/header.js') }}"></script>
<script src="{{ asset('assets/client/js/home.js') }}"></script>
<script src="{{ asset('assets/client/js/page-loader.js') }}"></script>

{{-- ── Notification System: chỉ chạy khi đã đăng nhập ────────── --}}
@auth
    <script>
        // Inject PHP config → JS (không có logic, chỉ data)
        window.NB_IS_AUTH = true;
        window.NB_CSRF = '{{ csrf_token() }}';
        window.NB_SSE_URL = '{{ route('home.api.thong-bao.stream') }}';
        window.NB_DROPDOWN_URL = '{{ route('home.api.thong-bao.dropdown') }}';
        window.NB_UNREAD_URL = '{{ route('home.api.thong-bao.unread-count') }}';
        window.NB_MARK_ALL_URL = '{{ route('home.api.thong-bao.mark-all-read') }}';
        window.NB_MARK_READ_URL = '/api/thong-bao'; // base path, JS appends /{id}/da-doc
        window.NB_PAGE_URL = '{{ route('home.thong-bao.index') }}';
    </script>
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/thong-bao/thong-bao.css') }}">
    <script src="{{ asset('assets/client/js/pages/thong-bao/thong-bao.js') }}"></script>
@endauth
