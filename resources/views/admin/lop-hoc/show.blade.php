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

        .lh-hero-merge {
            background: #f59e0b;
            color: #fff;
        }

        .lh-hero-merge:hover {
            background: #d97706;
            color: #fff;
        }

        .lh-hero-merge:disabled {
            background: rgba(245, 158, 11, .4);
            cursor: not-allowed;
            opacity: .7;
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
        .bh-tt-sap-dien-ra {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .bh-tt-dang-dien-ra {
            background: #f0fdf4;
            color: #15803d;
        }

        .bh-tt-da-hoan-thanh {
            background: #ecfdf5;
            color: #047857;
        }

        .bh-tt-da-huy {
            background: #fef2f2;
            color: #b91c1c;
        }

        .bh-tt-doi-lich {
            background: #fff7ed;
            color: #c2410c;
        }

        /* ── Modern Modal ───────────────────────────────── */
        .kf-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, .6);
            backdrop-filter: blur(4px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            padding: 20px;
        }

        .kf-modal {
            background: #fff;
            border-radius: 16px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: modalFadeIn .3s ease-out;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .kf-modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
        }

        .kf-modal-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .kf-modal-close {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            color: #64748b;
            background: #f1f5f9;
            border: none;
            cursor: pointer;
            font-size: 1.25rem;
            transition: all .2s;
        }

        .kf-modal-close:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .kf-modal-body {
            padding: 24px;
            overflow-y: auto;
            flex: 1;
        }

        .kf-modal-footer {
            padding: 16px 24px;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .kf-btn {
            padding: 10px 20px;
            border-radius: 10px;
            font-size: .875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid transparent;
        }

        .kf-btn-outline {
            background: #fff;
            color: #64748b;
            border-color: #e2e8f0;
        }

        .kf-btn-outline:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .kf-btn-primary {
            background: #7c3aed;
            color: #fff;
        }

        .kf-btn-primary:hover {
            background: #6d28d9;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(124, 58, 237, 0.25);
        }

        .merge-candidate-item:hover {
            background: #f5f3ff !important;
        }

        .merge-candidate-item input[type="radio"]:checked + div {
            color: #7c3aed !important;
        }

        .merge-candidate-item input[type="radio"]:checked {
            accent-color: #7c3aed;
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
                } elseif ($lopHoc->isClosedForRegistration()) {
                    $lopHocBadgeBg = '#fde68a';
                    $lopHocBadgeText = '#854d0e';
                } elseif ($lopHoc->isInProgress()) {
                    $lopHocBadgeBg = '#dcfce7';
                    $lopHocBadgeText = '#166534';
                } elseif ($lopHoc->isCompleted()) {
                    $lopHocBadgeBg = '#e2e8f0';
                    $lopHocBadgeText = '#334155';
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

            <button type="button" class="lh-hero-btn lh-hero-merge" id="btnOpenMergeModal"
                {{ !$mergeEligible ? 'disabled' : '' }}
                title="{{ !$mergeEligible ? implode('. ', $mergeBlockers) : 'Gộp lớp này vào lớp khác' }}">
                <i class="fas fa-object-group"></i> Gộp lớp
            </button>
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
    @if ($lopHoc->chinhSachGia)
        @php
            $hp = $lopHoc->chinhSachGia;
            $tongHocPhi = (float) $hp->hocPhiNiemYet;
            $tongDoanhThu = $tongHocPhi * $soHocVienDangKy; // Doanh thu thực tế
            $soBuoiThucHien = $lopHoc->buoiHocs->count();
            $chiPhiGV = ($lopHoc->donGiaDay ?? 0) * $soBuoiThucHien; // Chi phí GV thực tế
            $loiNhuan = $tongDoanhThu - $chiPhiGV;
        @endphp
        <div class="kf-card" style="margin-bottom:22px">
            <div class="kf-card-title"><i class="fas fa-file-invoice-dollar"></i> Tổng kết học phí</div>

            {{-- Chính sách giá --}}
            <div
                style="background:linear-gradient(135deg,#7c3aed,#a78bfa);border-radius:10px;padding:16px 20px;color:#fff;display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:16px">
                <div>
                    <div style="font-size:.7rem;font-weight:700;opacity:.8;text-transform:uppercase">Chính sách giá</div>
                    <div style="font-size:1.1rem;font-weight:700;margin-top:4px">{{ $hp->loaiThuLabel }}</div>
                    <div style="font-size:.78rem;opacity:.8">
                        {{ $hp->soBuoiCamKetHieuDung ? $hp->soBuoiCamKetHieuDung . ' buổi cam kết' : 'Không ràng buộc số buổi' }}
                    </div>
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
                    </div>
                @endif
            </div>

            @if ($hp->dotThus->isNotEmpty())
                <div style="margin-top:14px">
                    <div style="font-size:.78rem;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:8px">
                        Kế hoạch thu theo đợt
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px">
                        @foreach ($hp->dotThus as $dotThu)
                            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px 14px">
                                <div style="font-size:.84rem;font-weight:700;color:#1e293b">{{ $dotThu->tenDotThu }}</div>
                                <div style="font-size:1rem;font-weight:700;color:#0f766e;margin-top:4px">
                                    {{ number_format($dotThu->soTien, 0, ',', '.') }} đ
                                </div>
                                <div style="font-size:.72rem;color:#64748b;margin-top:4px">
                                    Hạn thanh toán:
                                    {{ $dotThu->hanThanhToan ? \Carbon\Carbon::parse($dotThu->hanThanhToan)->format('d/m/Y') : 'Chưa đặt' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @elseif ($lopHoc->isOpenForRegistration() || $lopHoc->isClosedForRegistration() || $lopHoc->isInProgress())
        <div class="kf-card" style="margin-bottom:22px;background:#fff7ed;border:1px solid #fdba74">
            <div class="kf-card-title"><i class="fas fa-exclamation-triangle"></i> Thiếu chính sách giá</div>
            <p style="margin:0;color:#9a3412;font-size:.88rem">
                Lớp đang ở trạng thái vận hành nhưng chưa có chính sách giá hợp lệ. Cần cập nhật học phí ngay để đảm bảo
                quy trình tuyển sinh và công nợ.
            </p>
        </div>
    @endif

    {{-- ── Auto generate buổi học ────────────────────────────── --}}
    @if ($lopHoc->lichHoc && $lopHoc->ngayBatDau && $lopHoc->soBuoiDuKien)
        <div class="auto-gen-card">
            <h3><i class="fas fa-magic me-2"></i> Tự động tạo buổi học</h3>
            <p>Tạo tự động các buổi học theo lịch
                <strong>Thứ {{ implode(', ', array_map('trim', explode(',', $lopHoc->lichHoc))) }}</strong>
                bắt đầu từ <strong>{{ \Carbon\Carbon::parse($lopHoc->ngayBatDau)->format('d/m/Y') }}</strong>
                cho đến khi đủ <strong>{{ $lopHoc->soBuoiDuKien }}</strong> buổi dự kiến.
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
                        <label style="font-size:.78rem;font-weight:600;color:#64748b;display:block;margin-bottom:4px">
                            Trạng thái buổi học
                        </label>
                        <select name="trangThai">
                            @foreach (\App\Models\Education\BuoiHoc::trangThaiOptions() as $value => $label)
                                <option value="{{ $value }}"
                                    {{ $value === \App\Models\Education\BuoiHoc::TRANG_THAI_SAP_DIEN_RA ? 'selected' : '' }}>
                                    {{ $label }}
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
                                    <span class="bh-badge bh-todo"><i class="fas fa-clock"></i> Chưa hoàn thành</span>
                                @endif
                                @if ($bh->daDiemDanh)
                                    <span class="bh-badge bh-att"><i class="fas fa-clipboard-check"></i> Điểm danh</span>
                                @endif
                                <span class="bh-badge bh-tt-{{ $bh->trangThaiKey }}">
                                    <i class="fas {{ $bh->trangThaiIcon }}"></i> {{ $bh->trangThaiLabel }}
                                </span>
                            </div>
                        </div>

                        <div class="bh-actions">
                            <button type="button"
                                class="lh-btn-action lh-btn-edit js-edit-bh"
                                title="Chỉnh sửa buổi học"
                                data-id="{{ $bh->buoiHocId }}"
                                data-ten="{{ e($bh->tenBuoiHoc ?? '') }}"
                                data-ngay="{{ e($bh->ngayHoc) }}"
                                data-ca-id="{{ $bh->caHocId ?? '' }}"
                                data-phong-id="{{ $bh->phongHocId ?? '' }}"
                                data-gv-id="{{ $bh->taiKhoanId ?? '' }}"
                                data-trang-thai="{{ $bh->trangThai ?? \App\Models\Education\BuoiHoc::TRANG_THAI_SAP_DIEN_RA }}"
                                data-ghi-chu="{{ e($bh->ghiChu ?? '') }}"
                                data-hoan-thanh="{{ $bh->daHoanThanh ? 1 : 0 }}">

                                <i class="fas fa-pen"></i>
                            </button>
                            <button type="button"
                                class="lh-btn-action lh-btn-edit js-toggle-hoan-thanh"
                                title="Đánh dấu hoàn thành"
                                data-id="{{ $bh->buoiHocId }}"
                                data-new-val="{{ $bh->daHoanThanh ? 0 : 1 }}"
                                style="width:auto;padding:0 10px;font-size:.72rem;gap:4px;color:{{ $bh->daHoanThanh ? '#16a34a' : '#d97706' }}">
                                <i class="fas fa-{{ $bh->daHoanThanh ? 'check-circle' : 'circle' }}"></i>
                            </button>
                            <button type="button"
                                class="lh-btn-action lh-btn-del js-delete-bh"
                                title="Xóa buổi học"
                                data-id="{{ $bh->buoiHocId }}"
                                data-name="{{ e($bh->tenBuoiHoc ?? ('Buổi ' . ($i + 1))) }}">

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
                            style="display:block;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Trạng
                            buổi học</label>
                        <select id="ebh-trangthai"
                            style="width:100%;box-sizing:border-box;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.9rem;color:#1e293b;background:#fff;outline:none;">
                            @foreach (\App\Models\Education\BuoiHoc::trangThaiOptions() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label
                            style="display:block;font-size:.75rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Trạng
                            thái hoàn thành</label>
                        <select id="ebh-hoanhthanh"
                            style="width:100%;box-sizing:border-box;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.9rem;color:#1e293b;background:#fff;outline:none;">
                            <option value="0">Chưa hoàn thành</option>
                            <option value="1">Đã hoàn thành</option>
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
        const BUOI_HOC_UPDATE_URL_TEMPLATE = @js(route('admin.buoi-hoc.update', ['id' => '__ID__']));
        const BUOI_HOC_DELETE_URL_TEMPLATE = @js(route('admin.buoi-hoc.destroy', ['id' => '__ID__']));

        function getBuoiHocActionUrl(template, id) {
            return template.replace('__ID__', String(id));
        }

        function bindBuoiHocActionButtons() {
            document.querySelectorAll('.js-edit-bh').forEach(button => {
                button.addEventListener('click', () => {
                    openEditModal(
                        Number(button.dataset.id),
                        button.dataset.ten || '',
                        button.dataset.ngay || '',
                        button.dataset.caId || '',
                        button.dataset.phongId || '',
                        button.dataset.gvId || '',
                        Number(button.dataset.trangThai || 0),
                        button.dataset.ghiChu || '',
                        Number(button.dataset.hoanThanh || 0)
                    );
                });
            });

            document.querySelectorAll('.js-toggle-hoan-thanh').forEach(button => {
                button.addEventListener('click', () => {
                    toggleHoanThanh(
                        Number(button.dataset.id),
                        Number(button.dataset.newVal)
                    );
                });
            });

            document.querySelectorAll('.js-delete-bh').forEach(button => {
                button.addEventListener('click', () => {
                    deleteBuoiHoc(
                        Number(button.dataset.id),
                        button.dataset.name || ''
                    );
                });
            });
        }

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
                    form.action = getBuoiHocActionUrl(BUOI_HOC_DELETE_URL_TEMPLATE, id);
                    form.submit();
                }
            });
        }

        function toggleHoanThanh(id, newVal) {
            const fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('_method', 'PUT');
            fd.append('daHoanThanh', newVal);
            fetch(getBuoiHocActionUrl(BUOI_HOC_UPDATE_URL_TEMPLATE, id), {
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
            bindBuoiHocActionButtons();
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

            fetch(getBuoiHocActionUrl(BUOI_HOC_UPDATE_URL_TEMPLATE, _editBuoiHocId), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: fd,
                })
                .then(async r => {
                    const data = await r.json().catch(() => ({}));
                    if (!r.ok) {
                        throw new Error(data.message || 'Không thể cập nhật buổi học.');
                    }
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        closeEditModal();
                        location.reload();
                    } else {
                        throw new Error(data.message || 'Thử lại sau.');
                    }
                })
                .catch((error) => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save me-1"></i> Lưu thay đổi';
                    Swal.fire('Lỗi', error.message || 'Không thể cập nhật buổi học.', 'error');
                });
        }
    </script>

    {{-- ── MERGE CLASS MODAL ────────────────────────────────── --}}
    <div id="mergeClassModal" class="kf-modal-overlay">
        <div class="kf-modal" style="max-width:600px">
            <div class="kf-modal-header">
                <h3 class="kf-modal-title">Gộp lớp học</h3>
                <button type="button" class="kf-modal-close" onclick="closeMergeModal()">&times;</button>
            </div>
            <div class="kf-modal-body">
                <div
                    style="background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:12px 14px;color:#92400e;font-size:.84rem;margin-bottom:16px">
                    <i class="fas fa-info-circle me-1"></i>
                    Thao tác này sẽ chuyển toàn bộ học viên có đăng ký hiệu lực sang lớp đích và hủy các buổi học chưa diễn
                    ra của lớp hiện tại. <strong>Hành động không thể hoàn tác.</strong>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
                    <div style="background:#f8fafc;padding:12px;border-radius:8px;border:1px solid #e2e8f0">
                        <label style="font-size:.7rem;font-weight:700;color:#64748b;text-transform:uppercase">Lớp nguồn
                            (Hiện tại)</label>
                        <div style="font-size:.9rem;font-weight:700;color:#1e293b;margin-top:2px">
                            {{ $lopHoc->tenLopHoc }}
                        </div>
                        <div style="font-size:.75rem;color:#7c3aed;margin-top:4px">
                            <i class="fas fa-users"></i> {{ $mergeStats['transferCount'] }} đăng ký sẽ chuyển
                        </div>
                    </div>
                    <div style="background:#f5f3ff;padding:12px;border-radius:8px;border:1px solid #ddd6fe">
                        <label style="font-size:.7rem;font-weight:700;color:#64748b;text-transform:uppercase">Tác động hủy
                            bỏ</label>
                        <div style="font-size:.9rem;font-weight:700;color:#1e293b;margin-top:2px">
                            {{ $mergeStats['cancelSessionsCount'] }} buổi học
                        </div>
                        <div style="font-size:.75rem;color:#dc2626;margin-top:4px">
                            Sẽ được chuyển sang "Đã hủy"
                        </div>
                    </div>
                </div>

                @if ($mergeCandidates->isEmpty())
                    <div style="text-align:center;padding:30px 20px;background:#f8fafc;border-radius:8px;color:#94a3b8">
                        <i class="fas fa-search" style="font-size:2rem;margin-bottom:10px;display:block;opacity:.3"></i>
                        Không tìm thấy lớp học đích phù hợp.<br>
                        <small>Yêu cầu: Cùng khóa học, cùng cơ sở, cùng chính sách giá và còn đủ chỗ.</small>
                    </div>
                @else
                    <form id="mergeClassForm" action="{{ route('admin.lop-hoc.merge', $lopHoc->slug) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <label
                            style="font-size:.84rem;font-weight:700;color:#1e293b;display:block;margin-bottom:10px">Chọn lớp
                            đích sẽ tiếp nhận học viên:</label>
                        <div style="max-height:280px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:8px">
                            @foreach ($mergeCandidates as $candidate)
                                <label
                                    style="display:block;padding:12px 14px;border-bottom:1px solid #f1f5f9;cursor:pointer;margin:0;transition:background .2s"
                                    class="merge-candidate-item">
                                    <div style="display:flex;align-items:flex-start;gap:12px">
                                        <input type="radio" name="targetLopHocId" value="{{ $candidate->lopHocId }}"
                                            style="margin-top:4px" required>
                                        <div style="flex:1">
                                            <div style="font-size:.875rem;font-weight:700;color:#1e293b">
                                                [{{ $candidate->maLopHoc }}] {{ $candidate->tenLopHoc }}
                                            </div>
                                            <div style="font-size:.75rem;color:#64748b;margin-top:3px">
                                                <i class="fas fa-calendar-alt"></i> Bắt đầu:
                                                {{ \Carbon\Carbon::parse($candidate->ngayBatDau)->format('d/m/Y') }}
                                                <span style="margin:0 6px">|</span>
                                                <i class="fas fa-users"></i> Sĩ số:
                                                {{ $candidate->dangKyLopHocs->count() }}/{{ $candidate->soHocVienToiDa }}
                                            </div>
                                            @php
                                                $newTotal = $candidate->dangKyLopHocs->count() + $mergeStats['transferCount'];
                                                $remaining = $candidate->soHocVienToiDa - $candidate->dangKyLopHocs->count();
                                                $needed = $mergeStats['transferCount'];
                                            @endphp
                                            <div style="font-size:.72rem;margin-top:5px;font-weight:600">
                                                @if ($newTotal <= $candidate->soHocVienToiDa)
                                                    <span style="color:#16a34a"><i class="fas fa-check"></i> Sau gộp:
                                                        {{ $newTotal }}/{{ $candidate->soHocVienToiDa }} (Dư
                                                        {{ $candidate->soHocVienToiDa - $newTotal }} chỗ)</span>
                                                @else
                                                    <span style="color:#dc2626"><i class="fas fa-times"></i> Thiếu
                                                        {{ $newTotal - $candidate->soHocVienToiDa }} chỗ</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </form>
                @endif
            </div>
            <div class="kf-modal-footer">
                <button type="button" class="kf-btn kf-btn-outline" onclick="closeMergeModal()">Đóng</button>
                @if ($mergeCandidates->isNotEmpty())
                    <button type="button" class="kf-btn kf-btn-primary" id="btnConfirmMerge"
                        style="background:#f59e0b;border-color:#d97706">
                        <i class="fas fa-check me-1"></i> Xác nhận gộp
                    </button>
                @endif
            </div>
        </div>
    </div>

    <script>
        function openMergeModal() {
            const m = document.getElementById('mergeClassModal');
            m.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeMergeModal() {
            const m = document.getElementById('mergeClassModal');
            m.style.display = 'none';
            document.body.style.overflow = '';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const btnOpen = document.getElementById('btnOpenMergeModal');
            if (btnOpen) {
                btnOpen.addEventListener('click', openMergeModal);
            }

            const btnConfirm = document.getElementById('btnConfirmMerge');
            if (btnConfirm) {
                btnConfirm.addEventListener('click', function() {
                    const selected = document.querySelector('input[name="targetLopHocId"]:checked');
                    if (!selected) {
                        Swal.fire('Thông báo', 'Vui lòng chọn một lớp học đích.', 'info');
                        return;
                    }

                    Swal.fire({
                        title: 'Xác nhận gộp lớp?',
                        text: "Hành động này sẽ chuyển học viên sang lớp mới và HỦY các buổi học của lớp này. Không thể hoàn tác!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Tôi đồng ý, tiến hành gộp',
                        cancelButtonText: 'Hủy',
                        confirmButtonColor: '#f59e0b',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('mergeClassForm').submit();
                        }
                    });
                });
            }

            // Backdrop click close
            document.getElementById('mergeClassModal').addEventListener('click', function(e) {
                if (e.target === this) closeMergeModal();
            });
        });
    </script>
@endsection
