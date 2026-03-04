@extends('layouts.admin')

@section('title', 'Danh sách liên hệ')
@section('page-title', 'Liên hệ')
@section('breadcrumb', 'Quản lý tương tác · Danh sách liên hệ')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/lien-he/index.css') }}">
    <style>
        /* Bulk-action bar */
        .lh-bulk-bar {
            display: none;
            align-items: center;
            gap: 12px;
            background: linear-gradient(135deg, #eff6ff, #f0f4f8);
            border: 1.5px solid #93c5fd;
            border-radius: 10px;
            padding: 10px 18px;
            margin-bottom: 16px;
            font-size: 0.85rem;
            color: #1e40af;
            font-weight: 600;
        }

        .lh-bulk-bar.active {
            display: flex;
        }

        .lh-bulk-bar .bulk-count {
            background: #3b82f6;
            color: #fff;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 0.78rem;
        }

        .btn-bulk-delete {
            margin-left: auto;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 16px;
            border-radius: 8px;
            background: #dc2626;
            color: #fff;
            font-size: 0.8rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: background .18s;
        }

        .btn-bulk-delete:hover {
            background: #b91c1c;
        }

        .btn-bulk-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 16px;
            border-radius: 8px;
            background: #059669;
            color: #fff;
            font-size: 0.8rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: background .18s;
        }

        .btn-bulk-status:hover {
            background: #047857;
        }

        /* Checkbox style */
        .lh-checkbox {
            width: 17px;
            height: 17px;
            accent-color: #3b82f6;
            cursor: pointer;
        }

        /* Inline status toggle */
        .btn-toggle-status {
            border: none;
            background: none;
            cursor: pointer;
            padding: 0;
        }
    </style>
@endsection

@section('content')

    {{-- ── Page Header ───────────────────────────────────────────── --}}
    <div class="lh-page-header">
        <div class="lh-page-title">
            <i class="fas fa-envelope-open-text me-2" style="color:#27c4b5"></i>Danh sách liên hệ
            <span>{{ $lienHes->total() }} kết quả</span>
        </div>
        <a href="{{ route('admin.lien-he.trash') }}" class="btn-filter btn-filter-reset" style="gap:8px;font-weight:600">
            <i class="fas fa-trash-can" style="color:#dc2626"></i> Thùng rác
            @if ($tongXoa > 0)
                <span
                    style="background:#dc2626;color:#fff;font-size:0.72rem;padding:2px 8px;border-radius:20px;font-weight:700">{{ $tongXoa }}</span>
            @endif
        </a>
    </div>

    {{-- ── Stats strip ────────────────────────────────────────────── --}}
    <div class="lh-stats">
        <div class="lh-stat-card">
            <div class="lh-stat-icon total"><i class="fas fa-inbox"></i></div>
            <div>
                <div class="lh-stat-value">{{ number_format($tongSo) }}</div>
                <div class="lh-stat-label">Tổng liên hệ</div>
            </div>
        </div>
        <div class="lh-stat-card">
            <div class="lh-stat-icon active"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="lh-stat-value">{{ number_format($daXuLy) }}</div>
                <div class="lh-stat-label">Đã xử lý</div>
            </div>
        </div>
        <div class="lh-stat-card">
            <div class="lh-stat-icon new"><i class="fas fa-hourglass-half"></i></div>
            <div>
                <div class="lh-stat-value">{{ number_format($chuaXuLy) }}</div>
                <div class="lh-stat-label">Chưa xử lý</div>
            </div>
        </div>
    </div>

    {{-- ── Filter bar ─────────────────────────────────────────────── --}}
    <form action="{{ route('admin.lien-he.index') }}" method="GET" class="lh-filter-bar" id="filter-form">
        {{-- Search --}}
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" name="q" class="search-input"
                placeholder="Tìm theo tên, email, số điện thoại, tiêu đề..." value="{{ request('q') }}" autocomplete="off">
        </div>

        {{-- Trạng thái --}}
        <select name="trangThai" onchange="this.form.submit()">
            <option value="">Tất cả trạng thái</option>
            <option value="1" {{ request('trangThai') === '1' ? 'selected' : '' }}>Đã xử lý</option>
            <option value="0" {{ request('trangThai') === '0' ? 'selected' : '' }}>Chưa xử lý</option>
        </select>

        {{-- Sắp xếp --}}
        <select name="orderBy" onchange="this.form.submit()">
            <option value="LienHeId" {{ request('orderBy', 'LienHeId') === 'LienHeId' ? 'selected' : '' }}>Mới nhất
            </option>
            <option value="created_at" {{ request('orderBy') === 'created_at' ? 'selected' : '' }}>Ngày gửi (cũ - mới)
            </option>
            <option value="hoTen" {{ request('orderBy') === 'hoTen' ? 'selected' : '' }}>Người gửi A-Z</option>
        </select>
        <input type="hidden" name="dir" value="{{ request('dir', 'desc') }}">

        {{-- Buttons --}}
        <button type="submit" class="btn-filter btn-filter-primary">
            <i class="fas fa-filter"></i> Lọc
        </button>
        <a href="{{ route('admin.lien-he.index') }}" class="btn-filter btn-filter-reset">
            <i class="fas fa-times"></i> Đặt lại
        </a>
    </form>

    {{-- ── Bulk action bar (hiện khi chọn checkbox) ────────────── --}}
    <div class="lh-bulk-bar" id="bulk-bar">
        <i class="fas fa-check-double"></i>
        Đã chọn <span class="bulk-count" id="bulk-count">0</span> liên hệ
        <button type="button" class="btn-bulk-status" onclick="confirmBulkStatus()">
            <i class="fas fa-arrows-rotate"></i> Chuyển trạng thái
        </button>
        <button type="button" class="btn-bulk-delete" onclick="confirmBulkDelete()">
            <i class="fas fa-trash"></i> Xóa đã chọn
        </button>
    </div>

    {{-- ── Table card ─────────────────────────────────────────────── --}}
    <div class="lh-card">
        <div class="lh-table-header">
            <div class="lh-table-title"><i class="fas fa-list me-2"></i> Danh sách liên hệ</div>
            <div class="lh-table-count">
                Hiển thị {{ $lienHes->firstItem() ?? 0 }}–{{ $lienHes->lastItem() ?? 0 }}
                / {{ $lienHes->total() }} bản ghi
            </div>
        </div>

        @if ($lienHes->isEmpty())
            <div class="lh-empty">
                <i class="fas fa-envelope-open"></i>
                <p>Không tìm thấy liên hệ nào.</p>
                @if (request()->anyFilled(['q', 'trangThai']))
                    <a href="{{ route('admin.lien-he.index') }}" class="btn-filter btn-filter-reset">
                        Xóa bộ lọc
                    </a>
                @endif
            </div>
        @else
            <div style="overflow-x:auto">
                <table class="lh-table">
                    <thead>
                        <tr>
                            <th style="width:36px">
                                <input type="checkbox" class="lh-checkbox" id="check-all" title="Chọn tất cả">
                            </th>
                            <th style="width:44px">#</th>
                            <th>
                                <a class="sort-link"
                                    href="{{ request()->fullUrlWithQuery(['orderBy' => 'hoTen', 'dir' => request('orderBy') === 'hoTen' && request('dir') === 'asc' ? 'desc' : 'asc']) }}">
                                    Người gửi
                                    @if (request('orderBy') === 'hoTen')
                                        <i class="fas fa-sort-{{ request('dir', 'asc') === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort" style="opacity:.4"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Email & Phone</th>
                            <th>Tiêu đề</th>
                            <th>
                                <a class="sort-link"
                                    href="{{ request()->fullUrlWithQuery(['orderBy' => 'created_at', 'dir' => request('orderBy') === 'created_at' && request('dir') === 'desc' ? 'asc' : 'desc']) }}">
                                    Thời gian gửi
                                    @if (request('orderBy', 'LienHeId') === 'LienHeId' || request('orderBy') === 'created_at')
                                        <i class="fas fa-sort-{{ request('dir', 'desc') === 'desc' ? 'down' : 'up' }}"></i>
                                    @else
                                        <i class="fas fa-sort" style="opacity:.4"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Trạng thái</th>
                            <th style="text-align:center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lienHes as $lh)
                            <tr>
                                <td>
                                    <input type="checkbox" class="lh-checkbox row-check" value="{{ $lh->lienHeId }}">
                                </td>

                                <td style="color:#8899a6;font-size:0.78rem">
                                    {{ $lienHes->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    <div class="lh-name">{{ $lh->hoTen }}</div>
                                </td>

                                <td>
                                    <div class="lh-info-sub" style="color: #2d3748">{{ $lh->email ?? '—' }}</div>
                                    <div class="lh-info-sub"><i class="fas fa-phone-alt me-1"
                                            style="font-size: 0.7rem; color: #aab8c2"></i>{{ $lh->soDienThoai ?? '—' }}
                                    </div>
                                </td>

                                <td>
                                    <span style="font-weight: 500;">
                                        {{ \Illuminate\Support\Str::limit($lh->tieuDe, 40) }}
                                    </span>
                                </td>

                                <td style="color:#8899a6;font-size:0.8rem">
                                    {{ $lh->created_at->format('d/m/Y H:i') }}
                                    <div class="lh-info-sub">{{ $lh->created_at->diffForHumans() }}</div>
                                </td>

                                <td>
                                    <button type="button" class="btn-toggle-status"
                                        onclick="toggleStatus({{ $lh->lienHeId }}, {{ $lh->trangThai }})"
                                        title="Click để chuyển trạng thái">
                                        @if ($lh->trangThai == 1)
                                            <span class="badge-active">
                                                <i class="fas fa-check-circle" style="font-size:.5em"></i> Đã xử lý
                                            </span>
                                        @else
                                            <span class="badge-inactive">
                                                <i class="fas fa-hourglass-half" style="font-size:.5em"></i> Chưa xử lý
                                            </span>
                                        @endif
                                    </button>
                                </td>

                                <td>
                                    <div class="lh-actions">
                                        <a href="{{ route('admin.lien-he.edit', $lh->lienHeId) }}"
                                            class="btn-action btn-action-edit" title="Chi tiết / Cập nhật">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn-action btn-action-del" title="Xóa"
                                            onclick="confirmDelete({{ $lh->lienHeId }}, '{{ addslashes($lh->hoTen) }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($lienHes->hasPages())
                <div class="lh-pagination">
                    <div class="lh-pagination-info">
                        Trang {{ $lienHes->currentPage() }} / {{ $lienHes->lastPage() }}
                    </div>
                    {{ $lienHes->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- Hidden forms INSIDE @section('content') --}}
    <form id="delete-form" method="POST" style="display:none">
        @csrf
        @method('DELETE')
    </form>

    <form id="bulk-delete-form" method="POST" action="{{ route('admin.lien-he.bulk-destroy') }}" style="display:none">
        @csrf
        @method('DELETE')
        <input type="hidden" name="ids" id="bulk-ids">
    </form>

    <form id="toggle-status-form" method="POST" style="display:none">
        @csrf
        @method('PUT')
        <input type="hidden" name="trangThai" id="toggle-trangThai">
    </form>

    <form id="bulk-status-form" method="POST" action="{{ route('admin.lien-he.bulk-status') }}" style="display:none">
        @csrf
        @method('PATCH')
        <input type="hidden" name="ids" id="bulk-status-ids">
        <input type="hidden" name="trangThai" id="bulk-trangThai">
    </form>

@endsection

@section('script')
    <script>
        // ── Single delete ───────────────────────────────────────
        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Xóa liên hệ?',
                html: `Liên hệ từ <strong>${name}</strong> sẽ được chuyển vào thùng rác.<br><small style="color:#8899a6">Bạn có thể khôi phục bất kỳ lúc nào.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-trash me-1"></i> Xóa',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusCancel: true,
            }).then(result => {
                if (result.isConfirmed) {
                    const form = document.getElementById('delete-form');
                    form.action = `/admin/lien-he/${id}`;
                    form.submit();
                }
            });
        }

        // ── Inline toggle status ────────────────────────────────
        function toggleStatus(id, current) {
            const newStatus = current == 1 ? 0 : 1;
            const label = newStatus == 1 ? 'Đã xử lý' : 'Chưa xử lý';
            Swal.fire({
                title: 'Chuyển trạng thái?',
                html: `Chuyển trạng thái liên hệ sang <strong>${label}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-check me-1"></i> Xác nhận',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#059669',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusCancel: true,
            }).then(result => {
                if (result.isConfirmed) {
                    const form = document.getElementById('toggle-status-form');
                    form.action = `/admin/lien-he/${id}`;
                    document.getElementById('toggle-trangThai').value = newStatus;
                    form.submit();
                }
            });
        }

        // ── Bulk status change ──────────────────────────────────
        function confirmBulkStatus() {
            const checked = document.querySelectorAll('.row-check:checked');
            const ids = Array.from(checked).map(cb => cb.value);
            if (ids.length === 0) return;

            Swal.fire({
                title: `Chuyển trạng thái ${ids.length} liên hệ?`,
                html: `Chọn trạng thái mới cho <strong>${ids.length}</strong> liên hệ đã chọn:`,
                icon: 'question',
                input: 'select',
                inputOptions: {
                    '1': '✅ Đã xử lý',
                    '0': '⏳ Chưa xử lý',
                },
                inputPlaceholder: '-- Chọn trạng thái --',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-check me-1"></i> Xác nhận',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#059669',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusCancel: true,
                inputValidator: (value) => {
                    if (value === '') return 'Vui lòng chọn trạng thái!';
                },
            }).then(result => {
                if (result.isConfirmed) {
                    document.getElementById('bulk-status-ids').value = ids.join(',');
                    document.getElementById('bulk-trangThai').value = result.value;
                    document.getElementById('bulk-status-form').submit();
                }
            });
        }

        // ── Checkbox / Bulk delete ──────────────────────────────
        const checkAll = document.getElementById('check-all');
        const rowChecks = document.querySelectorAll('.row-check');
        const bulkBar = document.getElementById('bulk-bar');
        const bulkCount = document.getElementById('bulk-count');

        function updateBulkBar() {
            const checked = document.querySelectorAll('.row-check:checked');
            bulkCount.textContent = checked.length;
            bulkBar.classList.toggle('active', checked.length > 0);
        }

        checkAll?.addEventListener('change', function() {
            rowChecks.forEach(cb => cb.checked = this.checked);
            updateBulkBar();
        });

        rowChecks.forEach(cb => cb.addEventListener('change', function() {
            checkAll.checked = document.querySelectorAll('.row-check:checked').length === rowChecks.length;
            updateBulkBar();
        }));

        function confirmBulkDelete() {
            const checked = document.querySelectorAll('.row-check:checked');
            const ids = Array.from(checked).map(cb => cb.value);
            if (ids.length === 0) return;

            Swal.fire({
                title: `Xóa ${ids.length} liên hệ?`,
                html: `<strong>${ids.length}</strong> liên hệ đã chọn sẽ được chuyển vào thùng rác.<br><small style="color:#8899a6">Bạn có thể khôi phục bất kỳ lúc nào.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-trash me-1"></i> Xóa tất cả',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusCancel: true,
            }).then(result => {
                if (result.isConfirmed) {
                    document.getElementById('bulk-ids').value = ids.join(',');
                    document.getElementById('bulk-delete-form').submit();
                }
            });
        }

        // Enter để submit filter
        document.querySelector('.search-input')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') document.getElementById('filter-form').submit();
        });
    </script>
@endsection
