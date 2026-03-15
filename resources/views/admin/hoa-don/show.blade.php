@extends('layouts.admin')

@section('title', 'Chi tiết hóa đơn ' . ($hoaDon->maHoaDon ?: 'HD-' . str_pad($hoaDon->hoaDonId, 6, '0', STR_PAD_LEFT)))
@section('page-title', 'Tài chính')
@section('breadcrumb', 'Quản lý tài chính · Chi tiết hóa đơn')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/hoa-don/show.css') }}">
@endsection

@php
    $maHD = $hoaDon->maHoaDon ?: 'HD-' . str_pad($hoaDon->hoaDonId, 6, '0', STR_PAD_LEFT);
    $profile = $hoaDon->taiKhoan?->hoSoNguoiDung;
    $hoTen = $profile->hoTen ?? ($hoaDon->taiKhoan?->taiKhoan ?? '—');
    $tongPhaiThu = max(0, (float) $hoaDon->tongTien - (float) $hoaDon->giamGia);
    $daThu = (float) $hoaDon->daTra;
    $conNo = (float) $hoaDon->conNo;
    $phanTramThu = $tongPhaiThu > 0 ? min(100, (int) round(($daThu / $tongPhaiThu) * 100)) : 100;
    $phieuThuHopLe = $hoaDon->phieuThus->where('trangThai', \App\Models\Finance\PhieuThu::TRANG_THAI_HOP_LE);
    $tongPhieuThuHopLe = (float) $phieuThuHopLe->sum('soTien');
    $soPhieuDaHuy = $hoaDon->phieuThus->where('trangThai', \App\Models\Finance\PhieuThu::TRANG_THAI_HUY)->count();
    $coTheThuTien = auth()->user()->canDo('tai_chinh', 'sua') && $conNo > 0;
    $defaultPanel = $errors->any() ? 'receipt' : 'summary';
@endphp

@section('content')
    <div class="invoice-detail-shell">
        <section
            class="invoice-detail-hero {{ $hoaDon->isQuaHan ? 'is-overdue' : ($hoaDon->trangThai == 2 ? 'is-paid' : '') }}">
            <div class="invoice-detail-hero__main">
                <div class="invoice-detail-hero__eyebrow">Chi tiết hóa đơn</div>
                <div class="invoice-detail-hero__title-row">
                    <h1>{{ $maHD }}</h1>
                    <div class="invoice-detail-hero__badges">
                        <span
                            class="invoice-detail-badge invoice-detail-badge--{{ $hoaDon->nguonThu === \App\Models\Finance\HoaDon::NGUON_THU_PHU_PHI ? 'supplemental' : 'tuition' }}">
                            {{ $hoaDon->nguonThuLabel }}
                        </span>
                        @if ($hoaDon->trangThai == 2)
                            <span class="invoice-detail-badge invoice-detail-badge--paid">Đã thanh toán đủ</span>
                        @elseif ($hoaDon->trangThai == 1)
                            <span class="invoice-detail-badge invoice-detail-badge--partial">Thanh toán một phần</span>
                        @else
                            <span class="invoice-detail-badge invoice-detail-badge--unpaid">Chưa thanh toán</span>
                        @endif
                        @if ($hoaDon->isQuaHan)
                            <span class="invoice-detail-badge invoice-detail-badge--danger">{{ $hoaDon->tinhTrangHanLabel }}</span>
                        @elseif ($hoaDon->isSapHetHan)
                            <span class="invoice-detail-badge invoice-detail-badge--warning">{{ $hoaDon->tinhTrangHanLabel }}</span>
                        @endif
                    </div>
                </div>
                <p class="invoice-detail-hero__subtitle">
                    {{ $hoaDon->dangKyLopHoc?->lopHoc?->tenLopHoc ?? 'Không gắn lớp học' }}
                    @if ($hoaDon->dangKyLopHoc?->lopHoc?->khoaHoc?->tenKhoaHoc)
                        · {{ $hoaDon->dangKyLopHoc->lopHoc->khoaHoc->tenKhoaHoc }}
                    @endif
                    @if ($hoaDon->lopHocDotThu?->tenDotThu)
                        · {{ $hoaDon->lopHocDotThu->tenDotThu }}
                    @endif
                    @if ($hoaDon->dangKyLopHocPhuPhi?->tenKhoanThuSnapshot)
                        · {{ $hoaDon->dangKyLopHocPhuPhi->tenKhoanThuSnapshot }}
                    @endif
                </p>
            </div>
            <div class="invoice-detail-hero__actions">
                <a href="{{ route('admin.hoa-don.index') }}" class="btn btn-light">
                    <i class="fas fa-arrow-left"></i> Quay lại danh sách
                </a>
                @if ($coTheThuTien)
                    <button type="button" class="btn btn-primary" data-panel-trigger="receipt">
                        <i class="fas fa-plus"></i> Lập phiếu thu
                    </button>
                @endif
            </div>
            <div class="invoice-detail-metrics">
                <article class="invoice-detail-metric">
                    <span>Tổng phải thu</span>
                    <strong>{{ number_format($tongPhaiThu, 0, ',', '.') }}đ</strong>
                    <small>Sau giảm giá</small>
                </article>
                <article class="invoice-detail-metric">
                    <span>Đã thu</span>
                    <strong>{{ number_format($daThu, 0, ',', '.') }}đ</strong>
                    <small>{{ number_format($phieuThuHopLe->count()) }} phiếu thu hợp lệ</small>
                </article>
                <article class="invoice-detail-metric">
                    <span>Còn nợ</span>
                    <strong>{{ number_format($conNo, 0, ',', '.') }}đ</strong>
                    <small>{{ $phanTramThu }}% đã hoàn thành</small>
                </article>
                <article class="invoice-detail-metric">
                    <span>Hạn thanh toán</span>
                    <strong>{{ $hoaDon->ngayHetHan ? \Carbon\Carbon::parse($hoaDon->ngayHetHan)->format('d/m/Y') : 'Không đặt hạn' }}</strong>
                    <small>{{ $hoaDon->tinhTrangHanLabel ?? 'Theo dõi công nợ thủ công' }}</small>
                </article>
            </div>
        </section>

        <div class="invoice-detail-layout">
            <main class="invoice-detail-main">
                @if ($hoaDon->isQuaHan && $hoaDon->trangThai != 2)
                    <section class="invoice-alert invoice-alert--danger">
                        <i class="fas fa-triangle-exclamation"></i>
                        <div>
                            <strong>Hóa đơn đang quá hạn thanh toán.</strong>
                            <span>Với học phí chính, trạng thái này có thể ảnh hưởng trực tiếp đến quyền học của học viên.</span>
                        </div>
                    </section>
                @elseif ($hoaDon->isSapHetHan && $hoaDon->trangThai != 2)
                    <section class="invoice-alert invoice-alert--warning">
                        <i class="fas fa-clock"></i>
                        <div>
                            <strong>Hóa đơn sắp đến hạn thanh toán.</strong>
                            <span>Kiểm tra lại kế hoạch thu và chủ động nhắc học viên trước ngày đến hạn.</span>
                        </div>
                    </section>
                @endif

                <section class="invoice-main-card">
                    <div class="invoice-main-card__header">
                        <h2>Tổng quan nghiệp vụ</h2>
                        <p>Nhìn nhanh học viên, lớp học và ngữ cảnh phát sinh công nợ của hóa đơn này.</p>
                    </div>
                    <div class="invoice-context-grid">
                        <article class="invoice-context-card">
                            <h3>Học viên</h3>
                            <ul>
                                <li><span>Họ tên</span><strong>{{ $hoTen }}</strong></li>
                                <li><span>Email</span><strong>{{ $hoaDon->taiKhoan?->email ?? '—' }}</strong></li>
                                <li><span>Điện thoại</span><strong>{{ $profile->soDienThoai ?? '—' }}</strong></li>
                                <li><span>Tài khoản</span><strong>{{ $hoaDon->taiKhoan?->taiKhoan ?? '—' }}</strong></li>
                            </ul>
                        </article>

                        <article class="invoice-context-card">
                            <h3>Lớp học và đăng ký</h3>
                            <ul>
                                <li><span>Lớp học</span><strong>{{ $hoaDon->dangKyLopHoc?->lopHoc?->tenLopHoc ?? 'Không gắn lớp' }}</strong></li>
                                <li><span>Khóa học</span><strong>{{ $hoaDon->dangKyLopHoc?->lopHoc?->khoaHoc?->tenKhoaHoc ?? '—' }}</strong></li>
                                <li><span>Ngày đăng ký</span><strong>{{ $hoaDon->dangKyLopHoc?->ngayDangKy ? \Carbon\Carbon::parse($hoaDon->dangKyLopHoc->ngayDangKy)->format('d/m/Y') : '—' }}</strong></li>
                                <li><span>Cơ sở</span><strong>{{ $hoaDon->coSo?->tenCoSo ?? '—' }}</strong></li>
                            </ul>
                        </article>

                        <article class="invoice-context-card">
                            <h3>Nguồn thu</h3>
                            <ul>
                                <li><span>Loại hóa đơn</span><strong>{{ $hoaDon->loaiHoaDonLabel }}</strong></li>
                                <li><span>Nguồn thu</span><strong>{{ $hoaDon->nguonThuLabel }}</strong></li>
                                <li>
                                    <span>Khoản thu cụ thể</span>
                                    <strong>
                                        @if ($hoaDon->nguonThu === \App\Models\Finance\HoaDon::NGUON_THU_PHU_PHI)
                                            {{ $hoaDon->dangKyLopHocPhuPhi?->tenKhoanThuSnapshot ?? 'Khoản bổ sung' }}
                                        @else
                                            {{ $hoaDon->lopHocDotThu?->tenDotThu ?? 'Học phí thu một lần' }}
                                        @endif
                                    </strong>
                                </li>
                                <li>
                                    <span>Nhóm phí</span>
                                    <strong>
                                        @if ($hoaDon->nguonThu === \App\Models\Finance\HoaDon::NGUON_THU_PHU_PHI)
                                            {{ $hoaDon->dangKyLopHocPhuPhi?->nhomPhiLabel ?? 'Khoản khác' }}
                                        @else
                                            Học phí chính
                                        @endif
                                    </strong>
                                </li>
                            </ul>
                        </article>
                    </div>
                </section>

                <section class="invoice-main-card">
                    <div class="invoice-main-card__header">
                        <h2>Tiến độ thanh toán</h2>
                        <p>Phiếu thu hợp lệ mới được cộng vào số tiền đã thu. Phiếu đã hủy chỉ lưu để đối soát lịch sử.</p>
                    </div>

                    <div class="invoice-payment-overview">
                        <div class="invoice-payment-progress">
                            <div class="invoice-payment-progress__meta">
                                <span>Tiến độ thu tiền</span>
                                <strong>{{ $phanTramThu }}%</strong>
                            </div>
                            <div class="invoice-payment-progress__bar">
                                <span style="width: {{ $phanTramThu }}%"></span>
                            </div>
                            <div class="invoice-payment-progress__legend">
                                <span>Tổng phải thu {{ number_format($tongPhaiThu, 0, ',', '.') }}đ</span>
                                <span>Đã thu {{ number_format($daThu, 0, ',', '.') }}đ</span>
                            </div>
                        </div>

                        <div class="invoice-summary-grid">
                            <article class="invoice-summary-box">
                                <span>Tổng tiền gốc</span>
                                <strong>{{ number_format((float) $hoaDon->tongTien, 0, ',', '.') }}đ</strong>
                            </article>
                            <article class="invoice-summary-box">
                                <span>Giảm giá</span>
                                <strong>{{ number_format((float) $hoaDon->giamGia, 0, ',', '.') }}đ</strong>
                            </article>
                            <article class="invoice-summary-box">
                                <span>Đã thu hợp lệ</span>
                                <strong class="text-success">{{ number_format($tongPhieuThuHopLe, 0, ',', '.') }}đ</strong>
                            </article>
                            <article class="invoice-summary-box">
                                <span>Phiếu thu đã hủy</span>
                                <strong>{{ number_format($soPhieuDaHuy) }}</strong>
                            </article>
                        </div>
                    </div>
                </section>

                <section class="invoice-main-card">
                    <div class="invoice-main-card__header">
                        <h2>Lịch sử phiếu thu</h2>
                        <p>Toàn bộ giao dịch thu tiền thực tế của hóa đơn này, bao gồm cả phiếu đã hủy để tiện kiểm tra.</p>
                    </div>

                    @if ($hoaDon->phieuThus->isEmpty())
                        <div class="invoice-empty-state invoice-empty-state--compact">
                            <i class="fas fa-receipt"></i>
                            <h3>Chưa có phiếu thu nào</h3>
                            <p>Tạo phiếu thu khi học viên thanh toán để hệ thống cập nhật lại công nợ và trạng thái hóa đơn.</p>
                            @if ($coTheThuTien)
                                <button type="button" class="btn btn-primary" data-panel-trigger="receipt">
                                    <i class="fas fa-plus"></i> Tạo phiếu thu đầu tiên
                                </button>
                            @endif
                        </div>
                    @else
                        <div class="receipt-timeline">
                            @foreach ($hoaDon->phieuThus->sortByDesc('ngayThu')->values() as $phieuThu)
                                @php
                                    $maPhieuThu = $phieuThu->maPhieuThu ?: 'PT-' . str_pad($phieuThu->phieuThuId, 6, '0', STR_PAD_LEFT);
                                    $hopLe = (int) $phieuThu->trangThai === \App\Models\Finance\PhieuThu::TRANG_THAI_HOP_LE;
                                @endphp
                                <article class="receipt-item {{ $hopLe ? '' : 'is-cancelled' }}">
                                    <div class="receipt-item__marker">
                                        <i class="fas {{ $hopLe ? 'fa-check' : 'fa-ban' }}"></i>
                                    </div>
                                    <div class="receipt-item__content">
                                        <div class="receipt-item__top">
                                            <div>
                                                <div class="receipt-item__code-row">
                                                    <strong>{{ $maPhieuThu }}</strong>
                                                    <span class="receipt-item__badge {{ $hopLe ? 'is-valid' : 'is-cancelled' }}">
                                                        {{ $hopLe ? 'Hợp lệ' : 'Đã hủy' }}
                                                    </span>
                                                </div>
                                                <p>
                                                    {{ \Carbon\Carbon::parse($phieuThu->ngayThu)->format('d/m/Y') }}
                                                    · {{ $phieuThu->phuongThucLabel }}
                                                    · Thu bởi {{ $phieuThu->taiKhoan?->hoSoNguoiDung?->hoTen ?? ($phieuThu->taiKhoan?->taiKhoan ?? '—') }}
                                                </p>
                                            </div>
                                            <div class="receipt-item__amount {{ $hopLe ? '' : 'is-cancelled' }}">
                                                {{ $hopLe ? '+' : '' }}{{ number_format((float) $phieuThu->soTien, 0, ',', '.') }}đ
                                            </div>
                                        </div>
                                        @if ($phieuThu->ghiChu)
                                            <div class="receipt-item__note">{{ $phieuThu->ghiChu }}</div>
                                        @endif
                                    </div>
                                    @if ($hopLe && auth()->user()->canDo('tai_chinh', 'sua'))
                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                            onclick="confirmCancelReceipt({{ $phieuThu->phieuThuId }}, '{{ $maPhieuThu }}')">
                                            <i class="fas fa-ban"></i> Hủy phiếu thu
                                        </button>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
            </main>

            <aside class="invoice-detail-sidebar">
                <div class="invoice-sidebar-card">
                    <div class="invoice-sidebar-card__header">
                        <h2>Trung tâm thao tác</h2>
                        <p>Chuyển nhanh giữa xem tóm tắt, cập nhật hóa đơn và lập phiếu thu.</p>
                    </div>

                    <div class="invoice-sidebar-actions">
                        <button type="button" class="btn btn-outline-secondary is-active" data-panel-trigger="summary">
                            <i class="fas fa-compass"></i> Tổng quan
                        </button>
                        @if (auth()->user()->canDo('tai_chinh', 'sua'))
                            <button type="button" class="btn btn-outline-secondary" data-panel-trigger="edit">
                                <i class="fas fa-pen"></i> Sửa hóa đơn
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-panel-trigger="receipt"
                                {{ $conNo <= 0 ? 'disabled' : '' }}>
                                <i class="fas fa-receipt"></i> Thêm phiếu thu
                            </button>
                        @endif
                    </div>

                    <div class="invoice-side-panel is-active" data-panel="summary">
                        <div class="invoice-side-stat-list">
                            <div class="invoice-side-stat">
                                <span>Công nợ còn lại</span>
                                <strong>{{ number_format($conNo, 0, ',', '.') }}đ</strong>
                            </div>
                            <div class="invoice-side-stat">
                                <span>Ngày lập</span>
                                <strong>{{ $hoaDon->ngayLap ? \Carbon\Carbon::parse($hoaDon->ngayLap)->format('d/m/Y') : '—' }}</strong>
                            </div>
                            <div class="invoice-side-stat">
                                <span>Hạn thanh toán</span>
                                <strong>{{ $hoaDon->ngayHetHan ? \Carbon\Carbon::parse($hoaDon->ngayHetHan)->format('d/m/Y') : 'Không đặt hạn' }}</strong>
                            </div>
                            <div class="invoice-side-stat">
                                <span>Người lập</span>
                                <strong>{{ $hoaDon->nguoiLap?->hoSoNguoiDung?->hoTen ?? ($hoaDon->nguoiLap?->taiKhoan ?? 'Hệ thống') }}</strong>
                            </div>
                        </div>

                        <div class="invoice-side-note">
                            <i class="fas fa-lightbulb"></i>
                            <div>
                                @if ($hoaDon->nguonThu === \App\Models\Finance\HoaDon::NGUON_THU_PHU_PHI)
                                    <strong>Khoản bổ sung</strong>
                                    <span>Không khóa học viên nếu chưa thanh toán, nhưng vẫn cần theo dõi công nợ riêng.</span>
                                @else
                                    <strong>Học phí chính</strong>
                                    <span>Công nợ này ảnh hưởng trực tiếp đến trạng thái đăng ký và quyền học của học viên.</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if (auth()->user()->canDo('tai_chinh', 'sua'))
                        <div class="invoice-side-panel" data-panel="edit">
                            <form action="{{ route('admin.hoa-don.update', $hoaDon->hoaDonId) }}" method="POST"
                                class="invoice-side-form">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label class="form-label">Loại hóa đơn</label>
                                    <select name="loaiHoaDon" class="form-select">
                                        <option value="0" @selected((string) $hoaDon->loaiHoaDon === '0')>Đăng ký mới</option>
                                        <option value="1" @selected((string) $hoaDon->loaiHoaDon === '1')>Gia hạn</option>
                                        <option value="2" @selected((string) $hoaDon->loaiHoaDon === '2')>Khác</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Hạn thanh toán</label>
                                    <input type="date" name="ngayHetHan" class="form-control"
                                        value="{{ $hoaDon->ngayHetHan ? \Carbon\Carbon::parse($hoaDon->ngayHetHan)->format('Y-m-d') : '' }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Giảm giá (VNĐ)</label>
                                    <input type="hidden" name="giamGia" value="{{ (int) $hoaDon->giamGia }}">
                                    <input type="text" class="form-control money-input" inputmode="numeric" data-target="giamGia"
                                        value="{{ number_format((int) $hoaDon->giamGia, 0, ',', '.') }}"
                                        placeholder="Nhập số tiền giảm giá">
                                    <div class="form-text">
                                        Không vượt quá {{ number_format((float) $hoaDon->tongTien, 0, ',', '.') }}đ.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Ghi chú nội bộ</label>
                                    <textarea name="ghiChu" class="form-control" rows="4"
                                        placeholder="Mô tả thêm về hóa đơn hoặc lưu ý xử lý thu tiền...">{{ $hoaDon->ghiChu }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save"></i> Lưu cập nhật hóa đơn
                                </button>
                            </form>
                        </div>

                        <div class="invoice-side-panel {{ $errors->any() ? 'is-active' : '' }}" data-panel="receipt">
                            @if ($conNo > 0)
                                <form action="{{ route('admin.hoa-don.phieu-thu.store', $hoaDon->hoaDonId) }}" method="POST"
                                    class="invoice-side-form" id="receiptCreateForm">
                                    @csrf

                                    @if ($errors->any())
                                        <div class="invoice-form-errors">
                                            <i class="fas fa-circle-exclamation"></i>
                                            <div>
                                                <strong>Không thể tạo phiếu thu.</strong>
                                                <ul>
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="receipt-quick-amounts">
                                        <button type="button" class="receipt-quick-amount" data-amount="{{ max(1000, (int) round($conNo * 0.25)) }}">25%</button>
                                        <button type="button" class="receipt-quick-amount" data-amount="{{ max(1000, (int) round($conNo * 0.5)) }}">50%</button>
                                        <button type="button" class="receipt-quick-amount" data-amount="{{ (int) $conNo }}">Thu đủ</button>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Số tiền thu</label>
                                        <input type="hidden" name="soTien" value="{{ (int) $conNo }}">
                                        <input type="text" class="form-control money-input" inputmode="numeric" required
                                            data-target="soTien" data-max="{{ (int) $conNo }}"
                                            value="{{ number_format((int) $conNo, 0, ',', '.') }}">
                                        <div class="form-text">
                                            Công nợ còn lại: {{ number_format($conNo, 0, ',', '.') }}đ.
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Ngày thu</label>
                                        <input type="date" name="ngayThu" class="form-control"
                                            value="{{ old('ngayThu', now()->format('Y-m-d')) }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Phương thức thanh toán</label>
                                        <select name="phuongThucThanhToan" class="form-select" id="receiptPaymentMethod" required>
                                            <option value="1" @selected(old('phuongThucThanhToan', '1') === '1')>Tiền mặt</option>
                                            <option value="2" @selected(old('phuongThucThanhToan') === '2')>Chuyển khoản</option>
                                            <option value="3" @selected(old('phuongThucThanhToan') === '3')>VNPay</option>
                                        </select>
                                    </div>

                                    <div class="invoice-payment-method-note" id="receiptPaymentMethodNote">
                                        <i class="fas fa-circle-info"></i>
                                        <span>Gợi ý: ghi rõ mã giao dịch hoặc nội dung chuyển khoản ở ô ghi chú để tiện đối soát.</span>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Ghi chú phiếu thu</label>
                                        <textarea name="ghiChu" class="form-control" rows="3"
                                            placeholder="Ví dụ: học viên chuyển khoản từ ngân hàng A, nội dung CK 123456...">{{ old('ghiChu') }}</textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-check"></i> Xác nhận tạo phiếu thu
                                    </button>
                                </form>
                            @else
                                <div class="invoice-empty-state invoice-empty-state--compact">
                                    <i class="fas fa-circle-check"></i>
                                    <h3>Hóa đơn đã thu đủ</h3>
                                    <p>Không cần tạo thêm phiếu thu mới. Nếu có sai sót, hãy kiểm tra lịch sử phiếu thu đã lập.</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </aside>
        </div>

        <form id="cancel-receipt-form" method="POST" style="display:none">
            @csrf
            @method('DELETE')
        </form>
    </div>
@endsection

@section('script')
    <script>
        function formatMoneyValue(number) {
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function parseMoneyValue(value) {
            return parseInt(String(value).replace(/[^\d]/g, ''), 10) || 0;
        }

        document.querySelectorAll('.money-input').forEach((input) => {
            const targetName = input.dataset.target;
            const hiddenInput = input.closest('form')?.querySelector(`input[name="${targetName}"][type="hidden"]`);

            const syncMoneyInput = () => {
                let amount = parseMoneyValue(input.value);
                const max = parseInt(input.dataset.max || '0', 10);

                if (max > 0 && amount > max) {
                    amount = max;
                }

                input.value = amount > 0 ? formatMoneyValue(amount) : '';
                if (hiddenInput) {
                    hiddenInput.value = amount;
                }
            };

            input.addEventListener('input', syncMoneyInput);
            input.addEventListener('blur', syncMoneyInput);
            syncMoneyInput();
        });

        document.querySelectorAll('[data-panel-trigger]').forEach((button) => {
            button.addEventListener('click', () => {
                if (button.disabled) {
                    return;
                }

                const panel = button.dataset.panelTrigger;
                document.querySelectorAll('.invoice-sidebar-actions [data-panel-trigger]').forEach((item) => item.classList.remove('is-active'));
                document.querySelectorAll('.invoice-side-panel').forEach((item) => item.classList.remove('is-active'));

                document.querySelector(`.invoice-sidebar-actions [data-panel-trigger="${panel}"]`)?.classList.add('is-active');
                document.querySelector(`.invoice-side-panel[data-panel="${panel}"]`)?.classList.add('is-active');
            });
        });

        document.querySelectorAll('.receipt-quick-amount').forEach((button) => {
            button.addEventListener('click', () => {
                const form = document.getElementById('receiptCreateForm');
                const displayInput = form?.querySelector('.money-input[data-target="soTien"]');
                const hiddenInput = form?.querySelector('input[name="soTien"][type="hidden"]');
                const amount = parseInt(button.dataset.amount || '0', 10);

                if (!displayInput || !hiddenInput || amount <= 0) {
                    return;
                }

                displayInput.value = formatMoneyValue(amount);
                hiddenInput.value = amount;
                displayInput.dispatchEvent(new Event('input'));
            });
        });

        const paymentMethodSelect = document.getElementById('receiptPaymentMethod');
        const paymentMethodNote = document.getElementById('receiptPaymentMethodNote');

        const syncPaymentMethodNote = () => {
            if (!paymentMethodSelect || !paymentMethodNote) {
                return;
            }

            const method = paymentMethodSelect.value;
            let message = 'Gợi ý: ghi rõ mã giao dịch hoặc nội dung chuyển khoản ở ô ghi chú để tiện đối soát.';

            if (method === '1') {
                message = 'Gợi ý: với thu tiền mặt, ghi tên người nộp hoặc mốc thu để dễ đối chiếu cuối ca.';
            } else if (method === '3') {
                message = 'Gợi ý: với VNPay, nên lưu mã giao dịch hoặc ảnh xác nhận ở nghiệp vụ nội bộ nếu cần đối soát.';
            }

            paymentMethodNote.querySelector('span').textContent = message;
        };

        paymentMethodSelect?.addEventListener('change', syncPaymentMethodNote);
        syncPaymentMethodNote();

        @if ($defaultPanel !== 'summary')
            document.querySelector(`.invoice-sidebar-actions [data-panel-trigger="{{ $defaultPanel }}"]`)?.click();
        @endif

        function confirmCancelReceipt(id, code) {
            Swal.fire({
                title: 'Hủy phiếu thu?',
                html: `Phiếu thu <strong>${code}</strong> sẽ bị chuyển sang trạng thái đã hủy và hệ thống trừ lại số tiền khỏi hóa đơn.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-ban me-1"></i> Xác nhận hủy',
                cancelButtonText: 'Đóng',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                const form = document.getElementById('cancel-receipt-form');
                form.action = `{{ url('/admin/hoa-don/phieu-thu') }}/${id}`;
                form.submit();
            });
        }
    </script>
@endsection
