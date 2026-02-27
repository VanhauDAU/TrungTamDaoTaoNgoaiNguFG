@extends('layouts.client')

@section('title', $class->tenLopHoc . ' - ' . $class->khoaHoc->tenKhoaHoc)

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/classesDetail.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/courseDetail.css') }}">
@endsection

@section('content')
    <section class="class-detail-page pt-5 pb-5">
        <div class="custom-container">
            {{-- BREADCRUMB --}}
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home.index') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('home.courses.index') }}">Khóa học</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('home.courses.show', $class->khoaHoc->slug) }}">
                            {{ $class->khoaHoc->tenKhoaHoc }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $class->tenLopHoc }}</li>
                </ol>
            </nav>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row g-4">
                {{-- LEFT COLUMN --}}
                <div class="col-lg-8">
                    {{-- HEADER --}}
                    <div class="class-detail-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-gradient-primary mb-2">
                                <i class="fas fa-layer-group me-1"></i>
                                {{ $class->khoaHoc->loaiKhoaHoc->tenLoai ?? 'Khóa học' }}
                            </span>

                            @if ($class->trangThai == 0)
                                <span class="status-badge" style="background:#e3f2fd;color:#1565c0"><i
                                        class="fas fa-hourglass-start me-1"></i> Sắp mở</span>
                            @elseif ($class->trangThai == 1)
                                <span class="status-badge status-open"><i class="fas fa-check-circle me-1"></i> Đang mở đăng
                                    ký</span>
                            @elseif ($class->trangThai == 4)
                                <span class="status-badge" style="background:#e8f5e9;color:#2e7d32"><i
                                        class="fas fa-chalkboard-teacher me-1"></i> Đang học</span>
                            @elseif ($class->trangThai == 3)
                                <span class="status-badge status-closed"><i class="fas fa-ban me-1"></i> Đã hủy</span>
                            @else
                                <span class="status-badge status-closed"><i class="fas fa-lock me-1"></i> Đã đóng đăng
                                    ký</span>
                            @endif
                        </div>

                        <h1 class="mb-3 fw-bold text-dark">{{ $class->tenLopHoc }}</h1>

                        <div class="d-flex align-items-center text-muted mb-4">
                            <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                            <span>{{ $class->coSo->tenCoSo }} - {{ $class->coSo->diaChi }}</span>
                        </div>
                    </div>

                    {{-- DETAIL INFO --}}
                    <div class="class-detail-card">
                        <h4 class="mb-4 fw-bold">Thông tin chi tiết</h4>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-icon">
                                        <i class="far fa-calendar-alt"></i>
                                    </div>
                                    <div>
                                        <div class="info-label">Ngày bắt đầu</div>
                                        <div class="info-value">
                                            {{ \Carbon\Carbon::parse($class->ngayBatDau)->format('d/m/Y') }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-icon">
                                        <i class="far fa-calendar-check"></i>
                                    </div>
                                    <div>
                                        <div class="info-label">Ngày kết thúc (dự kiến)</div>
                                        <div class="info-value">
                                            {{ \Carbon\Carbon::parse($class->ngayKetThuc)->format('d/m/Y') }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-icon">
                                        <i class="far fa-clock"></i>
                                    </div>
                                    <div>
                                        <div class="info-label">Thời lượng</div>
                                        <div class="info-value">{{ $class->soBuoiDuKien ?? 0 }} buổi</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div>
                                        <div class="info-label">Sĩ số</div>
                                        <div class="info-value">
                                            {{ $class->dangKyLopHocs->where('trangThai', '!=', 0)->count() }}/{{ $class->soHocVienToiDa }}
                                            học viên</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- LỊCH HỌC TRONG TUẦN --}}
                    @if ($class->lichHoc)
                        <div class="class-detail-card">
                            <h4 class="mb-4 fw-bold">
                                <i class="fas fa-calendar-week me-2 text-primary"></i>
                                Lịch học trong tuần
                            </h4>
                            <div class="week-schedule-container">
                                @php
                                    $weekDays = [
                                        '2' => ['label' => 'Thứ 2', 'short' => 'T2'],
                                        '3' => ['label' => 'Thứ 3', 'short' => 'T3'],
                                        '4' => ['label' => 'Thứ 4', 'short' => 'T4'],
                                        '5' => ['label' => 'Thứ 5', 'short' => 'T5'],
                                        '6' => ['label' => 'Thứ 6', 'short' => 'T6'],
                                        '7' => ['label' => 'Thứ 7', 'short' => 'T7'],
                                        'CN' => ['label' => 'Chủ nhật', 'short' => 'CN'],
                                    ];
                                    // Parse schedule - support both comma-separated and JSON format
                                    $scheduleData = $class->lichHoc;
                                    if (str_contains($scheduleData, '[')) {
                                        $schedule = json_decode($scheduleData, true) ?? [];
                                    } else {
                                        $schedule = array_filter(array_map('trim', explode(',', $scheduleData)));
                                    }
                                @endphp
                                <div class="week-schedule d-flex justify-content-between gap-2">
                                    @foreach ($weekDays as $key => $day)
                                        <div class="day-box {{ in_array($key, $schedule) ? 'active' : '' }}">
                                            <div class="day-label">{{ $day['short'] }}</div>
                                            @if (in_array($key, $schedule))
                                                <i class="fas fa-check-circle day-check"></i>
                                                @if ($class->caHoc)
                                                    <div class="day-time">
                                                        {{ \Carbon\Carbon::parse($class->caHoc->gioBatDau)->format('H:i') }}
                                                        -
                                                        {{ \Carbon\Carbon::parse($class->caHoc->gioKetThuc)->format('H:i') }}
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <div class="schedule-note mt-3 text-center">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <small class="text-muted">
                                        Lớp học diễn ra vào các ngày:
                                        <strong>
                                            @foreach ($schedule as $index => $dayKey)
                                                {{ $weekDays[$dayKey]['label'] ?? $dayKey }}{{ $index < count($schedule) - 1 ? ', ' : '' }}
                                            @endforeach
                                        </strong>
                                        @if ($class->caHoc)
                                            | Giờ học:
                                            <strong>{{ \Carbon\Carbon::parse($class->caHoc->gioBatDau)->format('H:i') }} -
                                                {{ \Carbon\Carbon::parse($class->caHoc->gioKetThuc)->format('H:i') }}</strong>
                                        @endif
                                    </small>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- GIẢNG VIÊN --}}
                    @if ($class->taiKhoan && $class->taiKhoan->hoSoNguoiDung)
                        <div class="class-detail-card">
                            <h4 class="mb-3 fw-bold">Giảng viên phụ trách</h4>
                            <div class="teacher-card">
                                <img src="{{ asset('storage/' . $class->taiKhoan->hoSoNguoiDung->anhDaiDien) }}"
                                    onerror="this.src='{{ asset('assets/images/user-default.png') }}'" alt="Teacher"
                                    class="teacher-avatar">
                                <div>
                                    <h5 class="mb-1 fw-bold">{{ $class->taiKhoan->hoSoNguoiDung->hoTen }}</h5>
                                    <p class="mb-0 text-muted small"><i class="fas fa-envelope me-1"></i>
                                        {{ $class->taiKhoan->email }}</p>
                                    {{-- chuyên môn --}}
                                    <p class="mb-0 text-muted small"><i class="fas fa-briefcase me-1"></i>
                                        {{ optional($class->taiKhoan->nhanSu)->chuyenMon ?? 'Chưa cập nhật' }}</p>
                                    {{-- bằng cấp --}}
                                    <p class="mb-0 text-muted small"><i class="fas fa-graduation-cap me-1"></i>
                                        {{ optional($class->taiKhoan->nhanSu)->bangCap ?? 'Chưa cập nhật' }}</p>
                                    {{-- Học vị --}}
                                    <p class="mb-0 text-muted small"><i class="fas fa-graduation-cap me-1"></i>
                                        {{ optional($class->taiKhoan->nhanSu)->hocVi ?? 'Chưa cập nhật' }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- RIGHT COLUMN (SIDEBAR) --}}
                <div class="col-lg-4">
                    <div class="sidebar-sticky" style="top: 100px">
                        {{-- HỌC PHÍ CARD --}}
                        <div class="sidebar-card p-4 text-center mb-4">
                            <p class="text-muted mb-1">Học phí khóa học</p>
                            @if ($class->hocPhi)
                                <h2 class="text-primary fw-bold mb-1">
                                    {{ number_format($class->hocPhi->tongHocPhi, 0, ',', '.') }}đ
                                </h2>
                                <p class="text-muted small mb-3">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ $class->hocPhi->soBuoi }} buổi &times;
                                    {{ number_format($class->hocPhi->donGia, 0, ',', '.') }}đ/buổi
                                </p>
                            @else
                                <h2 class="text-muted fw-bold mb-3">Liên hệ</h2>
                            @endif

                            @if ($class->trangThai == 1)
                                @auth
                                    @if (auth()->user()->role === \App\Models\Auth\TaiKhoan::ROLE_HOC_VIEN)
                                        <a href="{{ route('home.classes.confirm', ['slug' => $class->khoaHoc->slug, 'slugLopHoc' => $class->slug]) }}"
                                            class="btn btn-primary w-100 py-3 rounded-3 fw-bold mb-3 d-flex align-items-center justify-content-center text-decoration-none"
                                            style="background: linear-gradient(135deg, #10454F 0%, #27C4B5 100%); border: none;">
                                            <i class="fas fa-user-plus me-2"></i> ĐĂNG KÝ NGAY
                                        </a>
                                        <p class="small text-muted mb-0"><i class="fas fa-shield-alt me-1"></i> Cam kết hoàn
                                            tiền trong 7 ngày</p>
                                    @else
                                        <div class="alert alert-warning py-2 mb-0 small">
                                            <i class="fas fa-info-circle me-1"></i> Chỉ học viên mới có thể đăng ký lớp học.
                                        </div>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}"
                                        class="btn btn-primary w-100 py-3 rounded-3 fw-bold mb-3 d-flex align-items-center justify-content-center text-decoration-none"
                                        style="background: linear-gradient(135deg, #10454F 0%, #27C4B5 100%); border: none;">
                                        <i class="fas fa-sign-in-alt me-2"></i> ĐĂNG NHẬP ĐỂ ĐĂNG KÝ
                                    </a>
                                @endauth
                            @elseif ($class->trangThai == 0)
                                <button class="btn w-100 py-3 rounded-3 fw-bold disabled"
                                    style="background:#e3f2fd;color:#1565c0;border:none;">
                                    <i class="fas fa-hourglass-start me-2"></i> SẮP MỞ ĐĂNG KÝ
                                </button>
                                <p class="small text-muted mb-0 mt-2"><i class="fas fa-bell me-1"></i> Lớp học chưa mở
                                    đăng ký</p>
                            @elseif ($class->trangThai == 4)
                                <button class="btn w-100 py-3 rounded-3 fw-bold disabled"
                                    style="background:#e8f5e9;color:#2e7d32;border:none;">
                                    <i class="fas fa-chalkboard-teacher me-2"></i> ĐANG DIỄN RA
                                </button>
                                <p class="small text-muted mb-0 mt-2"><i class="fas fa-info-circle me-1"></i> Lớp học đang
                                    trong quá trình học</p>
                            @elseif ($class->trangThai == 3)
                                <button class="btn btn-danger w-100 py-3 rounded-3 fw-bold disabled opacity-75">
                                    <i class="fas fa-ban me-2"></i> LỚP ĐÃ HỦY
                                </button>
                            @else
                                <button class="btn btn-secondary w-100 py-3 rounded-3 fw-bold disabled">
                                    <i class="fas fa-lock me-2"></i> ĐÃ ĐÓNG ĐĂNG KÝ
                                </button>
                            @endif
                        </div>

                        {{-- CONTACT CARD (Reused) --}}
                        <div class="sidebar-card contact-card">
                            <div class="contact-header">
                                <i class="fas fa-headset contact-icon" style="font-size: 36px"></i>
                                <h3 class="contact-title" style="font-size: 1.2rem">Liên hệ tư vấn</h3>
                            </div>
                            <div class="contact-body">
                                <p class="contact-subtitle" style="font-size: 13px">Cần hỗ trợ về lớp học này?</p>
                                <a href="https://zalo.me/0816548150" target="_blank" class="btn-contact-zalo">
                                    <span>Chat qua Zalo</span>
                                </a>
                                <div class="contact-phone">
                                    <i class="fas fa-phone-alt me-2"></i>
                                    <span>0816548150</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
