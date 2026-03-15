<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Ho so nhan su</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 12px;
            line-height: 1.55;
        }

        h1,
        h2,
        h3 {
            margin: 0 0 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .section {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .section-title {
            font-size: 15px;
            margin-bottom: 12px;
        }

        .grid {
            width: 100%;
            border-collapse: collapse;
        }

        .grid td {
            width: 50%;
            padding: 7px 8px;
            vertical-align: top;
            border-bottom: 1px solid #f1f5f9;
        }

        .grid td strong {
            display: block;
            margin-bottom: 4px;
        }

        .mini-list {
            margin: 0;
            padding-left: 18px;
        }

        .signature {
            width: 100%;
            margin-top: 28px;
        }

        .signature td {
            width: 50%;
            text-align: center;
            padding-top: 40px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>HO SO NHAN SU</h1>
        <div>{{ $roleLabel }} - {{ $record->hoSoNguoiDung?->hoTen ?: $record->taiKhoan }}</div>
    </div>

    <div class="section">
        <div class="section-title">1. Tai khoan va trang thai</div>
        <table class="grid">
            <tr>
                <td>
                    <strong>Ten dang nhap</strong>
                    {{ $record->taiKhoan }}
                </td>
                <td>
                    <strong>Email</strong>
                    {{ $record->email }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Vai tro</strong>
                    {{ $roleLabel }}
                </td>
                <td>
                    <strong>Trang thai</strong>
                    {{ (int) $record->trangThai === 1 ? 'Dang hoat dong' : 'Bi khoa' }}
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">2. Thong tin ca nhan</div>
        <table class="grid">
            <tr>
                <td><strong>Ho ten</strong>{{ $record->hoSoNguoiDung?->hoTen ?: 'Chua cap nhat' }}</td>
                <td><strong>Ngay sinh</strong>{{ $record->hoSoNguoiDung?->ngaySinh ? \Illuminate\Support\Carbon::parse($record->hoSoNguoiDung->ngaySinh)->format('d/m/Y') : 'Chua cap nhat' }}</td>
            </tr>
            <tr>
                <td><strong>So dien thoai</strong>{{ $record->hoSoNguoiDung?->soDienThoai ?: 'Chua cap nhat' }}</td>
                <td><strong>Zalo</strong>{{ $record->hoSoNguoiDung?->zalo ?: 'Chua cap nhat' }}</td>
            </tr>
            <tr>
                <td><strong>CCCD / CMND</strong>{{ $record->hoSoNguoiDung?->cccd ?: 'Chua cap nhat' }}</td>
                <td><strong>Dia chi</strong>{{ $record->hoSoNguoiDung?->diaChi ?: 'Chua cap nhat' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">3. Thong tin nhan su</div>
        <table class="grid">
            <tr>
                <td><strong>Chuc vu</strong>{{ $record->nhanSu?->chucVu ?: 'Chua cap nhat' }}</td>
                <td><strong>Chuyen mon</strong>{{ $record->nhanSu?->chuyenMon ?: 'Chua cap nhat' }}</td>
            </tr>
            <tr>
                <td><strong>Bang cap</strong>{{ $record->nhanSu?->bangCap ?: 'Chua cap nhat' }}</td>
                <td><strong>Hoc vi / Chung chi</strong>{{ $record->nhanSu?->hocVi ?: 'Chua cap nhat' }}</td>
            </tr>
            <tr>
                <td><strong>Loai hop dong</strong>{{ $record->nhanSu?->loaiHopDong ? ($loaiHopDongOptions[$record->nhanSu->loaiHopDong] ?? $record->nhanSu->loaiHopDong) : 'Chua cap nhat' }}</td>
                <td><strong>Ngay vao lam</strong>{{ $record->nhanSu?->ngayVaoLam ? \Illuminate\Support\Carbon::parse($record->nhanSu->ngayVaoLam)->format('d/m/Y') : 'Chua cap nhat' }}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>Co so lam viec</strong>{{ $record->nhanSu?->coSoDaoTao?->tenCoSo ? $record->nhanSu->coSoDaoTao->tenCoSo . ' - ' . $record->nhanSu->coSoDaoTao->diaChiDayDu : 'Chua cap nhat' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">4. Goi luong hien hanh</div>
        @if ($goiLuongHienHanh)
            <table class="grid">
                <tr>
                    <td><strong>Loai luong</strong>{{ $loaiLuongOptions[$goiLuongHienHanh->loaiLuong] ?? $goiLuongHienHanh->loaiLuong }}</td>
                    <td><strong>Luong chinh</strong>{{ number_format((float) $goiLuongHienHanh->luongChinh, 0, ',', '.') }} VNĐ</td>
                </tr>
                <tr>
                    <td><strong>Hieu luc tu</strong>{{ optional($goiLuongHienHanh->hieuLucTu)->format('d/m/Y') ?: 'N/A' }}</td>
                    <td><strong>Ghi chu</strong>{{ $goiLuongHienHanh->ghiChu ?: 'Khong co' }}</td>
                </tr>
            </table>

            @if ($goiLuongHienHanh->chiTiets->isNotEmpty())
                <h3>Chi tiet phu cap / khau tru</h3>
                <ul class="mini-list">
                    @foreach ($goiLuongHienHanh->chiTiets as $chiTiet)
                        <li>
                            {{ $loaiLuongChiTietOptions[$chiTiet->loai] ?? $chiTiet->loai }} -
                            {{ $chiTiet->tenKhoan }}:
                            {{ number_format((float) $chiTiet->soTien, 0, ',', '.') }} VNĐ
                            @if ($chiTiet->ghiChu)
                                ({{ $chiTiet->ghiChu }})
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        @else
            Chua co goi luong hien hanh.
        @endif
    </div>

    <div class="section">
        <div class="section-title">5. Quy dinh ap dung</div>
        <div><strong>{{ $hoSoNhanSu?->tieuDeMauSnapshot ?: 'Chua co mau quy dinh' }}</strong></div>
        <div>{!! $hoSoNhanSu?->noiDungQuyDinhSnapshot ?: '<p>Chua co snapshot quy dinh.</p>' !!}</div>
    </div>

    <div class="section">
        <div class="section-title">6. Danh muc tai lieu</div>
        @if ($taiLieuHoatDong->isNotEmpty())
            <ul class="mini-list">
                @foreach ($taiLieuHoatDong as $taiLieu)
                    <li>{{ $taiLieu->tenHienThi }} - {{ $loaiTaiLieuOptions[$taiLieu->loaiTaiLieu] ?? $taiLieu->loaiTaiLieu }} - v{{ $taiLieu->phienBan }}</li>
                @endforeach
            </ul>
        @else
            Chua co tai lieu dang active.
        @endif
    </div>

    <table class="signature">
        <tr>
            <td>
                <strong>Nguoi lap ho so</strong><br><br><br>
                ....................................
            </td>
            <td>
                <strong>Quan ly phe duyet</strong><br><br><br>
                ....................................
            </td>
        </tr>
    </table>
</body>

</html>
