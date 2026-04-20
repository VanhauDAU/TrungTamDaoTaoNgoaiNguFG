@php
    $student = $receipt->taiKhoan?->hoSoNguoiDung;
    $studentName = $student->hoTen ?? ($receipt->taiKhoan?->taiKhoan ?? '—');
    $invoiceCode = $invoice?->maHoaDon ?: ($invoice ? 'HD-' . str_pad((string) $invoice->hoaDonId, 6, '0', STR_PAD_LEFT) : '—');
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phiếu thu {{ $code }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; }
        .page { padding: 18px; }
        .header { width: 100%; margin-bottom: 18px; }
        .header td { vertical-align: top; }
        .title { font-size: 24px; font-weight: 700; margin: 0 0 6px; }
        .card { border: 1px solid #dbe3ef; border-radius: 10px; padding: 14px; margin-bottom: 14px; }
        .card h3 { margin: 0 0 10px; font-size: 13px; }
        .row { margin-bottom: 7px; }
        .row strong { display: inline-block; min-width: 130px; }
        .amount-box { margin: 18px 0; padding: 18px; border-radius: 12px; background: #eff6ff; border: 1px solid #bfdbfe; text-align: center; }
        .amount-box small { display: block; color: #1d4ed8; margin-bottom: 6px; }
        .amount-box strong { font-size: 24px; color: #1e3a8a; }
        .footer { margin-top: 24px; font-size: 11px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="page">
        <table class="header">
            <tr>
                <td>
                    <div class="title">PHIẾU THU</div>
                    <div><strong>Mã phiếu:</strong> {{ $code }}</div>
                    <div><strong>Ngày thu:</strong> {{ $receipt->ngayThu ? \Carbon\Carbon::parse($receipt->ngayThu)->format('d/m/Y') : '—' }}</div>
                </td>
                <td style="text-align:right;">
                    <div><strong>Hóa đơn liên quan:</strong> {{ $invoiceCode }}</div>
                    <div><strong>Trạng thái:</strong> {{ (int) $receipt->trangThai === \App\Models\Finance\PhieuThu::TRANG_THAI_HOP_LE ? 'Hợp lệ' : 'Đã hủy' }}</div>
                </td>
            </tr>
        </table>

        <div class="card">
            <h3>Thông tin người nộp</h3>
            <div class="row"><strong>Họ tên:</strong> {{ $studentName }}</div>
            <div class="row"><strong>Email:</strong> {{ $receipt->taiKhoan?->email ?? '—' }}</div>
            <div class="row"><strong>Điện thoại:</strong> {{ $student->soDienThoai ?? '—' }}</div>
            <div class="row"><strong>Lớp học:</strong> {{ $invoice?->dangKyLopHoc?->lopHoc?->tenLopHoc ?? '—' }}</div>
            <div class="row"><strong>Khóa học:</strong> {{ $invoice?->dangKyLopHoc?->lopHoc?->khoaHoc?->tenKhoaHoc ?? '—' }}</div>
        </div>

        <div class="card">
            <h3>Thông tin thanh toán</h3>
            <div class="row"><strong>Phương thức:</strong> {{ $receipt->phuongThucLabel }}</div>
            <div class="row"><strong>Người ghi nhận:</strong> {{ $receipt->nguoiDuyet?->hoSoNguoiDung?->hoTen ?? ($receipt->nguoiDuyet?->taiKhoan ?? '—') }}</div>
            <div class="row"><strong>Cơ sở:</strong> {{ $invoice?->coSo?->tenCoSo ?? '—' }}</div>
            @if ($receipt->ghiChu)
                <div class="row"><strong>Ghi chú:</strong> {{ $receipt->ghiChu }}</div>
            @endif
        </div>

        <div class="amount-box">
            <small>SỐ TIỀN ĐÃ THU</small>
            <strong>{{ number_format((float) $receipt->soTien, 0, ',', '.') }}đ</strong>
        </div>

        <div class="footer">
            Tài liệu được tạo lúc {{ now()->format('d/m/Y H:i') }} từ hệ thống quản lý trung tâm.
        </div>
    </div>
</body>
</html>
