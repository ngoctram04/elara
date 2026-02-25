@extends('layouts.frontend')
@section('title','Thanh toán')

@section('content')

<style>
body{background:#f5f6fa;}
.checkout-title{font-weight:700;margin-bottom:20px;}
.checkout-card{
    border:0;
    border-radius:14px;
    box-shadow:0 6px 18px rgba(0,0,0,0.06);
    background:#fff;
}
.order-sticky{position:sticky;top:90px;}
.form-control{border-radius:10px;padding:12px;font-size:14px;}
.payment-option{
    border:1px solid #e5e7eb;
    border-radius:12px;
    padding:14px;
    margin-bottom:12px;
    cursor:pointer;
    transition:.2s;
}
.payment-option.active{
    border-color:#28a745;
    background:#f8fff9;
}
.order-item{
    display:flex;
    gap:10px;
    padding:12px 0;
    border-bottom:1px dashed #eee;
}
.order-item:last-child{border-bottom:0;}
.order-img{
    width:60px;
    height:60px;
    border-radius:10px;
    object-fit:cover;
    border:1px solid #eee;
}
.order-total{
    font-size:22px;
    font-weight:700;
    color:#dc3545;
}
.btn-order{
    border-radius:12px;
    font-size:16px;
    font-weight:600;
    padding:14px;
}
</style>

<div class="container py-4">

<h4 class="checkout-title">Thanh toán</h4>

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<form method="POST" action="{{ route('checkout.store') }}">
@csrf

<input type="hidden" name="promotion_code" value="{{ session('promotion_code') }}">

@php
    // Nếu controller chưa truyền thì tự tính fallback
    $discount = $discount ?? session('promotion_discount', 0);
    $total = $total ?? max($subtotal - $discount, 0);
    $promotionName = session('promotion_name');
@endphp

<div class="row g-4">

{{-- LEFT --}}
<div class="col-lg-7">

    <div class="checkout-card p-4 mb-3">
        <h6 class="fw-bold mb-3">Thông tin nhận hàng</h6>

        <input name="receiver_name"
               class="form-control mb-2"
               placeholder="Họ và tên"
               value="{{ auth()->user()->name ?? '' }}"
               required>

        <input name="receiver_phone"
               class="form-control mb-2"
               placeholder="Số điện thoại"
               value="{{ auth()->user()->phone ?? '' }}"
               required>

        <textarea name="receiver_address"
                  class="form-control"
                  rows="3"
                  placeholder="Địa chỉ nhận hàng"
                  required></textarea>
    </div>

    <div class="checkout-card p-4 mb-3">
        <h6 class="fw-bold mb-3">Phương thức thanh toán</h6>

        <label class="payment-option active d-flex align-items-center">
            <input type="radio" name="payment_method" value="cod" checked>
            <div class="ms-2">
                <strong>Thanh toán khi nhận hàng (COD)</strong>
            </div>
        </label>

        <label class="payment-option d-flex align-items-center">
            <input type="radio" name="payment_method" value="vnpay">
            <div class="ms-2">
                <strong>Thanh toán VNPay</strong>
            </div>
        </label>
    </div>

    <div class="checkout-card p-4">
        <h6 class="fw-bold mb-2">Ghi chú</h6>
        <textarea name="note"
                  class="form-control"
                  rows="3"
                  placeholder="Ghi chú (tuỳ chọn)"></textarea>
    </div>

</div>

{{-- RIGHT --}}
<div class="col-lg-5">
<div class="checkout-card p-4 order-sticky">

<h6 class="fw-bold mb-3">Đơn hàng của bạn</h6>

@foreach($carts as $cart)
@php
    $variant = $cart->variant;
    $product = $variant->product;

    $price = $variant->final_price ?? $variant->price;
    $lineTotal = $price * $cart->quantity;

    $image = optional($variant->mainImage)->image_path;
    $imageUrl = $image
        ? asset('storage/'.$image)
        : asset('images/no-image.png');
@endphp

<div class="order-item">
    <img src="{{ $imageUrl }}" class="order-img">

    <div class="flex-grow-1">
        <div class="fw-semibold">{{ $product->name }}</div>
        <div class="text-muted small">
            {{ $variant->attribute_value }} × {{ $cart->quantity }}
        </div>
        <div class="text-muted small">
            {{ number_format($price) }}đ
        </div>
    </div>

    <div class="fw-semibold">
        {{ number_format($lineTotal) }}đ
    </div>
</div>
@endforeach

<hr>

<div class="d-flex justify-content-between mb-1">
    <span>Tạm tính</span>
    <span>{{ number_format($subtotal) }}đ</span>
</div>

@if($discount > 0)
<div class="d-flex justify-content-between text-success mb-1">
    <span>Giảm giá</span>
    <span>-{{ number_format($discount) }}đ</span>
</div>

@if($promotionName)
<div class="small text-success mb-2">
    Đã áp dụng: {{ $promotionName }}
</div>
@endif
@endif

<div class="d-flex justify-content-between align-items-center mb-2">
    <span class="fw-bold">Thanh toán</span>
    <span class="order-total">
        {{ number_format($total) }}đ
    </span>
</div>

<button type="submit" class="btn btn-success w-100 mt-2 btn-order">
    Đặt hàng
</button>

</div>
</div>

</div>
</form>
</div>

<script>
document.querySelectorAll('.payment-option').forEach(option=>{
    option.addEventListener('click',()=>{
        document.querySelectorAll('.payment-option').forEach(o=>o.classList.remove('active'));
        option.classList.add('active');
        option.querySelector('input').checked = true;
    });
});
</script>

@endsection