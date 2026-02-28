@extends('layouts.client')
@section('title', 'Thông báo của tôi')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/account.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/breadcrumb.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/thong-bao/thong-bao.css') }}">
@endsection

@section('content')
    <section class="account-page">
        <div class="custom-container">
            <div class="row g-4">
                {{-- SIDEBAR --}}
                @include('components.client.account-sidebar')

                {{-- MAIN CONTENT --}}
                <div class="col-lg-9">
                    <div class="account-content">

                        {{-- Breadcrumb --}}
                        <x-client.account-breadcrumb :items="[
                            ['label' => 'Trang chủ', 'url' => route('home.index'), 'icon' => 'fas fa-home'],
                            ['label' => 'Tài khoản', 'url' => route('home.student.index')],
                            ['label' => 'Thông báo'],
                        ]" />

                        {{-- Header + actions --}}
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
                            <h2 class="content-title mb-0">
                                <i class="fas fa-bell me-2" style="color:#6366f1;"></i> Thông báo
                                @if ($tongChuaDoc > 0)
                                    <span class="nb-unread-count-badge" id="page-unread-badge">
                                        {{ $tongChuaDoc }} chưa đọc
                                    </span>
                                @endif
                            </h2>
                            <button class="nb-mark-all-btn" onclick="markAllRead(); refreshBadgeAfterMarkAll();">
                                <i class="fas fa-check-double me-1"></i> Đọc tất cả
                            </button>
                        </div>

                        {{-- Filter tabs --}}
                        @php
                            $currentFilter = request('filter', 'all');
                            $currentLoai = request('loai', '');
                        @endphp
                        <div class="nb-filter-tabs" style="margin-bottom:1.25rem;">
                            <a href="{{ route('home.thong-bao.index') }}"
                                class="nb-tab {{ $currentFilter === 'all' && !$currentLoai ? 'active' : '' }}">
                                Tất cả
                            </a>
                            <a href="{{ route('home.thong-bao.index', ['filter' => 'unread']) }}"
                                class="nb-tab {{ $currentFilter === 'unread' ? 'active' : '' }}">
                                Chưa đọc
                                @if ($tongChuaDoc > 0)
                                    <span
                                        class="nb-tab-count">{{ min($tongChuaDoc, 99) }}{{ $tongChuaDoc > 99 ? '+' : '' }}</span>
                                @endif
                            </a>
                            <a href="{{ route('home.thong-bao.index', ['filter' => 'read']) }}"
                                class="nb-tab {{ $currentFilter === 'read' ? 'active' : '' }}">
                                Đã đọc
                            </a>
                            @foreach ([1 => '🎓 Học tập', 2 => '💰 Tài chính', 3 => '📅 Sự kiện', 4 => '🚨 Khẩn cấp'] as $k => $v)
                                <a href="{{ route('home.thong-bao.index', ['loai' => $k]) }}"
                                    class="nb-tab {{ $currentLoai == $k ? 'active' : '' }}">
                                    {{ $v }}
                                </a>
                            @endforeach
                        </div>

                        {{-- Notification list --}}
                        @if ($items->isNotEmpty())
                            <div class="nb-list" id="nb-list">
                                @foreach ($items as $item)
                                    @php
                                        $tb = $item->thongBao;
                                        $isUnread = !$item->daDoc;
                                        $iconMap = [
                                            0 => ['cls' => 'icon-he-thong', 'fa' => 'fa-cog'],
                                            1 => ['cls' => 'icon-hoc-tap', 'fa' => 'fa-graduation-cap'],
                                            2 => ['cls' => 'icon-tai-chinh', 'fa' => 'fa-wallet'],
                                            3 => ['cls' => 'icon-su-kien', 'fa' => 'fa-calendar-alt'],
                                            4 => ['cls' => 'icon-khan-cap', 'fa' => 'fa-exclamation-triangle'],
                                        ];
                                        $icon = $iconMap[$tb->loaiGui ?? 0] ?? $iconMap[0];
                                    @endphp
                                    <div class="nb-item {{ $isUnread ? 'unread' : '' }}" data-id="{{ $tb->thongBaoId }}"
                                        data-read="{{ $isUnread ? '0' : '1' }}">
                                        <div class="nb-item-icon {{ $icon['cls'] }}">
                                            <i class="fas {{ $icon['fa'] }}"></i>
                                        </div>
                                        <div class="nb-item-body">
                                            <div class="nb-item-title">
                                                @if ($tb->ghim)
                                                    <i class="fas fa-thumbtack me-1"
                                                        style="color:#f59e0b;font-size:.75rem;"></i>
                                                @endif
                                                {{ $tb->tieuDe }}
                                            </div>
                                            <div class="nb-item-preview">{{ Str::limit(strip_tags($tb->noiDung), 120) }}
                                            </div>
                                            <div class="nb-item-meta">
                                                <span class="nb-item-time">
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ optional($tb->ngayGui ?? $tb->created_at)->diffForHumans() }}
                                                </span>
                                                @if ($tb->uuTien == 2)
                                                    <span class="nb-priority khan-cap">🚨 Khẩn cấp</span>
                                                @elseif($tb->uuTien == 1)
                                                    <span class="nb-priority quan-trong">⚠️ Quan trọng</span>
                                                @endif
                                                @if ($tb->ghim)
                                                    <span class="nb-pin-badge">📌 Ghim</span>
                                                @endif
                                            </div>
                                        </div>
                                        @if ($isUnread)
                                            <div class="nb-unread-dot"></div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            {{-- Pagination --}}
                            @if ($items->hasPages())
                                <div class="mt-4 d-flex justify-content-center">
                                    {{ $items->links() }}
                                </div>
                            @endif
                        @else
                            <div class="nb-empty">
                                <div class="nb-empty-icon"><i class="fas fa-bell-slash"></i></div>
                                <div class="nb-empty-title">
                                    @if (request('filter') === 'unread')
                                        Không có thông báo chưa đọc nào
                                    @elseif(request('filter') === 'read')
                                        Chưa có thông báo nào được đọc
                                    @else
                                        Bạn chưa có thông báo nào
                                    @endif
                                </div>
                                <div class="nb-empty-sub">Thông báo từ hệ thống sẽ được hiển thị tại đây</div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── DETAIL MODAL ───────────────────────────────────────────── --}}
    <div class="modal fade" id="nbDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius:18px; overflow:hidden;">
                <div class="modal-header border-0"
                    style="background:linear-gradient(135deg,#6366f1,#7c3aed); padding:1.25rem 1.5rem;">
                    <h5 class="modal-title fw-bold text-white" id="nbModalTitle">—</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="nbModalMeta" class="d-flex flex-wrap gap-2 mb-3"></div>
                    <div id="nbModalContent" style="font-size:.93rem; color:#374151; line-height:1.75;"></div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <small class="text-muted" id="nbModalTime"></small>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        @php
            $nbItemsData = [];
            foreach ($items as $i) {
                $tb = $i->thongBao;
                if (!$tb) {
                    continue;
                }
                $nbItemsData[$tb->thongBaoId] = [
                    'id' => $tb->thongBaoId,
                    'tieuDe' => $tb->tieuDe,
                    'noiDung' => $tb->noiDung,
                    'loai' => $tb->getLoaiLabel(),
                    'uuTien' => $tb->getUuTienLabel(),
                    'ngayGui' => optional($tb->ngayGui ?? $tb->created_at)->format('d/m/Y H:i'),
                    'daDoc' => (bool) $i->daDoc,
                ];
            }
        @endphp
        const NB_ITEMS_DATA = @json($nbItemsData);

        // Click mở modal + mark read
        document.querySelectorAll('.nb-item[data-id]').forEach(item => {
            item.addEventListener('click', async function() {
                const id = this.dataset.id;
                const nb = NB_ITEMS_DATA[id];
                if (!nb) return;

                document.getElementById('nbModalTitle').textContent = nb.tieuDe;
                document.getElementById('nbModalContent').innerHTML = nb.noiDung;
                document.getElementById('nbModalTime').textContent = nb.ngayGui;
                document.getElementById('nbModalMeta').innerHTML = `
                <span class="badge bg-primary-subtle text-primary">${nb.loai}</span>
                <span class="badge bg-secondary-subtle text-secondary">${nb.uuTien}</span>`;

                new bootstrap.Modal(document.getElementById('nbDetailModal')).show();

                if (this.dataset.read === '0') {
                    this.classList.remove('unread');
                    this.dataset.read = '1';
                    const dot = this.querySelector('.nb-unread-dot');
                    if (dot) dot.remove();

                    if (window.NB_MARK_READ_URL) {
                        fetch(`${window.NB_MARK_READ_URL}/${id}/da-doc`, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': window.NB_CSRF
                            },
                        }).then(() => {
                            if (window.fetchUnreadCount) fetchUnreadCount();
                        });
                    }

                    const badge = document.getElementById('page-unread-badge');
                    if (badge) {
                        const current = parseInt(badge.textContent) || 1;
                        if (current <= 1) badge.remove();
                        else badge.textContent = (current - 1) + ' chưa đọc';
                    }
                }
            });
        });

        function refreshBadgeAfterMarkAll() {
            const badge = document.getElementById('page-unread-badge');
            if (badge) badge.remove();
            document.querySelectorAll('.nb-item.unread').forEach(el => {
                el.classList.remove('unread');
                el.dataset.read = '1';
                const dot = el.querySelector('.nb-unread-dot');
                if (dot) dot.remove();
            });
        }
        window.refreshBadgeAfterMarkAll = refreshBadgeAfterMarkAll;
    </script>
@endsection
