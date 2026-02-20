@extends('layouts.client')
@section('title', 'Lớp học của tôi')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/account.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/my-classes.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/breadcrumb.css') }}">
@endsection

@section('content')
    <section class="account-page">
        <div class="custom-container">
            <div class="row">
                @include('components.client.account-sidebar')

                <div class="col-lg-9">
                    <div class="account-content">
                        {{-- Breadcrumb --}}
                        <x-client.account-breadcrumb :items="[
                            ['label' => 'Trang chủ', 'url' => route('home.index'), 'icon' => 'fas fa-home'],
                            ['label' => 'Tài khoản', 'url' => route('home.student.index')],
                            ['label' => 'Lớp học của tôi'],
                        ]" />

                        <div class="content-header">
                            <h2 class="page-title">
                                <i class="fas fa-graduation-cap me-2"></i>Lớp học của tôi
                            </h2>
                            <p class="page-subtitle">Quản lý và theo dõi các lớp học bạn đã đăng ký</p>
                        </div>

                        @if ($classes->count() > 0)
                            <div class="classes-grid">
                                @foreach ($classes as $registration)
                                    @php
                                        $class = $registration->lopHoc;
                                        $course = $class->khoaHoc;
                                        $instructor = $class->taiKhoan->hoSoNguoiDung ?? null;

                                        // Trạng thái đăng ký
                                        $regStatusClass = '';
                                        $regStatusText = '';
                                        $regStatusIcon = '';

                                        if ($registration->trangThai == 1) {
                                            $regStatusClass = 'status-pending';
                                            $regStatusText = 'Chờ thanh toán';
                                            $regStatusIcon = 'fas fa-clock';
                                        } else {
                                            $regStatusClass = 'status-confirmed';
                                            $regStatusText = 'Đã xác nhận';
                                            $regStatusIcon = 'fas fa-check-circle';
                                        }

                                        // Trạng thái lớp học
                                        $classStatusClass = '';
                                        $classStatusText = '';

                                        if ($class->trangThai == 0) {
                                            $classStatusClass = 'class-upcoming';
                                            $classStatusText = 'Sắp khai giảng';
                                        } elseif ($class->trangThai == 1) {
                                            $classStatusClass = 'class-open';
                                            $classStatusText = 'Đang tuyển sinh';
                                        } elseif ($class->trangThai == 4) {
                                            $classStatusClass = 'class-active';
                                            $classStatusText = 'Đang học';
                                        } else {
                                            $classStatusClass = 'class-closed';
                                            $classStatusText = 'Đã kết thúc';
                                        }

                                        // Lịch học
                                        $schedule = $class->buoiHocs->groupBy('ngayHoc')->map(function ($sessions) {
                                            return $sessions->first()->caHoc;
                                        });
                                    @endphp

                                    <div class="class-card">
                                        <div class="class-header">
                                            <div class="class-badges">
                                                <span class="badge {{ $regStatusClass }}">
                                                    <i class="{{ $regStatusIcon }}"></i> {{ $regStatusText }}
                                                </span>
                                                <span class="badge {{ $classStatusClass }}">
                                                    {{ $classStatusText }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="class-body">
                                            <h3 class="class-name">{{ $class->tenLopHoc }}</h3>
                                            <p class="course-name">
                                                <i class="fas fa-book text-primary"></i> {{ $course->tenKhoaHoc }}
                                            </p>

                                            <div class="class-info">
                                                @if ($instructor)
                                                    <div class="info-item">
                                                        <i class="fas fa-chalkboard-teacher"></i>
                                                        <span>GV: {{ $instructor->hoTen }}</span>
                                                    </div>
                                                @endif

                                                <div class="info-item">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <span>{{ $class->coSo->tenCoSo }}</span>
                                                </div>

                                                <div class="info-item">
                                                    <i class="far fa-calendar"></i>
                                                    <span>
                                                        {{ \Carbon\Carbon::parse($class->ngayBatDau)->format('d/m/Y') }}
                                                        -
                                                        {{ \Carbon\Carbon::parse($class->ngayKetThuc)->format('d/m/Y') }}
                                                    </span>
                                                </div>

                                                @if ($schedule->count() > 0)
                                                    <div class="info-item">
                                                        <i class="far fa-clock"></i>
                                                        <span>
                                                            @php
                                                                $firstSession = $schedule->first();
                                                            @endphp
                                                            {{ date('H:i', strtotime($firstSession->gioBatDau)) }} -
                                                            {{ date('H:i', strtotime($firstSession->gioKetThuc)) }}
                                                        </span>
                                                    </div>
                                                @endif

                                                <div class="info-item">
                                                    <i class="fas fa-users"></i>
                                                    <span>Đăng ký:
                                                        {{ \Carbon\Carbon::parse($registration->ngayDangKy)->format('d/m/Y') }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="class-footer">
                                            <a href="{{ route('home.classes.show', ['slug' => $course->slug, 'slugLopHoc' => $class->slug]) }}"
                                                class="btn btn-detail">
                                                <i class="fas fa-eye"></i> Xem chi tiết
                                            </a>

                                            <a href="#" class="btn btn-materials">
                                                <i class="fas fa-file-alt"></i> Tài liệu
                                            </a>

                                            @if ($registration->trangThai == 1)
                                                <a href="{{ route('home.student.invoices') }}" class="btn btn-pay">
                                                    <i class="fas fa-credit-card"></i> Thanh toán
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="fas fa-graduation-cap fa-4x"></i>
                                <h4>Bạn chưa đăng ký lớp học nào</h4>
                                <p>Khám phá các khóa học và đăng ký ngay để bắt đầu học tập</p>
                                <a href="{{ route('home.courses.index') }}" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i> Xem khóa học
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
