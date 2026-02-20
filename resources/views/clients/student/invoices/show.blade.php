@extends('layouts.client')
@section('title', 'Chi tiết hóa đơn #' . str_pad($invoice->hoaDonId, 6, '0', STR_PAD_LEFT))

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/invoices.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/account.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/breadcrumb.css') }}">
@endsection

@section('content')
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
                            ['label' => 'Hóa đơn thanh toán', 'url' => route('home.student.invoices')],
                            ['label' => 'Chi tiết hóa đơn #' . str_pad($invoice->hoaDonId, 6, '0', STR_PAD_LEFT)],
                        ]" />

                        {{-- Header --}}
                        <div class="content-header mb-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <div>
                                    <h2 class="page-title mb-2">
                                        <i class="fas fa-file-invoice me-2"></i>Chi tiết hóa đơn
                                        #{{ str_pad($invoice->hoaDonId, 6, '0', STR_PAD_LEFT) }}
                                    </h2>
                                    @php
                                        $statusClass = '';
                                        $statusText = '';
                                        if ($invoice->trangThai == 0) {
                                            $statusClass = 'status-unpaid';
                                            $statusText = 'Chưa thanh toán';
                                        } elseif ($invoice->trangThai == 1) {
                                            $statusClass = 'status-partial';
                                            $statusText = 'Đã thanh toán một phần';
                                        } else {
                                            $statusClass = 'status-paid';
                                            $statusText = 'Đã thanh toán đủ';
                                        }
                                    @endphp
                                    <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                                </div>
                                <a href="{{ route('home.student.invoices') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                                </a>
                            </div>
                        </div>

                        {{-- Top Row: Class Info + Invoice Details --}}
                        <div class="row g-4 mb-4">
                            {{-- Left: Class Information --}}
                            <div class="col-lg-6">
                                <div class="detail-section h-100">
                                    <h4 class="section-title">
                                        <i class="fas fa-graduation-cap me-2"></i>Thông tin lớp học
                                    </h4>
                                    <div class="class-info-details">
                                        <div class="info-item-row">
                                            <span class="info-label">Tên lớp học:</span>
                                            <span
                                                class="info-value fw-bold">{{ $invoice->dangKyLopHoc->lopHoc->tenLopHoc }}</span>
                                        </div>
                                        <div class="info-item-row">
                                            <span class="info-label">Khóa học:</span>
                                            <span
                                                class="info-value">{{ $invoice->dangKyLopHoc->lopHoc->khoaHoc->tenKhoaHoc }}</span>
                                        </div>
                                        <div class="info-item-row">
                                            <span class="info-label">Cơ sở:</span>
                                            <span class="info-value">{{ $invoice->coSo->tenCoSo }}</span>
                                        </div>
                                        <div class="info-item-row">
                                            <span class="info-label">Địa chỉ:</span>
                                            <span class="info-value">{{ $invoice->coSo->diaChi }},
                                                {{ $invoice->coSo->tinhThanh->tenTinhThanh ?? '' }}</span>
                                        </div>
                                        <div class="info-item-row">
                                            <span class="info-label">Ngày đăng ký:</span>
                                            <span
                                                class="info-value">{{ \Carbon\Carbon::parse($invoice->dangKyLopHoc->ngayDangKy)->format('d/m/Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Right: Invoice Details --}}
                            <div class="col-lg-6">
                                <div class="detail-section h-100">
                                    <h4 class="section-title">
                                        <i class="fas fa-file-alt me-2"></i>Chi tiết hóa đơn
                                    </h4>
                                    <div class="class-info-details">
                                        <div class="info-item-row">
                                            <span class="info-label">Mã hóa đơn:</span>
                                            <span
                                                class="info-value">HD-{{ str_pad($invoice->hoaDonId, 6, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                        <div class="info-item-row">
                                            <span class="info-label">Ngày lập:</span>
                                            <span
                                                class="info-value">{{ \Carbon\Carbon::parse($invoice->created_at)->format('d/m/Y H:i') }}</span>
                                        </div>
                                        <div class="info-item-row">
                                            <span class="info-label">Phương thức thanh toán:</span>
                                            <span class="info-value">
                                                @if ($invoice->phuongThucThanhToan == 1)
                                                    <i class="fas fa-money-bill-wave text-success"></i> Tiền mặt
                                                @elseif($invoice->phuongThucThanhToan == 2)
                                                    <i class="fas fa-university text-primary"></i> Chuyển khoản
                                                @else
                                                    <i class="fas fa-qrcode text-info"></i> VNPay
                                                @endif
                                            </span>
                                        </div>
                                        <div class="info-item-row">
                                            <span class="info-label">Tổng tiền:</span>
                                            <span
                                                class="info-value fw-bold text-danger">{{ number_format($invoice->tongTien, 0, ',', '.') }}đ</span>
                                        </div>
                                        @if ($invoice->ghiChu)
                                            <div class="info-item-row">
                                                <span class="info-label">Ghi chú:</span>
                                                <span class="info-value">{{ $invoice->ghiChu }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Bottom Row: Payment History + Payment Summary --}}
                        <div class="row g-4">
                            {{-- Left: Payment History --}}
                            <div class="col-lg-6">
                                <div class="detail-section h-100">
                                    <h4 class="section-title">
                                        <i class="fas fa-history me-2"></i>Lịch sử thanh toán
                                    </h4>
                                    @if ($invoice->phieuThus && $invoice->phieuThus->count() > 0)
                                        <div class="payment-history-timeline">
                                            @foreach ($invoice->phieuThus as $phieu)
                                                <div class="payment-history-item">
                                                    <div class="payment-date">
                                                        <i class="fas fa-calendar-check text-success"></i>
                                                        {{ \Carbon\Carbon::parse($phieu->ngayThu)->format('d/m/Y') }}
                                                    </div>
                                                    <div class="payment-amount">
                                                        <span
                                                            class="amount">+{{ number_format($phieu->soTien, 0, ',', '.') }}đ</span>
                                                    </div>
                                                    @if ($phieu->ghiChu)
                                                        <div class="payment-note">{{ $phieu->ghiChu }}</div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="empty-payment-history">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Chưa có lịch sử thanh toán</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Right: Payment Summary --}}
                            <div class="col-lg-6">
                                <div class="detail-section payment-summary h-100">
                                    <h4 class="section-title">
                                        <i class="fas fa-calculator me-2"></i>Tổng kết thanh toán
                                    </h4>
                                    <div class="summary-table">
                                        <div class="summary-row">
                                            <span class="label">Tổng tiền:</span>
                                            <span
                                                class="value">{{ number_format($invoice->tongTien, 0, ',', '.') }}đ</span>
                                        </div>
                                        <div class="summary-row">
                                            <span class="label">Đã trả:</span>
                                            <span
                                                class="value text-success">{{ number_format($invoice->daTra, 0, ',', '.') }}đ</span>
                                        </div>
                                        <div class="summary-row total-row">
                                            <span class="label">Còn nợ:</span>
                                            <span
                                                class="value {{ $invoice->tongTien - $invoice->daTra > 0 ? 'text-danger' : 'text-success' }}">
                                                <strong>{{ number_format($invoice->tongTien - $invoice->daTra, 0, ',', '.') }}đ</strong>
                                            </span>
                                        </div>
                                    </div>

                                    @if ($invoice->phuongThucThanhToan == 2 && $invoice->tongTien - $invoice->daTra > 0)
                                        <div class="transfer-info-box mt-4">
                                            <div class="alert alert-info mb-3">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Vui lòng chuyển khoản với nội dung chính xác
                                            </div>
                                            <div class="bank-info-compact">
                                                <div class="bank-row-compact">
                                                    <span class="bank-label">Ngân hàng:</span>
                                                    <span class="bank-value">Vietcombank</span>
                                                </div>
                                                <div class="bank-row-compact">
                                                    <span class="bank-label">Số TK:</span>
                                                    <span class="bank-value">1234567890</span>
                                                    <button class="btn-copy-small"
                                                        onclick="copyToClipboard('1234567890')">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </div>
                                                <div class="bank-row-compact">
                                                    <span class="bank-label">Tên TK:</span>
                                                    <span class="bank-value">TRUNG TAM NGOAI NGU</span>
                                                </div>
                                                <div class="bank-row-compact highlight-compact">
                                                    <span class="bank-label">Nội dung:</span>
                                                    @php
                                                        $transferContent =
                                                            'HD' .
                                                            str_pad($invoice->hoaDonId, 6, '0', STR_PAD_LEFT) .
                                                            ' ' .
                                                            auth()->user()->hoSoNguoiDung->hoTen;
                                                    @endphp
                                                    <span class="bank-value fw-bold">{{ $transferContent }}</span>
                                                    <button class="btn-copy-small"
                                                        onclick="copyToClipboard('{{ $transferContent }}')">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Đã sao chép: ' + text);
            }, function(err) {
                console.error('Không thể sao chép:', err);
            });
        }
    </script>
@endsection
