@extends('layouts.client')

@section('title', 'Trung tâm đào tạo ngoại ngữ - Five Genius')

@section('content')

{{-- HERO FULL WIDTH --}}
<section class="hero-section">
    <div class="container-fluid text-center">
        <h1>Khai Phá Tài Năng Ngôn Ngữ</h1>
        <p class="lead mb-4">
            Học ngoại ngữ cùng đội ngũ chuyên gia hàng đầu tại Five Genius.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="#courses" class="btn btn-warning btn-lg fw-bold">Xem khóa học</a>
            <a href="#contact" class="btn btn-outline-light btn-lg">Tư vấn ngay</a>
        </div>
    </div>
</section>

{{-- STATS --}}
<div class="container my-5">
    <div class="row text-center">
        <div class="col-md-3 stat-box">
            <span class="stat-number">5000+</span>
            <span>Học viên</span>
        </div>
        <div class="col-md-3 stat-box">
            <span class="stat-number">50+</span>
            <span>Giảng viên</span>
        </div>
        <div class="col-md-3 stat-box">
            <span class="stat-number">10+</span>
            <span>Năm kinh nghiệm</span>
        </div>
        <div class="col-md-3 stat-box">
            <span class="stat-number">98%</span>
            <span>Hài lòng</span>
        </div>
    </div>
</div>

{{-- COURSES SECTION --}}
<section id="courses" class="courses-section py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <h2 class="section-title text-start mb-2">Khóa Học Nổi Bật</h2>
                <p class="text-muted">Lộ trình bài bản, cam kết đầu ra theo tiêu chuẩn quốc tế.</p>
            </div>
            <a href="#" class="btn btn-outline-primary-genius d-none d-md-block">Xem tất cả khóa học</a>
        </div>

        <div class="row g-4">
            @foreach($khoaHocs as $khoaHoc)
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="course-card">
                    {{-- Badge Loại khóa học --}}
                    <div class="course-badge">{{ $khoaHoc->loaiKhoaHoc->tenLoai?? 'Ngoại ngữ' }}</div>
                    
                    {{-- Hình ảnh & Nút yêu thích --}}
                    <div class="course-image-wrapper">
                        <img src="{{ asset('assets/images/' . ($khoaHoc->anhKhoaHoc ?? 'course-demo.jpg')) }}" alt="{{ $khoaHoc->tenKhoaHoc }}" class="course-img">
                        <div class="course-overlay">
                            <button class="btn btn-light btn-sm rounded-pill px-3 fw-bold">Xem chi tiết</button>
                        </div>
                        <button class="wishlist-btn"><i class="far fa-heart"></i></button>
                    </div>

                    {{-- Nội dung --}}
                    <div class="course-content p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="course-meta"><i class="far fa-clock me-1"></i> 24 Buổi</span>
                            <span class="course-rating"><i class="fas fa-star text-warning me-1"></i> 4.9</span>
                        </div>
                        
                        <h5 class="course-title">
                            <a href="#">{{ $khoaHoc->tenKhoaHoc ?? 'Tên khóa học mẫu tại Five Genius' }}</a>
                        </h5>
                        
                        <p class="course-description text-muted">
                            {{ Str::limit($khoaHoc->moTa ?? 'Mô tả ngắn gọn về khóa học giúp học viên nắm bắt nội dung cốt lõi...', 80) }}
                        </p>

                        <hr class="my-3 opacity-50">

                        <div class="course-footer d-flex justify-content-between align-items-center">
                            <div class="course-price">
                                <span class="price-original">1.500.000đ</span>
                                <span class="price-discount">990.000đ</span>
                            </div>
                            <a href="#" class="enroll-btn"><i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            {{-- Kết thúc vòng lặp --}}
        </div>
    </div>
</section>
{{-- TEACHERS SECTION --}}
<section id="teachers" class="teachers-section py-5 bg-light">
    <div class="container">
        {{-- Header --}}
        <div class="text-center mb-5">
            <h2 class="section-title">Đội Ngũ Giảng Viên</h2>
            <p class="text-muted w-75 mx-auto">
                Five Genius tự hào sở hữu đội ngũ giảng viên giàu kinh nghiệm, 
                được đào tạo bài bản và có chứng chỉ quốc tế, luôn đồng hành cùng học viên trên hành trình chinh phục ngoại ngữ.
            </p>
        </div>

        {{-- Teachers list --}}
        <div class="row g-4">
            @foreach($giaoViens as $gv)
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="teacher-card text-center h-100">
                    {{-- Avatar --}}
                    <div class="teacher-avatar">
                        <img 
                            src="{{ asset('assets/images/' . ($gv->anhDaiDien ?? 'teacher-demo.jpg')) }}" 
                            alt="{{ $gv->tenGiaoVien }}"
                        >
                    </div>

                    {{-- Info --}}
                    <div class="teacher-info p-4">
                        <h5 class="teacher-name mb-1">
                            {{ $gv->tenGiaoVien ?? 'Nguyễn Văn A' }}
                        </h5>

                        <span class="teacher-major d-block mb-2">
                            {{ $gv->chuyenMon ?? 'Giảng viên Tiếng Anh' }}
                        </span>

                        <p class="teacher-desc text-muted mb-3">
                            {{ Str::limit($gv->gioiThieu ?? 'Giảng viên có nhiều năm kinh nghiệm giảng dạy, từng đào tạo hàng nghìn học viên đạt chứng chỉ quốc tế.', 90) }}
                        </p>

                        {{-- Meta --}}
                        <div class="teacher-meta d-flex justify-content-center gap-3 mb-3">
                            <span><i class="fas fa-briefcase me-1"></i> {{ $gv->kinhNghiem ?? '8+' }} năm</span>
                            <span><i class="fas fa-star text-warning me-1"></i> 4.9</span>
                        </div>

                        {{-- Social --}}
                        <div class="teacher-social d-flex justify-content-center gap-3">
                            <a href="#" class="social-btn"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-btn"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#" class="social-btn"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
