@extends('layouts.internal')

@section('title', 'Dashboard giáo viên')
@section('page-title', 'Dashboard giáo viên')
@section('breadcrumb', 'Cổng giảng dạy')

@section('content')
    <div class="container-fluid px-0">
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Lớp phụ trách</div>
                        <div class="fs-3 fw-semibold">{{ number_format($totalClasses) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Lớp đang hoạt động</div>
                        <div class="fs-3 fw-semibold">{{ number_format($activeClasses) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Buổi học sắp tới</div>
                        <div class="fs-3 fw-semibold">{{ number_format($upcomingSessionsCount) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Thông báo chưa đọc</div>
                        <div class="fs-3 fw-semibold">{{ number_format($unreadNotifications) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="mb-1">Không gian làm việc của giáo viên</h5>
                                <p class="text-muted mb-0">Phase này chuẩn hóa route, layout và navigation. Các module nghiệp vụ sẽ đi tiếp trên nền portal mới.</p>
                            </div>
                            <div class="badge bg-primary-subtle text-primary-emphasis">Teacher portal</div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <a href="{{ route('teacher.profile') }}" class="text-decoration-none">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="fw-semibold mb-1">Hồ sơ</div>
                                        <div class="text-muted small">Theo dõi thông tin cá nhân và hồ sơ nhân sự.</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('teacher.classes.index') }}" class="text-decoration-none">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="fw-semibold mb-1">Lớp học của tôi</div>
                                        <div class="text-muted small">Danh sách lớp đang phụ trách, sĩ số và trạng thái lớp.</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('teacher.schedule.index') }}" class="text-decoration-none">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="fw-semibold mb-1">Lịch dạy</div>
                                        <div class="text-muted small">Tập trung các buổi học sắp diễn ra và thay đổi lịch.</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('teacher.notifications.index') }}" class="text-decoration-none">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="fw-semibold mb-1">Thông báo</div>
                                        <div class="text-muted small">Theo dõi nhắc việc và thông báo hệ thống theo tài khoản giáo viên.</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Lịch dạy gần nhất</h5>
                            <a href="{{ route('teacher.schedule.index') }}" class="btn btn-sm btn-outline-secondary">Xem lịch</a>
                        </div>
                        @if ($upcomingSessions->isEmpty())
                            <div class="text-muted">Chưa có buổi học nào được xếp lịch.</div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach ($upcomingSessions as $session)
                                    <div class="list-group-item px-0">
                                        <div class="fw-semibold">{{ $session->tenBuoiHoc ?: ($session->lopHoc->tenLopHoc ?? 'Buổi học') }}</div>
                                        <div class="text-muted small">
                                            {{ \Illuminate\Support\Carbon::parse($session->ngayHoc)->format('d/m/Y') }}
                                            · {{ $session->phongHoc->tenPhong ?? 'Chưa gán phòng' }}
                                            · {{ $session->caHoc->tenCaHoc ?? 'Chưa gán ca' }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
