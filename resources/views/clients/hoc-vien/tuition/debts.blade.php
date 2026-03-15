@extends('layouts.client')
@section('title', 'Tra cứu công nợ')

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
                            ['label' => 'Tra cứu công nợ'],
                        ]" />
                        @if ($debts->count() > 0)
                            <div class="tuition-ledger">
                                <div class="tuition-ledger__head">
                                    <span>Hóa đơn</span>
                                    <span>Lớp học</span>
                                    <span>Mốc thời gian</span>
                                    <span>Số tiền</span>
                                    <span>Trạng thái</span>
                                    <span></span>
                                </div>

                                <div class="tuition-ledger__body">
                                    @foreach ($debts as $invoice)
                                        @php
                                            $netAmount = $invoice->tongTien - ($invoice->giamGia ?? 0);
                                            $outstanding = max(0, $netAmount - $invoice->daTra);
                                            $pctPaid = $netAmount > 0 ? min(100, round(($invoice->daTra / $netAmount) * 100)) : 100;
                                            $code = $invoice->maHoaDon ?: 'HD-' . str_pad($invoice->hoaDonId, 6, '0', STR_PAD_LEFT);
                                            $course = $invoice->dangKyLopHoc?->lopHoc?->khoaHoc?->tenKhoaHoc ?? 'Khoản thu ngoài lớp';
                                        @endphp

                                        <article class="tuition-row {{ $invoice->isQuaHan ? 'is-overdue' : ($invoice->isSapHetHan ? 'is-warning' : '') }}">
                                            <div class="tuition-row__cell">
                                                <span class="tuition-row__label">Hóa đơn</span>
                                                <div class="tuition-row__code">{{ $code }}</div>
                                                <div class="tuition-row__meta">{{ $invoice->nguonThuLabel }}</div>
                                            </div>

                                            <div class="tuition-row__cell">
                                                <span class="tuition-row__label">Lớp học</span>
                                                <div class="tuition-row__title">{{ $invoice->dangKyLopHoc?->lopHoc?->tenLopHoc ?? 'Khoản thu bổ sung' }}</div>
                                                <div class="tuition-row__meta">{{ $course }}</div>
                                                <div class="tuition-row__meta"><i class="fas fa-map-marker-alt"></i> {{ $invoice->coSo?->tenCoSo ?? 'Chưa gán cơ sở' }}</div>
                                            </div>

                                            <div class="tuition-row__cell">
                                                <span class="tuition-row__label">Mốc thời gian</span>
                                                <div class="tuition-row__meta"><strong>Ngày lập:</strong> {{ $invoice->ngayLap ? \Carbon\Carbon::parse($invoice->ngayLap)->format('d/m/Y') : '—' }}</div>
                                                <div class="tuition-row__meta">
                                                    <strong>Hạn thanh toán:</strong>
                                                    {{ $invoice->ngayHetHan ? \Carbon\Carbon::parse($invoice->ngayHetHan)->format('d/m/Y') : 'Không giới hạn' }}
                                                </div>
                                            </div>

                                            <div class="tuition-row__cell">
                                                <span class="tuition-row__label">Số tiền</span>
                                                <div class="tuition-money">
                                                    <div class="tuition-money__line">
                                                        <span>Tổng thu</span>
                                                        <strong>{{ number_format($netAmount, 0, ',', '.') }}đ</strong>
                                                    </div>
                                                    <div class="tuition-money__line">
                                                        <span>Đã trả</span>
                                                        <strong class="is-paid">{{ number_format($invoice->daTra, 0, ',', '.') }}đ</strong>
                                                    </div>
                                                    <div class="tuition-money__line">
                                                        <span>Còn nợ</span>
                                                        <strong class="{{ $outstanding > 0 ? 'is-debt' : 'is-clear' }}">{{ number_format($outstanding, 0, ',', '.') }}đ</strong>
                                                    </div>
                                                </div>
                                                <div class="tuition-progress">
                                                    <div class="tuition-progress__bar">
                                                        <div class="tuition-progress__fill {{ $pctPaid === 100 ? 'is-full' : '' }}" style="width: {{ $pctPaid }}%"></div>
                                                    </div>
                                                    <span class="tuition-progress__text">Đã thanh toán {{ $pctPaid }}%</span>
                                                </div>
                                            </div>

                                            <div class="tuition-row__cell">
                                                <span class="tuition-row__label">Trạng thái</span>
                                                <div class="tuition-statuses">
                                                    @if ($invoice->trangThai == 0)
                                                        <span class="inv-badge inv-badge--unpaid">Chưa thanh toán</span>
                                                    @elseif ($invoice->trangThai == 1)
                                                        <span class="inv-badge inv-badge--partial">Thanh toán một phần</span>
                                                    @else
                                                        <span class="inv-badge inv-badge--paid"><i class="fas fa-check-circle"></i> Đã thanh toán đủ</span>
                                                    @endif

                                                    @if ($invoice->isQuaHan)
                                                        <span class="inv-badge inv-badge--danger"><i class="fas fa-ban"></i> Quá hạn</span>
                                                    @elseif ($invoice->isSapHetHan)
                                                        <span class="inv-badge inv-badge--warn"><i class="fas fa-clock"></i> {{ $invoice->tinhTrangHanLabel }}</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="tuition-row__cell tuition-row__cell--actions">
                                                <a href="{{ route('home.student.tuition.invoices.show', $invoice->hoaDonId) }}" class="inv-btn inv-btn--outline">
                                                    <i class="fas fa-eye"></i> Chi tiết
                                                </a>
                                                @if ($invoice->trangThai !== 2)
                                                    <a href="{{ route('home.student.tuition.payments') }}" class="inv-btn inv-btn--pay">
                                                        <i class="fas fa-credit-card"></i> Thanh toán
                                                    </a>
                                                @endif
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-4">
                                {{ $debts->links() }}
                            </div>
                        @else
                            <div class="inv-empty">
                                <div class="inv-empty__icon">
                                    <i class="fas fa-wallet"></i>
                                </div>
                                <h4 class="inv-empty__title">Bạn chưa có công nợ nào</h4>
                                <p class="inv-empty__sub">Khi phát sinh học phí hoặc khoản bổ sung, danh sách công nợ sẽ hiển thị tại đây.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
