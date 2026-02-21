@extends('layouts.admin')

@section('title', 'Danh sách học viên')
@section('page-title', 'Học viên')
@section('breadcrumb', 'Quản lý học viên · Danh sách học viên')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/hoc-vien/index.css') }}">
@endsection

@section('content')

    {{-- ── Page Header ───────────────────────────────────────────── --}}
    <div class="hv-page-header">
        <div class="hv-page-title">
            <i class="fas fa-user-graduate me-2" style="color:#27c4b5"></i>Danh sách học viên
            <span>{{ $hocViens->total() }} kết quả</span>
        </div>
        <a href="{{ route('admin.hoc-vien.create') }}" class="btn-add-student">
            <i class="fas fa-plus"></i> Thêm học viên
        </a>
    </div>

    {{-- ── Stats strip ────────────────────────────────────────────── --}}
    <div class="hv-stats">
        <div class="hv-stat-card">
            <div class="hv-stat-icon total"><i class="fas fa-users"></i></div>
            <div>
                <div class="hv-stat-value">{{ number_format($tongSo) }}</div>
                <div class="hv-stat-label">Tổng học viên</div>
            </div>
        </div>
        <div class="hv-stat-card">
            <div class="hv-stat-icon active"><i class="fas fa-user-check"></i></div>
            <div>
                <div class="hv-stat-value">{{ number_format($dangHoatDong) }}</div>
                <div class="hv-stat-label">Đang hoạt động</div>
            </div>
        </div>
        <div class="hv-stat-card">
            <div class="hv-stat-icon new"><i class="fas fa-user-plus"></i></div>
            <div>
                <div class="hv-stat-value">{{ number_format($thangNay) }}</div>
                <div class="hv-stat-label">Mới trong tháng</div>
            </div>
        </div>
    </div>

    {{-- ── Filter bar ─────────────────────────────────────────────── --}}
    <form action="{{ route('admin.hoc-vien.index') }}" method="GET" class="hv-filter-bar" id="filter-form">
        {{-- Search --}}
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" name="q" class="search-input" placeholder="Tìm theo tên, email, số điện thoại..."
                value="{{ request('q') }}" autocomplete="off">
        </div>

        {{-- Trạng thái --}}
        <select name="trangThai" onchange="this.form.submit()">
            <option value="">Tất cả trạng thái</option>
            <option value="1" {{ request('trangThai') === '1' ? 'selected' : '' }}>Đang hoạt động</option>
            <option value="0" {{ request('trangThai') === '0' ? 'selected' : '' }}>Bị khóa</option>
        </select>

        {{-- Sắp xếp --}}
        <select name="orderBy" onchange="this.form.submit()">
            <option value="taiKhoanId" {{ request('orderBy') === 'taiKhoanId' ? 'selected' : '' }}>Mới nhất</option>
            <option value="email" {{ request('orderBy') === 'email' ? 'selected' : '' }}>Email A-Z</option>
            <option value="lastLogin" {{ request('orderBy') === 'lastLogin' ? 'selected' : '' }}>Đăng nhập gần nhất
            </option>
        </select>
        <input type="hidden" name="dir" value="{{ request('dir', 'desc') }}">

        {{-- Buttons --}}
        <button type="submit" class="btn-filter btn-filter-primary">
            <i class="fas fa-filter"></i> Lọc
        </button>
        <a href="{{ route('admin.hoc-vien.index') }}" class="btn-filter btn-filter-reset">
            <i class="fas fa-times"></i> Đặt lại
        </a>
    </form>

    {{-- ── Table card ─────────────────────────────────────────────── --}}
    <div class="hv-card">
        <div class="hv-table-header">
            <div class="hv-table-title"><i class="fas fa-list me-2"></i>Danh sách học viên</div>
            <div class="hv-table-count">
                Hiển thị {{ $hocViens->firstItem() ?? 0 }}–{{ $hocViens->lastItem() ?? 0 }}
                / {{ $hocViens->total() }} bản ghi
            </div>
        </div>

        @if ($hocViens->isEmpty())
            <div class="hv-empty">
                <i class="fas fa-user-slash"></i>
                <p>Không tìm thấy học viên nào.</p>
                @if (request()->anyFilled(['q', 'trangThai']))
                    <a href="{{ route('admin.hoc-vien.index') }}" class="btn-filter btn-filter-reset">
                        Xóa bộ lọc
                    </a>
                @endif
            </div>
        @else
            <div style="overflow-x:auto">
                <table class="hv-table">
                    <thead>
                        <tr>
                            <th style="width:44px">#</th>
                            <th>
                                <a class="sort-link"
                                    href="{{ request()->fullUrlWithQuery(['orderBy' => 'taiKhoanId', 'dir' => request('orderBy') === 'taiKhoanId' && request('dir') === 'asc' ? 'desc' : 'asc']) }}">
                                    Học viên
                                    @if (request('orderBy', 'taiKhoanId') === 'taiKhoanId')
                                        <i class="fas fa-sort-{{ request('dir', 'desc') === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort" style="opacity:.4"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a class="sort-link"
                                    href="{{ request()->fullUrlWithQuery(['orderBy' => 'email', 'dir' => request('orderBy') === 'email' && request('dir') === 'asc' ? 'desc' : 'asc']) }}">
                                    Email
                                    @if (request('orderBy') === 'email')
                                        <i class="fas fa-sort-{{ request('dir', 'asc') === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort" style="opacity:.4"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Điện thoại</th>
                            <th>Ngày sinh</th>
                            <th>Số lớp</th>
                            <th>
                                <a class="sort-link"
                                    href="{{ request()->fullUrlWithQuery(['orderBy' => 'lastLogin', 'dir' => request('orderBy') === 'lastLogin' && request('dir') === 'asc' ? 'desc' : 'asc']) }}">
                                    Đăng nhập gần nhất
                                    @if (request('orderBy') === 'lastLogin')
                                        <i class="fas fa-sort-{{ request('dir', 'asc') === 'asc' ? 'up' : 'down' }}"></i>
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
                        @foreach ($hocViens as $hv)
                            @php
                                $profile = $hv->hoSoNguoiDung;
                                $hoTen = $profile->hoTen ?? $hv->taiKhoan;
                                $initials = strtoupper(substr($hoTen, 0, 1));
                            @endphp
                            <tr>
                                <td style="color:#8899a6;font-size:0.78rem">
                                    {{ $hocViens->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    <div class="hv-info">
                                        <div class="hv-avatar">{{ $initials }}</div>
                                        <div>
                                            <div class="hv-name">{{ $hoTen }}</div>
                                            <div class="hv-username">{{ $hv->taiKhoan }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td>{{ $hv->email }}</td>

                                <td>{{ $profile->soDienThoai ?? '—' }}</td>

                                <td>
                                    @if ($profile?->ngaySinh)
                                        {{ \Carbon\Carbon::parse($profile->ngaySinh)->format('d/m/Y') }}
                                    @else
                                        <span style="color:#aab8c2">—</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge-classes">
                                        {{ $hv->dangKyLopHocs->count() }}
                                    </span>
                                </td>

                                <td style="color:#8899a6;font-size:0.8rem">
                                    @if ($hv->lastLogin)
                                        {{ \Carbon\Carbon::parse($hv->lastLogin)->diffForHumans() }}
                                    @else
                                        <span style="color:#aab8c2">Chưa đăng nhập</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($hv->trangThai)
                                        <span class="badge-active">
                                            <i class="fas fa-circle" style="font-size:.5em"></i> Hoạt động
                                        </span>
                                    @else
                                        <span class="badge-inactive">
                                            <i class="fas fa-circle" style="font-size:.5em"></i> Bị khóa
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="hv-actions">
                                        <a href="#" class="btn-action btn-action-view" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="#" class="btn-action btn-action-edit" title="Chỉnh sửa">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <button type="button" class="btn-action btn-action-del" title="Xóa"
                                            onclick="confirmDelete({{ $hv->taiKhoanId }}, '{{ addslashes($hoTen) }}')">
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
            @if ($hocViens->hasPages())
                <div class="hv-pagination">
                    <div class="hv-pagination-info">
                        Trang {{ $hocViens->currentPage() }} / {{ $hocViens->lastPage() }}
                    </div>
                    {{ $hocViens->links() }}
                </div>
            @endif
        @endif
    </div>

@endsection

@section('script')
    <script>
        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Xóa học viên?',
                html: `Bạn có chắc muốn xóa học viên <strong>${name}</strong>? Hành động này không thể hoàn tác.`,
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
                    // TODO: submit delete form khi có route
                    Swal.fire('Đã ghi nhận!', 'Chức năng xóa sẽ được triển khai sớm.', 'info');
                }
            });
        }

        // Enter để submit filter
        document.querySelector('.search-input')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') document.getElementById('filter-form').submit();
        });
    </script>
@endsection
