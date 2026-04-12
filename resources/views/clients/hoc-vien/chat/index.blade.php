@extends('layouts.client')
@section('title', 'Chat lớp học')
@section('body_class', 'chat-page-body')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/chat/chat.css') }}">
@endsection

@section('content')
    <section class="chat-page">
        <div id="chat-app" class="chat-shell"></div>
    </section>
@endsection

@section('script')
    <script>
        window.CHAT_BOOTSTRAP = {
            rooms: @json($rooms),
            selectedRoom: @json($selectedRoom),
            csrf: '{{ csrf_token() }}',
            backUrl: '{{ route('home.student.index') }}',
            reactionEmojis: @json(\App\Services\Client\Chat\ChatMessageService::reactionEmojis()),
            composerEmojis: @json(\App\Services\Client\Chat\ChatMessageService::composerEmojis()),
            endpoints: {
                poll: '{{ route('home.api.chat.poll') }}',
                rooms: '{{ route('home.api.chat.rooms') }}',
                messages: '{{ url('/api/chat/rooms/__ROOM__/messages') }}',
                members: '{{ url('/api/chat/rooms/__ROOM__/members') }}',
                search: '{{ url('/api/chat/rooms/__ROOM__/search') }}',
                join: '{{ url('/api/chat/rooms/__ROOM__/join') }}',
                leave: '{{ url('/api/chat/rooms/__ROOM__/leave') }}',
                typing: '{{ url('/api/chat/rooms/__ROOM__/typing') }}',
                direct: '{{ route('home.api.chat.direct') }}',
                read: '{{ url('/api/chat/rooms/__ROOM__/read') }}',
                send: '{{ route('home.api.chat.send') }}',
                recall: '{{ url('/api/chat/messages/__MESSAGE__/recall') }}',
                react: '{{ url('/api/chat/messages/__MESSAGE__/react') }}',
                deleteForMe: '{{ url('/api/chat/messages/__MESSAGE__/delete-for-me') }}',
            }
        };
    </script>
    <script src="{{ asset('assets/client/js/pages/chat/chat.js') }}"></script>
@endsection
