@extends('layouts.client')
@section('title', 'Đổi mật khẩu')

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
                            ['label' => 'Đổi mật khẩu'],
                        ]" />

                        <h2 class="content-title">
                            <i class="fas fa-lock me-2"></i> Đổi mật khẩu
                        </h2>

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('home.student.update-password') }}" method="POST">
                            @csrf
                            <div class="row g-4">
                                <div class="col-12">
                                    <div class="alert alert-info border-0 bg-light">
                                        <i class="fas fa-info-circle me-2 text-primary"></i>
                                        Để bảo mật tài khoản, vui lòng không chia sẻ mật khẩu cho người khác. Mật khẩu mới
                                        phải có ít nhất 8 ký tự.
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label-custom">Mật khẩu hiện tại</label>
                                    <input type="password"
                                        class="form-control form-control-custom @error('current_password') is-invalid @enderror"
                                        name="current_password" placeholder="Nhập mật khẩu hiện tại">
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label-custom">Mật khẩu mới</label>
                                    <input type="password"
                                        class="form-control form-control-custom @error('new_password') is-invalid @enderror"
                                        name="new_password" placeholder="Nhập mật khẩu mới">
                                    @error('new_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label-custom">Xác nhận mật khẩu mới</label>
                                    <input type="password" class="form-control form-control-custom"
                                        name="new_password_confirmation" placeholder="Nhập lại mật khẩu mới">
                                </div>

                                <div class="col-12 text-end mt-4">
                                    <button type="submit" class="btn btn-update">
                                        <i class="fas fa-save me-2"></i> Cập nhật mật khẩu
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
