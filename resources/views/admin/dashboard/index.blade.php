@extends('layouts.admin')

@section('title', 'Dashboard admin')
@section('page-title', 'Dashboard admin')
@section('breadcrumb', 'Quản trị hệ thống và dữ liệu nền')

@section('content')
    <div class="container-fluid px-0">
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Tổng học viên toàn hệ thống</div>
                        <div class="fs-3 fw-semibold">{{ number_format($totalStudent) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Lớp đang vận hành</div>
                        <div class="fs-3 fw-semibold">{{ number_format($activeClasses) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Doanh thu tháng hiện tại</div>
                        <div class="fs-3 fw-semibold">{{ number_format($revenueMonth, 0, ',', '.') }}đ</div>
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
                                <h5 class="mb-1">Scope của cổng admin</h5>
                                <p class="text-muted mb-0">Admin chỉ giữ phần quản trị hệ thống, dữ liệu nền và CMS sau khi tách portal.</p>
                            </div>
                            <div class="badge bg-danger-subtle text-danger-emphasis">Admin only</div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <a href="{{ route('admin.tai-khoan.index') }}" class="text-decoration-none">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="fw-semibold mb-1">Tài khoản hệ thống</div>
                                        <div class="text-muted small">Quản trị trạng thái tài khoản, reset mật khẩu và kiểm soát hệ thống.</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('admin.cau-hinh.index') }}" class="text-decoration-none">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="fw-semibold mb-1">Cấu hình</div>
                                        <div class="text-muted small">Thiết lập tham số hệ thống và dữ liệu điều hành nền.</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('admin.co-so.index') }}" class="text-decoration-none">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="fw-semibold mb-1">Cơ sở và phòng học</div>
                                        <div class="text-muted small">Dữ liệu cơ sở vật chất, ca học và vận hành cơ sở.</div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('admin.bai-viet.index') }}" class="text-decoration-none">
                                    <div class="border rounded-4 p-3 h-100">
                                        <div class="fw-semibold mb-1">Bài viết và thông báo</div>
                                        <div class="text-muted small">CMS, danh mục nội dung và quản trị thông báo hệ thống.</div>
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
                        <h5 class="mb-3">Phân bổ lớp theo ca</h5>
                        <div class="list-group list-group-flush">
                            @foreach ($classesByShift as $item)
                                <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <span>{{ $item['label'] }}</span>
                                    <span class="fw-semibold">{{ number_format($item['value']) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
