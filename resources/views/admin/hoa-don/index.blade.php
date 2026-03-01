@extends('layouts.admin')

@section('title', 'Quản lý hóa đơn')
@section('page-title', 'Tài chính')
@section('breadcrumb', 'Quản lý tài chính · Hóa Đơn & Phiếu Thu')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/hoa-don/index.css') }}">
@endsection

@section('content')

    {{-- ── Page Header ───────────────────────────────────────────── --}}
    <div class="hd-page-header">
        <div class="hd-page-title">
            <i class="fas fa-file-invoice-dollar me-2" style="color:#6366f1"></i>Quản lý hóa đơn
            <span>{{ $hoaDons->total() }} hóa đơn</span>
        </div>
    </div>

    {{-- ── Stats strip ────────────────────────────────────────────── --}}
    <div class="hd-stats">
        <div class="hd-stat-card">
            <div class="hd-stat-icon total"><i class="fas fa-file-invoice"></i></div>
            <div>
                <div class="hd-stat-value">{{ number_format($tongSo) }}</div>
                <div class="hd-stat-label">Tổng hóa đơn</div>
            </div>
        </div>
        <div class="hd-stat-card">
            <div class="hd-stat-icon unpaid"><i class="fas fa-clock"></i></div>
            <div>
                <div class="hd-stat-value">{{ number_format($chuaTT) }}</div>
                <div class="hd-stat-label">Chưa thanh toán</div>
            </div>
        </div>
        <div class="hd-stat-card">
            <div class="hd-stat-icon partial"><i class="fas fa-hourglass-half"></i></div>
            <div>
                <div class="hd-stat-value">{{ number_format($motPhan) }}</div>
                <div class="hd-stat-label">Thanh toán một phần</div>
            </div>
        </div>
        <div class="hd-stat-card">
            <div class="hd-stat-icon paid"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="hd-stat-value">{{ number_format($daTT) }}</div>
                <div class="hd-stat-label">Đã thanh toán đủ</div>
            </div>
        </div>
        <div class="hd-stat-card">
            <div class="hd-stat-icon revenue"><i class="fas fa-coins"></i></div>
            <div>
                <div class="hd-stat-value">{{ number_format($tongDoanhThu, 0, ',', '.') }}đ</div>
                <div class="hd-stat-label">Tổng đã thu</div>
            </div>
        </div>
    </div>

    {{-- ── Filter bar ─────────────────────────────────────────────── --}}
    <form action="{{ route('admin.hoa-don.index') }}" method="GET" class="hd-filter-bar" id="filter-form">
        {{-- Search --}}
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" name="q" class="search-input" placeholder="Tìm mã HD, tên học viên, email..."
                value="{{ request('q') }}" autocomplete="off">
        </div>

        {{-- Trạng thái --}}
        <select name="trangThai" onchange="this.form.submit()">
            <option value="">Tất cả trạng thái</option>
            <option value="0" {{ request('trangThai') === '0' ? 'selected' : '' }}>Chưa thanh toán</option>
            <option value="1" {{ request('trangThai') === '1' ? 'selected' : '' }}>Một phần</option>
            <option value="2" {{ request('trangThai') === '2' ? 'selected' : '' }}>Đã thanh toán đủ</option>
        </select>

        {{-- Cơ sở --}}
        <select name="coSoId" onchange="this.form.submit()">
            <option value="">Tất cả cơ sở</option>
            @foreach ($coSos as $cs)
                <option value="{{ $cs->coSoId }}" {{ request('coSoId') == $cs->coSoId ? 'selected' : '' }}>
                    {{ $cs->tenCoSo }}
                </option>
            @endforeach
        </select>

        {{-- Khoảng ngày --}}
        <input type="date" name="tuNgay" value="{{ request('tuNgay') }}" title="Từ ngày">
        <input type="date" name="denNgay" value="{{ request('denNgay') }}" title="Đến ngày">

        {{-- Buttons --}}
        <button type="submit" class="btn-filter btn-filter-primary">
            <i class="fas fa-filter"></i> Lọc
        </button>
        <a href="{{ route('admin.hoa-don.index') }}" class="btn-filter btn-filter-reset">
            <i class="fas fa-times"></i> Đặt lại
        </a>
    </form>

    {{-- ── Table card ─────────────────────────────────────────────── --}}
    <div class="hd-card">
        <div class="hd-table-header">
            <div class="hd-table-title"><i class="fas fa-list me-2"></i> Danh sách hóa đơn</div>
            <div class="hd-table-count">
                Hiển thị {{ $hoaDons->firstItem() ?? 0 }}–{{ $hoaDons->lastItem() ?? 0 }}
                / {{ $hoaDons->total() }} bản ghi
            </div>
        </div>

        @if ($hoaDons->isEmpty())
            <div class="hd-empty">
                <i class="fas fa-file-invoice"></i>
                <p>Không tìm thấy hóa đơn nào.</p>
                @if (request()->anyFilled(['q', 'trangThai', 'coSoId', 'tuNgay', 'denNgay']))
                    <a href="{{ route('admin.hoa-don.index') }}" class="btn-filter btn-filter-reset">
                        Xóa bộ lọc
                    </a>
                @endif
            </div>
        @else
            <div style="overflow-x:auto">
                <table class="hd-table">
                    <thead>
                        <tr>
                            <th style="width:44px">#</th>
                            <th>
                                <a class="sort-link"
                                    href="{{ request()->fullUrlWithQuery(['orderBy' => 'hoaDonId', 'dir' => request('orderBy', 'hoaDonId') === 'hoaDonId' && request('dir', 'desc') === 'desc' ? 'asc' : 'desc']) }}">
                                    Mã HD
                                    @if (request('orderBy', 'hoaDonId') === 'hoaDonId')
                                        <i class="fas fa-sort-{{ request('dir', 'desc') === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort" style="opacity:.4"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Học viên</th>
                            <th>Lớp học</th>
                            <th>Cơ sở</th>
                            <th>
                                <a class="sort-link"
                                    href="{{ request()->fullUrlWithQuery(['orderBy' => 'tongTien', 'dir' => request('orderBy') === 'tongTien' && request('dir') === 'asc' ? 'desc' : 'asc']) }}">
                                    Tổng tiền
                                    @if (request('orderBy') === 'tongTien')
                                        <i class="fas fa-sort-{{ request('dir') === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort" style="opacity:.4"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Đã trả</th>
                            <th>Còn nợ</th>
                            <th>Trạng thái</th>
                            <th>
                                <a class="sort-link"
                                    href="{{ request()->fullUrlWithQuery(['orderBy' => 'ngayLap', 'dir' => request('orderBy') === 'ngayLap' && request('dir') === 'asc' ? 'desc' : 'asc']) }}">
                                    Ngày lập
                                    @if (request('orderBy') === 'ngayLap')
                                        <i class="fas fa-sort-{{ request('dir') === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort" style="opacity:.4"></i>
                                    @endif
                                </a>
                            </th>
                            <th style="text-align:center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hoaDons as $hd)
                            @php
                                $profile = $hd->taiKhoan?->hoSoNguoiDung;
                                $hoTen = $profile->hoTen ?? ($hd->taiKhoan->taiKhoan ?? '—');
                                $initials = mb_strtoupper(mb_substr($hoTen, 0, 1));
                                $conNo = max(0, $hd->tongTien - $hd->giamGia - $hd->daTra);
                                $maHD = $hd->maHoaDon ?: ('HD-' . str_pad($hd->hoaDonId, 6, '0', STR_PAD_LEFT));
                            @endphp
                            <tr>
                                <td style="color:#8899a6;font-size:0.78rem">
                                    {{ $hoaDons->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    <span class="hd-code">{{ $maHD }}</span>
                                </td>

                                <td>
                                    <div class="hd-student-info">
                                        <div class="hd-avatar">{{ $initials }}</div>
                                        <div>
                                            <div class="hd-student-name">{{ $hoTen }}</div>
                                            <div class="hd-student-email">{{ $hd->taiKhoan->email ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="hd-class-name">
                                        {{ $hd->dangKyLopHoc?->lopHoc?->tenLopHoc ?? '—' }}
                                    </span>
                                </td>

                                <td>{{ $hd->coSo?->tenCoSo ?? '—' }}</td>

                                <td class="hd-money">{{ number_format($hd->tongTien, 0, ',', '.') }}đ</td>

                                <td class="hd-money text-success">{{ number_format($hd->daTra, 0, ',', '.') }}đ</td>

                                <td class="hd-money {{ $conNo > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($conNo, 0, ',', '.') }}đ
                                </td>

                                <td>
                                    @if ($hd->trangThai == 0)
                                        <span class="badge-status badge-unpaid">
                                            <i class="fas fa-circle" style="font-size:.5em"></i> Chưa TT
                                        </span>
                                    @elseif($hd->trangThai == 1)
                                        <span class="badge-status badge-partial">
                                            <i class="fas fa-circle" style="font-size:.5em"></i> Một phần
                                        </span>
                                    @else
                                        <span class="badge-status badge-paid">
                                            <i class="fas fa-circle" style="font-size:.5em"></i> Đã TT đủ
                                        </span>
                                    @endif
                                </td>

                                <td style="color:#8899a6;font-size:0.8rem;white-space:nowrap">
                                    {{ $hd->ngayLap ? \Carbon\Carbon::parse($hd->ngayLap)->format('d/m/Y') : '—' }}
                                </td>

                                <td>
                                    <div class="hd-actions">
                                        <a href="{{ route('admin.hoa-don.show', $hd->hoaDonId) }}"
                                            class="btn-action btn-action-view" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($hoaDons->hasPages())
                <div class="hd-pagination">
                    <div class="hd-pagination-info">
                        Trang {{ $hoaDons->currentPage() }} / {{ $hoaDons->lastPage() }}
                    </div>
                    {{ $hoaDons->links() }}
                </div>
            @endif
        @endif
    </div>

@endsection

@section('script')
    <script>
        // Enter submit filter
        document.querySelector('.search-input')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') document.getElementById('filter-form').submit();
        });
    </script>
@endsection