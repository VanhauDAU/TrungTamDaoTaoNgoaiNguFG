@extends('layouts.admin')

@section('title', 'Chi tiết hóa đơn ' . ($hoaDon->maHoaDon ?: 'HD-' . str_pad($hoaDon->hoaDonId, 6, '0', STR_PAD_LEFT)))
@section('page-title', 'Tài chính')
@section('breadcrumb', 'Quản lý tài chính · Chi tiết hóa đơn')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/hoa-don/show.css') }}">
@endsection

@php
    $maHD = $hoaDon->maHoaDon ?: ('HD-' . str_pad($hoaDon->hoaDonId, 6, '0', STR_PAD_LEFT));
    $profile = $hoaDon->taiKhoan?->hoSoNguoiDung;
    $hoTen = $profile->hoTen ?? ($hoaDon->taiKhoan->taiKhoan ?? '—');
    $conNo = max(0, $hoaDon->tongTien - $hoaDon->giamGia - $hoaDon->daTra);
@endphp

@section('content')

    {{-- ── Page Header ───────────────────────────────────────────── --}}
    <div class="hd-detail-header">
        <div>
            <h1 class="hd-detail-title">
                <i class="fas fa-file-invoice-dollar me-2"></i>Chi tiết hóa đơn
                <span class="hd-detail-code">{{ $maHD }}</span>
            </h1>
            <div class="hd-detail-meta">
                @if ($hoaDon->trangThai == 0)
                    <span class="badge-status badge-unpaid"><i class="fas fa-circle" style="font-size:.45em"></i> Chưa thanh
                        toán</span>
                @elseif($hoaDon->trangThai == 1)
                    <span class="badge-status badge-partial"><i class="fas fa-circle" style="font-size:.45em"></i> Thanh toán
                        một phần</span>
                @else
                    <span class="badge-status badge-paid"><i class="fas fa-circle" style="font-size:.45em"></i> Đã thanh toán
                        đủ</span>
                @endif
                <span class="hd-detail-date">
                    <i class="far fa-calendar-alt me-1"></i>
                    {{ $hoaDon->ngayLap ? \Carbon\Carbon::parse($hoaDon->ngayLap)->format('d/m/Y') : '—' }}
                </span>
            </div>
        </div>
        <a href="{{ route('admin.hoa-don.index') }}" class="btn-back">
            <i class="fas fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    {{-- ── Info Cards Row ─────────────────────────────────────────── --}}
    <div class="hd-info-grid">
        {{-- Card: Học viên --}}
        <div class="hd-info-card">
            <h4 class="hd-info-card-title"><i class="fas fa-user-graduate me-2"></i>Thông tin học viên</h4>
            <div class="hd-info-rows">
                <div class="hd-info-row">
                    <span class="hd-info-label">Họ tên:</span>
                    <span class="hd-info-value fw-bold">{{ $hoTen }}</span>
                </div>
                <div class="hd-info-row">
                    <span class="hd-info-label">Email:</span>
                    <span class="hd-info-value">{{ $hoaDon->taiKhoan->email ?? '—' }}</span>
                </div>
                <div class="hd-info-row">
                    <span class="hd-info-label">Điện thoại:</span>
                    <span class="hd-info-value">{{ $profile->soDienThoai ?? '—' }}</span>
                </div>
            </div>
        </div>

        {{-- Card: Lớp học --}}
        <div class="hd-info-card">
            <h4 class="hd-info-card-title"><i class="fas fa-graduation-cap me-2"></i>Thông tin lớp học</h4>
            <div class="hd-info-rows">
                <div class="hd-info-row">
                    <span class="hd-info-label">Lớp học:</span>
                    <span class="hd-info-value fw-bold">{{ $hoaDon->dangKyLopHoc?->lopHoc?->tenLopHoc ?? '—' }}</span>
                </div>
                <div class="hd-info-row">
                    <span class="hd-info-label">Khóa học:</span>
                    <span class="hd-info-value">{{ $hoaDon->dangKyLopHoc?->lopHoc?->khoaHoc?->tenKhoaHoc ?? '—' }}</span>
                </div>
                <div class="hd-info-row">
                    <span class="hd-info-label">Cơ sở:</span>
                    <span class="hd-info-value">{{ $hoaDon->coSo?->tenCoSo ?? '—' }}</span>
                </div>
                <div class="hd-info-row">
                    <span class="hd-info-label">Ngày đăng ký:</span>
                    <span class="hd-info-value">
                        {{ $hoaDon->dangKyLopHoc?->ngayDangKy ? \Carbon\Carbon::parse($hoaDon->dangKyLopHoc->ngayDangKy)->format('d/m/Y') : '—' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Card: Chi tiết hóa đơn --}}
        <div class="hd-info-card">
            <h4 class="hd-info-card-title"><i class="fas fa-file-alt me-2"></i>Chi tiết hóa đơn</h4>
            <div class="hd-info-rows">
                <div class="hd-info-row">
                    <span class="hd-info-label">Mã hóa đơn:</span>
                    <span class="hd-info-value fw-bold">{{ $maHD }}</span>
                </div>
                <div class="hd-info-row">
                    <span class="hd-info-label">Loại:</span>
                    <span class="hd-info-value">{{ $hoaDon->loai_hoa_don_label }}</span>
                </div>
                <div class="hd-info-row">
                    <span class="hd-info-label">Nguồn thu:</span>
                    <span class="hd-info-value">{{ $hoaDon->nguonThuLabel }}</span>
                </div>
                @if ($hoaDon->nguonThu === \App\Models\Finance\HoaDon::NGUON_THU_PHU_PHI && $hoaDon->dangKyLopHocPhuPhi)
                    <div class="hd-info-row">
                        <span class="hd-info-label">Khoản bổ sung:</span>
                        <span class="hd-info-value">{{ $hoaDon->dangKyLopHocPhuPhi->tenKhoanThuSnapshot }}</span>
                    </div>
                    <div class="hd-info-row">
                        <span class="hd-info-label">Nhóm phí:</span>
                        <span class="hd-info-value">{{ $hoaDon->dangKyLopHocPhuPhi->nhomPhiLabel }}</span>
                    </div>
                @endif
                <div class="hd-info-row">
                    <span class="hd-info-label">PT Thanh toán:</span>
                    <span class="hd-info-value">
                        @if ($hoaDon->phuongThucThanhToan == 1)
                            <i class="fas fa-money-bill-wave text-success"></i> Tiền mặt
                        @elseif($hoaDon->phuongThucThanhToan == 2)
                            <i class="fas fa-university text-primary"></i> Chuyển khoản
                        @elseif($hoaDon->phuongThucThanhToan == 3)
                            <i class="fas fa-qrcode text-info"></i> VNPay
                        @else
                            —
                        @endif
                    </span>
                </div>
                <div class="hd-info-row">
                    <span class="hd-info-label">Hạn thanh toán:</span>
                    <span
                        class="hd-info-value {{ $hoaDon->ngayHetHan && \Carbon\Carbon::parse($hoaDon->ngayHetHan)->isPast() && $conNo > 0 ? 'text-danger fw-bold' : '' }}">
                        {{ $hoaDon->ngayHetHan ? \Carbon\Carbon::parse($hoaDon->ngayHetHan)->format('d/m/Y') : '—' }}
                        @if ($hoaDon->ngayHetHan && \Carbon\Carbon::parse($hoaDon->ngayHetHan)->isPast() && $conNo > 0)
                            <span class="badge-overdue">Quá hạn</span>
                        @endif
                    </span>
                </div>
                @if ($hoaDon->nguoiLap)
                    <div class="hd-info-row">
                        <span class="hd-info-label">Người lập:</span>
                        <span
                            class="hd-info-value">{{ $hoaDon->nguoiLap->hoSoNguoiDung->hoTen ?? $hoaDon->nguoiLap->taiKhoan }}</span>
                    </div>
                @endif
                @if ($hoaDon->ghiChu)
                    <div class="hd-info-row">
                        <span class="hd-info-label">Ghi chú:</span>
                        <span class="hd-info-value">{{ $hoaDon->ghiChu }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Payment Summary + Update Form ──────────────────────────── --}}
    <div class="hd-bottom-grid">
        {{-- Left: Tổng kết --}}
        <div class="hd-summary-card">
            <h4 class="hd-info-card-title"><i class="fas fa-calculator me-2"></i>Tổng kết thanh toán</h4>
            <div class="hd-summary-rows">
                <div class="hd-summary-row">
                    <span>Tổng tiền:</span>
                    <span class="fw-bold">{{ number_format($hoaDon->tongTien, 0, ',', '.') }}đ</span>
                </div>
                @if ($hoaDon->giamGia > 0)
                    <div class="hd-summary-row">
                        <span>Giảm giá:</span>
                        <span class="text-success">-{{ number_format($hoaDon->giamGia, 0, ',', '.') }}đ</span>
                    </div>
                @endif
                @if ($hoaDon->thue > 0)
                    <div class="hd-summary-row">
                        <span>Thuế ({{ $hoaDon->thue }}%):</span>
                        <span>{{ number_format(($hoaDon->tongTien - $hoaDon->giamGia) * $hoaDon->thue / 100, 0, ',', '.') }}đ</span>
                    </div>
                @endif
                <div class="hd-summary-row">
                    <span>Đã thu:</span>
                    <span class="text-success fw-bold">{{ number_format($hoaDon->daTra, 0, ',', '.') }}đ</span>
                </div>
                <div class="hd-summary-row hd-summary-total">
                    <span>Còn nợ:</span>
                    <span class="{{ $conNo > 0 ? 'text-danger' : 'text-success' }} fw-bold">
                        {{ number_format($hoaDon->conNo, 0, ',', '.') }}đ
                    </span>
                </div>
            </div>

            {{-- Nút thêm phiếu thu --}}
            @if (auth()->user()->canDo('tai_chinh', 'sua') && $conNo > 0)
                <button type="button" class="btn-add-receipt" data-bs-toggle="modal" data-bs-target="#addPhieuThuModal">
                    <i class="fas fa-plus me-1"></i> Thêm phiếu thu
                </button>
            @endif
        </div>

        {{-- Right: Cập nhật hóa đơn --}}
        @if (auth()->user()->canDo('tai_chinh', 'sua'))
            <div class="hd-update-card">
                <h4 class="hd-info-card-title"><i class="fas fa-pen me-2"></i>Cập nhật hóa đơn</h4>
                <form action="{{ route('admin.hoa-don.update', $hoaDon->hoaDonId) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="hd-form-group">
                        <label>Loại hóa đơn</label>
                        <select name="loaiHoaDon" class="hd-form-control">
                            <option value="0" {{ $hoaDon->loaiHoaDon == 0 ? 'selected' : '' }}>Đăng ký mới</option>
                            <option value="1" {{ $hoaDon->loaiHoaDon == 1 ? 'selected' : '' }}>Gia hạn</option>
                            <option value="2" {{ $hoaDon->loaiHoaDon == 2 ? 'selected' : '' }}>Khác</option>
                        </select>
                    </div>
                    <div class="hd-form-group">
                        <label>Hạn thanh toán</label>
                        <input type="date" name="ngayHetHan" class="hd-form-control"
                            value="{{ $hoaDon->ngayHetHan ? \Carbon\Carbon::parse($hoaDon->ngayHetHan)->format('Y-m-d') : '' }}">
                    </div>
                    <div class="hd-form-group">
                        <label>Giảm giá (VNĐ)</label>
                        <input type="hidden" name="giamGia" value="{{ (int) $hoaDon->giamGia }}">
                        <input type="text" class="hd-form-control money-input" inputmode="numeric" data-target="giamGia"
                            value="{{ number_format((int) $hoaDon->giamGia, 0, ',', '.') }}" placeholder="0">
                    </div>
                    <div class="hd-form-group">
                        <label>Ghi chú</label>
                        <textarea name="ghiChu" class="hd-form-control" rows="3">{{ $hoaDon->ghiChu }}</textarea>
                    </div>
                    <button type="submit" class="btn-update-hd">
                        <i class="fas fa-save me-1"></i> Lưu thay đổi
                    </button>
                </form>
            </div>
        @endif
    </div>

    {{-- ── Phiếu Thu Timeline ─────────────────────────────────────── --}}
    <div class="hd-receipts-card">
        <div class="hd-receipts-header">
            <h4 class="hd-info-card-title"><i class="fas fa-receipt me-2"></i>Lịch sử phiếu thu</h4>
            <span class="hd-receipts-count">{{ $hoaDon->phieuThus->count() }} phiếu thu</span>
        </div>

        @if ($hoaDon->phieuThus->count() > 0)
            <div class="hd-receipts-list">
                @foreach ($hoaDon->phieuThus->sortByDesc('ngayThu') as $pt)
                    <div class="hd-receipt-item {{ $pt->trangThai == 0 ? 'hd-receipt-cancelled' : '' }}">
                        <div class="hd-receipt-icon {{ $pt->trangThai == 0 ? 'cancelled' : 'active' }}">
                            @if ($pt->trangThai == 0)
                                <i class="fas fa-times"></i>
                            @else
                                <i class="fas fa-check"></i>
                            @endif
                        </div>
                        <div class="hd-receipt-content">
                            <div class="hd-receipt-top">
                                <div>
                                    <span
                                        class="hd-receipt-code">{{ $pt->maPhieuThu ?: ('PT-' . str_pad($pt->phieuThuId, 6, '0', STR_PAD_LEFT)) }}</span>
                                    @if ($pt->trangThai == 0)
                                        <span class="badge-cancelled">Đã hủy</span>
                                    @endif
                                </div>
                                <span class="hd-receipt-amount {{ $pt->trangThai == 0 ? 'cancelled' : '' }}">
                                    +{{ number_format($pt->soTien, 0, ',', '.') }}đ
                                </span>
                            </div>
                            <div class="hd-receipt-details">
                                <span><i class="far fa-calendar-alt"></i>
                                    {{ \Carbon\Carbon::parse($pt->ngayThu)->format('d/m/Y') }}</span>
                                <span><i class="fas fa-credit-card"></i> {{ $pt->phuong_thuc_label }}</span>
                                <span><i class="fas fa-user"></i>
                                    {{ $pt->taiKhoan?->hoSoNguoiDung?->hoTen ?? ($pt->taiKhoan?->taiKhoan ?? '—') }}</span>
                            </div>
                            @if ($pt->ghiChu)
                                <div class="hd-receipt-note">{{ $pt->ghiChu }}</div>
                            @endif
                        </div>
                        @if ($pt->trangThai == 1 && auth()->user()->canDo('tai_chinh', 'sua'))
                            <button type="button" class="btn-cancel-receipt"
                                onclick="confirmCancelReceipt({{ $pt->phieuThuId }}, '{{ $pt->maPhieuThu ?: 'PT-' . str_pad($pt->phieuThuId, 6, '0', STR_PAD_LEFT) }}')">
                                <i class="fas fa-ban"></i>
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="hd-receipts-empty">
                <i class="fas fa-inbox"></i>
                <p>Chưa có phiếu thu nào</p>
            </div>
        @endif
    </div>

    {{-- Hidden DELETE form for cancel receipt --}}
    <form id="cancel-receipt-form" method="POST" style="display:none">
        @csrf
        @method('DELETE')
    </form>

@endsection

@section('modal')
    {{-- ── Modal: Thêm phiếu thu ──────────────────────────────────── --}}
    <div class="modal fade" id="addPhieuThuModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content hd-modal">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Thêm phiếu thu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.hoa-don.phieu-thu.store', $hoaDon->hoaDonId) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="hd-form-group">
                            <label>Số tiền (VNĐ) <span class="text-danger">*</span></label>
                            <input type="hidden" name="soTien" value="{{ $conNo }}">
                            <input type="text" class="hd-form-control money-input" inputmode="numeric" required
                                data-target="soTien" data-max="{{ $conNo }}"
                                value="{{ number_format($conNo, 0, ',', '.') }}" placeholder="Nhập số tiền...">
                            <small class="text-muted">Còn nợ: {{ number_format($conNo, 0, ',', '.') }}đ</small>
                        </div>
                        <div class="hd-form-group">
                            <label>Ngày thu <span class="text-danger">*</span></label>
                            <input type="date" name="ngayThu" class="hd-form-control" required
                                value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="hd-form-group">
                            <label>Phương thức thanh toán <span class="text-danger">*</span></label>
                            <select name="phuongThucThanhToan" class="hd-form-control" required>
                                <option value="1">Tiền mặt</option>
                                <option value="2">Chuyển khoản</option>
                                <option value="3">VNPay</option>
                            </select>
                        </div>
                        <div class="hd-form-group">
                            <label>Ghi chú</label>
                            <textarea name="ghiChu" class="hd-form-control" rows="2"
                                placeholder="Ghi chú phiếu thu..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn-modal-confirm">
                            <i class="fas fa-check me-1"></i> Tạo phiếu thu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // ── Auto-format money inputs ─────────────────────────────
        function formatNumber(n) {
            return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function parseNumber(s) {
            return parseInt(s.replace(/\./g, ''), 10) || 0;
        }

        document.querySelectorAll('.money-input').forEach(input => {
            const targetName = input.dataset.target;
            const hiddenInput = input.closest('form')?.querySelector(`input[name="${targetName}"]`)
                || document.querySelector(`input[name="${targetName}"]`);

            input.addEventListener('input', function () {
                // Strip non-digits
                let raw = this.value.replace(/[^\d]/g, '');
                let num = parseInt(raw, 10) || 0;

                // Enforce max if set
                const max = parseInt(this.dataset.max, 10);
                if (max && num > max) num = max;

                // Update display & hidden value
                this.value = num > 0 ? formatNumber(num) : '';
                if (hiddenInput) hiddenInput.value = num;
            });

            // Also handle paste
            input.addEventListener('paste', function (e) {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData).getData('text');
                const num = parseInt(pasted.replace(/[^\d]/g, ''), 10) || 0;
                this.value = num > 0 ? formatNumber(num) : '';
                if (hiddenInput) hiddenInput.value = num;
            });

            // Format initial value
            const initVal = parseNumber(input.value);
            if (initVal > 0) {
                input.value = formatNumber(initVal);
                if (hiddenInput) hiddenInput.value = initVal;
            }
        });

        // ── Cancel receipt confirm ───────────────────────────────
        function confirmCancelReceipt(id, code) {
            Swal.fire({
                title: 'Hủy phiếu thu?',
                html: `Bạn có chắc muốn hủy phiếu thu <strong>${code}</strong>?<br>
                           <small style="color:#8899a6">Số tiền sẽ được trừ khỏi tổng đã trả.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-ban me-1"></i> Hủy phiếu thu',
                cancelButtonText: 'Đóng',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                focusCancel: true,
            }).then(result => {
                if (result.isConfirmed) {
                    const form = document.getElementById('cancel-receipt-form');
                    form.action = `/admin/hoa-don/phieu-thu/${id}`;
                    form.submit();
                }
            });
        }
    </script>
@endsection
