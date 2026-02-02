@extends('layouts.client')

@section('title', 'Liên hệ - Trung tâm Anh ngữ Five Genius')
@section('stylesheet')
    <style>
        :root {
            /* Brand Colors */
            --primary-navy: #10454F;
            --accent-red: #E31E24;
            --accent-teal: #27C4B5;
            /* Backgrounds */
            --bg-pink: #FFF0F1;
            --bg-cream: #F9F5EE;
            --bg-cyan: #E8F3F5;
            /* Common Properties */
            --radius-xl: 30px;
            --radius-lg: 24px;
            --radius-md: 20px;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        /* --- TỔNG QUAN & RESET --- */
        .contact_page {
            background: #fff;
            overflow: hidden;
        }

        .contact_btn,
        .nav-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: var(--transition);
            border: none;
            cursor: pointer;
        }

        /* --- TIÊU ĐỀ ANIMATE --- */
        .title_animate {
            position: relative;
            display: inline-block;
        }

        .title_animate svg {
            position: absolute;
            width: 100%;
            height: auto;
            top: 55%;
            left: 0;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .title_animate .title-style-1 {
            stroke-dasharray: 2000;
            stroke-dashoffset: 2000;
            transition: 2s ease-in-out;
        }

        .title_animate svg.active .title-style-1 {
            stroke-dashoffset: 0;
        }

        /* --- CONTACT CARDS --- */
        .contact_item {
            padding: 40px;
            border-radius: var(--radius-lg);
            height: 100%;
            transition: var(--transition);
        }

        .contact_item:hover {
            transform: translateY(-10px);
        }

        .item_1 {
            background: var(--bg-pink);
        }

        .item_2 {
            background: var(--bg-cream);
        }

        .item_3 {
            background: var(--bg-cyan);
        }

        .contact_btn {
            gap: 10px;
            color: var(--primary-navy);
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            background: none;
        }

        .contact_btn img {
            height: 12px;
            transition: var(--transition);
        }

        .contact_btn:hover img {
            transform: translateX(5px);
        }

        /* --- MAP SLIDER CONTAINER --- */
        .map_section_container {
            position: relative;
            border-radius: var(--radius-xl);
            margin-top: 60px;
        }

        .map_slide_item {
            position: relative;
            outline: none;
        }

        .iframe_box {
            border-radius: var(--radius-xl);
            overflow: hidden;
            line-height: 0;
            filter: grayscale(0.2);
        }

        /* --- META INFO (Bảng thông tin nổi) --- */
        .meta_info_box {
            position: absolute;
            bottom: 40px;
            left: 40px;
            background: #fff;
            padding: 30px;
            border-radius: var(--radius-md);
            width: 350px;
            z-index: 10;
            box-shadow: var(--shadow);
        }

        .city_label {
            display: inline-block;
            padding: 3px 12px;
            background: var(--bg-pink);
            color: var(--accent-red);
            border-radius: 5px;
            font-weight: 700;
        }

        /* --- NAVIGATION BUTTONS --- */
        .map_nav_box {
            position: absolute;
            bottom: 40px;
            right: 40px;
            z-index: 20;
            display: flex;
            gap: 15px;
        }

        .nav-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--accent-teal);
        }

        .nav-btn:hover {
            background: var(--primary-navy);
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 991.98px) {
            .contact_item {
                padding: 30px;
            }

            .meta_info_box {
                width: 320px;
                padding: 25px;
            }
        }

        @media (max-width: 767.98px) {
            .meta_info_box {
                position: static;
                width: 100%;
                margin-top: -20px;
                box-shadow: none;
                border: 1px solid #eee;
            }

            .map_nav_box {
                bottom: auto;
                top: 20px;
                right: 20px;
            }

            .iframe_box iframe {
                height: 400px;
            }
        }
    </style>
@endsection
@section('content')
    <div class="contact_page py-80">
        <div class="container">
            <div class="title_animate mb-4 pe-5 mb-lg-5">
                <x-svg.title-accent class="active" />
                <h1 class="fs-48 ff-title cl-green mb-0">Có thắc mắc hoặc<br>
                    cần thêm thông tin?</h1>
                <div class="title_icon">
                    <img src="https://theforumcenter.com/wp-content/themes/the-forum/assets/images/plane.svg"
                        class="img-fluid" alt="">
                </div>
            </div>
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="contact_item item_1">
                        <h4 class="fs-32 ff-title cl-green ls-1 mb-3">Học IELTS</h4>
                        <div class="desc fw-light mb-5">
                            Nhận tư vấn về khoá học IELTS, SAT, tiếng anh trẻ em và thanh thiếu niên. </div>
                        <a href="" class="contact_btn ff-title" data-bs-toggle="modal"
                            data-bs-target="#adviseModal">Form Đăng ký <img
                                src="https://theforumcenter.com/wp-content/themes/the-forum/assets/images/readmore.png"
                                alt=""></a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="contact_item item_2">
                        <h4 class="fs-32 ff-title cl-green ls-1 mb-3">Thi IELTS</h4>
                        <div class="desc fw-light mb-5">
                            Đăng kí lịch thi iELTS tại The Form và đối tác IDP </div>
                        <a href="" class="contact_btn ff-title" data-bs-toggle="modal"
                            data-bs-target="#testModal">Form đăng ký <img
                                src="https://theforumcenter.com/wp-content/themes/the-forum/assets/images/readmore.png"
                                alt=""></a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="contact_item item_3">
                        <h4 class="fs-32 ff-title cl-green ls-1 mb-3">Liên hệ</h4>
                        <div class="desc fw-light mb-5">
                            Hợp tác, hoặc các thắc mắc khác. </div>
                        <a href="mailto:hi@theforumcenter.vn" class="contact_btn ff-title">Gửi email <img
                                src="https://theforumcenter.com/wp-content/themes/the-forum/assets/images/readmore.png"
                                alt=""></a>
                    </div>
                </div>
            </div>
            <div class="map_section_container mt-5 position-relative">
                <div class="map_nav_box">
                    <button class="nav-btn btn-prev">
                        <img src="https://theforumcenter.com/wp-content/themes/the-forum/assets/images/prev.png"
                            alt="Prev">
                    </button>
                    <button class="nav-btn btn-next">
                        <img src="https://theforumcenter.com/wp-content/themes/the-forum/assets/images/next.png"
                            alt="Next">
                    </button>
                </div>
                <div class="map_main_slider">
                    {{-- ITEM 1: Cơ sở Vũng Tàu --}}
                    @foreach ($coSoDaoTao as $coSo)
                        <div class="map_slide_item">
                            <div class="iframe_box">
                                <iframe src="{{ $coSo->banDoGoogle }}" width="100%" height="500" style="border:0;"
                                    allowfullscreen="" loading="lazy"></iframe>
                            </div>
                            <div class="meta_info_box shadow-lg">
                                <div class="city_label fs-12 mb-2">{{ $coSo->tinhThanh->tenTinhThanh }}</div>
                                <h4 class="fs-24 ff-title text-primary-dark mb-3">{{ $coSo->tenCoSo }}</h4>
                                <div class="address_text fs-12 fw-light cl-gray">
                                    {{ $coSo->diaChi }}
                                </div>
                                {{-- số điện thoại --}}
                                <div class="phone_text fs-12 fw-light cl-gray mt-2">
                                    Điện thoại: {{ $coSo->soDienThoai }}
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
@section('script')
    <script>
        $(document).ready(function() {
            $('.map_main_slider').slick({
                infinite: true,
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: true,
                fade: true, // Hiệu ứng mượt mà cho bản đồ
                cssEase: 'linear',
                // Liên kết nút bấm tùy chỉnh của bạn
                prevArrow: $('.btn-prev'),
                nextArrow: $('.btn-next'),
                responsive: [{
                    breakpoint: 768,
                    settings: {
                        adaptiveHeight: true
                    }
                }]
            });
        });
    </script>
@endsection
