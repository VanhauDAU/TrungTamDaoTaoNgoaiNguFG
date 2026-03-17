@extends('layouts.admin')

@section('title', 'Danh sách nhân viên')
@section('page-title', 'Nhân viên')
@section('breadcrumb', 'Quản lý nhân viên · Danh sách nhân viên')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/nhan-vien/index.css') }}">
@endsection

@section('content')

    {{-- ── Page Header ───────────────────────────────────────────── --}}
    <div class="nv-page-header">
        <div class="nv-page-title">
            <i class="fas fa-user-tie me-2" style="color:#6366f1"></i>Danh sách nhân viên
            <span>{{ $nhanViens->total() }} kết quả</span>
        </div>
        <div style="display:flex;gap:10px;align-items:center">
            @php $soXoa = \App\Models\Auth\TaiKhoan::onlyTrashed()->where('role', \App\Models\Auth\TaiKhoan::ROLE_NHAN_VIEN)->count(); @endphp
            <a href="{{ route('admin.nhan-vien.trash') }}" class="btn-add-employee"
                style="background:#fee2e2;color:#dc2626;border-color:#fca5a5" title="Thùng rác">
                <i class="fas fa-trash-can"></i> Thùng rác
                @if ($soXoa > 0)
                    <span
                        style="background:#dc2626;color:#fff;border-radius:20px;padding:1px 7px;
                                 font-size:.72rem;margin-left:4px">{{ $soXoa }}</span>
                @endif
            </a>
            <a href="{{ route('admin.nhan-vien.create') }}" class="btn-add-employee">
                <i class="fas fa-plus"></i> Thêm nhân viên
            </a>
        </div>
    </div>

    {{-- ── Stats strip ────────────────────────────────────────────── --}}
    <div class="nv-stats">
        <div class="nv-stat-card">
            <div class="nv-stat-icon total"><i class="fas fa-users"></i></div>
            <div>
                <div class="nv-stat-value">{{ number_format($tongSo) }}</div>
                <div class="nv-stat-label">Tổng nhân viên</div>
            </div>
        </div>
        <div class="nv-stat-card">
            <div class="nv-stat-icon active"><i class="fas fa-user-check"></i></div>
            <div>
                <div class="nv-stat-value">{{ number_format($dangHoatDong) }}</div>
                <div class="nv-stat-label">Đang hoạt động</div>
            </div>
        </div>
        <div class="nv-stat-card">
            <div class="nv-stat-icon new"><i class="fas fa-user-plus"></i></div>
            <div>
                <div class="nv-stat-value">{{ number_format($thangNay) }}</div>
                <div class="nv-stat-label">Mới trong tháng</div>
            </div>
        </div>
    </div>

    {{-- ── Filter bar ─────────────────────────────────────────────── --}}
    <form action="{{ route('admin.nhan-vien.index') }}" method="GET" class="nv-filter-bar" id="nv-filter-form">
        {{-- Search --}}
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" name="q" class="search-input" placeholder="Tìm theo tên, email, chuyên môn..."
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
        <button type="submit" class="nv-btn-filter nv-btn-filter-primary">
            <i class="fas fa-filter"></i> Lọc
        </button>
        <a href="{{ route('admin.nhan-vien.index') }}" class="nv-btn-filter nv-btn-filter-reset">
            <i class="fas fa-times"></i> Đặt lại
        </a>
    </form>

    {{-- ── Table card ─────────────────────────────────────────────── --}}
    <div class="nv-card">
        <div class="nv-table-header">
            <div class="nv-table-title"><i class="fas fa-list me-2"></i> Danh sách nhân viên</div>
            <div class="nv-table-count">
                Hiển thị {{ $nhanViens->firstItem() ?? 0 }}–{{ $nhanViens->lastItem() ?? 0 }}
                / {{ $nhanViens->total() }} bản ghi
            </div>
        </div>

        @if ($nhanViens->isEmpty())
            <div class="nv-empty">
                <i class="fas fa-user-slash"></i>
                <p>Không tìm thấy nhân viên nào.</p>
                @if (request()->anyFilled(['q', 'trangThai']))
                    <a href="{{ route('admin.nhan-vien.index') }}" class="nv-btn-filter nv-btn-filter-reset">
                        Xóa bộ lọc
                    </a>
                @endif
            </div>
        @else
            <div style="overflow-x:auto">
                <table class="nv-table">
                    <thead>
                        <tr>
                            <th style="width:44px">#</th>
                            <th>
                                <a class="nv-sort-link"
                                    href="{{ request()->fullUrlWithQuery(['orderBy' => 'taiKhoanId', 'dir' => request('orderBy') === 'taiKhoanId' && request('dir') === 'asc' ? 'desc' : 'asc']) }}">
                                    Nhân viên
                                    @if (request('orderBy', 'taiKhoanId') === 'taiKhoanId')
                                        <i class="fas fa-sort-{{ request('dir', 'desc') === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort" style="opacity:.4"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a class="nv-sort-link"
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
                            <th>Chuyên môn</th>
                            <th>Chức vụ</th>
                            <th>
                                <a class="nv-sort-link"
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
                        @foreach ($nhanViens as $nv)
                            @php
                                $profile = $nv->hoSoNguoiDung;
                                $nhanSu = $nv->nhanSu;
                                $hoTen = $profile->hoTen ?? $nv->taiKhoan;
                                $initials = strtoupper(substr($hoTen, 0, 1));
                            @endphp
                            <tr>
                                <td style="color:#8899a6;font-size:0.78rem">
                                    {{ $nhanViens->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    <div class="nv-info">
                                        <div class="nv-avatar">{{ $initials }}</div>
                                        <div>
                                            <div class="nv-name">{{ $hoTen }}</div>
                                            <div class="nv-username">{{ $nv->taiKhoan }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td>{{ $nv->email }}</td>

                                <td>{{ $profile->soDienThoai ?? '—' }}</td>

                                <td>
                                    @if ($nhanSu?->chuyenMon)
                                        <span class="nv-badge-specialty">{{ $nhanSu->chuyenMon }}</span>
                                    @else
                                        <span style="color:#aab8c2">—</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($nhanSu?->chucVu)
                                        <span class="nv-badge-position">{{ $nhanSu->chucVu }}</span>
                                    @else
                                        <span style="color:#aab8c2">—</span>
                                    @endif
                                </td>

                                <td style="color:#8899a6;font-size:0.8rem">
                                    @if ($nv->lastLogin)
                                        {{ \Carbon\Carbon::parse($nv->lastLogin)->diffForHumans() }}
                                    @else
                                        <span style="color:#aab8c2">Chưa đăng nhập</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($nv->trangThai)
                                        <span class="nv-badge-active">
                                            <i class="fas fa-circle" style="font-size:.5em"></i> Hoạt động
                                        </span>
                                    @else
                                        <span class="nv-badge-inactive">
                                            <i class="fas fa-circle" style="font-size:.5em"></i> Bị khóa
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="nv-actions">
                                        <a href="{{ route('admin.nhan-vien.edit', $nv->taiKhoan) }}"
                                            class="nv-btn-action nv-btn-action-edit" title="Chỉnh sửa">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <button type="button" class="nv-btn-action nv-btn-action-del" title="Xóa"
                                            onclick="confirmDelete('{{ $nv->taiKhoan }}', '{{ addslashes($hoTen) }}')">
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
            @if ($nhanViens->hasPages())
                <div class="nv-pagination">
                    <div class="nv-pagination-info">
                        Trang {{ $nhanViens->currentPage() }} / {{ $nhanViens->lastPage() }}
                    </div>
                    {{ $nhanViens->links() }}
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
                title: 'Xóa nhân viên?',
                html: `Bạn có chắc muốn xóa nhân viên <strong>${name}</strong>?<br>
                       <small style="color:#8899a6">Nhân viên sẽ được chuyển vào thùng rác.</small>`,
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
                    form.action = `/admin/nhan-vien/${id}`;
                    form.submit();
                }
            });
        }

        // Enter để submit filter
        document.querySelector('.search-input')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') document.getElementById('nv-filter-form').submit();
        });
    </script>
@endsection
