@extends('layouts.client')
@section('title', 'Chi tiết hóa đơn ' . ($invoice->maHoaDon ?: 'HD-' . str_pad($invoice->hoaDonId, 6, '0',
    STR_PAD_LEFT)))

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/account.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/invoices.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/breadcrumb.css') }}">
@endsection

@section('content')
    @php
        $maHD = $invoice->maHoaDon ?: 'HD-' . str_pad($invoice->hoaDonId, 6, '0', STR_PAD_LEFT);
        $tongThucThu = $invoice->tongTien - ($invoice->giamGia ?? 0);
        $conNo = max(0, $tongThucThu - $invoice->daTra);
        $pctPaid = $tongThucThu > 0 ? min(100, round(($invoice->daTra / $tongThucThu) * 100)) : 100;
        $isQuaHan = $invoice->isQuaHan;
        $isSapHHan = $invoice->isSapHetHan;
        $phieuThusHL = $invoice->phieuThus?->where('trangThai', 1) ?? collect();
    @endphp

    <section class="account-page">
        <div class="custom-container">
            <div class="row">
                @include('components.client.account-sidebar')

                <div class="col-lg-9">
                    <div class="account-content">
                        {{-- Breadcrumb --}}
                        <x-client.account-breadcrumb :items="[
                            ['label' => 'Trang chủ', 'url' => route('home.index'), 'icon' => 'fas fa-home'],
                            ['label' => 'Tài khoản', 'url' => route('home.student.index')],
                            ['label' => 'Học phí', 'url' => route('home.student.tuition.debts')],
                            ['label' => $maHD],
                        ]" />

                        {{-- ── HERO HEADER ─────────────────────────────────────── --}}
                        <div
                            class="inv-detail-hero {{ $isQuaHan ? 'inv-detail-hero--danger' : ($isSapHHan ? 'inv-detail-hero--warn' : ($invoice->trangThai == 2 ? 'inv-detail-hero--success' : 'inv-detail-hero--default')) }}">
                            <div class="inv-detail-hero__left">
                                <div class="inv-detail-hero__icon">
                                    @if ($invoice->trangThai == 2)
                                        <i class="fas fa-check-circle"></i>
                                    @elseif ($isQuaHan)
                                        <i class="fas fa-exclamation-circle"></i>
                                    @else
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    @endif
                                </div>
                                <div>
                                    <div class="inv-detail-hero__label">Hóa đơn thanh toán</div>
                                    <div class="inv-detail-hero__code">{{ $maHD }}</div>
                                    <div class="inv-detail-hero__badges">
                                        @if ($invoice->trangThai == 0)
                                            <span class="inv-badge inv-badge--unpaid">Chưa thanh toán</span>
                                        @elseif ($invoice->trangThai == 1)
                                            <span class="inv-badge inv-badge--partial">Thanh toán một phần</span>
                                        @else
                                            <span class="inv-badge inv-badge--paid"><i class="fas fa-check-circle"></i> Đã
                                                thanh toán đủ</span>
                                        @endif

                                        @if ($isQuaHan)
                                            <span class="inv-badge inv-badge--danger"><i class="fas fa-ban"></i> Quá hạn
                                                thanh toán</span>
                                        @elseif ($isSapHHan)
                                            <span class="inv-badge inv-badge--warn"><i class="fas fa-clock"></i>
                                                {{ $invoice->tinhTrangHanLabel }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-2 justify-content-end">
                                <a href="{{ route('home.student.invoices.print', $invoice->hoaDonId) }}" target="_blank"
                                    class="inv-btn inv-btn--outline">
                                    <i class="fas fa-print"></i> In hóa đơn
                                </a>
                                <button type="button" class="inv-btn inv-btn--outline"
                                    onclick="studentRequestDocumentEmail('{{ route('home.student.invoices.email', $invoice->hoaDonId) }}', '{{ auth()->user()->email ?? '' }}', 'hóa đơn {{ $maHD }}')">
                                    <i class="fas fa-envelope"></i> Gửi email
                                </button>
                                <a href="{{ route('home.student.tuition.debts') }}" class="inv-btn inv-btn--ghost">
                                    <i class="fas fa-arrow-left"></i> Quay lại
                                </a>
                            </div>
                        </div>
                        <form id="student-document-email-form" method="POST" style="display:none;">
                            @csrf
                            <input type="hidden" name="email" id="student-document-email-input">
                            <input type="hidden" name="message" id="student-document-email-message">
                        </form>

                        {{-- ── CẢNH BÁO QUÁ HẠN ──────────────────────────────── --}}
                        @if ($isQuaHan)
                            <div class="inv-alert inv-alert--danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div>
                                    <strong>Hóa đơn đã quá hạn thanh toán!</strong>
                                    <span>Hạn thanh toán:
                                        <strong>{{ \Carbon\Carbon::parse($invoice->ngayHetHan)->format('d/m/Y') }}</strong>.
                                        @if ($invoice->nguonThu === \App\Models\Finance\HoaDon::NGUON_THU_HOC_PHI)
                                            Việc đăng ký lớp học có thể bị tạm dừng cho đến khi bạn thanh toán đủ.
                                        @else
                                            Đây là khoản bổ sung riêng, không làm khóa trạng thái học của bạn.
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @elseif ($isSapHHan && $invoice->trangThai != 2)
                            <div class="inv-alert inv-alert--warn">
                                <i class="fas fa-clock"></i>
                                <div>
                                    <strong>Sắp đến hạn thanh toán!</strong>
                                    <span>Hạn thanh toán:
                                        <strong>{{ \Carbon\Carbon::parse($invoice->ngayHetHan)->format('d/m/Y') }}</strong>
                                        ({{ $invoice->tinhTrangHanLabel }}). Vui lòng thanh toán đúng hạn.</span>
                                </div>
                            </div>
                        @endif

                        {{-- ── PROGRESS THANH TOÁN ────────────────────────────── --}}
                        <div class="inv-progress-card">
                            <div class="inv-progress-card__top">
                                <div class="inv-progress-card__amounts">
                                    <div class="inv-progress-card__amount-item">
                                        <span
                                            class="inv-progress-card__amount-num">{{ number_format($invoice->tongTien, 0, ',', '.') }}đ</span>
                                        <span class="inv-progress-card__amount-lbl">Tổng tiền</span>
                                    </div>
                                    @if ($invoice->giamGia > 0)
                                        <div class="inv-progress-card__sep">→</div>
                                        <div
                                            class="inv-progress-card__amount-item inv-progress-card__amount-item--discount">
                                            <span
                                                class="inv-progress-card__amount-num">-{{ number_format($invoice->giamGia, 0, ',', '.') }}đ</span>
                                            <span class="inv-progress-card__amount-lbl">Giảm giá</span>
                                        </div>
                                        <div class="inv-progress-card__sep">→</div>
                                        <div class="inv-progress-card__amount-item">
                                            <span
                                                class="inv-progress-card__amount-num">{{ number_format($tongThucThu, 0, ',', '.') }}đ</span>
                                            <span class="inv-progress-card__amount-lbl">Thực thu</span>
                                        </div>
                                    @endif
                                    <div class="inv-progress-card__sep">·</div>
                                    <div class="inv-progress-card__amount-item inv-progress-card__amount-item--paid">
                                        <span
                                            class="inv-progress-card__amount-num">{{ number_format($invoice->daTra, 0, ',', '.') }}đ</span>
                                        <span class="inv-progress-card__amount-lbl">Đã trả</span>
                                    </div>
                                    <div class="inv-progress-card__sep">·</div>
                                    <div
                                        class="inv-progress-card__amount-item {{ $conNo > 0 ? 'inv-progress-card__amount-item--debt' : 'inv-progress-card__amount-item--clear' }}">
                                        <span
                                            class="inv-progress-card__amount-num">{{ number_format($conNo, 0, ',', '.') }}đ</span>
                                        <span
                                            class="inv-progress-card__amount-lbl">{{ $conNo > 0 ? 'Còn nợ' : 'Đã xong' }}</span>
                                    </div>
                                </div>
                                <div
                                    class="inv-progress-card__pct {{ $pctPaid == 100 ? 'inv-progress-card__pct--full' : '' }}">
                                    {{ $pctPaid }}%
                                </div>
                            </div>
                            <div class="inv-progress-bar">
                                <div class="inv-progress-bar__fill {{ $pctPaid == 100 ? 'inv-progress-bar__fill--full' : '' }}"
                                    style="width: {{ $pctPaid }}%"></div>
                            </div>
                        </div>

                        {{-- ── 2 CỘT: THÔNG TIN LỚP + CHI TIẾT HĐ ─────────────── --}}
                        <div class="inv-detail-grid">

                            {{-- 1. Thông tin lớp học --}}
                            <div class="inv-detail-section">
                                <div class="inv-detail-section__head">
                                    <i class="fas fa-graduation-cap"></i>
                                    <span>Thông tin lớp học</span>
                                </div>
                                <div class="inv-detail-section__body">
                                    <div class="inv-info-row">
                                        <span class="inv-info-row__label">Tên lớp học</span>
                                        <span class="inv-info-row__val inv-info-row__val--bold">
                                            {{ $invoice->dangKyLopHoc?->lopHoc?->tenLopHoc ?? '—' }}
                                        </span>
                                    </div>
                                    <div class="inv-info-row">
                                        <span class="inv-info-row__label">Khóa học</span>
                                        <span class="inv-info-row__val">
                                            {{ $invoice->dangKyLopHoc?->lopHoc?->khoaHoc?->tenKhoaHoc ?? '—' }}
                                        </span>
                                    </div>
                                    <div class="inv-info-row">
                                        <span class="inv-info-row__label">Cơ sở</span>
                                        <span class="inv-info-row__val">{{ $invoice->coSo?->tenCoSo ?? '—' }}</span>
                                    </div>
                                    @if ($invoice->coSo?->diaChi)
                                        <div class="inv-info-row">
                                            <span class="inv-info-row__label">Địa chỉ</span>
                                            <span class="inv-info-row__val">
                                                {{ $invoice->coSo->diaChi }}{{ $invoice->coSo->tinhThanh?->tenTinhThanh ? ', ' . $invoice->coSo->tinhThanh->tenTinhThanh : '' }}
                                            </span>
                                        </div>
                                    @endif
                                    <div class="inv-info-row">
                                        <span class="inv-info-row__label">Ngày đăng ký</span>
                                        <span class="inv-info-row__val">
                                            {{ $invoice->dangKyLopHoc?->ngayDangKy ? \Carbon\Carbon::parse($invoice->dangKyLopHoc->ngayDangKy)->format('d/m/Y') : '—' }}
                                        </span>
                                    </div>
                                    @php
                                        $dkTrangThai = $invoice->dangKyLopHoc?->trangThai;
                                    @endphp
                                    <div class="inv-info-row">
                                        <span class="inv-info-row__label">Trạng thái lớp</span>
                                        <span class="inv-info-row__val">
                                            @if ($dkTrangThai == 1)
                                                <span class="inv-badge inv-badge--paid">Đang học</span>
                                            @elseif ($dkTrangThai == 2)
                                                <span class="inv-badge inv-badge--danger">⏸ Tạm dừng – Nợ học phí</span>
                                            @elseif ($dkTrangThai == 3)
                                                <span class="inv-badge inv-badge--unpaid">Đã hủy</span>
                                            @else
                                                <span class="inv-badge inv-badge--partial">Chờ xác nhận</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- 2. Chi tiết hóa đơn --}}
                            <div class="inv-detail-section">
                                <div class="inv-detail-section__head">
                                    <i class="fas fa-file-alt"></i>
                                    <span>Chi tiết hóa đơn</span>
                                </div>
                                <div class="inv-detail-section__body">
                                    <div class="inv-info-row">
                                        <span class="inv-info-row__label">Mã hóa đơn</span>
                                        <span class="inv-info-row__val">
                                            <code class="inv-code">{{ $maHD }}</code>
                                        </span>
                                    </div>
                                    <div class="inv-info-row">
                                        <span class="inv-info-row__label">Loại hóa đơn</span>
                                        <span
                                            class="inv-info-row__val">{{ $invoice->loaiHoaDonLabel ?? 'Đăng ký mới' }}</span>
                                    </div>
                                    <div class="inv-info-row">
                                        <span class="inv-info-row__label">Nguồn thu</span>
                                        <span class="inv-info-row__val">{{ $invoice->nguonThuLabel }}</span>
                                    </div>
                                    @if ($invoice->nguonThu === \App\Models\Finance\HoaDon::NGUON_THU_PHU_PHI && $invoice->dangKyLopHocPhuPhi)
                                        <div class="inv-info-row">
                                            <span class="inv-info-row__label">Khoản bổ sung</span>
                                            <span class="inv-info-row__val">{{ $invoice->dangKyLopHocPhuPhi->tenKhoanThuSnapshot }}</span>
                                        </div>
                                        <div class="inv-info-row">
                                            <span class="inv-info-row__label">Nhóm phí</span>
                                            <span class="inv-info-row__val">{{ $invoice->dangKyLopHocPhuPhi->nhomPhiLabel }}</span>
                                        </div>
                                    @endif
                                    <div class="inv-info-row">
                                        <span class="inv-info-row__label">Ngày lập</span>
                                        <span class="inv-info-row__val">
                                            {{ $invoice->ngayLap ? \Carbon\Carbon::parse($invoice->ngayLap)->format('d/m/Y') : '—' }}
                                        </span>
                                    </div>
                                    @if ($invoice->ngayHetHan)
                                        <div class="inv-info-row">
                                            <span class="inv-info-row__label">Hạn thanh toán</span>
                                            <span
                                                class="inv-info-row__val {{ $isQuaHan ? 'text-danger fw-bold' : ($isSapHHan ? 'text-warning-dark fw-bold' : '') }}">
                                                {{ \Carbon\Carbon::parse($invoice->ngayHetHan)->format('d/m/Y') }}
                                                @if ($isQuaHan)
                                                    <span class="inv-badge inv-badge--danger ms-1">Quá hạn</span>
                                                @elseif ($isSapHHan)
                                                    <span
                                                        class="inv-badge inv-badge--warn ms-1">{{ $invoice->tinhTrangHanLabel }}</span>
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                    <div class="inv-info-row">
                                        <span class="inv-info-row__label">Phương thức TT</span>
                                        <span class="inv-info-row__val">
                                            @if ($invoice->phuongThucThanhToan == 1)
                                                <i class="fas fa-money-bill-wave text-success me-1"></i>Tiền mặt
                                            @elseif ($invoice->phuongThucThanhToan == 2)
                                                <i class="fas fa-university text-primary me-1"></i>Chuyển khoản
                                            @elseif ($invoice->phuongThucThanhToan == 3)
                                                <i class="fas fa-qrcode text-info me-1"></i>VNPay
                                            @else
                                                —
                                            @endif
                                        </span>
                                    </div>
                                    @if ($invoice->ghiChu)
                                        <div class="inv-info-row">
                                            <span class="inv-info-row__label">Ghi chú</span>
                                            <span class="inv-info-row__val">{{ $invoice->ghiChu }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- ── LỊCH SỬ THANH TOÁN ─────────────────────────────── --}}
                        <div class="inv-detail-section inv-detail-section--full mt-4">
                            <div class="inv-detail-section__head">
                                <i class="fas fa-history"></i>
                                <span>Lịch sử thanh toán</span>
                                <span class="inv-detail-section__badge">{{ $phieuThusHL->count() }} phiếu thu</span>
                            </div>
                            <div class="inv-detail-section__body">
                                @if ($phieuThusHL->count() > 0)
                                    <div class="inv-timeline">
                                        @foreach ($phieuThusHL->sortByDesc('ngayThu') as $phieu)
                                            <div class="inv-timeline__item">
                                                <div class="inv-timeline__dot"></div>
                                                <div class="inv-timeline__content">
                                                    <div class="inv-timeline__header">
                                                        <div>
                                                            <span
                                                                class="inv-timeline__code">{{ $phieu->maPhieuThu ?? 'PT-' . str_pad($phieu->phieuThuId, 6, '0', STR_PAD_LEFT) }}</span>
                                                            <span class="inv-timeline__method">
                                                                @if ($phieu->phuongThucThanhToan == 1)
                                                                    <i class="fas fa-money-bill-wave"></i> Tiền mặt
                                                                @elseif ($phieu->phuongThucThanhToan == 2)
                                                                    <i class="fas fa-university"></i> Chuyển khoản
                                                                @else
                                                                    <i class="fas fa-qrcode"></i> VNPay
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <span
                                                            class="inv-timeline__amount">+{{ number_format($phieu->soTien, 0, ',', '.') }}đ</span>
                                                    </div>
                                                    <div class="inv-timeline__date">
                                                        <i class="fas fa-calendar-check"></i>
                                                        {{ \Carbon\Carbon::parse($phieu->ngayThu)->format('d/m/Y') }}
                                                    </div>
                                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                                        <a href="{{ route('home.student.tuition.receipts.print', $phieu->phieuThuId) }}"
                                                            target="_blank" class="inv-btn inv-btn--outline">
                                                            <i class="fas fa-print"></i> In phiếu thu
                                                        </a>
                                                        <button type="button" class="inv-btn inv-btn--outline"
                                                            onclick="studentRequestDocumentEmail('{{ route('home.student.tuition.receipts.email', $phieu->phieuThuId) }}', '{{ auth()->user()->email ?? '' }}', 'phiếu thu {{ $phieu->maPhieuThu ?? 'PT-' . str_pad($phieu->phieuThuId, 6, '0', STR_PAD_LEFT) }}')">
                                                            <i class="fas fa-envelope"></i> Gửi email
                                                        </button>
                                                    </div>
                                                    @if ($phieu->ghiChu)
                                                        <div class="inv-timeline__note">{{ $phieu->ghiChu }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="inv-empty-small">
                                        <i class="fas fa-inbox"></i>
                                        <span>Chưa có lịch sử thanh toán</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- ── HƯỚNG DẪN CHUYỂN KHOẢN ─────────────────────────── --}}
                        @if ($conNo > 0)
                            <div class="inv-detail-section inv-detail-section--full mt-4">
                                <div class="inv-detail-section__head">
                                    <i class="fas fa-university"></i>
                                    <span>Thông tin thanh toán</span>
                                </div>
                                <div class="inv-detail-section__body">
                                    <div class="inv-payment-guide">
                                        <div class="inv-payment-guide__row">
                                            <span class="inv-payment-guide__label">Số tiền cần thanh toán</span>
                                            <span class="inv-payment-guide__val inv-payment-guide__val--highlight">
                                                {{ number_format($conNo, 0, ',', '.') }}đ
                                            </span>
                                        </div>
                                        <div class="inv-payment-guide__divider"></div>
                                        <div class="inv-payment-guide__row">
                                            <span class="inv-payment-guide__label">Ngân hàng</span>
                                            <span class="inv-payment-guide__val">Vietcombank</span>
                                        </div>
                                        <div class="inv-payment-guide__row">
                                            <span class="inv-payment-guide__label">Số tài khoản</span>
                                            <div class="inv-payment-guide__copy-row">
                                                <span class="inv-payment-guide__val inv-payment-guide__val--mono"
                                                    id="accNum">1234567890</span>
                                                <button class="inv-copy-btn" onclick="invCopy('accNum', this)"
                                                    title="Sao chép">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="inv-payment-guide__row">
                                            <span class="inv-payment-guide__label">Tên tài khoản</span>
                                            <span class="inv-payment-guide__val">TRUNG TAM NGOAI NGU</span>
                                        </div>
                                        <div class="inv-payment-guide__row">
                                            <span class="inv-payment-guide__label">Nội dung chuyển khoản</span>
                                            @php
                                                $content =
                                                    $maHD .
                                                    ' ' .
                                                    (auth()->user()->hoSoNguoiDung->hoTen ?? auth()->user()->email);
                                            @endphp
                                            <div class="inv-payment-guide__copy-row">
                                                <span
                                                    class="inv-payment-guide__val inv-payment-guide__val--mono inv-payment-guide__val--important"
                                                    id="transContent">
                                                    {{ $content }}
                                                </span>
                                                <button class="inv-copy-btn" onclick="invCopy('transContent', this)"
                                                    title="Sao chép">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="inv-payment-guide__notice">
                                            <i class="fas fa-info-circle"></i>
                                            Vui lòng chuyển khoản <strong>đúng nội dung</strong> để hệ thống xác nhận thanh
                                            toán.
                                            Liên hệ trung tâm nếu cần hỗ trợ.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        function invCopy(elId, btn) {
            const text = document.getElementById(elId).innerText.trim();
            navigator.clipboard.writeText(text).then(() => {
                const icon = btn.querySelector('i');
                icon.className = 'fas fa-check';
                btn.style.color = '#16a34a';
                setTimeout(() => {
                    icon.className = 'fas fa-copy';
                    btn.style.color = '';
                }, 2000);
            });
        }

        function studentSubmitDocumentEmail(action, email, message) {
            const form = document.getElementById('student-document-email-form');
            document.getElementById('student-document-email-input').value = email;
            document.getElementById('student-document-email-message').value = message || '';
            form.action = action;
            form.submit();
        }

        function studentRequestDocumentEmail(action, defaultEmail, label) {
            const email = window.prompt(`Nhập email để gửi ${label}:`, defaultEmail || '');
            if (!email) {
                return;
            }

            const message = window.prompt('Ghi chú email (không bắt buộc):', '') || '';
            studentSubmitDocumentEmail(action, email.trim(), message.trim());
        }
    </script>
@endsection
