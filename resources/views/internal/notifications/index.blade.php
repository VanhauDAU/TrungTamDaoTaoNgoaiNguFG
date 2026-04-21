@extends('layouts.internal')

@section('title', 'Thông báo')
@section('page-title', 'Thông báo')
@section('breadcrumb', 'Danh sách thông báo nội bộ')

@section('content')
    @php
        $currentBox = $currentBox ?? 'inbox';
        $selectedNotificationId = $selectedNotificationId ?? 0;
        $createRoute = $createRoute ?? null;
        $markReadRoute = $markReadRoute ?? null;
        $markUnreadRoute = $markUnreadRoute ?? null;
        $stats = $stats ?? [];
        $loaiLabels = App\Models\Interaction\ThongBao::loaiLabels();
        $uuTienLabels = App\Models\Interaction\ThongBao::uuTienLabels();
    @endphp

    <div class="container-fluid px-0">
        <style>
            .nb-notify-shell {
                display: grid;
                gap: 1.25rem;
            }

            .nb-notify-hero,
            .nb-notify-panel,
            .nb-notify-card {
                background: rgba(255, 255, 255, 0.95);
                border: 1px solid rgba(226, 232, 240, 0.95);
                border-radius: 24px;
                box-shadow: 0 24px 50px rgba(15, 23, 42, 0.08);
            }

            .nb-notify-hero {
                padding: 1.5rem 1.6rem;
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 1rem;
            }

            .nb-notify-hero h4 {
                margin: 0 0 .35rem;
                font-weight: 700;
                color: #10233a;
            }

            .nb-notify-hero p {
                margin: 0;
                color: #63748b;
            }

            .nb-notify-actions {
                display: flex;
                flex-wrap: wrap;
                gap: .75rem;
            }

            .nb-notify-stats {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
                gap: 1rem;
            }

            .nb-stat-card {
                padding: 1rem 1.1rem;
                background: linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
                border-radius: 20px;
                border: 1px solid #dce6f3;
            }

            .nb-stat-label {
                display: block;
                margin-bottom: .35rem;
                font-size: .82rem;
                color: #64748b;
            }

            .nb-stat-value {
                font-size: 1.55rem;
                font-weight: 700;
                color: #0f172a;
            }

            .nb-notify-panel {
                padding: 1.25rem;
            }

            .nb-notify-toolbar {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                gap: 1rem;
                margin-bottom: 1rem;
            }

            .nb-notify-tabs,
            .nb-notify-filters {
                display: flex;
                flex-wrap: wrap;
                gap: .65rem;
            }

            .nb-notify-tab,
            .nb-filter-chip {
                display: inline-flex;
                align-items: center;
                gap: .45rem;
                min-height: 42px;
                padding: .65rem 1rem;
                border-radius: 999px;
                border: 1px solid #d6dfeb;
                background: #fff;
                color: #334155;
                text-decoration: none;
                font-weight: 600;
            }

            .nb-notify-tab.active,
            .nb-filter-chip.active {
                background: #102a43;
                border-color: #102a43;
                color: #fff;
            }

            .nb-filter-form {
                display: grid;
                grid-template-columns: minmax(220px, 1.4fr) repeat(3, minmax(140px, .8fr));
                gap: .85rem;
                margin-bottom: 1rem;
            }

            .nb-filter-input,
            .nb-filter-select {
                width: 100%;
                min-height: 46px;
                border-radius: 16px;
                border: 1px solid #d7e0ec;
                padding: 0 .95rem;
                background: #fff;
                color: #1e293b;
            }

            .nb-notify-list {
                display: grid;
                gap: 1rem;
            }

            .nb-notify-card {
                padding: 1.1rem 1.2rem;
                transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
            }

            .nb-notify-card:hover {
                transform: translateY(-1px);
                box-shadow: 0 24px 44px rgba(15, 23, 42, 0.11);
            }

            .nb-notify-card.is-selected {
                border-color: #4f46e5;
                box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.13);
            }

            .nb-notify-card.unread {
                border-color: #bfdbfe;
                background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
            }

            .nb-notify-head,
            .nb-notify-meta,
            .nb-notify-footer {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                gap: .85rem;
            }

            .nb-notify-title {
                margin: .2rem 0 .45rem;
                font-size: 1.08rem;
                font-weight: 700;
                color: #10233a;
            }

            .nb-notify-summary {
                margin: 0;
                color: #5b6b80;
                line-height: 1.65;
            }

            .nb-badge-stack {
                display: flex;
                flex-wrap: wrap;
                gap: .5rem;
            }

            .nb-mini-badge {
                display: inline-flex;
                align-items: center;
                gap: .35rem;
                min-height: 30px;
                padding: .35rem .7rem;
                border-radius: 999px;
                font-size: .78rem;
                font-weight: 700;
                background: #eef2ff;
                color: #4338ca;
            }

            .nb-mini-badge.priority-1 {
                background: #fff7ed;
                color: #c2410c;
            }

            .nb-mini-badge.priority-2 {
                background: #fef2f2;
                color: #b91c1c;
            }

            .nb-mini-badge.state-read {
                background: #f1f5f9;
                color: #475569;
            }

            .nb-mini-badge.state-unread {
                background: #dbeafe;
                color: #1d4ed8;
            }

            .nb-notify-footer {
                align-items: center;
                margin-top: 1rem;
            }

            .nb-notify-actions-inline {
                display: flex;
                flex-wrap: wrap;
                gap: .55rem;
            }

            .nb-notify-btn {
                border: 1px solid #d7e0ec;
                background: #fff;
                color: #334155;
                border-radius: 999px;
                min-height: 38px;
                padding: 0 .9rem;
                font-weight: 600;
            }

            .nb-notify-empty {
                padding: 3rem 1.25rem;
                text-align: center;
                color: #64748b;
            }

            @media (max-width: 991.98px) {
                .nb-filter-form {
                    grid-template-columns: 1fr;
                }

                .nb-notify-hero {
                    padding: 1.25rem;
                }
            }
        </style>

        <div class="nb-notify-shell">
            <div class="nb-notify-hero">
                <div>
                    <h4>Trung tâm thông báo cổng {{ $portalTitle }}</h4>
                    <p>Phân loại rõ hộp thư đến và thông báo đã gửi, giảm nhiễu từ các thông báo tài chính lặp lại và thao tác nhanh hơn theo từng ngữ cảnh.</p>
                </div>
                <div class="nb-notify-actions">
                    @if ($createRoute)
                        <a href="{{ route($createRoute) }}" class="btn btn-primary rounded-pill px-4">
                            <i class="fas fa-paper-plane me-2"></i>Soạn thông báo
                        </a>
                    @endif
                    <a href="{{ route($indexRoute) }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-rotate-right me-2"></i>Làm mới
                    </a>
                </div>
            </div>

            <div class="nb-notify-stats">
                <div class="nb-stat-card">
                    <span class="nb-stat-label">Tổng thư đến</span>
                    <div class="nb-stat-value">{{ number_format($stats['tong_hop_thu_den'] ?? 0) }}</div>
                </div>
                <div class="nb-stat-card">
                    <span class="nb-stat-label">Chưa đọc</span>
                    <div class="nb-stat-value">{{ number_format($stats['chua_doc'] ?? 0) }}</div>
                </div>
                <div class="nb-stat-card">
                    <span class="nb-stat-label">Ưu tiên cao</span>
                    <div class="nb-stat-value">{{ number_format($stats['quan_trong'] ?? 0) }}</div>
                </div>
                <div class="nb-stat-card">
                    <span class="nb-stat-label">Thông báo tài chính</span>
                    <div class="nb-stat-value">{{ number_format($stats['tai_chinh'] ?? 0) }}</div>
                </div>
                <div class="nb-stat-card">
                    <span class="nb-stat-label">Đã gửi</span>
                    <div class="nb-stat-value">{{ number_format($stats['da_gui'] ?? 0) }}</div>
                </div>
            </div>

            <div class="nb-notify-panel">
                <div class="nb-notify-toolbar">
                    <div class="nb-notify-tabs">
                        <a href="{{ route($indexRoute, array_merge(request()->except('page'), ['box' => 'inbox'])) }}"
                            class="nb-notify-tab {{ $currentBox === 'inbox' ? 'active' : '' }}">
                            <i class="fas fa-inbox"></i> Hộp thư đến
                        </a>
                        <a href="{{ route($indexRoute, array_merge(request()->except('page'), ['box' => 'sent'])) }}"
                            class="nb-notify-tab {{ $currentBox === 'sent' ? 'active' : '' }}">
                            <i class="fas fa-paper-plane"></i> Đã gửi
                        </a>
                    </div>

                    <div class="nb-notify-filters">
                        <a href="{{ route($indexRoute, array_merge(request()->except(['page', 'read']), ['box' => $currentBox, 'read' => 'unread'])) }}"
                            class="nb-filter-chip {{ request('read') === 'unread' ? 'active' : '' }}">
                            <i class="fas fa-envelope-open-text"></i> Chưa đọc
                        </a>
                        <a href="{{ route($indexRoute, array_merge(request()->except(['page', 'onlyPinned']), ['box' => $currentBox, 'onlyPinned' => 1])) }}"
                            class="nb-filter-chip {{ request()->boolean('onlyPinned') ? 'active' : '' }}">
                            <i class="fas fa-thumbtack"></i> Đã ghim
                        </a>
                    </div>
                </div>

                <form method="GET" action="{{ route($indexRoute) }}" class="nb-filter-form">
                    <input type="hidden" name="box" value="{{ $currentBox }}">
                    <input type="search" class="nb-filter-input" name="q" value="{{ request('q') }}"
                        placeholder="Tìm theo tiêu đề hoặc nội dung...">
                    <select name="loaiGui" class="nb-filter-select">
                        <option value="">Tất cả loại thông báo</option>
                        @foreach ($loaiLabels as $key => $label)
                            <option value="{{ $key }}" {{ request('loaiGui') === (string) $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <select name="uuTien" class="nb-filter-select">
                        <option value="">Tất cả mức ưu tiên</option>
                        @foreach ($uuTienLabels as $key => $label)
                            <option value="{{ $key }}" {{ request('uuTien') === (string) $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-dark rounded-pill flex-fill">Lọc</button>
                        <a href="{{ route($indexRoute, ['box' => $currentBox]) }}" class="btn btn-outline-secondary rounded-pill flex-fill">Xóa lọc</a>
                    </div>
                </form>

                @if ($notifications->isEmpty())
                    <div class="nb-notify-empty">
                        <i class="fas fa-bell-slash fs-1 mb-3 d-block"></i>
                        <p class="mb-0">{{ $currentBox === 'sent' ? 'Bạn chưa gửi thông báo nào từ cổng này.' : 'Chưa có thông báo nào khớp bộ lọc hiện tại.' }}</p>
                    </div>
                @else
                    <div class="nb-notify-list">
                        @foreach ($notifications as $notification)
                            @php
                                $item = $currentBox === 'sent' ? $notification : $notification->thongBao;
                                $isUnread = $currentBox === 'inbox' ? !$notification->daDoc : false;
                                $senderName = $currentBox === 'sent'
                                    ? 'Bạn'
                                    : ($item?->nguoiGui?->hoSoNguoiDung?->hoTen ?? $item?->nguoiGui?->nhanSu?->hoTen ?? $item?->nguoiGui?->taiKhoan ?? 'Hệ thống');
                                $createdTime = optional($item?->ngayGui ?? $item?->created_at)->format('d/m/Y H:i');
                            @endphp
                            <div class="nb-notify-card {{ $isUnread ? 'unread' : '' }} {{ $selectedNotificationId === (int) ($item?->thongBaoId ?? 0) ? 'is-selected' : '' }}"
                                id="thong-bao-{{ $item?->thongBaoId }}">
                                <div class="nb-notify-head">
                                    <div>
                                        <div class="nb-badge-stack mb-2">
                                            <span class="nb-mini-badge {{ $currentBox === 'inbox' ? ($isUnread ? 'state-unread' : 'state-read') : '' }}">
                                                <i class="fas {{ $currentBox === 'inbox' ? ($isUnread ? 'fa-envelope' : 'fa-envelope-open') : 'fa-paper-plane' }}"></i>
                                                {{ $currentBox === 'inbox' ? ($isUnread ? 'Chưa đọc' : 'Đã đọc') : 'Đã gửi' }}
                                            </span>
                                            <span class="nb-mini-badge">
                                                <i class="fas fa-layer-group"></i>{{ $item?->getLoaiLabel() ?? 'Hệ thống' }}
                                            </span>
                                            <span class="nb-mini-badge priority-{{ (int) ($item?->uuTien ?? 0) }}">
                                                <i class="fas fa-flag"></i>{{ $item?->getUuTienLabel() ?? 'Bình thường' }}
                                            </span>
                                            @if ($item?->ghim)
                                                <span class="nb-mini-badge">
                                                    <i class="fas fa-thumbtack"></i>Đã ghim
                                                </span>
                                            @endif
                                            @if (($item?->tepDinhs?->count() ?? 0) > 0)
                                                <span class="nb-mini-badge">
                                                    <i class="fas fa-paperclip"></i>{{ $item->tepDinhs->count() }} tệp
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-muted small">
                                            {{ $currentBox === 'sent' ? 'Gửi bởi bạn' : 'Gửi bởi ' . $senderName }}
                                            • {{ $createdTime ?: 'Không rõ thời gian' }}
                                        </div>
                                        <h5 class="nb-notify-title">{{ $item?->tieuDe ?? 'Thông báo không còn tồn tại' }}</h5>
                                    </div>

                                    @if ($currentBox === 'sent')
                                        <div class="text-muted small text-end">
                                            <div>Người nhận: {{ number_format($item?->nguoi_nhans_count ?? 0) }}</div>
                                            <div>Đã đọc: {{ number_format($item?->da_doc_count ?? 0) }}</div>
                                        </div>
                                    @endif
                                </div>

                                <div class="nb-notify-meta">
                                    <p class="nb-notify-summary">{!! nl2br(e(\Illuminate\Support\Str::limit(trim(strip_tags((string) ($item?->noiDung ?? ''))), 320))) !!}</p>
                                </div>

                                <div class="nb-notify-footer">
                                    <div class="text-muted small">
                                        {{ $currentBox === 'sent' ? ($item?->getDoiTuongLabel() ?? 'Đối tượng không xác định') : 'Nằm trong hộp thư của tài khoản hiện tại' }}
                                    </div>

                                    @if ($currentBox === 'inbox' && $item)
                                        <div class="nb-notify-actions-inline">
                                            @if ($isUnread && $markReadRoute)
                                                <button type="button" class="nb-notify-btn"
                                                    onclick="toggleNotificationReadState('{{ route($markReadRoute, ['id' => $item->thongBaoId]) }}', true)">
                                                    Đánh dấu đã đọc
                                                </button>
                                            @elseif($markUnreadRoute)
                                                <button type="button" class="nb-notify-btn"
                                                    onclick="toggleNotificationReadState('{{ route($markUnreadRoute, ['id' => $item->thongBaoId]) }}', false)">
                                                    Đánh dấu chưa đọc
                                                </button>
                                            @endif
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

@section('script')
    <script>
        async function toggleNotificationReadState(url) {
            try {
                await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });
                window.location.reload();
            } catch (error) {
                console.error('Notification state update failed', error);
            }
        }

        window.toggleNotificationReadState = toggleNotificationReadState;
    </script>
@endsection
