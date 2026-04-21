@extends('layouts.internal')

@section('title', 'Lớp học của tôi')
@section('page-title', 'Lớp học của tôi')
@section('breadcrumb', 'Danh sách lớp giáo viên phụ trách')

@section('content')
    <div class="container-fluid px-0">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h5 class="mb-1">Danh sách lớp phụ trách</h5>
                        <p class="text-muted mb-0">Portal giáo viên chỉ hiển thị các lớp gắn với tài khoản hiện tại.</p>
                    </div>
                    <div class="badge bg-info-subtle text-info-emphasis">{{ $classes->total() }} lớp</div>
                </div>

                @if ($classes->isEmpty())
                    <div class="border rounded-4 p-5 text-center text-muted">
                        <i class="fas fa-chalkboard fs-1 mb-3"></i>
                        <p class="mb-0">Chưa có lớp học nào được phân công.</p>
                    </div>
                @else
                    <div class="row g-4">
                        @foreach ($classes as $class)
                            <div class="col-lg-6">
                                <div class="border rounded-4 p-4 h-100">
                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                        <div>
                                            <div class="fw-semibold fs-5">{{ $class->tenLopHoc }}</div>
                                            <div class="text-muted">{{ $class->khoaHoc->tenKhoaHoc ?? 'Chưa gắn khóa học' }}</div>
                                        </div>
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis">{{ $class->trang_thai_label }}</span>
                                    </div>
                                    <div class="row g-3 small">
                                        <div class="col-md-6">
                                            <div class="text-muted">Mã lớp</div>
                                            <div class="fw-semibold">{{ $class->maLopHoc }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="text-muted">Cơ sở</div>
                                            <div class="fw-semibold">{{ $class->coSo->tenCoSo ?? 'Chưa cập nhật' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="text-muted">Ca học</div>
                                            <div class="fw-semibold">{{ $class->caHoc->tenCaHoc ?? 'Chưa gán ca' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="text-muted">Số học viên đăng ký</div>
                                            <div class="fw-semibold">{{ number_format($class->dang_ky_lop_hocs_count) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $classes->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
