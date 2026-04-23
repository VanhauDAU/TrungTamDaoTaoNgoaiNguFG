@extends('layouts.internal')

@section('title', 'Chi tiết lớp học: ' . $lopHoc->tenLopHoc)
@section('page-title', 'Chi tiết lớp học')
@section('breadcrumb', 'Danh sách Lớp học / ' . $lopHoc->tenLopHoc)

@section('stylesheet')
    <style>
        .glass-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .glass-header::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 60%);
            pointer-events: none;
        }

        .nav-pills-futuristic .nav-link {
            color: #64748b;
            border-radius: 12px;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            background: transparent;
            border: 1px solid transparent;
            margin-right: 0.5rem;
        }

        .nav-pills-futuristic .nav-link:hover {
            color: #334155;
            background: #f8fafc;
        }

        .nav-pills-futuristic .nav-link.active {
            background: #eff6ff;
            color: #2563eb;
            border-color: #bfdbfe;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.1);
        }

        .info-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(8px);
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border-color: #cbd5e1;
        }

        .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .student-item {
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        .student-item:hover {
            background-color: #f8fafc;
            border-left-color: #6366f1;
        }

        .session-card {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .session-card:hover {
            border-color: #6366f1;
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.08);
            transform: translateX(5px);
        }

        .status-badge-glow {
            position: relative;
        }

        .status-badge-glow::before {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: inherit;
            background: inherit;
            filter: blur(6px);
            opacity: 0.4;
            z-index: -1;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid px-0">
        <!-- Header Area -->
        <div class="glass-header rounded-4 p-4 p-md-5 mb-4 text-white">
            <div class="row align-items-center position-relative" style="z-index: 1;">
                <div class="col-lg-8">
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                        <span
                            class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-50 px-3 py-2 rounded-pill status-badge-glow">
                            <i class="fas fa-layer-group me-1"></i> {{ $lopHoc->maLopHoc }}
                        </span>
                        <span
                            class="badge {{ $lopHoc->isOperational() ? 'bg-success' : 'bg-secondary' }} bg-opacity-25 text-white border border-light border-opacity-25 px-3 py-2 rounded-pill status-badge-glow">
                            {{ $lopHoc->trang_thai_label }}
                        </span>
                    </div>
                    <h2 class="fw-bold mb-2">{{ $lopHoc->tenLopHoc }}</h2>
                    <p class="text-secondary opacity-75 fs-5 mb-0">
                        <i class="fas fa-book-reader me-2"></i> {{ $lopHoc->khoaHoc->tenKhoaHoc ?? 'Chưa gắn khóa học' }}
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                    <a href="{{ route('teacher.classes.index') }}"
                        class="btn btn-outline-light rounded-pill px-4 py-2 me-2 shadow-sm border-white border-opacity-50">
                        <i class="fas fa-arrow-left me-2"></i> Trở về
                    </a>
                    <button type="button"
                        class="btn btn-light rounded-pill px-4 py-2 shadow-sm text-primary fw-medium border-0"
                        onclick="joinChat('{{ $lopHoc->slug }}')">
                        <i class="fas fa-comment-dots text-primary me-2"></i> Nhóm chat
                    </button>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-pills nav-pills-futuristic mb-4" id="classTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview"
                    type="button" role="tab" aria-controls="overview" aria-selected="true">
                    <i class="fas fa-info-circle me-2"></i> Tổng quan
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="students-tab" data-bs-toggle="pill" data-bs-target="#students" type="button"
                    role="tab" aria-controls="students" aria-selected="false">
                    <i class="fas fa-users me-2"></i> Học viên ({{ $dangKyLopHocs->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="sessions-tab" data-bs-toggle="pill" data-bs-target="#sessions" type="button"
                    role="tab" aria-controls="sessions" aria-selected="false">
                    <i class="fas fa-calendar-check me-2"></i> Danh sách buổi học
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="materials-tab" data-bs-toggle="pill" data-bs-target="#materials" type="button"
                    role="tab" aria-controls="materials" aria-selected="false">
                    <i class="fas fa-folder-open me-2"></i> Tài liệu
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="classTabContent">

            <!-- Tab Tổng quan -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                <div class="row g-4">
                    <div class="col-md-6 col-xl-3">
                        <div class="info-card p-4 h-100">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-box bg-primary bg-opacity-10 text-primary me-3">
                                    <i class="fas fa-building"></i>
                                </div>
                                <h6 class="text-secondary fw-semibold mb-0">Cơ sở đào tạo</h6>
                            </div>
                            <h5 class="fw-bold text-dark mb-0 ms-1">{{ $lopHoc->coSo->tenCoSo ?? 'N/A' }}</h5>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="info-card p-4 h-100">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-box bg-success bg-opacity-10 text-success me-3">
                                    <i class="fas fa-door-open"></i>
                                </div>
                                <h6 class="text-secondary fw-semibold mb-0">Phòng học</h6>
                            </div>
                            <h5 class="fw-bold text-dark mb-0 ms-1">{{ $lopHoc->phongHoc->tenPhong ?? 'Chưa phân phòng' }}
                            </h5>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="info-card p-4 h-100">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-box bg-warning bg-opacity-10 text-warning me-3">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <h6 class="text-secondary fw-semibold mb-0">Ca học định kỳ</h6>
                            </div>
                            <h5 class="fw-bold text-dark mb-0 ms-1">{{ $lopHoc->caHoc->tenCa ?? 'N/A' }}</h5>
                            @if($lopHoc->caHoc)
                                <div class="small text-muted mt-1 ms-1">
                                    {{ \Carbon\Carbon::parse($lopHoc->caHoc->gioBatDau)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($lopHoc->caHoc->gioKetThuc)->format('H:i') }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="info-card p-4 h-100">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-box bg-info bg-opacity-10 text-info me-3">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <h6 class="text-secondary fw-semibold mb-0">Thời gian</h6>
                            </div>
                            <h5 class="fw-bold text-dark mb-0 ms-1">
                                {{ $lopHoc->ngayBatDau ? \Carbon\Carbon::parse($lopHoc->ngayBatDau)->format('d/m/Y') : 'N/A' }}
                                <i class="fas fa-arrow-right mx-1 text-muted fs-6"></i>
                                {{ $lopHoc->ngayKetThuc ? \Carbon\Carbon::parse($lopHoc->ngayKetThuc)->format('d/m/Y') : 'N/A' }}
                            </h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Danh sách học viên -->
            <div class="tab-pane fade" id="students" role="tabpanel" aria-labelledby="students-tab">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    @if($dangKyLopHocs->isEmpty())
                        <div class="card-body p-5 text-center text-muted">
                            <i class="fas fa-user-slash fa-3x mb-3 text-light-emphasis"></i>
                            <h5>Chưa có học viên nào tham gia lớp học</h5>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4 text-secondary text-uppercase fs-7 py-3">Học viên</th>
                                        <th class="text-secondary text-uppercase fs-7 py-3">Mã Đăng Ký</th>
                                        <th class="text-secondary text-uppercase fs-7 py-3">Tình trạng ghi danh</th>
                                        <th class="text-secondary text-uppercase fs-7 py-3 text-end pe-4">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dangKyLopHocs as $dk)
                                                                <tr class="student-item">
                                                                    <td class="ps-4">
                                                                        <div class="d-flex align-items-center">
                                                                            <img src="{{ $dk->taiKhoan->getAvatarUrl() }}" alt="Avatar"
                                                                                class="rounded-circle shadow-sm" width="40" height="40"
                                                                                style="object-fit: cover">
                                                                            <div class="ms-3">
                                                                                <div class="fw-bold text-dark">
                                                                                    {{ $dk->taiKhoan->hoSoNguoiDung->hoTen ?? $dk->taiKhoan->taiKhoan }}
                                                                                </div>
                                                                                <div class="text-muted small">{{ $dk->taiKhoan->email ?? 'Không có email' }}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <span
                                                                            class="badge bg-light text-dark border font-monospace">#DK-{{ $dk->dangKyLopHocId }}</span>
                                                                    </td>
                                                                    <td>
                                                                        @php
                                                                            $statusColor = match ((int) $dk->trangThai) {
                                                                                \App\Models\Education\DangKyLopHoc::TRANG_THAI_DA_XAC_NHAN => 'info',
                                                                                \App\Models\Education\DangKyLopHoc::TRANG_THAI_DANG_HOC => 'success',
                                                                                default => 'secondary'
                                                                            };
                                                                        @endphp
                                         <span
                                                                            class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }} border border-{{ $statusColor }}-subtle rounded-pill px-3 py-1">
                                                                            {{ $dk->trang_thai_label }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="text-end pe-4">
                                                                        <button type="button" class="btn btn-sm btn-light rounded-circle" title="Nhắn tin"
                                                                            onclick="joinChat('{{ $lopHoc->slug }}')">
                                                                            <i class="fas fa-paper-plane text-indigo"></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tab Danh sách buổi học -->
            <div class="tab-pane fade" id="sessions" role="tabpanel" aria-labelledby="sessions-tab">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        @if($buoiHocs->isEmpty())
                            <div class="text-center p-5 text-muted">
                                <i class="fas fa-calendar-times fa-3x mb-3 text-light-emphasis"></i>
                                <h5>Chưa có buổi học nào được lên kế hoạch</h5>
                            </div>
                        @else
                            <div class="row g-3">
                                @foreach($buoiHocs as $index => $buoiHoc)
                                    <div class="col-md-6 col-xxl-4">
                                        <div class="session-card p-3 bg-white h-100 d-flex flex-column">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div class="badge bg-light text-secondary border">Buổi {{ $index + 1 }}</div>
                                                <div class="opacity-75 fs-4">
                                                    <i
                                                        class="fas {{ $buoiHoc->trangThaiIcon }} {{ $buoiHoc->isCompleted() ? 'text-success' : ($buoiHoc->isLive() ? 'text-danger' : 'text-primary') }}"></i>
                                                </div>
                                            </div>
                                            <h6 class="fw-bold mb-2">{{ $buoiHoc->tenBuoiHoc }}</h6>
                                            <div class="d-flex align-items-center text-muted small mb-2">
                                                <i class="far fa-calendar-alt me-2 w-15px"></i>
                                                {{ \Carbon\Carbon::parse($buoiHoc->ngayHoc)->format('d/m/Y') }}
                                            </div>
                                            <div class="d-flex align-items-center text-muted small mb-3">
                                                <i class="far fa-clock me-2 w-15px"></i>
                                                {{ $buoiHoc->caHoc->tenCa ?? 'N/A' }}
                                                @if($buoiHoc->caHoc)
                                                    ({{ \Carbon\Carbon::parse($buoiHoc->caHoc->gioBatDau)->format('H:i') }} -
                                                    {{ \Carbon\Carbon::parse($buoiHoc->caHoc->gioKetThuc)->format('H:i') }})
                                                @endif
                                            </div>

                                            <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                                                <span
                                                    class="badge {{ $buoiHoc->isUpcoming() ? 'bg-secondary' : ($buoiHoc->isCompleted() ? 'bg-success' : 'bg-primary') }}-subtle text-dark border">
                                                    {{ $buoiHoc->trang_thai_label }}
                                                </span>
                                                @if($buoiHoc->daHoanThanh)
                                                    <span class="text-success small fw-medium"><i class="fas fa-check-circle me-1"></i>
                                                        Đã dạy</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tab Tài liệu -->
            <div class="tab-pane fade" id="materials" role="tabpanel" aria-labelledby="materials-tab">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-5 text-center text-muted">
                        <i class="fas fa-file-pdf fa-4x mb-3 text-primary opacity-50"></i>
                        <h5 class="fw-semibold pb-2">Tài liệu học tập</h5>
                        <p class="mb-0">Tính năng chia sẻ tài liệu đối với lớp học đang trong quá trình phát triển.</p>
                    </div>
                </div>
            </div>

        </div>
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