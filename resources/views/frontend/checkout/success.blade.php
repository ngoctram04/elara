@extends('layouts.frontend')
@section('title','Đặt hàng thành công')

@section('content')
<div class="container py-5 text-center">

    <div class="card shadow-sm p-5">

        <h2 class="text-success mb-3">
            ✔ Đặt hàng thành công!
        </h2>

        <p class="mb-4">
            Mã đơn hàng của bạn: <strong>#{{ $order->id }}</strong>
        </p>

        <a href="{{ route('home') }}" class="btn btn-primary me-2">
            Tiếp tục mua hàng
        </a>

        <a href="{{ route('orders.history') }}">
    Xem đơn hàng của tôi
</a>

    </div>

</div>
@endsection