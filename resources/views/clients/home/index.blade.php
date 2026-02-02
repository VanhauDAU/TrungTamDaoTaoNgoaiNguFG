@extends('layouts.client')

@section('title', 'Trung tâm đào tạo ngoại ngữ - Five Genius')

@section('content')
    <section class="home-banner">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 order-2 order-lg-1 py-5">
                    <div class="banner_content">
                        <div class="title_animate mb-4">
                            <svg width="431" height="119" viewBox="0 0 431 119" fill="none"
                                xmlns="http://www.w3.org/2000/svg" class="svg-bg">
                                <path
                                    d="M26.4044 50.1739C143.289 11.2828 368.028 18.9611 426.999 30.7288C333.952 35.1662 60.1018 98.4423 60.1018 98.4423C60.1018 98.4423 220.66 52.5281 320.761 91.3785"
                                    stroke="#B8D3D9" stroke-width="40.8987" stroke-linecap="square" />
                            </svg>
                            <h1 class="ff-title cl-green fw-bold position-relative" style="">
                                Trung tâm Anh Ngữ <br> FIVE GENIUS
                            </h1>
                            <img src="https://theforumcenter.com/wp-content/themes/the-forum/assets/images/title-icon.png"
                                class="title-heart" alt="">
                        </div>

                        <div class="desc mb-4">
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check-circle-fill me-2"></i>Mô hình học tiên phong University Lecture
                                </li>
                                <li><i class="bi bi-check-circle-fill me-2"></i>Nâng band cấp tốc chỉ sau 90 giờ học</li>
                                <li><i class="bi bi-check-circle-fill me-2"></i>Chuyên đào tạo IELTS/SAT/Tiếng Anh Trẻ Em
                                </li>
                                <li><i class="bi bi-check-circle-fill me-2"></i>Cam kết chất lượng đầu ra</li>
                            </ul>
                        </div>

                        <a href="#" class="btn btn-red mb-5">
                            Đăng ký ngay <img
                                src="https://theforumcenter.com/wp-content/themes/the-forum/assets/images/right.png"
                                alt="">
                        </a>

                        <div class="partners">
                            <h6 class="ff-title fs-18 mb-3 text-muted">Hợp tác với các tổ chức giáo dục hàng đầu.</h6>
                            <div class="d-flex gap-4 align-items-center grayscale opacity-50">
                                <img src="https://theforumcenter.com/wp-content/uploads/2025/05/partner1.png" height="35"
                                    alt="">
                                <img src="https://theforumcenter.com/wp-content/uploads/2025/05/partner2.png" height="35"
                                    alt="">
                                <img src="https://theforumcenter.com/wp-content/uploads/2025/05/partner3.png" height="35"
                                    alt="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 order-1 order-lg-2">
                    <div class="slider-container-wrapper">
                        <div class="main-image-slider">
                            @foreach ($topGiaoVien as $giaoVien)
                                <div class="item">
                                    <div class="blob-frame">
                                        <img src="{{ $giaoVien->hoSoNguoiDung->anhDaiDien ? asset('storage/teachers/' . $giaoVien->hoSoNguoiDung->anhDaiDien) : asset('assets/images/default-teacher.jpeg') }}"
                                            alt="{{ $giaoVien->hoSoNguoiDung->hoTen }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="ielts-badge">
                            <div class="badge-slider">
                                @foreach ($topGiaoVien as $giaoVien)
                                    <div class="item"><strong>9.0</strong><span>IELTS</span></div>
                                @endforeach
                            </div>
                        </div>

                        <div class="slider-nav">
                            <button class="nav-btn prev"><i class="bi bi-chevron-left"></i></button>
                            <button class="nav-btn next"><i class="bi bi-chevron-right"></i></button>
                        </div>

                        <div class="teacher-info-slider mt-4 text-center">
                            @foreach ($topGiaoVien as $giaoVien)
                                <div class="item">
                                    <h4 class="cl-green fw-bold">{{ $giaoVien->hoSoNguoiDung->hoTen }}</h4>
                                    <p class="text-muted">Tốt nghiệp {{ $giaoVien->nhanSu->hocVi }} - với bằng cấp
                                        {{ $giaoVien->nhanSu->bangCap }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {{-- TRAINNING STATS SECTION --}}
    <section id="trainning-stats">
        <div class="training py-80">
            <div class="container">
                <div class="text-center">
                    <div class="title_animate px-5 mb-lg-5 mb-4">
                        <x-svg.title-accent class="active" />
                        <h3 class="fs-48 ff-title cl-green mb-0 textSkewUp" style="perspective: 400px; opacity: 1;">
                            <div class="word" style="display: inline-block;">
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    C</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    h</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    ư</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    ơ</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    n</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    g</div>
                            </div>
                            <div class="word" style="display: inline-block;">
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    t</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    r</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    ì</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    n</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    h</div>
                            </div>
                            <div class="word" style="display: inline-block;">
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    đ</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    à</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    o</div>
                            </div>
                            <div class="word" style="display: inline-block;">
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    t</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    ạ</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    o</div>
                            </div><br>
                            <div class="word" style="display: inline-block;">
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    c</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    h</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    ấ</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    t</div>
                            </div>
                            <div class="word" style="display: inline-block;">
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    l</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    ư</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    ợ</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    n</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    g</div>
                            </div>
                            <div class="word" style="display: inline-block;">
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    c</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    a</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    o</div>
                            </div>
                        </h3>
                        <div class="title_icon no-1">
                            <img src="{{ asset('assets/images/star-title.png') }}" class="img-fluid" alt="">
                        </div>
                        <div class="title_icon no-2">
                            <img src="{{ asset('assets/images/star-title.png') }}" class="img-fluid" alt="">
                        </div>
                        <div class="title_icon no-3">
                            <img src="{{ asset('assets/images/star-title.png') }}" class="img-fluid" alt="">
                        </div>
                    </div>
                </div>
                <div class="counter">
                    <div class="row g-4">
                        <div class="col-lg-4 col-6">
                            <div class="training_item item item_1 fadeUp" data-aos="fade-up" data-aos-delay="100"
                                style="translate: none; rotate: none; scale: none; transform: translate(0px, 0px); opacity: 1;">
                                <div class="fs-96 cl-red ff-title mb-3">
                                    <span class="number" data-count="8">8</span><span class="fs-48">+</span>
                                </div>
                                <div class="ff-title fs-24">
                                    Năm<br>hoạt động
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-6">
                            <div class="training_item item item_2 fadeUp"
                                style="translate: none; rotate: none; scale: none; transform: translate(0px, 0px); opacity: 1;">
                                <div class="fs-96 cl-red ff-title mb-3">
                                    <span class="number" data-count="10">10</span><span class="fs-48">+</span>
                                </div>
                                <div class="ff-title fs-24">
                                    Cơ sở<br>trên toàn quốc
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-6">
                            <div class="training_item item item_3 fadeUp"
                                style="translate: none; rotate: none; scale: none; transform: translate(0px, 0px); opacity: 1;">
                                <div class="fs-96 cl-red ff-title mb-3">
                                    <span class="number" data-count="200">200</span><span class="fs-48">+</span>
                                </div>
                                <div class="ff-title fs-24">
                                    Học viên xuất sắc đạt <br>IELTS 8.0+
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-6">
                            <div class="training_item item item_4 fadeUp"
                                style="translate: none; rotate: none; scale: none; transform: translate(0px, 0px); opacity: 1;">
                                <div class="fs-96 cl-red ff-title mb-3">
                                    <span class="number" data-count="100">0</span><span class="fs-48">%</span>
                                </div>
                                <div class="ff-title fs-24">
                                    Giáo viên chuyên môn chất lượng cao
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-6">
                            <div class="training_item item item_5 fadeUp"
                                style="translate: none; rotate: none; scale: none; transform: translate(0px, 0px); opacity: 1;">
                                <div class="fs-96 cl-red ff-title mb-3">
                                    <span class="number" data-count="999">0</span><span class="fs-48">+</span>
                                </div>
                                <div class="ff-title fs-24">
                                    Học viên<br>cán đích
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-6">
                            <div class="training_item item item_6 fadeUp"
                                style="translate: none; rotate: none; scale: none; transform: translate(0px, 0px); opacity: 1;">
                                <div class="fs-96 cl-red ff-title mb-3">
                                    <span class="number" data-count="10">10</span><span class="fs-48">+ Top</span>
                                </div>
                                <div class="ff-title fs-24">
                                    Đối tác bạch kim của IDP <br>và British Council Vietnam
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {{-- COURSES SECTION --}}
    <section id="courses" class="courses-section py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-5">
                <div>
                    <h2 class="section-title text-start mb-2">Khóa Học Nổi Bật</h2>
                    <p class="text-muted mt-4">Lộ trình bài bản, cam kết đầu ra theo tiêu chuẩn quốc tế.</p>
                </div>
                <a href="#" class="btn btn-outline-primary-genius d-none d-md-block">Xem tất cả khóa học</a>
            </div>

            <div class="row g-4">
                @foreach ($khoaHocs as $khoaHoc)
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="course-card">
                            {{-- Badge Loại khóa học --}}
                            <div class="course-badge">{{ $khoaHoc->loaiKhoaHoc->tenLoai ?? 'Ngoại ngữ' }}</div>

                            {{-- Hình ảnh & Nút yêu thích --}}
                            <div class="course-image-wrapper">
                                <img src="{{ asset('assets/images/' . ($khoaHoc->anhKhoaHoc ?? 'course-demo.jpg')) }}"
                                    alt="{{ $khoaHoc->tenKhoaHoc }}" class="course-img">
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
            </div>
        </div>
    </section>
    {{-- NEWS SECTION --}}
    <div class="news py-80">
        <div class="container">
            <div class="row justify-content-between align-items-end my-5">
                <div class="col-auto">
                    <div class="title_animate ps-lg-5 pe-5">
                        <svg width="348" height="75" viewBox="0 0 348 75" fill="none"
                            xmlns="http://www.w3.org/2000/svg" class="active" style="position: absolute; left: 150px;">
                            <g opacity="0.5">
                                <path d="M27.6708 48.0817C113.841 11.4972 279.525 18.7201 323 29.7898" stroke="#B8D3D9"
                                    stroke-width="40.8987" stroke-linecap="square" class="title-style-4"></path>
                            </g>
                        </svg>
                        <h3 class="fs-48 ff-title cl-green mb-0 textSkewUp" style="perspective: 400px; opacity: 1;">
                            <div class="word" style="display: inline-block;">
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    T</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    i</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    n</div>
                            </div>
                            <div class="word" style="display: inline-block;">
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    t</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    ứ</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    c</div>
                            </div>
                            <div class="word" style="display: inline-block;">
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    &amp;</div>
                            </div>
                            <div class="word" style="display: inline-block;">
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    B</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    l</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    o</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    g</div>
                                <div class="char"
                                    style="display: inline-block; translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                                    s</div>
                            </div>
                        </h3>
                        <div class="title_icon">
                            <img src="https://theforumcenter.com/wp-content/themes/the-forum/assets/images/news-title.png"
                                class="img-fluid" alt="">
                        </div>
                    </div>
                </div>
                <div class="col-auto">
                    <a href="{{ route('home.blog.index') }}" class="btn btn-red">
                        Xem thêm
                    </a>
                </div>
            </div>
            <div class="news_wrapper mt-60">
                <div class="row">
                    @foreach ($baiViets as $baiViet)
                        <div class="col-lg-4">
                            <div class="post_item fadeUp"
                                style="translate: none; rotate: none; scale: none; transform: translate(0px, 0px); opacity: 1;">
                                <figure>
                                    <a href="{{ route('home.blog.show', $baiViet->slug) }}">
                                        <img width="600" height="450"
                                            src="https://theforumcenter.com/wp-content/uploads/2023/02/trung-tam-luyen-thi-ielts-binh-thanh.jpg"
                                            class="img-fluid wp-post-image" alt="" decoding="async"> </a>
                                </figure>
                                <div class="meta_post">
                                    <div class="row">
                                        <div class="col">
                                            <ul class="post_category fs-12">
                                                <li>
                                                    <a
                                                        href="https://theforumcenter.com/category/tips-luyen-thi-du-hoc/">{{ $baiViet->danhMucs->first()->tenDanhMuc ?? '' }}</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-auto">
                                            <div class="post_date fs-12">
                                                {{ \Carbon\Carbon::parse($baiViet->created_at)->format('d/m/Y') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="title_wrapper mb-3">
                                        <svg width="305" height="52" viewBox="0 0 305 52" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <g opacity="0.5">
                                                <path
                                                    d="M10.595 21.7566C95.912 4.64015 259.955 8.01944 303 13.1985C235.083 15.1515 35.1916 43 35.1916 43C35.1916 43 152.387 22.7926 225.454 39.8912"
                                                    stroke="#27C4B5" stroke-width="18" stroke-linecap="square"
                                                    class="title_hover"></path>
                                            </g>
                                        </svg>
                                        <h4 class="fs-24 ff-title post_title mb-0"><a
                                                href="{{ route('home.blog.show', $baiViet->slug) }}">{{ $baiViet->tieuDe }}</a>
                                        </h4>
                                    </div>
                                    <div class="post_excerpt fw-light">
                                        <p>{{ $baiViet->tomTat }}</p>
                                    </div>
                                    {{-- Các tag thuộc bài viết --}}
                                    <div class="tags">
                                        @foreach ($baiViet->tags as $tag)
                                            <a href="#" class="tag">
                                                #{{ $tag->tenTag }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <x-client.register-advice />
@endsection
