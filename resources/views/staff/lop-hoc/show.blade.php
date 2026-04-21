@extends('layouts.internal')

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

        .enrollment-hub {
            display: grid;
            grid-template-columns: 1.15fr .95fr;
            gap: 18px;
            margin-bottom: 22px;
        }

        .enrollment-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 18px 20px;
            box-shadow: 0 1px 4px rgba(15, 23, 42, .06);
        }

        .enrollment-card h3 {
            margin: 0 0 6px;
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
        }

        .enrollment-card p {
            margin: 0 0 14px;
            font-size: .83rem;
            color: #64748b;
        }

        .enrollment-search {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            margin-bottom: 14px;
        }

        .enrollment-search input,
        .enrollment-card select {
            width: 100%;
            min-height: 42px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            padding: 0 12px;
            font-size: .85rem;
        }

        .enrollment-result-list,
        .promotion-student-list {
            display: grid;
            gap: 10px;
            max-height: 320px;
            overflow-y: auto;
        }

        .enrollment-result-item,
        .promotion-student-item {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 14px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
            background: #f8fafc;
        }

        .enrollment-result-item button {
            margin-left: auto;
            border: none;
            background: #ede9fe;
            color: #6d28d9;
            border-radius: 999px;
            padding: 7px 12px;
            font-size: .78rem;
            font-weight: 700;
            cursor: pointer;
        }

        .selected-student-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 12px 0;
        }

        .selected-student-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #eef2ff;
            color: #4338ca;
            border-radius: 999px;
            padding: 7px 12px;
            font-size: .8rem;
            font-weight: 700;
        }

        .selected-student-chip button {
            border: none;
            background: transparent;
            color: inherit;
            cursor: pointer;
            padding: 0;
        }

        .enrollment-form-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            margin-top: 14px;
        }

        .btn-enroll-primary {
            min-height: 42px;
            border: none;
            border-radius: 10px;
            padding: 0 16px;
            background: linear-gradient(135deg, #4338ca, #7c3aed);
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }

        .promotion-toolbar {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            margin-bottom: 12px;
        }

        .hint-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f8fafc;
            color: #475569;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: .76rem;
            font-weight: 700;
        }

        .hub-feedback {
            margin-top: 12px;
            padding: 10px 12px;
            border-radius: 10px;
            font-size: .8rem;
        }

        .inline-create-card {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px dashed #cbd5e1;
        }

        .inline-create-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .inline-create-grid .full {
            grid-column: 1 / -1;
        }

        .inline-create-grid input,
        .inline-create-grid select,
        .inline-create-grid textarea {
            width: 100%;
            min-height: 42px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            padding: 10px 12px;
            font-size: .84rem;
        }

        .inline-create-grid textarea {
            min-height: 92px;
            resize: vertical;
        }

        .inline-create-label {
            display: block;
            font-size: .78rem;
            font-weight: 700;
            color: #334155;
            margin-bottom: 6px;
        }

        .inline-create-note {
            margin: 10px 0 0;
            font-size: .78rem;
            color: #64748b;
        }

        .hub-action-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 14px;
        }

        .hub-mini-card {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px;
            background: linear-gradient(180deg, #ffffff, #f8fafc);
        }

        .hub-mini-card h4 {
            margin: 0 0 6px;
            font-size: .92rem;
            color: #0f172a;
        }

        .hub-mini-card p {
            margin: 0 0 12px;
            font-size: .8rem;
            color: #64748b;
        }

        .btn-enroll-secondary {
            min-height: 40px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #334155;
            font-weight: 700;
            padding: 0 14px;
            cursor: pointer;
        }

        .portal-modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9998;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, .58);
            padding: 18px;
        }

        .portal-modal.is-open {
            display: flex;
        }

        .portal-modal-dialog {
            width: min(920px, 100%);
            max-height: 90vh;
            overflow: auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .28);
        }

        .portal-modal-header {
            padding: 18px 22px;
            color: #fff;
            background: linear-gradient(135deg, #312e81, #7c3aed);
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
        }

        .portal-modal-header h3 {
            margin: 0 0 4px;
            font-size: 1rem;
            font-weight: 800;
        }

        .portal-modal-header p {
            margin: 0;
            color: rgba(255, 255, 255, .82);
            font-size: .82rem;
        }

        .portal-modal-close {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 999px;
            background: rgba(255, 255, 255, .16);
            color: #fff;
            cursor: pointer;
            font-size: 1rem;
        }

        .portal-modal-body {
            padding: 20px 22px 22px;
        }

        .session-hint-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 12px;
        }

        .session-hint-row .hint-badge {
            background: #eef2ff;
            color: #4338ca;
        }

        .hub-feedback.warning {
            background: #fff7ed;
            border: 1px solid #fdba74;
            color: #9a3412;
        }

        @media(max-width: 992px) {
            .enrollment-hub {
                grid-template-columns: 1fr;
            }

            .inline-create-grid {
                grid-template-columns: 1fr;
            }

            .hub-action-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $portalRouteBase = request()->routeIs('staff.*') ? 'staff' : 'admin';
    @endphp

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
            <a href="{{ route($portalRouteBase . '.lop-hoc.edit', $lopHoc->slug) }}" class="lh-hero-btn lh-hero-edit">
                <i class="fas fa-pen"></i> Chỉnh sửa
            </a>
            @if ($lopHoc->khoaHoc)
                <a href="{{ route('admin.khoa-hoc.show', $lopHoc->khoaHoc->slug) }}" class="lh-hero-btn lh-hero-kh">
                    <i class="fas fa-graduation-cap"></i> Xem khóa học
                </a>
            @endif
            <a href="{{ route($portalRouteBase . '.lop-hoc.index') }}" class="lh-hero-btn lh-hero-back">
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
    @if (session('registrationErrors'))
        <div class="hub-feedback warning">
            <strong>Một số học viên chưa được thêm:</strong>
            {{ collect(session('registrationErrors'))->pluck('message')->filter()->implode(' • ') }}
        </div>
    @endif
    @if (session('promotionErrors'))
        <div class="hub-feedback warning">
            <strong>Một số học viên chưa được lên lớp tiếp theo:</strong>
            {{ collect(session('promotionErrors'))->pluck('message')->filter()->implode(' • ') }}
        </div>
    @endif

    <div class="enrollment-hub">
        <div class="enrollment-card">
            <div class="promotion-toolbar">
                <div>
                    <h3>Ghi danh nhanh vào lớp</h3>
                    <p>Tìm học viên theo tên, tài khoản, email hoặc số điện thoại rồi thêm thẳng vào lớp hiện tại mà không cần đi qua màn đăng ký rời.</p>
                </div>
                <a href="{{ route($portalRouteBase . '.dang-ky.create', ['lopHocId' => $lopHoc->lopHocId]) }}" class="hint-badge">
                    <i class="fas fa-up-right-from-square"></i> Mở form đầy đủ
                </a>
            </div>

            <div class="enrollment-search">
                <input type="search" id="studentSearchInput" placeholder="Nhập tên, tài khoản, email hoặc số điện thoại học viên...">
                <button type="button" class="btn-enroll-primary" onclick="searchEligibleStudents()">
                    <i class="fas fa-magnifying-glass"></i> Tìm
                </button>
            </div>

            <div class="enrollment-result-list" id="studentSearchResults">
                <div style="padding:18px;border:1px dashed #cbd5e1;border-radius:12px;color:#64748b;text-align:center">
                    Bắt đầu tìm kiếm để hiện danh sách học viên có thể thêm vào lớp.
                </div>
            </div>

            <form action="{{ route($portalRouteBase . '.lop-hoc.quick-add-students', $lopHoc->slug) }}" method="POST" id="quickEnrollForm">
                @csrf
                <div class="selected-student-list" id="selectedStudentsList"></div>
                <div class="enrollment-form-actions">
                    <select name="payment_method" required style="max-width:220px">
                        <option value="">Chọn hình thức thanh toán</option>
                        @foreach ($paymentMethods as $methodValue => $methodLabel)
                            <option value="{{ $methodValue }}">{{ $methodLabel }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-enroll-primary">
                        <i class="fas fa-user-plus"></i> Thêm học viên đã chọn
                    </button>
                    <span class="hint-badge"><i class="fas fa-sparkles"></i> Hệ thống tự bỏ qua học viên trùng lịch, trùng lớp hoặc vượt sĩ số.</span>
                </div>
            </form>

            <div class="hub-action-grid">
                <div class="hub-mini-card">
                    <h4>Tạo học viên mới</h4>
                    <p>Form ngắn gọn trong popup, tự tạo tài khoản và mở phiếu hợp đồng để in ngay.</p>
                    <button type="button" class="btn-enroll-primary" onclick="openPortalModal('createStudentModal')">
                        <i class="fas fa-user-plus"></i> Tạo học viên trong popup
                    </button>
                </div>
                <div class="hub-mini-card">
                    <h4>Lên lớp tiếp theo theo nhóm</h4>
                    <p>Chọn lớp đích và danh sách học viên trong popup để thao tác nhanh, gọn hơn.</p>
                    <button type="button" class="btn-enroll-secondary" onclick="openPortalModal('promoteStudentsModal')">
                        <i class="fas fa-arrow-trend-up"></i> Mở popup lên lớp
                    </button>
                </div>
            </div>
        </div>

        <div class="enrollment-card">
            <div class="promotion-toolbar">
                <div>
                    <h3>Điều phối học viên trong lớp</h3>
                    <p>Từ đây nhân viên có thể ghi danh nhanh học viên sẵn có, tạo mới học viên hoặc chuẩn bị danh sách nâng cấp sang lớp tiếp theo mà không phải rời trang.</p>
                </div>
                <span class="hint-badge"><i class="fas fa-people-arrows"></i> Luồng thao tác tại chỗ</span>
            </div>

            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px">
                <div class="hub-mini-card">
                    <h4>Tạo tài khoản học viên</h4>
                    <p>Mật khẩu tạm lấy theo CCCD nếu đủ 8 ký tự, nếu không dùng mặc định <strong>12345678</strong>.</p>
                    <span class="hint-badge"><i class="fas fa-file-pdf"></i> Có phiếu in ngay</span>
                </div>
                <div class="hub-mini-card">
                    <h4>Nâng cấp sang lớp mới</h4>
                    <p>Phù hợp khi lớp cũ kết thúc và cần tạo đăng ký hàng loạt cho lớp nối tiếp.</p>
                    <span class="hint-badge"><i class="fas fa-filter"></i> Chọn theo từng học viên</span>
                </div>
            </div>
        </div>
    </div>

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
    @if ($lopHoc->lichHoc && $lopHoc->ngayBatDau && $lopHoc->ngayKetThuc)
        <div class="auto-gen-card">
            <h3><i class="fas fa-magic me-2"></i> Tự động tạo buổi học</h3>
            <p>Tạo tự động các buổi học theo lịch
                <strong>Thứ {{ implode(', ', array_map('trim', explode(',', $lopHoc->lichHoc))) }}</strong>
                bắt đầu từ <strong>{{ \Carbon\Carbon::parse($lopHoc->ngayBatDau)->format('d/m/Y') }}</strong>
                đến <strong>{{ \Carbon\Carbon::parse($lopHoc->ngayKetThuc)->format('d/m/Y') }}</strong>.
            </p>
            <form action="{{ route($portalRouteBase . '.buoi-hoc.auto-generate', $lopHoc->lopHocId) }}" method="POST"
                style="display:inline">
                @csrf
                <div class="auto-gen-row">
                    <label class="auto-gen-check">
                        <input type="checkbox" name="xoa_cu" value="1">
                        <span style="opacity:.9">Xóa buổi học chưa hoàn thành trước khi tạo mới</span>
                    </label>
                    <button type="submit" class="btn-auto-gen"
                        onclick="return confirm('Tạo lại danh sách buổi học trong toàn bộ khoảng ngày của lớp?')">
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
            <form action="{{ route($portalRouteBase . '.buoi-hoc.store') }}" method="POST">
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
                        <input type="date" name="ngayHoc" required class="session-date-input"
                            min="{{ $lopHoc->ngayBatDau }}"
                            max="{{ $lopHoc->ngayKetThuc }}">
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
                <div class="session-hint-row">
                    <span class="hint-badge"><i class="fas fa-calendar-check"></i> Chỉ chọn ngày trong khoảng {{ $lopHoc->ngayBatDau ? \Carbon\Carbon::parse($lopHoc->ngayBatDau)->format('d/m/Y') : '—' }} - {{ $lopHoc->ngayKetThuc ? \Carbon\Carbon::parse($lopHoc->ngayKetThuc)->format('d/m/Y') : '—' }}</span>
                    @if ($lopHoc->lichHoc)
                        <span class="hint-badge"><i class="fas fa-repeat"></i> Phải khớp lịch học: {{ $lopHoc->lichHoc }}</span>
                    @endif
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


    <div id="createStudentModal" class="portal-modal" onclick="handlePortalModalBackdrop(event, 'createStudentModal')">
        <div class="portal-modal-dialog">
            <div class="portal-modal-header">
                <div>
                    <h3>Tạo học viên mới và ghi danh ngay</h3>
                    <p>Tạo tài khoản theo logic học viên, đồng thời mở phiếu hợp đồng để in ngay sau khi ghi danh.</p>
                </div>
                <button type="button" class="portal-modal-close" onclick="closePortalModal('createStudentModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="portal-modal-body">
                <form action="{{ route($portalRouteBase . '.lop-hoc.create-student-and-enroll', $lopHoc->slug) }}" method="POST" target="_blank">
                    @csrf
                    <div class="inline-create-grid">
                        <div class="full">
                            <label class="inline-create-label" for="inline_hoTen">Họ và tên học viên</label>
                            <input type="text" id="inline_hoTen" name="hoTen" value="{{ old('hoTen') }}"
                                placeholder="Nhập họ tên học viên" required>
                        </div>
                        <div>
                            <label class="inline-create-label" for="inline_cccd">CCCD / CMND</label>
                            <input type="text" id="inline_cccd" name="cccd" value="{{ old('cccd') }}"
                                placeholder="Dùng làm mật khẩu tạm nếu đủ 8 ký tự">
                        </div>
                        <div>
                            <label class="inline-create-label" for="inline_soDienThoai">Số điện thoại</label>
                            <input type="text" id="inline_soDienThoai" name="soDienThoai" value="{{ old('soDienThoai') }}"
                                placeholder="0901234567">
                        </div>
                        <div>
                            <label class="inline-create-label" for="inline_email">Email</label>
                            <input type="email" id="inline_email" name="email" value="{{ old('email') }}"
                                placeholder="Có thể để trống, hệ thống sẽ tự tạo email nội bộ">
                        </div>
                        <div>
                            <label class="inline-create-label" for="inline_payment_method">Hình thức thanh toán</label>
                            <select name="payment_method" id="inline_payment_method" required>
                                <option value="">Chọn hình thức thanh toán</option>
                                @foreach ($paymentMethods as $methodValue => $methodLabel)
                                    <option value="{{ $methodValue }}" @selected(old('payment_method') == (string) $methodValue)>{{ $methodLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="inline-create-label" for="inline_ngaySinh">Ngày sinh</label>
                            <input type="date" id="inline_ngaySinh" name="ngaySinh" value="{{ old('ngaySinh') }}">
                        </div>
                        <div>
                            <label class="inline-create-label" for="inline_gioiTinh">Giới tính</label>
                            <select name="gioiTinh" id="inline_gioiTinh">
                                <option value="">Chọn giới tính</option>
                                <option value="1" @selected(old('gioiTinh') === '1')>Nam</option>
                                <option value="0" @selected(old('gioiTinh') === '0')>Nữ</option>
                                <option value="2" @selected(old('gioiTinh') === '2')>Khác</option>
                            </select>
                        </div>
                        <div>
                            <label class="inline-create-label" for="inline_nguoiGiamHo">Người giám hộ</label>
                            <input type="text" id="inline_nguoiGiamHo" name="nguoiGiamHo" value="{{ old('nguoiGiamHo') }}"
                                placeholder="Nếu học viên chưa đủ tuổi">
                        </div>
                        <div>
                            <label class="inline-create-label" for="inline_sdtGuardian">SĐT giám hộ</label>
                            <input type="text" id="inline_sdtGuardian" name="sdtGuardian" value="{{ old('sdtGuardian') }}"
                                placeholder="0912345678">
                        </div>
                        <div>
                            <label class="inline-create-label" for="inline_moiQuanHe">Mối quan hệ</label>
                            <input type="text" id="inline_moiQuanHe" name="moiQuanHe" value="{{ old('moiQuanHe') }}"
                                placeholder="Bố, mẹ, anh, chị...">
                        </div>
                        <div class="full">
                            <label class="inline-create-label" for="inline_diaChi">Địa chỉ</label>
                            <input type="text" id="inline_diaChi" name="diaChi" value="{{ old('diaChi') }}"
                                placeholder="Địa chỉ liên hệ">
                        </div>
                        <div>
                            <label class="inline-create-label" for="inline_trinhDoHienTai">Trình độ hiện tại</label>
                            <input type="text" id="inline_trinhDoHienTai" name="trinhDoHienTai" value="{{ old('trinhDoHienTai') }}"
                                placeholder="Beginner, Intermediate...">
                        </div>
                        <div>
                            <label class="inline-create-label" for="inline_ngonNguMucTieu">Ngôn ngữ mục tiêu</label>
                            <input type="text" id="inline_ngonNguMucTieu" name="ngonNguMucTieu" value="{{ old('ngonNguMucTieu') }}"
                                placeholder="Tiếng Anh, Nhật, Hàn...">
                        </div>
                        <div class="full">
                            <label class="inline-create-label" for="inline_ghiChu">Ghi chú</label>
                            <textarea id="inline_ghiChu" name="ghiChu"
                                placeholder="Ghi chú thêm nếu cần">{{ old('ghiChu') }}</textarea>
                        </div>
                    </div>

                    <div class="enrollment-form-actions">
                        <button type="submit" class="btn-enroll-primary">
                            <i class="fas fa-user-plus"></i> Tạo học viên, ghi danh và mở phiếu in
                        </button>
                        <button type="button" class="btn-enroll-secondary" onclick="closePortalModal('createStudentModal')">
                            Đóng
                        </button>
                    </div>
                    <p class="inline-create-note">
                        Mật khẩu tạm sẽ lấy theo CCCD nếu đủ 8 ký tự, nếu không hệ thống dùng mặc định <strong>12345678</strong>.
                    </p>
                </form>
            </div>
        </div>
    </div>

    <div id="promoteStudentsModal" class="portal-modal" onclick="handlePortalModalBackdrop(event, 'promoteStudentsModal')">
        <div class="portal-modal-dialog">
            <div class="portal-modal-header">
                <div>
                    <h3>Lên lớp tiếp theo theo nhóm</h3>
                    <p>Chọn lớp đích và tick các học viên cần tạo đăng ký lớp tiếp theo.</p>
                </div>
                <button type="button" class="portal-modal-close" onclick="closePortalModal('promoteStudentsModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="portal-modal-body">
                <form action="{{ route($portalRouteBase . '.lop-hoc.promote-students', $lopHoc->slug) }}" method="POST">
                    @csrf
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px">
                        <select name="target_lop_hoc_id" required>
                            <option value="">Chọn lớp đích</option>
                            @foreach ($promotionTargetClasses as $candidate)
                                <option value="{{ $candidate->lopHocId }}">
                                    [{{ $candidate->maLopHoc }}] {{ $candidate->tenLopHoc }}
                                    • {{ $candidate->khoaHoc?->tenKhoaHoc ?? '—' }}
                                    • {{ $candidate->ngayBatDau ? \Carbon\Carbon::parse($candidate->ngayBatDau)->format('d/m/Y') : 'Chưa có ngày' }}
                                </option>
                            @endforeach
                        </select>
                        <select name="payment_method" required>
                            <option value="">Hình thức thanh toán</option>
                            @foreach ($paymentMethods as $methodValue => $methodLabel)
                                <option value="{{ $methodValue }}">{{ $methodLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="promotion-toolbar">
                        <label style="font-size:.82rem;font-weight:700;color:#334155;display:flex;gap:8px;align-items:center">
                            <input type="checkbox" id="selectAllPromotionStudents">
                            Chọn tất cả học viên trong danh sách dưới
                        </label>
                        <span class="hint-badge"><i class="fas fa-filter"></i> Chỉ nên chọn các học viên đủ điều kiện tiếp tục khóa mới.</span>
                    </div>

                    <div class="promotion-student-list">
                        @forelse ($lopHoc->dangKyLopHocs as $dk)
                            <label class="promotion-student-item">
                                <input type="checkbox" name="registration_ids[]" value="{{ $dk->dangKyLopHocId }}" class="promotion-student-checkbox">
                                <div style="flex:1">
                                    <div style="font-size:.86rem;font-weight:700;color:#1e293b">
                                        {{ $dk->taiKhoan?->hoSoNguoiDung?->hoTen ?? ($dk->taiKhoan?->taiKhoan ?? '—') }}
                                    </div>
                                    <div style="font-size:.74rem;color:#64748b;margin-top:3px">
                                        {{ $dk->taiKhoan?->taiKhoan ?? '—' }}
                                        @if ($dk->taiKhoan?->email)
                                            • {{ $dk->taiKhoan->email }}
                                        @endif
                                    </div>
                                </div>
                                <span class="hint-badge">{{ $dk->trangThaiLabel }}</span>
                            </label>
                        @empty
                            <div style="padding:18px;border:1px dashed #cbd5e1;border-radius:12px;color:#64748b;text-align:center">
                                Chưa có đăng ký nào để thực hiện lên lớp tiếp theo.
                            </div>
                        @endforelse
                    </div>

                    <div class="enrollment-form-actions">
                        <button type="submit" class="btn-enroll-primary">
                            <i class="fas fa-arrow-trend-up"></i> Tạo đăng ký lớp tiếp theo
                        </button>
                        <button type="button" class="btn-enroll-secondary" onclick="closePortalModal('promoteStudentsModal')">
                            Đóng
                        </button>
                    </div>
                </form>
            </div>
        </div>
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
                            min="{{ $lopHoc->ngayBatDau }}"
                            max="{{ $lopHoc->ngayKetThuc }}"
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
        const BUOI_HOC_UPDATE_URL_TEMPLATE = @js(route($portalRouteBase . '.buoi-hoc.update', ['id' => '__ID__']));
        const BUOI_HOC_DELETE_URL_TEMPLATE = @js(route($portalRouteBase . '.buoi-hoc.destroy', ['id' => '__ID__']));
        const SEARCH_STUDENTS_URL = @js(route($portalRouteBase . '.lop-hoc.search-students', $lopHoc->slug));
        const CLASS_START_DATE = @js($lopHoc->ngayBatDau);
        const CLASS_END_DATE = @js($lopHoc->ngayKetThuc);
        const CLASS_SCHEDULE_DAYS = @js(array_map('trim', explode(',', (string) $lopHoc->lichHoc)));
        const SCHEDULE_DAY_MAP = {
            '2': 1,
            '3': 2,
            '4': 3,
            '5': 4,
            '6': 5,
            '7': 6,
            'CN': 0,
        };
        const selectedStudentIds = new Map();

        function getBuoiHocActionUrl(template, id) {
            return template.replace('__ID__', String(id));
        }

        async function searchEligibleStudents() {
            const keyword = document.getElementById('studentSearchInput')?.value?.trim() || '';
            const resultBox = document.getElementById('studentSearchResults');
            resultBox.innerHTML =
                '<div style="padding:18px;border:1px dashed #cbd5e1;border-radius:12px;color:#64748b;text-align:center">Đang tải danh sách học viên phù hợp...</div>';

            try {
                const url = new URL(SEARCH_STUDENTS_URL, window.location.origin);
                if (keyword) {
                    url.searchParams.set('q', keyword);
                }

                const response = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                const students = data.students || [];

                if (!students.length) {
                    resultBox.innerHTML =
                        '<div style="padding:18px;border:1px dashed #cbd5e1;border-radius:12px;color:#64748b;text-align:center">Không tìm thấy học viên phù hợp với lớp này.</div>';
                    return;
                }

                resultBox.innerHTML = students.map(student => `
                    <div class="enrollment-result-item">
                        <div style="width:40px;height:40px;border-radius:50%;background:#ede9fe;color:#6d28d9;display:flex;align-items:center;justify-content:center;font-weight:800;flex-shrink:0">
                            ${(student.hoTen || student.taiKhoan || '?').charAt(0).toUpperCase()}
                        </div>
                        <div style="min-width:0">
                            <div style="font-size:.86rem;font-weight:700;color:#1e293b">${student.hoTen || student.taiKhoan}</div>
                            <div style="font-size:.74rem;color:#64748b;margin-top:2px">${student.taiKhoan}${student.soDienThoai ? ` • ${student.soDienThoai}` : ''}</div>
                            <div style="font-size:.72rem;color:#94a3b8">${student.email || ''}</div>
                        </div>
                        <button type="button" onclick="addStudentToSelection(${student.taiKhoanId}, '${(student.hoTen || student.taiKhoan).replace(/'/g, "\\'")}', '${student.taiKhoan}')">
                            <i class="fas fa-plus"></i> Chọn
                        </button>
                    </div>
                `).join('');
            } catch (error) {
                resultBox.innerHTML =
                    '<div style="padding:18px;border:1px dashed #fecaca;border-radius:12px;color:#b91c1c;text-align:center">Không thể tải danh sách học viên. Vui lòng thử lại.</div>';
            }
        }

        function addStudentToSelection(id, name, account) {
            selectedStudentIds.set(String(id), {
                id: String(id),
                name,
                account
            });
            renderSelectedStudents();
        }

        function removeSelectedStudent(id) {
            selectedStudentIds.delete(String(id));
            renderSelectedStudents();
        }

        function renderSelectedStudents() {
            const container = document.getElementById('selectedStudentsList');
            const form = document.getElementById('quickEnrollForm');
            form.querySelectorAll('input[name="student_ids[]"]').forEach(input => input.remove());

            if (!selectedStudentIds.size) {
                container.innerHTML = '<span class="hint-badge"><i class="fas fa-users"></i> Chưa chọn học viên nào.</span>';
                return;
            }

            container.innerHTML = Array.from(selectedStudentIds.values()).map(student => `
                <span class="selected-student-chip">
                    ${student.name} <small style="opacity:.75">${student.account}</small>
                    <button type="button" onclick="removeSelectedStudent('${student.id}')">&times;</button>
                </span>
            `).join('');

            selectedStudentIds.forEach(student => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'student_ids[]';
                input.value = student.id;
                form.appendChild(input);
            });
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

        function openPortalModal(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        function closePortalModal(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.remove('is-open');
            document.body.style.overflow = '';
        }

        function handlePortalModalBackdrop(event, id) {
            if (event.target.id === id) {
                closePortalModal(id);
            }
        }

        function isClassScheduleDate(dateValue) {
            if (!dateValue) {
                return false;
            }

            const date = new Date(`${dateValue}T00:00:00`);
            if (Number.isNaN(date.getTime())) {
                return false;
            }

            if (CLASS_START_DATE && dateValue < CLASS_START_DATE) {
                return false;
            }

            if (CLASS_END_DATE && dateValue > CLASS_END_DATE) {
                return false;
            }

            const allowedDays = CLASS_SCHEDULE_DAYS
                .map(day => SCHEDULE_DAY_MAP[day])
                .filter(day => day !== undefined);

            if (allowedDays.length && !allowedDays.includes(date.getDay())) {
                return false;
            }

            return true;
        }

        function getSessionDateValidationMessage() {
            const rangeText = CLASS_START_DATE && CLASS_END_DATE ?
                `Ngày học phải nằm trong khoảng ${CLASS_START_DATE} đến ${CLASS_END_DATE}.` :
                'Ngày học không hợp lệ.';
            const scheduleText = CLASS_SCHEDULE_DAYS.filter(Boolean).length ?
                ` Đồng thời phải khớp lịch học của lớp: ${CLASS_SCHEDULE_DAYS.join(', ')}.` :
                '';

            return rangeText + scheduleText;
        }

        function validateSessionDateValue(dateValue) {
            if (isClassScheduleDate(dateValue)) {
                return true;
            }

            Swal.fire('Ngày học chưa hợp lệ', getSessionDateValidationMessage(), 'warning');
            return false;
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
            renderSelectedStudents();
            document.getElementById('editBuoiHocModal').addEventListener('click', function(e) {
                if (e.target === this) closeEditModal();
            });
            document.getElementById('studentSearchInput')?.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchEligibleStudents();
                }
            });
            document.getElementById('selectAllPromotionStudents')?.addEventListener('change', function() {
                document.querySelectorAll('.promotion-student-checkbox').forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
            document.querySelector('#addBhForm form')?.addEventListener('submit', function(e) {
                const dateValue = this.querySelector('input[name="ngayHoc"]')?.value;
                if (!validateSessionDateValue(dateValue)) {
                    e.preventDefault();
                }
            });
            @if ($errors->has('ngayHoc') || $errors->has('caHocId') || $errors->has('lopHocId'))
                document.getElementById('addBhForm').style.display = 'block';
            @endif
            @if ($errors->has('hoTen') || $errors->has('cccd') || $errors->has('soDienThoai') || $errors->has('email') || $errors->has('nguoiGiamHo'))
                openPortalModal('createStudentModal');
            @endif
            @if ($errors->has('target_lop_hoc_id') || $errors->has('registration_ids'))
                openPortalModal('promoteStudentsModal');
            @endif
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
            const sessionDate = document.getElementById('ebh-ngay').value;
            if (!validateSessionDateValue(sessionDate)) {
                return;
            }

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
@endsection
