@extends('layouts.admin')

@section('title', 'Danh sách giáo viên')
@section('page-title', 'Giáo viên')
@section('breadcrumb', 'Quản lý giáo viên · Danh sách giáo viên')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/giao-vien/index.css') }}">
@endsection

@section('content')

    {{-- ── Page Header ───────────────────────────────────────────── --}}
    <div class="gv-page-header">
        <div class="gv-page-title">
            <i class="fas fa-chalkboard-teacher me-2" style="color:#4f46e5"></i>Danh sách giáo viên
            <span>{{ $giaoViens->total() }} kết quả</span>
        </div>
        <div style="display:flex;gap:10px;align-items:center">
            @php $soXoa = \App\Models\Auth\TaiKhoan::onlyTrashed()->where('role', \App\Models\Auth\TaiKhoan::ROLE_GIAO_VIEN)->count(); @endphp
            <a href="{{ route('admin.giao-vien.trash') }}" class="btn-add-teacher"
                style="background:#fee2e2;color:#dc2626;border-color:#fca5a5" title="Thùng rác">
                <i class="fas fa-trash-can"></i> Thùng rác
                @if ($soXoa > 0)
                    <span
                        style="background:#dc2626;color:#fff;border-radius:20px;padding:1px 7px;
                                 font-size:.72rem;margin-left:4px">{{ $soXoa }}</span>
                @endif
            </a>
            <a href="{{ route('admin.giao-vien.create') }}" class="btn-add-teacher">
                <i class="fas fa-plus"></i> Thêm giáo viên
            </a>
        </div>
    </div>

    {{-- ── Stats strip ────────────────────────────────────────────── --}}
    <div class="gv-stats">
        <div class="gv-stat-card">
            <div class="gv-stat-icon total"><i class="fas fa-users"></i></div>
            <div>
                <div class="gv-stat-value">{{ number_format($tongSo) }}</div>
                <div class="gv-stat-label">Tổng giáo viên</div>
            </div>
        </div>
        <div class="gv-stat-card">
            <div class="gv-stat-icon active"><i class="fas fa-user-check"></i></div>
            <div>
                <div class="gv-stat-value">{{ number_format($dangHoatDong) }}</div>
                <div class="gv-stat-label">Đang hoạt động</div>
            </div>
        </div>
        <div class="gv-stat-card">
            <div class="gv-stat-icon new"><i class="fas fa-user-plus"></i></div>
            <div>
                <div class="gv-stat-value">{{ number_format($thangNay) }}</div>
                <div class="gv-stat-label">Mới trong tháng</div>
            </div>
        </div>
    </div>

    {{-- ── Filter bar ─────────────────────────────────────────────── --}}
    <form action="{{ route('admin.giao-vien.index') }}" method="GET" class="gv-filter-bar" id="gv-filter-form">
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
        <button type="submit" class="gv-btn-filter gv-btn-filter-primary">
            <i class="fas fa-filter"></i> Lọc
        </button>
        <a href="{{ route('admin.giao-vien.index') }}" class="gv-btn-filter gv-btn-filter-reset">
            <i class="fas fa-times"></i> Đặt lại
        </a>
    </form>

    {{-- ── Table card ─────────────────────────────────────────────── --}}
    <div class="gv-card">
        <div class="gv-table-header">
            <div class="gv-table-title"><i class="fas fa-list me-2"></i> Danh sách giáo viên</div>
            <div class="gv-table-count">
                Hiển thị {{ $giaoViens->firstItem() ?? 0 }}–{{ $giaoViens->lastItem() ?? 0 }}
                / {{ $giaoViens->total() }} bản ghi
            </div>
        </div>

        @if ($giaoViens->isEmpty())
            <div class="gv-empty">
                <i class="fas fa-user-slash"></i>
                <p>Không tìm thấy giáo viên nào.</p>
                @if (request()->anyFilled(['q', 'trangThai']))
                    <a href="{{ route('admin.giao-vien.index') }}" class="gv-btn-filter gv-btn-filter-reset">
                        Xóa bộ lọc
                    </a>
                @endif
            </div>
        @else
            <div style="overflow-x:auto">
                <table class="gv-table">
                    <thead>
                        <tr>
                            <th style="width:44px">#</th>
                            <th>
                                <a class="gv-sort-link"
                                    href="{{ request()->fullUrlWithQuery(['orderBy' => 'taiKhoanId', 'dir' => request('orderBy') === 'taiKhoanId' && request('dir') === 'asc' ? 'desc' : 'asc']) }}">
                                    Giáo viên
                                    @if (request('orderBy', 'taiKhoanId') === 'taiKhoanId')
                                        <i class="fas fa-sort-{{ request('dir', 'desc') === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort" style="opacity:.4"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a class="gv-sort-link"
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
                                <a class="gv-sort-link"
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
                        @foreach ($giaoViens as $gv)
                            @php
                                $profile = $gv->hoSoNguoiDung;
                                $nhanSu = $gv->nhanSu;
                                $hoTen = $profile->hoTen ?? $gv->taiKhoan;
                                $initials = strtoupper(substr($hoTen, 0, 1));
                            @endphp
                            <tr>
                                <td style="color:#8899a6;font-size:0.78rem">
                                    {{ $giaoViens->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    <div class="gv-info">
                                        <div class="gv-avatar">{{ $initials }}</div>
                                        <div>
                                            <div class="gv-name">{{ $hoTen }}</div>
                                            <div class="gv-username">{{ $gv->taiKhoan }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td>{{ $gv->email }}</td>

                                <td>{{ $profile->soDienThoai ?? '—' }}</td>

                                <td>
                                    @if ($nhanSu?->chuyenMon)
                                        <span class="gv-badge-specialty">{{ $nhanSu->chuyenMon }}</span>
                                    @else
                                        <span style="color:#aab8c2">—</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($nhanSu?->chucVu)
                                        <span class="gv-badge-position">{{ $nhanSu->chucVu }}</span>
                                    @else
                                        <span style="color:#aab8c2">—</span>
                                    @endif
                                </td>

                                <td style="color:#8899a6;font-size:0.8rem">
                                    @if ($gv->lastLogin)
                                        {{ \Carbon\Carbon::parse($gv->lastLogin)->diffForHumans() }}
                                    @else
                                        <span style="color:#aab8c2">Chưa đăng nhập</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($gv->trangThai)
                                        <span class="gv-badge-active">
                                            <i class="fas fa-circle" style="font-size:.5em"></i> Hoạt động
                                        </span>
                                    @else
                                        <span class="gv-badge-inactive">
                                            <i class="fas fa-circle" style="font-size:.5em"></i> Bị khóa
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="gv-actions">
                                        <a href="{{ route('admin.giao-vien.edit', $gv->taiKhoan) }}"
                                            class="gv-btn-action gv-btn-action-edit" title="Chỉnh sửa">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <button type="button" class="gv-btn-action gv-btn-action-del" title="Xóa"
                                            onclick="confirmDelete({{ $gv->taiKhoanId }}, '{{ addslashes($hoTen) }}')">
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
            @if ($giaoViens->hasPages())
                <div class="gv-pagination">
                    <div class="gv-pagination-info">
                        Trang {{ $giaoViens->currentPage() }} / {{ $giaoViens->lastPage() }}
                    </div>
                    {{ $giaoViens->links() }}
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
                title: 'Xóa giáo viên?',
                html: `Bạn có chắc muốn xóa giáo viên <strong>${name}</strong>?<br>
                       <small style="color:#8899a6">Giáo viên sẽ được chuyển vào thùng rác.</small>`,
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
                    form.action = `/admin/giao-vien/${id}`;
                    form.submit();
                }
            });
        }

        // Enter để submit filter
        document.querySelector('.search-input')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') document.getElementById('gv-filter-form').submit();
        });
    </script>
@endsection
