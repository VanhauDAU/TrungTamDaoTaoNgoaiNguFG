@extends('layouts.client')
@section('title', 'Hồ sơ học viên')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/account.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/breadcrumb.css') }}">
@endsection

@section('content')
    <section class="account-page">
        <div class="custom-container">
            <div class="row g-4">
                {{-- SIDEBAR --}}
                @include('components.client.account-sidebar')

                {{-- MAIN CONTENT --}}
                <div class="col-lg-9">
                    <div class="account-content">
                        {{-- Breadcrumb --}}
                        <x-client.account-breadcrumb :items="[
                            ['label' => 'Trang chủ', 'url' => route('home.index'), 'icon' => 'fas fa-home'],
                            ['label' => 'Tài khoản', 'url' => route('home.student.index')],
                            ['label' => 'Thông tin cá nhân'],
                        ]" />

                        <h2 class="content-title">
                            <i class="fas fa-edit me-2"></i> Thông tin cá nhân
                        </h2>

                        <form action="" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-4">
                                {{-- Avatar Upload (Optional UI) --}}
                                <div class="col-12 mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="me-4">
                                            @if (Auth::user()->hoSoNguoiDung && Auth::user()->hoSoNguoiDung->anhDaiDien)
                                                <img src="{{ asset('storage/avatars/' . Auth::user()->hoSoNguoiDung->anhDaiDien) }}"
                                                    class="rounded-circle" width="100" height="100"
                                                    style="object-fit: cover; border: 3px solid #f0f0f0;">
                                            @else
                                                <img src="{{ asset('assets/images/user-default.png') }}"
                                                    class="rounded-circle" width="100" height="100"
                                                    style="object-fit: cover;">
                                            @endif
                                        </div>
                                        <div>
                                            <h5 class="fw-bold mb-1">Ảnh đại diện</h5>
                                            <p class="text-muted small mb-3">Chấp nhận file JPG, PNG hoặc GIF. Tối đa 2MB.
                                            </p>
                                            <input type="file" class="form-control form-control-sm" name="anhDaiDien">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label-custom">Họ và tên</label>
                                    <input type="text" class="form-control form-control-custom"
                                        value="{{ Auth::user()->hoSoNguoiDung->hoTen ?? '' }}"
                                        placeholder="Nhập họ tên của bạn">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-custom">Email đăng nhập</label>
                                    <input type="email" class="form-control form-control-custom bg-light"
                                        value="{{ Auth::user()->email }}" disabled readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-custom">Số điện thoại</label>
                                    <input type="text" class="form-control form-control-custom"
                                        value="{{ Auth::user()->hoSoNguoiDung->soDienThoai ?? '' }}"
                                        placeholder="Nhập số điện thoại">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-custom">Ngày sinh</label>
                                    <input type="date" class="form-control form-control-custom"
                                        value="{{ Auth::user()->hoSoNguoiDung->ngaySinh ?? '' }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label-custom">Địa chỉ</label>
                                    <input type="text" class="form-control form-control-custom"
                                        value="{{ Auth::user()->hoSoNguoiDung->diaChi ?? '' }}"
                                        placeholder="Nhập địa chỉ liên hệ">
                                </div>
                                <div class="col-12">
                                    <label class="form-label-custom">Giới thiệu bản thân</label>
                                    <textarea class="form-control form-control-custom" rows="4" placeholder="Viết đôi dòng về bạn...">{{ Auth::user()->hoSoNguoiDung->gioiThieu ?? '' }}</textarea>
                                </div>

                                <div class="col-12 text-end mt-4">
                                    <button type="submit" class="btn btn-update">
                                        <i class="fas fa-save me-2"></i> Lưu thay đổi
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
