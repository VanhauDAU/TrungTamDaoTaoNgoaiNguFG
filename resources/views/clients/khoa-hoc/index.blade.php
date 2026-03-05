@extends('layouts.client')
@section('title', 'Danh sách khóa học - Five Genius')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/khoa-hoc/khoa-hoc.css') }}">
@endsection

@section('content')
    <section class="cl-courses-section">
        <div class="cl-container">

            {{-- ── PAGE HERO ─────────────────────────────────────────── --}}
            <div class="cl-hero">
                <div class="cl-hero-text">
                    <h1>
                        <span class="cl-hero-accent">Khóa học</span>
                        @if ($activeDanhMuc)
                            <span style="font-weight:400;color:#64748b;"> / {{ $activeDanhMuc->tenDanhMuc }}</span>
                        @endif
                    </h1>
                    <p>Chọn danh mục phù hợp để tìm khóa học tốt nhất cho bạn</p>
                </div>
                <div class="cl-hero-count">
                    <span class="cl-count-num">{{ $listCourses->total() }}</span>
                    <span class="cl-count-label">Khóa học</span>
                </div>
            </div>

            <div class="cl-layout">

                {{-- ══ SIDEBAR: CÂY DANH MỤC ═══════════════════════════ --}}
                <aside class="cl-sidebar">
                    <div class="cl-sidebar-header">
                        <i class="fas fa-sitemap"></i> Danh mục
                    </div>

                    {{-- Tất cả --}}
                    <a href="{{ route('home.courses.index') }}" class="cl-cat-root {{ !$activeSlug ? 'active' : '' }}">
                        <i class="fas fa-th-large"></i>
                        <span>Tất cả khóa học</span>
                    </a>

                    {{-- Tree danh mục --}}
                    @foreach ($tree as $root)
                        @php
                            // root active nếu chọn chính nó hoặc một con của nó
                            $childSlugs = $root->children->pluck('slug')->toArray();
                            $rootActive = $activeSlug === $root->slug || in_array($activeSlug, $childSlugs);
                        @endphp

                        <div class="cl-cat-group {{ $rootActive ? 'open' : '' }}" id="cg-{{ $root->danhMucId }}">
                            <div class="cl-cat-root-row">
                                <a href="{{ route('home.courses.index', ['category' => $root->slug]) }}"
                                    class="cl-cat-root {{ $activeSlug === $root->slug ? 'active' : '' }}">
                                    <i class="fas fa-folder"></i>
                                    <span>{{ $root->tenDanhMuc }}</span>
                                </a>
                                @if ($root->children->isNotEmpty())
                                    <button class="cl-cat-toggle" onclick="toggleCat({{ $root->danhMucId }}, this)"
                                        title="{{ $rootActive ? 'Thu gọn' : 'Mở rộng' }}">
                                        <i class="fas fa-chevron-{{ $rootActive ? 'up' : 'down' }}"></i>
                                    </button>
                                @endif
                            </div>

                            @if ($root->children->isNotEmpty())
                                <div class="cl-cat-children" id="cc-{{ $root->danhMucId }}"
                                    style="{{ $rootActive ? '' : 'display:none;' }}">
                                    @foreach ($root->children as $child)
                                        <a href="{{ route('home.courses.index', ['category' => $child->slug]) }}"
                                            class="cl-cat-child {{ $activeSlug === $child->slug ? 'active' : '' }}">
                                            <i class="fas fa-circle-dot" style="font-size:.45rem;"></i>
                                            {{ $child->tenDanhMuc }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </aside>

                {{-- ══ CONTENT: GRID KHÓA HỌC ══════════════════════════ --}}
                <div class="cl-main">

                    {{-- Active filter tag --}}
                    @if ($activeSlug && $activeDanhMuc)
                        <div class="cl-active-filter">
                            <span>Đang xem: <strong>{{ $activeDanhMuc->tenDanhMuc }}</strong></span>
                            <a href="{{ route('home.courses.index') }}" class="cl-filter-clear">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    @endif

                    @if ($listCourses->isEmpty())
                        <div class="cl-empty">
                            <i class="fas fa-graduation-cap"></i>
                            <p>Không có khóa học nào trong danh mục này.</p>
                            <a href="{{ route('home.courses.index') }}" class="cl-btn-browse">
                                Xem tất cả khóa học
                            </a>
                        </div>
                    @else
                        <div class="cl-course-grid">
                            @foreach ($listCourses as $course)
                                <div class="cl-course-card">
                                    {{-- Ảnh --}}
                                    <div class="cl-card-img">
                                        <img src="{{ asset('storage/' . ($course->anhKhoaHoc ?? 'assets/client/images/default-course.jpg')) }}"
                                            alt="{{ $course->tenKhoaHoc }}" loading="lazy">
                                        <div class="cl-card-img-overlay"></div>
                                        <span class="cl-card-tag">{{ $course->danhMuc->tenDanhMuc ?? '—' }}</span>
                                    </div>

                                    {{-- Nội dung --}}
                                    <div class="cl-card-body">
                                        <h3 class="cl-card-title">{{ $course->tenKhoaHoc }}</h3>
                                        <p class="cl-card-desc">{{ Str::limit($course->moTa, 100) }}</p>

                                        <div class="cl-card-meta">
                                            <span><i class="fas fa-layer-group"></i> {{ $course->lopHoc->count() }}
                                                lớp</span>
                                            <span><i class="fas fa-signal"></i>
                                                {{ $course->capDo ?? 'Mọi trình độ' }}</span>
                                        </div>
                                    </div>

                                    <div class="cl-card-footer">
                                        <a href="{{ route('home.courses.show', $course->slug) }}" class="cl-card-btn">
                                            Xem chi tiết <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        <div class="cl-pagination">
                            {{ $listCourses->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
    <script>
        function toggleCat(id, btn) {
            const panel = document.getElementById('cc-' + id);
            const icon = btn.querySelector('i');
            const group = document.getElementById('cg-' + id);
            const open = panel.style.display === 'none' || panel.style.display === '';
            if (panel.style.display === 'none') {
                panel.style.display = '';
                icon.className = 'fas fa-chevron-up';
                group.classList.add('open');
            } else {
                panel.style.display = 'none';
                icon.className = 'fas fa-chevron-down';
                group.classList.remove('open');
            }
        }
    </script>
@endsection
