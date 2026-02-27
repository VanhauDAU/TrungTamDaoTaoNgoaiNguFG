@extends('layouts.client')
@section('title', $blog->tieuDe . ' - Blog')

@section('stylesheet')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/blog.css') }}">
@endsection

@section('content')
    {{-- ═══ HERO BANNER ═══ --}}
    <div class="blog-detail-hero">
        <div class="blog-detail-hero-overlay"></div>
        <div class="blog-detail-hero-img"
            style="background-image:url('{{ $blog->anhDaiDien ? asset('storage/' . $blog->anhDaiDien) : asset('assets/images/default-course.jpg') }}')">
        </div>
        <div class="container position-relative" style="z-index:2">
            <div class="blog-detail-hero-content">
                <div class="blog-detail-categories mb-3">
                    @foreach ($blog->danhMucs as $dm)
                        <a href="{{ route('home.blog.index', ['category' => $dm->slug]) }}"
                            class="blog-detail-category-badge">
                            {{ $dm->tenDanhMuc }}
                        </a>
                    @endforeach
                </div>
                <h1 class="blog-detail-title">{{ $blog->tieuDe }}</h1>
                <div class="blog-detail-meta">
                    <span class="blog-detail-meta-item">
                        <i class="far fa-calendar-alt"></i>
                        {{ $blog->created_at?->format('d/m/Y') ?? '' }}
                    </span>
                    <span class="blog-detail-meta-item">
                        <i class="far fa-eye"></i>
                        {{ number_format($blog->luotXem ?? 0, 0, ',', '.') }} lượt xem
                    </span>
                    @if ($blog->taiKhoan && $blog->taiKhoan->hoSoNguoiDung)
                        <span class="blog-detail-meta-item">
                            <i class="far fa-user"></i>
                            {{ $blog->taiKhoan->hoSoNguoiDung->hoTen }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ NỘI DUNG CHÍNH ═══ --}}
    <div class="blog-detail-body">
        <div class="container">
            <div class="row g-4">

                {{-- CỘT NỘI DUNG (trái) --}}
                <div class="col-lg-8">

                    {{-- Bài viết --}}
                    <article class="blog-detail-article">
                        @if ($blog->tomTat)
                            <div class="blog-detail-summary">
                                <i class="fas fa-quote-left"></i>
                                <p>{{ $blog->tomTat }}</p>
                            </div>
                        @endif

                        <div class="blog-detail-content">
                            {!! $blog->noiDung !!}
                        </div>

                        @if ($blog->tags->count() > 0)
                            <div class="blog-detail-tags">
                                <span class="blog-detail-tags-label"><i class="fas fa-tags me-2"></i>Tags:</span>
                                @foreach ($blog->tags as $tag)
                                    <span class="blog-detail-tag">{{ $tag->tenTag }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="blog-detail-share">
                            <span class="blog-detail-share-label"><i class="fas fa-share-alt me-2"></i>Chia sẻ:</span>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}"
                                target="_blank" class="blog-detail-share-btn facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($blog->tieuDe) }}"
                                target="_blank" class="blog-detail-share-btn twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <button class="blog-detail-share-btn copy-link" onclick="copyBlogLink()"
                                title="Sao chép liên kết">
                                <i class="fas fa-link"></i>
                            </button>
                        </div>
                    </article>

                    {{-- ═══ BÀI VIẾT LIÊN QUAN (dưới article, cùng cột trái) ═══ --}}
                    @if ($relatedPosts->count() > 0 || $otherPosts->count() > 0)
                        <div class="blog-related-inline">
                            <div class="blog-related-inline-header">
                                <h3 class="blog-related-inline-title">
                                    <i class="fas fa-newspaper me-2"></i>Bài viết liên quan
                                </h3>
                                <a href="{{ route('home.blog.index') }}" class="btn-view-all">
                                    Xem tất cả bài viết <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>

                            <div class="row g-3">
                                @php
                                    $allRelated = $relatedPosts->merge($otherPosts)->take(6);
                                @endphp
                                @foreach ($allRelated as $related)
                                    <div class="col-md-6">
                                        <a href="{{ route('home.blog.show', ['slug' => $related->slug]) }}"
                                            class="blog-related-card">
                                            <div class="blog-related-card-img">
                                                <img src="{{ $related->anhDaiDien ? asset('storage/' . $related->anhDaiDien) : asset('assets/images/default-course.jpg') }}"
                                                    alt="{{ $related->tieuDe }}">
                                            </div>
                                            <div class="blog-related-card-body">
                                                <div class="blog-related-card-cats">
                                                    @foreach ($related->danhMucs->take(1) as $rdm)
                                                        <span class="blog-related-card-cat">{{ $rdm->tenDanhMuc }}</span>
                                                    @endforeach
                                                </div>
                                                <h4 class="blog-related-card-title">{{ $related->tieuDe }}</h4>
                                                <div class="blog-related-card-meta">
                                                    <span><i
                                                            class="far fa-calendar-alt me-1"></i>{{ $related->created_at?->format('d/m/Y') ?? '' }}</span>
                                                    <span><i
                                                            class="far fa-eye me-1"></i>{{ number_format($related->luotXem ?? 0, 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>

                            <div class="text-center mt-4">
                                <a href="{{ route('home.blog.index') }}" class="btn btn-back-blog">
                                    <i class="fas fa-th-large me-2"></i>Xem tất cả bài viết
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- SIDEBAR (phải, sticky) --}}
                <div class="col-lg-4">
                    <aside class="blog-sidebar">

                        {{-- Thông tin bài viết --}}
                        <div class="blog-sidebar-card blog-sidebar-info">
                            <div class="blog-sidebar-author">
                                @if ($blog->taiKhoan && $blog->taiKhoan->hoSoNguoiDung)
                                    <div class="blog-sidebar-author-avatar">
                                        @if ($blog->taiKhoan->hoSoNguoiDung->anhDaiDien)
                                            <img src="{{ asset('storage/' . $blog->taiKhoan->hoSoNguoiDung->anhDaiDien) }}"
                                                alt="">
                                        @else
                                            <div class="blog-sidebar-author-initial">
                                                {{ mb_substr($blog->taiKhoan->hoSoNguoiDung->hoTen, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="blog-sidebar-author-label">Tác giả</div>
                                        <div class="blog-sidebar-author-name">
                                            {{ $blog->taiKhoan->hoSoNguoiDung->hoTen }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="blog-sidebar-stats">
                                <div class="blog-sidebar-stat">
                                    <i class="far fa-calendar-alt"></i>
                                    <span>{{ $blog->created_at?->format('d/m/Y') ?? '' }}</span>
                                </div>
                                <div class="blog-sidebar-stat">
                                    <i class="far fa-eye"></i>
                                    <span>{{ number_format($blog->luotXem ?? 0, 0, ',', '.') }} lượt xem</span>
                                </div>
                            </div>
                        </div>

                        {{-- Danh mục --}}
                        <div class="blog-sidebar-card">
                            <h5 class="blog-sidebar-title"><i class="fas fa-folder-open me-2"></i>Danh mục</h5>
                            <ul class="blog-sidebar-categories">
                                @foreach ($categories as $cat)
                                    <li>
                                        <a href="{{ route('home.blog.index', ['category' => $cat->slug]) }}"
                                            class="{{ $blog->danhMucs->contains('danhMucId', $cat->danhMucId) ? 'active' : '' }}">
                                            <span>{{ $cat->tenDanhMuc }}</span>
                                            <span class="blog-sidebar-count">{{ $cat->bai_viets_count }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Tags --}}
                        @if ($blog->tags->count() > 0)
                            <div class="blog-sidebar-card">
                                <h5 class="blog-sidebar-title"><i class="fas fa-hashtag me-2"></i>Tags bài viết</h5>
                                <div class="blog-sidebar-tags">
                                    @foreach ($blog->tags as $tag)
                                        <span class="blog-sidebar-tag">{{ $tag->tenTag }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Quay lại --}}
                        <div class="blog-sidebar-card text-center">
                            <a href="{{ route('home.blog.index') }}" class="btn btn-back-blog w-100">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
                            </a>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </div>

    <x-client.register-advice />
@endsection

@section('script')
    <script>
        function copyBlogLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                const btn = document.querySelector('.copy-link');
                const origHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i>';
                btn.classList.add('copied');
                setTimeout(() => {
                    btn.innerHTML = origHTML;
                    btn.classList.remove('copied');
                }, 2000);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Fade in article
            const article = document.querySelector('.blog-detail-article');
            if (article) {
                article.style.opacity = '0';
                article.style.transform = 'translateY(20px)';
                article.style.transition = '0.6s ease-out';
                setTimeout(() => {
                    article.style.opacity = '1';
                    article.style.transform = 'translateY(0)';
                }, 200);
            }

            // Fade in related cards
            document.querySelectorAll('.blog-related-card').forEach((el, i) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = `0.5s ease-out ${i * 0.1}s`;
                const obs = new IntersectionObserver(entries => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                            obs.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.1
                });
                obs.observe(el);
            });
        });
    </script>
@endsection
