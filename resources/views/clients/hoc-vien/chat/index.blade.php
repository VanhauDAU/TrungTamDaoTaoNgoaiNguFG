@extends('layouts.client')
@section('title', 'Chat lớp học')
@section('body_class', 'chat-page-body')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/chat/chat.css') }}">
@endsection

@section('content')
    <section class="chat-page">
        <div class="chat-page-container">
            <div class="chat-page-header">
                <div class="chat-page-header-main">
                    <a href="{{ route('home.student.index') }}" class="chat-back-link">
                        <i class="fas fa-arrow-left"></i>
                        <span>Quay lại tài khoản</span>
                    </a>
                </div>
            </div>

            <div id="chat-app" class="chat-shell"></div>
        </div>
    </section>
@endsection

@section('script')
    <script>
        window.CHAT_BOOTSTRAP = {
            rooms: @json($rooms),
            selectedRoom: @json($selectedRoom),
            csrf: '{{ csrf_token() }}',
            endpoints: {
                poll: '{{ route('home.api.chat.poll') }}',
                rooms: '{{ route('home.api.chat.rooms') }}',
                messages: '{{ url('/api/chat/rooms/__ROOM__/messages') }}',
                members: '{{ url('/api/chat/rooms/__ROOM__/members') }}',
                join: '{{ url('/api/chat/rooms/__ROOM__/join') }}',
                direct: '{{ route('home.api.chat.direct') }}',
                read: '{{ url('/api/chat/rooms/__ROOM__/read') }}',
                send: '{{ route('home.api.chat.send') }}',
                recall: '{{ url('/api/chat/messages/__MESSAGE__/recall') }}',
            }
        };
    </script>
    <script src="{{ asset('assets/client/js/pages/chat/chat.js') }}"></script>
@endsection
