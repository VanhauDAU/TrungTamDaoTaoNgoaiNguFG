@extends('layouts.client')
@section('title', 'Hóa đơn thanh toán')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/client/css/accountInfo/account.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/invoices.css') }}">
@endsection

@section('content')
    <section class="account-page">
        <div class="custom-container">
            <div class="row">
                @include('components.client.account-sidebar')

                <div class="col-lg-9">
                    <div class="account-content">
                        <div class="content-header">
                            <h2 class="page-title">
                                <i class="fas fa-file-invoice me-2"></i>Hóa đơn thanh toán
                            </h2>
                            <p class="page-subtitle">Quản lý và theo dõi hóa đơn thanh toán của bạn</p>
                        </div>

                        @if ($invoices->count() > 0)
                            <div class="invoices-grid">
                                @foreach ($invoices as $invoice)
                                    <div class="invoice-card">
                                        <div class="invoice-header">
                                            <div class="invoice-id">
                                                <i class="fas fa-hashtag"></i>
                                                <span>HD-{{ str_pad($invoice->hoaDonId, 6, '0', STR_PAD_LEFT) }}</span>
                                            </div>
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

                                        <div class="invoice-body">
                                            <h4 class="class-name">
                                                {{ $invoice->dangKyLopHoc->lopHoc->tenLopHoc }}
                                            </h4>
                                            <p class="course-name text-muted">
                                                {{ $invoice->dangKyLopHoc->lopHoc->khoaHoc->tenKhoaHoc }}
                                            </p>

                                            <div class="invoice-details">
                                                <div class="detail-row">
                                                    <span class="label">
                                                        <i class="far fa-calendar-alt"></i> Ngày lập:
                                                    </span>
                                                    <span
                                                        class="value">{{ \Carbon\Carbon::parse($invoice->ngayLap)->format('d/m/Y') }}</span>
                                                </div>
                                                <div class="detail-row">
                                                    <span class="label">
                                                        <i class="fas fa-map-marker-alt"></i> Cơ sở:
                                                    </span>
                                                    <span class="value">{{ $invoice->coSo->tenCoSo }}</span>
                                                </div>
                                                <div class="detail-row">
                                                    <span class="label">
                                                        <i class="fas fa-credit-card"></i> Phương thức:
                                                    </span>
                                                    <span class="value">
                                                        @if ($invoice->phuongThucThanhToan == 1)
                                                            Tiền mặt
                                                        @elseif($invoice->phuongThucThanhToan == 2)
                                                            Chuyển khoản
                                                        @else
                                                            VNPay
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="invoice-amount">
                                                <div class="amount-row">
                                                    <span>Tổng tiền:</span>
                                                    <strong>{{ number_format($invoice->tongTien, 0, ',', '.') }}đ</strong>
                                                </div>
                                                <div class="amount-row">
                                                    <span>Đã trả:</span>
                                                    <span
                                                        class="text-success">{{ number_format($invoice->daTra, 0, ',', '.') }}đ</span>
                                                </div>
                                                @if ($invoice->tongTien - $invoice->daTra > 0)
                                                    <div class="amount-row debt-row">
                                                        <span>Còn nợ:</span>
                                                        <span
                                                            class="text-danger fw-bold">{{ number_format($invoice->tongTien - $invoice->daTra, 0, ',', '.') }}đ</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="invoice-footer d-flex gap-2 mt-3">
                                            <a href="{{ route('home.student.invoices.show', $invoice->hoaDonId) }}"
                                                class="btn btn-detail flex-fill text-nowrap">
                                                <i class="fas fa-eye me-2"></i>Xem chi tiết
                                            </a>
                                            @if ($invoice->trangThai !== 2)
                                                <a href="#" class="btn btn-pay flex-fill text-nowrap">
                                                    <i class="fas fa-credit-card me-2"></i>Thanh toán
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4">
                                {{ $invoices->links() }}
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-file-invoice fa-4x"></i>
                                <h4>Bạn chưa có hóa đơn nào</h4>
                                <p>Các hóa đơn sẽ được tạo sau khi bạn đăng ký lớp học</p>
                                <a href="{{ route('home.courses.index') }}" class="btn btn-primary">
                                    <i class="fas fa-book me-1"></i> Xem khóa học
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
