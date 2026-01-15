@extends('layouts.frontend')

@section('title','Trang mua sắm')

@section('content')

{{-- ================= SLIDER / BANNER ================= --}}
<div class="mb-4">
    <div id="homeBanner" class="carousel slide rounded overflow-hidden" data-bs-ride="carousel">

        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="{{ asset('storage/frontend/banner1.png') }}" class="d-block w-100" alt="Banner 1">
            </div>
            <div class="carousel-item">
                <img src="{{ asset('storage/frontend/banner2.png') }}" class="d-block w-100" alt="Banner 2">
            </div>
            <div class="carousel-item">
                <img src="{{ asset('storage/frontend/banner3.png') }}" class="d-block w-100" alt="Banner 3">
            </div>
            <div class="carousel-item">
                <img src="{{ asset('storage/frontend/banner4.png') }}" class="d-block w-100" alt="Banner 4">
            </div>
            <div class="carousel-item">
                <img src="{{ asset('storage/frontend/banner5.png') }}" class="d-block w-100" alt="Banner 5">
            </div>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#homeBanner" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>

        <button class="carousel-control-next" type="button" data-bs-target="#homeBanner" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
</div>

{{-- ================= TIỆN ÍCH ================= --}}
<div class="service-box mb-5">
    <div class="row text-center">

        <div class="col-md-4 service-item">
            <div class="service-icon blue">
                <i class="bi bi-truck"></i>
            </div>
            <h6>Giao hàng nhanh</h6>
        </div>

        <div class="col-md-4 service-item">
            <div class="service-icon green">
                <i class="bi bi-patch-check"></i>
            </div>
            <h6>Sản phẩm chính hãng</h6>
            <small>Đảm bảo 100% chính hãng từ thương hiệu uy tín</small>
        </div>

        <div class="col-md-4 service-item">
            <div class="service-icon red">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h6>Thanh toán an toàn</h6>
        </div>

    </div>
</div>


{{-- ================= FLASH SALE ================= --}}
<h4 class="text-center mb-4 text-danger">FLASH SALE</h4>

<div class="row mb-5">
    @foreach($flashSaleProducts as $product)
        @include('frontend.partials.product-card-flash', ['product' => $product])
    @endforeach
</div>


{{-- ================= SẢN PHẨM NỔI BẬT ================= --}}
<h4 class="text-center mb-4">SẢN PHẨM NỔI BẬT</h4>

<div class="row mb-5">
    @foreach($featuredProducts as $product)
        @include('frontend.partials.product-card-common', ['product' => $product])
    @endforeach
</div>


{{-- ================= SẢN PHẨM MỚI ================= --}}
<h4 class="text-center mb-4">SẢN PHẨM MỚI</h4>

<div class="row mb-5">
    @foreach($latestProducts as $product)
        @include('frontend.partials.product-card-common', ['product' => $product])
    @endforeach
</div>

@endsection
