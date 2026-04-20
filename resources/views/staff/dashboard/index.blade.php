@extends('layouts.internal')

@section('title', 'Dashboard nhân viên')
@section('page-title', 'Dashboard nhân viên')
@section('breadcrumb', 'Cổng vận hành')

@section('content')
    <div class="container-fluid px-0">
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Học viên</div>
                        <div class="fs-3 fw-semibold">{{ number_format($totalStudent) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Lớp đang vận hành</div>
                        <div class="fs-3 fw-semibold">{{ number_format($activeClasses) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Đăng ký mới hôm nay</div>
                        <div class="fs-3 fw-semibold">{{ number_format($newRegistrationsToday) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Hóa đơn chờ xử lý</div>
                        <div class="fs-3 fw-semibold">{{ number_format($pendingInvoices) }}</div>
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
                                <h5 class="mb-1">Tổng quan vận hành</h5>
                                <p class="text-muted mb-0">Portal nhân viên đã tách riêng khỏi admin và sẵn sàng nhận các module nghiệp vụ.</p>
                            </div>
                            <div class="badge bg-success-subtle text-success-emphasis">Foundation</div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <a href="{{ route('staff.hoc-vien.index') }}" class="text-decoration-none">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="fw-semibold mb-1">Quản lý học viên</div>
                                        <div class="text-muted small">Danh sách, hồ sơ, import/export và cập nhật trạng thái.</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('staff.dang-ky.index') }}" class="text-decoration-none">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="fw-semibold mb-1">Đăng ký học</div>
                                        <div class="text-muted small">Xác nhận, bảo lưu, hủy hoặc chuyển lớp theo quy trình vận hành.</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('staff.lop-hoc.index') }}" class="text-decoration-none">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="fw-semibold mb-1">Lớp học</div>
                                        <div class="text-muted small">Theo dõi lớp vận hành, buổi học và lịch trình.</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('staff.hoa-don.index') }}" class="text-decoration-none">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="fw-semibold mb-1">Hóa đơn và phiếu thu</div>
                                        <div class="text-muted small">Theo dõi công nợ và chứng từ thanh toán.</div>
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
                            <h5 class="mb-0">Đăng ký gần đây</h5>
                            <a href="{{ route('staff.dang-ky.index') }}" class="btn btn-sm btn-outline-secondary">Xem tất cả</a>
                        </div>
                        @if ($upcomingRegistrations->isEmpty())
                            <div class="text-muted">Chưa có đăng ký gần đây.</div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach ($upcomingRegistrations as $registration)
                                    <div class="list-group-item px-0">
                                        <div class="fw-semibold">{{ optional($registration->taiKhoan->hoSoNguoiDung)->hoTen ?? $registration->taiKhoan->taiKhoan }}</div>
                                        <div class="text-muted small">{{ $registration->lopHoc->tenLopHoc ?? '—' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Doanh thu tháng hiện tại</span>
                            <span class="fw-semibold">{{ number_format($revenueMonth, 0, ',', '.') }}đ</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <span class="text-muted">Thông báo chưa đọc</span>
                            <span class="fw-semibold">{{ number_format($unreadNotifications) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
