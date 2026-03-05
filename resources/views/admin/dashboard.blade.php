@extends('layouts.admin')

@section('title', 'Tổng quan')
@section('page-title', 'Tổng quan')
@section('breadcrumb', 'Dashboard')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/dashboard/index.css') }}">
@endsection

@section('content')
    {{-- Welcome Banner --}}
    <div class="dashboard-welcome">
        <div class="dashboard-welcome__content">
            <h1 class="dashboard-welcome__title">
                Xin chào,
                {{ auth()->user()->hoSoNguoiDung->hoTen ?? (auth()->user()->nhanSu->hoTen ?? auth()->user()->taiKhoan) }}!
                👋
            </h1>
            <p class="dashboard-welcome__sub">
                {{ \Carbon\Carbon::now()->isoFormat('dddd, D/M/YYYY') }} — Chào mừng bạn trở lại {{ config('app.name') }}.
            </p>
        </div>
        <div class="dashboard-welcome__badge">
            <i class="fas fa-shield-alt"></i>
            {{ auth()->user()->getRoleLabel() }}
        </div>
    </div>

    {{-- Period filter --}}
    <div class="dashboard-toolbar">
        <span class="dashboard-toolbar__label">Doanh thu:</span>
        <div class="dashboard-period-tabs">
            <a href="{{ route('admin.dashboard', ['period' => '7']) }}"
                class="dashboard-period-tab {{ ($periodDays ?? 7) == 7 ? 'active' : '' }}">7 ngày</a>
            <a href="{{ route('admin.dashboard', ['period' => '14']) }}"
                class="dashboard-period-tab {{ ($periodDays ?? 7) == 14 ? 'active' : '' }}">14 ngày</a>
            <a href="{{ route('admin.dashboard', ['period' => '30']) }}"
                class="dashboard-period-tab {{ ($periodDays ?? 7) == 30 ? 'active' : '' }}">30 ngày</a>
        </div>
    </div>

    {{-- Stats Cards (4) --}}
    <div class="dashboard-stats">
        <a href="{{ route('admin.hoc-vien.index') }}" class="dashboard-stat-card dashboard-stat-card--teal">
            <div class="dashboard-stat-card__icon">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="dashboard-stat-card__body">
                <div class="dashboard-stat-card__value">{{ number_format($totalStudent) }}</div>
                <div class="dashboard-stat-card__label">Tổng học viên</div>
            </div>
        </a>
        <div class="dashboard-stat-card dashboard-stat-card--blue">
            <div class="dashboard-stat-card__icon">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="dashboard-stat-card__body">
                <div class="dashboard-stat-card__value">{{ number_format($activeClasses) }}</div>
                <div class="dashboard-stat-card__label">Lớp đang hoạt động</div>
            </div>
        </div>
        <div class="dashboard-stat-card dashboard-stat-card--orange">
            <div class="dashboard-stat-card__icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="dashboard-stat-card__body">
                <div class="dashboard-stat-card__value">{{ number_format($newRegistrationsToday) }}</div>
                <div class="dashboard-stat-card__label">Đăng ký mới hôm nay</div>
                @if (isset($registrationTrend) && $registrationTrend != 0)
                    <span
                        class="dashboard-stat-card__trend dashboard-stat-card__trend--{{ $registrationTrend >= 0 ? 'up' : 'down' }}">
                        <i class="fas fa-arrow-{{ $registrationTrend >= 0 ? 'up' : 'down' }}"></i>
                        {{ abs($registrationTrend) }}% so hôm qua
                    </span>
                @endif
            </div>
        </div>
        <div class="dashboard-stat-card dashboard-stat-card--green">
            <div class="dashboard-stat-card__icon">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="dashboard-stat-card__body">
                <div class="dashboard-stat-card__value">{{ number_format($revenueMonth, 0, ',', '.') }}<span
                        class="dashboard-stat-card__unit">đ</span></div>
                <div class="dashboard-stat-card__label">Doanh thu tháng này</div>
                @if (isset($monthlyComparison['growth']) && $monthlyComparison['growth'] != 0)
                    <span
                        class="dashboard-stat-card__trend dashboard-stat-card__trend--{{ $monthlyComparison['growth'] >= 0 ? 'up' : 'down' }}">
                        <i class="fas fa-arrow-{{ $monthlyComparison['growth'] >= 0 ? 'up' : 'down' }}"></i>
                        {{ abs($monthlyComparison['growth']) }}% so tháng trước
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Charts row: Bar + Donut --}}
    <div class="dashboard-charts">
        <div class="dashboard-card dashboard-card--chart">
            <div class="dashboard-card__header">
                <h3 class="dashboard-card__title"><i class="fas fa-chart-bar me-2"></i>Doanh thu theo ngày</h3>
            </div>
            <div class="dashboard-card__body">
                <div class="dashboard-chart-wrap">
                    <canvas id="dashboardRevenueChart" height="260"></canvas>
                </div>
            </div>
        </div>
        <div class="dashboard-card dashboard-card--donut">
            <div class="dashboard-card__header">
                <h3 class="dashboard-card__title"><i class="fas fa-chart-pie me-2"></i>Lớp học theo ca</h3>
            </div>
            <div class="dashboard-card__body dashboard-card__body--center">
                <div class="dashboard-donut-wrap">
                    <canvas id="dashboardDonutChart" height="220"></canvas>
                </div>
                <div class="dashboard-donut-legend" id="donutLegend"></div>
            </div>
        </div>
    </div>

    {{-- Bottom row: Upcoming registrations + Quick actions --}}
    <div class="dashboard-bottom">
        <div class="dashboard-card">
            <div class="dashboard-card__header">
                <h3 class="dashboard-card__title"><i class="fas fa-calendar-check me-2"></i>Đăng ký gần đây</h3>
                @if (auth()->user()->canDo('dang_ky', 'xem'))
                    <a href="#" class="dashboard-card__link">Xem tất cả</a>
                @endif
            </div>
            <div class="dashboard-card__body">
                @if ($upcomingRegistrations->isEmpty())
                    <div class="dashboard-empty">
                        <i class="fas fa-inbox"></i>
                        <p>Chưa có đăng ký nào</p>
                    </div>
                @else
                    <ul class="dashboard-list">
                        @foreach ($upcomingRegistrations as $reg)
                            <li class="dashboard-list__item">
                                <div class="dashboard-list__main">
                                    <span
                                        class="dashboard-list__name">{{ optional($reg->taiKhoan->hoSoNguoiDung)->hoTen ?? $reg->taiKhoan->taiKhoan }}</span>
                                    <span class="dashboard-list__meta">{{ $reg->lopHoc->tenLopHoc ?? '—' }} ·
                                        {{ optional($reg->lopHoc->khoaHoc)->tenKhoaHoc ?? '—' }}</span>
                                </div>
                                <div class="dashboard-list__date">
                                    {{ \Carbon\Carbon::parse($reg->ngayDangKy)->format('d/m/Y') }}</div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        <div class="dashboard-card">
            <div class="dashboard-card__header">
                <h3 class="dashboard-card__title"><i class="fas fa-bolt me-2"></i>Thao tác nhanh</h3>
            </div>
            <div class="dashboard-card__body">
                <div class="dashboard-quick-grid">
                    @if (auth()->user()->canDo('hoc_vien', 'xem'))
                        <a href="{{ route('admin.hoc-vien.index') }}" class="dashboard-quick-btn">
                            <i class="fas fa-user-graduate"></i>
                            <span>Học viên</span>
                        </a>
                    @endif
                    <a href="{{ route('admin.danh-muc-khoa-hoc.index') }}" class="dashboard-quick-btn">
                        <i class="fas fa-book-open"></i>
                        <span>Khóa học</span>
                    </a>
                    <a href="{{ route('admin.lop-hoc.index') }}" class="dashboard-quick-btn">
                        <i class="fas fa-chalkboard"></i>
                        <span>Lớp học</span>
                    </a>
                    @if (auth()->user()->canDo('tai_chinh', 'xem'))
                        <a href="{{ route('admin.hoa-don.index') }}" class="dashboard-quick-btn">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span>Hóa đơn</span>
                            @if ($pendingInvoices > 0)
                                <span class="dashboard-quick-badge">{{ $pendingInvoices }}</span>
                            @endif
                        </a>
                    @endif
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.co-so.index') }}" class="dashboard-quick-btn">
                            <i class="fas fa-building"></i>
                            <span>Cơ sở</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- System info compact --}}
    <div class="dashboard-footer-info">
        <span>{{ config('app.name') }}</span>
        <span>Laravel {{ app()->version() }}</span>
        <span>PHP {{ phpversion() }}</span>
        <span>{{ \Carbon\Carbon::now()->format('H:i d/m/Y') }}</span>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (function() {
            const revenueData = @json($revenueChartData);
            const shiftData = @json($classesByShift);

            // ── Bar Chart: Doanh thu theo ngày ─────────────────────
            const revenueCtx = document.getElementById('dashboardRevenueChart');
            if (revenueCtx && revenueData.length) {
                new Chart(revenueCtx, {
                    type: 'bar',
                    data: {
                        labels: revenueData.map(d => d.date),
                        datasets: [{
                            label: 'Doanh thu (VNĐ)',
                            data: revenueData.map(d => d.revenue),
                            backgroundColor: 'rgba(39, 196, 181, 0.6)',
                            borderColor: 'rgb(39, 196, 181)',
                            borderWidth: 1,
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        const v = ctx.raw;
                                        return new Intl.NumberFormat('vi-VN').format(v) + 'đ';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(v) {
                                        if (v >= 1000000) return (v / 1e6) + 'M';
                                        if (v >= 1000) return (v / 1000) + 'K';
                                        return v;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // ── Donut: Lớp học theo ca ─────────────────────────────
            const donutCtx = document.getElementById('dashboardDonutChart');
            if (donutCtx && shiftData.length) {
                const labels = shiftData.map(d => d.label);
                const values = shiftData.map(d => d.value);
                const colors = shiftData.map(d => d.color);
                const total = values.reduce((a, b) => a + b, 0);

                new Chart(donutCtx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: colors,
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '62%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        const p = total ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                                        return ctx.label + ': ' + ctx.raw + ' lớp (' + p + '%)';
                                    }
                                }
                            }
                        }
                    },
                    plugins: [{
                        id: 'centerText',
                        afterDraw: function(chart) {
                            if (chart.config.data.datasets[0].data.every(v => v === 0)) return;
                            const ctx = chart.ctx;
                            const w = chart.width;
                            const h = chart.height;
                            ctx.save();
                            ctx.font = 'bold 1.5rem Inter, sans-serif';
                            ctx.fillStyle = '#1a2b3c';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            ctx.fillText(total, w / 2, h / 2 - 8);
                            ctx.font = '0.75rem Inter, sans-serif';
                            ctx.fillStyle = '#8899a6';
                            ctx.fillText('lớp', w / 2, h / 2 + 14);
                            ctx.restore();
                        }
                    }]
                });

                // Legend HTML
                const legendEl = document.getElementById('donutLegend');
                if (legendEl) {
                    legendEl.innerHTML = shiftData.map((d, i) =>
                        '<span class="dashboard-donut-legend__item"><i style="background:' + d.color + '"></i>' + d
                        .label + ' (' + d.value + ')</span>'
                    ).join('');
                }
            }
        })();
    </script>
@endsection
