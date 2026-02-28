@extends('layouts.admin')

@section('title', 'Quản lý Bài Viết')
@section('page-title', 'Bài Viết / Blog')
@section('breadcrumb', 'Nội dung · Danh sách bài viết')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/bai-viet/index.css') }}">
@endsection

@section('content')

    {{-- ── Page Header ─────────────────────────────────────────── --}}
    <div class="bv-page-header">
        <div class="bv-page-title">
            <i class="fas fa-newspaper" style="color:#1d4ed8"></i>
            Danh sách bài viết
            <span>{{ $baiViets->total() }} kết quả</span>
        </div>
        <div style="display:flex;gap:10px;align-items:center">
            @if ($daXoa > 0)
                <a href="{{ route('admin.bai-viet.trash') }}" class="btn-add-bv"
                    style="background:linear-gradient(135deg,#dc2626,#f87171)">
                    <i class="fas fa-trash-alt"></i> Thùng rác
                    <span
                        style="background:rgba(255,255,255,.25);padding:1px 8px;border-radius:12px;font-size:.75rem">{{ $daXoa }}</span>
                </a>
            @endif
            <a href="{{ route('admin.danh-muc-bai-viet.index') }}" class="btn-add-bv"
                style="background:linear-gradient(135deg,#7c3aed,#a78bfa)">
                <i class="fas fa-folder-open"></i> Danh mục
            </a>
            <a href="{{ route('admin.bai-viet.create') }}" class="btn-add-bv">
                <i class="fas fa-plus"></i> Thêm bài viết
            </a>
        </div>
    </div>

    {{-- ── Bulk action bar (hidden by default) ──────────────────── --}}
    <div class="bv-bulk-bar" id="bulk-bar" style="display:none">
        <div class="bv-bulk-bar-inner">
            <span id="bulk-count">0</span> bài viết đã chọn
            <button type="button" class="bv-bulk-btn bv-bulk-btn-danger" onclick="bulkDelete()">
                <i class="fas fa-trash"></i> Xóa đã chọn
            </button>
            <button type="button" class="bv-bulk-btn bv-bulk-btn-cancel" onclick="clearSelection()">
                <i class="fas fa-times"></i> Bỏ chọn
            </button>
        </div>
    </div>

    {{-- ── Flash messages ───────────────────────────────────────── --}}
    @if (session('success'))
        <div class="bv-alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="bv-alert-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    {{-- ── Stats strip ──────────────────────────────────────────── --}}
    <div class="bv-stats">
        <div class="bv-stat-card">
            <div class="bv-stat-icon total"><i class="fas fa-newspaper"></i></div>
            <div>
                <div class="bv-stat-value">{{ number_format($tongSo) }}</div>
                <div class="bv-stat-label">Tổng bài viết</div>
            </div>
        </div>
        <div class="bv-stat-card">
            <div class="bv-stat-icon active"><i class="fas fa-circle-check"></i></div>
            <div>
                <div class="bv-stat-value">{{ number_format($daXuatBan) }}</div>
                <div class="bv-stat-label">Đã xuất bản</div>
            </div>
        </div>
        <div class="bv-stat-card">
            <div class="bv-stat-icon draft"><i class="fas fa-file-alt"></i></div>
            <div>
                <div class="bv-stat-value">{{ number_format($banNhap) }}</div>
                <div class="bv-stat-label">Bản nháp</div>
            </div>
        </div>
        <div class="bv-stat-card">
            <div class="bv-stat-icon views"><i class="fas fa-eye"></i></div>
            <div>
                <div class="bv-stat-value">{{ number_format($tongLuotXem) }}</div>
                <div class="bv-stat-label">Tổng lượt xem</div>
            </div>
        </div>
    </div>

    {{-- ── Filter bar ────────────────────────────────────────────── --}}
    <form action="{{ route('admin.bai-viet.index') }}" method="GET" class="bv-filter-bar" id="bv-filter-form">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" name="q" class="search-input" placeholder="Tìm theo tiêu đề, tóm tắt..."
                value="{{ request('q') }}" autocomplete="off">
        </div>

        <select name="danhMucId" onchange="this.form.submit()">
            <option value="">Tất cả danh mục</option>
            @foreach ($danhMucs as $dm)
                <option value="{{ $dm->danhMucId }}" {{ request('danhMucId') == $dm->danhMucId ? 'selected' : '' }}>
                    {{ $dm->tenDanhMuc }}
                </option>
            @endforeach
        </select>

        <select name="trangThai" onchange="this.form.submit()">
            <option value="">Tất cả trạng thái</option>
            <option value="1" {{ request('trangThai') === '1' ? 'selected' : '' }}>Đã xuất bản</option>
            <option value="0" {{ request('trangThai') === '0' ? 'selected' : '' }}>Bản nháp</option>
        </select>

        <select name="orderBy" onchange="this.form.submit()">
            <option value="baiVietId" {{ request('orderBy', 'baiVietId') === 'baiVietId' ? 'selected' : '' }}>Mới nhất
            </option>
            <option value="tieuDe" {{ request('orderBy') === 'tieuDe' ? 'selected' : '' }}>Tiêu đề A-Z</option>
            <option value="luotXem" {{ request('orderBy') === 'luotXem' ? 'selected' : '' }}>Lượt xem</option>
        </select>

        <button type="submit" class="bv-btn-filter bv-btn-filter-primary">
            <i class="fas fa-filter"></i> Lọc
        </button>
        <a href="{{ route('admin.bai-viet.index') }}" class="bv-btn-filter bv-btn-filter-reset">
            <i class="fas fa-times"></i> Đặt lại
        </a>
    </form>

    {{-- ── Table ─────────────────────────────────────────────── --}}
    @if ($baiViets->isEmpty())
        <div class="bv-empty">
            <i class="fas fa-newspaper"></i>
            <p>Không tìm thấy bài viết nào.</p>
            @if (request()->anyFilled(['q', 'danhMucId', 'trangThai']))
                <a href="{{ route('admin.bai-viet.index') }}" class="bv-btn-filter bv-btn-filter-reset"
                    style="margin-top:12px;display:inline-flex">
                    Xóa bộ lọc
                </a>
            @endif
        </div>
    @else
        <div class="bv-table-wrap">
            <table class="bv-table">
                <thead>
                    <tr>
                        <th style="width:40px">
                            <label class="bv-checkbox-wrap">
                                <input type="checkbox" id="select-all">
                                <span class="bv-checkbox-custom"></span>
                            </label>
                        </th>
                        <th style="width:60px">Ảnh</th>
                        <th>Tiêu đề</th>
                        <th>Danh mục</th>
                        <th>Tags</th>
                        <th style="width:90px">Trạng thái</th>
                        <th style="width:90px">Lượt xem</th>
                        <th style="width:110px">Ngày tạo</th>
                        <th style="width:100px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($baiViets as $bv)
                        <tr data-id="{{ $bv->baiVietId }}">
                            <td>
                                <label class="bv-checkbox-wrap">
                                    <input type="checkbox" class="bv-row-check" value="{{ $bv->baiVietId }}">
                                    <span class="bv-checkbox-custom"></span>
                                </label>
                            </td>
                            <td>
                                @if ($bv->anhDaiDien)
                                    <img src="{{ asset('storage/' . $bv->anhDaiDien) }}" alt="" class="bv-row-thumb">
                                @else
                                    <div class="bv-row-thumb-placeholder"><i class="fas fa-image"></i></div>
                                @endif
                            </td>
                            <td>
                                <div class="bv-row-title">
                                    <a href="{{ route('admin.bai-viet.show', $bv->baiVietId) }}">{{ $bv->tieuDe }}</a>
                                </div>
                                <div class="bv-row-author">
                                    <i class="fas fa-user" style="font-size:.65rem"></i>
                                    {{ $bv->taiKhoan->hoSoNguoiDung->hoTen ?? ($bv->taiKhoan->taiKhoan ?? 'N/A') }}
                                </div>
                            </td>
                            <td>
                                @foreach ($bv->danhMucs as $dm)
                                    <span class="bv-cat">{{ $dm->tenDanhMuc }}</span>
                                @endforeach
                            </td>
                            <td>
                                <div class="bv-tags">
                                    @foreach ($bv->tags->take(3) as $tag)
                                        <span class="bv-tag">{{ $tag->tenTag }}</span>
                                    @endforeach
                                    @if ($bv->tags->count() > 3)
                                        <span class="bv-tag"
                                            style="background:#f1f5f9;color:#64748b">+{{ $bv->tags->count() - 3 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <label class="bv-toggle">
                                    <input type="checkbox" {{ $bv->trangThai ? 'checked' : '' }}
                                        onchange="toggleBaiViet({{ $bv->baiVietId }}, this)">
                                    <span class="bv-toggle-slider"></span>
                                </label>
                            </td>
                            <td>
                                <div class="bv-views">
                                    <i class="fas fa-eye"></i>
                                    {{ number_format($bv->luotXem) }}
                                </div>
                            </td>
                            <td style="font-size:.8rem;color:#64748b">
                                {{ $bv->created_at ? $bv->created_at->format('d/m/Y') : '—' }}
                            </td>
                            <td>
                                <div class="bv-actions">
                                    <a href="{{ route('admin.bai-viet.show', $bv->baiVietId) }}" class="bv-btn-action bv-btn-view"
                                        title="Xem">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.bai-viet.edit', $bv->baiVietId) }}" class="bv-btn-action bv-btn-edit"
                                        title="Sửa">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <button type="button" class="bv-btn-action bv-btn-del" title="Xóa"
                                        onclick="confirmDeleteBV({{ $bv->baiVietId }}, '{{ addslashes($bv->tieuDe) }}')">
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
        @if ($baiViets->hasPages())
            <div class="bv-pagination">
                <div class="bv-pagination-info">
                    Trang {{ $baiViets->currentPage() }} / {{ $baiViets->lastPage() }}
                    · {{ $baiViets->total() }} bài viết
                </div>
                {{ $baiViets->links() }}
            </div>
        @endif
    @endif

@endsection

{{-- Hidden delete form --}}
<form id="delete-bv-form" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>

@section('script')
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // ── Select all / individual checkboxes ─────────────────────
        const selectAll = document.getElementById('select-all');
        const bulkBar = document.getElementById('bulk-bar');
        const bulkCount = document.getElementById('bulk-count');

        function getCheckedIds() {
            return [...document.querySelectorAll('.bv-row-check:checked')].map(el => parseInt(el.value));
        }

        function updateBulkBar() {
            const ids = getCheckedIds();
            if (ids.length > 0) {
                bulkBar.style.display = 'block';
                bulkCount.textContent = ids.length;
            } else {
                bulkBar.style.display = 'none';
            }
            // Update "select all" state
            const allChecks = document.querySelectorAll('.bv-row-check');
            if (selectAll) {
                selectAll.checked = allChecks.length > 0 && ids.length === allChecks.length;
                selectAll.indeterminate = ids.length > 0 && ids.length < allChecks.length;
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                document.querySelectorAll('.bv-row-check').forEach(cb => cb.checked = this.checked);
                updateBulkBar();
            });
        }

        document.querySelectorAll('.bv-row-check').forEach(cb => {
            cb.addEventListener('change', updateBulkBar);
        });

        function clearSelection() {
            document.querySelectorAll('.bv-row-check').forEach(cb => cb.checked = false);
            if (selectAll) selectAll.checked = false;
            updateBulkBar();
        }

        // ── Bulk delete (soft) ─────────────────────────────────────
        function bulkDelete() {
            const ids = getCheckedIds();
            if (ids.length === 0) return;

            Swal.fire({
                title: 'Xóa nhiều bài viết?',
                html: `Chuyển <strong>${ids.length} bài viết</strong> vào thùng rác?<br>
                           <small style="color:#64748b">Bạn có thể khôi phục sau từ thùng rác.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: `<i class="fas fa-trash me-1"></i> Xóa ${ids.length} bài viết`,
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusCancel: true,
            }).then(result => {
                if (!result.isConfirmed) return;

                fetch('{{ route("admin.bai-viet.bulk-destroy") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ ids }),
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Toast.fire({ icon: 'success', title: data.message });
                            // Remove rows from DOM
                            ids.forEach(id => {
                                const row = document.querySelector(`tr[data-id="${id}"]`);
                                if (row) row.remove();
                            });
                            clearSelection();
                            // Reload after short delay to update stats
                            setTimeout(() => location.reload(), 1200);
                        }
                    })
                    .catch(() => Toast.fire({ icon: 'error', title: 'Có lỗi xảy ra.' }));
            });
        }

        // ── Toggle trạng thái AJAX ─────────────────────────────────
        function toggleBaiViet(id, el) {
            fetch(`/admin/bai-viet/${id}/toggle-status`, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) Toast.fire({ icon: 'success', title: data.message });
                })
                .catch(() => {
                    el.checked = !el.checked;
                    Toast.fire({ icon: 'error', title: 'Có lỗi xảy ra.' });
                });
        }

        // ── Xác nhận xóa đơn ──────────────────────────────────────
        function confirmDeleteBV(id, name) {
            Swal.fire({
                title: 'Xóa bài viết?',
                html: `Chuyển <strong>${name}</strong> vào thùng rác?<br>
                           <small style="color:#64748b">Bạn có thể khôi phục sau từ thùng rác.</small>`,
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
                    const form = document.getElementById('delete-bv-form');
                    form.action = `/admin/bai-viet/${id}`;
                    form.submit();
                }
            });
        }

        // ── Search on Enter ────────────────────────────────────────
        document.querySelector('.search-input')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') document.getElementById('bv-filter-form').submit();
        });
    </script>
@endsection