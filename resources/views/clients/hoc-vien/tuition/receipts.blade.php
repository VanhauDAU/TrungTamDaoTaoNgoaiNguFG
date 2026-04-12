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

                                <div class="tuition-ledger__meta-note">
                                    Danh sách được sắp theo thời gian thu, phiếu thu mới nhất hiển thị ở trên cùng.
                                </div>

                                <div class="tuition-ledger__body">
                                    @foreach ($receipts as $receipt)
                                        @php
                                            $receiptCode = $receipt->maPhieuThu ?: 'PT-' . str_pad($receipt->phieuThuId, 6, '0', STR_PAD_LEFT);
                                            $invoice = $receipt->hoaDon;
                                            $invoiceCode = $invoice?->maHoaDon ?: ($invoice ? 'HD-' . str_pad($invoice->hoaDonId, 6, '0', STR_PAD_LEFT) : '—');
                                            $printUrl = route('home.student.tuition.receipts.print', $receipt->phieuThuId);
                                            $downloadUrl = route('home.student.tuition.receipts.download', $receipt->phieuThuId);
                                            $receiptDetailPayload = [
                                                'id' => $receipt->phieuThuId,
                                                'code' => $receiptCode,
                                                'invoiceCode' => $invoiceCode,
                                                'invoiceUrl' => $invoice ? route('home.student.tuition.invoices.show', $invoice->hoaDonId) : null,
                                                'className' => $invoice?->dangKyLopHoc?->lopHoc?->tenLopHoc ?? 'Khoản bổ sung',
                                                'courseName' => $invoice?->dangKyLopHoc?->lopHoc?->khoaHoc?->tenKhoaHoc ?? '—',
                                                'campus' => $invoice?->coSo?->tenCoSo ?? '—',
                                                'paymentMethod' => $receipt->phuongThucLabel,
                                                'amount' => number_format((float) $receipt->soTien, 0, ',', '.') . 'đ',
                                                'date' => $receipt->ngayThu ? \Carbon\Carbon::parse($receipt->ngayThu)->format('d/m/Y') : '—',
                                                'note' => $receipt->ghiChu ?: 'Không có ghi chú.',
                                                'collector' => $receipt->nguoiDuyet?->hoSoNguoiDung?->hoTen ?? ($receipt->nguoiDuyet?->taiKhoan ?? 'Trung tâm'),
                                                'sourceLabel' => $invoice?->nguonThuLabel ?? '—',
                                                'printUrl' => $printUrl,
                                                'downloadUrl' => $downloadUrl,
                                            ];
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
                                                <div class="tuition-row__actions mt-2">
                                                    <button type="button"
                                                        class="tuition-action-icon"
                                                        data-receipt-detail='@json($receiptDetailPayload)'
                                                        title="Xem chi tiết phiếu thu"
                                                        aria-label="Xem chi tiết phiếu thu">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="{{ $downloadUrl }}"
                                                        class="tuition-action-icon"
                                                        title="Tải xuống phiếu thu"
                                                        aria-label="Tải xuống phiếu thu">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    @if ($invoice)
                                                        <a href="{{ route('home.student.tuition.invoices.show', $invoice->hoaDonId) }}"
                                                            class="tuition-action-icon"
                                                            title="Xem hóa đơn liên kết"
                                                            aria-label="Xem hóa đơn liên kết">
                                                            <i class="fas fa-file-invoice"></i>
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

    <div class="modal fade" id="receiptDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content receipt-modal">
                <div class="modal-header">
                    <div>
                        <div class="receipt-modal__eyebrow">Chi tiết phiếu thu</div>
                        <h5 class="modal-title" id="receiptModalTitle">Phiếu thu</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="receipt-modal__amount" id="receiptModalAmount">0đ</div>
                    <div class="receipt-modal__grid">
                        <div class="receipt-modal__item">
                            <span>Mã hóa đơn</span>
                            <strong id="receiptModalInvoiceCode">—</strong>
                        </div>
                        <div class="receipt-modal__item">
                            <span>Ngày thu</span>
                            <strong id="receiptModalDate">—</strong>
                        </div>
                        <div class="receipt-modal__item">
                            <span>Phương thức</span>
                            <strong id="receiptModalMethod">—</strong>
                        </div>
                        <div class="receipt-modal__item">
                            <span>Người ghi nhận</span>
                            <strong id="receiptModalCollector">—</strong>
                        </div>
                        <div class="receipt-modal__item">
                            <span>Lớp học</span>
                            <strong id="receiptModalClass">—</strong>
                        </div>
                        <div class="receipt-modal__item">
                            <span>Khóa học</span>
                            <strong id="receiptModalCourse">—</strong>
                        </div>
                        <div class="receipt-modal__item">
                            <span>Cơ sở</span>
                            <strong id="receiptModalCampus">—</strong>
                        </div>
                        <div class="receipt-modal__item">
                            <span>Nguồn thu</span>
                            <strong id="receiptModalSource">—</strong>
                        </div>
                    </div>

                    <div class="receipt-modal__note">
                        <span>Ghi chú</span>
                        <div id="receiptModalNote">Không có ghi chú.</div>
                    </div>
                </div>
                <div class="modal-footer receipt-modal__footer">
                    <a href="#" class="tuition-inline-link" id="receiptModalInvoiceLink" target="_self" style="display:none;">
                        Xem hóa đơn
                    </a>
                    <a href="#" class="tuition-inline-link" id="receiptModalDownloadLink">
                        Tải xuống
                    </a>
                    <a href="#" class="btn btn-primary" id="receiptModalPrintLink" target="_blank" rel="noopener">
                        <i class="fas fa-print me-1"></i> In phiếu thu
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        (() => {
            const modalElement = document.getElementById('receiptDetailModal');
            if (!modalElement || typeof bootstrap === 'undefined') {
                return;
            }

            const modal = new bootstrap.Modal(modalElement);
            const title = document.getElementById('receiptModalTitle');
            const amount = document.getElementById('receiptModalAmount');
            const invoiceCode = document.getElementById('receiptModalInvoiceCode');
            const date = document.getElementById('receiptModalDate');
            const method = document.getElementById('receiptModalMethod');
            const collector = document.getElementById('receiptModalCollector');
            const className = document.getElementById('receiptModalClass');
            const courseName = document.getElementById('receiptModalCourse');
            const campus = document.getElementById('receiptModalCampus');
            const source = document.getElementById('receiptModalSource');
            const note = document.getElementById('receiptModalNote');
            const invoiceLink = document.getElementById('receiptModalInvoiceLink');
            const downloadLink = document.getElementById('receiptModalDownloadLink');
            const printLink = document.getElementById('receiptModalPrintLink');

            document.querySelectorAll('[data-receipt-detail]').forEach((button) => {
                button.addEventListener('click', () => {
                    const payload = JSON.parse(button.dataset.receiptDetail || '{}');

                    title.textContent = payload.code || 'Phiếu thu';
                    amount.textContent = payload.amount || '0đ';
                    invoiceCode.textContent = payload.invoiceCode || '—';
                    date.textContent = payload.date || '—';
                    method.textContent = payload.paymentMethod || '—';
                    collector.textContent = payload.collector || '—';
                    className.textContent = payload.className || '—';
                    courseName.textContent = payload.courseName || '—';
                    campus.textContent = payload.campus || '—';
                    source.textContent = payload.sourceLabel || '—';
                    note.textContent = payload.note || 'Không có ghi chú.';

                    if (payload.invoiceUrl) {
                        invoiceLink.href = payload.invoiceUrl;
                        invoiceLink.style.display = 'inline-flex';
                    } else {
                        invoiceLink.removeAttribute('href');
                        invoiceLink.style.display = 'none';
                    }

                    downloadLink.href = payload.downloadUrl || '#';
                    printLink.href = payload.printUrl || '#';

                    modal.show();
                });
            });
        })();
    </script>
@endsection
