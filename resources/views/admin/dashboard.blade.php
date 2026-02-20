@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Tổng quan hệ thống')

@section('stylesheet')
    <style>
        /* ── STAT CARDS ─────────────────────────────── */
        .stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 18px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .stat-icon.teal {
            background: rgba(39, 196, 181, 0.12);
            color: #27c4b5;
        }

        .stat-icon.blue {
            background: rgba(66, 153, 225, 0.12);
            color: #4299e1;
        }

        .stat-icon.orange {
            background: rgba(237, 137, 54, 0.12);
            color: #ed8936;
        }

        .stat-icon.red {
            background: rgba(227, 30, 36, 0.12);
            color: #e31e24;
        }

        .stat-value {
            font-size: 1.7rem;
            font-weight: 700;
            color: #1a2b3c;
            line-height: 1;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #8899a6;
            margin-top: 4px;
        }

        /* ── WELCOME BANNER ──────────────────────────── */
        .welcome-banner {
            background: linear-gradient(135deg, #10454f 0%, #0f1923 100%);
            border-radius: 16px;
            padding: 28px 32px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 28px;
            position: relative;
            overflow: hidden;
        }

        .welcome-banner::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(39, 196, 181, 0.08);
        }

        .welcome-banner::after {
            content: '';
            position: absolute;
            bottom: -60px;
            right: 100px;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: rgba(39, 196, 181, 0.05);
        }

        .welcome-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .welcome-sub {
            font-size: 0.85rem;
            opacity: 0.7;
        }

        .welcome-role {
            background: rgba(39, 196, 181, 0.2);
            color: #27c4b5;
            border-radius: 20px;
            padding: 6px 16px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid rgba(39, 196, 181, 0.3);
            flex-shrink: 0;
        }

        /* ── SECTION CARD ────────────────────────────── */
        .section-card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid #f0f4f8;
        }

        .section-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #1a2b3c;
        }

        /* ── QUICK ACCESS ────────────────────────────── */
        .quick-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .quick-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 20px 10px;
            border-radius: 12px;
            background: #f8fafc;
            border: 2px solid transparent;
            text-decoration: none;
            color: #4a5568;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .quick-btn i {
            font-size: 1.3rem;
        }

        .quick-btn:hover {
            border-color: #27c4b5;
            background: rgba(39, 196, 181, 0.06);
            color: #10454f;
        }

        .quick-btn:hover i {
            color: #27c4b5;
        }
    </style>
@endsection

@section('content')

    {{-- Welcome Banner --}}
    <div class="welcome-banner">
        <div>
            <div class="welcome-title">
                Xin chào,
                {{ auth()->user()->hoSoNguoiDung->hoTen ?? (auth()->user()->nhanSu->hoTen ?? auth()->user()->taiKhoan) }}!
                👋
            </div>
            <div class="welcome-sub">
                Hôm nay là {{ \Carbon\Carbon::now()->isoFormat('dddd, D/M/YYYY') }}. Chào mừng bạn trở lại hệ thống quản
                trị.
            </div>
        </div>
        <div class="welcome-role">
            <i class="fas fa-shield-alt me-1"></i>
            {{ auth()->user()->getRoleLabel() }}
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon teal">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $totalStudent }}</div>
                    <div class="stat-label">Tổng học viên</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div>
                    <div class="stat-value">—</div>
                    <div class="stat-label">Lớp đang hoạt động</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div>
                    <div class="stat-value">—</div>
                    <div class="stat-label">Đăng ký mới hôm nay</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fas fa-wallet"></i>
                </div>
                <div>
                    <div class="stat-value">—</div>
                    <div class="stat-label">Doanh thu tháng này</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Access + System Info --}}
    <div class="row g-3">
        {{-- Quick Access --}}
        <div class="col-lg-4">
            <div class="section-card">
                <div class="section-header">
                    <div class="section-title"><i class="fas fa-bolt me-2" style="color:#f6ad55"></i>Truy cập nhanh</div>
                </div>
                <div class="quick-grid">
                    <a href="#" class="quick-btn">
                        <i class="fas fa-book-open"></i> Khoá học
                    </a>
                    <a href="#" class="quick-btn">
                        <i class="fas fa-chalkboard"></i> Lớp học
                    </a>
                    <a href="#" class="quick-btn">
                        <i class="fas fa-user-graduate"></i> Học viên
                    </a>
                    <a href="#" class="quick-btn">
                        <i class="fas fa-wallet"></i> Tài chính
                    </a>
                </div>
            </div>
        </div>

        {{-- System Info --}}
        <div class="col-lg-8">
            <div class="section-card">
                <div class="section-header">
                    <div class="section-title"><i class="fas fa-info-circle me-2" style="color:#4299e1"></i>Thông tin hệ
                        thống</div>
                </div>
                <table class="table table-borderless table-sm mb-0" style="font-size:0.875rem;">
                    <tbody>
                        <tr>
                            <td class="text-muted" style="width:180px">Tên hệ thống</td>
                            <td class="fw-600">{{ config('app.name', 'Five Genius') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Phiên bản Laravel</td>
                            <td class="fw-600">{{ app()->version() }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">PHP Version</td>
                            <td class="fw-600">{{ phpversion() }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tài khoản đăng nhập</td>
                            <td class="fw-600">{{ auth()->user()->taiKhoan }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Quyền hạn</td>
                            <td>
                                <span class="badge rounded-pill"
                                    style="background:rgba(39,196,181,0.15); color:#0f6b63; font-size:0.75rem; padding:4px 10px;">
                                    {{ auth()->user()->getRoleLabel() }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Thời gian máy chủ</td>
                            <td class="fw-600">{{ \Carbon\Carbon::now()->format('H:i:s — d/m/Y') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
