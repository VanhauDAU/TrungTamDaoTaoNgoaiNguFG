<footer id="footer_site">
    <div class="custom-container">
        <div class="row gx-lg-5 justify-content-between">
            <div class="col-lg-3">
                <div class="mb-4">
                    <a href="{{ route('home.index') }}" class="logo_ft">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="">
                    </a>
                </div>
                <div class="fw-light cl-gray mb-4">
                    Trung tâm Ngoại ngữ hàng đầu chuyên luyện thi IELTS, SAT và giảng dạy tiếng Anh cho mọi trình độ.
                </div>
                <ul class="contact">
                    <li><a href="mailto:fivegenius@gmail.com">fivegenius@gmail.com</a></li>
                    <li><a href="tel:0777464347">0777.46.43.47</a></li>
                </ul>
            </div>
            <div class="col-lg col-6">
                <h4 class="fs-12 ff-title cl-red mb-3">THE FIVEGENIUS</h4>
                <ul>
                    <li><a href="https://theforumcenter.com/" target="">Trang chủ</a></li>
                    <li><a href="https://theforum.vn/" target="_blank">The FiveGenius</a></li>
                    <li><a href="https://theforumcenter.com/blog/" target="">Blog</a></li>
                    <li><a href="https://theforumcenter.com/lien-he/" target="">Liên hệ</a></li>
                </ul>
            </div>
            <div class="col-lg col-6">
                <h4 class="fs-12 ff-title cl-red mb-3">Khoá học</h4>
                <ul>
                    @foreach ($footerCourses as $khoaHoc)
                        <li>
                            <a href="https://theforumcenter.com/khoa-hoc/ielts-tai-trung-tam/"
                                target="">{{ $khoaHoc->tenKhoaHoc }}</a>
                        </li>
                    @endforeach
                    <li>
                        <a href="{{ route('home.courses.index') }}">Xem thêm...</a>
                    </li>
                </ul>
            </div>
            <div class="col-lg col-6">
                <h4 class="fs-12 ff-title cl-red mb-3">THI THỬ</h4>
                <ul>
                    <li><a href="https://theforumcenter.com/thi-thu-ielts-la-gi/" target="">Thi thử IELTS là
                            gì?</a></li>
                    <li><a href="https://theforumcenter.com/danh-sach-bai-thi/" target="">Danh sách bài thi</a>
                    </li>
                </ul>
            </div>
            <div class="col-lg col-6">
                <h4 class="fs-12 ff-title cl-red mb-3">CHÍNH SÁCH</h4>
                <ul>
                    <li><a href="https://theforumcenter.com/chinh-sach-bao-hanh/" target="">Chính sách bảo hành</a>
                    </li>
                    <li><a href="https://theforumcenter.com/chinh-sach-bao-ve-thong-tin-nguoi-tieu-dung/"
                            target="">Chính Sách Bảo Vệ Thông Tin Người Tiêu Dùng</a></li>
                    <li><a href="https://theforumcenter.com/chinh-sach-dat-hang/" target="">Chính sách đặt hàng</a>
                    </li>
                    <li><a href="https://theforumcenter.com/chinh-sach-van-chuyen-giao-nhan/" target="">Chính sách
                            vận chuyển, giao nhận</a></li>
                    <li><a href="https://theforumcenter.com/dieu-khoan-dich-vu/" target="">Điều khoản dịch vụ</a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="copyright mt-80">
            <div class="row gx-lg-5 align-items-center">
                <div class="col-lg-3 col-auto order-lg-0">
                    <div class="ff-title cl-green">
                        © 2026 The FiveGenius. All Rights Reserved.
                    </div>
                </div>
                <div class="col-lg col-auto order-lg-1">
                    <div class="ff-title cl-green">
                        Make With Love by <a href="https://adesolutions.vn/" target="_blank">ADE</a>
                    </div>
                </div>
                <div class="col-lg-auto order-lg-2">
                    <ul class="social mt-2 mt-lg-0">
                        <li><a href="https://www.facebook.com/theforum.english/" target="_blank"><img
                                    src="https://theforumcenter.com/wp-content/themes/the-forum/assets/images/facebook.png"
                                    class="img-fluid" alt=""></a></li>
                        <li><a href="https://www.instagram.com/nhhforum" target="_blank"><img
                                    src="https://theforumcenter.com/wp-content/themes/the-forum/assets/images/instagram.png"
                                    class="img-fluid" alt=""></a></li>
                        <li><a href="https://tiktok.com/@theforumcenter" target="_blank"><img
                                    src="https://theforumcenter.com/wp-content/themes/the-forum/assets/images/tiktok.png"
                                    class="img-fluid" alt=""></a></li>
                        <li><a href="https://www.youtube.com/@theforumcenter" target="_blank"><img
                                    src="https://theforumcenter.com/wp-content/themes/the-forum/assets/images/youtube.png"
                                    class="img-fluid" alt=""></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>
