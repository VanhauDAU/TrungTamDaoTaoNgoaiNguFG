@extends('layouts.admin')

@section('title', 'Quản lý hóa đơn')
@section('page-title', 'Tài chính')
@section('breadcrumb', 'Quản lý tài chính · Hóa đơn')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/hoa-don/index.css') }}">
@endsection

@section('content')
    @php
        $advancedVisible =
            request()->filled('coSoId') ||
            request()->filled('tuNgay') ||
            request()->filled('denNgay') ||
            request()->filled('hanThanhToan');
    @endphp

    <div class="invoice-page container-fluid px-0">

        {{-- Header --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 invoice-header-card">
            <div class="card-body p-4 d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="invoice-header-icon">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </span>
                        <h1 class="h4 fw-bold mb-0 text-dark">Danh sách hóa đơn</h1>
                    </div>
                    <p class="text-secondary mb-0">
                        Theo dõi công nợ học phí chính và các khoản bổ sung theo từng học viên.
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <span class="badge rounded-pill text-bg-light border px-3 py-2 fw-semibold">
                        <i class="fas fa-file-invoice me-1 text-primary"></i>
                        {{ number_format($hoaDons->total()) }} hóa đơn
                    </span>
                    <span class="badge rounded-pill text-bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fw-semibold">
                        <i class="fas fa-triangle-exclamation me-1"></i>
                        {{ number_format($resultStats['tongConNo'], 0, ',', '.') }}đ còn phải thu
                    </span>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3">
                <div class="card stat-card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon bg-success-subtle text-success">
                            <i class="fas fa-list-check"></i>
                        </div>
                        <div>
                            <div class="stat-value">{{ number_format($resultStats['tongKetQua']) }}</div>
                            <div class="stat-label">Kết quả đang xem</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="card stat-card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon bg-danger-subtle text-danger">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <div>
                            <div class="stat-value">{{ number_format($resultStats['tongConNo'], 0, ',', '.') }}đ</div>
                            <div class="stat-label">Còn nợ · {{ number_format($resultStats['dangChoThu']) }} hóa đơn</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="card stat-card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon bg-primary-subtle text-primary">
                            <i class="fas fa-circle-check"></i>
                        </div>
                        <div>
                            <div class="stat-value">{{ number_format($resultStats['tongDaThu'], 0, ',', '.') }}đ</div>
                            <div class="stat-label">Đã thu trong danh sách</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="card stat-card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon bg-warning-subtle text-warning">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div>
                            <div class="stat-value">{{ number_format($resultStats['hocPhiCount']) }} / {{ number_format($resultStats['phuPhiCount']) }}</div>
                            <div class="stat-label">Học phí / Phụ phí</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <form action="{{ route('admin.hoa-don.index') }}" method="GET" id="invoiceFilterForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-xl-5">
                            <label class="form-label fw-semibold">Tìm kiếm</label>
                            <div class="input-group input-group-modern">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-secondary"></i>
                                </span>
                                <input type="text" name="q" class="form-control border-start-0"
                                    placeholder="Mã hóa đơn, học viên, lớp học..."
                                    value="{{ request('q') }}" autocomplete="off">
                            </div>
                        </div>

                        <div class="col-6 col-md-4 col-xl-2">
                            <label class="form-label fw-semibold">Nguồn thu</label>
                            <select name="nguonThu" class="form-select">
                                <option value="">Tất cả</option>
                                <option value="{{ \App\Models\Finance\HoaDon::NGUON_THU_HOC_PHI }}"
                                    @selected(request('nguonThu') === \App\Models\Finance\HoaDon::NGUON_THU_HOC_PHI)>
                                    Học phí chính
                                </option>
                                <option value="{{ \App\Models\Finance\HoaDon::NGUON_THU_PHU_PHI }}"
                                    @selected(request('nguonThu') === \App\Models\Finance\HoaDon::NGUON_THU_PHU_PHI)>
                                    Khoản bổ sung
                                </option>
                            </select>
                        </div>

                        <div class="col-6 col-md-4 col-xl-2">
                            <label class="form-label fw-semibold">Trạng thái</label>
                            <select name="trangThai" class="form-select">
                                <option value="">Tất cả</option>
                                <option value="0" @selected(request('trangThai') === '0')>Chưa thanh toán</option>
                                <option value="1" @selected(request('trangThai') === '1')>Một phần</option>
                                <option value="2" @selected(request('trangThai') === '2')>Đã đủ</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4 col-xl-3">
                            <div class="d-flex flex-wrap gap-2 justify-content-xl-end">
                                <button type="button" class="btn btn-outline-secondary rounded-3" id="toggleAdvancedFilters">
                                    <i class="fas fa-sliders-h me-1"></i> Nâng cao
                                </button>
                                <button type="submit" class="btn btn-primary rounded-3">
                                    <i class="fas fa-filter me-1"></i> Lọc
                                </button>
                                <a href="{{ route('admin.hoa-don.index') }}" class="btn btn-light border rounded-3">
                                    Đặt lại
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Quick filters --}}
                    <div class="quick-filter-wrap mt-3">
                        <button type="button"
                            class="quick-filter-chip {{ request('hanThanhToan') === 'qua_han' ? 'active' : '' }}"
                            data-filter-name="hanThanhToan" data-filter-value="qua_han">
                            <i class="fas fa-fire-flame-curved me-1"></i> Quá hạn
                        </button>

                        <button type="button"
                            class="quick-filter-chip {{ request('hanThanhToan') === 'sap_het_han' ? 'active' : '' }}"
                            data-filter-name="hanThanhToan" data-filter-value="sap_het_han">
                            <i class="fas fa-clock me-1"></i> Sắp hết hạn
                        </button>

                        <button type="button"
                            class="quick-filter-chip {{ request('nguonThu') === \App\Models\Finance\HoaDon::NGUON_THU_HOC_PHI ? 'active' : '' }}"
                            data-filter-name="nguonThu" data-filter-value="{{ \App\Models\Finance\HoaDon::NGUON_THU_HOC_PHI }}">
                            Chỉ học phí
                        </button>

                        <button type="button"
                            class="quick-filter-chip {{ request('nguonThu') === \App\Models\Finance\HoaDon::NGUON_THU_PHU_PHI ? 'active' : '' }}"
                            data-filter-name="nguonThu" data-filter-value="{{ \App\Models\Finance\HoaDon::NGUON_THU_PHU_PHI }}">
                            Chỉ phụ phí
                        </button>
                    </div>

                    {{-- Advanced --}}
                    <div class="advanced-filter-box {{ $advancedVisible ? 'show' : '' }}" id="invoiceAdvancedFilters">
                        <div class="row g-3 mt-1">
                            <div class="col-12 col-md-6 col-xl-3">
                                <label class="form-label fw-semibold">Cơ sở</label>
                                <select name="coSoId" class="form-select">
                                    <option value="">Tất cả cơ sở</option>
                                    @foreach ($coSos as $coSo)
                                        <option value="{{ $coSo->coSoId }}" @selected((string) request('coSoId') === (string) $coSo->coSoId)>
                                            {{ $coSo->tenCoSo }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-6 col-xl-3">
                                <label class="form-label fw-semibold">Hạn thanh toán</label>
                                <select name="hanThanhToan" class="form-select">
                                    <option value="">Tất cả</option>
                                    <option value="sap_het_han" @selected(request('hanThanhToan') === 'sap_het_han')>Sắp hết hạn</option>
                                    <option value="qua_han" @selected(request('hanThanhToan') === 'qua_han')>Quá hạn</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6 col-xl-3">
                                <label class="form-label fw-semibold">Ngày lập từ</label>
                                <input type="date" name="tuNgay" class="form-control" value="{{ request('tuNgay') }}">
                            </div>

                            <div class="col-12 col-md-6 col-xl-3">
                                <label class="form-label fw-semibold">Ngày lập đến</label>
                                <input type="date" name="denNgay" class="form-control" value="{{ request('denNgay') }}">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom-0 p-4 pb-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div>
                        <h2 class="h6 fw-bold mb-1 text-dark">Danh sách hóa đơn</h2>
                        <div class="text-secondary small">
                            Hiển thị {{ $hoaDons->firstItem() ?? 0 }}–{{ $hoaDons->lastItem() ?? 0 }}
                            trên {{ $hoaDons->total() }} bản ghi
                        </div>
                    </div>
                </div>
            </div>

            @if ($hoaDons->isEmpty())
                <div class="card-body py-5 text-center empty-state">
                    <div class="empty-icon mb-3">
                        <i class="fas fa-file-circle-xmark"></i>
                    </div>
                    <h6 class="fw-bold mb-2">Không có hóa đơn phù hợp</h6>
                    <p class="text-secondary mb-3">Hãy thử thay đổi bộ lọc để xem thêm dữ liệu.</p>
                    <a href="{{ route('admin.hoa-don.index') }}" class="btn btn-outline-secondary rounded-3">
                        Đặt lại bộ lọc
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle mb-0 invoice-table">
                        <thead>
                            <tr>
                                <th>Học viên</th>
                                <th>Mã HĐ / Trạng thái</th>
                                <th>Lớp học</th>
                                <th>Khoản thu</th>
                                <th>Tiến độ</th>
                                <th class="text-end">Tổng phải thu</th>
                                <th class="text-end">Còn nợ</th>
                                <th>Ngày lập / Hạn TT</th>
                                <th class="text-center">Xem</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($hoaDons as $hoaDon)
                                @php
                                    $profile  = $hoaDon->taiKhoan?->hoSoNguoiDung;
                                    $hoTen    = $profile->hoTen ?? ($hoaDon->taiKhoan?->taiKhoan ?? '—');
                                    $initials = mb_strtoupper(mb_substr(trim($hoTen), 0, 1));
                                    $maHD     = $hoaDon->maHoaDon ?: 'HD-' . str_pad($hoaDon->hoaDonId, 6, '0', STR_PAD_LEFT);
                                    $tongPhaiThu = max(0, (float) $hoaDon->tongTien - (float) $hoaDon->giamGia);
                                    $conNo    = (float) $hoaDon->conNo;
                                    $daThu    = (float) $hoaDon->daTra;
                                    $pct      = $tongPhaiThu > 0 ? min(100, (int) round(($daThu / $tongPhaiThu) * 100)) : 100;
                                    $nguCanhThu = $hoaDon->nguonThu === \App\Models\Finance\HoaDon::NGUON_THU_PHU_PHI
                                        ? ($hoaDon->dangKyLopHocPhuPhi?->tenKhoanThuSnapshot ?? 'Khoản bổ sung')
                                        : ($hoaDon->lopHocDotThu?->tenDotThu ?? 'Thu học phí một lần');
                                    $isPhuPhi = $hoaDon->nguonThu === \App\Models\Finance\HoaDon::NGUON_THU_PHU_PHI;
                                @endphp

                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="invoice-avatar">{{ $initials }}</div>
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $hoTen }}</div>
                                                <div class="small text-secondary">{{ $hoaDon->taiKhoan?->email ?? '—' }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="mb-1">
                                            <span class="invoice-code">{{ $maHD }}</span>
                                        </div>

                                        <div class="d-flex flex-wrap gap-1">
                                            <span class="badge rounded-pill {{ $isPhuPhi ? 'text-bg-warning' : 'text-bg-success' }}">
                                                {{ $hoaDon->nguonThuLabel }}
                                            </span>

                                            @if ($hoaDon->trangThai == 2)
                                                <span class="badge rounded-pill text-bg-success">Đã đủ</span>
                                            @elseif ($hoaDon->trangThai == 1)
                                                <span class="badge rounded-pill text-bg-warning">Một phần</span>
                                            @else
                                                <span class="badge rounded-pill text-bg-danger">Chưa TT</span>
                                            @endif

                                            @if ($hoaDon->isQuaHan)
                                                <span class="badge rounded-pill text-bg-danger">Quá hạn</span>
                                            @elseif ($hoaDon->isSapHetHan)
                                                <span class="badge rounded-pill text-bg-warning">{{ $hoaDon->tinhTrangHanLabel }}</span>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        <div class="fw-semibold text-dark">
                                            {{ $hoaDon->dangKyLopHoc?->lopHoc?->tenLopHoc ?? '—' }}
                                        </div>
                                        <div class="small text-secondary">
                                            {{ $hoaDon->dangKyLopHoc?->lopHoc?->khoaHoc?->tenKhoaHoc ?? '' }}
                                        </div>
                                    </td>

                                    <td>
                                        <div class="fw-semibold text-dark">{{ $nguCanhThu }}</div>
                                        <div class="small text-secondary">{{ $hoaDon->coSo?->tenCoSo ?? '—' }}</div>
                                    </td>

                                    <td style="min-width: 150px;">
                                        <div class="progress invoice-progress mb-1" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                                            <div class="progress-bar" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <div class="small text-secondary fw-medium">{{ $pct }}%</div>
                                    </td>

                                    <td class="text-end">
                                        <div class="fw-bold text-dark">{{ number_format($tongPhaiThu, 0, ',', '.') }}đ</div>
                                    </td>

                                    <td class="text-end">
                                        <div class="fw-bold {{ $conNo > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($conNo, 0, ',', '.') }}đ
                                        </div>
                                    </td>

                                    <td class="text-nowrap">
                                        <div class="fw-semibold text-dark">
                                            {{ $hoaDon->ngayLap ? \Carbon\Carbon::parse($hoaDon->ngayLap)->format('d/m/Y') : '—' }}
                                        </div>
                                        <div class="small {{ $hoaDon->isQuaHan ? 'text-danger fw-semibold' : 'text-secondary' }}">
                                            {{ $hoaDon->ngayHetHan ? 'Hạn ' . \Carbon\Carbon::parse($hoaDon->ngayHetHan)->format('d/m/Y') : 'Không đặt hạn' }}
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <a href="{{ route('admin.hoa-don.show', $hoaDon->hoaDonId) }}"
                                            class="btn btn-sm btn-light border rounded-circle action-btn"
                                            title="Xem chi tiết">
                                            <i class="fas fa-eye text-primary"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($hoaDons->hasPages())
                    <div class="card-footer bg-white border-top px-4 py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                        <div class="small text-secondary">
                            Trang {{ $hoaDons->currentPage() }} / {{ $hoaDons->lastPage() }}
                        </div>
                        {{ $hoaDons->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection

@section('script')
    <script>
        const filterForm = document.getElementById('invoiceFilterForm');
        const advancedFilters = document.getElementById('invoiceAdvancedFilters');

        document.getElementById('toggleAdvancedFilters')?.addEventListener('click', () => {
            advancedFilters?.classList.toggle('show');
        });

        document.querySelectorAll('.quick-filter-chip').forEach((btn) => {
            btn.addEventListener('click', () => {
                const field = filterForm?.querySelector(`[name="${btn.dataset.filterName}"]`);
                if (!field) return;
                const next = btn.dataset.filterValue ?? '';
                field.value = field.value === next && next !== '' ? '' : next;
                filterForm?.submit();
            });
        });

        filterForm?.querySelector('input[name="q"]')?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') filterForm.submit();
        });
    </script>
@endsection