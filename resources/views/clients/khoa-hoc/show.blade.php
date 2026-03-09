@extends('layouts.client')

@section('title', $course->tenKhoaHoc . ' - Five Genius')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/courseDetail.css') }}">
    <style>
        /* ── BỘ LỌC LỚP HỌC ───────────────────────────────────── */
        .filter-panel {
            background: #fff;
            border: 1.5px solid #e8edf3;
            border-radius: 16px;
            padding: 20px 24px;
            margin-bottom: 28px;
            box-shadow: 0 2px 12px rgba(30, 80, 200, 0.06);
        }

        .filter-panel-title {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #7a8aa0;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        /* Tabs cơ sở */
        .filter-tab {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 30px;
            border: 1.5px solid #d1dbe8;
            background: #f7f9fc;
            color: #4a5568;
            font-size: 0.855rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.18s ease;
            user-select: none;
            white-space: nowrap;
        }

        .filter-tab:hover {
            border-color: #3b82f6;
            color: #1d4ed8;
            background: #eff6ff;
        }

        .filter-tab.active {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-color: #1d4ed8;
            color: #fff;
            box-shadow: 0 3px 10px rgba(59, 130, 246, 0.35);
        }

        .filter-tab .tab-count {
            background: rgba(0,0,0,0.12);
            border-radius: 10px;
            padding: 1px 7px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .filter-tab.active .tab-count {
            background: rgba(255,255,255,0.25);
        }

        /* Divider giữa nhóm filter */
        .filter-divider {
            width: 1px;
            height: 28px;
            background: #d1dbe8;
            margin: 0 4px;
            flex-shrink: 0;
        }

        /* Toggle sắp khai giảng */
        .filter-toggle-wrap {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 7px 16px;
            border-radius: 30px;
            border: 1.5px solid #d1dbe8;
            background: #f7f9fc;
            cursor: pointer;
            transition: all 0.18s ease;
            user-select: none;
        }

        .filter-toggle-wrap:hover {
            border-color: #f59e0b;
            background: #fffbeb;
        }

        .filter-toggle-wrap.active {
            border-color: #f59e0b;
            background: #fef3c7;
            color: #92400e;
        }

        .filter-toggle-wrap input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #f59e0b;
            cursor: pointer;
        }

        .filter-toggle-label {
            font-size: 0.855rem;
            font-weight: 500;
            color: #4a5568;
            cursor: pointer;
        }

        .filter-toggle-wrap.active .filter-toggle-label {
            color: #92400e;
        }

        /* Banner lớp sắp khai giảng gần nhất */
        .upcoming-banner {
            display: flex;
            align-items: center;
            gap: 14px;
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border: 1.5px solid #fcd34d;
            border-radius: 14px;
            padding: 14px 20px;
            margin-bottom: 24px;
            animation: fadeInSlide 0.4s ease;
        }

        .upcoming-banner-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.1rem;
            flex-shrink: 0;
            box-shadow: 0 3px 8px rgba(245,158,11,0.4);
        }

        .upcoming-banner-body { flex: 1; }

        .upcoming-banner-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #92400e;
            margin-bottom: 2px;
        }

        .upcoming-banner-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #78350f;
            margin-bottom: 2px;
        }

        .upcoming-banner-date {
            font-size: 0.82rem;
            color: #b45309;
        }

        .upcoming-banner-action {
            flex-shrink: 0;
        }

        .upcoming-banner-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 30px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
            font-size: 0.82rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.18s ease;
            box-shadow: 0 3px 8px rgba(245,158,11,0.4);
            white-space: nowrap;
        }

        .upcoming-banner-btn:hover {
            background: linear-gradient(135deg, #d97706, #b45309);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 5px 12px rgba(245,158,11,0.5);
        }

        /* No-results state */
        .no-filter-result {
            display: none;
            text-align: center;
            padding: 40px 20px;
            color: #7a8aa0;
        }

        .no-filter-result i {
            font-size: 2.5rem;
            margin-bottom: 12px;
            opacity: 0.4;
        }

        .no-filter-result p {
            font-size: 0.95rem;
            margin: 0;
        }

        /* Card ẩn khi filter */
        .class-card-col.hidden-by-filter {
            display: none !important;
        }

        /* Facility group ẩn khi không có card nào */
        .facility-group.all-hidden {
            display: none !important;
        }

        /* Sort badge cho lớp sắp nhất */
        .upcoming-highlight-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
            border-radius: 8px;
            padding: 2px 9px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            margin-left: 6px;
            vertical-align: middle;
            animation: pulse-badge 1.5s ease-in-out infinite;
        }

        @keyframes pulse-badge {
            0%, 100% { box-shadow: 0 0 0 0 rgba(245,158,11,0.5); }
            50% { box-shadow: 0 0 0 5px rgba(245,158,11,0); }
        }

        @keyframes fadeInSlide {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Sort indicator */
        .sort-info {
            display: none;
            align-items: center;
            gap: 6px;
            font-size: 0.78rem;
            color: #92400e;
            background: rgba(245,158,11,0.1);
            border-radius: 8px;
            padding: 4px 12px;
            margin-top: 10px;
        }

        .sort-info.visible { display: inline-flex; }

        @media (max-width: 576px) {
            .filter-panel { padding: 16px; }
            .filter-tab { padding: 6px 12px; font-size: 0.8rem; }
            .upcoming-banner { flex-wrap: wrap; gap: 10px; }
            .upcoming-banner-action { width: 100%; }
            .upcoming-banner-btn { width: 100%; justify-content: center; }
        }
    </style>
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
                            <a href="{{ route('home.courses.index', ['loai' => $course->danhMuc->slug]) }}">
                                {{ $course->danhMuc->tenDanhMuc }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ $course->tenKhoaHoc }}
                        </li>
                    </ol>
                </nav>
            </div>

            {{-- NEW LAYOUT: 9-3 SPLIT --}}
            <div class="row g-4">
                {{-- BÊN TRÁI: THÔNG TIN KHÓA HỌC + LỚP HỌC --}}
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

                            {{-- ═══════════════════════════════════════════ --}}
                            {{-- BỘ LỌC LỚP HỌC                            --}}
                            {{-- ═══════════════════════════════════════════ --}}
                            <div class="filter-panel">
                                <div class="filter-panel-title">
                                    <i class="fas fa-sliders-h"></i>
                                    Bộ lọc lớp học
                                </div>
                                <div class="filter-group" id="filterGroup">

                                    {{-- Tab Tất cả --}}
                                    <button class="filter-tab active"
                                            data-filter="coso"
                                            data-coso-id="all"
                                            onclick="filterByCoso('all', this)">
                                        <i class="fas fa-globe-asia"></i>
                                        Tất cả
                                        <span class="tab-count">{{ $course->lopHoc->count() }}</span>
                                    </button>

                                    @if ($coSos->count() > 1)
                                        {{-- Tabs từng cơ sở --}}
                                        @foreach ($coSos as $coSo)
                                            @php
                                                $countInThisCoso = $course->lopHoc->where('coSoId', $coSo->coSoId)->count();
                                            @endphp
                                            <button class="filter-tab"
                                                    data-filter="coso"
                                                    data-coso-id="{{ $coSo->coSoId }}"
                                                    onclick="filterByCoso('{{ $coSo->coSoId }}', this)">
                                                <i class="fas fa-map-marker-alt"></i>
                                                {{ $coSo->tenCoSo }}
                                                <span class="tab-count">{{ $countInThisCoso }}</span>
                                            </button>
                                        @endforeach

                                        <div class="filter-divider"></div>
                                    @endif

                                    {{-- Toggle: Chỉ lớp sắp khai giảng --}}
                                    @php
                                        $countUpcoming = $course->lopHoc->filter(fn($l) => in_array((int)$l->trangThai, [
                                            \App\Models\Education\LopHoc::TRANG_THAI_SAP_MO,
                                            \App\Models\Education\LopHoc::TRANG_THAI_DANG_TUYEN_SINH,
                                        ]))->count();
                                    @endphp
                                    @if ($countUpcoming > 0)
                                        <label class="filter-toggle-wrap" id="upcomingToggleWrap">
                                            <input type="checkbox" id="filterUpcoming"
                                                   onchange="toggleUpcomingFilter(this)">
                                            <i class="fas fa-calendar-check" style="color:#f59e0b; font-size:0.9rem;"></i>
                                            <span class="filter-toggle-label">
                                                Chỉ lớp sắp khai giảng
                                                <strong>({{ $countUpcoming }})</strong>
                                            </span>
                                        </label>
                                    @endif
                                </div>

                                {{-- Sort indicator --}}
                                <div class="sort-info" id="sortInfo">
                                    <i class="fas fa-sort-amount-up"></i>
                                    Đang sắp xếp theo ngày khai giảng gần nhất
                                </div>
                            </div>
                            {{-- /BỘ LỌC --}}

                            {{-- BANNER LỚP SẮP KHAI GIẢNG GẦN NHẤT --}}
                            @if ($upcomingClass)
                                <div class="upcoming-banner" id="upcomingBanner">
                                    <div class="upcoming-banner-icon">
                                        <i class="fas fa-bell"></i>
                                    </div>
                                    <div class="upcoming-banner-body">
                                        <div class="upcoming-banner-label">
                                            <i class="fas fa-star me-1"></i>Khai giảng sớm nhất
                                        </div>
                                        <div class="upcoming-banner-title">
                                            {{ $upcomingClass->tenLopHoc }}
                                            @if ($upcomingClass->isSapMo())
                                                <span class="upcoming-highlight-badge">
                                                    <i class="fas fa-fire"></i> Sắp mở
                                                </span>
                                            @else
                                                <span class="upcoming-highlight-badge">
                                                    <i class="fas fa-door-open"></i> Đang tuyển sinh
                                                </span>
                                            @endif
                                        </div>
                                        <div class="upcoming-banner-date">
                                            <i class="fas fa-calendar-day me-1"></i>
                                            Khai giảng:
                                            <strong>{{ \Carbon\Carbon::parse($upcomingClass->ngayBatDau)->format('d/m/Y') }}</strong>
                                            @if ($upcomingClass->coSo)
                                                &nbsp;·&nbsp;<i class="fas fa-map-marker-alt me-1"></i>{{ $upcomingClass->coSo->tenCoSo }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="upcoming-banner-action">
                                        <a href="{{ route('home.classes.show', ['slug' => $upcomingClass->khoaHoc->slug ?? $course->slug, 'slugLopHoc' => $upcomingClass->slug]) }}"
                                           class="upcoming-banner-btn">
                                            <i class="fas fa-arrow-right"></i>
                                            Xem lớp
                                        </a>
                                    </div>
                                </div>
                            @endif

                            {{-- No results placeholder --}}
                            <div class="no-filter-result" id="noFilterResult">
                                <i class="fas fa-search"></i>
                                <p>Không có lớp học nào phù hợp với bộ lọc hiện tại.<br>
                                    <span style="font-size:0.85rem;">Hãy thử chọn cơ sở khác hoặc bỏ bộ lọc.</span>
                                </p>
                            </div>

                            @php
                                // Nhóm lớp học theo cơ sở
                                $classesByFacility = $course->lopHoc->groupBy('coSoId');
                            @endphp

                            @foreach ($classesByFacility as $coSoId => $classes)
                                @php
                                    $facility = $classes->first()->coSo;
                                @endphp

                                {{-- Facility Group --}}
                                <div class="facility-group mb-4"
                                     data-facility-coso="{{ $coSoId }}">
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
                                    <div class="row g-3 mt-3" data-classes-row="{{ $coSoId }}">
                                        @foreach ($classes as $lopHoc)
                                            @php
                                                // Xác định lớp nào là upcoming gần nhất (để highlight)
                                                $isUpcoming = $upcomingClass && $lopHoc->lopHocId === $upcomingClass->lopHocId;
                                                $soHocVienDaDangKy = $lopHoc->dangKyLopHocs
                                                    ->filter(fn($dk) => in_array($dk->trangThai, [1, 2, 3]))->count();
                                            @endphp
                                            <div class="col-xl-6 col-lg-6 col-md-6 class-card-col"
                                                 data-coso-id="{{ $lopHoc->coSoId }}"
                                                 data-trang-thai="{{ $lopHoc->trangThai }}"
                                                 data-ngay-bat-dau="{{ $lopHoc->ngayBatDau }}"
                                                 data-lop-id="{{ $lopHoc->lopHocId }}">
                                                <div class="class-card-compact {{ $isUpcoming ? 'upcoming-nearest' : '' }}">
                                                    <div class="class-card-header">
                                                        <div class="class-name-wrapper">
                                                            <h4 class="class-name-compact">
                                                                {{ $lopHoc->tenLopHoc }}
                                                                @if ($isUpcoming)
                                                                    <span class="upcoming-highlight-badge" title="Lớp khai giảng sớm nhất">
                                                                        <i class="fas fa-fire"></i> Sớm nhất
                                                                    </span>
                                                                @endif
                                                            </h4>
                                                            @if ($lopHoc->isSapMo())
                                                                <span class="badge-status badge-coming">Sắp mở</span>
                                                            @elseif ($lopHoc->isOpenForRegistration())
                                                                <span class="badge-status badge-open">Đang mở</span>
                                                            @elseif ($lopHoc->isClosedForRegistration())
                                                                <span class="badge-status badge-coming">Chốt danh
                                                                    sách</span>
                                                            @elseif ($lopHoc->isInProgress())
                                                                <span class="badge-status"
                                                                    style="background:#e8f5e9;color:#2e7d32">Đang học</span>
                                                            @elseif ($lopHoc->isCompleted())
                                                                <span class="badge-status"
                                                                    style="background:#e2e8f0;color:#334155">Đã kết
                                                                    thúc</span>
                                                            @elseif ($lopHoc->isCancelled())
                                                                <span class="badge-status badge-full">Đã hủy</span>
                                                            @else
                                                                <span class="badge-status badge-full">{{ $lopHoc->trangThaiLabel }}</span>
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
                                                                    <span class="seats-current">{{ $soHocVienDaDangKy }}</span>/<span
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
                                                        @if ($lopHoc->isOpenForRegistration())
                                                            <a href="{{ route('home.classes.confirm', ['slug' => $lopHoc->khoaHoc->slug, 'slugLopHoc' => $lopHoc->slug]) }}"
                                                                class="btn-action btn-register">
                                                                <i class="fas fa-user-plus me-1"></i>
                                                                Đăng ký
                                                            </a>
                                                        @else
                                                            <span class="btn-action btn-register disabled"
                                                                style="opacity:0.5;cursor:not-allowed;pointer-events:none;">
                                                                <i class="fas fa-lock me-1"></i>
                                                                {{ $lopHoc->trangThaiLabel }}
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
                                                    <img src="{{ asset('storage/' . $relatedCourse->anhKhoaHoc) }}"
                                                        alt="{{ $relatedCourse->tenKhoaHoc }}">
                                                @else
                                                    <img src="{{ asset('assets/images/course-placeholder.jpg') }}"
                                                        alt="{{ $relatedCourse->tenKhoaHoc }}">
                                                @endif
                                                <div class="related-course-overlay"></div>
                                                <div class="related-course-tag">
                                                    <span>{{ $relatedCourse->danhMuc->tenDanhMuc }}</span>
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

                {{-- BÊN PHẢI: SIDEBAR STICKY --}}
                <div class="col-lg-3">
                    <div class="sidebar-sticky">
                        {{-- Hình ảnh khóa học --}}
                        <div class="sidebar-card">
                            <div class="course-thumbnail-sidebar">
                                @if ($course->anhKhoaHoc)
                                    <img src="{{ asset('storage/' . $course->anhKhoaHoc) }}"
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
                                    {{ $course->danhMuc->tenDanhMuc }}
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

@section('script')
<script>
(function () {
    'use strict';

    // ─── Trạng thái lọc ─────────────────────────────────────────
    let activeCoSoId   = 'all';   // 'all' hoặc coSoId dạng string
    let onlyUpcoming   = false;   // toggle chỉ lớp sắp khai giảng

    // Trạng thái là sắp mở hoặc đang tuyển sinh
    const UPCOMING_STATES = [0, 1];

    /**
     * Áp dụng bộ lọc – ẩn/hiện các .class-card-col và .facility-group
     */
    function applyFilter() {
        const cards = document.querySelectorAll('.class-card-col');
        let totalVisible = 0;

        cards.forEach(card => {
            const cardCoSo   = card.dataset.cosoId;
            const trangThai  = parseInt(card.dataset.trangThai, 10);

            let show = true;

            // Lọc theo cơ sở
            if (activeCoSoId !== 'all' && cardCoSo !== activeCoSoId) {
                show = false;
            }

            // Lọc theo sắp khai giảng
            if (onlyUpcoming && !UPCOMING_STATES.includes(trangThai)) {
                show = false;
            }

            card.classList.toggle('hidden-by-filter', !show);
            if (show) totalVisible++;
        });

        // Ẩn/hiện facility-group nếu không có card nào visible
        const facilityGroups = document.querySelectorAll('.facility-group');
        facilityGroups.forEach(group => {
            const cosoId = group.dataset.facilityCoso;
            const visibleInGroup = group.querySelectorAll('.class-card-col:not(.hidden-by-filter)').length;
            group.classList.toggle('all-hidden', visibleInGroup === 0);
        });

        // Hiển thị no-results nếu cần
        const noResult = document.getElementById('noFilterResult');
        if (noResult) noResult.style.display = totalVisible === 0 ? 'block' : 'none';

        // Sort indicator
        const sortInfo = document.getElementById('sortInfo');
        if (sortInfo) sortInfo.classList.toggle('visible', onlyUpcoming);

        // Nếu đang lọc upcoming, sắp xếp lại card theo ngày khai giảng
        if (onlyUpcoming) sortVisibleCardsByDate();
    }

    /**
     * Sắp xếp các card visible theo ngayBatDau tăng dần
     */
    function sortVisibleCardsByDate() {
        const facilityGroups = document.querySelectorAll('.facility-group:not(.all-hidden)');
        facilityGroups.forEach(group => {
            const row = group.querySelector('[data-classes-row]');
            if (!row) return;

            const cols = Array.from(row.querySelectorAll('.class-card-col:not(.hidden-by-filter)'));
            if (cols.length === 0) return;

            // Tạm xóa khỏi DOM rồi chèn lại theo thứ tự
            cols.sort((a, b) => {
                const da = a.dataset.ngayBatDau || '9999-99-99';
                const db = b.dataset.ngayBatDau || '9999-99-99';
                return da.localeCompare(db);
            });

            cols.forEach(col => row.appendChild(col));
        });
    }

    // ─── Public: lọc theo cơ sở ─────────────────────────────────
    window.filterByCoso = function (cosoId, btn) {
        activeCoSoId = String(cosoId);

        // Cập nhật active tab
        document.querySelectorAll('.filter-tab[data-filter="coso"]').forEach(t => {
            t.classList.remove('active');
        });
        if (btn) btn.classList.add('active');

        applyFilter();
    };

    // ─── Public: toggle sắp khai giảng ──────────────────────────
    window.toggleUpcomingFilter = function (checkbox) {
        onlyUpcoming = checkbox.checked;

        const wrap = document.getElementById('upcomingToggleWrap');
        if (wrap) wrap.classList.toggle('active', onlyUpcoming);

        applyFilter();
    };

    // ─── Khởi tạo ────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        applyFilter(); // đảm bảo trạng thái ban đầu đúng
    });
})();
</script>
@endsection
