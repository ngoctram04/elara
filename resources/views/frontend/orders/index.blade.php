@extends('layouts.frontend')
@section('title','Lịch sử đơn hàng')

@section('content')

<style>
body{background:#f5f6fa;}

.order-box{
    background:#fff;
    border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,.05);
    margin-bottom:20px;
}

.order-header{
    padding:12px 16px;
    border-bottom:1px solid #f0f0f0;
    display:flex;
    justify-content:space-between;
    align-items:center;
    font-size:14px;
}

.order-status{font-weight:600;}

.status-1{ color:#f39c12; }
.status-2{ color:#3498db; }
.status-3{ color:#2ecc71; }
.status-4{ color:#e74c3c; }

.order-item{
    display:flex;
    gap:12px;
    padding:14px 16px;
    border-bottom:1px solid #f7f7f7;
}

.order-item:last-child{border-bottom:none;}

.order-img{
    width:70px;
    height:70px;
    border-radius:6px;
    object-fit:cover;
    border:1px solid #eee;
}

.order-name{font-weight:600;margin-bottom:4px;}
.order-variant{font-size:13px;color:#888;}

.order-price{
    margin-left:auto;
    text-align:right;
    font-weight:600;
}

.order-footer{
    padding:14px 16px;
    border-top:1px solid #f0f0f0;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.order-total{
    font-size:18px;
    font-weight:700;
    color:#ee4d2d;
}

.btn-action{
    border-radius:6px;
    font-size:13px;
    padding:6px 14px;
    margin-left:6px;
}
</style>

<div class="container py-4">

<h4 class="fw-bold mb-4">Lịch sử đơn hàng</h4>

@forelse($orders as $order)

<div class="order-box">

    {{-- ================= HEADER ================= --}}
    <div class="order-header">
        <div>
            Mã đơn: <b>#{{ $order->id }}</b> |
            {{ $order->created_at->format('d/m/Y H:i') }}

            {{-- Ngày giao --}}
            @if($order->isCompleted() && $order->delivered_at)
                <br>
                <small class="text-muted">
                    Đã giao: {{ $order->delivered_at->format('d/m/Y H:i') }}
                </small>
            @endif

            {{-- Nếu bị huỷ --}}
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

        <div class="order-status status-{{ $order->status }}">
            {{ $order->status_name }}
        </div>
    </div>


    {{-- ================= ITEMS ================= --}}
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

        <div class="order-item">

            <img src="{{ $imageUrl }}" class="order-img">

            <div style="flex:1">
                <div class="order-name">
                    {{ $product->name ?? 'Sản phẩm' }}
                </div>

                <div class="order-variant">
                    {{ $variant->attribute_name ?? '' }}
                    {{ $variant->attribute_value ?? '' }}
                    x{{ $item->quantity }}
                </div>
            </div>

            <div class="order-price">
                {{ number_format($item->price) }}đ
            </div>

        </div>
    @endforeach


    {{-- ================= FOOTER ================= --}}
    <div class="order-footer">

        <div>
            Thanh toán: <b>{{ $order->payment_method_name }}</b>

            @if($order->discount > 0)
                <br>
                <small class="text-success">
                    Giảm: -{{ number_format($order->discount) }}đ
                </small>
            @endif
        </div>

        <div class="text-end">
            Tổng tiền:
            <span class="order-total">
                {{ number_format($order->total) }}đ
            </span>

            <div class="mt-2">

                {{-- Chi tiết --}}
                <a href="{{ route('orders.show',$order->id) }}"
                   class="btn btn-outline-secondary btn-sm btn-action">
                    Chi tiết
                </a>

                {{-- Pending → Huỷ --}}
                @if($order->canCancel())
                    <form action="{{ route('orders.cancel',$order->id) }}"
                          method="POST"
                          style="display:inline;">
                        @csrf
                        @method('PUT')
                        <button type="submit"
                                class="btn btn-outline-danger btn-sm btn-action"
                                onclick="return confirm('Bạn chắc chắn muốn huỷ đơn?')">
                            Huỷ đơn
                        </button>
                    </form>
                @endif

                {{-- Completed hoặc Cancelled → Mua lại --}}
                @if($order->isCompleted() || $order->isCancelled())
                    <form action="{{ route('orders.reorder',$order->id) }}"
                          method="POST"
                          style="display:inline;">
                        @csrf
                        <button type="submit"
                                class="btn btn-outline-primary btn-sm btn-action">
                            Mua lại
                        </button>
                    </form>
                @endif

            </div>
        </div>

    </div>

</div>

@empty
<div class="alert alert-info">
    Bạn chưa có đơn hàng nào.
</div>
@endforelse

<div class="mt-3">
    {{ $orders->links() }}
</div>

</div>
@endsection