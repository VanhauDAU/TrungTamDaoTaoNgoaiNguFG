@extends('layouts.client')

@section('title', 'Về Chúng Tôi - Five Genius English Center')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/client/css/about.css') }}">
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
                    <svg width="567" height="115" viewBox="0 0 567 115" fill="none" xmlns="http://www.w3.org/2000/svg"
                        class="active">
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
                                    <li><i class="bi bi-check-circle-fill text-success me-2"></i>Lớp học 10-15 học viên</li>
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
    <section class="facilities-section">
        <div class="container">
            <div class="text-center mb-5">
                <div class="title_animate px-5 mb-lg-5 mb-4">
                    <svg width="377" height="117" viewBox="0 0 377 117" fill="none" xmlns="http://www.w3.org/2000/svg"
                        class="active">
                        <g opacity="0.5">
                            <path
                                d="M41.9234 42.5535C137.854 9.43906 258.635 21.1999 307.035 31.2197C230.669 34.998 5.91113 88.8754 5.91113 88.8754C5.91113 88.8754 221.602 27.2786 349.177 88.8754"
                                stroke="#B8D3D9" stroke-width="40.8987" stroke-linecap="square"></path>
                        </g>
                    </svg>
                    <h2 class="fs-48 ff-title cl-green mb-0">Hệ Thống<br>Cơ Sở</h2>
                    <div class="title_icon">
                        <img src="{{ asset('assets/images/star-title.png') }}" class="img-fluid" alt="">
                    </div>
                </div>
            </div>

            {{-- Gallery --}}
            <div class="facility-gallery mb-5" data-aos="fade-up">
                <div class="item">
                    <img src="https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=400" alt="Classroom 1"
                        loading="lazy">
                </div>
                <div class="item">
                    <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=400" alt="Classroom 2"
                        loading="lazy">
                </div>
                <div class="item">
                    <img src="https://images.unsplash.com/photo-1606761568499-6d2451b23c66?w=400" alt="Library"
                        loading="lazy">
                </div>
                <div class="item">
                    <img src="https://images.unsplash.com/photo-1562774053-701939374585?w=400" alt="Campus" loading="lazy">
                </div>
                <div class="item">
                    <img src="https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=400" alt="Study Area"
                        loading="lazy">
                </div>
            </div>

            {{-- Locations --}}
            <div class="row g-4 mt-4">
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="location-card">
                        <figure>
                            <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?w=500" alt="Cơ sở 1"
                                loading="lazy">
                        </figure>
                        <div class="card-content">
                            <span class="city-tag">TP. Hồ Chí Minh</span>
                            <h4><a href="#">Cơ Sở Quận 1</a></h4>
                            <div class="address">
                                <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                                123 Nguyễn Huệ, Quận 1, TP.HCM
                            </div>
                            <div class="mt-2">
                                <i class="fas fa-phone me-2 text-success"></i>
                                <a href="tel:0777464347">0777.46.43.47</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="location-card">
                        <figure>
                            <img src="https://images.unsplash.com/photo-1562774053-701939374585?w=500" alt="Cơ sở 2"
                                loading="lazy">
                        </figure>
                        <div class="card-content">
                            <span class="city-tag">TP. Hồ Chí Minh</span>
                            <h4><a href="#">Cơ Sở Tân Bình</a></h4>
                            <div class="address">
                                <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                                456 Lê Văn Sỹ, Quận Tân Bình, TP.HCM
                            </div>
                            <div class="mt-2">
                                <i class="fas fa-phone me-2 text-success"></i>
                                <a href="tel:0777464347">0777.46.43.47</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                    <div class="location-card">
                        <figure>
                            <img src="https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=500" alt="Cơ sở 3"
                                loading="lazy">
                        </figure>
                        <div class="card-content">
                            <span class="city-tag">TP. Hồ Chí Minh</span>
                            <h4><a href="#">Cơ Sở Bình Thạnh</a></h4>
                            <div class="address">
                                <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                                789 Điện Biên Phủ, Quận Bình Thạnh, TP.HCM
                            </div>
                            <div class="mt-2">
                                <i class="fas fa-phone me-2 text-success"></i>
                                <a href="tel:0777464347">0777.46.43.47</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== TEAM SECTION ===== --}}
    <section class="team-section">
        <div class="container">
            <div class="text-center mb-5">
                <div class="title_animate px-5 mb-lg-5 mb-4">
                    <svg width="400" height="115" viewBox="0 0 567 115" fill="none" xmlns="http://www.w3.org/2000/svg"
                        class="active">
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
                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
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
                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
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
                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
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
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true
        });
    </script>
@endsection