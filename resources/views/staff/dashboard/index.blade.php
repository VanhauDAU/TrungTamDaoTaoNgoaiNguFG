@extends('layouts.internal')

@section('title', 'Dashboard nhân viên')
@section('page-title', 'Dashboard nhân viên')
@section('breadcrumb', 'Cổng vận hành')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/dashboard/index.css') }}">
@endsection

@section('content')
    @php
        $revenueGrowth = (float) ($revenueSummary['growth'] ?? 0);
        $revenueTrendClass = $revenueGrowth >= 0 ? 'dashboard-stat-card__trend dashboard-stat-card__trend--up' : 'dashboard-stat-card__trend dashboard-stat-card__trend--down';
        $revenueTrendIcon = $revenueGrowth >= 0 ? 'fas fa-arrow-trend-up' : 'fas fa-arrow-trend-down';
        $registrationTrendClass = $registrationTrend >= 0 ? 'dashboard-stat-card__trend dashboard-stat-card__trend--up' : 'dashboard-stat-card__trend dashboard-stat-card__trend--down';
        $registrationTrendIcon = $registrationTrend >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down';
    @endphp

    <div class="container-fluid px-0">
        <section class="dashboard-welcome">
            <div>
                <h2 class="dashboard-welcome__title">Bảng điều phối vận hành trung tâm</h2>
                <p class="dashboard-welcome__sub">
                    Gộp tuyển sinh, lớp học, công nợ và thông báo nội bộ trên một dashboard để nhân viên xử lý nhanh
                    theo đúng nhịp vận hành mỗi ngày.
                </p>
            </div>
            <div class="dashboard-welcome__badge">
                <i class="fas fa-briefcase"></i>
                Staff operations
            </div>
        </section>

        <section class="dashboard-toolbar">
            <span class="dashboard-toolbar__label">Khoảng thời gian theo dõi</span>
            <div class="dashboard-period-tabs">
                <a href="{{ route('staff.dashboard', ['period' => 7]) }}" class="dashboard-period-tab {{ $periodDays === 7 ? 'active' : '' }}">7 ngày</a>
                <a href="{{ route('staff.dashboard', ['period' => 14]) }}" class="dashboard-period-tab {{ $periodDays === 14 ? 'active' : '' }}">14 ngày</a>
                <a href="{{ route('staff.dashboard', ['period' => 30]) }}" class="dashboard-period-tab {{ $periodDays === 30 ? 'active' : '' }}">30 ngày</a>
            </div>
        </section>

        <section class="dashboard-stats">
            <a href="{{ route('staff.hoc-vien.index') }}" class="dashboard-stat-card dashboard-stat-card--teal">
                <div class="dashboard-stat-card__icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <div class="dashboard-stat-card__value">{{ number_format($totalStudent) }}</div>
                    <div class="dashboard-stat-card__label">Học viên đang quản lý</div>
                    <div class="{{ $registrationTrendClass }}">
                        <i class="{{ $registrationTrendIcon }}"></i>
                        {{ abs($registrationTrend) }}% đăng ký so với hôm qua
                    </div>
                </div>
            </a>

            <a href="{{ route('staff.lop-hoc.index') }}" class="dashboard-stat-card dashboard-stat-card--blue">
                <div class="dashboard-stat-card__icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <div class="dashboard-stat-card__value">{{ number_format($activeClasses) }}</div>
                    <div class="dashboard-stat-card__label">Lớp đang vận hành</div>
                    <div class="dashboard-stat-card__trend">
                        <i class="fas fa-clock"></i>
                        {{ number_format($classesByShift[0]['value'] + $classesByShift[1]['value'] + $classesByShift[2]['value']) }} lớp đang phân ca
                    </div>
                </div>
            </a>

            <a href="{{ route('staff.dang-ky.index') }}" class="dashboard-stat-card dashboard-stat-card--orange">
                <div class="dashboard-stat-card__icon">
                    <i class="fas fa-file-signature"></i>
                </div>
                <div>
                    <div class="dashboard-stat-card__value">{{ number_format($newRegistrationsToday) }}</div>
                    <div class="dashboard-stat-card__label">Đăng ký mới hôm nay</div>
                    <div class="dashboard-stat-card__trend">
                        <i class="fas fa-bolt"></i>
                        {{ number_format($registrationStatusData[0]['value']) }} hồ sơ chờ thanh toán
                    </div>
                </div>
            </a>

            <a href="{{ route('staff.hoa-don.index') }}" class="dashboard-stat-card dashboard-stat-card--green">
                <div class="dashboard-stat-card__icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div>
                    <div class="dashboard-stat-card__value">
                        {{ number_format($revenueMonth, 0, ',', '.') }}
                        <span class="dashboard-stat-card__unit">đ</span>
                    </div>
                    <div class="dashboard-stat-card__label">Doanh thu ghi nhận trong tháng</div>
                    <div class="{{ $revenueTrendClass }}">
                        <i class="{{ $revenueTrendIcon }}"></i>
                        {{ abs($revenueGrowth) }}% so với tháng trước
                    </div>
                </div>
            </a>
        </section>

        <section class="dashboard-charts">
            <article class="dashboard-card">
                <header class="dashboard-card__header">
                    <h3 class="dashboard-card__title">
                        <i class="fas fa-chart-line me-2"></i>
                        Doanh thu và đăng ký trong {{ $periodDays }} ngày gần nhất
                    </h3>
                    <a href="{{ route('staff.hoa-don.index') }}" class="dashboard-card__link">Xem hóa đơn</a>
                </header>
                <div class="dashboard-card__body">
                    <div class="dashboard-chart-wrap">
                        <canvas id="staffRevenueChart"></canvas>
                    </div>
                </div>
            </article>

            <article class="dashboard-card">
                <header class="dashboard-card__header">
                    <h3 class="dashboard-card__title">
                        <i class="fas fa-chart-donut me-2"></i>
                        Trạng thái hồ sơ đăng ký
                    </h3>
                </header>
                <div class="dashboard-card__body dashboard-card__body--center">
                    <div class="dashboard-donut-wrap">
                        <canvas id="staffRegistrationChart"></canvas>
                    </div>
                    <div class="dashboard-donut-legend">
                        @foreach ($registrationStatusData as $status)
                            <span class="dashboard-donut-legend__item">
                                <i style="background: {{ $status['color'] }}"></i>
                                {{ $status['label'] }}: {{ number_format($status['value']) }}
                            </span>
                        @endforeach
                    </div>
                    <div class="dashboard-footer-info mt-3 justify-content-center">
                        <span>{{ number_format($pendingInvoices) }} hóa đơn chờ xử lý</span>
                        <span>{{ number_format($unreadNotifications) }} thông báo chưa đọc</span>
                    </div>
                </div>
            </article>
        </section>

        <section class="dashboard-bottom">
            <article class="dashboard-card">
                <header class="dashboard-card__header">
                    <h3 class="dashboard-card__title">
                        <i class="fas fa-user-plus me-2"></i>
                        Đăng ký gần đây
                    </h3>
                    <a href="{{ route('staff.dang-ky.index') }}" class="dashboard-card__link">Xem tất cả</a>
                </header>
                <div class="dashboard-card__body">
                    @if ($upcomingRegistrations->isEmpty())
                        <div class="dashboard-empty">
                            <i class="fas fa-inbox"></i>
                            <p>Chưa có đăng ký gần đây.</p>
                        </div>
                    @else
                        <ul class="dashboard-list">
                            @foreach ($upcomingRegistrations as $registration)
                                @php
                                    $studentName = $registration->taiKhoan?->hoSoNguoiDung?->hoTen ?? $registration->taiKhoan?->taiKhoan ?? 'Học viên';
                                    $className = $registration->lopHoc?->tenLopHoc ?? 'Chưa gán lớp';
                                @endphp
                                <li class="dashboard-list__item">
                                    <div class="dashboard-list__main">
                                        <span class="dashboard-list__name">{{ $studentName }}</span>
                                        <span class="dashboard-list__meta">
                                            {{ $className }} · {{ $registration->trangThaiLabel }}
                                        </span>
                                    </div>
                                    <span class="dashboard-list__date">
                                        {{ \Illuminate\Support\Carbon::parse($registration->ngayDangKy)->format('d/m/Y') }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </article>

            <article class="dashboard-card">
                <header class="dashboard-card__header">
                    <h3 class="dashboard-card__title">
                        <i class="fas fa-bolt me-2"></i>
                        Tác vụ nhanh
                    </h3>
                </header>
                <div class="dashboard-card__body">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <div class="rounded-4 p-3 h-100 bg-light">
                                <div class="small text-muted mb-1">Hóa đơn chờ xử lý</div>
                                <div class="fw-semibold fs-5">{{ number_format($pendingInvoices) }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="rounded-4 p-3 h-100 bg-light">
                                <div class="small text-muted mb-1">Thông báo chưa đọc</div>
                                <div class="fw-semibold fs-5">{{ number_format($unreadNotifications) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="dashboard-quick-grid">
                        <a href="{{ route('staff.hoc-vien.index') }}" class="dashboard-quick-btn">
                            <i class="fas fa-user-graduate"></i>
                            <span>Học viên</span>
                        </a>
                        <a href="{{ route('staff.dang-ky.index') }}" class="dashboard-quick-btn">
                            <i class="fas fa-file-signature"></i>
                            <span>Đăng ký</span>
                        </a>
                        <a href="{{ route('staff.lop-hoc.index') }}" class="dashboard-quick-btn">
                            <i class="fas fa-layer-group"></i>
                            <span>Lớp học</span>
                        </a>
                        <a href="{{ route('staff.lop-hoc.create') }}" class="dashboard-quick-btn">
                            <i class="fas fa-plus-circle"></i>
                            <span>Tạo lớp</span>
                        </a>
                        <a href="{{ route('staff.hoa-don.index') }}" class="dashboard-quick-btn">
                            <i class="fas fa-wallet"></i>
                            <span>Hóa đơn</span>
                            @if ($pendingInvoices > 0)
                                <span class="dashboard-quick-badge">{{ number_format($pendingInvoices) }}</span>
                            @endif
                        </a>
                        <a href="{{ route('staff.notifications.index') }}" class="dashboard-quick-btn">
                            <i class="fas fa-bell"></i>
                            <span>Thông báo</span>
                            @if ($unreadNotifications > 0)
                                <span class="dashboard-quick-badge">{{ number_format($unreadNotifications) }}</span>
                            @endif
                        </a>
                    </div>

                    <div class="dashboard-footer-info mt-3">
                        <span>Cập nhật lúc {{ $dashboardGeneratedAt->format('d/m/Y H:i') }}</span>
                        <span>{{ number_format($classesByShift[0]['value']) }} lớp sáng</span>
                        <span>{{ number_format($classesByShift[2]['value']) }} lớp tối</span>
                    </div>
                </div>
            </article>
        </section>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        (() => {
            if (!window.Chart) {
                return;
            }

            const revenueData = @json($revenueChartData);
            const registrationStatusData = @json($registrationStatusData);

            const revenueCtx = document.getElementById('staffRevenueChart');
            if (revenueCtx) {
                new window.Chart(revenueCtx, {
                    type: 'line',
                    data: {
                        labels: revenueData.map(item => item.date),
                        datasets: [{
                            label: 'Doanh thu',
                            data: revenueData.map(item => item.revenue),
                            borderColor: '#27c4b5',
                            backgroundColor: 'rgba(39, 196, 181, 0.14)',
                            tension: 0.35,
                            fill: true,
                            yAxisID: 'y',
                        }, {
                            label: 'Đăng ký',
                            data: revenueData.map(item => item.bookings),
                            borderColor: '#ed8936',
                            backgroundColor: 'rgba(237, 137, 54, 0.12)',
                            borderDash: [6, 6],
                            tension: 0.25,
                            fill: false,
                            yAxisID: 'y1',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(148, 163, 184, 0.18)',
                                },
                            },
                            y1: {
                                beginAtZero: true,
                                position: 'right',
                                grid: {
                                    drawOnChartArea: false,
                                },
                            },
                            x: {
                                grid: {
                                    display: false,
                                },
                            },
                        },
                    }
                });
            }

            const registrationCtx = document.getElementById('staffRegistrationChart');
            if (registrationCtx) {
                new window.Chart(registrationCtx, {
                    type: 'doughnut',
                    data: {
                        labels: registrationStatusData.map(item => item.label),
                        datasets: [{
                            data: registrationStatusData.map(item => item.value),
                            backgroundColor: registrationStatusData.map(item => item.color),
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false,
                            },
                        },
                        cutout: '68%',
                    }
                });
            }
        })();
    </script>
@endsection
