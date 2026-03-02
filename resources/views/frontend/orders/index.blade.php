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

@php
    // Tổng khách phải trả (ưu tiên grand_total)
    $payAmount = $order->grand_total
        ?? ($order->total + ($order->shipping_fee ?? 0));
@endphp

<div class="order-box">

    {{-- HEADER --}}
    <div class="order-header">
        <div>
            Mã đơn: <b>#{{ $order->id }}</b> |
            {{ $order->created_at->format('d/m/Y H:i') }}

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
                </small>
            @endif
        </div>

        <div class="order-status status-{{ $order->status }}">
            {{ $order->status_name }}
        </div>
    </div>


    {{-- ITEMS --}}
    {{-- ITEMS --}}
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
                {{ $variant->attribute_value ?? '' }}
                x{{ $item->quantity }}
            </div>

            {{-- ===== ĐÁNH GIÁ ===== --}}
            @if($order->status == 3)

                @if(!$item->review)
                    <a href="{{ route('reviews.create', $item->id) }}"
                       class="btn btn-warning btn-sm mt-2">
                        Đánh giá
                    </a>
                @else
                    <span class="badge bg-success mt-2">
                        Đã đánh giá
                    </span>
                @endif

            @endif
        </div>

        <div class="order-price">
            {{ number_format($item->price) }}đ
        </div>

    </div>
@endforeach


    {{-- FOOTER --}}
    <div class="order-footer">

        <div>
            Thanh toán: <b>{{ $order->payment_method_name }}</b>
{{-- Sinh nhật --}}
@if($order->birthday_discount > 0)
    <div class="text-success">
        Ưu đãi sinh nhật:
        -{{ number_format($order->birthday_discount) }}đ
    </div>
@endif
            {{-- Voucher --}}
@if($order->voucher_discount > 0)
    <div class="text-success">
        Voucher:
        -{{ number_format($order->voucher_discount) }}đ
    </div>
@endif



            @if($order->shipping_fee > 0)
                <br>
                <small class="text-muted">
                    Phí ship: {{ number_format($order->shipping_fee) }}đ
                </small>
            @endif
        </div>

        <div class="text-end">
            Tổng thanh toán:
            <span class="order-total">
                {{ number_format($payAmount) }}đ
            </span>

            <div class="mt-2">

                <a href="{{ route('orders.show',$order->id) }}"
                   class="btn btn-outline-secondary btn-sm btn-action">
                    Chi tiết
                </a>

                @if($order->canCancel())
                    <form action="{{ route('orders.cancel',$order->id) }}"
      method="POST"
      class="d-inline cancel-form">
    @csrf
    @method('PUT')
    <button type="button"
            class="btn btn-outline-danger btn-sm btn-action btn-cancel">
        Huỷ đơn
    </button>
</form>
                @endif

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
@push('scripts')

{{-- SweetAlert --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Xác nhận huỷ đơn
document.querySelectorAll('.btn-cancel').forEach(function(button){
    button.addEventListener('click', function () {

        let form = this.closest('.cancel-form');

        Swal.fire({
            title: 'Bạn muốn huỷ đơn?',
            text: 'Đơn hàng sẽ được huỷ và không thể khôi phục!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Huỷ đơn',
            cancelButtonText: 'Không',
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });

    });
});
</script>

{{-- Thông báo sau khi huỷ --}}
@if(session('success'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Thành công',
    text: '{{ session('success') }}',
    confirmButtonColor: '#3085d6'
});
</script>
@endif

@if(session('error'))
<script>
Swal.fire({
    icon: 'error',
    title: 'Lỗi',
    text: '{{ session('error') }}',
    confirmButtonColor: '#d33'
});
</script>
@endif

@endpush