@extends('layouts.frontend')
@section('title','Chi tiết đơn hàng')

@section('content')
<div class="container py-4">

    <h4 class="mb-4 fw-bold">Đơn hàng #{{ $order->id }}</h4>

    <div class="card shadow-sm mb-3">
        <div class="card-body">

            <p><b>Ngày đặt:</b> {{ $order->created_at->format('d/m/Y H:i') }}</p>
            <p><b>Trạng thái:</b> {{ $order->status_name }}</p>
            <p><b>Tổng tiền:</b> {{ number_format($order->total) }}đ</p>

        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <h6 class="fw-bold mb-3">Sản phẩm</h6>

            @foreach($order->items as $item)
                <div class="d-flex justify-content-between mb-2">
                    <div>
                        {{ $item->variant->product->name }}<br>
                        <small>
                            {{ $item->variant->attribute_name }}:
                            {{ $item->variant->attribute_value }}
                            × {{ $item->quantity }}
                        </small>
                    </div>

                    <div>
                        {{ number_format($item->price * $item->quantity) }}đ
                    </div>
                </div>
            @endforeach

        </div>
    </div>

</div>
@endsection