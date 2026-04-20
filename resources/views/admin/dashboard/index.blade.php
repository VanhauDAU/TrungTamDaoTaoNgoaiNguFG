@extends('layouts.admin')

@section('title', 'Dashboard admin')
@section('page-title', 'Dashboard admin')
@section('breadcrumb', 'Quản trị hệ thống và dữ liệu nền')

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
                <h2 class="dashboard-welcome__title">Bảng điều hành quản trị trung tâm</h2>
                <p class="dashboard-welcome__sub">
                    Theo dõi tăng trưởng tuyển sinh, dòng tiền tháng này, lớp đang vận hành và các đầu việc quản trị trọng yếu
                    trên cùng một màn hình.
                </p>
            </div>
            <div class="dashboard-welcome__badge">
                <i class="fas fa-shield-halved"></i>
                Admin control center
            </div>
        </section>

        <section class="dashboard-stats">
            <a href="{{ route('admin.tai-khoan.index') }}" class="dashboard-stat-card dashboard-stat-card--teal">
                <div class="dashboard-stat-card__icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <div class="dashboard-stat-card__value">{{ number_format($totalStudent) }}</div>
                    <div class="dashboard-stat-card__label">Học viên toàn hệ thống</div>
                    <div class="{{ $registrationTrendClass }}">
                        <i class="{{ $registrationTrendIcon }}"></i>
                        {{ abs($registrationTrend) }}% so với hôm qua
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.giao-vien.index') }}" class="dashboard-stat-card dashboard-stat-card--blue">
                <div class="dashboard-stat-card__icon">
                    <i class="fas fa-chalkboard-user"></i>
                </div>
                <div>
                    <div class="dashboard-stat-card__value">{{ number_format($totalTeacher) }}</div>
                    <div class="dashboard-stat-card__label">Giáo viên đang quản lý</div>
                    <div class="dashboard-stat-card__trend">
                        <i class="fas fa-users"></i>
                        {{ number_format($totalStaff) }} nhân viên vận hành
                    </div>
                </div>
            </a>

            <a href="{{ route('admin.khoa-hoc.index') }}" class="dashboard-stat-card dashboard-stat-card--orange">
                <div class="dashboard-stat-card__icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <div class="dashboard-stat-card__value">{{ number_format($activeClasses) }}</div>
                    <div class="dashboard-stat-card__label">Lớp đang vận hành</div>
                    <div class="dashboard-stat-card__trend">
                        <i class="fas fa-file-signature"></i>
                        {{ number_format($newRegistrationsToday) }} đăng ký mới hôm nay
                    </div>
                </div>
            </a>

            <div class="dashboard-stat-card dashboard-stat-card--green">
                <div class="dashboard-stat-card__icon">
                    <i class="fas fa-sack-dollar"></i>
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
            </div>
        </section>

        <section class="dashboard-charts">
            <article class="dashboard-card">
                <header class="dashboard-card__header">
                    <h3 class="dashboard-card__title">
                        <i class="fas fa-chart-line me-2"></i>
                        Doanh thu và đăng ký trong 14 ngày gần nhất
                    </h3>
                </header>
                <div class="dashboard-card__body">
                    <div class="dashboard-chart-wrap">
                        <canvas id="adminRevenueChart"></canvas>
                    </div>
                </div>
            </article>

            <article class="dashboard-card">
                <header class="dashboard-card__header">
                    <h3 class="dashboard-card__title">
                        <i class="fas fa-clock me-2"></i>
                        Phân bổ lớp theo ca học
                    </h3>
                </header>
                <div class="dashboard-card__body dashboard-card__body--center">
                    <div class="dashboard-donut-wrap">
                        <canvas id="adminShiftChart"></canvas>
                    </div>
                    <div class="dashboard-donut-legend">
                        @foreach ($classesByShift as $shift)
                            <span class="dashboard-donut-legend__item">
                                <i style="background: {{ $shift['color'] }}"></i>
                                {{ $shift['label'] }}: {{ number_format($shift['value']) }}
                            </span>
                        @endforeach
                    </div>
                    <div class="dashboard-footer-info mt-3 justify-content-center">
                        <span>{{ number_format($pendingInvoicesCount) }} hóa đơn chờ thanh toán</span>
                        <span>{{ number_format($revenueSummary['lastMonth'] ?? 0, 0, ',', '.') }}đ tháng trước</span>
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
                </header>
                <div class="dashboard-card__body">
                    @if ($recentRegistrations->isEmpty())
                        <div class="dashboard-empty">
                            <i class="fas fa-inbox"></i>
                            <p>Chưa có đăng ký mới trong thời gian gần đây.</p>
                        </div>
                    @else
                        <ul class="dashboard-list">
                            @foreach ($recentRegistrations as $registration)
                                @php
                                    $studentName = $registration->taiKhoan?->hoSoNguoiDung?->hoTen ?? $registration->taiKhoan?->taiKhoan ?? 'Học viên';
                                    $className = $registration->lopHoc?->tenLopHoc ?? $registration->lopHoc?->khoaHoc?->tenKhoaHoc ?? 'Chưa gán lớp';
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
                    <div class="dashboard-quick-grid">
                        <a href="{{ route('admin.tai-khoan.index') }}" class="dashboard-quick-btn">
                            <i class="fas fa-users-cog"></i>
                            <span>Tài khoản</span>
                        </a>
                        <a href="{{ route('admin.giao-vien.index') }}" class="dashboard-quick-btn">
                            <i class="fas fa-person-chalkboard"></i>
                            <span>Giáo viên</span>
                        </a>
                        <a href="{{ route('admin.khoa-hoc.index') }}" class="dashboard-quick-btn">
                            <i class="fas fa-book-open"></i>
                            <span>Khóa học</span>
                        </a>
                        <a href="{{ route('admin.co-so.index') }}" class="dashboard-quick-btn">
                            <i class="fas fa-building"></i>
                            <span>Cơ sở</span>
                        </a>
                        <a href="{{ route('admin.thong-bao.index') }}" class="dashboard-quick-btn">
                            <i class="fas fa-bell"></i>
                            <span>Thông báo</span>
                        </a>
                        <a href="{{ route('admin.bai-viet.index') }}" class="dashboard-quick-btn">
                            <i class="fas fa-newspaper"></i>
                            <span>Bài viết</span>
                        </a>
                        <a href="{{ route('admin.cau-hinh.index') }}" class="dashboard-quick-btn">
                            <i class="fas fa-sliders"></i>
                            <span>Cấu hình</span>
                        </a>
                        <a href="{{ route('admin.lien-he.index') }}" class="dashboard-quick-btn">
                            <i class="fas fa-headset"></i>
                            <span>Liên hệ</span>
                        </a>
                    </div>

                    <div class="dashboard-footer-info mt-3">
                        <span>Cập nhật lúc {{ $dashboardGeneratedAt->format('d/m/Y H:i') }}</span>
                        <span>{{ number_format($newRegistrationsToday) }} đăng ký mới hôm nay</span>
                        <span>{{ number_format($pendingInvoicesCount) }} hóa đơn cần xử lý</span>
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
            const shiftData = @json($classesByShift);

            const revenueCtx = document.getElementById('adminRevenueChart');
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
                            borderColor: '#4299e1',
                            backgroundColor: 'rgba(66, 153, 225, 0.12)',
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

            const shiftCtx = document.getElementById('adminShiftChart');
            if (shiftCtx) {
                new window.Chart(shiftCtx, {
                    type: 'doughnut',
                    data: {
                        labels: shiftData.map(item => item.label),
                        datasets: [{
                            data: shiftData.map(item => item.value),
                            backgroundColor: shiftData.map(item => item.color),
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
