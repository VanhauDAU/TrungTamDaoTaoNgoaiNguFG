@extends('layouts.client')
@section('title', 'Thanh toán trực tuyến')

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
                            ['label' => 'Thanh toán trực tuyến'],
                        ]" />

                        <div class="tuition-hero tuition-hero--payments">
                            <div>
                                <span class="tuition-hero__eyebrow">Thanh toan</span>
                                <h2 class="tuition-hero__title">Thanh toán trực tuyến</h2>
                                <p class="tuition-hero__sub">Tập trung các khoản cần thanh toán, thông tin chuyển khoản và kênh thanh toán đang được hỗ trợ cho học viên.</p>
                            </div>
                            <div class="tuition-hero__stats">
                                <div class="tuition-stat tuition-stat--warn">
                                    <span class="tuition-stat__num">{{ $summary['count'] }}</span>
                                    <span class="tuition-stat__label">Khoản cần thanh toán</span>
                                </div>
                                <div class="tuition-stat tuition-stat--danger">
                                    <span class="tuition-stat__num">{{ number_format($summary['outstandingTotal'], 0, ',', '.') }}đ</span>
                                    <span class="tuition-stat__label">Tổng còn nợ</span>
                                </div>
                                <div class="tuition-stat tuition-stat--danger">
                                    <span class="tuition-stat__num">{{ $summary['overdueCount'] }}</span>
                                    <span class="tuition-stat__label">Quá hạn</span>
                                </div>
                                <div class="tuition-stat">
                                    <span class="tuition-stat__num">{{ $summary['dueSoonCount'] }}</span>
                                    <span class="tuition-stat__label">Sắp đến hạn</span>
                                </div>
                            </div>
                        </div>

                        @include('clients.hoc-vien.tuition.partials.nav', ['active' => 'payments'])

                        <div class="tuition-method-grid">
                            <section class="tuition-method-card is-primary">
                                <div class="tuition-method-card__icon"><i class="fas fa-university"></i></div>
                                <div>
                                    <h3>Chuyển khoản</h3>
                                    <p>Kênh đang dùng thực tế. Hệ thống dẫn bạn vào chi tiết hóa đơn để lấy số tiền, số tài khoản và nội dung chuyển khoản chuẩn.</p>
                                </div>
                            </section>
                            <section class="tuition-method-card">
                                <div class="tuition-method-card__icon"><i class="fas fa-money-bill-wave"></i></div>
                                <div>
                                    <h3>Thu tại cơ sở</h3>
                                    <p>Phù hợp với học viên muốn đóng trực tiếp tại quầy. Sau khi thu, trung tâm sẽ ghi nhận phiếu thu và cập nhật công nợ.</p>
                                </div>
                            </section>
                            <section class="tuition-method-card is-muted">
                                <div class="tuition-method-card__icon"><i class="fas fa-qrcode"></i></div>
                                <div>
                                    <h3>VNPay</h3>
                                    <p>Schema đã hỗ trợ phương thức thanh toán này, nhưng cổng thanh toán tự động vẫn đang ở pha chuẩn bị kích hoạt.</p>
                                </div>
                            </section>
                        </div>

                        @if ($payments->count() > 0)
                            <div class="tuition-ledger">
                                <div class="tuition-ledger__head">
                                    <span>Khoản cần thanh toán</span>
                                    <span>Lớp học</span>
                                    <span>Hạn thanh toán</span>
                                    <span>Còn phải thu</span>
                                    <span>Trạng thái</span>
                                    <span></span>
                                </div>

                                <div class="tuition-ledger__body">
                                    @foreach ($payments as $invoice)
                                        @php
                                            $code = $invoice->maHoaDon ?: 'HD-' . str_pad($invoice->hoaDonId, 6, '0', STR_PAD_LEFT);
                                            $netAmount = $invoice->tongTien - ($invoice->giamGia ?? 0);
                                            $outstanding = max(0, $netAmount - $invoice->daTra);
                                        @endphp

                                        <article class="tuition-row {{ $invoice->isQuaHan ? 'is-overdue' : ($invoice->isSapHetHan ? 'is-warning' : '') }}">
                                            <div class="tuition-row__cell">
                                                <span class="tuition-row__label">Khoản cần thanh toán</span>
                                                <div class="tuition-row__code">{{ $code }}</div>
                                                <div class="tuition-row__meta">{{ $invoice->nguonThuLabel }}</div>
                                            </div>

                                            <div class="tuition-row__cell">
                                                <span class="tuition-row__label">Lớp học</span>
                                                <div class="tuition-row__title">{{ $invoice->dangKyLopHoc?->lopHoc?->tenLopHoc ?? 'Khoản thu bổ sung' }}</div>
                                                <div class="tuition-row__meta">{{ $invoice->dangKyLopHoc?->lopHoc?->khoaHoc?->tenKhoaHoc ?? '—' }}</div>
                                            </div>

                                            <div class="tuition-row__cell">
                                                <span class="tuition-row__label">Hạn thanh toán</span>
                                                <div class="tuition-row__title">{{ $invoice->ngayHetHan ? \Carbon\Carbon::parse($invoice->ngayHetHan)->format('d/m/Y') : 'Không giới hạn' }}</div>
                                                <div class="tuition-row__meta">
                                                    @if ($invoice->isQuaHan)
                                                        Đã quá hạn thanh toán
                                                    @elseif ($invoice->isSapHetHan)
                                                        {{ $invoice->tinhTrangHanLabel }}
                                                    @else
                                                        Thanh toán theo hướng dẫn trong hóa đơn
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="tuition-row__cell tuition-row__cell--amount-only">
                                                <span class="tuition-row__label">Còn phải thu</span>
                                                <div class="tuition-row__amount-main is-debt">{{ number_format($outstanding, 0, ',', '.') }}đ</div>
                                                <div class="tuition-row__meta">Đã trả {{ number_format($invoice->daTra, 0, ',', '.') }}đ</div>
                                            </div>

                                            <div class="tuition-row__cell">
                                                <span class="tuition-row__label">Trạng thái</span>
                                                <div class="tuition-statuses">
                                                    @if ($invoice->trangThai == 0)
                                                        <span class="inv-badge inv-badge--unpaid">Chưa thanh toán</span>
                                                    @else
                                                        <span class="inv-badge inv-badge--partial">Thanh toán một phần</span>
                                                    @endif

                                                    @if ($invoice->isQuaHan)
                                                        <span class="inv-badge inv-badge--danger"><i class="fas fa-ban"></i> Quá hạn</span>
                                                    @elseif ($invoice->isSapHetHan)
                                                        <span class="inv-badge inv-badge--warn"><i class="fas fa-clock"></i> {{ $invoice->tinhTrangHanLabel }}</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="tuition-row__cell tuition-row__cell--actions">
                                                <a href="{{ route('home.student.tuition.invoices.show', $invoice->hoaDonId) }}" class="inv-btn inv-btn--pay">
                                                    <i class="fas fa-arrow-right"></i> Mở hướng dẫn thanh toán
                                                </a>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-4">
                                {{ $payments->links() }}
                            </div>
                        @else
                            <div class="inv-empty">
                                <div class="inv-empty__icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <h4 class="inv-empty__title">Hiện không có khoản nào cần thanh toán</h4>
                                <p class="inv-empty__sub">Tất cả hóa đơn của bạn đang ở trạng thái đã thanh toán đủ hoặc chưa phát sinh nghĩa vụ thu mới.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
