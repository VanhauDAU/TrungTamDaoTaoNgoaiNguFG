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

    <div class="invoice-index-page">
        <div class="invoice-index-header">
            <div>
                <h1 class="invoice-index-title">Danh sách hóa đơn</h1>
                <p class="invoice-index-subtitle">
                    Theo dõi công nợ học phí chính và khoản bổ sung theo từng học viên.
                </p>
            </div>
            <div class="invoice-index-meta">
                <span>{{ number_format($hoaDons->total()) }} hóa đơn</span>
                <span>{{ number_format($resultStats['tongConNo'], 0, ',', '.') }}đ còn phải thu</span>
            </div>
        </div>

        <div class="row g-3 invoice-stats-row">
            <div class="col-6 col-xl-3">
                <div class="invoice-stat-card">
                    <span class="invoice-stat-card__label">Kết quả đang xem</span>
                    <strong class="invoice-stat-card__value">{{ number_format($resultStats['tongKetQua']) }}</strong>
                    <small class="invoice-stat-card__hint">Theo bộ lọc hiện tại</small>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="invoice-stat-card">
                    <span class="invoice-stat-card__label">Còn nợ</span>
                    <strong class="invoice-stat-card__value">{{ number_format($resultStats['tongConNo'], 0, ',', '.') }}đ</strong>
                    <small class="invoice-stat-card__hint">{{ number_format($resultStats['dangChoThu']) }} hóa đơn chưa đủ tiền</small>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="invoice-stat-card">
                    <span class="invoice-stat-card__label">Đã thu</span>
                    <strong class="invoice-stat-card__value">{{ number_format($resultStats['tongDaThu'], 0, ',', '.') }}đ</strong>
                    <small class="invoice-stat-card__hint">Trong danh sách đang xem</small>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="invoice-stat-card">
                    <span class="invoice-stat-card__label">Nguồn thu</span>
                    <strong class="invoice-stat-card__value">{{ number_format($resultStats['hocPhiCount']) }} / {{ number_format($resultStats['phuPhiCount']) }}</strong>
                    <small class="invoice-stat-card__hint">Học phí / Phụ phí</small>
                </div>
            </div>
        </div>

        <div class="card invoice-filter-card">
            <div class="card-body">
                <form action="{{ route('admin.hoa-don.index') }}" method="GET" id="invoiceFilterForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-xl-5">
                            <label class="form-label">Tìm kiếm</label>
                            <input type="text" name="q" class="form-control"
                                placeholder="Mã hóa đơn, học viên, lớp học, khoản bổ sung..."
                                value="{{ request('q') }}" autocomplete="off">
                        </div>
                        <div class="col-6 col-md-4 col-xl-2">
                            <label class="form-label">Nguồn thu</label>
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
                            <label class="form-label">Trạng thái</label>
                            <select name="trangThai" class="form-select">
                                <option value="">Tất cả</option>
                                <option value="0" @selected(request('trangThai') === '0')>Chưa thanh toán</option>
                                <option value="1" @selected(request('trangThai') === '1')>Một phần</option>
                                <option value="2" @selected(request('trangThai') === '2')>Đã đủ</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 col-xl-3">
                            <div class="invoice-filter-actions">
                                <button type="button" class="btn btn-outline-secondary" id="toggleAdvancedFilters">
                                    <i class="fas fa-sliders-h me-1"></i> Nâng cao
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-1"></i> Lọc
                                </button>
                                <a href="{{ route('admin.hoa-don.index') }}" class="btn btn-light border">
                                    Đặt lại
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="invoice-quick-filters">
                        <button type="button"
                            class="invoice-chip {{ request('hanThanhToan') === 'qua_han' ? 'is-active' : '' }}"
                            data-filter-name="hanThanhToan" data-filter-value="qua_han">
                            Quá hạn
                        </button>
                        <button type="button"
                            class="invoice-chip {{ request('hanThanhToan') === 'sap_het_han' ? 'is-active' : '' }}"
                            data-filter-name="hanThanhToan" data-filter-value="sap_het_han">
                            Sắp hết hạn
                        </button>
                        <button type="button"
                            class="invoice-chip {{ request('nguonThu') === \App\Models\Finance\HoaDon::NGUON_THU_HOC_PHI ? 'is-active' : '' }}"
                            data-filter-name="nguonThu" data-filter-value="{{ \App\Models\Finance\HoaDon::NGUON_THU_HOC_PHI }}">
                            Chỉ học phí
                        </button>
                        <button type="button"
                            class="invoice-chip {{ request('nguonThu') === \App\Models\Finance\HoaDon::NGUON_THU_PHU_PHI ? 'is-active' : '' }}"
                            data-filter-name="nguonThu" data-filter-value="{{ \App\Models\Finance\HoaDon::NGUON_THU_PHU_PHI }}">
                            Chỉ phụ phí
                        </button>
                    </div>

                    <div class="invoice-advanced-section {{ $advancedVisible ? 'is-open' : '' }}" id="invoiceAdvancedFilters">
                        <div class="row g-3">
                            <div class="col-12 col-md-6 col-xl-3">
                                <label class="form-label">Cơ sở</label>
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
                                <label class="form-label">Hạn thanh toán</label>
                                <select name="hanThanhToan" class="form-select">
                                    <option value="">Tất cả</option>
                                    <option value="sap_het_han" @selected(request('hanThanhToan') === 'sap_het_han')>Sắp hết hạn</option>
                                    <option value="qua_han" @selected(request('hanThanhToan') === 'qua_han')>Quá hạn</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 col-xl-3">
                                <label class="form-label">Ngày lập từ</label>
                                <input type="date" name="tuNgay" class="form-control" value="{{ request('tuNgay') }}">
                            </div>
                            <div class="col-12 col-md-6 col-xl-3">
                                <label class="form-label">Ngày lập đến</label>
                                <input type="date" name="denNgay" class="form-control" value="{{ request('denNgay') }}">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card invoice-list-card">
            <div class="card-body">
                <div class="invoice-list-head">
                    <div>
                        <h2 class="invoice-list-head__title">Danh sách hóa đơn</h2>
                        <p class="invoice-list-head__subtitle">
                            Hiển thị {{ $hoaDons->firstItem() ?? 0 }}–{{ $hoaDons->lastItem() ?? 0 }} trên {{ $hoaDons->total() }} bản ghi.
                        </p>
                    </div>
                </div>

                @if ($hoaDons->isEmpty())
                    <div class="invoice-empty">
                        <i class="fas fa-file-circle-xmark"></i>
                        <h3>Không có hóa đơn phù hợp</h3>
                        <p>Thử đổi bộ lọc hoặc tìm lại theo mã hóa đơn, học viên hoặc lớp học.</p>
                    </div>
                @else
                    <div class="invoice-list">
                        @foreach ($hoaDons as $hoaDon)
                            @php
                                $profile = $hoaDon->taiKhoan?->hoSoNguoiDung;
                                $hoTen = $profile->hoTen ?? ($hoaDon->taiKhoan?->taiKhoan ?? '—');
                                $maHD = $hoaDon->maHoaDon ?: 'HD-' . str_pad($hoaDon->hoaDonId, 6, '0', STR_PAD_LEFT);
                                $tongPhaiThu = max(0, (float) $hoaDon->tongTien - (float) $hoaDon->giamGia);
                                $conNo = (float) $hoaDon->conNo;
                                $daThu = (float) $hoaDon->daTra;
                                $phanTramThu = $tongPhaiThu > 0 ? min(100, (int) round(($daThu / $tongPhaiThu) * 100)) : 100;
                                $nguCanhThu =
                                    $hoaDon->nguonThu === \App\Models\Finance\HoaDon::NGUON_THU_PHU_PHI
                                        ? ($hoaDon->dangKyLopHocPhuPhi?->tenKhoanThuSnapshot ?? 'Khoản bổ sung')
                                        : ($hoaDon->lopHocDotThu?->tenDotThu ?? 'Thu học phí một lần');
                            @endphp

                            <article class="invoice-list-item">
                                <div class="invoice-list-item__main">
                                    <div class="invoice-list-item__row invoice-list-item__row--top">
                                        <div class="invoice-list-item__identity">
                                            <div class="invoice-list-item__badges">
                                                <span class="invoice-badge invoice-badge--code">{{ $maHD }}</span>
                                                <span
                                                    class="invoice-badge invoice-badge--{{ $hoaDon->nguonThu === \App\Models\Finance\HoaDon::NGUON_THU_PHU_PHI ? 'supplemental' : 'tuition' }}">
                                                    {{ $hoaDon->nguonThuLabel }}
                                                </span>
                                                @if ($hoaDon->trangThai == 2)
                                                    <span class="invoice-badge invoice-badge--paid">Đã đủ</span>
                                                @elseif ($hoaDon->trangThai == 1)
                                                    <span class="invoice-badge invoice-badge--partial">Một phần</span>
                                                @else
                                                    <span class="invoice-badge invoice-badge--unpaid">Chưa thanh toán</span>
                                                @endif
                                                @if ($hoaDon->isQuaHan)
                                                    <span class="invoice-badge invoice-badge--danger">Quá hạn</span>
                                                @elseif ($hoaDon->isSapHetHan)
                                                    <span class="invoice-badge invoice-badge--warning">{{ $hoaDon->tinhTrangHanLabel }}</span>
                                                @endif
                                            </div>
                                            <h3>{{ $hoTen }}</h3>
                                            <p>
                                                {{ $hoaDon->taiKhoan?->email ?? 'Không có email' }}
                                                @if ($profile?->soDienThoai)
                                                    · {{ $profile->soDienThoai }}
                                                @endif
                                            </p>
                                        </div>

                                        <div class="invoice-list-item__amounts">
                                            <div>
                                                <span>Tổng phải thu</span>
                                                <strong>{{ number_format($tongPhaiThu, 0, ',', '.') }}đ</strong>
                                            </div>
                                            <div>
                                                <span>Đã thu</span>
                                                <strong class="text-success">{{ number_format($daThu, 0, ',', '.') }}đ</strong>
                                            </div>
                                            <div>
                                                <span>Còn nợ</span>
                                                <strong class="{{ $conNo > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($conNo, 0, ',', '.') }}đ</strong>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="invoice-list-item__meta">
                                        <div class="invoice-meta-cell">
                                            <span class="invoice-meta-cell__label">Lớp học</span>
                                            <strong>{{ $hoaDon->dangKyLopHoc?->lopHoc?->tenLopHoc ?? 'Không gắn lớp' }}</strong>
                                            <small>{{ $hoaDon->dangKyLopHoc?->lopHoc?->khoaHoc?->tenKhoaHoc ?? '—' }}</small>
                                        </div>
                                        <div class="invoice-meta-cell">
                                            <span class="invoice-meta-cell__label">Khoản thu</span>
                                            <strong>{{ $nguCanhThu }}</strong>
                                            <small>{{ $hoaDon->coSo?->tenCoSo ?? '—' }}</small>
                                        </div>
                                        <div class="invoice-meta-cell">
                                            <span class="invoice-meta-cell__label">Ngày lập</span>
                                            <strong>{{ $hoaDon->ngayLap ? \Carbon\Carbon::parse($hoaDon->ngayLap)->format('d/m/Y') : '—' }}</strong>
                                            <small>
                                                {{ $hoaDon->ngayHetHan ? 'Hạn ' . \Carbon\Carbon::parse($hoaDon->ngayHetHan)->format('d/m/Y') : 'Không đặt hạn' }}
                                            </small>
                                        </div>
                                    </div>

                                    <div class="invoice-progress">
                                        <div class="invoice-progress__bar">
                                            <span style="width: {{ $phanTramThu }}%"></span>
                                        </div>
                                        <div class="invoice-progress__meta">
                                            <span>Tiến độ thu</span>
                                            <strong>{{ $phanTramThu }}%</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="invoice-list-item__actions">
                                    <a href="{{ route('admin.hoa-don.show', $hoaDon->hoaDonId) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i> Chi tiết
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    @if ($hoaDons->hasPages())
                        <div class="invoice-pagination">
                            <div class="invoice-pagination__info">
                                Trang {{ $hoaDons->currentPage() }} / {{ $hoaDons->lastPage() }}
                            </div>
                            {{ $hoaDons->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        const filterForm = document.getElementById('invoiceFilterForm');
        const advancedFilters = document.getElementById('invoiceAdvancedFilters');

        document.getElementById('toggleAdvancedFilters')?.addEventListener('click', () => {
            advancedFilters?.classList.toggle('is-open');
        });

        document.querySelectorAll('.invoice-chip').forEach((button) => {
            button.addEventListener('click', () => {
                const field = filterForm?.querySelector(`[name="${button.dataset.filterName}"]`);
                if (!field) return;

                const nextValue = button.dataset.filterValue ?? '';
                field.value = field.value === nextValue && nextValue !== '' ? '' : nextValue;
                filterForm?.submit();
            });
        });

        filterForm?.querySelector('input[name="q"]')?.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                filterForm.submit();
            }
        });
    </script>
@endsection
