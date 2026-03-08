@extends('layouts.admin')

@section('title', 'Danh sách lớp học')
@section('page-title', 'Lớp Học')
@section('breadcrumb', 'Quản lý · Lớp học')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/lop-hoc/index.css') }}">
@endsection

@section('content')

    {{-- ── Page header ──────────────────────────────────────────── --}}
    <div class="lh-page-header">
        <div class="lh-page-title">
            <i class="fas fa-chalkboard" style="color:#7c3aed"></i>
            Danh sách lớp học
            <span>{{ $lopHocs->total() }} kết quả</span>
        </div>
        <div style="display:flex;gap:10px;align-items:center">
            <a href="{{ route('admin.khoa-hoc.index') }}" class="btn-add-lh"
                style="background:linear-gradient(135deg,#0f766e,#14b8a6)">
                <i class="fas fa-graduation-cap"></i> Khóa học
            </a>
            <a href="{{ route('admin.lop-hoc.create') }}" class="btn-add-lh">
                <i class="fas fa-plus"></i> Thêm lớp học
            </a>
        </div>
    </div>

    @if (session('success'))
        <div
            style="background:#f0fdf4;border:1px solid #bbf7d0;color:#16a34a;padding:12px 16px;border-radius:8px;margin-bottom:14px;display:flex;align-items:center;gap:8px">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div
            style="background:#fff1f2;border:1px solid #fecdd3;color:#dc2626;padding:12px 16px;border-radius:8px;margin-bottom:14px;display:flex;align-items:center;gap:8px">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- ── Stats strip ──────────────────────────────────────────── --}}
    <div class="lh-stats">
        <div class="lh-stat-card">
            <div class="lh-stat-icon total"><i class="fas fa-chalkboard"></i></div>
            <div>
                <div class="lh-stat-value">{{ number_format($tongLop) }}</div>
                <div class="lh-stat-label">Tổng lớp học</div>
            </div>
        </div>
        <div class="lh-stat-card">
            <div class="lh-stat-icon active"><i class="fas fa-play-circle"></i></div>
            <div>
                <div class="lh-stat-value">{{ number_format($dangHoc) }}</div>
                <div class="lh-stat-label">Đang học</div>
            </div>
        </div>
        <div class="lh-stat-card">
            <div class="lh-stat-icon soon"><i class="fas fa-calendar-plus"></i></div>
            <div>
                <div class="lh-stat-value">{{ number_format($sapMo) }}</div>
                <div class="lh-stat-label">Sắp khai giảng</div>
            </div>
        </div>
    </div>

    {{-- ── Filter bar ────────────────────────────────────────────── --}}
    <form action="{{ route('admin.lop-hoc.index') }}" method="GET" class="lh-filter-bar" id="lh-filter-form">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" name="q" class="search-input" placeholder="Tìm tên lớp, khóa học..."
                value="{{ request('q') }}" autocomplete="off">
        </div>

        <select name="khoaHocId" onchange="this.form.submit()">
            <option value="">Tất cả khóa học</option>
            @foreach ($khoaHocs as $kh)
                <option value="{{ $kh->khoaHocId }}" {{ request('khoaHocId') == $kh->khoaHocId ? 'selected' : '' }}>
                    {{ $kh->tenKhoaHoc }}
                </option>
            @endforeach
        </select>

        <select name="coSoId" onchange="this.form.submit()">
            <option value="">Tất cả cơ sở</option>
            @foreach ($coSos as $cs)
                <option value="{{ $cs->coSoId }}" {{ request('coSoId') == $cs->coSoId ? 'selected' : '' }}>
                    {{ $cs->tenCoSo }}
                </option>
            @endforeach
        </select>

        <select name="trangThai" onchange="this.form.submit()">
            <option value="">Tất cả trạng thái</option>
            <option value="0" {{ request('trangThai') === '0' ? 'selected' : '' }}>Sắp mở</option>
            <option value="1" {{ request('trangThai') === '1' ? 'selected' : '' }}>Đang mở</option>
            <option value="4" {{ request('trangThai') === '4' ? 'selected' : '' }}>Đang học</option>
            <option value="2" {{ request('trangThai') === '2' ? 'selected' : '' }}>Đã đóng</option>
            <option value="3" {{ request('trangThai') === '3' ? 'selected' : '' }}>Đã hủy</option>
        </select>

        <select name="orderBy" onchange="this.form.submit()">
            <option value="lopHocId" {{ request('orderBy', 'lopHocId') === 'lopHocId' ? 'selected' : '' }}>Mới nhất
            </option>
            <option value="tenLopHoc" {{ request('orderBy') === 'tenLopHoc' ? 'selected' : '' }}>Tên A-Z</option>
            <option value="ngayBatDau" {{ request('orderBy') === 'ngayBatDau' ? 'selected' : '' }}>Ngày bắt đầu</option>
        </select>

        <button type="submit" class="lh-btn-filter lh-btn-filter-primary">
            <i class="fas fa-filter"></i> Lọc
        </button>
        <a href="{{ route('admin.lop-hoc.index') }}" class="lh-btn-filter lh-btn-filter-reset">
            <i class="fas fa-times"></i> Đặt lại
        </a>
    </form>

    {{-- ── Table card ────────────────────────────────────────────── --}}
    <div class="lh-card">
        <div class="lh-table-header">
            <div class="lh-table-title"><i class="fas fa-list me-2"></i> Danh sách lớp học</div>
            <div style="font-size:.82rem;color:#94a3b8">
                Hiển thị {{ $lopHocs->firstItem() ?? 0 }}–{{ $lopHocs->lastItem() ?? 0 }} / {{ $lopHocs->total() }}
            </div>
        </div>

        @if ($lopHocs->isEmpty())
            <div class="lh-empty">
                <i class="fas fa-chalkboard"></i>
                <p>Không tìm thấy lớp học nào.</p>
                @if (request()->anyFilled(['q', 'khoaHocId', 'coSoId', 'trangThai']))
                    <a href="{{ route('admin.lop-hoc.index') }}" class="lh-btn-filter lh-btn-filter-reset"
                        style="margin-top:10px;display:inline-flex">Xóa bộ lọc</a>
                @endif
            </div>
        @else
            <div style="overflow-x:auto">
                <table class="lh-table">
                    <thead>
                        <tr>
                            <th style="width:44px">#</th>
                            <th>Tên lớp</th>
                            <th>Khóa học</th>
                            <th>Giáo viên</th>
                            <th>Cơ sở</th>
                            <th>Ca học</th>
                            <th>Lịch học</th>
                            <th>Sĩ số</th>
                            <th>Ngày bắt đầu</th>
                            <th>Trạng thái</th>
                            <th style="text-align:center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lopHocs as $lop)
                            @php
                                $ttLabels = ['Sắp mở', 'Đang mở', 'Đã đóng', 'Đã hủy', 'Đang học'];
                                $ttLabel = $ttLabels[$lop->trangThai] ?? '?';
                                $soHV = $lop->dangKyLopHocs->count();
                            @endphp
                            <tr>
                                <td style="color:#94a3b8;font-size:.78rem">{{ $lopHocs->firstItem() + $loop->index }}</td>
                                <td>
                                    <a href="{{ route('admin.lop-hoc.show', $lop->slug) }}"
                                        style="font-weight:600;color:#4c1d95;text-decoration:none">
                                        <span class="badge"
                                            style="background:#e0f2fe;color:#0284c7;border:1px solid #bae6fd;font-size:0.75rem;padding:2px 6px;margin-right:5px;border-radius:4px;">{{ $lop->maLopHoc }}</span>
                                        {{ $lop->tenLopHoc }}
                                    </a>
                                    @if ($lop->soBuoiDuKien)
                                        <div style="font-size:.72rem;color:#94a3b8">{{ $lop->soBuoiDuKien }} buổi dự kiến
                                        </div>
                                    @endif
                                </td>
                                <td style="font-size:.82rem">
                                    <a href="{{ route('admin.khoa-hoc.show', $lop->khoaHoc->slug) }}"
                                        style="color:#0f766e;text-decoration:none;font-weight:500">
                                        {{ $lop->khoaHoc?->tenKhoaHoc ?? '—' }}
                                    </a>
                                </td>
                                <td style="font-size:.83rem">
                                    {{ $lop->taiKhoan?->hoSoNguoiDung?->hoTen ?? '—' }}
                                </td>
                                <td style="font-size:.83rem">{{ $lop->coSo?->tenCoSo ?? '—' }}</td>
                                <td style="font-size:.83rem">
                                    @if ($lop->caHoc)
                                        {{ $lop->caHoc->tenCa }}
                                        <div style="font-size:.7rem;color:#94a3b8">
                                            {{ $lop->caHoc->gioBatDau }} – {{ $lop->caHoc->gioKetThuc }}
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td style="font-size:.82rem">
                                    @if ($lop->lichHoc)
                                        @php
                                            $thuMap = [
                                                '2' => 'T2',
                                                '3' => 'T3',
                                                '4' => 'T4',
                                                '5' => 'T5',
                                                '6' => 'T6',
                                                '7' => 'T7',
                                                'CN' => 'CN',
                                            ];
                                            $thuArr = array_map('trim', explode(',', $lop->lichHoc));
                                        @endphp
                                        <div style="display:flex;flex-wrap:wrap;gap:3px">
                                            @foreach ($thuArr as $thu)
                                                <span
                                                    style="background:#ede9fe;color:#7c3aed;padding:1px 6px;border-radius:4px;font-size:.7rem;font-weight:600">
                                                    {{ $thuMap[$thu] ?? $thu }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td style="font-size:.83rem;text-align:center">
                                    <span style="font-weight:600;color:#4c1d95">{{ $soHV }}</span>
                                    @if ($lop->soHocVienToiDa)
                                        <span style="color:#94a3b8"> / {{ $lop->soHocVienToiDa }}</span>
                                    @endif
                                </td>
                                <td style="font-size:.82rem;color:#64748b;white-space:nowrap">
                                    {{ $lop->ngayBatDau ? \Carbon\Carbon::parse($lop->ngayBatDau)->format('d/m/Y') : '—' }}
                                </td>
                                <td>
                                    <span class="lh-tt lh-tt-{{ $lop->trangThai }}">{{ $ttLabel }}</span>
                                </td>
                                <td>
                                    <div class="lh-actions">
                                        <a href="{{ route('admin.lop-hoc.show', $lop->slug) }}"
                                            class="lh-btn-action lh-btn-view" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.lop-hoc.edit', $lop->slug) }}"
                                            class="lh-btn-action lh-btn-edit" title="Chỉnh sửa">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <button type="button" class="lh-btn-action lh-btn-del" title="Xóa"
                                            onclick="confirmDeleteLH({{ $lop->lopHocId }}, '{{ addslashes($lop->tenLopHoc) }}', {{ $soHV }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($lopHocs->hasPages())
                <div class="lh-pagination">
                    <div class="lh-pagination-info">
                        Trang {{ $lopHocs->currentPage() }} / {{ $lopHocs->lastPage() }}
                    </div>
                    {{ $lopHocs->links() }}
                </div>
            @endif
        @endif
    </div>

@endsection

<form id="delete-lh-form" method="POST" style="display:none">
    @csrf
    @method('DELETE')
</form>

@section('script')
    <script>
        function confirmDeleteLH(id, name, soHV) {
            if (soHV > 0) {
                Swal.fire({
                    title: 'Không thể xóa!',
                    html: `Lớp học <strong>${name}</strong> đang có <strong>${soHV} học viên</strong> đăng ký.`,
                    icon: 'warning',
                    confirmButtonText: 'Đã hiểu',
                    confirmButtonColor: '#7c3aed',
                });
                return;
            }
            Swal.fire({
                title: 'Xóa lớp học?',
                html: `Xóa <strong>${name}</strong> sẽ xóa toàn bộ buổi học thuộc lớp.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-trash me-1"></i> Xóa',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
            }).then(r => {
                if (r.isConfirmed) {
                    const form = document.getElementById('delete-lh-form');
                    form.action = `/admin/lop-hoc/${id}`;
                    form.submit();
                }
            });
        }
        document.querySelector('.search-input')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') document.getElementById('lh-filter-form').submit();
        });
    </script>
@endsection
