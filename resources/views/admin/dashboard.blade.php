@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Tổng quan hệ thống')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/dashboard/index.css') }}">
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
