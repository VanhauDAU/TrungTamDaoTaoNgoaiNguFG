{{-- REGISTER ADVISE SECTION --}}
<section id="form_register_wrapper" class="register-section py-80">
    <div class="container">
        <div class="form_register_wrapper mx-auto">
            {{-- Form Laravel chuẩn --}}
            <form action="{{ route('home.contact.consultation.store') }}" method="POST" class="register-sentence-form">
                @csrf
                <img src="{{ asset('assets/images/form-corner.png') }}" class="form-decor position-absolute"
                    alt="">

                <div class="title_animate mb-4 text-center position-relative animate-trigger">
                    {{-- SVG đã thêm class "draw-path" --}}
                    <svg width="574" height="97" viewBox="0 0 574 97" fill="none"
                        xmlns="http://www.w3.org/2000/svg" class="position-absolute top-0 start-50 translate-middle-x"
                        style="z-index: 0;">
                        <path d="M24.6462 72.3225C177.933 35.7379 472.664 42.9608 550.001 54.0305" stroke="#B8D3D9"
                            stroke-width="30" stroke-linecap="round" class="draw-path" />
                    </svg>
                    <h4 class="fs-48 ff-title cl-white position-relative" style="z-index: 1;">Đăng ký tư vấn miễn phí
                    </h4>
                </div>

                <div class="text-center mb-5">
                    <p class="desc fw-light cl-white fs-5">Xây dựng lộ trình học TOÀN DIỆN, bằng cách đăng ký qua form
                        hoặc liên hệ với chúng tôi qua email hoặc số điện thoại bên dưới</p>
                </div>

                <div class="sentence-body">
                    <div class="line-group mb-3">
                        <span class="text">Xin chào! Mình là</span>
                        <input type="text" name="fullname" class="input-inline" placeholder="Họ và tên"
                            value="{{ old('fullname') }}" required>
                        <span class="text">,</br></span>
                    </div>

                    <div class="line-group mb-3">
                        <span class="text">quan tâm đến khoá học</span>
                        {{-- Select dùng class tùy chỉnh "select-inline" --}}
                        <select name="course" class="select-inline course-select">
                            <option value="" disabled selected>Chọn khóa học</option>
                            @foreach ($danhSachKhoaHoc as $khoaHoc)
                                <option value="{{ $khoaHoc->tenKhoaHoc }}">{{ $khoaHoc->tenKhoaHoc }}</option>
                            @endforeach

                        </select>
                        <span class="text">!</br></span>
                    </div>
                    <div class="line-group mb-3">
                        <span class="text">tại cơ sở</span>
                        {{-- Select dùng class tùy chỉnh "select-inline" --}}
                        <select name="facility" class="select-inline course-select">
                            <option value="" disabled selected>Chọn cơ sở</option>
                            @foreach ($danhSachCoSo as $coSo)
                                <option value="{{ $coSo->tenCoSo }}"
                                    {{ old('facility') == $coSo->tenCoSo ? 'selected' : '' }}>{{ $coSo->tenCoSo }}
                                </option>
                            @endforeach

                        </select>
                        <span class="text">!</br></span>
                    </div>

                    <div class="line-group">
                        <span class="text">Liên hệ với mình qua số</span>
                        <input type="tel" name="phone" class="input-inline phone-input"
                            placeholder="Số điện thoại" value="{{ old('phone') }}">
                        <span class="text">, Hoặc Email</span>
                        <input type="email" name="email" class="input-inline email-input"
                            placeholder="Email của bạn" value="{{ old('email') }}">
                        <span class="text">. ❤️</span>
                    </div>
                </div>

                <div class="form-submit text-center mt-5">
                    <button type="submit" class="btn btn-red px-5 py-3 shadow rounded-pill fw-bold fs-5">
                        GỬI YÊU CẦU TƯ VẤN<i class="bi bi-send-fill ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
