@extends('layouts.client')

@section('title', $course->tenKhoaHoc . ' - Five Genius')

@section('content')
    <section class="course-detail-page pt-100 pb-100">
        <div class="custom-container">
            <div class="row mb-5 align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="course-thumb-wrapper">
                        <img src="{{ asset('storage/' . $course->anhKhoaHoc) }}" alt="{{ $course->tenKhoaHoc }}"
                            class="img-fluid rounded-30 shadow-lg"
                            onerror="this.src='https://theforumcenter.com/wp-content/uploads/2025/05/brand-500x400.jpg'">
                    </div>
                </div>
                <div class="col-lg-6 ps-lg-5">
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#" class="cl-green">Khóa học</a></li>
                            <li class="breadcrumb-item active">{{ $course->loaiKhoaHoc->tenLoai }}</li>
                        </ol>
                    </nav>
                    <h1 class="ff-title cl-green fs-48 mb-3">{{ $course->tenKhoaHoc }}</h1>
                    <div class="course-summary cl-gray mb-4">
                        {!! nl2br(e($course->moTa)) !!}
                    </div>
                    <div class="course-quick-stats d-flex gap-4">
                        <div class="stat-item">
                            <span class="d-block cl-green fw-bold fs-24">{{ $course->lopHoc->count() }}</span>
                            <span class="fs-12 cl-gray text-uppercase">Lớp đang mở</span>
                        </div>
                        <div class="stat-item border-start ps-4">
                            <span class="d-block cl-green fw-bold fs-24">{{ $course->lopHoc->sum('soBuoiDuKien') }}</span>
                            <span class="fs-12 cl-gray text-uppercase">Tổng số buổi</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="classes-container mt-5">
                <div class="title_animate mb-5">
                    <x-svg.title-accent />
                    <h3 class="ff-title cl-green mb-0">Lịch khai giảng dự kiến</h3>
                </div>
                <div class="row g-4">
                    @forelse ($course->lopHoc as $lop)
                        <div class="col-lg-4 col-md-6">
                            <div class="class-card h-100">
                                <div class="class-header d-flex justify-content-between align-items-center mb-3">
                                    @php
                                        $statusLabels = [
                                            '0' => 'Sắp mở',
                                            '1' => 'Đang học',
                                            '2' => 'Kết thúc',
                                            '3' => 'Hủy',
                                        ];
                                        $statusClasses = [
                                            '0' => 'bg-info',
                                            '1' => 'bg-success',
                                            '2' => 'bg-secondary',
                                            '3' => 'bg-danger',
                                        ];
                                    @endphp
                                    <span
                                        class="badge {{ $statusClasses[$lop->trangThai] }}">{{ $statusLabels[$lop->trangThai] }}</span>
                                    <span class="class-code fw-bold cl-green">#{{ $lop->lopHocId }}</span>
                                </div>

                                <h4 class="cl-green fw-bold mb-3">{{ $lop->tenLopHoc }}</h4>

                                <ul class="class-details list-unstyled mb-4">
                                    <li><i class="fas fa-calendar-alt"></i> <strong>Khai giảng:</strong>
                                        {{ \Carbon\Carbon::parse($lop->ngayBatDau)->format('d/m/Y') }}</li>
                                    <li><i class="fas fa-clock"></i> <strong>Thời lượng:</strong> {{ $lop->soBuoiDuKien }}
                                        buổi</li>
                                    <li><i class="fas fa-users"></i> <strong>Sĩ số:</strong> Tối đa
                                        {{ $lop->soHocVienToiDa }} học viên</li>
                                    <li><i class="fas fa-chalkboard-teacher"></i> <strong>Giáo viên:</strong>
                                        {{ $lop->giaoVien->hoTen ?? 'Đang cập nhật' }}</li>
                                </ul>

                                <div class="class-footer pt-3 border-top d-flex justify-content-between align-items-center">
                                    @php
                                        // Lấy học phí đầu tiên của khóa học (vì HocPhi liên kết với KhoaHoc, không phải LopHoc)
                                        $hocPhi = $course->hocPhis->first();
                                    @endphp 
                                    <div class="price">
                                        <span class="fs-12 cl-gray d-block">Học phí</span>
                                        @if ($hocPhi)
                                            <span class="fw-bold cl-red fs-20">{{ number_format($hocPhi->donGia) }}đ</span>
                                            <small class="d-block cl-gray">({{ $hocPhi->soBuoi }} buổi)</small>
                                        @else
                                            <span class="fw-bold cl-gray fs-20">Liên hệ</span>
                                        @endif
                                    </div>
                                    <a href="#" class="btn btn-primary-genius btn-sm px-4 rounded-pill">Đăng ký</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5">
                            <img src="https://theforumcenter.com/wp-content/themes/the-forum/assets/images/cookieicon.svg"
                                width="80" class="mb-3 opacity-50">
                            <p class="cl-gray">Hiện chưa có lớp học nào phù hợp cho khóa học này.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
@endsection
