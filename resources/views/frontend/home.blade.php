@extends('layouts.frontend')

@section('title','Trang mua sắm')

@section('content')

{{-- ================= BANNER ================= --}}
<section class="home-banner mb-4">
    <div id="homeBanner"
         class="carousel slide rounded-4 overflow-hidden"
         data-bs-ride="carousel"
         data-bs-interval="5000"> {{-- vẫn auto --}}

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
                data-bs-target="#homeBanner"
                data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>

        <button class="carousel-control-next"
                data-bs-target="#homeBanner"
                data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
</section>

{{-- ================= SERVICE ================= --}}
<section class="home-services mb-4">
    <div class="row text-center g-4">

        <div class="col-md-4">
            <div class="service-item">
                <i class="bi bi-truck service-icon blue"></i>
                <h6 class="mt-2 mb-0">Giao hàng nhanh</h6>
            </div>
        </div>

        <div class="col-md-4">
            <div class="service-item">
                <i class="bi bi-patch-check service-icon green"></i>
                <h6 class="mt-2 mb-1">Sản phẩm chính hãng</h6>
                <small class="text-muted">
                    Đảm bảo 100% chính hãng từ thương hiệu uy tín
                </small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="service-item">
                <i class="bi bi-shield-lock service-icon red"></i>
                <h6 class="mt-2 mb-0">Thanh toán an toàn</h6>
            </div>
        </div>

    </div>
</section>

{{-- ================= FLASH SALE ================= --}}
@if($flashSaleProducts->count())
<section class="home-section mb-4">
    <h2 class="section-title fancy text-danger">FLASH SALE</h2>

    <div class="swiper flash-slider">
        <div class="swiper-wrapper">
            @foreach($flashSaleProducts as $product)
                <div class="swiper-slide">
                    @include('frontend.partials.product-card-flash', ['product'=>$product])
                </div>
            @endforeach
        </div>

        <div class="swiper-button-next flash-next"></div>
        <div class="swiper-button-prev flash-prev"></div>
    </div>
</section>
@endif

{{-- ================= FEATURED ================= --}}
@if($featuredProducts->count())
<section class="home-section mb-4">
    <h2 class="section-title fancy">SẢN PHẨM NỔI BẬT</h2>

    <div class="swiper featured-slider">
        <div class="swiper-wrapper">
            @foreach($featuredProducts as $product)
                <div class="swiper-slide">
                    @include('frontend.partials.product-card-common', ['product'=>$product])
                </div>
            @endforeach
        </div>

        <div class="swiper-button-next featured-next"></div>
        <div class="swiper-button-prev featured-prev"></div>
    </div>
</section>
@endif

{{-- ================= LATEST ================= --}}
@if($latestProducts->count())
<section class="home-section mb-4">
    <h2 class="section-title fancy">SẢN PHẨM MỚI</h2>

    <div class="swiper latest-slider">
        <div class="swiper-wrapper">
            @foreach($latestProducts as $product)
                <div class="swiper-slide">
                    @include('frontend.partials.product-card-common', ['product'=>$product])
                </div>
            @endforeach
        </div>

        <div class="swiper-button-next latest-next"></div>
        <div class="swiper-button-prev latest-prev"></div>
    </div>
</section>
@endif

@endsection


{{-- ================= CSS ================= --}}
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>

<style>
.home-section .swiper {
    padding: 10px 0;
}

.home-section .swiper-slide {
    width: auto !important;
}

.product-item {
    width: 240px;
    flex-shrink: 0;
}

.home-section .swiper-button-next,
.home-section .swiper-button-prev {
    background: #fff;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.home-section .swiper-button-next::after,
.home-section .swiper-button-prev::after {
    font-size: 14px;
    color: #333;
}
</style>
@endpush


{{-- ================= JS ================= --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
function initSlider(selector, next, prev){
    new Swiper(selector, {
        slidesPerView: 'auto',
        spaceBetween: 16,
        loop: true,
        autoplay: false, // 🔥 TẮT AUTO
        speed: 700,
        navigation: {
            nextEl: next,
            prevEl: prev,
        },
    });
}

initSlider('.flash-slider', '.flash-next', '.flash-prev');
initSlider('.featured-slider', '.featured-next', '.featured-prev');
initSlider('.latest-slider', '.latest-next', '.latest-prev');
</script>
@endpush