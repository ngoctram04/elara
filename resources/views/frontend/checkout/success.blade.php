@extends('layouts.frontend')
@section('title','Đặt hàng thành công')

@section('content')

<style>
body{
    background:#f5f6fa;
}

/* Card */
.success-card{
    border:0;
    border-radius:16px;
    box-shadow:0 8px 25px rgba(0,0,0,0.06);
    background:#fff;
    max-width:650px;
    margin:auto;
}

/* Icon */
.success-icon{
    width:90px;
    height:90px;
    border-radius:50%;
    background:#e9f9ef;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:40px;
    color:#28a745;
    margin:auto;
}

/* Error icon */
.error-icon{
    background:#fdeaea;
    color:#dc3545;
}

/* Title */
.success-title{
    font-weight:700;
    margin-top:15px;
}

/* Order info */
.order-info{
    background:#f8f9fa;
    border-radius:10px;
    padding:15px;
    margin-top:20px;
    font-size:14px;
}

.order-info .row{
    margin-bottom:6px;
}

.order-info .label{
    color:#888;
}

.order-total{
    font-size:20px;
    font-weight:700;
    color:#dc3545;
}

/* Buttons */
.btn-action{
    border-radius:10px;
    font-weight:600;
    padding:10px 18px;
}
</style>

<div class="container py-5">

    <div class="success-card p-4 p-md-5 text-center">

        {{-- ============================= --}}
        {{-- FLASH MESSAGE (QUAN TRỌNG) --}}
        {{-- ============================= --}}
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        {{-- ============================= --}}
        {{-- ICON --}}
        {{-- ============================= --}}
        <div class="success-icon {{ session('error') ? 'error-icon' : '' }}">
            {{ session('error') ? '✕' : '✓' }}
        </div>

        {{-- Title --}}
        <h3 class="success-title {{ session('error') ? 'text-danger' : 'text-success' }}">
            {{ session('error') ? 'Thanh toán thất bại' : 'Đặt hàng thành công!' }}
        </h3>

        <p class="text-muted">
            Cảm ơn bạn đã mua sắm tại <strong>ELARA</strong>
        </p>

        {{-- ============================= --}}
        {{-- ORDER INFO --}}
        {{-- ============================= --}}
        <div class="order-info text-start">

            <div class="row">
                <div class="col-6 label">Mã đơn hàng:</div>
                <div class="col-6 text-end">
                    <strong>#{{ $order->id }}</strong>
                </div>
            </div>

            <div class="row">
                <div class="col-6 label">Ngày đặt:</div>
                <div class="col-6 text-end">
                    {{ $order->created_at->format('d/m/Y H:i') }}
                </div>
            </div>

            <div class="row">
                <div class="col-6 label">Phương thức thanh toán:</div>
                <div class="col-6 text-end">
                    {{ $order->payment_method == 'cod' ? 'Thanh toán khi nhận hàng (COD)' : 'VNPay' }}
                </div>
            </div>

            <div class="row">
                <div class="col-6 label">Trạng thái thanh toán:</div>
                <div class="col-6 text-end">
                    @if($order->payment_status == \App\Models\Order::PAYMENT_PAID)
                        <span class="badge bg-success">Đã thanh toán</span>
                    @else
                        <span class="badge bg-warning text-dark">Chưa thanh toán</span>
                    @endif
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-6 label">Tổng thanh toán:</div>
                <div class="col-6 text-end order-total">
                    {{ number_format($order->total) }}đ
                </div>
            </div>

        </div>

        {{-- ============================= --}}
        {{-- BUTTONS --}}
        {{-- ============================= --}}
        <div class="mt-4 d-flex justify-content-center flex-wrap gap-2">

            <a href="{{ route('home') }}"
               class="btn btn-outline-secondary btn-action">
                ← Tiếp tục mua hàng
            </a>

            <a href="{{ route('orders.history') }}"
               class="btn btn-success btn-action">
                Xem đơn hàng của tôi
            </a>

        </div>

    </div>

</div>

@endsection