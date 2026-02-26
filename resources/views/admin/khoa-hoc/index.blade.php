@extends('layouts.admin')

@section('title', 'Quản lý Khóa Học')
@section('page-title', 'Khóa Học')
@section('breadcrumb', 'Quản lý · Danh sách khóa học')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/khoa-hoc/index.css') }}">
@endsection

@section('content')

    {{-- ── Page Header ─────────────────────────────────────────── --}}
    <div class="kh-page-header">
        <div class="kh-page-title">
            <i class="fas fa-graduation-cap" style="color:#0f766e"></i>
            Danh sách khóa học
            <span>{{ $khoaHocs->total() }} kết quả</span>
        </div>
        <div style="display:flex;gap:10px;align-items:center">
            <a href="{{ route('admin.lop-hoc.index') }}" class="btn-add-kh"
                style="background:linear-gradient(135deg,#7c3aed,#a78bfa)">
                <i class="fas fa-chalkboard"></i> Quản lý lớp học
            </a>
            <a href="{{ route('admin.khoa-hoc.create') }}" class="btn-add-kh">
                <i class="fas fa-plus"></i> Thêm khóa học
            </a>
        </div>
    </div>

    {{-- ── Flash messages ───────────────────────────────────────── --}}
    @if (session('success'))
        <div class="kf-alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="kf-alert-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    {{-- ── Stats strip ──────────────────────────────────────────── --}}
    <div class="kh-stats">
        <div class="kh-stat-card">
            <div class="kh-stat-icon total"><i class="fas fa-book-open"></i></div>
            <div>
                <div class="kh-stat-value">{{ number_format($tongSo) }}</div>
                <div class="kh-stat-label">Tổng khóa học</div>
            </div>
        </div>
        <div class="kh-stat-card">
            <div class="kh-stat-icon active"><i class="fas fa-circle-check"></i></div>
            <div>
                <div class="kh-stat-value">{{ number_format($dangHoatDong) }}</div>
                <div class="kh-stat-label">Đang hoạt động</div>
            </div>
        </div>
        <div class="kh-stat-card">
            <div class="kh-stat-icon classes"><i class="fas fa-chalkboard"></i></div>
            <div>
                <div class="kh-stat-value">{{ number_format($tongLopHoc) }}</div>
                <div class="kh-stat-label">Tổng lớp học</div>
            </div>
        </div>
    </div>

    {{-- ── Filter bar ────────────────────────────────────────────── --}}
    <form action="{{ route('admin.khoa-hoc.index') }}" method="GET" class="kh-filter-bar" id="kh-filter-form">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" name="q" class="search-input" placeholder="Tìm theo tên khóa học, mô tả..."
                value="{{ request('q') }}" autocomplete="off">
        </div>

        <select name="loaiKhoaHocId" onchange="this.form.submit()">
            <option value="">Tất cả loại</option>
            @foreach ($loaiKhoaHocs as $loai)
                <option value="{{ $loai->loaiKhoaHocId }}"
                    {{ request('loaiKhoaHocId') == $loai->loaiKhoaHocId ? 'selected' : '' }}>
                    {{ $loai->tenLoai ?? 'Loại ' . $loai->loaiKhoaHocId }}
                </option>
            @endforeach
        </select>

        <select name="trangThai" onchange="this.form.submit()">
            <option value="">Tất cả trạng thái</option>
            <option value="1" {{ request('trangThai') === '1' ? 'selected' : '' }}>Đang hoạt động</option>
            <option value="0" {{ request('trangThai') === '0' ? 'selected' : '' }}>Ngừng hoạt động</option>
        </select>

        <select name="orderBy" onchange="this.form.submit()">
            <option value="khoaHocId" {{ request('orderBy', 'khoaHocId') === 'khoaHocId' ? 'selected' : '' }}>Mới nhất
            </option>
            <option value="tenKhoaHoc" {{ request('orderBy') === 'tenKhoaHoc' ? 'selected' : '' }}>Tên A-Z</option>
        </select>

        <button type="submit" class="kh-btn-filter kh-btn-filter-primary">
            <i class="fas fa-filter"></i> Lọc
        </button>
        <a href="{{ route('admin.khoa-hoc.index') }}" class="kh-btn-filter kh-btn-filter-reset">
            <i class="fas fa-times"></i> Đặt lại
        </a>
    </form>

    {{-- ── Grid list ─────────────────────────────────────────────── --}}
    @if ($khoaHocs->isEmpty())
        <div class="kh-empty">
            <i class="fas fa-book-open"></i>
            <p>Không tìm thấy khóa học nào.</p>
            @if (request()->anyFilled(['q', 'loaiKhoaHocId', 'trangThai']))
                <a href="{{ route('admin.khoa-hoc.index') }}" class="kh-btn-filter kh-btn-filter-reset"
                    style="margin-top:12px;display:inline-flex">
                    Xóa bộ lọc
                </a>
            @endif
        </div>
    @else
        <div class="kh-grid">
            @foreach ($khoaHocs as $kh)
                @php
                    $soLop = $kh->lopHoc->count();
                @endphp
                <div class="kh-card">
                    {{-- Ảnh --}}
                    <div class="kh-card-img">
                        @if ($kh->anhKhoaHoc)
                            <img src="{{ asset('storage/' . $kh->anhKhoaHoc) }}" alt="{{ $kh->tenKhoaHoc }}">
                        @else
                            <i class="fas fa-book-open"></i>
                        @endif
                    </div>

                    <div class="kh-card-body">
                        @if ($kh->loaiKhoaHoc)
                            <span class="kh-card-loai">{{ $kh->loaiKhoaHoc->tenLoai ?? 'Chương trình học' }}</span>
                        @endif
                        <div class="kh-card-title" title="{{ $kh->tenKhoaHoc }}">{{ $kh->tenKhoaHoc }}</div>

                        <div class="kh-card-meta">
                            <span><i class="fas fa-chalkboard"></i> {{ $soLop }} lớp</span>
                            @if ($kh->doiTuong)
                                <span title="{{ $kh->doiTuong }}">
                                    <i class="fas fa-user-graduate"></i>
                                    {{ Str::limit($kh->doiTuong, 30) }}
                                </span>
                            @endif
                        </div>

                        @if ($kh->moTa)
                            <p
                                style="font-size:.8rem;color:#64748b;margin:0;
                               display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                                {{ strip_tags($kh->moTa) }}
                            </p>
                        @endif
                    </div>

                    <div class="kh-card-footer">
                        @if ($kh->trangThai)
                            <span class="kh-badge-active"><i class="fas fa-circle" style="font-size:.4em"></i> Hoạt
                                động</span>
                        @else
                            <span class="kh-badge-inactive"><i class="fas fa-circle" style="font-size:.4em"></i>
                                Ngừng</span>
                        @endif

                        <div class="kh-actions">
                            <a href="{{ route('admin.khoa-hoc.show', $kh->khoaHocId) }}" class="kh-btn-action kh-btn-view"
                                title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.khoa-hoc.edit', $kh->khoaHocId) }}" class="kh-btn-action kh-btn-edit"
                                title="Chỉnh sửa">
                                <i class="fas fa-pen"></i>
                            </a>
                            <button type="button" class="kh-btn-action kh-btn-del" title="Xóa"
                                onclick="confirmDeleteKH({{ $kh->khoaHocId }}, '{{ addslashes($kh->tenKhoaHoc) }}', {{ $soLop }})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if ($khoaHocs->hasPages())
            <div class="kh-pagination">
                <div class="kh-pagination-info">
                    Trang {{ $khoaHocs->currentPage() }} / {{ $khoaHocs->lastPage() }}
                    · {{ $khoaHocs->total() }} khóa học
                </div>
                {{ $khoaHocs->links() }}
            </div>
        @endif
    @endif

@endsection

{{-- Hidden delete form --}}
<form id="delete-kh-form" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>

@section('script')
    <script>
        function confirmDeleteKH(id, name, soLop) {
            if (soLop > 0) {
                // Có lớp học — kiểm tra thêm ở server, ở đây chỉ cảnh báo
                Swal.fire({
                    title: 'Xác nhận lưu trữ?',
                    html: `Khóa học <strong>${name}</strong> có <strong>${soLop} lớp học</strong>.<br>
                           <small style="color:#64748b">Chỉ các lớp đã đóng/hủy mới cho phép lưu trữ. Server sẽ kiểm tra lại.</small>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-archive me-1"></i> Vẫn lưu trữ',
                    cancelButtonText: 'Hủy',
                    confirmButtonColor: '#d97706',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true,
                    focusCancel: true,
                }).then(result => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('delete-kh-form');
                        form.action = `/admin/khoa-hoc/${id}`;
                        form.submit();
                    }
                });
                return;
            }
            Swal.fire({
                title: 'Lưu trữ khóa học?',
                html: `Lưu trữ <strong>${name}</strong>?<br><small style="color:#64748b">Dữ liệu không bị xóa, có thể khôi phục sau.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-archive me-1"></i> Lưu trữ',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#d97706',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusCancel: true,
            }).then(result => {
                if (result.isConfirmed) {
                    const form = document.getElementById('delete-kh-form');
                    form.action = `/admin/khoa-hoc/${id}`;
                    form.submit();
                }
            });
        }

        document.querySelector('.search-input')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') document.getElementById('kh-filter-form').submit();
        });
    </script>
@endsection
