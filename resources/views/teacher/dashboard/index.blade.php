@extends('layouts.internal')

@section('title', 'Dashboard giáo viên')
@section('page-title', 'Dashboard giáo viên')
@section('breadcrumb', 'Cổng giảng dạy')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/dashboard/index.css') }}">
@endsection

@section('content')
    <div class="container-fluid px-0">
        <section class="dashboard-welcome">
            <div>
                <h2 class="dashboard-welcome__title">Không gian điều phối giảng dạy</h2>
                <p class="dashboard-welcome__sub">
                    Theo dõi lịch dạy tuần này, trạng thái lớp đang phụ trách, số ca hôm nay và các đầu việc lớp học
                    ngay trong một dashboard gọn, dễ thao tác.
                </p>
            </div>
            <div class="dashboard-welcome__badge">
                <i class="fas fa-person-chalkboard"></i>
                Teaching workspace
            </div>
        </section>

        <section class="dashboard-stats">
            <a href="{{ route('teacher.classes.index') }}" class="dashboard-stat-card dashboard-stat-card--teal">
                <div class="dashboard-stat-card__icon">
                    <i class="fas fa-school"></i>
                </div>
                <div>
                    <div class="dashboard-stat-card__value">{{ number_format($totalClasses) }}</div>
                    <div class="dashboard-stat-card__label">Tổng lớp phụ trách</div>
                    <div class="dashboard-stat-card__trend">
                        <i class="fas fa-layer-group"></i>
                        {{ number_format($activeClasses) }} lớp đang hoạt động
                    </div>
                </div>
            </a>

            <a href="{{ route('teacher.schedule.index') }}" class="dashboard-stat-card dashboard-stat-card--blue">
                <div class="dashboard-stat-card__icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div>
                    <div class="dashboard-stat-card__value">{{ number_format($todaySessionsCount) }}</div>
                    <div class="dashboard-stat-card__label">Buổi dạy trong hôm nay</div>
                    <div class="dashboard-stat-card__trend">
                        <i class="fas fa-calendar-week"></i>
                        {{ number_format($upcomingSessionsCount) }} buổi sắp tới
                    </div>
                </div>
            </a>

            <a href="{{ route('teacher.attendance.index') }}" class="dashboard-stat-card dashboard-stat-card--orange">
                <div class="dashboard-stat-card__icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div>
                    <div class="dashboard-stat-card__value">{{ number_format($attendanceSummary['present'] + $attendanceSummary['late']) }}</div>
                    <div class="dashboard-stat-card__label">Lượt điểm danh có mặt</div>
                    <div class="dashboard-stat-card__trend">
                        <i class="fas fa-user-clock"></i>
                        {{ number_format($attendanceSummary['late']) }} lượt đi trễ
                    </div>
                </div>
            </a>

            <a href="{{ route('teacher.notifications.index') }}" class="dashboard-stat-card dashboard-stat-card--green">
                <div class="dashboard-stat-card__icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div>
                    <div class="dashboard-stat-card__value">{{ number_format($unreadNotifications) }}</div>
                    <div class="dashboard-stat-card__label">Thông báo chưa đọc</div>
                    <div class="dashboard-stat-card__trend">
                        <i class="fas fa-person-rays"></i>
                        {{ number_format($liveSessionsCount) }} buổi đang diễn ra
                    </div>
                </div>
            </a>
        </section>

        <section class="dashboard-charts">
            <article class="dashboard-card">
                <header class="dashboard-card__header">
                    <h3 class="dashboard-card__title">
                        <i class="fas fa-chart-column me-2"></i>
                        Tải giảng dạy trong 7 ngày tới
                    </h3>
                    <a href="{{ route('teacher.schedule.index') }}" class="dashboard-card__link">Xem lịch đầy đủ</a>
                </header>
                <div class="dashboard-card__body">
                    <div class="dashboard-chart-wrap">
                        <canvas id="teacherScheduleChart"></canvas>
                    </div>
                </div>
            </article>

            <article class="dashboard-card">
                <header class="dashboard-card__header">
                    <h3 class="dashboard-card__title">
                        <i class="fas fa-chart-pie me-2"></i>
                        Trạng thái lớp phụ trách
                    </h3>
                </header>
                <div class="dashboard-card__body dashboard-card__body--center">
                    <div class="dashboard-donut-wrap">
                        <canvas id="teacherClassStatusChart"></canvas>
                    </div>
                    <div class="dashboard-donut-legend">
                        @foreach ($classStatusData as $status)
                            <span class="dashboard-donut-legend__item">
                                <i style="background: {{ $status['color'] }}"></i>
                                {{ $status['label'] }}: {{ number_format($status['value']) }}
                            </span>
                        @endforeach
                    </div>
                    <div class="dashboard-footer-info mt-3 justify-content-center">
                        <span>{{ number_format($attendanceSummary['absent']) }} lượt vắng không phép hoặc khóa học phí</span>
                        <span>{{ number_format($attendanceSummary['excused']) }} lượt vắng có phép</span>
                    </div>
                </div>
            </article>
        </section>

        <section class="dashboard-bottom">
            <article class="dashboard-card">
                <header class="dashboard-card__header">
                    <h3 class="dashboard-card__title">
                        <i class="fas fa-calendar-check me-2"></i>
                        Lịch dạy sắp tới
                    </h3>
                </header>
                <div class="dashboard-card__body">
                    @if ($upcomingSessions->isEmpty())
                        <div class="dashboard-empty">
                            <i class="fas fa-calendar-xmark"></i>
                            <p>Chưa có buổi học nào được xếp lịch trong các ngày tới.</p>
                        </div>
                    @else
                        <ul class="dashboard-list">
                            @foreach ($upcomingSessions as $session)
                                <li class="dashboard-list__item">
                                    <div class="dashboard-list__main">
                                        <span class="dashboard-list__name">
                                            {{ $session->tenBuoiHoc ?: ($session->lopHoc->tenLopHoc ?? 'Buổi học') }}
                                        </span>
                                        <span class="dashboard-list__meta">
                                            {{ $session->lopHoc->khoaHoc->tenKhoaHoc ?? 'Khóa học' }}
                                            · {{ $session->phongHoc->tenPhong ?? 'Chưa gán phòng' }}
                                            · {{ $session->caHoc->tenCaHoc ?? 'Chưa gán ca' }}
                                        </span>
                                    </div>
                                    <span class="dashboard-list__date">
                                        {{ \Illuminate\Support\Carbon::parse($session->ngayHoc)->format('d/m/Y') }}
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
                                <div class="small text-muted mb-1">Có mặt / đi trễ</div>
                                <div class="fw-semibold fs-5">{{ number_format($attendanceSummary['present'] + $attendanceSummary['late']) }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="rounded-4 p-3 h-100 bg-light">
                                <div class="small text-muted mb-1">Vắng / nợ học phí</div>
                                <div class="fw-semibold fs-5">{{ number_format($attendanceSummary['absent']) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="dashboard-quick-grid">
                        <a href="{{ route('teacher.profile') }}" class="dashboard-quick-btn">
                            <i class="fas fa-id-card"></i>
                            <span>Hồ sơ</span>
                        </a>
                        <a href="{{ route('teacher.classes.index') }}" class="dashboard-quick-btn">
                            <i class="fas fa-school"></i>
                            <span>Lớp học</span>
                        </a>
                        <a href="{{ route('teacher.schedule.index') }}" class="dashboard-quick-btn">
                            <i class="fas fa-calendar-week"></i>
                            <span>Lịch dạy</span>
                        </a>
                        <a href="{{ route('teacher.attendance.index') }}" class="dashboard-quick-btn">
                            <i class="fas fa-clipboard-user"></i>
                            <span>Điểm danh</span>
                        </a>
                        <a href="{{ route('teacher.evaluations.index') }}" class="dashboard-quick-btn">
                            <i class="fas fa-star-half-stroke"></i>
                            <span>Nhận xét</span>
                        </a>
                        <a href="{{ route('teacher.materials.index') }}" class="dashboard-quick-btn">
                            <i class="fas fa-folder-open"></i>
                            <span>Tài liệu</span>
                        </a>
                        <a href="{{ route('teacher.notifications.index') }}" class="dashboard-quick-btn">
                            <i class="fas fa-bell"></i>
                            <span>Thông báo</span>
                            @if ($unreadNotifications > 0)
                                <span class="dashboard-quick-badge">{{ number_format($unreadNotifications) }}</span>
                            @endif
                        </a>
                    </div>

                    <div class="dashboard-footer-info mt-3">
                        <span>Cập nhật lúc {{ $dashboardGeneratedAt->format('d/m/Y H:i') }}</span>
                        <span>{{ number_format($todaySessionsCount) }} ca dạy hôm nay</span>
                        <span>{{ number_format($liveSessionsCount) }} buổi đang diễn ra</span>
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

            const scheduleData = @json($weeklyScheduleData);
            const classStatusData = @json($classStatusData);

            const scheduleCtx = document.getElementById('teacherScheduleChart');
            if (scheduleCtx) {
                new window.Chart(scheduleCtx, {
                    type: 'bar',
                    data: {
                        labels: scheduleData.map(item => item.label),
                        datasets: [{
                            label: 'Số buổi',
                            data: scheduleData.map(item => item.count),
                            backgroundColor: '#27c4b5',
                            borderRadius: 10,
                            maxBarThickness: 38,
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
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                },
                                grid: {
                                    color: 'rgba(148, 163, 184, 0.18)',
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

            const classStatusCtx = document.getElementById('teacherClassStatusChart');
            if (classStatusCtx) {
                new window.Chart(classStatusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: classStatusData.map(item => item.label),
                        datasets: [{
                            data: classStatusData.map(item => item.value),
                            backgroundColor: classStatusData.map(item => item.color),
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
