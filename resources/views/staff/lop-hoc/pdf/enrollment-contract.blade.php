<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Phiếu hợp đồng ghi danh</title>
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
            margin-bottom: 20px;
        }

        .title h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }

        .subtitle {
            color: #475569;
            margin-top: 4px;
        }

        .box {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 16px;
            background: #f8fafc;
        }

        .box-title {
            margin: 0 0 10px;
            font-size: 15px;
            font-weight: 700;
        }

        .kv {
            width: 100%;
            border-collapse: collapse;
        }

        .kv td {
            padding: 7px 0;
            vertical-align: top;
        }

        .kv td:first-child {
            width: 190px;
            font-weight: 700;
        }

        .note {
            margin: 0;
            padding-left: 18px;
        }

        .footer {
            margin-top: 24px;
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
            <h1>Phiếu hợp đồng ghi danh</h1>
            <div class="subtitle">Trung tâm ngoại ngữ - Biên nhận tạo tài khoản và đăng ký lớp học</div>
        </div>

        <div class="box">
            <div class="box-title">Thông tin học viên</div>
            <table class="kv">
                <tr>
                    <td>Họ tên</td>
                    <td>{{ $student->hoSoNguoiDung?->hoTen ?? $student->taiKhoan }}</td>
                </tr>
                <tr>
                    <td>Tài khoản đăng nhập</td>
                    <td><strong>{{ $student->taiKhoan }}</strong></td>
                </tr>
                <tr>
                    <td>Mật khẩu tạm</td>
                    <td><strong>{{ $temporaryPassword }}</strong></td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>{{ $student->email }}</td>
                </tr>
                <tr>
                    <td>Số điện thoại</td>
                    <td>{{ $student->hoSoNguoiDung?->soDienThoai ?: '—' }}</td>
                </tr>
                <tr>
                    <td>CCCD / CMND</td>
                    <td>{{ $student->hoSoNguoiDung?->cccd ?: '—' }}</td>
                </tr>
            </table>
        </div>

        <div class="box">
            <div class="box-title">Thông tin ghi danh</div>
            <table class="kv">
                <tr>
                    <td>Mã lớp</td>
                    <td>{{ $registration->lopHoc?->maLopHoc ?: '—' }}</td>
                </tr>
                <tr>
                    <td>Tên lớp</td>
                    <td>{{ $registration->lopHoc?->tenLopHoc ?: '—' }}</td>
                </tr>
                <tr>
                    <td>Khóa học</td>
                    <td>{{ $registration->lopHoc?->khoaHoc?->tenKhoaHoc ?: '—' }}</td>
                </tr>
                <tr>
                    <td>Cơ sở</td>
                    <td>{{ $registration->lopHoc?->coSo?->tenCoSo ?: '—' }}</td>
                </tr>
                <tr>
                    <td>Ngày ghi danh</td>
                    <td>{{ $registration->ngayDangKy ? \Illuminate\Support\Carbon::parse($registration->ngayDangKy)->format('d/m/Y H:i') : '—' }}</td>
                </tr>
                <tr>
                    <td>Học phí phải thu</td>
                    <td><strong>{{ number_format((float) $registration->hocPhiPhaiThuSnapshot, 0, ',', '.') }} VNĐ</strong></td>
                </tr>
                <tr>
                    <td>Số hóa đơn phát sinh</td>
                    <td>{{ $registration->hoaDons->count() }}</td>
                </tr>
            </table>
        </div>

        <div class="box">
            <div class="box-title">Lưu ý khi bàn giao tài khoản</div>
            <ul class="note">
                <li>Học viên đăng nhập bằng tài khoản được in trên phiếu này.</li>
                <li>Mật khẩu tạm được sinh theo CCCD nếu có ít nhất 8 ký tự, nếu không hệ thống dùng mặc định 12345678.</li>
                <li>Học viên cần đổi mật khẩu sau lần đăng nhập đầu tiên để đảm bảo an toàn.</li>
                <li>Phiếu này đồng thời là căn cứ xác nhận đã tạo tài khoản và ghi danh vào lớp.</li>
            </ul>
        </div>

        <table class="footer">
            <tr>
                <td>
                    <strong>Nhân viên tiếp nhận</strong><br><br><br>
                    ....................................
                </td>
                <td>
                    <strong>Học viên / Người giám hộ</strong><br><br><br>
                    ....................................
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
