@extends('layouts.admin')

@section('title', 'Quản lý Danh Mục Khóa Học')
@section('page-title', 'Danh Mục Khóa Học')
@section('breadcrumb', 'Quản lý · Danh sách danh mục khóa học')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/danh-muc-khoa-hoc/index.css') }}">
@endsection

@section('content')

    {{-- ── Page Header ──────────────────────────────────────── --}}
    <div class="dm-page-header">
        <div class="dm-page-title">
            <i class="fas fa-tags" style="color:#0f766e"></i>
            Danh sách danh mục khóa học
            <span>{{ $tongSo }} danh mục</span>
        </div>
        <a href="{{ route('admin.danh-muc-khoa-hoc.create') }}" class="btn-add-dm">
            <i class="fas fa-plus"></i> Thêm danh mục
        </a>
    </div>

    {{-- ── Stats strip ──────────────────────────────────────── --}}
    <div class="dm-stats">
        <div class="dm-stat-card">
            <div class="dm-stat-icon total"><i class="fas fa-tags"></i></div>
            <div>
                <div class="dm-stat-value">{{ number_format($tongSo) }}</div>
                <div class="dm-stat-label">Tổng danh mục</div>
            </div>
        </div>
        <div class="dm-stat-card">
            <div class="dm-stat-icon active"><i class="fas fa-circle-check"></i></div>
            <div>
                <div class="dm-stat-value">{{ number_format($dangHoatDong) }}</div>
                <div class="dm-stat-label">Đang hoạt động</div>
            </div>
        </div>
        <div class="dm-stat-card">
            <div class="dm-stat-icon course"><i class="fas fa-graduation-cap"></i></div>
            <div>
                <div class="dm-stat-value">{{ number_format($tongKhoaHoc) }}</div>
                <div class="dm-stat-label">Tổng khóa học</div>
            </div>
        </div>
    </div>

    {{-- ── Filter bar ───────────────────────────────────────── --}}
    <form action="{{ route('admin.danh-muc-khoa-hoc.index') }}" method="GET" class="dm-filter-bar" id="dm-filter-form">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" name="q" class="search-input" placeholder="Tìm theo tên, mô tả..."
                value="{{ request('q') }}" autocomplete="off">
        </div>

        <select name="trangThai" onchange="this.form.submit()">
            <option value="">Tất cả trạng thái</option>
            <option value="1" {{ request('trangThai') === '1' ? 'selected' : '' }}>Đang hoạt động</option>
            <option value="0" {{ request('trangThai') === '0' ? 'selected' : '' }}>Ngừng hoạt động</option>
        </select>

        <button type="submit" class="dm-btn-filter dm-btn-filter-primary">
            <i class="fas fa-filter"></i> Lọc
        </button>
        <a href="{{ route('admin.danh-muc-khoa-hoc.index') }}" class="dm-btn-filter dm-btn-filter-reset">
            <i class="fas fa-times"></i> Đặt lại
        </a>
    </form>

    {{-- ── Table ────────────────────────────────────────────── --}}
    @if ($danhMucs->isEmpty())
        <div class="dm-empty">
            <i class="fas fa-tags"></i>
            <p>Chưa có danh mục nào. Hãy thêm danh mục đầu tiên!</p>
            <a href="{{ route('admin.danh-muc-khoa-hoc.create') }}" class="btn-add-dm"
                style="margin-top:16px;display:inline-flex">
                <i class="fas fa-plus"></i> Thêm danh mục
            </a>
        </div>
    @else
        <div class="dm-table-wrap">
            <table class="dm-table">
                <thead>
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Tên danh mục</th>
                        <th>Đường dẫn (Slug)</th>
                        <th>Mô tả</th>
                        <th style="text-align:center">Số khóa học</th>
                        <th style="text-align:center">Trạng thái</th>
                        <th style="text-align:center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($danhMucs as $i => $dm)
                        <tr>
                            <td style="color:#94a3b8;font-size:.8rem">
                                {{ ($danhMucs->currentPage() - 1) * $danhMucs->perPage() + $i + 1 }}
                            </td>
                            <td>
                                <div style="font-weight:600;color:#1e293b">{{ $dm->tenDanhMuc }}</div>
                            </td>
                            <td style="color:#0f766e;font-family:monospace;font-size:0.85rem">
                                {{ $dm->slug ?? '—' }}
                            </td>
                            <td style="color:#64748b;max-width:300px">
                                {{ $dm->moTa ? Str::limit($dm->moTa, 80) : '—' }}
                            </td>
                            <td style="text-align:center">
                                <span class="dm-count-badge">
                                    {{ $dm->khoa_hocs_count ?? 0 }} khóa
                                </span>
                            </td>
                            <td style="text-align:center">
                                @if ($dm->trangThai)
                                    <span class="dm-badge-active"><i class="fas fa-circle" style="font-size:.4em"></i> Hoạt
                                        động</span>
                                @else
                                    <span class="dm-badge-inactive"><i class="fas fa-circle" style="font-size:.4em"></i>
                                        Ngừng</span>
                                @endif
                            </td>
                            <td style="text-align:center">
                                <div class="dm-actions" style="justify-content:center">
                                    <a href="{{ route('admin.danh-muc-khoa-hoc.edit', $dm->danhMucId) }}"
                                        class="dm-btn-action dm-btn-edit" title="Chỉnh sửa">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <button type="button" class="dm-btn-action dm-btn-del" title="Xóa"
                                        onclick="confirmDeleteDM({{ $dm->danhMucId }}, '{{ addslashes($dm->tenDanhMuc) }}', {{ $dm->khoa_hocs_count ?? 0 }})">
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
        @if ($danhMucs->hasPages())
            <div class="dm-pagination">
                <div class="dm-pagination-info">
                    Trang {{ $danhMucs->currentPage() }} / {{ $danhMucs->lastPage() }}
                    · {{ $danhMucs->total() }} danh mục
                </div>
                {{ $danhMucs->links() }}
            </div>
        @endif
    @endif

@endsection

{{-- Hidden delete form --}}
<form id="delete-dm-form" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>

@section('script')
    <script>
        function confirmDeleteDM(id, name, soKH) {
            if (soKH > 0) {
                Swal.fire({
                    title: 'Không thể xóa!',
                    html: `Danh mục <strong>${name}</strong> đang có <strong>${soKH} khóa học</strong>.<br>
                           <small style="color:#64748b">Hãy chuyển các khóa học sang danh mục khác trước khi xóa.</small>`,
                    icon: 'warning',
                    confirmButtonText: '<i class="fas fa-times me-1"></i> Đóng',
                    confirmButtonColor: '#6c757d',
                });
                return;
            }
            Swal.fire({
                title: 'Xóa danh mục?',
                html: `Xóa <strong>${name}</strong>?<br><small style="color:#64748b">Hành động này không thể hoàn tác.</small>`,
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
                    const form = document.getElementById('delete-dm-form');
                    form.action = `/admin/danh-muc-khoa-hoc/${id}`;
                    form.submit();
                }
            });
        }

        document.querySelector('.search-input')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') document.getElementById('dm-filter-form').submit();
        });
    </script>
@endsection
