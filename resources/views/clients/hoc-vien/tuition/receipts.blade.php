@extends('layouts.client')
@section('title', 'Phiếu thu tổng hợp')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/account.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/invoices.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/breadcrumb.css') }}">
@endsection

@section('content')
    <section class="account-page">
        <div class="custom-container">
            <div class="row">
                @include('components.client.account-sidebar')

                <div class="col-lg-9">
                    <div class="account-content">
                        <x-client.account-breadcrumb :items="[
                            ['label' => 'Trang chủ', 'url' => route('home.index'), 'icon' => 'fas fa-home'],
                            ['label' => 'Tài khoản', 'url' => route('home.student.index')],
                            ['label' => 'Phiếu thu tổng hợp'],
                        ]" />
                        @if ($receipts->count() > 0)
                            <div class="tuition-ledger">
                                <div class="tuition-ledger__head tuition-ledger__head--receipts">
                                    <span>Phiếu thu</span>
                                    <span>Liên kết hóa đơn</span>
                                    <span>Lớp học</span>
                                    <span>Ngày thu</span>
                                    <span>Phương thức</span>
                                    <span>Số tiền</span>
                                </div>

                                <div class="tuition-ledger__body">
                                    @foreach ($receipts as $receipt)
                                        @php
                                            $receiptCode = $receipt->maPhieuThu ?: 'PT-' . str_pad($receipt->phieuThuId, 6, '0', STR_PAD_LEFT);
                                            $invoice = $receipt->hoaDon;
                                            $invoiceCode = $invoice?->maHoaDon ?: ($invoice ? 'HD-' . str_pad($invoice->hoaDonId, 6, '0', STR_PAD_LEFT) : '—');
                                        @endphp

                                        <article class="tuition-row tuition-row--receipt">
                                            <div class="tuition-row__cell">
                                                <span class="tuition-row__label">Phiếu thu</span>
                                                <div class="tuition-row__code">{{ $receiptCode }}</div>
                                                <div class="tuition-row__meta">{{ $receipt->ghiChu ?: 'Giao dịch đã ghi nhận' }}</div>
                                            </div>

                                            <div class="tuition-row__cell">
                                                <span class="tuition-row__label">Liên kết hóa đơn</span>
                                                <div class="tuition-row__title">{{ $invoiceCode }}</div>
                                                <div class="tuition-row__meta">{{ $invoice?->nguonThuLabel ?? '—' }}</div>
                                            </div>

                                            <div class="tuition-row__cell">
                                                <span class="tuition-row__label">Lớp học</span>
                                                <div class="tuition-row__title">{{ $invoice?->dangKyLopHoc?->lopHoc?->tenLopHoc ?? 'Khoản bổ sung' }}</div>
                                                <div class="tuition-row__meta">{{ $invoice?->dangKyLopHoc?->lopHoc?->khoaHoc?->tenKhoaHoc ?? '—' }}</div>
                                            </div>

                                            <div class="tuition-row__cell">
                                                <span class="tuition-row__label">Ngày thu</span>
                                                <div class="tuition-row__title">{{ $receipt->ngayThu ? \Carbon\Carbon::parse($receipt->ngayThu)->format('d/m/Y') : '—' }}</div>
                                                <div class="tuition-row__meta">
                                                    Ghi nhận bởi {{ $receipt->nguoiDuyet?->hoSoNguoiDung?->hoTen ?? ($receipt->nguoiDuyet?->taiKhoan ?? 'Trung tâm') }}
                                                </div>
                                            </div>

                                            <div class="tuition-row__cell">
                                                <span class="tuition-row__label">Phương thức</span>
                                                <div class="tuition-statuses">
                                                    <span class="inv-badge {{ $receipt->phuongThucThanhToan == 1 ? 'inv-badge--paid' : ($receipt->phuongThucThanhToan == 2 ? 'inv-badge--partial' : 'inv-badge--warn') }}">
                                                        {{ $receipt->phuongThucLabel }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="tuition-row__cell tuition-row__cell--amount-only">
                                                <span class="tuition-row__label">Số tiền</span>
                                                <div class="tuition-row__amount-main">{{ number_format($receipt->soTien, 0, ',', '.') }}đ</div>
                                                <div class="d-flex flex-wrap gap-2 mt-2">
                                                    @if ($invoice)
                                                        <a href="{{ route('home.student.tuition.invoices.show', $invoice->hoaDonId) }}" class="tuition-inline-link">
                                                            Xem hóa đơn liên quan
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-4">
                                {{ $receipts->links() }}
                            </div>
                        @else
                            <div class="inv-empty">
                                <div class="inv-empty__icon">
                                    <i class="fas fa-receipt"></i>
                                </div>
                                <h4 class="inv-empty__title">Chưa có phiếu thu nào</h4>
                                <p class="inv-empty__sub">Khi trung tâm ghi nhận thanh toán hợp lệ, phiếu thu sẽ xuất hiện tại đây để bạn đối soát.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
