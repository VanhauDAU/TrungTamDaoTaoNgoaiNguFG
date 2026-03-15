<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Phiếu bàn giao tài khoản</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 13px;
            line-height: 1.6;
        }

        .sheet {
            border: 1px solid #cbd5e1;
            border-radius: 16px;
            padding: 24px;
        }

        .title {
            text-align: center;
            margin-bottom: 18px;
        }

        .title h1 {
            margin: 0;
            font-size: 24px;
        }

        .box {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 16px;
            background: #f8fafc;
        }

        .kv {
            width: 100%;
            border-collapse: collapse;
        }

        .kv td {
            padding: 8px 0;
            vertical-align: top;
        }

        .kv td:first-child {
            width: 180px;
            font-weight: 700;
        }

        .footer {
            margin-top: 28px;
            width: 100%;
        }

        .footer td {
            width: 50%;
            text-align: center;
            padding-top: 36px;
        }
    </style>
</head>

<body>
    <div class="sheet">
        <div class="title">
            <h1>PHIEU BAN GIAO TAI KHOAN</h1>
            <div>{{ $roleLabel }} - Trung tam ngoai ngu</div>
        </div>

        <div class="box">
            <table class="kv">
                <tr>
                    <td>Ho ten</td>
                    <td>{{ $record->hoSoNguoiDung?->hoTen ?: $record->taiKhoan }}</td>
                </tr>
                <tr>
                    <td>Ten dang nhap</td>
                    <td><strong>{{ $handover['username'] }}</strong></td>
                </tr>
                <tr>
                    <td>Mat khau tam</td>
                    <td><strong>{{ $handover['password'] }}</strong></td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>{{ $record->email }}</td>
                </tr>
                <tr>
                    <td>Ngay ban giao</td>
                    <td>{{ now()->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td>Han hieu luc</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($handover['expires_at'])->format('d/m/Y H:i') }}</td>
                </tr>
            </table>
        </div>

        <div class="box">
            <strong>Luu y</strong>
            <ul>
                <li>Mat khau tam chi dung cho lan dang nhap dau tien.</li>
                <li>Nhan su bat buoc doi mat khau ngay sau khi dang nhap.</li>
                <li>Khong chia se thong tin tai khoan cho nguoi khong co tham quyen.</li>
            </ul>
        </div>

        <table class="footer">
            <tr>
                <td>
                    <strong>Nguoi ban giao</strong><br><br><br>
                    ....................................
                </td>
                <td>
                    <strong>Nguoi nhan</strong><br><br><br>
                    ....................................
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
