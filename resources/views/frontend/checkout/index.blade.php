@extends('layouts.frontend')
@section('title','Thanh toán')

@section('content')

<style>
body{background:#f5f6fa;}

.checkout-title{
    font-weight:700;
    margin-bottom:20px;
}

.checkout-card{
    border:0;
    border-radius:14px;
    box-shadow:0 6px 18px rgba(0,0,0,0.06);
    background:#fff;
}

.order-sticky{
    position:sticky;
    top:90px;
}

.form-control{
    border-radius:10px;
    padding:12px;
    font-size:14px;
}

.payment-option{
    border:1px solid #e5e7eb;
    border-radius:12px;
    padding:14px;
    margin-bottom:12px;
    cursor:pointer;
    transition:.2s;
}

.payment-option:hover{
    border-color:#28a745;
    background:#f8fff9;
}

.payment-option.active{
    border-color:#28a745;
    background:#f8fff9;
}

.payment-option input{
    margin-right:10px;
}

/* Order */
.order-item{
    display:flex;
    gap:10px;
    padding:12px 0;
    border-bottom:1px dashed #eee;
}

.order-item:last-child{
    border-bottom:0;
}

.order-img{
    width:60px;
    height:60px;
    border-radius:10px;
    object-fit:cover;
    border:1px solid #eee;
    background:#f8f9fa;
}

.order-info{flex:1;font-size:14px;}
.order-name{font-weight:600;margin-bottom:2px;}
.order-variant{font-size:12px;color:#888;}
.order-price{font-weight:600;white-space:nowrap;}

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

    {{-- Alert --}}
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('checkout.store') }}">
        @csrf

        <div class="row g-4">

            {{-- LEFT --}}
            <div class="col-lg-7">

                {{-- Thông tin nhận --}}
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

                {{-- Payment --}}
                <div class="checkout-card p-4 mb-3">
                    <h6 class="fw-bold mb-3">Phương thức thanh toán</h6>

                    <label class="payment-option active d-flex align-items-center">
                        <input type="radio" name="payment_method" value="cod" checked>
                        <div>
                            <strong>Thanh toán khi nhận hàng (COD)</strong><br>
                            <small class="text-muted">Trả tiền khi nhận hàng</small>
                        </div>
                    </label>

                    <label class="payment-option d-flex align-items-center">
                        <input type="radio" name="payment_method" value="vnpay">
                        <div>
                            <strong>Thanh toán VNPay</strong><br>
                            <small class="text-muted">Thanh toán online qua ngân hàng / ví</small>
                        </div>
                    </label>
                </div>

                {{-- Note --}}
                <div class="checkout-card p-4">
                    <h6 class="fw-bold mb-2">Ghi chú</h6>
                    <textarea name="note"
                              class="form-control"
                              rows="3"
                              placeholder="Ghi chú cho đơn hàng (tuỳ chọn)"></textarea>
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

                            // Giá (có khuyến mãi)
                            $price = $variant->final_price ?? $variant->price;
                            $lineTotal = $price * $cart->quantity;

                            // Ảnh variant
                            $image = optional($variant->mainImage)->image_path;

                            // Fallback ảnh product nếu có relation mainImage
                            if(!$image && isset($product->mainImage)){
                                $image = optional($product->mainImage)->image_path;
                            }

                            $imageUrl = $image
                                ? asset('storage/'.$image)
                                : asset('images/no-image.png');
                        @endphp

                        <div class="order-item">

                            <img src="{{ $imageUrl }}" class="order-img">

                            <div class="order-info">
                                <div class="order-name">
                                    {{ $product->name }}
                                </div>

                                <div class="order-variant">
                                    {{ $variant->attribute_name }}:
                                    {{ $variant->attribute_value }}
                                    × {{ $cart->quantity }}
                                </div>

                                <div class="text-muted">
                                    {{ number_format($price) }}đ
                                </div>
                            </div>

                            <div class="order-price">
                                {{ number_format($lineTotal) }}đ
                            </div>

                        </div>
                    @endforeach

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Tổng tiền</span>
                        <span class="order-total">
                            {{ number_format($subtotal) }}đ
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