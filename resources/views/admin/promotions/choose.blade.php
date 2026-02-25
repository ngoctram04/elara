@extends('layouts.admin')
@section('title', 'Chọn loại khuyến mãi')

@section('content')
<div class="container">
    <h4 class="mb-4 fw-bold">Chọn loại khuyến mãi</h4>

    <div class="row g-4">

        {{-- Khuyến mãi sản phẩm --}}
        <div class="col-md-6">
            <a href="{{ route('admin.promotions.createProduct') }}"
               class="card text-center p-4 h-100 shadow-sm text-decoration-none border-0 promotion-card">
               
                <i class="bi bi-box-seam display-4 text-primary"></i>

                <h5 class="mt-3 fw-semibold">
                    Khuyến mãi sản phẩm
                </h5>

                <p class="text-muted mb-0">
                    Áp dụng cho sản phẩm hoặc biến thể cụ thể
                </p>
            </a>
        </div>

        {{-- Khuyến mãi đơn hàng --}}
        <div class="col-md-6">
            <a href="{{ route('admin.promotions.createOrder') }}"
               class="card text-center p-4 h-100 shadow-sm text-decoration-none border-0 promotion-card">
               
                <i class="bi bi-cart-check display-4 text-success"></i>

                <h5 class="mt-3 fw-semibold">
                    Mã giảm giá đơn hàng
                </h5>

                <p class="text-muted mb-0">
                    Áp dụng cho toàn bộ đơn hàng
                </p>
            </a>
        </div>

    </div>
</div>

<style>
.promotion-card{
    transition: all .2s ease;
    cursor: pointer;
}
.promotion-card:hover{
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,.08);
}
</style>
@endsection