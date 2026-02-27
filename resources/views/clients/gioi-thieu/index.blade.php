@extends('layouts.client')

@section('title', 'Về Chúng Tôi - Five Genius English Center')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/aboutUs.css') }}">
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
                            <a href="#" class="btn btn-red mt-3">Tìm Hiểu Thêm <i class="fas fa-arrow-right ms-2"></i></a>
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
                            <a href="#" class="btn btn-red mt-3">Tìm Hiểu Thêm <i class="fas fa-arrow-right ms-2"></i></a>
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
                            <a href="#" class="btn btn-red mt-3">Tìm Hiểu Thêm <i class="fas fa-arrow-right ms-2"></i></a>
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
                    <li><a href="#" data-filter="province-{{ $province->tinhThanhId }}">{{ $province->tenTinhThanh }}</a></li>
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
    <section class="team-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <div class="title_animate px-5 mb-lg-5 mb-4">
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
                        <div class="col-lg-3 col-md-6">
                            <div class="team-card">
                                <div class="avatar">
                                    <img src="{{ $giaoVien->hoSoNguoiDung && $giaoVien->hoSoNguoiDung->anhDaiDien ? asset('storage/' . $giaoVien->hoSoNguoiDung->anhDaiDien) : asset('assets/images/default-teacher.jpeg') }}"
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
                    <div class="col-lg-3 col-md-6">
                        <div class="team-card">
                            <div class="avatar">
                                <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400" alt="Teacher 1"
                                    loading="lazy">
                            </div>
                            <div class="info">
                                <h4>Nguyễn Văn A</h4>
                                <div class="role">IELTS 8.5</div>
                                <p class="bio">Thạc sĩ Ngôn ngữ Anh - Đại học Sư phạm TP.HCM</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="team-card">
                            <div class="avatar">
                                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400" alt="Teacher 2"
                                    loading="lazy">
                            </div>
                            <div class="info">
                                <h4>Trần Văn B</h4>
                                <div class="role">IELTS 8.0</div>
                                <p class="bio">Cử nhân Sư phạm Anh - 5 năm kinh nghiệm</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="team-card">
                            <div class="avatar">
                                <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?w=400" alt="Teacher 3"
                                    loading="lazy">
                            </div>
                            <div class="info">
                                <h4>Lê Thị C</h4>
                                <div class="role">IELTS 8.5</div>
                                <p class="bio">Thạc sĩ TESOL - Đại học Melbourne</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="team-card">
                            <div class="avatar">
                                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400" alt="Teacher 4"
                                    loading="lazy">
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
        $(document).ready(function () {
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
            $('.system_filter a').on('click', function (e) {
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