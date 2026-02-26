@extends('layouts.frontend')
@section('title','Chi tiết đơn hàng')

@section('content')

<div class="container py-4">

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Đơn hàng #{{ $order->id }}</h4>

    @if($order->canCancel())
        <form action="{{ route('orders.cancel', $order->id) }}" method="POST"
              onsubmit="return confirm('Bạn chắc chắn muốn huỷ đơn này?')">
            @csrf
            @method('PUT')
            <button class="btn btn-danger btn-sm">Huỷ đơn</button>
        </form>
    @endif
</div>

{{-- THÔNG BÁO --}}
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif


{{-- ================= THÔNG TIN CHUNG ================= --}}
<div class="card shadow-sm mb-3 border-0">
    <div class="card-body row">

        <div class="col-md-4">
            <p class="mb-1 text-muted">Ngày đặt</p>
            <b>{{ $order->created_at->format('d/m/Y H:i') }}</b>
        </div>

        <div class="col-md-4">
            <p class="mb-1 text-muted">Trạng thái</p>
            <span class="badge bg-{{ $order->status_badge }}">
                {{ $order->status_name }}
            </span>

            @if($order->isCompleted() && $order->delivered_at)
                <br>
                <small class="text-muted">
                    Đã giao: {{ $order->delivered_at->format('d/m/Y H:i') }}
                </small>
            @endif

            @if($order->isCancelled())
                <br>
                <small class="text-danger">
                    Huỷ bởi:
                    {{ $order->cancelled_by == 'admin' ? 'Admin' : 'Khách' }}
                    @if($order->cancelled_by_name)
                        ({{ $order->cancelled_by_name }})
                    @endif
                </small>

                @if($order->cancel_reason)
                    <br>
                    <small class="text-muted">
                        Lý do: {{ $order->cancel_reason }}
                    </small>
                @endif
            @endif
        </div>

        <div class="col-md-4">
            <p class="mb-1 text-muted">Thanh toán</p>
            <b>{{ $order->payment_method_name }}</b><br>

            <span class="badge bg-{{ $order->payment_status_badge }}">
                {{ $order->payment_status_name }}
            </span>

            <p class="mb-1 mt-2 text-muted">Khách thanh toán</p>
            <b class="text-danger fs-5">
                {{ number_format($order->grand_total) }}đ
            </b>
        </div>

    </div>
</div>


{{-- ================= TIMELINE ================= --}}
<div class="card shadow-sm mb-3 border-0">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Trạng thái đơn hàng</h6>

        <div class="d-flex justify-content-between text-center">

            <div class="flex-fill">
                <div class="status-circle {{ $order->status >= 1 ? 'active' : '' }}">1</div>
                <small>Đang xử lý</small>
            </div>

            <div class="flex-fill">
                <div class="status-circle {{ $order->status >= 2 ? 'active' : '' }}">2</div>
                <small>Đang giao</small>
            </div>

            <div class="flex-fill">
                <div class="status-circle {{ $order->status >= 3 ? 'active' : '' }}">3</div>
                <small>Đã giao</small>
            </div>

            <div class="flex-fill">
                <div class="status-circle {{ $order->status == 4 ? 'cancel' : '' }}">4</div>
                <small>Đã huỷ</small>
            </div>

        </div>
    </div>
</div>


{{-- ================= THÔNG TIN NHẬN HÀNG ================= --}}
<div class="card shadow-sm mb-3 border-0">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Thông tin nhận hàng</h6>

        <p class="mb-1"><b>Người nhận:</b> {{ $order->receiver_name }}</p>
        <p class="mb-1"><b>SĐT:</b> {{ $order->receiver_phone }}</p>
        <p class="mb-1"><b>Địa chỉ:</b> {{ $order->receiver_address }}</p>
    </div>
</div>


{{-- ================= DANH SÁCH SẢN PHẨM ================= --}}
<div class="card shadow-sm border-0">
    <div class="card-body">

        <h6 class="fw-bold mb-3">Sản phẩm</h6>

        @foreach($order->items as $item)

            @php
                $variant = $item->variant;
                $product = $variant->product ?? null;

                $image = optional($variant->mainImage)->image_path
                    ?? optional($product->mainImage)->image_path;

                $imageUrl = $image
                    ? asset('storage/'.$image)
                    : asset('images/no-image.png');
            @endphp

            <div class="d-flex justify-content-between align-items-center border-bottom py-3">

                <div class="d-flex align-items-center">
                    <img src="{{ $imageUrl }}"
                         width="70"
                         height="70"
                         class="rounded me-3"
                         style="object-fit:cover">

                    <div>
                        <b>{{ $product->name }}</b><br>
                        <small class="text-muted">
                            {{ $variant->attribute_name }}:
                            {{ $variant->attribute_value }}
                            × {{ $item->quantity }}
                        </small>
                    </div>
                </div>

                <div class="text-end">
                    <div>{{ number_format($item->price) }}đ</div>
                    <b class="text-danger">
                        {{ number_format($item->price * $item->quantity) }}đ
                    </b>
                </div>

            </div>
        @endforeach


        {{-- Tổng tiền --}}
        <div class="text-end mt-3">

            <div>
                Tạm tính:
                <strong>{{ number_format($order->subtotal) }}đ</strong>
            </div>

            @if($order->discount > 0)
                <div class="text-success">
                    Giảm giá:
                    - {{ number_format($order->discount) }}đ
                </div>
            @endif

            <div>
                Phí vận chuyển:
                <strong>{{ number_format($order->shipping_fee) }}đ</strong>
            </div>

            <div class="fs-5 mt-1">
                <b>Tổng thanh toán:</b>
                <b class="text-danger">
                    {{ number_format($order->grand_total) }}đ
                </b>
            </div>

        </div>

    </div>
</div>


</div>

<style>
.status-circle {
    width: 36px;
    height: 36px;
    line-height: 36px;
    border-radius: 50%;
    background: #e9ecef;
    margin: auto;
    font-weight: bold;
}

.status-circle.active {
    background: #0d6efd;
    color: #fff;
}

.status-circle.cancel {
    background: #dc3545;
    color: #fff;
}
</style>

@endsection
