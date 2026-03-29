@extends('layouts.frontend')

@section('title', 'Trang mua sắm')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
@endpush

@section('content')

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

    {{-- ================= SERVICE ================= --}}
    

    {{-- ================= FLASH SALE ================= --}}
    @if($flashSaleProducts->count())
        <section class="flash-sale-section mb-4"
                 data-countdown-end="{{ now()->addDays(2)->addHours(15)->format('Y-m-d H:i:s') }}">

            <div class="flash-sale-header">
                <div class="flash-sale-left">
                    <h2 class="flash-sale-title">FLASH SALE</h2>

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

                <a href="{{ route('shop', ['sort' => 'newest']) }}" class="flash-sale-more">
                    Xem thêm
                </a>
            </div>

            <div class="swiper flash-sale-swiper">
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

    {{-- ================= FEATURED ================= --}}
    @if($featuredProducts->count())
        <section class="home-section mb-4">
            <h2 class="section-title fancy">SẢN PHẨM NỔI BẬT</h2>

            <div class="swiper featured-slider">
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

    {{-- ================= LATEST ================= --}}
    @if($latestProducts->count())
        <section class="home-section mb-4">
            <h2 class="section-title fancy">SẢN PHẨM MỚI</h2>

            <div class="swiper latest-slider">
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

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
@endpush