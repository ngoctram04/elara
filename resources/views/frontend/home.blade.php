@extends('layouts.frontend')

@section('title', 'Trang mua sắm')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
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
        <section class="flash-sale-section home-block mb-5"
                 data-countdown-end="{{ now()->addDays(2)->addHours(15)->format('Y-m-d H:i:s') }}">

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
        <section class="home-section brand-section mb-5">
            <div class="section-head">
                <h2 class="section-title mb-0">THƯƠNG HIỆU NỔI BẬT</h2>
            </div>

            <div class="swiper brand-slider small-slider">
                <div class="swiper-wrapper">
                    @foreach($brands as $brand)
                        <div class="swiper-slide">
                            <a href="{{ route('shop', ['brand' => $brand->id]) }}" class="brand-card">
                                <div class="brand-card-inner">
                                    <img
                                        src="{{ $brand->image ? asset('storage/' . $brand->image) : asset('images/no-image.png') }}"
                                        alt="{{ $brand->name }}"
                                    >
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
        <section class="home-section mb-5">
            <div class="section-head">
                <h2 class="section-title mb-0">SẢN PHẨM NỔI BẬT</h2>
                <a href="{{ route('shop', ['sort' => 'featured']) }}" class="section-more-link">
                    Xem thêm
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
        <section class="home-section category-section mb-5">
            <div class="section-head">
                <h2 class="section-title mb-0">DANH MỤC NỔI BẬT</h2>
            </div>

            <div class="swiper category-slider small-slider">
                <div class="swiper-wrapper">
                    @foreach($categories as $category)
                        <div class="swiper-slide">
                            <a href="{{ route('shop', ['category' => $category->id]) }}" class="category-card">
                                <div class="category-card-inner">
                                    <div class="category-thumb">
                                        @if(!empty($category->image))
                                            <img
                                                src="{{ asset('storage/' . $category->image) }}"
                                                alt="{{ $category->name }}"
                                            >
                                        @else
                                            <div class="category-thumb-placeholder">
                                                <i class="bi bi-grid"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="category-name">
                                        {{ $category->name }}
                                    </div>
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
        <section class="home-section mb-5">
            <div class="section-head">
                <h2 class="section-title mb-0">SẢN PHẨM MỚI</h2>
                <a href="{{ route('shop', ['sort' => 'newest']) }}" class="section-more-link">
                    Xem thêm
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
            $sideBlogs = $blogs->skip(1)->take(4);
        @endphp

        <section class="home-blog-section mb-4">
            <div class="section-head">
                <h2 class="section-title mb-0">BLOG LÀM ĐẸP</h2>

                @if(Route::has('blogs.index'))
                    <a href="{{ route('blogs.index') }}" class="section-more-link">Xem thêm</a>
                @endif
            </div>

            <div class="row g-4 blog-layout">
                @if($mainBlog)
                    <div class="col-lg-7">
                        <article class="blog-feature-card">
                            <a href="{{ Route::has('blogs.show') ? route('blogs.show', $mainBlog->slug ?? $mainBlog->id) : '#' }}"
                               class="blog-feature-image">
                                <img
                                    src="{{ !empty($mainBlog->image) ? asset('storage/' . $mainBlog->image) : asset('images/no-image.png') }}"
                                    alt="{{ $mainBlog->title }}"
                                >
                            </a>

                            <div class="blog-feature-content">
                                <h3 class="blog-feature-title">
                                    <a href="{{ Route::has('blogs.show') ? route('blogs.show', $mainBlog->slug ?? $mainBlog->id) : '#' }}">
                                        {{ $mainBlog->title }}
                                    </a>
                                </h3>

                                <div class="blog-meta">
                                    <span>
                                        <i class="bi bi-calendar3"></i>
                                        {{ optional($mainBlog->published_at ?? $mainBlog->created_at)->format('d.m.Y') }}
                                    </span>
                                    <span>/</span>
                                    <span>
                                        <i class="bi bi-person"></i>
                                        {{ $mainBlog->author->name ?? 'ELARA Cosmetics' }}
                                    </span>
                                </div>

                                <p class="blog-excerpt">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($mainBlog->excerpt ?? $mainBlog->content), 220) }}
                                </p>

                                <a href="{{ Route::has('blogs.show') ? route('blogs.show', $mainBlog->slug ?? $mainBlog->id) : '#' }}"
                                   class="blog-read-more">
                                    Đọc tiếp
                                </a>
                            </div>
                        </article>
                    </div>
                @endif

                <div class="col-lg-5">
                    <div class="blog-side-list">
                        @foreach($sideBlogs as $blog)
                            <article class="blog-side-item">
                                <a href="{{ Route::has('blogs.show') ? route('blogs.show', $blog->slug ?? $blog->id) : '#' }}"
                                   class="blog-side-thumb">
                                    <img
                                        src="{{ !empty($blog->image) ? asset('storage/' . $blog->image) : asset('images/no-image.png') }}"
                                        alt="{{ $blog->title }}"
                                    >
                                </a>

                                <div class="blog-side-content">
                                    <h4 class="blog-side-title">
                                        <a href="{{ Route::has('blogs.show') ? route('blogs.show', $blog->slug ?? $blog->id) : '#' }}">
                                            {{ $blog->title }}
                                        </a>
                                    </h4>

                                    <div class="blog-meta">
                                        <span>
                                            <i class="bi bi-calendar3"></i>
                                            {{ optional($blog->published_at ?? $blog->created_at)->format('d.m.Y') }}
                                        </span>
                                        <span>/</span>
                                        <span>
                                            <i class="bi bi-person"></i>
                                            {{ $blog->author->name ?? 'ELARA Cosmetics' }}
                                        </span>
                                    </div>

                                    <p class="blog-side-excerpt">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($blog->excerpt ?? $blog->content), 120) }}
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
@vite('resources/js/home.js')
@endpush