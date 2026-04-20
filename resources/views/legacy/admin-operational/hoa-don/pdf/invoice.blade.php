@php
    $profile = $invoice->taiKhoan?->hoSoNguoiDung;
    $studentName = $profile->hoTen ?? ($invoice->taiKhoan?->taiKhoan ?? '—');
    $gross = (float) ($invoice->tongTienSauThue > 0 ? $invoice->tongTienSauThue : $invoice->tongTien);
    $discount = (float) ($invoice->giamGia ?? 0);
    $net = max(0, $gross - $discount);
    $paid = (float) $invoice->daTra;
    $remaining = max(0, $net - $paid);
    $validReceipts = $invoice->phieuThus->where('trangThai', \App\Models\Finance\PhieuThu::TRANG_THAI_HOP_LE)->sortByDesc('ngayThu')->values();
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa đơn {{ $code }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; }
        .page { padding: 12px 18px; }
        .header { width: 100%; margin-bottom: 18px; }
        .header td { vertical-align: top; }
        .title { font-size: 24px; font-weight: 700; margin: 0 0 6px; }
        .muted { color: #6b7280; }
        .pill { display: inline-block; padding: 5px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; background: #eff6ff; color: #1d4ed8; }
        .grid { width: 100%; margin-bottom: 18px; }
        .grid td { width: 50%; vertical-align: top; padding-right: 12px; }
        .card { border: 1px solid #dbe3ef; border-radius: 10px; padding: 12px; min-height: 120px; }
        .card h3 { margin: 0 0 10px; font-size: 13px; }
        .row { margin-bottom: 7px; }
        .row strong { display: inline-block; min-width: 120px; }
        .summary { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .summary td { border: 1px solid #dbe3ef; padding: 10px 12px; }
        .summary .label { background: #f8fafc; font-weight: 700; width: 40%; }
        .timeline { width: 100%; border-collapse: collapse; }
        .timeline th, .timeline td { border: 1px solid #dbe3ef; padding: 8px 10px; text-align: left; }
        .timeline th { background: #f8fafc; font-size: 11px; text-transform: uppercase; }
        .text-right { text-align: right; }
        .footer { margin-top: 24px; font-size: 11px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="page">
        <table class="header">
            <tr>
                <td>
                    <div class="title">HÓA ĐƠN</div>
                    <div><strong>Mã:</strong> {{ $code }}</div>
                    <div><strong>Ngày lập:</strong> {{ $invoice->ngayLap ? \Carbon\Carbon::parse($invoice->ngayLap)->format('d/m/Y') : '—' }}</div>
                    <div><strong>Hạn thanh toán:</strong> {{ $invoice->ngayHetHan ? \Carbon\Carbon::parse($invoice->ngayHetHan)->format('d/m/Y') : 'Không đặt hạn' }}</div>
                </td>
                <td style="text-align:right;">
                    <div class="pill">{{ $invoice->nguonThuLabel }}</div>
                    <div style="margin-top:8px;"><strong>Trạng thái:</strong> {{ $invoice->trangThaiLabel }}</div>
                    <div><strong>Loại:</strong> {{ $invoice->loaiHoaDonLabel }}</div>
                </td>
            </tr>
        </table>

        <table class="grid">
            <tr>
                <td>
                    <div class="card">
                        <h3>Thông tin học viên</h3>
                        <div class="row"><strong>Họ tên:</strong> {{ $studentName }}</div>
                        <div class="row"><strong>Email:</strong> {{ $invoice->taiKhoan?->email ?? '—' }}</div>
                        <div class="row"><strong>Điện thoại:</strong> {{ $profile->soDienThoai ?? '—' }}</div>
                        <div class="row"><strong>Tài khoản:</strong> {{ $invoice->taiKhoan?->taiKhoan ?? '—' }}</div>
                    </div>
                </td>
                <td>
                    <div class="card">
                        <h3>Thông tin khoản thu</h3>
                        <div class="row"><strong>Lớp học:</strong> {{ $invoice->dangKyLopHoc?->lopHoc?->tenLopHoc ?? '—' }}</div>
                        <div class="row"><strong>Khóa học:</strong> {{ $invoice->dangKyLopHoc?->lopHoc?->khoaHoc?->tenKhoaHoc ?? '—' }}</div>
                        <div class="row"><strong>Cơ sở:</strong> {{ $invoice->coSo?->tenCoSo ?? '—' }}</div>
                        <div class="row"><strong>Khoản thu:</strong>
                            @if ($invoice->nguonThu === \App\Models\Finance\HoaDon::NGUON_THU_PHU_PHI)
                                {{ $invoice->dangKyLopHocPhuPhi?->tenKhoanThuSnapshot ?? 'Khoản bổ sung' }}
                            @else
                                {{ $invoice->lopHocDotThu?->tenDotThu ?? 'Học phí chính' }}
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="summary">
            <tr>
                <td class="label">Tổng tiền</td>
                <td class="text-right">{{ number_format($gross, 0, ',', '.') }}đ</td>
            </tr>
            <tr>
                <td class="label">Giảm giá</td>
                <td class="text-right">{{ number_format($discount, 0, ',', '.') }}đ</td>
            </tr>
            <tr>
                <td class="label">Đã thu</td>
                <td class="text-right">{{ number_format($paid, 0, ',', '.') }}đ</td>
            </tr>
            <tr>
                <td class="label">Còn nợ</td>
                <td class="text-right">{{ number_format($remaining, 0, ',', '.') }}đ</td>
            </tr>
            @if ($invoice->ghiChu)
                <tr>
                    <td class="label">Ghi chú</td>
                    <td>{{ $invoice->ghiChu }}</td>
                </tr>
            @endif
        </table>

        <table class="timeline">
            <thead>
                <tr>
                    <th>Mã phiếu thu</th>
                    <th>Ngày thu</th>
                    <th>Phương thức</th>
                    <th>Người duyệt</th>
                    <th class="text-right">Số tiền</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($validReceipts as $receipt)
                    <tr>
                        <td>{{ $receipt->maPhieuThu ?: 'PT-' . str_pad((string) $receipt->phieuThuId, 6, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $receipt->ngayThu ? \Carbon\Carbon::parse($receipt->ngayThu)->format('d/m/Y') : '—' }}</td>
                        <td>{{ $receipt->phuongThucLabel }}</td>
                        <td>{{ $receipt->nguoiDuyet?->hoSoNguoiDung?->hoTen ?? ($receipt->nguoiDuyet?->taiKhoan ?? '—') }}</td>
                        <td class="text-right">{{ number_format((float) $receipt->soTien, 0, ',', '.') }}đ</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="muted">Chưa có phiếu thu hợp lệ nào cho hóa đơn này.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            Tài liệu được tạo lúc {{ now()->format('d/m/Y H:i') }} từ hệ thống quản lý trung tâm.
        </div>
    </div>
</body>
</html>
