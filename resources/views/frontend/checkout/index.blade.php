@extends('layouts.frontend')
@section('title','Thanh toán')

@section('content')
<div class="container py-4">

    <h4 class="mb-4 fw-bold">Thanh toán</h4>

    {{-- Alert --}}
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('checkout.store') }}">
        @csrf

        <div class="row">

            {{-- LEFT --}}
            <div class="col-md-7">

                {{-- Thông tin nhận hàng --}}
                <div class="card p-3 shadow-sm mb-3">
                    <h6 class="fw-bold mb-3">Thông tin nhận hàng</h6>

                    <input name="receiver_name"
                           class="form-control mb-2"
                           placeholder="Họ tên"
                           value="{{ auth()->user()->name ?? '' }}"
                           required>

                    <input name="receiver_phone"
                           class="form-control mb-2"
                           placeholder="Số điện thoại"
                           value="{{ auth()->user()->phone ?? '' }}"
                           required>

                    <textarea name="receiver_address"
                              class="form-control"
                              placeholder="Địa chỉ"
                              required></textarea>
                </div>

                {{-- Phương thức thanh toán --}}
                <div class="card p-3 shadow-sm mb-3">
                    <h6 class="fw-bold mb-3">Phương thức thanh toán</h6>

                    <div class="form-check mb-2">
                        <input class="form-check-input"
                               type="radio"
                               name="payment_method"
                               value="cod"
                               checked>
                        <label class="form-check-label">
                            Thanh toán khi nhận hàng (COD)
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input"
                               type="radio"
                               name="payment_method"
                               value="vnpay">
                        <label class="form-check-label">
                            Thanh toán VNPay
                        </label>
                    </div>
                </div>

                {{-- Ghi chú --}}
                <div class="card p-3 shadow-sm mb-3">
                    <h6 class="fw-bold mb-2">Ghi chú</h6>
                    <textarea name="note"
                              class="form-control"
                              placeholder="Ghi chú cho đơn hàng (tuỳ chọn)"></textarea>
                </div>

            </div>

            {{-- RIGHT --}}
            <div class="col-md-5">
                <div class="card p-3 shadow-sm">

                    <h6 class="fw-bold mb-3">Đơn hàng</h6>

                    @foreach($carts as $cart)
                        @php
                            $variant = $cart->variant;
                            $product = $variant->product;
                            $lineTotal = $variant->price * $cart->quantity;
                        @endphp

                        <div class="d-flex justify-content-between mb-2">
                            <div>
                                {{ $product->name }}<br>
                                <small>
                                    {{ $variant->attribute_name }}:
                                    {{ $variant->attribute_value }}
                                    × {{ $cart->quantity }}
                                </small>
                            </div>

                            <div>{{ number_format($lineTotal) }}đ</div>
                        </div>
                    @endforeach

                    <hr>

                    <h5 class="text-end text-danger">
                        Tổng: {{ number_format($subtotal) }}đ
                    </h5>

                    <button class="btn btn-success w-100 mt-3 btn-lg">
                        Đặt hàng
                    </button>

                </div>
            </div>

        </div>
    </form>

</div>
@endsection