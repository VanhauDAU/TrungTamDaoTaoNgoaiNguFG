@extends('layouts.admin')

@section('title', $lopHoc->tenLopHoc . ' – Chi tiết lớp học')
@section('page-title', 'Lớp Học')
@section('breadcrumb', 'Quản lý · Lớp học · Chi tiết')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/lop-hoc/index.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/khoa-hoc/form.css') }}">
    <style>
        /* ── Hero ─────────────────────────────────────── */
        .lh-show-hero {
            background: linear-gradient(135deg, #4c1d95 0%, #7c3aed 55%, #a78bfa 100%);
            border-radius: 14px;
            padding: 26px 28px;
            color: #fff;
            margin-bottom: 22px;
            position: relative;
            overflow: hidden;
        }

        .lh-show-hero::before {
            content: '';
            position: absolute;
            right: -40px;
            top: -40px;
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, .06);
            border-radius: 50%;
        }

        .lh-show-hero h1 {
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0 0 8px;
        }

        .lh-show-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            font-size: .83rem;
            opacity: .85;
            margin-bottom: 12px;
        }

        .lh-show-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .lh-show-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .lh-hero-btn {
            padding: 7px 14px;
            border-radius: 7px;
            font-size: .82rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all .2s;
        }

        .lh-hero-edit {
            background: rgba(255, 255, 255, .2);
            color: #fff;
        }

        .lh-hero-edit:hover {
            background: rgba(255, 255, 255, .35);
            color: #fff;
        }

        .lh-hero-back {
            background: rgba(255, 255, 255, .15);
            color: #fff;
        }

        .lh-hero-back:hover {
            background: rgba(255, 255, 255, .3);
            color: #fff;
        }

        .lh-hero-kh {
            background: #10b981;
            color: #fff;
        }

        .lh-hero-kh:hover {
            background: #059669;
            color: #fff;
        }

        /* ── Summary cards ─────────────────────────────── */
        .lh-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
            margin-bottom: 22px;
        }

        .lh-sum-card {
            background: #fff;
            border-radius: 10px;
            padding: 14px 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .06);
        }

        .lh-sum-card label {
            font-size: .72rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .lh-sum-val {
            font-size: 1.5rem;
            font-weight: 700;
            margin-top: 4px;
            color: #1e293b;
        }

        /* ── Detail panel ──────────────────────────────── */
        .lh-detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 22px;
        }

        @media(max-width:768px) {
            .lh-detail-grid {
                grid-template-columns: 1fr;
            }
        }

        .lh-detail-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .875rem;
        }

        .lh-detail-table tr td {
            padding: 9px 0;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }

        .lh-detail-table tr:last-child td {
            border-bottom: none;
        }

        .lh-detail-table td:first-child {
            color: #64748b;
            font-size: .8rem;
            font-weight: 600;
            width: 140px;
        }

        /* ── Buổi học timeline ─────────────────────────── */
        .bh-timeline {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .bh-item {
            display: grid;
            grid-template-columns: 90px 1fr auto;
            gap: 0;
            align-items: center;
            padding: 10px 14px;
            border-bottom: 1px solid #f1f5f9;
            transition: background .15s;
        }

        .bh-item:last-child {
            border-bottom: none;
        }

        .bh-item:hover {
            background: #fafcff;
        }

        .bh-date {
            font-size: .78rem;
            font-weight: 700;
            color: #7c3aed;
            line-height: 1.3;
        }

        .bh-date small {
            font-weight: 400;
            color: #94a3b8;
            display: block;
        }

        .bh-info {
            padding: 0 12px;
        }

        .bh-name {
            font-size: .875rem;
            font-weight: 600;
            color: #1e293b;
        }

        .bh-sub {
            font-size: .75rem;
            color: #94a3b8;
            margin-top: 2px;
            display: flex;
            gap: 10px;
        }

        .bh-actions {
            display: flex;
            gap: 5px;
            align-items: center;
        }

        .bh-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 600;
        }

        .bh-done {
            background: #f0fdf4;
            color: #16a34a;
        }

        .bh-todo {
            background: #fff7ed;
            color: #c2410c;
        }

        .bh-cancel {
            background: #f8fafc;
            color: #64748b;
        }

        .bh-att {
            background: #eff6ff;
            color: #1d4ed8;
        }

        /* ── Auto generate form ─────────────────────────── */
        .auto-gen-card {
            background: linear-gradient(135deg, #4c1d95, #7c3aed);
            border-radius: 12px;
            padding: 20px 24px;
            color: #fff;
            margin-bottom: 22px;
        }

        .auto-gen-card h3 {
            font-size: 1rem;
            font-weight: 700;
            margin: 0 0 8px;
        }

        .auto-gen-card p {
            font-size: .82rem;
            opacity: .85;
            margin: 0 0 14px;
        }

        .auto-gen-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .auto-gen-check {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: .84rem;
            cursor: pointer;
        }

        .btn-auto-gen {
            padding: 9px 18px;
            background: #fff;
            color: #7c3aed;
            border: none;
            border-radius: 7px;
            font-size: .875rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 7px;
            transition: all .2s;
        }

        .btn-auto-gen:hover {
            background: #f5f3ff;
            transform: translateY(-1px);
        }

        /* ── Add buoi form ─────────────────────────────── */
        .add-bh-form {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 16px;
            display: none;
        }

        .add-bh-form .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }

        .add-bh-form input,
        .add-bh-form select {
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 7px;
            font-size: .83rem;
            outline: none;
            width: 100%;
        }

        .add-bh-form input:focus,
        .add-bh-form select:focus {
            border-color: #7c3aed;
        }

        /* ── trangThai badges ─────────────────────────────── */
        .bh-tt-ly-thuyet {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .bh-tt-thuc-hanh {
            background: #f0fdf4;
            color: #15803d;
        }

        .bh-tt-truc-tuyen {
            background: #f5f3ff;
            color: #7c3aed;
        }

        .bh-tt-lich-thi {
            background: #fff7ed;
            color: #c2410c;
        }

        .bh-tt-tam-ngung {
            background: #f8fafc;
            color: #64748b;
        }
    </style>
@endsection

@section('content')

    {{-- ── Hero ──────────────────────────────────────────────── --}}
    <div class="lh-show-hero">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;flex-wrap:wrap">
            @if ($lopHoc->khoaHoc)
                <span
                    style="background:rgba(255,255,255,.2);padding:2px 10px;border-radius:20px;font-size:.72rem;font-weight:600">
                    {{ $lopHoc->khoaHoc->tenKhoaHoc }}
                </span>
            @endif
            @php
                if ($lopHoc->isSapMo()) {
                    $lopHocBadgeBg = '#fef3c7';
                    $lopHocBadgeText = '#92400e';
                } elseif ($lopHoc->isOpenForRegistration()) {
                    $lopHocBadgeBg = '#dbeafe';
                    $lopHocBadgeText = '#1e3a8a';
                } elseif ($lopHoc->isInProgress()) {
                    $lopHocBadgeBg = '#dcfce7';
                    $lopHocBadgeText = '#166534';
                } elseif ($lopHoc->isCancelled()) {
                    $lopHocBadgeBg = '#fee2e2';
                    $lopHocBadgeText = '#991b1b';
                } else {
                    $lopHocBadgeBg = '#f1f5f9';
                    $lopHocBadgeText = '#475569';
                }
            @endphp
            <span
                style="background:{{ $lopHocBadgeBg }};color:{{ $lopHocBadgeText }};padding:2px 10px;border-radius:20px;font-size:.72rem;font-weight:700">
                {{ $lopHoc->trangThaiLabel }}
            </span>
        </div>
        <h1>[<span style="color:#fde68a;">{{ $lopHoc->maLopHoc }}</span>] {{ $lopHoc->tenLopHoc }}</h1>
        <div class="lh-show-meta">
            @if ($lopHoc->caHoc)
                <span><i class="fas fa-clock"></i> {{ $lopHoc->caHoc->tenCa }}
                    ({{ $lopHoc->caHoc->gioBatDau }}–{{ $lopHoc->caHoc->gioKetThuc }})</span>
            @endif
            @if ($lopHoc->coSo)
                <span><i class="fas fa-building"></i> {{ $lopHoc->coSo->tenCoSo }}</span>
            @endif
            @if ($lopHoc->lichHoc)
                <span><i class="fas fa-calendar-days"></i> Thứ
                    {{ implode(', ', array_map('trim', explode(',', $lopHoc->lichHoc))) }}</span>
            @endif
            @if ($lopHoc->taiKhoan)
                <span><i class="fas fa-chalkboard-teacher"></i>
                    {{ $lopHoc->taiKhoan->hoSoNguoiDung?->hoTen ?? $lopHoc->taiKhoan->taiKhoan }}</span>
            @endif
        </div>
        <div class="lh-show-actions">
            <a href="{{ route('admin.lop-hoc.edit', $lopHoc->slug) }}" class="lh-hero-btn lh-hero-edit">
                <i class="fas fa-pen"></i> Chỉnh sửa
            </a>
            @if ($lopHoc->khoaHoc)
                <a href="{{ route('admin.khoa-hoc.show', $lopHoc->khoaHoc->slug) }}" class="lh-hero-btn lh-hero-kh">
                    <i class="fas fa-graduation-cap"></i> Xem khóa học
                </a>
            @endif
            <a href="{{ route('admin.lop-hoc.index') }}" class="lh-hero-btn lh-hero-back">
                <i class="fas fa-arrow-left"></i> Danh sách
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="kf-alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="kf-alert-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    {{-- ── Summary stats ─────────────────────────────────────── --}}
    <div class="lh-summary-grid">
        <div class="lh-sum-card">
            <label><i class="fas fa-users me-1"></i> Học viên đăng ký</label>
            <div class="lh-sum-val" style="color:#7c3aed">{{ $soHocVienDangKy }}</div>
        </div>
        <div class="lh-sum-card">
            <label><i class="fas fa-calendar-check me-1"></i> Buổi đã học</label>
            <div class="lh-sum-val" style="color:#16a34a">{{ $soBuoiDaHoc }}</div>
        </div>
        <div class="lh-sum-card">
            <label><i class="fas fa-calendar me-1"></i> Buổi còn lại</label>
            <div class="lh-sum-val" style="color:#d97706">{{ $soBuoiChuaHoc }}</div>
        </div>
        <div class="lh-sum-card">
            <label><i class="fas fa-list-ol me-1"></i> Tổng buổi học</label>
            <div class="lh-sum-val">{{ $lopHoc->buoiHocs->count() }}</div>
        </div>
        @if ($lopHoc->soHocVienToiDa)
            <div class="lh-sum-card">
                <label><i class="fas fa-user-check me-1"></i> Sĩ số (đăng ký/tối đa)</label>
                <div class="lh-sum-val">
                    <span style="color:#7c3aed">{{ $soHocVienDangKy }}</span>
                    <span style="font-size:1rem;color:#94a3b8"> / {{ $lopHoc->soHocVienToiDa }}</span>
                </div>
            </div>
        @endif
    </div>

    {{-- ── Chi tiết lớp học ─────────────────────────────────── --}}
    <div class="lh-detail-grid">
        <div class="kf-card">
            <div class="kf-card-title"><i class="fas fa-info-circle"></i> Thông tin lớp học</div>
            <table class="lh-detail-table">
                <tr>
                    <td>Khóa học</td>
                    <td>
                        @if ($lopHoc->khoaHoc)
                            <a href="{{ route('admin.khoa-hoc.show', $lopHoc->khoaHoc->slug) }}"
                                style="color:#0f766e;font-weight:600;text-decoration:none">
                                {{ $lopHoc->khoaHoc->tenKhoaHoc }}
                            </a>
                        @else
                            —
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Cơ sở</td>
                    <td>{{ $lopHoc->coSo?->tenCoSo ?? '—' }}</td>
                </tr>
                <tr>
                    <td>Phòng học</td>
                    <td>{{ $lopHoc->phongHoc?->tenPhong ?? '—' }}</td>
                </tr>
                <tr>
                    <td>Ca học</td>
                    <td>
                        @if ($lopHoc->caHoc)
                            {{ $lopHoc->caHoc->tenCa }}
                            <small style="color:#94a3b8">({{ $lopHoc->caHoc->gioBatDau }} –
                                {{ $lopHoc->caHoc->gioKetThuc }})</small>
                        @else
                            —
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Giáo viên</td>
                    <td>{{ $lopHoc->taiKhoan?->hoSoNguoiDung?->hoTen ?? '—' }}</td>
                </tr>
                <tr>
                    <td>Lịch học</td>
                    <td>
                        @if ($lopHoc->lichHoc)
                            @php $thuMap = ['2'=>'Thứ 2','3'=>'Thứ 3','4'=>'Thứ 4','5'=>'Thứ 5','6'=>'Thứ 6','7'=>'Thứ 7','CN'=>'Chủ Nhật']; @endphp
                            @foreach (array_map('trim', explode(',', $lopHoc->lichHoc)) as $thu)
                                <span
                                    style="background:#ede9fe;color:#7c3aed;padding:2px 8px;border-radius:4px;font-size:.75rem;font-weight:600;display:inline-block;margin:1px">{{ $thuMap[$thu] ?? $thu }}</span>
                            @endforeach
                        @else
                            —
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Ngày bắt đầu</td>
                    <td>{{ $lopHoc->ngayBatDau ? \Carbon\Carbon::parse($lopHoc->ngayBatDau)->format('d/m/Y') : '—' }}</td>
                </tr>
                <tr>
                    <td>Ngày kết thúc</td>
                    <td>{{ $lopHoc->ngayKetThuc ? \Carbon\Carbon::parse($lopHoc->ngayKetThuc)->format('d/m/Y') : '—' }}
                    </td>
                </tr>
                @if ($lopHoc->donGiaDay)
                    <tr>
                        <td>Đơn giá dạy</td>
                        <td style="font-weight:600;color:#7c3aed">{{ number_format($lopHoc->donGiaDay, 0, ',', '.') }}
                            đ/buổi</td>
                    </tr>
                @endif
            </table>
        </div>

        {{-- ── Danh sách học viên ───────────────────────────────── --}}
        <div class="kf-card">
            <div class="kf-card-title">
                <span><i class="fas fa-users"></i> Học viên đăng ký ({{ $soHocVienDangKy }})</span>
            </div>
            @if ($lopHoc->dangKyLopHocs->isEmpty())
                <div style="text-align:center;padding:20px;color:#94a3b8;font-size:.85rem">
                    <i class="fas fa-user-slash" style="font-size:1.5rem;margin-bottom:8px;display:block;opacity:.3"></i>
                    Chưa có học viên đăng ký
                </div>
            @else
                <div style="max-height:300px;overflow-y:auto">
                    @foreach ($lopHoc->dangKyLopHocs as $dk)
                        <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #f1f5f9">
                            <div
                                style="width:32px;height:32px;border-radius:50%;background:#ede9fe;color:#7c3aed;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0">
                                {{ strtoupper(substr($dk->taiKhoan?->hoSoNguoiDung?->hoTen ?? '?', 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-size:.85rem;font-weight:600;color:#1e293b">
                                    {{ $dk->taiKhoan?->hoSoNguoiDung?->hoTen ?? ($dk->taiKhoan?->taiKhoan ?? '—') }}
                                </div>
                                <div style="font-size:.72rem;color:#94a3b8">
                                    {{ $dk->ngayDangKy ? \Carbon\Carbon::parse($dk->ngayDangKy)->format('d/m/Y') : '' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ── Học phí ────────────────────────────────────────────── --}}
    @if ($lopHoc->hocPhi)
        @php
            $hp = $lopHoc->hocPhi;
            $tongHocPhi = $hp->tongHocPhi; // Doanh thu 1 HV
            $tongDoanhThu = $tongHocPhi * $soHocVienDangKy; // Doanh thu thực tế
            $soBuoiThucHien = $lopHoc->buoiHocs->count();
            $total_HVDuKien = $lopHoc->soHocVienToiDa ?? $soHocVienDangKy;
            $chiPhiGV = ($lopHoc->donGiaDay ?? 0) * $soBuoiThucHien; // Chi phí GV thực tế
            $loiNhuan = $tongDoanhThu - $chiPhiGV;
        @endphp
        <div class="kf-card" style="margin-bottom:22px">
            <div class="kf-card-title"><i class="fas fa-file-invoice-dollar"></i> Tổng kết học phí</div>

            {{-- Gói học phí --}}
            <div
                style="background:linear-gradient(135deg,#7c3aed,#a78bfa);border-radius:10px;padding:16px 20px;color:#fff;display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:16px">
                <div>
                    <div style="font-size:.7rem;font-weight:700;opacity:.8;text-transform:uppercase">Gói học phí</div>
                    <div style="font-size:1.1rem;font-weight:700;margin-top:4px">{{ $hp->soBuoi }} buổi</div>
                    <div style="font-size:.78rem;opacity:.8">{{ number_format($hp->donGia, 0, ',', '.') }} đ/buổi</div>
                </div>
                <div>
                    <div style="font-size:.7rem;font-weight:700;opacity:.8;text-transform:uppercase">HV đóng/người</div>
                    <div style="font-size:1.3rem;font-weight:700;color:#fde68a;margin-top:4px">
                        {{ number_format($tongHocPhi, 0, ',', '.') }} đ</div>
                </div>
                <div>
                    <div style="font-size:.7rem;font-weight:700;opacity:.8;text-transform:uppercase">Doanh thu thực tế</div>
                    <div style="font-size:1.3rem;font-weight:700;color:#a7f3d0;margin-top:4px">
                        {{ number_format($tongDoanhThu, 0, ',', '.') }} đ</div>
                    <div style="font-size:.75rem;opacity:.75">{{ $soHocVienDangKy }} học viên đã đăng ký</div>
                </div>
            </div>

            {{-- Chi phí & Lợi nhuận --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px">
                <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:12px 14px">
                    <div style="font-size:.72rem;font-weight:700;color:#c2410c;text-transform:uppercase">Chi phí giáo viên
                    </div>
                    <div style="font-size:1.2rem;font-weight:700;color:#ea580c;margin-top:4px">
                        {{ number_format($chiPhiGV, 0, ',', '.') }} đ
                    </div>
                    <div style="font-size:.72rem;color:#c2410c;margin-top:2px">
                        {{ $soBuoiThucHien }} buổi × {{ number_format($lopHoc->donGiaDay ?? 0, 0, ',', '.') }} đ/buổi
                    </div>
                </div>

                <div
                    style="background:{{ $loiNhuan >= 0 ? '#f0fdf4' : '#fff1f2' }};border:1px solid {{ $loiNhuan >= 0 ? '#bbf7d0' : '#fecdd3' }};border-radius:8px;padding:12px 14px">
                    <div
                        style="font-size:.72rem;font-weight:700;color:{{ $loiNhuan >= 0 ? '#16a34a' : '#dc2626' }};text-transform:uppercase">
                        Lợi nhuận ước tính</div>
                    <div
                        style="font-size:1.2rem;font-weight:700;color:{{ $loiNhuan >= 0 ? '#15803d' : '#dc2626' }};margin-top:4px">
                        {{ ($loiNhuan >= 0 ? '+' : '') . number_format($loiNhuan, 0, ',', '.') }} đ
                    </div>
                    <div style="font-size:.72rem;color:{{ $loiNhuan >= 0 ? '#16a34a' : '#dc2626' }};margin-top:2px">
                        Doanh thu − Chi phí GV
                    </div>
                </div>

                @if ($lopHoc->donGiaDay)
                    <div style="background:#f5f3ff;border:1px solid #ddd6fe;border-radius:8px;padding:12px 14px">
                        <div style="font-size:.72rem;font-weight:700;color:#7c3aed;text-transform:uppercase">Đơn giá dạy GV
                        </div>
                        <div style="font-size:1.2rem;font-weight:700;color:#7c3aed;margin-top:4px">
                            {{ number_format($lopHoc->donGiaDay, 0, ',', '.') }} đ/buổi
                        </div>
                        <div style="font-size:.72rem;color:#7c3aed;margin-top:2px">
                            Chênh lệch: {{ number_format($hp->donGia - $lopHoc->donGiaDay, 0, ',', '.') }} đ/buổi
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ── Auto generate buổi học ────────────────────────────── --}}
    @if ($lopHoc->lichHoc && $lopHoc->ngayBatDau && $lopHoc->ngayKetThuc)
        <div class="auto-gen-card">
            <h3><i class="fas fa-magic me-2"></i> Tự động tạo buổi học</h3>
            <p>Tạo tự động các buổi học theo lịch
                <strong>Thứ {{ implode(', ', array_map('trim', explode(',', $lopHoc->lichHoc))) }}</strong>
                từ <strong>{{ \Carbon\Carbon::parse($lopHoc->ngayBatDau)->format('d/m/Y') }}</strong>
                đến <strong>{{ \Carbon\Carbon::parse($lopHoc->ngayKetThuc)->format('d/m/Y') }}</strong>.
            </p>
            <form action="{{ route('admin.buoi-hoc.auto-generate', $lopHoc->lopHocId) }}" method="POST"
                style="display:inline">
                @csrf
                <div class="auto-gen-row">
                    <label class="auto-gen-check">
                        <input type="checkbox" name="xoa_cu" value="1">
                        <span style="opacity:.9">Xóa buổi học chưa hoàn thành trước khi tạo mới</span>
                    </label>
                    <button type="submit" class="btn-auto-gen"
                        onclick="return confirm('Tự động tạo buổi học từ lịch học?')">
                        <i class="fas fa-magic"></i> Tự động tạo
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- ── Danh sách buổi học ────────────────────────────────── --}}
    <div class="kf-card">
        <div class="kf-card-title" style="justify-content:space-between">
            <span><i class="fas fa-calendar-days"></i> Danh sách buổi học ({{ $lopHoc->buoiHocs->count() }})</span>
            <button type="button" onclick="toggleAddForm()"
                style="font-size:.82rem;background:#f5f3ff;color:#7c3aed;padding:5px 12px;border-radius:6px;border:none;cursor:pointer;font-weight:600">
                <i class="fas fa-plus"></i> Thêm thủ công
            </button>
        </div>

        {{-- Form thêm buổi học thủ công --}}
        <div class="add-bh-form" id="addBhForm">
            <form action="{{ route('admin.buoi-hoc.store') }}" method="POST">
                @csrf
                <input type="hidden" name="lopHocId" value="{{ $lopHoc->lopHocId }}">
                <div class="form-grid">
                    <div>
                        <label style="font-size:.78rem;font-weight:600;color:#64748b;display:block;margin-bottom:4px">
                            Tên buổi học
                        </label>
                        <input type="text" name="tenBuoiHoc" placeholder="Để trống = tự đặt tên">
                    </div>
                    <div>
                        <label style="font-size:.78rem;font-weight:600;color:#64748b;display:block;margin-bottom:4px">
                            Ngày học <span style="color:#dc2626">*</span>
                        </label>
                        <input type="date" name="ngayHoc" required>
                    </div>
                    <div>
                        <label style="font-size:.78rem;font-weight:600;color:#64748b;display:block;margin-bottom:4px">
                            Ca học <span style="color:#dc2626">*</span>
                        </label>
                        <select name="caHocId" required>
                            <option value="">-- Chọn ca --</option>
                            @foreach ($caHocs as $ca)
                                <option value="{{ $ca->caHocId }}"
                                    {{ $ca->caHocId == $lopHoc->caHocId ? 'selected' : '' }}>
                                    {{ $ca->tenCa }} ({{ $ca->gioBatDau }}–{{ $ca->gioKetThuc }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="font-size:.78rem;font-weight:600;color:#64748b;display:block;margin-bottom:4px">Phòng
                            học</label>
                        <select name="phongHocId">
                            <option value="">-- Tùy chọn --</option>
                            @foreach ($phongHocs as $ph)
                                <option value="{{ $ph->phongHocId }}"
                                    {{ $ph->phongHocId == $lopHoc->phongHocId ? 'selected' : '' }}>
                                    {{ $ph->tenPhong }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="font-size:.78rem;font-weight:600;color:#64748b;display:block;margin-bottom:4px">Giáo
                            viên</label>
                        <select name="taiKhoanId">
                            <option value="">-- Tùy chọn --</option>
                            @if ($giaoVienCoSo->count() > 0)
                                <optgroup label="Giáo viên thuộc cơ sở này">
                                    @foreach ($giaoVienCoSo as $gv)
                                        <option value="{{ $gv->taiKhoanId }}"
                                            {{ $gv->taiKhoanId == $lopHoc->taiKhoanId ? 'selected' : '' }}>
                                            {{ $gv->hoSoNguoiDung?->hoTen ?? $gv->taiKhoan }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                            @if ($giaoVienKhac->count() > 0)
                                <optgroup label="Giáo viên cơ sở khác">
                                    @foreach ($giaoVienKhac as $gv)
                                        <option value="{{ $gv->taiKhoanId }}"
                                            {{ $gv->taiKhoanId == $lopHoc->taiKhoanId ? 'selected' : '' }}>
                                            {{ $gv->hoSoNguoiDung?->hoTen ?? $gv->taiKhoan }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>
                    <div style="align-self:flex-end">
                        <button type="submit"
                            style="width:100%;padding:8px;background:linear-gradient(135deg,#7c3aed,#a78bfa);color:#fff;border:none;border-radius:7px;font-weight:600;cursor:pointer">
                            <i class="fas fa-plus me-1"></i> Thêm buổi học
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Danh sách --}}
        @if ($lopHoc->buoiHocs->isEmpty())
            <div style="text-align:center;padding:40px;color:#94a3b8">
                <i class="fas fa-calendar-xmark" style="font-size:2rem;opacity:.25;display:block;margin-bottom:12px"></i>
                <p style="margin:0">Chưa có buổi học nào.<br>
                    <small>Dùng nút <strong>Tự động tạo</strong> ở trên hoặc <strong>Thêm thủ công</strong>.</small>
                </p>
            </div>
        @else
            <div class="bh-timeline" id="bh-timeline">
                @foreach ($lopHoc->buoiHocs->sortBy('ngayHoc') as $i => $bh)
                    @php
                        $thuVN = ['Chủ Nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
                        $ngay = $bh->ngayHoc ? \Carbon\Carbon::parse($bh->ngayHoc) : null;
                        $dayName = $ngay ? $thuVN[$ngay->dayOfWeek] : '';
                    @endphp
                    <div class="bh-item" id="bh-row-{{ $bh->buoiHocId }}">
                        <div class="bh-date">
                            @if ($ngay)
                                {{ $ngay->format('d/m') }}
                                <small>{{ $dayName }}</small>
                            @else
                                —
                            @endif
                        </div>

                        <div class="bh-info">
                            <div class="bh-name">{{ $bh->tenBuoiHoc ?? 'Buổi ' . ($i + 1) }}</div>
                            <div class="bh-sub">
                                @if ($bh->caHoc)
                                    <span><i class="fas fa-clock"></i> {{ $bh->caHoc->tenCa }}</span>
                                @endif
                                @if ($bh->phongHoc)
                                    <span><i class="fas fa-door-open"></i> {{ $bh->phongHoc->tenPhong }}</span>
                                @endif
                                @if ($bh->taiKhoan)
                                    <span><i class="fas fa-user"></i> {{ $bh->taiKhoan->hoSoNguoiDung?->hoTen }}</span>
                                @endif
                                @if ($bh->ghiChu)
                                    <span><i class="fas fa-note-sticky"></i> {{ Str::limit($bh->ghiChu, 30) }}</span>
                                @endif
                            </div>
                            <div style="margin-top:4px;display:flex;gap:5px;flex-wrap:wrap">
                                @if ($bh->daHoanThanh)
                                    <span class="bh-badge bh-done"><i class="fas fa-check"></i> Đã xong</span>
                                @else
                                    <span class="bh-badge bh-todo"><i class="fas fa-clock"></i> Chưa học</span>
                                @endif
                                @if ($bh->daDiemDanh)
                                    <span class="bh-badge bh-att"><i class="fas fa-clipboard-check"></i> Điểm danh</span>
                                @endif
                                @php
                                    $ttBuoi = [
                                        0 => [
                                            'cls' => 'bh-tt-ly-thuyet',
                                            'icon' => 'fa-book-open',
                                            'lbl' => 'Lý thuyết',
                                        ],
                                        1 => ['cls' => 'bh-tt-thuc-hanh', 'icon' => 'fa-flask', 'lbl' => 'Thực hành'],
                                        2 => ['cls' => 'bh-tt-truc-tuyen', 'icon' => 'fa-wifi', 'lbl' => 'Trực tuyến'],
                                        3 => [
                                            'cls' => 'bh-tt-lich-thi',
                                            'icon' => 'fa-pencil-alt',
                                            'lbl' => 'Lịch thi',
                                        ],
                                        4 => [
                                            'cls' => 'bh-tt-tam-ngung',
                                            'icon' => 'fa-pause-circle',
                                            'lbl' => 'Tạm ngưng',
                                        ],
                                    ];
                                    $bhTT = $ttBuoi[$bh->trangThai ?? 0] ?? $ttBuoi[0];
                                @endphp
                                <span class="bh-badge {{ $bhTT['cls'] }}">
                                    <i class="fas {{ $bhTT['icon'] }}"></i> {{ $bhTT['lbl'] }}
                                </span>
                            </div>
                        </div>

                        <div class="bh-actions">
                            <button type="button" class="lh-btn-action lh-btn-edit" title="Chỉnh sửa buổi học"
                                onclick='openEditModal(
                                    {{ $bh->buoiHocId }},
                                    @js($bh->tenBuoiHoc ?? ""),
                                    @js($bh->ngayHoc),
                                    {{ $bh->caHocId ?? 'null' }},
                                    {{ $bh->phongHocId ?? 'null' }},
                                    {{ $bh->taiKhoanId ?? 'null' }},
                                    {{ $bh->trangThai ?? 0 }},
                                    @js($bh->ghiChu ?? ""),
                                    {{ $bh->daHoanThanh ? 1 : 0 }}
                                )'>
                                <i class="fas fa-pen"></i>
                            </button>
                            <button type="button" class="lh-btn-action lh-btn-edit" title="Đánh dấu hoàn thành"
                                onclick="toggleHoanThanh({{ $bh->buoiHocId }}, {{ $bh->daHoanThanh ? 0 : 1 }})"
                                style="width:auto;padding:0 10px;font-size:.72rem;gap:4px;color:{{ $bh->daHoanThanh ? '#16a34a' : '#d97706' }}">
                                <i class="fas fa-{{ $bh->daHoanThanh ? 'check-circle' : 'circle' }}"></i>
                            </button>
                            <button type="button" class="lh-btn-action lh-btn-del" title="Xóa buổi học"
                                onclick='deleteBuoiHoc({{ $bh->buoiHocId }}, @js($bh->tenBuoiHoc ?? ("Buổi " . ($i + 1))))'>
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>


    {{-- Hidden forms --}}
    <form id="delete-bh-form" method="POST" style="display:none">
        @csrf @method('DELETE')
    </form>
    <form id="update-bh-form" method="POST" style="display:none">
        @csrf @method('PUT')
        <input type="hidden" name="daHoanThanh" id="update-bh-value">
    </form>

    {{-- Modal Edit Buổi Học (Custom, không dùng Bootstrap modal class) --}}
    <div id="editBuoiHocModal"
        style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;background:rgba(0,0,0,.55);">
        <div
            style="background:#fff;border-radius:14px;width:100%;max-width:680px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.25);margin:16px;">
            <div
                style="background:linear-gradient(135deg,#4c1d95,#7c3aed);border-radius:14px 14px 0 0;padding:18px 24px;display:flex;align-items:center;justify-content:space-between;">
                <h5 style="color:#fff;font-weight:700;margin:0;font-size:1rem;">
                    <i class="fas fa-pen me-2"></i>Chỉnh sửa buổi học
                </h5>
                <button type="button" onclick="closeEditModal()"
                    style="background:none;border:none;color:#fff;font-size:1.3rem;cursor:pointer;padding:0;line-height:1;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            {{-- Body --}}
            <div style="padding:24px 24px 8px;">
                {{-- Tên buổi học full width --}}
                <div style="margin-bottom:16px;">
                    <label
                        style="display:block;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Tên
                        buổi học</label>
                    <input type="text" id="ebh-ten"
                        style="width:100%;box-sizing:border-box;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.9rem;color:#1e293b;outline:none;"
                        placeholder="Để trống = tự đặt" onfocus="this.style.borderColor='#7c3aed'"
                        onblur="this.style.borderColor='#e2e8f0'">
                </div>
                {{-- Grid 2 cột --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;">
                    <div>
                        <label
                            style="display:block;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Ngày
                            học <span style="color:#dc2626">*</span></label>
                        <input type="date" id="ebh-ngay" required
                            style="width:100%;box-sizing:border-box;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.9rem;color:#1e293b;outline:none;"
                            onfocus="this.style.borderColor='#7c3aed'" onblur="this.style.borderColor='#e2e8f0'">
                    </div>
                    <div>
                        <label
                            style="display:block;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Ca
                            học <span style="color:#dc2626">*</span></label>
                        <select id="ebh-ca"
                            style="width:100%;box-sizing:border-box;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.9rem;color:#1e293b;background:#fff;outline:none;">
                            @foreach ($caHocs as $ca)
                                <option value="{{ $ca->caHocId }}">{{ $ca->tenCa }}
                                    ({{ $ca->gioBatDau }}–{{ $ca->gioKetThuc }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label
                            style="display:block;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Phòng
                            học</label>
                        <select id="ebh-phong"
                            style="width:100%;box-sizing:border-box;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.9rem;color:#1e293b;background:#fff;outline:none;">
                            <option value="">-- Không chọn --</option>
                            @foreach ($phongHocs as $ph)
                                <option value="{{ $ph->phongHocId }}">{{ $ph->tenPhong }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label
                            style="display:block;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Giáo
                            viên</label>
                        <select id="ebh-gv"
                            style="width:100%;box-sizing:border-box;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.9rem;color:#1e293b;background:#fff;outline:none;">
                            <option value="">-- Không chọn --</option>
                            @if ($giaoVienCoSo->count() > 0)
                                <optgroup label="Giáo viên thuộc cơ sở này">
                                    @foreach ($giaoVienCoSo as $gv)
                                        <option value="{{ $gv->taiKhoanId }}">
                                            {{ $gv->hoSoNguoiDung?->hoTen ?? $gv->taiKhoan }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                            @if ($giaoVienKhac->count() > 0)
                                <optgroup label="Giáo viên cơ sở khác">
                                    @foreach ($giaoVienKhac as $gv)
                                        <option value="{{ $gv->taiKhoanId }}">
                                            {{ $gv->hoSoNguoiDung?->hoTen ?? $gv->taiKhoan }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                    </div>
                    <div>
                        <label
                            style="display:block;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Loại
                            buổi học</label>
                        <select id="ebh-trangthai"
                            style="width:100%;box-sizing:border-box;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.9rem;color:#1e293b;background:#fff;outline:none;">
                            <option value="0">📖 Lý thuyết</option>
                            <option value="1">🔬 Thực hành</option>
                            <option value="2">💻 Trực tuyến</option>
                            <option value="3">✏️ Lịch thi</option>
                            <option value="4">⏸️ Tạm ngưng</option>
                        </select>
                    </div>
                    <div>
                        <label
                            style="display:block;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Trạng
                            thái hoàn thành</label>
                        <select id="ebh-hoanhthanh"
                            style="width:100%;box-sizing:border-box;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.9rem;color:#1e293b;background:#fff;outline:none;">
                            <option value="0">⏳ Chưa học</option>
                            <option value="1">✅ Đã hoàn thành</option>
                        </select>
                    </div>
                </div>
                {{-- Ghi chú full width --}}
                <div style="margin-bottom:8px;">
                    <label
                        style="display:block;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Ghi
                        chú</label>
                    <textarea id="ebh-ghichu" rows="2"
                        style="width:100%;box-sizing:border-box;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.9rem;color:#1e293b;outline:none;resize:vertical;"
                        placeholder="Ghi chú thêm..." onfocus="this.style.borderColor='#7c3aed'"
                        onblur="this.style.borderColor='#e2e8f0'"></textarea>
                </div>
            </div>
            {{-- Footer --}}
            <div style="padding:16px 24px;border-top:1px solid #f1f5f9;display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" onclick="closeEditModal()"
                    style="padding:9px 20px;border:1.5px solid #e2e8f0;border-radius:8px;background:#fff;color:#64748b;font-size:.875rem;font-weight:600;cursor:pointer;">
                    Hủy
                </button>
                <button type="button" id="ebh-save-btn" onclick="saveEditBuoiHoc()"
                    style="padding:9px 22px;border:none;border-radius:8px;background:linear-gradient(135deg,#7c3aed,#a78bfa);color:#fff;font-size:.875rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                    <i class="fas fa-save"></i> Lưu thay đổi
                </button>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        function toggleAddForm() {
            const f = document.getElementById('addBhForm');
            f.style.display = f.style.display === 'block' ? 'none' : 'block';
        }

        function deleteBuoiHoc(id, name) {
            Swal.fire({
                title: 'Xóa buổi học?',
                html: `Xóa buổi <strong>${name}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-trash me-1"></i> Xóa',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
            }).then(r => {
                if (r.isConfirmed) {
                    const form = document.getElementById('delete-bh-form');
                    form.action = `/admin/buoi-hoc/${id}`;
                    form.submit();
                }
            });
        }

        function toggleHoanThanh(id, newVal) {
            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('_method', 'PUT');
            fd.append('daHoanThanh', newVal);
            fetch(`/admin/buoi-hoc/${id}`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json'
                },
                body: fd,
            }).then(r => r.json()).then(data => {
                if (data.success) location.reload();
            }).catch(() => {
                location.reload();
            });
        }


        /* ── EDIT MODAL ──────────────────────────────────────── */
        let _editBuoiHocId = null;

        function openEditModal(id, ten, ngay, caId, phongId, gvId, trangThai, ghiChu, hoanThanh) {
            _editBuoiHocId = id;
            document.getElementById('ebh-ten').value = ten || '';
            document.getElementById('ebh-ngay').value = ngay || '';
            document.getElementById('ebh-ghichu').value = ghiChu || '';

            setSelectVal('ebh-ca', caId);
            setSelectVal('ebh-phong', phongId);
            setSelectVal('ebh-gv', gvId);
            setSelectVal('ebh-trangthai', trangThai);
            setSelectVal('ebh-hoanhthanh', hoanThanh);

            // Hiện custom modal
            const m = document.getElementById('editBuoiHocModal');
            m.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeEditModal() {
            const m = document.getElementById('editBuoiHocModal');
            m.style.display = 'none';
            document.body.style.overflow = '';
        }

        // Đóng modal khi click backdrop
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('editBuoiHocModal').addEventListener('click', function(e) {
                if (e.target === this) closeEditModal();
            });
        });

        function setSelectVal(id, val) {
            const sel = document.getElementById(id);
            if (!sel) return;
            const opt = sel.querySelector(`option[value="${val}"]`);
            if (opt) opt.selected = true;
            else if (sel.options.length > 0) {
                // try setting by value directly
                sel.value = (val !== null && val !== undefined) ? String(val) : '';
            }
        }

        function saveEditBuoiHoc() {
            const btn = document.getElementById('ebh-save-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Đang lưu...';

            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('_method', 'PUT');
            fd.append('tenBuoiHoc', document.getElementById('ebh-ten').value);
            fd.append('ngayHoc', document.getElementById('ebh-ngay').value);
            fd.append('caHocId', document.getElementById('ebh-ca').value);
            const phong = document.getElementById('ebh-phong').value;
            const gv = document.getElementById('ebh-gv').value;
            if (phong) fd.append('phongHocId', phong);
            if (gv) fd.append('taiKhoanId', gv);
            fd.append('trangThai', document.getElementById('ebh-trangthai').value);
            fd.append('daHoanThanh', document.getElementById('ebh-hoanhthanh').value);
            fd.append('ghiChu', document.getElementById('ebh-ghichu').value);

            fetch(`/admin/buoi-hoc/${_editBuoiHocId}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: fd,
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        closeEditModal();
                        location.reload();
                    } else {
                        alert('Có lỗi xảy ra: ' + (data.message || 'Thử lại sau.'));
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-save me-1"></i> Lưu thay đổi';
                    }
                })
                .catch(() => {
                    location.reload();
                });
        }
    </script>
@endsection
