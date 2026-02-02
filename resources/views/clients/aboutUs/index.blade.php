@extends('layouts.client')

@section('title', 'Về Chúng Tôi - Five Genius English Center')

@section('stylesheet')
    <style>
        /* ===== ABOUT US PAGE CSS ===== */

        /* Variables */
        :root {
            --primary-green: #10454f;
            --primary-red: #e31e24;
            --accent-teal: #27c4b5;
            --text-gray: #636e72;
            --light-bg: #f8f9fa;
        }

        /* ===== INTRO SECTION ===== */
        .intro-about {
            padding: 100px 0 80px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            position: relative;
            overflow: hidden;
        }

        .intro-about::before {
            content: "";
            position: absolute;
            top: -50%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle,
                    rgba(39, 196, 181, 0.1) 0%,
                    transparent 70%);
            border-radius: 50%;
        }

        .intro-about .title_animate {
            position: relative;
            display: inline-block;
        }

        .intro-about .title_animate svg {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            z-index: 0;
            pointer-events: none;
        }

        .intro-about h1 {
            position: relative;
            z-index: 1;
        }

        /* ===== SHARED TITLE ANIMATE (for all sections) ===== */
        .title_animate {
            position: relative;
            display: inline-block;
        }

        .title_animate svg {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            z-index: 0;
            pointer-events: none;
            /* SVG không chặn click vào text */
        }

        .title_animate h2,
        .title_animate h3,
        .title_animate .fs-48 {
            position: relative !important;
            z-index: 1 !important;
            /* Text nằm TRÊN SVG */
        }

        .title_animate .title_icon {
            position: absolute;
            right: -40px;
            top: -15px;
            z-index: 2;
        }

        .title_animate .title_icon img {
            width: 35px;
            animation: float 3s ease-in-out infinite;
        }

        .intro-about .title_icon {
            position: absolute;
            right: -30px;
            top: -20px;
        }

        .intro-about .title_icon img {
            width: 40px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .intro-about .desc {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-gray);
            max-width: 800px;
            margin: 0 auto;
        }

        /* Video Wrapper */
        .video-wrapper {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .video-wrapper::before,
        .video-wrapper::after {
            content: "";
            position: absolute;
            width: 120px;
            height: 120px;
            background-size: contain;
            background-repeat: no-repeat;
            z-index: 2;
            pointer-events: none;
            /* QUAN TRỌNG: Không chặn click vào video */
        }

        .video-wrapper iframe {
            width: 100%;
            aspect-ratio: 16/9;
            background: #000;
            display: block;
            position: relative;
            z-index: 1;
        }

        .video-wrapper::before {
            top: -30px;
            right: -30px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ccircle cx='50' cy='50' r='45' fill='none' stroke='%2327C4B5' stroke-width='2' stroke-dasharray='10 5'/%3E%3C/svg%3E");
            animation: rotate 20s linear infinite;
        }

        .video-wrapper::after {
            bottom: -30px;
            left: -30px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Cpolygon points='50,5 95,90 5,90' fill='none' stroke='%23E31E24' stroke-width='2'/%3E%3C/svg%3E");
            animation: rotate 25s linear infinite reverse;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .video-wrapper iframe {
            width: 100%;
            aspect-ratio: 16/9;
            border: none;
            border-radius: 20px;
        }

        /* ===== PROGRAMS SECTION ===== */
        .programs-section {
            padding: 80px 0;
            background: #fff;
        }

        .program-item {
            margin-bottom: 60px;
            padding: 40px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .program-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }

        .program-item::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(to bottom,
                    var(--primary-red),
                    var(--accent-teal));
        }

        .program-item h4 {
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .program-item h4 a {
            color: var(--primary-green);
            text-decoration: none;
            transition: color 0.3s;
        }

        .program-item h4 a:hover {
            color: var(--primary-red);
        }

        .program-item .desc {
            color: var(--text-gray);
            line-height: 1.7;
            margin-bottom: 20px;
        }

        .program-item figure {
            margin: 0;
            border-radius: 15px;
            overflow: hidden;
        }

        .program-item figure img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .program-item:hover figure img {
            transform: scale(1.05);
        }

        .program-item .logo-badge {
            position: absolute;
            bottom: 20px;
            right: 20px;
            width: 80px;
            height: 80px;
            background: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .program-item .logo-badge img {
            max-width: 50px;
        }

        /* ===== FACILITIES SECTION ===== */
        .facilities-section {
            padding: 80px 0;
            background: var(--light-bg);
        }

        .facility-gallery {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
            margin-bottom: 40px;
        }

        .facility-gallery .item {
            border-radius: 15px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .facility-gallery .item:hover {
            transform: scale(1.05);
        }

        .facility-gallery .item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        @media (max-width: 992px) {
            .facility-gallery {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 576px) {
            .facility-gallery {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Location Filter */
        .location-filter {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 30px;
        }

        .location-filter li {
            list-style: none;
        }

        .location-filter li a {
            display: inline-block;
            padding: 10px 25px;
            background: #fff;
            border-radius: 50px;
            color: var(--primary-green);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .location-filter li a:hover,
        .location-filter li.active a {
            background: var(--primary-green);
            color: #fff;
        }

        /* Location Slider */
        .location-slider {
            position: relative;
        }

        .location-card {
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            margin: 0 10px;
        }

        .location-card:hover {
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .location-card figure {
            margin: 0;
            overflow: hidden;
        }

        .location-card figure img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .location-card:hover figure img {
            transform: scale(1.1);
        }

        .location-card .card-content {
            padding: 20px;
        }

        .location-card .city-tag {
            display: inline-block;
            padding: 5px 12px;
            background: rgba(39, 196, 181, 0.1);
            color: var(--accent-teal);
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .location-card h4 {
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .location-card h4 a {
            color: var(--primary-green);
            text-decoration: none;
        }

        .location-card .address {
            color: var(--text-gray);
            font-size: 14px;
            line-height: 1.5;
        }

        /* Slider Navigation */
        .slider-nav-custom {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 30px;
        }

        .slider-nav-custom button {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid var(--primary-green);
            background: transparent;
            color: var(--primary-green);
            cursor: pointer;
            transition: all 0.3s;
        }

        .slider-nav-custom button:hover {
            background: var(--primary-green);
            color: #fff;
        }

        /* ===== TEAM SECTION ===== */
        .team-section {
            padding: 80px 0;
            background: #fff;
        }

        .team-card {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            text-align: center;
        }

        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }

        .team-card .avatar {
            width: 100%;
            height: 280px;
            overflow: hidden;
            position: relative;
        }

        .team-card .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .team-card:hover .avatar img {
            transform: scale(1.1);
        }

        .team-card .info {
            padding: 25px;
        }

        .team-card h4 {
            color: var(--primary-green);
            font-size: 1.3rem;
            margin-bottom: 5px;
        }

        .team-card .role {
            color: var(--primary-red);
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .team-card .bio {
            color: var(--text-gray);
            font-size: 14px;
            line-height: 1.6;
        }

        /* ===== VALUES SECTION ===== */
        .values-section {
            padding: 80px 0;
            background: linear-gradient(135deg, var(--primary-green) 0%, #0d3a42 100%);
            color: #fff;
        }

        .value-card {
            text-align: center;
            padding: 40px 30px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            transition: all 0.3s;
        }

        .value-card:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-10px);
        }

        .value-card .icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-red), #ff6b6b);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
        }

        .value-card h4 {
            font-size: 1.3rem;
            margin-bottom: 15px;
        }

        .value-card p {
            opacity: 0.9;
            line-height: 1.7;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .intro-about {
                padding: 80px 0 60px;
            }

            .intro-about h1 {
                font-size: 2rem;
            }

            .program-item {
                padding: 25px;
            }

            .program-item h4 {
                font-size: 1.4rem;
            }
        }

        /* ===== SYSTEM LOCATION SECTION ===== */
        .system_location {
            padding: 40px 0;
        }

        .system_locate {
            max-width: 1320px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* 1. Filter Tabs */
        .system_filter {
            display: flex;
            list-style: none;
            padding: 0;
            gap: 10px;
            margin-bottom: 40px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .system_filter li a {
            text-decoration: none;
            font-weight: 700;
            color: var(--primary-green);
            font-size: 16px;
            padding: 10px 20px;
            border-radius: 50px;
            transition: all 0.3s ease;
            display: inline-block;
            background: #fff;
            border: 2px solid var(--primary-green);
        }

        .system_filter li a:hover {
            background-color: var(--accent-teal);
            border-color: var(--accent-teal);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 196, 181, 0.3);
        }

        .system_filter li.active a {
            background-color: var(--primary-green);
            border-color: var(--primary-green);
            color: #fff;
        }

        /* 2. Wrapper */
        .locate_wrapper {
            position: relative;
            padding: 0 60px;
        }

        /* 3. Slider Container */
        .slider_locate {
            margin-left: -10px;
            margin-right: -10px;
        }

        .slider_locate .slick-track {
            display: flex !important;
            margin-left: 0 !important;
            justify-content: flex-start !important;
        }

        /* 4. Branch Card Item */
        .slider_locate .item {
            padding: 0 10px;
            outline: none;
        }

        .slider_locate .item figure {
            margin: 0 0 20px 0;
            border-radius: 30px;
            overflow: hidden;
            position: relative;
            height: 280px;
        }

        .slider_locate .item figure img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.5s ease;
        }

        .slider_locate .item figure:hover img {
            transform: scale(1.1);
        }

        /* 5. Badge & Text */
        .slider_locate .item .city {
            background: linear-gradient(135deg, rgba(227, 30, 36, 0.1), rgba(255, 77, 77, 0.15));
            color: var(--primary-red);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            margin-bottom: 15px;
        }

        .slider_locate .item h4 {
            margin-bottom: 12px;
        }

        .slider_locate .item h4 a {
            color: var(--primary-green);
            text-decoration: none;
            font-size: 24px;
            font-weight: 800;
            display: block;
            transition: color 0.3s ease;
            line-height: 1.3;
        }

        .slider_locate .item h4 a:hover {
            color: var(--primary-red);
        }

        .slider_locate .item .address,
        .slider_locate .item .mail {
            font-size: 14px;
            color: var(--text-gray);
            display: flex;
            align-items: flex-start;
            gap: 10px;
            line-height: 1.6;
            margin-bottom: 8px;
        }

        .slider_locate .item .address i,
        .slider_locate .item .mail i {
            color: var(--accent-teal);
            width: 18px;
            flex-shrink: 0;
            margin-top: 3px;
        }

        /* 6. Navigation Arrows */
        .nav-arrow {
            position: absolute;
            top: 40%;
            z-index: 100;
            transform: translateY(-50%);
        }

        .nav-prev {
            left: -20px;
        }

        .nav-next {
            right: -20px;
        }

        .nav-arrow a {
            background: linear-gradient(135deg, var(--accent-teal), #1ea89a);
            width: 55px;
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            box-shadow: 0 8px 20px rgba(42, 190, 177, 0.3);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .nav-arrow a:hover {
            background: linear-gradient(135deg, var(--primary-green), #0d3a42);
            transform: scale(1.1);
            box-shadow: 0 10px 30px rgba(16, 69, 79, 0.4);
        }

        .nav-arrow img {
            width: 22px;
            filter: brightness(0) invert(1);
        }

        /* 7. Slick Disabled State */
        .nav-arrow .slick-disabled {
            opacity: 0.4;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* 8. Location Section Responsive */
        @media (max-width: 1200px) {
            .locate_wrapper {
                padding: 0 50px;
            }

            .nav-prev {
                left: -15px;
            }

            .nav-next {
                right: -15px;
            }

            .nav-arrow a {
                width: 50px;
                height: 50px;
            }
        }

        @media (max-width: 992px) {
            .locate_wrapper {
                padding: 0 40px;
            }

            .system_filter {
                gap: 12px;
            }

            .system_filter li a {
                padding: 10px 20px;
                font-size: 14px;
            }

            .slider_locate .item h4 a {
                font-size: 20px;
            }

            .slider_locate .item figure {
                height: 240px;
            }
        }

        @media (max-width: 768px) {
            .locate_wrapper {
                padding: 0;
            }

            .nav-arrow {
                display: none;
            }

            .system_filter {
                overflow-x: auto;
                justify-content: flex-start;
                padding-bottom: 10px;
                -webkit-overflow-scrolling: touch;
            }

            .system_filter li a {
                white-space: nowrap;
            }

            .slider_locate .item figure {
                height: 220px;
                border-radius: 20px;
            }

            .slider_locate .item h4 a {
                font-size: 18px;
            }

            .slider_locate .item .address,
            .slider_locate .item .mail {
                font-size: 13px;
            }
        }

        @media (max-width: 480px) {
            .system_locate {
                padding: 20px 15px;
            }

            .system_filter li a {
                padding: 8px 16px;
                font-size: 13px;
            }

            .slider_locate .item .city {
                font-size: 10px;
                padding: 5px 12px;
            }

            .slider_locate .item h4 a {
                font-size: 16px;
            }

            .slider_locate .item figure {
                height: 200px;
            }
        }
    </style>
@endsection

@section('content')
    {{-- ===== INTRO SECTION ===== --}}
    <section class="intro-about">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="text-center">
                        {{-- Animated Title --}}
                        <div class="title_animate px-5 mb-lg-5 mb-4">
                            <svg width="377" height="117" viewBox="0 0 377 117" fill="none"
                                xmlns="http://www.w3.org/2000/svg" class="active">
                                <g opacity="0.5">
                                    <path
                                        d="M41.9234 42.5535C137.854 9.43906 258.635 21.1999 307.035 31.2197C230.669 34.998 5.91113 88.8754 5.91113 88.8754C5.91113 88.8754 221.602 27.2786 349.177 88.8754"
                                        stroke="#B8D3D9" stroke-width="40.8987" stroke-linecap="square"></path>
                                </g>
                            </svg>
                            <h1 class="fs-48 ff-title cl-green mb-0">Five Genius<br>English Center</h1>
                            <div class="title_icon no-1">
                                <img src="{{ asset('assets/images/star-title.png') }}" class="img-fluid" alt="">
                            </div>
                            <div class="title_icon no-2">
                                <img src="{{ asset('assets/images/star-title.png') }}" class="img-fluid" alt="">
                            </div>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="desc fw-light text-center mb-5" data-aos="fade-up">
                        <strong>Five Genius</strong> là tổ chức giáo dục chuyên đào tạo luyện thi <strong>IELTS,
                            SAT</strong>, và giảng dạy <strong>Tiếng Anh</strong> ở mọi trình độ. Với đội ngũ giảng viên
                        chất lượng cao và phương pháp giảng dạy tiên tiến, chúng tôi cam kết mang đến chất lượng đào tạo tốt
                        nhất cho học viên.
                    </div>

                    {{-- Video Intro --}}
                    <div class="video-wrapper" data-aos="fade-up" data-aos-delay="200">
                        <iframe src="https://www.youtube.com/embed/oafyFIOqtsw?autoplay=1&mute=1&rel=0&si=lhkQVctnoXQuh_A6"
                            title="Five Genius Introduction" loading="lazy"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== VALUES SECTION ===== --}}
    <section class="values-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fs-48 ff-title text-white mb-3">Giá Trị Cốt Lõi</h2>
                <p class="text-white opacity-75">Những giá trị định hình nên Five Genius</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="value-card">
                        <div class="icon"><i class="fas fa-star"></i></div>
                        <h4>Chất Lượng</h4>
                        <p>Cam kết chất lượng đầu ra với đội ngũ giảng viên IELTS 8.0+ và phương pháp đào tạo chuẩn quốc tế.
                        </p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="value-card">
                        <div class="icon"><i class="fas fa-heart"></i></div>
                        <h4>Tận Tâm</h4>
                        <p>Đồng hành cùng học viên trong suốt hành trình học tập với sự quan tâm và hỗ trợ tận tình.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="value-card">
                        <div class="icon"><i class="fas fa-lightbulb"></i></div>
                        <h4>Sáng Tạo</h4>
                        <p>Ứng dụng công nghệ và phương pháp giảng dạy hiện đại để tối ưu hóa trải nghiệm học tập.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                    <div class="value-card">
                        <div class="icon"><i class="fas fa-users"></i></div>
                        <h4>Cộng Đồng</h4>
                        <p>Xây dựng cộng đồng học viên năng động, hỗ trợ lẫn nhau trên con đường chinh phục ngoại ngữ.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== PROGRAMS SECTION ===== --}}
    <section class="programs-section">
        <div class="container">
            <div class="text-center mb-5">
                <div class="title_animate px-5 mb-lg-5 mb-4">
                    <svg width="567" height="115" viewBox="0 0 567 115" fill="none"
                        xmlns="http://www.w3.org/2000/svg" class="active">
                        <g opacity="0.5">
                            <path
                                d="M60.2197 42.5534C210.232 9.439 399.105 21.1999 474.79 31.2196C355.372 34.9979 3.90527 88.8754 3.90527 88.8754C3.90527 88.8754 341.193 27.2786 540.69 88.8754"
                                stroke="#B8D3D9" stroke-width="40.8987" stroke-linecap="square"></path>
                        </g>
                    </svg>
                    <h2 class="fs-48 ff-title cl-green mb-0">Các Chương Trình<br>Đào Tạo</h2>
                    <div class="title_icon">
                        <img src="{{ asset('assets/images/star-title.png') }}" class="img-fluid" alt="">
                    </div>
                </div>
            </div>

            {{-- Program Items --}}
            <div class="program-list">
                {{-- IELTS Program --}}
                <div class="program-item" data-aos="fade-up">
                    <div class="row align-items-center">
                        <div class="col-lg-5">
                            <h4 class="fs-32 ff-title">
                                <a href="#">Luyện Thi IELTS</a>
                            </h4>
                            <div class="desc fw-light">
                                <p>Lộ trình học IELTS toàn diện, được nghiên cứu và thiết kế riêng biệt. Xây dựng nền tảng
                                    Tiếng Anh vững chắc với tư duy ngôn ngữ phù hợp với mọi lứa tuổi và trình độ.</p>
                                <ul class="list-unstyled mt-3">
                                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>Cam kết đầu ra IELTS 6.5+
                                    </li>
                                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>Giảng viên 8.0+ IELTS</li>
                                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>Lớp học 10-15 học viên
                                    </li>
                                </ul>
                            </div>
                            <a href="#" class="btn btn-red mt-3">Tìm Hiểu Thêm <i
                                    class="fas fa-arrow-right ms-2"></i></a>
                        </div>
                        <div class="col-lg-7">
                            <figure>
                                <img src="https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=800"
                                    alt="IELTS Program" class="img-fluid rounded-4" loading="lazy">
                            </figure>
                        </div>
                    </div>
                </div>

                {{-- SAT Program --}}
                <div class="program-item" data-aos="fade-up" data-aos-delay="100">
                    <div class="row align-items-center flex-row-reverse">
                        <div class="col-lg-5">
                            <h4 class="fs-32 ff-title">
                                <a href="#">SAT Preparation</a>
                            </h4>
                            <div class="desc fw-light">
                                <p>Luyện thi SAT với lộ trình học tập khoa học, giúp cải thiện và phát triển toàn diện kỹ
                                    năng cho học viên. Trọng tâm sát nhất với các tiêu chí đánh giá của bài thi.</p>
                                <ul class="list-unstyled mt-3">
                                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>Mục tiêu 1400+ SAT</li>
                                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>Tài liệu độc quyền</li>
                                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>Thi thử hàng tuần</li>
                                </ul>
                            </div>
                            <a href="#" class="btn btn-red mt-3">Tìm Hiểu Thêm <i
                                    class="fas fa-arrow-right ms-2"></i></a>
                        </div>
                        <div class="col-lg-7">
                            <figure>
                                <img src="https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=800"
                                    alt="SAT Program" class="img-fluid rounded-4" loading="lazy">
                            </figure>
                        </div>
                    </div>
                </div>

                {{-- Kids & Teenagers --}}
                <div class="program-item" data-aos="fade-up" data-aos-delay="200">
                    <div class="row align-items-center">
                        <div class="col-lg-5">
                            <h4 class="fs-32 ff-title">
                                <a href="#">Tiếng Anh Trẻ Em</a>
                            </h4>
                            <div class="desc fw-light">
                                <p>Xây dựng nền tảng tiếng Anh toàn diện, phát triển khả năng tư duy logic và phản biện. Đào
                                    tạo theo chuẩn đầu ra của chứng chỉ Cambridge cho các bé từ 6-15 tuổi.</p>
                                <ul class="list-unstyled mt-3">
                                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>Phương pháp vui học</li>
                                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>Chuẩn Cambridge YLE</li>
                                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>Lớp học sinh động</li>
                                </ul>
                            </div>
                            <a href="#" class="btn btn-red mt-3">Tìm Hiểu Thêm <i
                                    class="fas fa-arrow-right ms-2"></i></a>
                        </div>
                        <div class="col-lg-7">
                            <figure>
                                <img src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=800"
                                    alt="Kids Program" class="img-fluid rounded-4" loading="lazy">
                            </figure>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== FACILITIES SECTION ===== --}}
    <section class="system_location pt-100 pb-100">
        <div class="text-center">
            <div class="title_animate px-5 mb-lg-5 mb-4">
                <svg width="377" height="117" viewBox="0 0 377 117" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <g opacity="0.5">
                        <path
                            d="M41.9234 42.5535C137.854 9.43906 258.635 21.1999 307.035 31.2197C230.669 34.998 5.91113 88.8754 5.91113 88.8754C5.91113 88.8754 221.602 27.2786 349.177 88.8754"
                            stroke="#B8D3D9" stroke-width="40.8987" stroke-linecap="square" class="title-style-5">
                        </path>
                    </g>
                </svg>

                <h3 class="fs-48 ff-title cl-green mb-0 textSkewUp">
                    Hệ thống </br> trung tâm
                </h3>
                <div class="title_icon">
                    <img src="https://theforumcenter.com/wp-content/themes/the-forum/assets/images/cookieicon.svg"
                        class="img-fluid" alt="">
                </div>
            </div>
        </div>
        <div class="system_locate pt-40">
            {{-- Menu lọc tỉnh thành --}}
            <ul class="system_filter mb-3">
                <li class="active"><a href="#" data-filter="all">Tất cả</a></li>
                @foreach ($provinces as $province)
                    {{-- Dùng ID để làm filter để đảm bảo tính duy nhất --}}
                    <li><a href="#"
                            data-filter="province-{{ $province->tinhThanhId }}">{{ $province->tenTinhThanh }}</a></li>
                @endforeach
            </ul>

            <div class="locate_wrapper">
                {{-- Nút điều hướng --}}
                <div class="nav-arrow nav-prev">
                    <a href="javascript:void(0)"><img
                            src="https://theforumcenter.com/wp-content/themes/the-forum/assets/images/prev.png"
                            alt="Prev"></a>
                </div>
                <div class="nav-arrow nav-next">
                    <a href="javascript:void(0)"><img
                            src="https://theforumcenter.com/wp-content/themes/the-forum/assets/images/next.png"
                            alt="Next"></a>
                </div>

                {{-- Danh sách cơ sở --}}
                <div class="slider_locate">
                    @foreach ($branches as $branch)
                        {{-- Thêm class province-ID để JS có thể lọc --}}
                        <div class="item province-{{ $branch->tinhThanhId }}">
                            <figure>
                                <a href="{{ $branch->banDoGoogle ?? '#' }}" target="_blank">
                                    <img src="{{ asset('storage/' . $branch->hinhAnh) }}" alt="{{ $branch->tenCoSo }}"
                                        onerror="this.src='https://theforumcenter.com/wp-content/uploads/2025/05/brand-500x400.jpg'">
                                </a>
                            </figure>
                            <div class="city">{{ $branch->tinhThanh->tenTinhThanh ?? 'N/A' }}</div>
                            <h4>
                                <a href="{{ $branch->link_map ?? '#' }}" target="_blank">
                                    {{ $branch->tenCoSo }}
                                </a>
                            </h4>
                            <div class="address">
                                <i class="fas fa-map-marker-alt"></i> {{ $branch->diaChi }}
                            </div>
                            <div class="mail">
                                <i class="fas fa-envelope"></i> {{ $branch->email }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ===== TEAM SECTION ===== --}}
    <section class="team-section">
        <div class="container">
            <div class="text-center mb-5">
                <div class="title_animate px-5 mb-lg-5 mb-4">
                    <svg width="400" height="115" viewBox="0 0 567 115" fill="none"
                        xmlns="http://www.w3.org/2000/svg" class="active">
                        <g opacity="0.5">
                            <path
                                d="M60.2197 42.5534C210.232 9.439 399.105 21.1999 474.79 31.2196C355.372 34.9979 3.90527 88.8754 3.90527 88.8754C3.90527 88.8754 341.193 27.2786 540.69 88.8754"
                                stroke="#B8D3D9" stroke-width="40.8987" stroke-linecap="square"></path>
                        </g>
                    </svg>
                    <h2 class="fs-48 ff-title cl-green mb-0">Đội Ngũ<br>Giảng Viên</h2>
                    <div class="title_icon">
                        <img src="{{ asset('assets/images/star-title.png') }}" class="img-fluid" alt="">
                    </div>
                </div>
                <p class="text-muted">Đội ngũ giảng viên chất lượng với chứng chỉ IELTS 8.0+ và kinh nghiệm giảng dạy đa
                    dạng</p>
            </div>

            <div class="row g-4">
                @if (isset($topGiaoVien) && count($topGiaoVien) > 0)
                    @foreach ($topGiaoVien as $giaoVien)
                        <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                            <div class="team-card">
                                <div class="avatar">
                                    <img src="{{ $giaoVien->hoSoNguoiDung && $giaoVien->hoSoNguoiDung->anhDaiDien ? asset('storage/teachers/' . $giaoVien->hoSoNguoiDung->anhDaiDien) : asset('assets/images/default-teacher.jpeg') }}"
                                        alt="{{ $giaoVien->hoSoNguoiDung->hoTen ?? 'Giảng viên' }}" loading="lazy">
                                </div>
                                <div class="info">
                                    <h4>{{ $giaoVien->hoSoNguoiDung->hoTen ?? 'Giảng viên' }}</h4>
                                    <div class="role">IELTS {{ $giaoVien->nhanSu->bangCap ?? '8.0+' }}</div>
                                    <p class="bio">{{ $giaoVien->nhanSu->hocVi ?? 'Giảng viên IELTS' }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    {{-- Default team members if no data --}}
                    <div class="col-lg-3 col-md-6" data-aos="fade-up">
                        <div class="team-card">
                            <div class="avatar">
                                <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400"
                                    alt="Teacher 1" loading="lazy">
                            </div>
                            <div class="info">
                                <h4>Nguyễn Văn A</h4>
                                <div class="role">IELTS 8.5</div>
                                <p class="bio">Thạc sĩ Ngôn ngữ Anh - Đại học Sư phạm TP.HCM</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                        <div class="team-card">
                            <div class="avatar">
                                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400"
                                    alt="Teacher 2" loading="lazy">
                            </div>
                            <div class="info">
                                <h4>Trần Văn B</h4>
                                <div class="role">IELTS 8.0</div>
                                <p class="bio">Cử nhân Sư phạm Anh - 5 năm kinh nghiệm</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                        <div class="team-card">
                            <div class="avatar">
                                <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?w=400"
                                    alt="Teacher 3" loading="lazy">
                            </div>
                            <div class="info">
                                <h4>Lê Thị C</h4>
                                <div class="role">IELTS 8.5</div>
                                <p class="bio">Thạc sĩ TESOL - Đại học Melbourne</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                        <div class="team-card">
                            <div class="avatar">
                                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400"
                                    alt="Teacher 4" loading="lazy">
                            </div>
                            <div class="info">
                                <h4>Phạm Văn D</h4>
                                <div class="role">SAT 1550</div>
                                <p class="bio">Cựu du học sinh Mỹ - Chuyên gia luyện SAT</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ===== REGISTER SECTION ===== --}}
    <x-client.register-advice />

@endsection
@section('script')
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
        $(document).ready(function() {
            // 1. Khởi tạo Slick Slider
            var $slider = $('.slider_locate').slick({
                rows: 0, // Quan trọng để tránh lỗi dư thừa div
                slidesToShow: 4,
                slidesToScroll: 1,
                infinite: false,
                arrows: true,
                prevArrow: $('.nav-prev a'), // Kết nối nút HTML với Slick
                nextArrow: $('.nav-next a'),
                responsive: [{
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 2
                        }
                    },
                    {
                        breakpoint: 600,
                        settings: {
                            slidesToShow: 1
                        }
                    }
                ]
            });

            // 2. Xử lý Filter
            $('.system_filter a').on('click', function(e) {
                e.preventDefault(); // Chặn load lại trang

                $('.system_filter li').removeClass('active');
                $(this).parent().addClass('active');

                var filter = $(this).data('filter');

                $slider.slick('slickUnfilter'); // Xóa bộ lọc cũ
                if (filter !== 'all') {
                    // Slick filter sử dụng class (ví dụ .tp-vung-tau)
                    $slider.slick('slickFilter', '.' + filter);
                }
            });
        });
    </script>
@endsection
