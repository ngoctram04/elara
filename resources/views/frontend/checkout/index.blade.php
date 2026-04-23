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
$discount = $discount ?? 0;
$birthdayDiscount = $birthdayDiscount ?? 0;
@endphp

<div class="row g-4">


<div class="col-lg-7">

    <div class="checkout-card p-4 mb-3">
        <h6 class="fw-bold mb-3">Địa chỉ nhận hàng</h6>

        @if($defaultAddress)

        <div class="d-flex justify-content-between align-items-start">

            <div id="selected-address-info">
                <div class="fw-semibold">
                    {{ $defaultAddress->receiver_name }}
                    ({{ $defaultAddress->phone }})
                    <span class="badge bg-success ms-2">Mặc định</span>
                </div>

                <div class="text-muted small mt-1">
                    {{ $defaultAddress->address_detail }},
                    {{ $defaultAddress->ward }},
                    {{ $defaultAddress->district }},
                    {{ $defaultAddress->province }}
                </div>
            </div>

            <a href="#" class="text-primary"
               data-bs-toggle="modal"
               data-bs-target="#changeAddressModal">
                Thay đổi
            </a>
        </div>

        <input type="hidden"
               name="address_id"
               id="selected-address-id"
               value="{{ $defaultAddress->id }}">

        @else
        <div class="alert alert-warning">
            Bạn chưa có địa chỉ.
            <a href="{{ route('addresses.index') }}">Thêm địa chỉ</a>
        </div>
        @endif
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
$imageUrl = $image ? asset('storage/'.$image) : asset('images/no-image.png');
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

@php
$promotionDiscount = $discount ?? 0;
$birthdayDiscount = $birthdayDiscount ?? 0; 
$totalDiscount = $promotionDiscount + $birthdayDiscount;
@endphp
@if($birthdayDiscount > 0)
<div class="d-flex justify-content-between text-success mb-1">
    <span>Ưu đãi sinh nhật</span>
    <span>-{{ number_format($birthdayDiscount) }}đ</span>
</div>
@endif
@if($discount > 0)
<div class="d-flex justify-content-between text-success mb-1">
    <span>Giảm mã khuyến mãi</span>
    <span>-{{ number_format($discount) }}đ</span>
</div>
@endif


@php
    $memberLevel = auth()->user()->member_level ?? 'bronze';

    if ($shippingFee > 0) {
        $shippingText = number_format($shippingFee).'đ';
    } else {
        if ($memberLevel === 'diamond') {
            $shippingText = 'Miễn phí (Hạng Kim Cương)';
        }
        elseif ($memberLevel === 'gold' && $total >= 300000) {
            $shippingText = 'Miễn phí (Hạng Vàng)';
        }
        else {
            $shippingText = 'Miễn phí';
        }
    }
@endphp

<div class="d-flex justify-content-between mb-1">
<span>Phí vận chuyển</span>
<span id="shipping-fee">
    {{ $shippingText }}
</span>
</div>

<hr>

<div class="d-flex justify-content-between align-items-center mb-2">
<span class="fw-bold">Tổng thanh toán</span>
<span class="order-total" id="grand-total">
{{ number_format($grandTotal) }}đ
</span>
</div>

<button type="submit" class="btn btn-success w-100 mt-2 btn-order">
Đặt hàng
</button>

</div>
</div>

</div>
<div class="modal fade" id="changeAddressModal">
<div class="modal-dialog">
<div class="modal-content">

<div class="modal-header">
    <h6 class="modal-title">Chọn địa chỉ nhận hàng</h6>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

@foreach($addresses as $address)
<div class="border rounded p-2 mb-2 address-option"
     data-id="{{ $address->id }}"
     data-name="{{ $address->receiver_name }}"
     data-phone="{{ $address->phone }}"
     data-province="{{ $address->province }}"
     data-full="{{ $address->address_detail }}, {{ $address->ward }}, {{ $address->district }}, {{ $address->province }}"
     style="cursor:pointer;">

    <div class="fw-semibold">
        {{ $address->receiver_name }} ({{ $address->phone }})
        @if($address->is_default)
            <span class="badge bg-success">Mặc định</span>
        @endif
    </div>

    <div class="text-muted small">
        {{ $address->address_detail }},
        {{ $address->ward }},
        {{ $address->district }},
        {{ $address->province }}
    </div>
</div>
@endforeach

</div>

</div>
</div>
</div>
</form>
</div>
<script>
const memberLevel = "{{ auth()->user()->member_level ?? 'bronze' }}";
</script>
<script>

document.querySelectorAll('.payment-option').forEach(option=>{
    option.addEventListener('click',()=>{
        document.querySelectorAll('.payment-option')
            .forEach(o=>o.classList.remove('active'));

        option.classList.add('active');
        option.querySelector('input').checked = true;
    });
});

const subtotal = {{ $subtotal }};
const promotionDiscount = {{ $discount }};
const birthdayDiscount = {{ $birthdayDiscount }};

const totalWithoutShip = {{ $total }};

let serverShipping = {{ $shippingFee }};


function calculateShipping(province){

    province = province.toLowerCase();
    let shipping = 35000;

    if(province.includes('vĩnh long')){
        shipping = 15000;
    }
    else{
        const mienTay = [
            'cần thơ','bến tre','trà vinh','sóc trăng',
            'hậu giang','đồng tháp','an giang','kiên giang',
            'cà mau','bạc liêu','tiền giang'
        ];

        for(let t of mienTay){
            if(province.includes(t)){
                shipping = 25000;
                break;
            }
        }
    }
if(memberLevel === 'diamond'){
    shipping = 0;
}
else if(memberLevel === 'gold' && totalWithoutShip >= 300000){
    shipping = 0;
}

    let grandTotal = totalWithoutShip + shipping;

    let shippingText = shipping === 0
        ? 'Miễn phí'
        : new Intl.NumberFormat('vi-VN').format(shipping) + 'đ';

    document.getElementById('shipping-fee').innerText = shippingText;

    document.getElementById('grand-total').innerText =
        new Intl.NumberFormat('vi-VN').format(grandTotal) + 'đ';
}


document.querySelectorAll('.address-option').forEach(item=>{
    item.addEventListener('click', function(){

        let id = this.dataset.id;
        let name = this.dataset.name;
        let phone = this.dataset.phone;
        let full = this.dataset.full;
        let province = this.dataset.province;

        document.getElementById('selected-address-id').value = id;

        document.getElementById('selected-address-info').innerHTML = `
            <div class="fw-semibold">
                ${name} (${phone})
            </div>
            <div class="text-muted small mt-1">
                ${full}
            </div>
        `;

        calculateShipping(province);

        let modal = bootstrap.Modal.getInstance(
            document.getElementById('changeAddressModal')
        );
        modal.hide();
    });
});

window.addEventListener('load', function(){

    let selectedId = document.getElementById('selected-address-id').value;

    let defaultAddress = document.querySelector(
        `.address-option[data-id="${selectedId}"]`
    );

    if(defaultAddress){
        let province = defaultAddress.dataset.province;
        calculateShipping(province);
    }
});
</script>

@endsection
