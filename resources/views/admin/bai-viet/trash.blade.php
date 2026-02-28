@extends('layouts.admin')

@section('title', 'Thùng rác - Bài Viết')
@section('page-title', 'Bài Viết / Blog')
@section('breadcrumb', 'Nội dung · Bài viết · Thùng rác')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/bai-viet/index.css') }}">
@endsection

@section('content')

    {{-- ── Page Header ─────────────────────────────────────────── --}}
    <div class="bv-page-header">
        <div class="bv-page-title">
            <i class="fas fa-trash-alt" style="color:#dc2626"></i>
            Thùng rác
            <span>{{ $baiViets->total() }} bài viết</span>
        </div>
        <a href="{{ route('admin.bai-viet.index') }}" class="btn-add-bv"
            style="background:linear-gradient(135deg,#475569,#64748b)">
            <i class="fas fa-arrow-left"></i> Quay lại danh sách
        </a>
    </div>

    {{-- ── Bulk action bar ──────────────────────────────────────── --}}
    <div class="bv-bulk-bar" id="bulk-bar" style="display:none">
        <div class="bv-bulk-bar-inner">
            <span id="bulk-count">0</span> bài viết đã chọn
            <button type="button" class="bv-bulk-btn" onclick="bulkRestore()"
                style="background:linear-gradient(135deg,#059669,#34d399);color:#fff">
                <i class="fas fa-undo"></i> Khôi phục đã chọn
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

    {{-- ── Info banner ──────────────────────────────────────────── --}}
    <div
        style="background:#fffbeb;border:1px solid #fde68a;color:#92400e;padding:12px 16px;border-radius:8px;font-size:.85rem;display:flex;align-items:center;gap:8px;margin-bottom:20px">
        <i class="fas fa-info-circle"></i>
        Các bài viết trong thùng rác có thể được khôi phục hoặc xóa vĩnh viễn.
    </div>

    {{-- ── Table ─────────────────────────────────────────────── --}}
    @if ($baiViets->isEmpty())
        <div class="bv-empty">
            <i class="fas fa-trash-alt"></i>
            <p>Thùng rác trống.</p>
            <a href="{{ route('admin.bai-viet.index') }}" class="bv-btn-filter bv-btn-filter-reset"
                style="margin-top:12px;display:inline-flex">
                Quay lại danh sách
            </a>
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
                        <th style="width:130px">Ngày xóa</th>
                        <th style="width:140px">Thao tác</th>
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
                                    <img src="{{ asset('storage/' . $bv->anhDaiDien) }}" alt="" class="bv-row-thumb" style="opacity:.6">
                                @else
                                    <div class="bv-row-thumb-placeholder"><i class="fas fa-image"></i></div>
                                @endif
                            </td>
                            <td>
                                <div class="bv-row-title" style="opacity:.7">{{ $bv->tieuDe }}</div>
                                <div class="bv-row-author">
                                    <i class="fas fa-user" style="font-size:.65rem"></i>
                                    {{ $bv->taiKhoan->hoSoNguoiDung->hoTen ?? ($bv->taiKhoan->taiKhoan ?? 'N/A') }}
                                </div>
                            </td>
                            <td>
                                @foreach ($bv->danhMucs as $dm)
                                    <span class="bv-cat" style="opacity:.6">{{ $dm->tenDanhMuc }}</span>
                                @endforeach
                            </td>
                            <td style="font-size:.8rem;color:#dc2626">
                                <i class="fas fa-clock" style="font-size:.7rem"></i>
                                {{ $bv->deleted_at ? $bv->deleted_at->format('d/m/Y H:i') : '—' }}
                            </td>
                            <td>
                                <div class="bv-actions">
                                    <form action="{{ route('admin.bai-viet.restore', $bv->baiVietId) }}" method="POST"
                                        style="display:inline">
                                        @csrf
                                        <button type="submit" class="bv-btn-action" title="Khôi phục"
                                            style="background:#f0fdf4;color:#059669">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="bv-btn-action bv-btn-del" title="Xóa vĩnh viễn"
                                        onclick="confirmForceDelete({{ $bv->baiVietId }}, '{{ addslashes($bv->tieuDe) }}')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

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

{{-- Hidden force-delete form --}}
<form id="force-delete-form" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>

@section('script')
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const selectAll = document.getElementById('select-all');
        const bulkBar = document.getElementById('bulk-bar');
        const bulkCount = document.getElementById('bulk-count');

        function getCheckedIds() {
            return [...document.querySelectorAll('.bv-row-check:checked')].map(el => parseInt(el.value));
        }

        function updateBulkBar() {
            const ids = getCheckedIds();
            if (ids.length > 0) { bulkBar.style.display = 'block'; bulkCount.textContent = ids.length; }
            else { bulkBar.style.display = 'none'; }
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
        document.querySelectorAll('.bv-row-check').forEach(cb => cb.addEventListener('change', updateBulkBar));

        function clearSelection() {
            document.querySelectorAll('.bv-row-check').forEach(cb => cb.checked = false);
            if (selectAll) selectAll.checked = false;
            updateBulkBar();
        }

        // Bulk restore
        function bulkRestore() {
            const ids = getCheckedIds();
            if (!ids.length) return;
            Swal.fire({
                title: 'Khôi phục?',
                html: `Khôi phục <strong>${ids.length} bài viết</strong> từ thùng rác?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: `<i class="fas fa-undo me-1"></i> Khôi phục ${ids.length}`,
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#059669',
                reverseButtons: true,
            }).then(result => {
                if (!result.isConfirmed) return;
                fetch('{{ route("admin.bai-viet.bulk-restore") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ids }),
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Toast.fire({ icon: 'success', title: data.message });
                            ids.forEach(id => { const row = document.querySelector(`tr[data-id="${id}"]`); if (row) row.remove(); });
                            clearSelection();
                            setTimeout(() => location.reload(), 1200);
                        }
                    })
                    .catch(() => Toast.fire({ icon: 'error', title: 'Có lỗi xảy ra.' }));
            });
        }

        // Force delete
        function confirmForceDelete(id, name) {
            Swal.fire({
                title: 'Xóa vĩnh viễn?',
                html: `Xóa vĩnh viễn <strong>${name}</strong>?<br>
                           <small style="color:#dc2626;font-weight:600">⚠ Hành động này KHÔNG THỂ hoàn tác.</small>`,
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-times me-1"></i> Xóa vĩnh viễn',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#dc2626',
                reverseButtons: true,
                focusCancel: true,
            }).then(result => {
                if (result.isConfirmed) {
                    const form = document.getElementById('force-delete-form');
                    form.action = `/admin/bai-viet/${id}/xoa-vinh-vien`;
                    form.submit();
                }
            });
        }
    </script>
@endsection