@extends('layouts.internal')

@section('title', 'Thông báo')
@section('page-title', 'Thông báo')
@section('breadcrumb', 'Danh sách thông báo cá nhân')

@section('content')
    <div class="container-fluid px-0">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                    <div>
                        <h5 class="mb-1">Thông báo cổng {{ $portalTitle }}</h5>
                        <p class="text-muted mb-0">Danh sách thông báo gắn với tài khoản đang đăng nhập.</p>
                    </div>
                    <a href="{{ route($indexRoute) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-rotate-right me-2"></i>Làm mới
                    </a>
                </div>

                @if ($notifications->isEmpty())
                    <div class="border rounded-4 p-5 text-center text-muted">
                        <i class="fas fa-bell-slash fs-1 mb-3"></i>
                        <p class="mb-0">Chưa có thông báo nào cho tài khoản này.</p>
                    </div>
                @else
                    <div class="list-group list-group-flush">
                        @foreach ($notifications as $notification)
                            @php($item = $notification->thongBao)
                            <div class="list-group-item px-0 py-3">
                                <div class="d-flex flex-wrap justify-content-between gap-3">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge {{ $notification->daDoc ? 'bg-secondary-subtle text-secondary-emphasis' : 'bg-primary-subtle text-primary-emphasis' }}">
                                                {{ $notification->daDoc ? 'Đã đọc' : 'Chưa đọc' }}
                                            </span>
                                            <span class="text-muted small">{{ optional($item?->created_at)->format('d/m/Y H:i') }}</span>
                                        </div>
                                        <h6 class="mb-1">{{ $item?->tieuDe ?? 'Thông báo không còn tồn tại' }}</h6>
                                        <p class="text-muted mb-0">{{ \Illuminate\Support\Str::limit(strip_tags((string) ($item?->noiDung ?? '')), 220) }}</p>
                                    </div>

                                    @if (($item?->tepDinhs?->count() ?? 0) > 0)
                                        <div class="text-muted small">
                                            <i class="fas fa-paperclip me-1"></i>{{ $item->tepDinhs->count() }} tệp đính kèm
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
