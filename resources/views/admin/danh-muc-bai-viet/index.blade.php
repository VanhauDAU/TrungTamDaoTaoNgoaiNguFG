@extends('layouts.admin')

@section('title', 'Quản lý Danh Mục Bài Viết')
@section('page-title', 'Danh Mục Bài Viết')
@section('breadcrumb', 'Nội dung · Danh mục bài viết')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/danh-muc-bai-viet/index.css') }}">
@endsection

@section('content')

    {{-- ── Page Header ─────────────────────────────────────────── --}}
    <div class="dm-page-header">
        <div class="dm-page-title">
            <i class="fas fa-folder-open" style="color:#1d4ed8"></i>
            Danh mục bài viết
            <span>{{ $danhMucs->total() }} kết quả</span>
        </div>
        <div style="display:flex;gap:10px;align-items:center">
            <a href="{{ route('admin.bai-viet.index') }}" class="btn-add-dm"
                style="background:linear-gradient(135deg,#7c3aed,#a78bfa)">
                <i class="fas fa-newspaper"></i> Bài viết
            </a>
            <a href="{{ route('admin.danh-muc-bai-viet.create') }}" class="btn-add-dm">
                <i class="fas fa-plus"></i> Thêm danh mục
            </a>
        </div>
    </div>

    {{-- ── Flash messages ───────────────────────────────────────── --}}
    @if (session('success'))
        <div class="dm-alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="dm-alert-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    {{-- ── Stats ──────────────────────────────────────────── --}}
    <div class="dm-stats">
        <div class="dm-stat-card">
            <div class="dm-stat-icon total"><i class="fas fa-folder-open"></i></div>
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
            <div class="dm-stat-icon posts"><i class="fas fa-newspaper"></i></div>
            <div>
                <div class="dm-stat-value">{{ number_format($tongBaiViet) }}</div>
                <div class="dm-stat-label">Tổng bài viết</div>
            </div>
        </div>
    </div>

    {{-- ── Filter bar ──────────────────────────────────────────── --}}
    <form action="{{ route('admin.danh-muc-bai-viet.index') }}" method="GET" class="dm-filter-bar" id="dm-filter-form">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" name="q" class="search-input" placeholder="Tìm theo tên danh mục..."
                value="{{ request('q') }}" autocomplete="off">
        </div>

        <select name="trangThai" onchange="this.form.submit()">
            <option value="">Tất cả trạng thái</option>
            <option value="1" {{ request('trangThai') === '1' ? 'selected' : '' }}>Hoạt động</option>
            <option value="0" {{ request('trangThai') === '0' ? 'selected' : '' }}>Ngừng</option>
        </select>

        <select name="orderBy" onchange="this.form.submit()">
            <option value="danhMucId" {{ request('orderBy', 'danhMucId') === 'danhMucId' ? 'selected' : '' }}>Mới nhất
            </option>
            <option value="tenDanhMuc" {{ request('orderBy') === 'tenDanhMuc' ? 'selected' : '' }}>Tên A-Z</option>
            <option value="bai_viets_count" {{ request('orderBy') === 'bai_viets_count' ? 'selected' : '' }}>Số bài viết
            </option>
        </select>

        <button type="submit" class="dm-btn-filter dm-btn-filter-primary">
            <i class="fas fa-filter"></i> Lọc
        </button>
        <a href="{{ route('admin.danh-muc-bai-viet.index') }}" class="dm-btn-filter dm-btn-filter-reset">
            <i class="fas fa-times"></i> Đặt lại
        </a>
    </form>

    {{-- ── Table ─────────────────────────────────────────────── --}}
    @if ($danhMucs->isEmpty())
        <div class="dm-empty">
            <i class="fas fa-folder-open"></i>
            <p>Không tìm thấy danh mục nào.</p>
        </div>
    @else
        <div class="dm-table-wrap">
            <table class="dm-table">
                <thead>
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Tên danh mục</th>
                        <th>Slug</th>
                        <th>Mô tả</th>
                        <th style="width:90px">Số bài viết</th>
                        <th style="width:100px">Trạng thái</th>
                        <th style="width:90px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($danhMucs as $dm)
                        <tr>
                            <td style="color:#94a3b8;font-size:.82rem">{{ $dm->danhMucId }}</td>
                            <td style="font-weight:600;color:#1e3a5f">{{ $dm->tenDanhMuc }}</td>
                            <td style="font-size:.82rem;color:#64748b">{{ $dm->slug }}</td>
                            <td
                                style="font-size:.82rem;color:#64748b;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                {{ $dm->moTa ?? '—' }}
                            </td>
                            <td style="text-align:center">
                                <span
                                    style="background:#eff6ff;color:#1d4ed8;padding:2px 10px;border-radius:12px;font-size:.78rem;font-weight:600">
                                    {{ $dm->bai_viets_count }}
                                </span>
                            </td>
                            <td>
                                @if ($dm->trangThai)
                                    <span class="dm-badge-active"><i class="fas fa-circle" style="font-size:.4em"></i> Hoạt động</span>
                                @else
                                    <span class="dm-badge-inactive"><i class="fas fa-circle" style="font-size:.4em"></i> Ngừng</span>
                                @endif
                            </td>
                            <td>
                                <div class="dm-actions">
                                    <a href="{{ route('admin.danh-muc-bai-viet.edit', $dm->danhMucId) }}"
                                        class="dm-btn-action dm-btn-edit" title="Sửa">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <button type="button" class="dm-btn-action dm-btn-del" title="Xóa"
                                        onclick="confirmDeleteDM({{ $dm->danhMucId }}, '{{ addslashes($dm->tenDanhMuc) }}', {{ $dm->bai_viets_count }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

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

<form id="delete-dm-form" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>

@section('script')
    <script>
        function confirmDeleteDM(id, name, soBaiViet) {
            if (soBaiViet > 0) {
                Swal.fire({
                    title: 'Không thể xóa!',
                    html: `Danh mục <strong>${name}</strong> còn <strong>${soBaiViet} bài viết</strong>.<br>
                                   <small style="color:#64748b">Hãy chuyển bài viết sang danh mục khác trước khi xóa.</small>`,
                    icon: 'error',
                    confirmButtonText: 'Đã hiểu',
                    confirmButtonColor: '#1d4ed8',
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
                    form.action = `/admin/danh-muc-bai-viet/${id}`;
                    form.submit();
                }
            });
        }

        document.querySelector('.search-input')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') document.getElementById('dm-filter-form').submit();
        });
    </script>
@endsection