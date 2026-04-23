@extends('layouts.internal')

@section('title', 'Lớp học của tôi')
@section('page-title', 'Lớp học của tôi')
@section('breadcrumb', 'Danh sách lớp giáo viên phụ trách')

@section('stylesheet')
    <style>
        .class-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(226, 232, 240, 0.8) !important;
            position: relative;
            overflow: hidden;
        }

        .class-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.05) !important;
            border-color: rgba(99, 102, 241, 0.3) !important;
        }

        .class-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #6366f1, #06b6d4);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .class-card:hover::before {
            opacity: 1;
        }

        .card-actions {
            border-top: 1px solid rgba(226, 232, 240, 0.8);
            padding-top: 1rem;
            margin-top: 1.5rem;
        }

        .btn-futuristic-outline {
            border: 1px solid #cbd5e1;
            color: #475569;
            transition: all 0.25s ease;
            border-radius: 8px;
        }

        .btn-futuristic-outline:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #94a3b8;
        }

        .btn-futuristic-primary {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
            transition: all 0.25s ease;
        }

        .btn-futuristic-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
            color: white;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1 fw-bold text-dark">Danh sách lớp phụ trách</h4>
                <p class="text-muted mb-0">Portal giáo viên chỉ hiển thị các lớp gắn với tài khoản hiện tại.</p>
            </div>
            <div class="badge bg-primary text-white shadow-sm px-3 py-2 rounded-pill fs-6 border border-primary-subtle">
                <i class="fas fa-layer-group me-1"></i> {{ $classes->total() }} lớp
            </div>
        </div>

        @if ($classes->isEmpty())
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-5 text-center text-muted">
                    <div class="mb-4">
                        <i class="fas fa-chalkboard fa-4x text-primary opacity-50"></i>
                    </div>
                    <h5 class="fw-semibold">Chưa có lớp học nào</h5>
                    <p class="mb-0">Bạn chưa được phân công phụ trách lớp học nào vào thời điểm này.</p>
                </div>
            </div>
        @else
            <div class="row g-4">
                @foreach ($classes as $class)
                    <div class="col-lg-6 col-xl-4">
                        <div class="class-card rounded-4 p-4 h-100 d-flex flex-column shadow-sm">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <div>
                                    <div class="fw-bold fs-5 text-dark mb-1">{{ $class->tenLopHoc }}</div>
                                    <div class="badge bg-light text-secondary border">Mã: {{ $class->maLopHoc }}</div>
                                </div>
                                <span
                                    class="badge {{ $class->isOperational() ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }} px-2 py-1 rounded-3">
                                    {{ $class->trang_thai_label }}
                                </span>
                            </div>

                            <div class="mb-3 text-secondary small">
                                <i class="fas fa-book-open me-2 text-primary"></i>
                                {{ $class->khoaHoc->tenKhoaHoc ?? 'Chưa gắn khóa học' }}
                            </div>

                            <div class="row g-3 small mb-auto">
                                <div class="col-6">
                                    <div class="d-flex align-items-center text-muted mb-1">
                                        <i class="fas fa-building me-2 w-15px"></i> Cơ sở
                                    </div>
                                    <div class="fw-semibold text-dark">{{ $class->coSo->tenCoSo ?? 'N/A' }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center text-muted mb-1">
                                        <i class="fas fa-clock me-2 w-15px"></i> Ca học
                                    </div>
                                    <div class="fw-semibold text-dark">{{ $class->caHoc->tenCa ?? 'N/A' }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center text-muted mb-1">
                                        <i class="fas fa-users me-2 w-15px"></i> Học viên
                                    </div>
                                    <div class="fw-semibold text-dark">{{ number_format($class->dang_ky_lop_hocs_count) }} /
                                        {{ $class->soHocVienToiDa ?? '∞' }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center text-muted mb-1">
                                        <i class="fas fa-calendar-alt me-2 w-15px"></i> Ngày bắt đầu
                                    </div>
                                    <div class="fw-semibold text-dark">
                                        {{ $class->ngayBatDau ? \Carbon\Carbon::parse($class->ngayBatDau)->format('d/m/Y') : 'Chưa định' }}
                                    </div>
                                </div>
                            </div>

                            @php
                                $totalBuoi = $class->buoi_hocs_count ?? 0;
                                $buoiDaDay = $class->buoi_da_day_count ?? 0;
                                $progress = $totalBuoi > 0 ? round(($buoiDaDay / $totalBuoi) * 100) : 0;
                            @endphp
                            <div class="mb-3 mt-3">
                                <div class="d-flex justify-content-between align-items-center mb-1 small">
                                    <span class="text-muted fw-medium">Tiến độ khóa học</span>
                                    <span class="fw-bold text-primary">{{ $progress }}% ({{ $buoiDaDay }}/{{ $totalBuoi }})</span>
                                </div>
                                <div class="progress rounded-pill bg-light border" style="height: 6px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $progress }}%"
                                        aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>

                            <div class="card-actions d-flex gap-2">
                                <a href="{{ route('teacher.classes.show', $class->slug) }}"
                                    class="btn btn-futuristic-outline flex-grow-1 py-2 fw-medium">
                                    <i class="fas fa-eye me-1"></i> Xem chi tiết
                                </a>
                                <button type="button" class="btn btn-futuristic-primary py-2 px-3"
                                    onclick="joinChat('{{ $class->slug }}')" title="Tham gia nhóm chat">
                                    <i class="fas fa-comment-dots"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 d-flex justify-content-center">
                {{ $classes->links() }}
            </div>
        @endif
    </div>
@endsection

@section('script')
    <script>
        function joinChat(slug) {
            Swal.fire({
                title: 'Đang phát triển!',
                text: 'Tính năng "Tham gia nhóm chat" dành cho Giáo viên đang được nâng cấp và sẽ sớm ra mắt.',
                icon: 'info',
                confirmButtonText: 'Đã hiểu',
                confirmButtonColor: '#6366f1',
                background: '#ffffff',
                customClass: {
                    popup: 'rounded-4 shadow-lg border-0',
                    confirmButton: 'btn btn-primary px-4 py-2 rounded-pill'
                }
            });
        }
    </script>
@endsection