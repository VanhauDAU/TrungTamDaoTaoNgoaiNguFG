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

                        <x-client.account-breadcrumb :items="[
                            ['label' => 'Trang chủ', 'url' => route('home.index'), 'icon' => 'fas fa-home'],
                            ['label' => 'Tài khoản', 'url' => route('home.student.index')],
                            ['label' => 'Thông báo'],
                        ]" />

                        {{-- Header --}}
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
                            <h2 class="content-title mb-0">
                                <i class="fas fa-bell me-2" style="color:#6366f1;"></i> Thông báo
                                @if ($tongChuaDoc > 0)
                                    <span class="nb-unread-count-badge" id="page-unread-badge">
                                        {{ $tongChuaDoc }} chưa đọc
                                    </span>
                                @endif
                            </h2>
                            <button class="nb-mark-all-btn" onclick="markAllRead(); refreshAllBadges();">
                                <i class="fas fa-check-double me-1"></i> Đọc tất cả
                            </button>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <a class="nb-mark-all-btn {{ $scope === 'all' ? 'active' : '' }}" href="{{ route('home.thong-bao.index', ['scope' => 'all']) }}">Tất cả</a>
                            <a class="nb-mark-all-btn {{ $scope === 'unread' ? 'active' : '' }}" href="{{ route('home.thong-bao.index', ['scope' => 'unread']) }}">Chưa đọc</a>
                            <a class="nb-mark-all-btn {{ $scope === 'important' ? 'active' : '' }}" href="{{ route('home.thong-bao.index', ['scope' => 'important']) }}">Quan trọng</a>
                            <a class="nb-mark-all-btn {{ $scope === 'system' ? 'active' : '' }}" href="{{ route('home.thong-bao.index', ['scope' => 'system']) }}">Hệ thống</a>
                        </div>

                        {{-- ═══════════ GRID 2×2 CATEGORIES ═══════════ --}}
                        @php
                            $categoryOrder = [1, 2, 3, 4]; // Học tập | Tài chính / Sự kiện | Khẩn cấp
                            $heThong = $byCategory[0]; // Hệ thống – full width bên dưới
                        @endphp

                        <div class="nb-category-grid">
                            @foreach ($categoryOrder as $loaiKey)
                                @php $cat = $byCategory[$loaiKey]; @endphp
                                <div class="nb-cat-card" data-loai="{{ $loaiKey }}">

                                    {{-- Card Header --}}
                                    <div class="nb-cat-header" style="--cat-color: {{ $cat['color'] }};">
                                        <div class="nb-cat-icon">
                                            <i class="fas {{ $cat['icon'] }}"></i>
                                        </div>
                                        <span class="nb-cat-label">{{ $cat['label'] }}</span>
                                        @if ($cat['unread'] > 0)
                                            <span class="nb-cat-badge">{{ $cat['unread'] }}</span>
                                        @endif
                                        <span class="nb-cat-total">{{ $cat['total'] }}</span>
                                    </div>

                                    {{-- Danh sách thông báo trong ô --}}
                                    <div class="nb-cat-body" id="cat-body-{{ $loaiKey }}">
                                        @if ($cat['items']->isEmpty())
                                            <div class="nb-cat-empty">
                                                <i class="fas fa-inbox"></i>
                                                <span>Không có thông báo</span>
                                            </div>
                                        @else
                                            @foreach ($cat['items']->take(4) as $item)
                                                @php
                                                    $tb = $item->thongBao;
                                                    $isUnread = !$item->daDoc;
                                                    $hasTep = $tb->tepDinhs && $tb->tepDinhs->isNotEmpty();
                                                @endphp
                                                <div class="nb-cat-item {{ $isUnread ? 'unread' : '' }}"
                                                    data-id="{{ $tb->thongBaoId }}"
                                                    data-read="{{ $isUnread ? '0' : '1' }}"
                                                    onclick="openDetail({{ $tb->thongBaoId }}, this)">
                                                    @if ($isUnread)
                                                        <div class="nb-cat-dot"></div>
                                                    @endif
                                                    <div class="nb-cat-item-title">
                                                        @if ($tb->ghim)
                                                            <i class="fas fa-thumbtack"
                                                                style="color:#f59e0b;font-size:.7rem;"></i>
                                                        @endif
                                                        {{ $tb->tieuDe }}
                                                    </div>
                                                    <div class="nb-cat-item-preview">
                                                        {{ Str::limit(strip_tags($tb->noiDung), 80) }}
                                                    </div>
                                                    <div class="nb-cat-item-meta">
                                                        <span>{{ optional($tb->ngayGui ?? $tb->created_at)->diffForHumans() }}</span>
                                                        @if ($tb->uuTien == 2)
                                                            <span class="nb-priority-dot red">🚨</span>
                                                        @elseif ($tb->uuTien == 1)
                                                            <span class="nb-priority-dot amber">⚠️</span>
                                                        @endif
                                                        @if ($hasTep)
                                                            <span class="nb-attach-badge">
                                                                <i class="fas fa-paperclip"></i>
                                                                {{ $tb->tepDinhs->count() }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach

                                            {{-- Ẩn các mục thêm (>4) --}}
                                            @if ($cat['items']->count() > 4)
                                                @foreach ($cat['items']->slice(4) as $item)
                                                    @php
                                                        $tb = $item->thongBao;
                                                        $isUnread = !$item->daDoc;
                                                        $hasTep = $tb->tepDinhs && $tb->tepDinhs->isNotEmpty();
                                                    @endphp
                                                    <div class="nb-cat-item nb-cat-extra {{ $isUnread ? 'unread' : '' }}"
                                                        data-id="{{ $tb->thongBaoId }}"
                                                        data-read="{{ $isUnread ? '0' : '1' }}"
                                                        onclick="openDetail({{ $tb->thongBaoId }}, this)"
                                                        style="display:none;">
                                                        @if ($isUnread)
                                                            <div class="nb-cat-dot"></div>
                                                        @endif
                                                        <div class="nb-cat-item-title">{{ $tb->tieuDe }}</div>
                                                        <div class="nb-cat-item-preview">
                                                            {{ Str::limit(strip_tags($tb->noiDung), 80) }}</div>
                                                        <div class="nb-cat-item-meta">
                                                            <span>{{ optional($tb->ngayGui ?? $tb->created_at)->diffForHumans() }}</span>
                                                            @if ($hasTep)
                                                                <span class="nb-attach-badge"><i
                                                                        class="fas fa-paperclip"></i>
                                                                    {{ $tb->tepDinhs->count() }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach

                                                <button class="nb-show-more-btn"
                                                    onclick="toggleMore({{ $loaiKey }}, this)">
                                                    <i class="fas fa-chevron-down me-1"></i>
                                                    Xem thêm {{ $cat['items']->count() - 4 }} thông báo
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Hệ thống – full width --}}
                        <div class="nb-cat-card nb-cat-full-width mt-3" data-loai="0">
                            <div class="nb-cat-header" style="--cat-color: {{ $heThong['color'] }};">
                                <div class="nb-cat-icon"><i class="fas {{ $heThong['icon'] }}"></i></div>
                                <span class="nb-cat-label">{{ $heThong['label'] }}</span>
                                @if ($heThong['unread'] > 0)
                                    <span class="nb-cat-badge">{{ $heThong['unread'] }}</span>
                                @endif
                                <span class="nb-cat-total">{{ $heThong['total'] }}</span>
                            </div>
                            <div class="nb-cat-body nb-cat-body-row" id="cat-body-0">
                                @if ($heThong['items']->isEmpty())
                                    <div class="nb-cat-empty">
                                        <i class="fas fa-inbox"></i> <span>Không có thông báo hệ thống</span>
                                    </div>
                                @else
                                    @foreach ($heThong['items']->take(6) as $item)
                                        @php
                                            $tb = $item->thongBao;
                                            $isUnread = !$item->daDoc;
                                            $hasTep = $tb->tepDinhs && $tb->tepDinhs->isNotEmpty();
                                        @endphp
                                        <div class="nb-cat-item {{ $isUnread ? 'unread' : '' }}"
                                            data-id="{{ $tb->thongBaoId }}" data-read="{{ $isUnread ? '0' : '1' }}"
                                            onclick="openDetail({{ $tb->thongBaoId }}, this)">
                                            @if ($isUnread)
                                                <div class="nb-cat-dot"></div>
                                            @endif
                                            <div class="nb-cat-item-title">{{ $tb->tieuDe }}</div>
                                            <div class="nb-cat-item-preview">{{ Str::limit(strip_tags($tb->noiDung), 70) }}
                                            </div>
                                            <div class="nb-cat-item-meta">
                                                <span>{{ optional($tb->ngayGui ?? $tb->created_at)->diffForHumans() }}</span>
                                                @if ($hasTep)
                                                    <span class="nb-attach-badge"><i
                                                            class="fas fa-paperclip"></i>{{ $tb->tepDinhs->count() }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                    @if ($heThong['items']->count() > 6)
                                        @foreach ($heThong['items']->slice(6) as $item)
                                            @php
                                                $tb = $item->thongBao;
                                                $isUnread = !$item->daDoc;
                                            @endphp
                                            <div class="nb-cat-item nb-cat-extra {{ $isUnread ? 'unread' : '' }}"
                                                data-id="{{ $tb->thongBaoId }}" data-read="{{ $isUnread ? '0' : '1' }}"
                                                onclick="openDetail({{ $tb->thongBaoId }}, this)" style="display:none;">
                                                @if ($isUnread)
                                                    <div class="nb-cat-dot"></div>
                                                @endif
                                                <div class="nb-cat-item-title">{{ $tb->tieuDe }}</div>
                                                <div class="nb-cat-item-preview">
                                                    {{ Str::limit(strip_tags($tb->noiDung), 70) }}</div>
                                                <div class="nb-cat-item-meta">
                                                    <span>{{ optional($tb->ngayGui ?? $tb->created_at)->diffForHumans() }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                        <button class="nb-show-more-btn" onclick="toggleMore(0, this)">
                                            <i class="fas fa-chevron-down me-1"></i>
                                            Xem thêm {{ $heThong['items']->count() - 6 }} thông báo
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── DETAIL MODAL ──────────────────────────────────────────── --}}
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
                    {{-- File đính kèm trong modal --}}
                    <div id="nbModalAttach" style="display:none; margin-top:1.25rem;">
                        <div style="font-size:.82rem; font-weight:700; color:#374151; margin-bottom:.6rem;">
                            <i class="fas fa-paperclip me-1" style="color:#6366f1;"></i> File đính kèm
                        </div>
                        <div id="nbModalAttachList" class="nb-modal-attach-list"></div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <small class="text-muted" id="nbModalTime"></small>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="nbMarkUnreadBtn" style="display:none;">
                        Đánh dấu chưa đọc
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        {{-- Serialize tất cả thông báo ra JS (kể cả file đính kèm) --}}
        @php
            $allNbData = [];
            foreach ($byCategory as $loaiKey => $cat) {
                foreach ($cat['items'] as $i) {
                    $tb = $i->thongBao;
                    if (!$tb) {
                        continue;
                    }
                    $teps = $tb->tepDinhs
                        ? $tb->tepDinhs
                            ->map(
                                fn($t) => [
                                    'tenFile' => $t->tenFile,
                                    'url' => $t->url,
                                    'size' => $t->kichThuocHienThi,
                                    'icon' => $t->iconClass,
                                ],
                            )
                            ->values()
                            ->toArray()
                        : [];
                    $allNbData[$tb->thongBaoId] = [
                        'id' => $tb->thongBaoId,
                        'tieuDe' => $tb->tieuDe,
                        'noiDung' => $tb->noiDung,
                        'loai' => $tb->getLoaiLabel(),
                        'uuTien' => $tb->getUuTienLabel(),
                        'ngayGui' => optional($tb->ngayGui ?? $tb->created_at)->format('d/m/Y H:i'),
                        'tepDinhs' => $teps,
                    ];
                }
            }
        @endphp
        const NB_DATA = @json($allNbData);
        let CURRENT_OPEN_ID = null;
        let CURRENT_OPEN_EL = null;

        /* ── Mở modal chi tiết ───────────────────────────────── */
        function openDetail(id, el) {
            const nb = NB_DATA[id];
            if (!nb) return;
            CURRENT_OPEN_ID = id;
            CURRENT_OPEN_EL = el || null;

            document.getElementById('nbModalTitle').textContent = nb.tieuDe;
            document.getElementById('nbModalContent').innerHTML = nb.noiDung;
            document.getElementById('nbModalTime').textContent = nb.ngayGui;
            document.getElementById('nbModalMeta').innerHTML = `
            <span class="badge bg-primary-subtle text-primary">${nb.loai}</span>
            <span class="badge bg-secondary-subtle text-secondary">${nb.uuTien}</span>`;

            // File đính kèm
            const attachWrap = document.getElementById('nbModalAttach');
            const attachList = document.getElementById('nbModalAttachList');
            if (nb.tepDinhs && nb.tepDinhs.length > 0) {
                attachList.innerHTML = nb.tepDinhs.map(t => `
                <div class="nb-modal-attach-item">
                    <i class="fas ${t.icon} attach-icon-modal"></i>
                    <span class="attach-name-modal">${t.tenFile}</span>
                    <span class="attach-size-modal">${t.size}</span>
                    <a href="${t.url}" download="${t.tenFile}" class="attach-dl-modal">
                        <i class="fas fa-download me-1"></i>Tải
                    </a>
                </div>`).join('');
                attachWrap.style.display = 'block';
            } else {
                attachWrap.style.display = 'none';
            }

            new bootstrap.Modal(document.getElementById('nbDetailModal')).show();

            // Mark as read
            if (el && el.dataset.read === '0') {
                el.classList.remove('unread');
                el.dataset.read = '1';
                const dot = el.querySelector('.nb-cat-dot');
                if (dot) dot.remove();

                updatePageBadge(-1);

                if (window.NB_MARK_READ_URL) {
                    fetch(`${window.NB_MARK_READ_URL}/${id}/da-doc`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': window.NB_CSRF
                        }
                    }).then(() => {
                        if (window.fetchUnreadCount) fetchUnreadCount();
                    });
                }
            }

            const unreadBtn = document.getElementById('nbMarkUnreadBtn');
            if (unreadBtn) {
                unreadBtn.style.display = el && el.dataset.read === '1' ? '' : 'none';
            }
        }

        /* ── Xem thêm / thu gọn ─────────────────────────────── */
        function toggleMore(loai, btn) {
            const extras = document.querySelectorAll(`[data-loai="${loai}"] .nb-cat-extra`);
            const isHidden = [...extras].some(e => e.style.display === 'none');
            extras.forEach(e => e.style.display = isHidden ? '' : 'none');
            btn.innerHTML = isHidden ?
                '<i class="fas fa-chevron-up me-1"></i> Thu gọn' :
                `<i class="fas fa-chevron-down me-1"></i> Xem thêm ${extras.length} thông báo`;
        }

        /* ── Cập nhật badge số chưa đọc trên trang ─────────── */
        function updatePageBadge(delta) {
            const badge = document.getElementById('page-unread-badge');
            if (!badge) return;
            const curr = parseInt(badge.textContent) || 0;
            const newVal = curr + delta;
            if (newVal <= 0) badge.remove();
            else badge.textContent = newVal + ' chưa đọc';
        }

        /* ── Refresh tất cả sau mark all ────────────────────── */
        function refreshAllBadges() {
            const badge = document.getElementById('page-unread-badge');
            if (badge) badge.remove();
            document.querySelectorAll('.nb-cat-item.unread').forEach(el => {
                el.classList.remove('unread');
                el.dataset.read = '1';
                const dot = el.querySelector('.nb-cat-dot');
                if (dot) dot.remove();
            });
            // Xóa badge số trong header từng card
            document.querySelectorAll('.nb-cat-badge').forEach(b => b.remove());
        }
        window.refreshBadgeAfterMarkAll = refreshAllBadges;

        async function markUnreadFromModal() {
            if (!CURRENT_OPEN_ID || !CURRENT_OPEN_EL) return;
            await fetch(`${window.NB_MARK_UNREAD_URL}/${CURRENT_OPEN_ID}/chua-doc`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': window.NB_CSRF
                }
            });
            CURRENT_OPEN_EL.classList.add('unread');
            CURRENT_OPEN_EL.dataset.read = '0';
            if (!CURRENT_OPEN_EL.querySelector('.nb-cat-dot')) {
                const dot = document.createElement('div');
                dot.className = 'nb-cat-dot';
                CURRENT_OPEN_EL.prepend(dot);
            }
            const badge = document.getElementById('page-unread-badge');
            if (!badge) {
                const title = document.querySelector('.content-title');
                if (title) {
                    const b = document.createElement('span');
                    b.className = 'nb-unread-count-badge';
                    b.id = 'page-unread-badge';
                    b.textContent = '1 chưa đọc';
                    title.appendChild(b);
                }
            } else {
                const curr = parseInt(badge.textContent) || 0;
                badge.textContent = `${curr + 1} chưa đọc`;
            }
            const unreadBtn = document.getElementById('nbMarkUnreadBtn');
            if (unreadBtn) unreadBtn.style.display = 'none';
            if (window.fetchUnreadCount) window.fetchUnreadCount();
        }

        document.getElementById('nbMarkUnreadBtn')?.addEventListener('click', markUnreadFromModal);
    </script>
@endsection
