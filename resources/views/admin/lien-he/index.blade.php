@extends('layouts.admin')

@section('title', 'Danh sách liên hệ')
@section('page-title', 'Liên hệ')
@section('breadcrumb', 'Quản lý tương tác · Danh sách liên hệ')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/lien-he/index.css') }}">
@endsection

@section('content')

    {{-- ── Page Header ───────────────────────────────────────────── --}}
    <div class="lh-page-header">
        <div class="lh-page-title">
            <i class="fas fa-envelope-open-text me-2" style="color:#27c4b5"></i>Danh sách liên hệ
            <span>{{ $lienHes->total() }} kết quả</span>
        </div>
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
            <option value="LienHeId" {{ request('orderBy', 'LienHeId') === 'LienHeId' ? 'selected' : '' }}>Mới nhất</option>
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
                                    @if ($lh->trangThai == 1)
                                        <span class="badge-active">
                                            <i class="fas fa-check-circle" style="font-size:.5em"></i> Đã xử lý
                                        </span>
                                    @else
                                        <span class="badge-inactive">
                                            <i class="fas fa-hourglass-half" style="font-size:.5em"></i> Chưa xử lý
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="lh-actions">
                                        {{-- <a href="{{ route('admin.lien-he.edit', $lh->LienHeId) }}"
                                            class="btn-action btn-action-edit" title="Chi tiết / Cập nhật">
                                            <i class="fas fa-eye"></i>
                                        </a> --}}
                                        <button type="button" class="btn-action btn-action-del" title="Xóa"
                                            onclick="confirmDelete({{ $lh->LienHeId }}, '{{ addslashes($lh->hoTen) }}')">
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

@endsection

{{-- Hidden DELETE form --}}
<form id="delete-form" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>

@section('script')
    <script>
        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Xóa liên hệ?',
                html: `Bạn có chắc muốn xóa liên hệ từ <strong>${name}</strong>?`,
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

        // Enter để submit filter
        document.querySelector('.search-input')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') document.getElementById('filter-form').submit();
        });
    </script>
@endsection
