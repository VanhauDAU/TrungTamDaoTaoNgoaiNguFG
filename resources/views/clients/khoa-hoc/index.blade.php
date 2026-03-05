@extends('layouts.client')
@section('title', 'Danh sách khóa học - Five Genius')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/khoa-hoc/khoa-hoc.css') }}">
@endsection

@section('content')


    <section class="cl-courses-section">
        <div class="cl-container">

            {{-- ── HERO ─────────────────────────────────────────────── --}}
            <div class="cl-hero">
                <div class="cl-hero-text">
                    <h1>
                        <span class="cl-hero-accent">Khóa học</span>
                        @if ($activeDanhMuc)
                            &nbsp;/ {{ $activeDanhMuc->tenDanhMuc }}
                        @endif
                    </h1>
                    <p class="cl-hero-sub">Chọn danh mục hoặc tìm kiếm để tìm khóa học phù hợp nhất</p>
                </div>
                <div class="cl-hero-count">
                    <span class="cl-count-num">{{ $listCourses->total() }}</span>
                    <span class="cl-count-label">Khóa học</span>
                </div>
            </div>

            {{-- ── FILTER BAR ───────────────────────────────────────── --}}
            <form action="{{ route('home.courses.index') }}" method="GET" class="cl-filter-bar" id="cl-filter-form">
                @if ($activeSlug)
                    <input type="hidden" name="category" value="{{ $activeSlug }}">
                @endif

                {{-- Tìm kiếm --}}
                <div class="cl-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" name="q" class="cl-search-input" placeholder="Tìm tên khóa học..."
                        value="{{ $searchQ }}" autocomplete="off">
                </div>

                {{-- Sắp xếp --}}
                <label>Sắp xếp</label>
                <select name="sort" class="cl-filter-select" onchange="this.form.submit()">
                    <option value="newest" {{ $sortBy === 'newest' ? 'selected' : '' }}>Mới nhất</option>
                    <option value="name_asc" {{ $sortBy === 'name_asc' ? 'selected' : '' }}>Tên A → Z</option>
                    <option value="name_desc" {{ $sortBy === 'name_desc' ? 'selected' : '' }}>Tên Z → A</option>
                </select>

                <button type="submit" class="cl-filter-btn cl-filter-btn-primary">
                    <i class="fas fa-search"></i> Tìm
                </button>
                <a href="{{ route('home.courses.index') }}" class="cl-filter-btn cl-filter-btn-reset">
                    <i class="fas fa-times"></i> Đặt lại
                </a>
            </form>

            <div class="cl-layout">

                {{-- ══ SIDEBAR: CÂY DANH MỤC ĐỆ QUY ════════════════════ --}}
                <aside class="cl-sidebar">
                    <div class="cl-sidebar-header">
                        <i class="fas fa-sitemap"></i> Danh mục
                    </div>

                    {{-- Tất cả --}}
                    <a href="{{ route('home.courses.index', array_filter(['q' => $searchQ, 'sort' => $sortBy !== 'newest' ? $sortBy : null])) }}"
                        class="cl-cat-all {{ !$activeSlug ? 'active' : '' }}">
                        <i class="fas fa-th-large" style="color:#27C4B5"></i>
                        Tất cả khóa học
                    </a>

                    {{-- Tree đệ quy --}}
                    @foreach ($tree as $root)
                        @php $rootActive = in_array($root->danhMucId, $activeIds); @endphp
                        <div class="cl-cat-group" id="cg-{{ $root->danhMucId }}">
                            <div class="cl-cat-root-row">
                                <a href="{{ route('home.courses.index', array_filter(['category' => $root->slug, 'q' => $searchQ, 'sort' => $sortBy !== 'newest' ? $sortBy : null])) }}"
                                    class="cl-cat-root-link {{ $activeSlug === $root->slug ? 'active' : '' }}">
                                    <span class="cl-cat-icon"><i class="fas fa-folder"></i></span>
                                    {{ $root->tenDanhMuc }}
                                </a>
                                @if ($root->childrenRecursive->isNotEmpty())
                                    <button type="button" class="cl-cat-toggle {{ $rootActive ? 'open' : '' }}"
                                        onclick="toggleCat({{ $root->danhMucId }}, this)">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                @endif
                            </div>

                            @if ($root->childrenRecursive->isNotEmpty())
                                <div class="cl-cat-children" id="cc-{{ $root->danhMucId }}"
                                    style="{{ $rootActive ? '' : 'display:none' }}">
                                    @foreach ($root->childrenRecursive as $child)
                                        @include('clients.khoa-hoc._cat-tree', [
                                            'node' => $child,
                                            'depth' => 1,
                                            'activeSlug' => $activeSlug,
                                            'searchQ' => $searchQ,
                                            'sortBy' => $sortBy,
                                        ])
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </aside>

                {{-- ══ MAIN CONTENT ════════════════════════════════════ --}}
                <div class="cl-main">

                    {{-- Active filter display --}}
                    @if (($activeSlug && $activeDanhMuc) || $searchQ)
                        <div class="cl-active-filter">
                            <i class="fas fa-filter" style="color:#27C4B5"></i>
                            <span>
                                @if ($activeDanhMuc)
                                    Danh mục: <strong>{{ $activeDanhMuc->tenDanhMuc }}</strong>
                                @endif
                                @if ($searchQ)
                                    &nbsp;·&nbsp; Tìm: &laquo;<strong>{{ $searchQ }}</strong>&raquo;
                                @endif
                                &nbsp;— <em style="color:#6b7280">{{ $listCourses->total() }} kết quả</em>
                            </span>
                            <a href="{{ route('home.courses.index') }}" class="cl-filter-clear" title="Xóa bộ lọc">
                                <i class="fas fa-times-circle"></i>
                            </a>
                        </div>
                    @endif

                    @if ($listCourses->isEmpty())
                        <div class="cl-empty">
                            <i class="fas fa-graduation-cap"></i>
                            <p>Không tìm thấy khóa học nào phù hợp.</p>
                            <a href="{{ route('home.courses.index') }}" class="cl-btn-browse">
                                Xem tất cả khóa học
                            </a>
                        </div>
                    @else
                        <div class="cl-course-grid">
                            @foreach ($listCourses as $course)
                                <div class="cl-course-card">
                                    <div class="cl-card-img">
                                        <img src="{{ asset('storage/' . ($course->anhKhoaHoc ?? 'assets/client/images/default-course.jpg')) }}"
                                            alt="{{ $course->tenKhoaHoc }}" loading="lazy">
                                        <div class="cl-card-img-overlay"></div>
                                        <span class="cl-card-tag">{{ $course->danhMuc->tenDanhMuc ?? '—' }}</span>
                                    </div>
                                    <div class="cl-card-body">
                                        <h3 class="cl-card-title">{{ $course->tenKhoaHoc }}</h3>
                                        <p class="cl-card-desc">{{ Str::limit($course->moTa, 100) }}</p>
                                        <div class="cl-card-meta">
                                            <span><i class="fas fa-layer-group"></i>{{ $course->lopHoc->count() }}
                                                lớp</span>
                                            <span><i
                                                    class="fas fa-signal"></i>{{ $course->capDo ?? 'Mọi trình độ' }}</span>
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
            const isHidden = panel.style.display === 'none' || panel.style.display === '';
            panel.style.display = isHidden ? '' : 'none';
            btn.classList.toggle('open', isHidden);
        }
        document.querySelector('.cl-search-input')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') e.target.closest('form').submit();
        });
    </script>
@endsection
