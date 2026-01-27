@extends('layouts.client')

@section('title', 'Liên hệ - Trung tâm Anh ngữ Five Genius')
@section('content')
    <div class="contact_page py-80">
        <div class="container">
            <div class="title_animate mb-4 pe-5 mb-lg-5">
                <svg width="431" height="119" viewBox="0 0 431 119" fill="none" xmlns="http://www.w3.org/2000/svg"
                    class="active">
                    <g opacity="0.5">
                        <path
                            d="M26.4044 50.1739C143.289 11.2828 368.028 18.9611 426.999 30.7288C333.952 35.1662 60.1018 98.4423 60.1018 98.4423C60.1018 98.4423 220.66 52.5281 320.761 91.3785"
                            stroke="#B8D3D9" stroke-width="40.8987" stroke-linecap="square" class="title-style-1"></path>
                    </g>
                </svg>
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
                        <img src="https://theforumcenter.com/wp-content/themes/the-forum/assets/images/prev.png" alt="Prev">
                    </button>
                    <button class="nav-btn btn-next">
                        <img src="https://theforumcenter.com/wp-content/themes/the-forum/assets/images/next.png" alt="Next">
                    </button>
                </div>
                <div class="map_main_slider">
                    {{-- ITEM 1: Cơ sở Vũng Tàu --}}
                    @foreach($coSoDaoTao as $coSo)
                        <div class="map_slide_item">
                            <div class="iframe_box">
                                <iframe src="{{ $coSo->banDoGoogle }}" 
                                    width="100%" height="500" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
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
