@extends('layouts.client')

@section('title', 'Khóa học - Trung tâm Anh ngữ Five Genius')
@section('content')
    <section class="courses_section">
        <div class="container">
            <div role="main" id="yui_3_18_1_1_1769760065435_13"><span id="maincontent"></span>
                <div class="ielts-test-header" id="yui_3_18_1_1_1769760065435_12">
                    <div class="heading_animate">
                        <svg width="435" height="47" viewBox="0 0 455 47" fill="none"
                            xmlns="http://www.w3.org/2000/svg" class="active">
                            <path id="heading-svg-path" d="M21.6835 22.4956C82.2331 20.6507 312.987 19.4468 433 25.5443"
                                stroke="#B8D3D9" stroke-width="40.8987" stroke-linecap="square"
                                style="opacity: 0.5; stroke-dashoffset: 0px;"></path>
                        </svg>
                        <div class="heading_content">
                            <x-svg.title-accent class="active" />
                            <h1 class="fs-48 ff-title cl-green mb-0" style="z-index: 2">Danh sách khóa học</h1>
                            <div class="heading_icon">
                                <img src="	https://ieltstest.theforumcenter.com/local/ieltstest/pix/heading-icon.png"
                                    class="img-fluid" alt="heading icon">
                            </div>
                        </div>
                    </div>
                    <div class="type-courses-tabs"><a
                            href="https://ieltstest.theforumcenter.com/local/ieltstest/index.php?tab=all&amp;wp=true"
                            class="active">Tất cả</a>
                        @foreach ($listTypeCourses as $typeCourse)
                            <a
                                href="https://ieltstest.theforumcenter.com/local/ieltstest/index.php?tab=reading&amp;wp=true">{{ $typeCourse->tenLoai }}</a>
                        @endforeach
                    </div>
                    <hr class="mobile-ielts-divider">
                    <form action="https://ieltstest.theforumcenter.com/local/ieltstest/index.php" method="get"
                        class="ielts-search-container" id="yui_3_18_1_1_1769760065435_16">
                        <div class="ielts-search" id="yui_3_18_1_1_1769760065435_15">
                            <input type="text" name="search" placeholder="Nhập nội dung tìm kiếm" value=""
                                data-auto-search="true" id="yui_3_18_1_1_1769760065435_14">
                            <button type="submit" class="search-button"><img src="pix/search-icon.png" class="img-fluid"
                                    alt="search icon"></button>
                            <span class="ielts-search-loading" style="display: none;">Đang tìm kiếm...</span>
                        </div>
                        <input type="hidden" name="tab" value="all">
                        <div class="ielts-filters" id="yui_3_18_1_1_1769760065435_18">
                            <select name="year" onchange="this.form.submit()">
                                <option value="0">Năm</option>
                                <option value="2021">2021</option>
                                <option value="2022">2022</option>
                                <option value="2023">2023</option>
                                <option value="2024">2024</option>
                                <option value="2025">2025</option>
                                <option value="2026">2026</option>
                                <option value="2027">2027</option>
                                <option value="2028">2028</option>
                                <option value="2029">2029</option>
                                <option value="2030">2030</option>
                                <option value="2031">2031</option>
                            </select>
                            <select name="sort" onchange="this.form.submit()" id="yui_3_18_1_1_1769760065435_17">
                                <option value="default" selected="">Sắp xếp</option>
                                <option value="newest">Mới nhất</option>
                                <option value="oldest">Cũ nhất</option>
                            </select>
                        </div>
                        <input type="hidden" name="wp" value="true">
                    </form>
                </div>
                <div class="list-courses-content" id="yui_3_18_1_1_1769760065435_22">
                    <div class="list-courses-grid" id="yui_3_18_1_1_1769760065435_21">
                        <div class="list-courses-row" id="yui_3_18_1_1_1769760065435_20">
                            @foreach ($listCourses as $course)
                                <div class="course-item">
                                    <!-- Course Image with Overlay -->
                                    <div class="course-image">

                                        <img src="{{ asset('storage/' . $course->hinhAnh) ?? asset('assets/client/images/default-course.jpg') }}"
                                            alt="{{ $course->tenKhoaHoc }}">
                                        <div class="course-image-overlay"></div>
                                        <div class="course-tag-floating">
                                            <span>{{ $course->loaiKhoaHoc->tenLoai }}</span>
                                        </div>
                                    </div>

                                    <!-- Course Content -->
                                    <div class="course-content">
                                        <div class="course-header">
                                            <h3 class="course-title">{{ $course->tenKhoaHoc }}</h3>
                                        </div>

                                        <div class="course-description">
                                            <p>{{ Str::limit($course->moTa, 120) }}</p>
                                        </div>

                                        <div class="course-meta">
                                            <div class="meta-item">
                                                <i class="fas fa-book-open"></i>
                                                <span>{{ $course->lopHoc->count() }} lớp học</span>
                                            </div>
                                            <div class="meta-item">
                                                <i class="fas fa-clock"></i>
                                                <span>12 giờ</span>
                                            </div>
                                            <div class="meta-item">
                                                <i class="fas fa-users"></i>
                                                <span>{{ rand(50, 500) }} học viên</span>
                                            </div>
                                        </div>

                                        <div class="course-footer">
                                            <a href="#" class="course-button">
                                                Xem chi tiết
                                                <i class="fas fa-arrow-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="j_paging">
                        {{-- Hiển thị bộ nút phân trang của Laravel --}}
                        {{ $listCourses->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
        <x-client.register-advice />
    @endsection
