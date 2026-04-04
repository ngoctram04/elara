@extends('layouts.frontend')

@section('title', 'Trang mua sắm')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>

<style>
    /* ===============================
       BRAND / CATEGORY NAME OVERLAY
    =============================== */
    .brand-card,
    .home-category-card {
        display: block;
        position: relative;
    }

    .brand-card-inner,
    .home-category-card-inner {
        position: relative;
        overflow: hidden;
    }

    .brand-name-overlay,
    .category-name-overlay {
        position: absolute;
        left: 10px;
        right: 10px;
        bottom: 10px;
        background: rgba(0, 0, 0, 0.65);
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        text-align: center;
        padding: 6px 10px;
        border-radius: 999px;
        opacity: 0;
        transform: translateY(8px);
        transition: all 0.25s ease;
        pointer-events: none;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        z-index: 2;
    }

    .brand-card:hover .brand-name-overlay,
    .home-category-card:hover .category-name-overlay {
        opacity: 1;
        transform: translateY(0);
    }
</style>
@endpush

@section('content')
<div class="page-wrapper home-page">

    {{-- ================= BANNER ================= --}}
    <section class="home-banner mb-4">
        <div id="homeBanner"
             class="carousel slide carousel-fade rounded-4 overflow-hidden"
             data-bs-ride="carousel"
             data-bs-interval="3000"
             data-bs-pause="false">

            <div class="carousel-inner">
                @for ($i = 1; $i <= 5; $i++)
                    <div class="carousel-item {{ $i === 1 ? 'active' : '' }}">
                        <img
                            src="{{ asset("storage/frontend/banner$i.png") }}"
                            class="d-block w-100"
                            alt="Banner {{ $i }}"
                        >
                    </div>
                @endfor
            </div>

            <button class="carousel-control-prev"
                    type="button"
                    data-bs-target="#homeBanner"
                    data-bs-slide="prev"
                    aria-label="Banner trước">
                <span class="carousel-control-prev-icon"></span>
            </button>

            <button class="carousel-control-next"
                    type="button"
                    data-bs-target="#homeBanner"
                    data-bs-slide="next"
                    aria-label="Banner tiếp theo">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </section>

    {{-- ================= FLASH SALE ================= --}}
    @if(isset($flashSaleProducts) && $flashSaleProducts->count())
        <section class="flash-sale-section home-block mb-4"
                 data-countdown-end="{{ $flashSaleEndTime ? \Carbon\Carbon::parse($flashSaleEndTime)->timestamp * 1000 : '' }}">

            <div class="section-head flash-sale-head">
                <div class="section-head-left">
                    <h2 class="section-title mb-0">FLASH SALE</h2>

                    <div class="flash-sale-countdown">
                        <span class="countdown-label">Thời gian còn lại</span>

                        <div class="countdown-box" id="flash-days">00 NGÀY</div>
                        <span class="countdown-sep">:</span>

                        <div class="countdown-box" id="flash-hours">00 GIỜ</div>
                        <span class="countdown-sep">:</span>

                        <div class="countdown-box" id="flash-minutes">00 PHÚT</div>
                        <span class="countdown-sep">:</span>

                        <div class="countdown-box" id="flash-seconds">00 GIÂY</div>
                    </div>
                </div>

                <a href="{{ route('shop', ['sort' => 'discount']) }}" class="section-more-link">
                    Xem thêm >>
                </a>
            </div>

            <div class="swiper flash-sale-swiper product-swiper">
                <div class="swiper-wrapper">
                    @foreach($flashSaleProducts as $product)
                        <div class="swiper-slide">
                            @include('frontend.partials.product-card-flash', [
                                'product' => $product,
                                'favorites' => $favorites ?? []
                            ])
                        </div>
                    @endforeach
                </div>

                <div class="swiper-button-prev flash-sale-prev"></div>
                <div class="swiper-button-next flash-sale-next"></div>
            </div>
        </section>
    @endif

    {{-- ================= BRANDS ================= --}}
    @if(isset($brands) && $brands->count())
        <section class="home-section brand-section mb-4">
            <div class="swiper brand-slider small-slider">
                <div class="swiper-wrapper">
                    @foreach($brands as $brand)
                        <div class="swiper-slide">
                            <a href="{{ route('shop', ['brand' => $brand->id]) }}"
                               class="brand-card"
                               title="{{ $brand->name }}"
                               aria-label="{{ $brand->name }}">
                                <div class="brand-card-inner">
                                    <img
                                        src="{{ $brand->image ? asset('storage/' . $brand->image) : asset('images/no-image.png') }}"
                                        alt="{{ $brand->name }}"
                                    >
                                    <span class="brand-name-overlay">{{ $brand->name }}</span>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <div class="swiper-button-prev brand-prev"></div>
                <div class="swiper-button-next brand-next"></div>
            </div>
        </section>
    @endif

    {{-- ================= FEATURED PRODUCTS ================= --}}
    @if(isset($featuredProducts) && $featuredProducts->count())
        <section class="home-section mb-4">
            <div class="section-head">
                <h2 class="section-title mb-0">SẢN PHẨM NỔI BẬT</h2>
                <a href="{{ route('shop', ['sort' => 'featured']) }}" class="section-more-link">
                    Xem thêm >>
                </a>
            </div>

            <div class="swiper featured-slider product-swiper">
                <div class="swiper-wrapper">
                    @foreach($featuredProducts as $product)
                        <div class="swiper-slide">
                            @include('frontend.partials.product-card-common', [
                                'product' => $product,
                                'favorites' => $favorites ?? []
                            ])
                        </div>
                    @endforeach
                </div>

                <div class="swiper-button-next featured-next"></div>
                <div class="swiper-button-prev featured-prev"></div>
            </div>
        </section>
    @endif

    {{-- ================= CATEGORIES ================= --}}
    @if(isset($categories) && $categories->count())
        <section class="home-section category-section mb-4">
            <div class="swiper category-slider small-slider">
                <div class="swiper-wrapper">
                    @foreach($categories as $category)
                        <div class="swiper-slide">
                            <a href="{{ route('shop', ['category' => $category->id]) }}"
                               class="home-category-card"
                               title="{{ $category->name }}"
                               aria-label="{{ $category->name }}">
                                <div class="home-category-card-inner">
                                    @if(!empty($category->image))
                                        <img
                                            src="{{ asset('storage/' . $category->image) }}"
                                            alt="{{ $category->name }}"
                                            class="home-category-image"
                                        >
                                    @else
                                        <div class="home-category-thumb-placeholder">
                                            <i class="bi bi-grid"></i>
                                        </div>
                                    @endif

                                    <span class="category-name-overlay">{{ $category->name }}</span>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <div class="swiper-button-prev category-prev"></div>
                <div class="swiper-button-next category-next"></div>
            </div>
        </section>
    @endif

    {{-- ================= LATEST PRODUCTS ================= --}}
    @if(isset($latestProducts) && $latestProducts->count())
        <section class="home-section mb-4">
            <div class="section-head">
                <h2 class="section-title mb-0">SẢN PHẨM MỚI</h2>
                <a href="{{ route('shop', ['sort' => 'newest']) }}" class="section-more-link">
                    Xem thêm >>
                </a>
            </div>

            <div class="swiper latest-slider product-swiper">
                <div class="swiper-wrapper">
                    @foreach($latestProducts as $product)
                        <div class="swiper-slide">
                            @include('frontend.partials.product-card-common', [
                                'product' => $product,
                                'favorites' => $favorites ?? []
                            ])
                        </div>
                    @endforeach
                </div>

                <div class="swiper-button-next latest-next"></div>
                <div class="swiper-button-prev latest-prev"></div>
            </div>
        </section>
    @endif

    {{-- ================= BLOG ================= --}}
    @if(isset($blogs) && $blogs->count())
        @php
            $mainBlog = $blogs->first();
            $sideBlogs = $blogs->skip(1)->take(3);

            $getBlogThumbnail = function ($thumbnail) {
                if (empty($thumbnail)) {
                    return asset('images/no-image.png');
                }

                if (\Illuminate\Support\Str::startsWith($thumbnail, ['http://', 'https://'])) {
                    return $thumbnail;
                }

                if (\Illuminate\Support\Str::startsWith($thumbnail, '/storage/')) {
                    return asset(ltrim($thumbnail, '/'));
                }

                if (\Illuminate\Support\Str::startsWith($thumbnail, 'storage/')) {
                    return asset($thumbnail);
                }

                return asset('storage/' . ltrim($thumbnail, '/'));
            };
        @endphp

        <section class="home-blog-section mb-4">
    <div class="section-head">
        <h2 class="section-title mb-0">BLOG LÀM ĐẸP</h2>

        @if(Route::has('blogs.index'))
            <a href="{{ route('blogs.index') }}" class="section-more-link">Xem thêm >></a>
        @endif
    </div>

    <div class="row g-4 blog-layout align-items-stretch">

        {{-- BLOG CHÍNH --}}
        @if($mainBlog)
            <div class="col-lg-7 d-flex">
                <article class="blog-feature-card w-100 h-100">

                    <a href="{{ Route::has('blogs.show') ? route('blogs.show', $mainBlog->slug ?? $mainBlog->id) : '#' }}"
                       class="blog-feature-image">
                        <img
                            src="{{ $getBlogThumbnail($mainBlog->thumbnail ?? null) }}"
                            alt="{{ html_entity_decode($mainBlog->title, ENT_QUOTES, 'UTF-8') }}"
                            onerror="this.onerror=null;this.src='{{ asset('images/no-image.png') }}';"
                        >
                    </a>

                    <div class="blog-feature-content">

                        <h3 class="blog-feature-title">
                            <a href="{{ Route::has('blogs.show') ? route('blogs.show', $mainBlog->slug ?? $mainBlog->id) : '#' }}">
                                {{ html_entity_decode($mainBlog->title, ENT_QUOTES, 'UTF-8') }}
                            </a>
                        </h3>

                        <div class="blog-meta">
                            <span>
                                <i class="bi bi-calendar3"></i>
                                {{ \Carbon\Carbon::parse($mainBlog->published_at ?? $mainBlog->created_at)->format('d.m.Y') }}
                            </span>
                            <span>/</span>
                            <span>
                                <i class="bi bi-person"></i>
                                {{ optional($mainBlog->author)->name ?? 'ELARA Cosmetics' }}
                            </span>
                        </div>

                        <p class="blog-excerpt">
                            {{ \Illuminate\Support\Str::limit(
                                html_entity_decode(
                                    strip_tags($mainBlog->excerpt ?: $mainBlog->content),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ),
                                220
                            ) }}
                        </p>

                        <a href="{{ Route::has('blogs.show') ? route('blogs.show', $mainBlog->slug ?? $mainBlog->id) : '#' }}"
                           class="blog-read-more">
                            Đọc tiếp
                        </a>

                    </div>
                </article>
            </div>
        @endif


        {{-- BLOG PHỤ --}}
        <div class="col-lg-5 d-flex">
            <div class="blog-side-list w-100 h-100">

                @foreach($sideBlogs as $blog)
                    <article class="blog-side-item">

                        <a href="{{ Route::has('blogs.show') ? route('blogs.show', $blog->slug ?? $blog->id) : '#' }}"
                           class="blog-side-thumb">
                            <img
                                src="{{ $getBlogThumbnail($blog->thumbnail ?? null) }}"
                                alt="{{ html_entity_decode($blog->title, ENT_QUOTES, 'UTF-8') }}"
                                onerror="this.onerror=null;this.src='{{ asset('images/no-image.png') }}';"
                            >
                        </a>

                        <div class="blog-side-content">

                            <h4 class="blog-side-title">
                                <a href="{{ Route::has('blogs.show') ? route('blogs.show', $blog->slug ?? $blog->id) : '#' }}">
                                    {{ html_entity_decode($blog->title, ENT_QUOTES, 'UTF-8') }}
                                </a>
                            </h4>

                            <div class="blog-meta">
                                <span>
                                    <i class="bi bi-calendar3"></i>
                                    {{ \Carbon\Carbon::parse($blog->published_at ?? $blog->created_at)->format('d.m.Y') }}
                                </span>
                                <span>/</span>
                                <span>
                                    <i class="bi bi-person"></i>
                                    {{ optional($blog->author)->name ?? 'ELARA Cosmetics' }}
                                </span>
                            </div>

                            <p class="blog-side-excerpt">
                                {{ \Illuminate\Support\Str::limit(
                                    html_entity_decode(
                                        strip_tags($blog->excerpt ?: $blog->content),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ),
                                    95
                                ) }}
                            </p>

                        </div>
                    </article>
                @endforeach

            </div>
        </div>

    </div>
</section>
    @endif

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
@endpush