@extends('layouts.client')

@section('title', 'Blog - Trung tâm Anh ngữ Five Genius')
@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/blog.css') }}">
@endsection
@section('content')
    <div class="blog_page pt-80">
        <div class="container">
            <div class="row justify-content-between align-items-end py-4">
                <div class="col-lg-6">
                    <div class="title_page">
                        <div class="title_animate mb-4 mb-lg-0">

                            <h1 class="fs-48 ff-title cl-green mb-0">Chia sẻ kiến thức, tin tức, sự kiện</h1>
                            <div class="title_icon">
                                <img src="https://theforumcenter.com/wp-content/themes/the-forum/assets/images/plane.svg"
                                    class="img-fluid" alt="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="desc fw-light">
                        Cập nhật xu hướng thi cử, mẹo học tập hiệu quả, sự kiện quan trọng và tin tức mới nhất về IELTS, SAT
                        và giáo dục. </div>
                </div>
            </div>
            <div class="mt-60">
                <div class="row align-items-center">
                    <div class="col-lg-4 order-lg-1">
                        <div class="search_post mb-3 mb-lg-0">
                            <form action="{{ route('home.blog.index') }}" method="get">
                                {{-- Giữ lại danh mục đang lọc --}}
                                @if(request('category'))
                                    <input type="hidden" name="category" value="{{ request('category') }}">
                                @endif
                                @if(request('sort'))
                                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                                @endif
                                <div class="input-group">
                                    <input type="text" name="s" value="{{ request('s') }}" class="form-control"
                                        placeholder="Tìm kiếm bài viết...">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-8 order-lg-0 py-4">
                        {{-- Thanh danh mục cuộn ngang --}}
                        <ul class="cate_menu">
                            <li class="{{ !request('category') ? 'active' : '' }}">
                                <a href="{{ route('home.blog.index', array_filter(['s' => request('s'), 'sort' => request('sort')])) }}">Tất cả</a>
                            </li>
                            @foreach ($categories as $cate)
                                <li class="{{ request('category') == $cate->slug ? 'active' : '' }}">
                                    <a href="{{ route('home.blog.index', array_filter(['category' => $cate->slug, 's' => request('s'), 'sort' => request('sort')])) }}">
                                        {{ $cate->tenDanhMuc }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- Thanh sắp xếp + thông tin kết quả --}}
                <div class="blog-filter-bar">
                    <div class="blog-filter-info">
                        <span class="blog-filter-count">
                            <i class="fas fa-file-alt me-1"></i>
                            <strong>{{ $blogs->total() }}</strong> bài viết
                            @if(request('s'))
                                cho "<em>{{ request('s') }}</em>"
                            @endif
                            @if(request('category'))
                                @php $activeCat = $categories->firstWhere('slug', request('category')); @endphp
                                @if($activeCat)
                                    trong <strong>{{ $activeCat->tenDanhMuc }}</strong>
                                @endif
                            @endif
                        </span>
                        @if(request('s') || request('category') || request('sort'))
                            <a href="{{ route('home.blog.index') }}" class="blog-filter-clear">
                                <i class="fas fa-times me-1"></i>Xóa bộ lọc
                            </a>
                        @endif
                    </div>
                    <div class="blog-sort-group">
                        <span class="blog-sort-label"><i class="fas fa-sort-amount-down me-1"></i>Sắp xếp:</span>
                        @php
                            $currentSort = request('sort', 'latest');
                            $sortOptions = [
                                'latest'  => 'Mới nhất',
                                'oldest'  => 'Cũ nhất',
                                'popular' => 'Xem nhiều nhất',
                                'az'      => 'Tên A → Z',
                            ];
                            $baseParams = array_filter(['category' => request('category'), 's' => request('s')]);
                        @endphp
                        @foreach($sortOptions as $key => $label)
                            <a href="{{ route('home.blog.index', array_merge($baseParams, ['sort' => $key])) }}"
                               class="blog-sort-btn {{ $currentSort === $key ? 'active' : '' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="news_wrapper mt-4">
                <div class="row">
                    @foreach ($blogs as $blog)
                        <div class="col-lg-4">
                            <div class="post_item">
                                <figure>
                                    <a href="{{ route('home.blog.show', ['slug' => $blog->slug]) }}">
                                        <img width="600" height="450"
                                            src="{{ asset('storage/' . $blog->anhDaiDien ?? '') }}"
                                            class="img-fluid wp-post-image" alt="" decoding="async"
                                            fetchpriority="high"> </a>
                                </figure>
                                <div class="meta_post">
                                    <div class="row align-items-center"> {{-- Thêm align-items-center để căn hàng chuẩn --}}
                                        <div class="col">
                                            <ul class="post_tag fs-12">
                                                <li>
                                                    <a href="{{ route('home.blog.index', ['category' => $cate->slug]) }}">
                                                        {{ $cate->tenDanhMuc }}
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-auto d-flex align-items-center"> {{-- Sử dụng flex để các icon thẳng hàng --}}
                                            <div class="post_date fs-12 me-3">
                                                <i class="far fa-calendar-alt me-1"></i>
                                                {{ $blog->created_at->format('d/m/Y') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="title_wrapper mb-3">
                                        <h4 class="fs-24 ff-title post_title mb-0"><a
                                                href="{{ route('home.blog.show', ['slug' => $blog->slug]) }}">{{ $blog->tieuDe }}</a>
                                        </h4>
                                    </div>
                                    <div class="post_excerpt fw-light">
                                        <p>{{ $blog->tomTat }}</p>
                                    </div>
                                    <div class="post_view fs-12">
                                        <i class="far fa-eye me-1"></i>
                                        {{ number_format($blog->luotXem ?? 0, 0, ',', '.') }} lượt xem
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="j_paging">
                    {{-- Hiển thị bộ nút phân trang của Laravel --}}
                    {{ $blogs->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
    <x-client.register-advice />
@endsection
@section('script')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // 1. Hiệu ứng vẽ nét mực tiêu đề chính
            const mainTitlePath = document.querySelector('.title-style-1');
            if (mainTitlePath) {
                const length = mainTitlePath.getTotalLength();
                mainTitlePath.style.strokeDasharray = length;
                mainTitlePath.style.strokeDashoffset = length;

                // Chạy animation sau khi trang load
                setTimeout(() => {
                    mainTitlePath.style.transition = "stroke-dashoffset 2s ease-in-out";
                    mainTitlePath.style.strokeDashoffset = "0";
                }, 300);
            }

            // 2. Tự động cuộn thanh danh mục đến mục đang Active
            const activeCate = document.querySelector('.cate_menu li.active');
            const cateMenu = document.querySelector('.cate_menu');
            if (activeCate && cateMenu) {
                cateMenu.scrollLeft = activeCate.offsetLeft - 20;
            }

            // 3. Animation Fade In cho các bài viết khi cuộn trang
            const observerOptions = {
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = "1";
                        entry.target.style.transform = "translateY(0)";
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.post_item').forEach(el => {
                el.style.opacity = "0";
                el.style.transform = "translateY(30px)";
                el.style.transition = "0.6s ease-out";
                observer.observe(el);
            });
        });
    </script>
@endsection
