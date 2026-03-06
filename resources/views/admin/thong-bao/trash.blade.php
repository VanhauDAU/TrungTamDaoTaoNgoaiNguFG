@extends('layouts.admin')

@section('title', 'Thùng Rác - Thông Báo')
@section('page-title', 'Thùng Rác Thông Báo')
@section('breadcrumb', 'Nội dung & tương tác / Thông Báo / Thùng Rác')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/thong-bao/thong-bao.css') }}">
@endsection

@section('content')
    <div class="container-fluid px-4 py-2">

        {{-- ── ALERT ─────────────────────────────────────────────── --}}
        @if (session('success'))
            <div class="nb-alert-success mb-3">
                <i class="fas fa-circle-check"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        {{-- ── TOOLBAR ─────────────────────────────────────────────── --}}
        <form method="GET" action="{{ route('admin.thong-bao.trash') }}" id="filterForm">
            <div class="nb-toolbar">
                <div class="input-search">
                    <i class="fas fa-search icon-search"></i>
                    <input type="text" name="q" placeholder="Tìm tiêu đề, nội dung…" value="{{ request('q') }}">
                </div>

                <button type="submit" class="nb-btn nb-btn-primary nb-btn-sm">
                    <i class="fas fa-search"></i>
                </button>

                @if (request()->filled('q'))
                    <a href="{{ route('admin.thong-bao.trash') }}" class="nb-btn nb-btn-secondary nb-btn-sm">
                        <i class="fas fa-times"></i>
                    </a>
                @endif

                <div class="nb-spacer"></div>

                <button type="button" id="btnBulkRestore" class="nb-btn nb-btn-secondary nb-btn-sm" disabled>
                    <i class="fas fa-rotate-left"></i> Khôi phục (<span id="selectedCount">0</span>)
                </button>

                <a href="{{ route('admin.thong-bao.index') }}" class="nb-btn nb-btn-primary">
                    <i class="fas fa-arrow-left"></i> Quay lại danh sách
                </a>
            </div>
        </form>

        {{-- ── EMPTY / COUNT ───────────────────────────────────────── --}}
        <div class="mb-3" style="color:#6b7280;font-size:.9rem;">
            <i class="fas fa-trash-can me-1"></i>
            Có <strong>{{ $soLuong }}</strong> thông báo trong thùng rác.
            Các thông báo sẽ <strong>không tự động xóa vĩnh viễn</strong>, bạn cần thao tác thủ công.
        </div>

        {{-- ── TABLE ─────────────────────────────────────────────── --}}
        <div class="nb-table-card">
            <table class="nb-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" class="cb-check" id="checkAll"></th>
                        <th>LOẠI</th>
                        <th>THÔNG BÁO</th>
                        <th>NGƯỜI GỬI</th>
                        <th>NGÀY XÓA</th>
                        <th>THAO TÁC</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($thongBaos as $tb)
                        <tr>
                            <td><input type="checkbox" class="cb-check cb-row" value="{{ $tb->thongBaoId }}"></td>
                            <td>
                                <span class="nb-badge {{ $tb->getLoaiBadgeClass() }}">
                                    {{ $tb->getLoaiLabel() }}
                                </span>
                            </td>
                            <td class="nb-title-cell">
                                <div class="tb-title">{{ Str::limit($tb->tieuDe, 60) }}</div>
                                <div class="tb-preview">{{ Str::limit(strip_tags($tb->noiDung), 80) }}</div>
                            </td>
                            <td>
                                @if ($tb->nguoiGui)
                                    @php
                                        $g = $tb->nguoiGui;
                                        $ten = $g->hoSoNguoiDung->hoTen ?? ($g->nhanSu->hoTen ?? $g->taiKhoan);
                                    @endphp
                                    <div class="nb-sender">
                                        <div class="nb-avatar">{{ strtoupper(mb_substr($ten, 0, 1)) }}</div>
                                        <div class="nb-sender-name">{{ $ten }}</div>
                                    </div>
                                @else
                                    <span class="nb-date">Hệ thống</span>
                                @endif
                            </td>
                            <td class="nb-date">
                                {{ optional($tb->deleted_at)->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <div class="nb-actions">
                                    {{-- Khôi phục --}}
                                    <form method="POST" action="{{ route('admin.thong-bao.restore', $tb->thongBaoId) }}"
                                        style="display:inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="nb-action-btn view" title="Khôi phục"
                                            onclick="return confirm('Khôi phục thông báo này?')">
                                            <i class="fas fa-rotate-left"></i>
                                        </button>
                                    </form>

                                    {{-- Xóa vĩnh viễn --}}
                                    <button class="nb-action-btn del" title="Xóa vĩnh viễn"
                                        onclick="forceDelete({{ $tb->thongBaoId }})">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <form id="force-del-form-{{ $tb->thongBaoId }}" method="POST"
                                        action="{{ route('admin.thong-bao.force-destroy', $tb->thongBaoId) }}"
                                        style="display:none">
                                        @csrf @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="nb-empty">
                                    <div class="icon-empty"><i class="fas fa-trash-can"></i></div>
                                    <p>Thùng rác trống.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($thongBaos->hasPages())
                <div class="nb-pagination">
                    <div class="page-info">
                        Hiển thị {{ $thongBaos->firstItem() }}–{{ $thongBaos->lastItem() }} / {{ $thongBaos->total() }}
                        thông báo
                    </div>
                    {{ $thongBaos->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection

@section('script')
    <script>
        const BULK_RESTORE_URL = '{{ route('admin.thong-bao.bulk-restore') }}';

        // ── Checkbox logic ─────────────────────────────────────────
        const checkAll = document.getElementById('checkAll');
        const rows = () => document.querySelectorAll('.cb-row');
        const counter = document.getElementById('selectedCount');
        const btnBulkRestore = document.getElementById('btnBulkRestore');

        function updateBulkBtn() {
            const cnt = document.querySelectorAll('.cb-row:checked').length;
            counter.textContent = cnt;
            btnBulkRestore.disabled = cnt === 0;
        }

        checkAll.addEventListener('change', function() {
            rows().forEach(cb => cb.checked = this.checked);
            updateBulkBtn();
        });

        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('cb-row')) updateBulkBtn();
        });

        // ── Force delete (single) ───────────────────────────────────
        function forceDelete(id) {
            if (!confirm('Xóa VĨNH VIỄN thông báo này? Hành động này không thể hoàn tác!')) return;
            document.getElementById('force-del-form-' + id).submit();
        }

        // ── Bulk restore ─────────────────────────────────────────────
        btnBulkRestore.addEventListener('click', function() {
            const ids = [...document.querySelectorAll('.cb-row:checked')].map(cb => cb.value);
            if (!ids.length) return;
            if (!confirm(`Khôi phục ${ids.length} thông báo?`)) return;

            fetch(BULK_RESTORE_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        ids
                    }),
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) location.reload();
                    else alert('Có lỗi xảy ra!');
                });
        });
    </script>
@endsection
