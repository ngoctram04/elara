@extends('layouts.frontend')

@section('title','Trang mua sắm')

@section('content')

{{-- ================= BANNER ================= --}}
<div class="mb-5">
    <div class="banner-box">
        <img src="/images/banner.jpg" alt="Banner">
    </div>
</div>

{{-- ================= FLASH SALE ================= --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="page-title m-0">🔥 Flash Sale</h4>
    <a href="#" class="see-all">
        Xem tất cả →
    </a>
</div>

<div class="row">
    @forelse($flashSaleProducts as $product)
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="product-card h-100">

                {{-- IMAGE --}}
                <div class="product-image">
                    <img src="{{ $product->mainImage() }}"
                         alt="{{ $product->name }}">
                </div>

                {{-- INFO --}}
                <div class="product-body">
                    <h6 class="product-name">
                        {{ $product->name }}
                    </h6>

                    <div class="product-price">
                        {{ number_format($product->min_price) }}đ
                    </div>
                </div>

            </div>
        </div>
    @empty
        <p class="text-muted">Chưa có sản phẩm.</p>
    @endforelse
</div>

{{-- ================= SẢN PHẨM MỚI ================= --}}
<div class="mt-5 mb-3">
    <h4 class="page-title">✨ Sản phẩm mới</h4>
</div>

<div class="row">
    @foreach($latestProducts as $product)
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="product-card h-100">

                <div class="product-image">
                    <img src="{{ $product->mainImage() }}"
                         alt="{{ $product->name }}">
                </div>

                <div class="product-body">
                    <h6 class="product-name">
                        {{ $product->name }}
                    </h6>

                    <div class="product-price">
                        {{ number_format($product->min_price) }}đ
                    </div>
                </div>

            </div>
        </div>
    @endforeach
</div>

@endsection
