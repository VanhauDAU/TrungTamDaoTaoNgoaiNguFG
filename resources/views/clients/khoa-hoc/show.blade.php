@extends('layouts.client')

@section('title', $course->tenKhoaHoc . ' - Five Genius')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/courseDetail.css') }}">
@endsection

@section('content')
    <section class="course-detail-page pt-5 pb-5">
        <div class="custom-container">
            {{-- BREADCRUMB + NÚT QUAY LẠI --}}
            <div class="breadcrumb-wrapper">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.courses.index') }}">Khóa học</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.courses.index', ['loai' => $course->loaiKhoaHoc->slug]) }}">
                                {{ $course->loaiKhoaHoc->tenLoai }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ $course->tenKhoaHoc }}
                        </li>
                    </ol>
                </nav>
            </div>

            {{-- NEW LAYOUT: 8-2 SPLIT --}}
            <div class="row g-4">
                {{-- BÊN TRÁI: THÔNG TIN KHÓA HỌC + LỚP HỌC (8 CỘT) --}}
                <div class="col-lg-9">

                    {{--  LỚP HỌC ĐANG MỞ --}}
                    <div class="available-classes-section">
                        <div class="section-header-left mb-4">
                            <h2 class="section-title-custom-left">
                                <i class="fas fa-graduation-cap text-primary me-2"></i>
                                Lớp học đang mở
                            </h2>
                            <p class="section-subtitle-left">Chọn lớp học phù hợp với lịch trình của bạn</p>
                        </div>

                        @if ($course->lopHoc && $course->lopHoc->count() > 0)
                            @php
                                // Nhóm lớp học theo cơ sở
                                $classesByFacility = $course->lopHoc->groupBy('coSoId');
                            @endphp

                            @foreach ($classesByFacility as $coSoId => $classes)
                                @php
                                    $facility = $classes->first()->coSo;
                                @endphp

                                {{-- Facility Group --}}
                                <div class="facility-group mb-4">
                                    <div class="facility-header">
                                        <h3 class="facility-name">
                                            <i class="fas fa-map-marker-alt me-2"></i>
                                            {{ $facility->tenCoSo ?? 'Cơ sở' }}
                                        </h3>
                                        <p class="facility-address">
                                            {{ $facility->diaChi ?? '' }}
                                            @if ($facility->tinhThanh)
                                                - {{ $facility->tinhThanh->tenTinhThanh }}
                                            @endif
                                            <span class="facility-email">
                                                <i class="fas fa-envelope me-2"></i>
                                                {{ $facility->email ?? '' }}
                                            </span>
                                        </p>
                                    </div>

                                    {{-- Classes in this facility --}}
                                    <div class="row g-3 mt-3">
                                        @foreach ($classes as $lopHoc)
                                            <div class="col-xl-4 col-lg-4 col-md-6">
                                                <div class="class-card-compact">
                                                    <div class="class-card-header">
                                                        <div class="class-name-wrapper">
                                                            <h4 class="class-name-compact">{{ $lopHoc->tenLopHoc }}</h4>
                                                            @if ($lopHoc->trangThai == 0)
                                                                <span class="badge-status badge-coming">Sắp mở</span>
                                                            @elseif ($lopHoc->trangThai == 1)
                                                                <span class="badge-status badge-open">Đang mở</span>
                                                            @elseif ($lopHoc->trangThai == 4)
                                                                <span class="badge-status"
                                                                    style="background:#e8f5e9;color:#2e7d32">Đang học</span>
                                                            @elseif ($lopHoc->trangThai == 3)
                                                                <span class="badge-status badge-full">Đã hủy</span>
                                                            @else
                                                                <span class="badge-status badge-full">Đã đóng</span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="class-card-body">
                                                        <div class="class-info-row">

                                                            <div class="info-item-inline">
                                                                <i class="fas fa-calendar-alt"></i>
                                                                <span>{{ $lopHoc->ngayBatDau ? \Carbon\Carbon::parse($lopHoc->ngayBatDau)->format('d/m/Y') : 'Chưa xác định' }}</span>
                                                            </div>
                                                            <div class="info-item-inline">
                                                                <i class="fas fa-clock"></i>
                                                                <span>{{ $lopHoc->ngayKetThuc ? \Carbon\Carbon::parse($lopHoc->ngayKetThuc)->format('d/m/Y') : 'Chưa xác định' }}</span>
                                                            </div>
                                                            <div class="info-item-inline">
                                                                <i class="fas fa-users"></i>
                                                                <span>
                                                                    <span class="seats-current">0</span>/<span
                                                                        class="seats-max">{{ $lopHoc->soHocVienToiDa ?? 30 }}</span>
                                                                </span>
                                                            </div>
                                                            <div class="info-item-inline">
                                                                <i class="fas fa-user-tie text-primary"></i>
                                                                <span
                                                                    class="fw-medium">{{ $lopHoc->taiKhoan->hoSoNguoiDung->hoTen ?? 'Chưa cập nhật' }}</span>
                                                            </div>
                                                            <div class="info-item-inline">
                                                                <i class="fas fa-book-reader"></i>
                                                                <span>{{ $lopHoc->soBuoiDuKien ?? 'N/A' }} buổi</span>
                                                            </div>
                                                            {{-- thứ học (lichhoc) --}}
                                                            <div class="info-item-inline">
                                                                <i class="fas fa-calendar-alt"></i>
                                                                @php
                                                                    $lichHoc = json_decode($lopHoc->lichHoc, true);
                                                                    $thoiGianHoc = '';
                                                                    if ($lichHoc) {
                                                                        $thoiGianHoc = implode(', ', $lichHoc);
                                                                    }
                                                                @endphp
                                                                <span>{{ $thoiGianHoc }}</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="class-card-footer">
                                                        <a href="{{ route('home.classes.show', ['slug' => $lopHoc->khoaHoc->slug, 'slugLopHoc' => $lopHoc->slug]) }}"
                                                            class="btn-action btn-detail">
                                                            <i class="fas fa-info-circle me-1"></i>
                                                            Chi tiết
                                                        </a>
                                                        @if ($lopHoc->trangThai == 1)
                                                            <a href="{{ route('home.classes.confirm', ['slug' => $lopHoc->khoaHoc->slug, 'slugLopHoc' => $lopHoc->slug]) }}"
                                                                class="btn-action btn-register">
                                                                <i class="fas fa-user-plus me-1"></i>
                                                                Đăng ký
                                                            </a>
                                                        @else
                                                            <span class="btn-action btn-register disabled"
                                                                style="opacity:0.5;cursor:not-allowed;pointer-events:none;">
                                                                <i class="fas fa-lock me-1"></i>
                                                                Không khả dụng
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="empty-state">
                                <i class="fas fa-info-circle mb-3"></i>
                                <p class="mb-0">Hiện chưa có lớp học nào đang mở đăng ký</p>
                            </div>
                        @endif
                    </div>
                    {{-- SECTION 8: KHÓA HỌC LIÊN QUAN --}}
                    @if ($relatedCourses && $relatedCourses->count() > 0)
                        <div class="related-courses-section mt-5">
                            <div class="section-header mb-4">
                                <h2 class="section-title-custom">
                                    <i class="fas fa-layer-group text-primary me-2"></i>
                                    Khóa học liên quan
                                </h2>
                                <p class="section-subtitle">Các khóa học khác bạn có thể quan tâm</p>
                            </div>

                            <div class="row g-4">
                                @foreach ($relatedCourses as $relatedCourse)
                                    <div class="col-lg-3 col-md-6">
                                        <div class="related-course-card">
                                            {{-- Course image --}}
                                            <div class="related-course-image">
                                                @if ($relatedCourse->anhKhoaHoc)
                                                    <img src="{{ asset('storage/courses/' . $relatedCourse->anhKhoaHoc) }}"
                                                        alt="{{ $relatedCourse->tenKhoaHoc }}">
                                                @else
                                                    <img src="{{ asset('assets/images/course-placeholder.jpg') }}"
                                                        alt="{{ $relatedCourse->tenKhoaHoc }}">
                                                @endif
                                                <div class="related-course-overlay"></div>
                                                <div class="related-course-tag">
                                                    <span>{{ $relatedCourse->loaiKhoaHoc->tenLoai }}</span>
                                                </div>
                                            </div>

                                            {{-- Course content --}}
                                            <div class="related-course-content">
                                                <h5 class="related-course-title">{{ $relatedCourse->tenKhoaHoc }}</h5>

                                                @if ($relatedCourse->moTa)
                                                    <p class="related-course-desc">
                                                        {{ Str::limit($relatedCourse->moTa, 80) }}</p>
                                                @endif

                                                <div class="related-course-meta">
                                                    <div class="meta-item-small">
                                                        <i class="fas fa-book-open"></i>
                                                        <span>{{ $relatedCourse->lopHoc->count() }} lớp</span>
                                                    </div>
                                                    <div class="meta-item-small">
                                                        <i class="fas fa-users"></i>
                                                        <span>{{ $relatedCourse->lopHoc->sum('soHocVienToiDa') ?? 0 }}
                                                            HV</span>
                                                    </div>
                                                </div>

                                                <a href="{{ route('home.courses.show', $relatedCourse->slug) }}"
                                                    class="btn-view-course">
                                                    Xem chi tiết
                                                    <i class="fas fa-arrow-right ms-2"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- BÊN PHẢI: SIDEBAR STICKY (4 CỘT) --}}
                <div class="col-lg-3">
                    <div class="sidebar-sticky">
                        {{-- Hình ảnh khóa học --}}
                        <div class="sidebar-card">
                            <div class="course-thumbnail-sidebar">
                                @if ($course->anhKhoaHoc)
                                    <img src="{{ asset('storage/courses/' . $course->anhKhoaHoc) }}"
                                        alt="{{ $course->tenKhoaHoc }}" class="img-fluid rounded-3">
                                @else
                                    <img src="{{ asset('assets/images/course-placeholder.jpg') }}"
                                        alt="{{ $course->tenKhoaHoc }}" class="img-fluid rounded-3">
                                @endif
                            </div>
                        </div>
                        <div class="course-header-section mb-4">
                            {{-- Danh mục --}}
                            <div class="course-category mb-3">
                                <span class="badge bg-gradient-primary">
                                    <i class="fas fa-folder-open me-1"></i>
                                    {{ $course->loaiKhoaHoc->tenLoai }}
                                </span>
                            </div>

                            {{-- Tiêu đề khóa học --}}
                            <h1 class="course-title mb-3">{{ $course->tenKhoaHoc }}</h1>

                            {{-- Mô tả ngắn --}}
                            @if ($course->moTa)
                                <h2 class="course-title mb-3">Mô tả ngắn</h2>
                                <p class="course-description">{{ $course->moTa }}</p>
                            @endif
                            @if ($course->doiTuong)
                                <h2 class="course-title mb-3">Đối tượng</h2>
                                <p class="course-description">{{ $course->doiTuong }}</p>
                            @endif
                            @if ($course->yeuCauDauVao)
                                <h2 class="course-title mb-3">Yêu cầu đầu vào</h2>
                                <p class="course-description">{{ $course->yeuCauDauVao }}</p>
                            @endif
                            @if ($course->ketQuaDatDuoc)
                                <h2 class="course-title mb-3">Kết quả đạt được</h2>
                                <p class="course-description">{{ $course->ketQuaDatDuoc }}</p>
                            @endif


                        </div>
                        {{-- Liên hệ tư vấn --}}
                        <div class="sidebar-card contact-card">
                            <div class="contact-header">
                                <i class="fas fa-headset contact-icon"></i>
                                <h3 class="contact-title">Liên hệ tư vấn</h3>
                            </div>
                            <div class="contact-body">
                                <p class="contact-subtitle">Cần hỗ trợ về khóa học này?</p>
                                <a href="https://zalo.me/0816548150" target="_blank" class="btn-contact-zalo">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 0C5.373 0 0 4.975 0 11.111c0 3.498 1.814 6.614 4.644 8.593L3.752 24l4.867-2.499c1.096.293 2.24.444 3.381.444 6.627 0 12-4.974 12-11.11C24 4.974 18.627 0 12 0zm0 20.389c-1.048 0-2.069-.172-3.021-.491l-.22-.073-2.248 1.156.593-2.716-.091-.153c-1.677-2.073-2.563-4.458-2.563-6.891C4.45 6.47 7.825 3.333 12 3.333s7.55 3.137 7.55 6.777c0 3.64-3.375 6.777-7.55 6.777z" />
                                    </svg>
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
