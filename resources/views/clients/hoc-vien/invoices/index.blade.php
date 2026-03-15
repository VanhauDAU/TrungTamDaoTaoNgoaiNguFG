@extends('layouts.client')
@section('title', 'Hóa đơn thanh toán')

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
                        {{-- Breadcrumb --}}
                        <x-client.account-breadcrumb :items="[
                            ['label' => 'Trang chủ', 'url' => route('home.index'), 'icon' => 'fas fa-home'],
                            ['label' => 'Tài khoản', 'url' => route('home.student.index')],
                            ['label' => 'Hóa đơn thanh toán'],
                        ]" />

                        {{-- Page Header --}}
                        <div class="inv-page-header">
                            <div class="inv-page-header__left">
                                <div class="inv-page-header__icon">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <div>
                                    <h2 class="inv-page-header__title">Hóa đơn thanh toán</h2>
                                    <p class="inv-page-header__sub">Quản lý và theo dõi toàn bộ hóa đơn của bạn</p>
                                </div>
                            </div>
                            <div class="inv-page-header__stats">
                                <div class="inv-stat">
                                    <span class="inv-stat__num">{{ $invoices->total() }}</span>
                                    <span class="inv-stat__label">Tổng HĐ</span>
                                </div>
                                <div class="inv-stat inv-stat--warn">
                                    <span class="inv-stat__num">
                                        {{ $invoices->getCollection()->filter(fn($i) => $i->trangThai != 2)->count() }}
                                    </span>
                                    <span class="inv-stat__label">Chưa TT đủ</span>
                                </div>
                            </div>
                        </div>

                        @if ($invoices->count() > 0)
                            <div class="inv-list">
                                @foreach ($invoices as $invoice)
                                    @php
                                        $tongThucThu = $invoice->tongTien - ($invoice->giamGia ?? 0);
                                        $conNo = max(0, $tongThucThu - $invoice->daTra);
                                        $pctPaid =
                                            $tongThucThu > 0
                                                ? min(100, round(($invoice->daTra / $tongThucThu) * 100))
                                                : 100;
                                        $maHD =
                                            $invoice->maHoaDon ?:
                                            'HD-' . str_pad($invoice->hoaDonId, 6, '0', STR_PAD_LEFT);

                                        $isQuaHan = $invoice->isQuaHan;
                                        $isSapHHan = $invoice->isSapHetHan;
                                    @endphp

                                    <div
                                        class="inv-card {{ $isQuaHan ? 'inv-card--overdue' : ($isSapHHan ? 'inv-card--warning' : '') }}">
                                        {{-- Card Header --}}
                                        <div class="inv-card__head">
                                            <div class="inv-card__code">
                                                <i class="fas fa-receipt"></i>
                                                <span>{{ $maHD }}</span>
                                            </div>
                                            <div class="inv-card__badges">
                                                @if ($isQuaHan)
                                                    <span class="inv-badge inv-badge--danger">
                                                        <i class="fas fa-ban"></i> Quá hạn TT
                                                    </span>
                                                @elseif ($isSapHHan)
                                                    <span class="inv-badge inv-badge--warn">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        {{ $invoice->tinhTrangHanLabel }}
                                                    </span>
                                                @endif

                                                @if ($invoice->trangThai == 0)
                                                    <span class="inv-badge inv-badge--unpaid">Chưa thanh toán</span>
                                                @elseif ($invoice->trangThai == 1)
                                                    <span class="inv-badge inv-badge--partial">Thanh toán một phần</span>
                                                @else
                                                    <span class="inv-badge inv-badge--paid">
                                                        <i class="fas fa-check-circle"></i> Đã thanh toán đủ
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Card Body --}}
                                        <div class="inv-card__body">
                                            <div class="inv-card__class-info">
                                                <div class="inv-card__class-name">
                                                    {{ $invoice->dangKyLopHoc?->lopHoc?->tenLopHoc ?? '—' }}
                                                </div>
                                                <div class="inv-card__course-name">
                                                    <i class="fas fa-book-open"></i>
                                                    {{ $invoice->dangKyLopHoc?->lopHoc?->khoaHoc?->tenKhoaHoc ?? '—' }}
                                                </div>
                                                <div class="inv-card__course-name">
                                                    <i class="fas fa-tag"></i>
                                                    {{ $invoice->nguonThuLabel }}
                                                    @if ($invoice->nguonThu === \App\Models\Finance\HoaDon::NGUON_THU_PHU_PHI)
                                                        · {{ $invoice->dangKyLopHocPhuPhi?->tenKhoanThuSnapshot ?? 'Khoản bổ sung' }}
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="inv-card__meta">
                                                <div class="inv-card__meta-item">
                                                    <i class="fas fa-calendar-alt"></i>
                                                    <span>Ngày lập:
                                                        {{ $invoice->ngayLap ? \Carbon\Carbon::parse($invoice->ngayLap)->format('d/m/Y') : '—' }}</span>
                                                </div>
                                                @if ($invoice->ngayHetHan)
                                                    <div
                                                        class="inv-card__meta-item {{ $isQuaHan ? 'text-danger' : ($isSapHHan ? 'text-warning-dark' : '') }}">
                                                        <i class="fas fa-clock"></i>
                                                        <span>Hạn TT:
                                                            {{ \Carbon\Carbon::parse($invoice->ngayHetHan)->format('d/m/Y') }}</span>
                                                    </div>
                                                @endif
                                                <div class="inv-card__meta-item">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <span>{{ $invoice->coSo?->tenCoSo ?? '—' }}</span>
                                                </div>
                                            </div>

                                            {{-- Progress Bar --}}
                                            <div class="inv-card__progress-wrap">
                                                <div class="inv-card__progress-bar">
                                                    <div class="inv-card__progress-fill {{ $pctPaid == 100 ? 'inv-card__progress-fill--full' : '' }}"
                                                        style="width: {{ $pctPaid }}%"></div>
                                                </div>
                                                <div class="inv-card__progress-label">
                                                    <span>Đã trả {{ $pctPaid }}%</span>
                                                    <span>{{ number_format($invoice->daTra, 0, ',', '.') }}đ /
                                                        {{ number_format($tongThucThu, 0, ',', '.') }}đ</span>
                                                </div>
                                            </div>

                                            {{-- Amount Summary --}}
                                            <div class="inv-card__amounts">
                                                <div class="inv-card__amount-cell">
                                                    <div class="inv-card__amount-val">
                                                        {{ number_format($invoice->tongTien, 0, ',', '.') }}đ</div>
                                                    <div class="inv-card__amount-lbl">Tổng tiền</div>
                                                </div>
                                                @if ($invoice->giamGia > 0)
                                                    <div class="inv-card__amount-cell inv-card__amount-cell--discount">
                                                        <div class="inv-card__amount-val">
                                                            -{{ number_format($invoice->giamGia, 0, ',', '.') }}đ</div>
                                                        <div class="inv-card__amount-lbl">Giảm giá</div>
                                                    </div>
                                                @endif
                                                <div class="inv-card__amount-cell inv-card__amount-cell--paid">
                                                    <div class="inv-card__amount-val">
                                                        {{ number_format($invoice->daTra, 0, ',', '.') }}đ</div>
                                                    <div class="inv-card__amount-lbl">Đã trả</div>
                                                </div>
                                                <div
                                                    class="inv-card__amount-cell {{ $conNo > 0 ? 'inv-card__amount-cell--debt' : 'inv-card__amount-cell--zero' }}">
                                                    <div class="inv-card__amount-val">
                                                        {{ number_format($conNo, 0, ',', '.') }}đ</div>
                                                    <div class="inv-card__amount-lbl">Còn nợ</div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Card Footer --}}
                                        <div class="inv-card__foot">
                                            <a href="{{ route('home.student.invoices.show', $invoice->hoaDonId) }}"
                                                class="inv-btn inv-btn--outline">
                                                <i class="fas fa-eye"></i> Xem chi tiết
                                            </a>
                                            @if ($invoice->trangThai !== 2)
                                                <a href="{{ route('home.student.invoices.show', $invoice->hoaDonId) }}"
                                                    class="inv-btn inv-btn--pay">
                                                    <i class="fas fa-credit-card"></i> Thanh toán
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
                            <div class="inv-empty">
                                <div class="inv-empty__icon">
                                    <i class="fas fa-file-invoice"></i>
                                </div>
                                <h4 class="inv-empty__title">Bạn chưa có hóa đơn nào</h4>
                                <p class="inv-empty__sub">Hóa đơn sẽ được tạo sau khi bạn đăng ký lớp học thành công</p>
                                <a href="{{ route('home.courses.index') }}" class="inv-btn inv-btn--pay">
                                    <i class="fas fa-book-open"></i> Khám phá khóa học
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
