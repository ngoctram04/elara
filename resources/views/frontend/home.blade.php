@extends('layouts.frontend')

@section('title','Trang mua sắm')

@section('content')

{{-- ================= BANNER ================= --}}
<section class="home-banner mb-5">
    <div id="homeBanner"
         class="carousel slide rounded-4 overflow-hidden"
         data-bs-ride="carousel">

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
                data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>

        <button class="carousel-control-next"
                type="button"
                data-bs-target="#homeBanner"
                data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
</section>

{{-- ================= SERVICE / USP ================= --}}
<section class="home-services mb-5">
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
<section class="home-section mb-5">
    <h3 class="section-title text-danger">
        🔥 FLASH SALE
    </h3>

    {{-- BẮT BUỘC PHẢI CÓ ROW --}}
    <div class="row g-4">
        @foreach($flashSaleProducts as $product)
            @include('frontend.partials.product-card-flash', [
                'product' => $product
            ])
        @endforeach
    </div>
</section>
@endif

{{-- ================= FEATURED PRODUCTS ================= --}}
@if($featuredProducts->count())
<section class="home-section mb-5">
    <h3 class="section-title">
        ⭐ SẢN PHẨM NỔI BẬT
    </h3>

    <div class="row g-4">
        @foreach($featuredProducts as $product)
            @include('frontend.partials.product-card-common', [
                'product' => $product
            ])
        @endforeach
    </div>
</section>
@endif

{{-- ================= LATEST PRODUCTS ================= --}}
@if($latestProducts->count())
<section class="home-section mb-5">
    <h3 class="section-title">
        🆕 SẢN PHẨM MỚI
    </h3>

    <div class="row g-4">
        @foreach($latestProducts as $product)
            @include('frontend.partials.product-card-common', [
                'product' => $product
            ])
        @endforeach
    </div>
</section>
@endif

@endsection
