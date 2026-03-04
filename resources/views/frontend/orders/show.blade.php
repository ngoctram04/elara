@extends('layouts.frontend')
@section('title','Chi tiết đơn hàng')

@section('content')

<div class="container py-4">

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0">Đơn hàng #{{ $order->id }}</h4>

    @if($order->canCancel())
        <form action="{{ route('orders.cancel', $order->id) }}"
      method="POST"
      class="cancel-form">
    @csrf
    @method('PUT')

    <input type="hidden" name="cancel_reason" class="cancel-reason">

    <button type="button"
            class="btn btn-danger btn-sm btn-cancel">
        Huỷ đơn
    </button>
</form>
    @endif
</div>



{{-- ================= THÔNG TIN CHUNG ================= --}}
<div class="card shadow-sm mb-3 border-0">
    <div class="card-body row">

        <div class="col-md-4">
    <p class="mb-1 text-muted">Ngày đặt</p>
    <b>{{ $order->created_at->format('d/m/Y H:i') }}</b>

    {{-- Nếu đã giao --}}
    @if($order->isCompleted() && $order->delivered_at)
        <br>
        <small class="text-success">
            Ngày giao: {{ $order->delivered_at->format('d/m/Y H:i') }}
        </small>
    @endif

    {{-- Nếu đã huỷ --}}
    @if($order->isCancelled() && $order->cancelled_at)
        <br>
        <small class="text-danger">
            Thời gian huỷ: {{ $order->cancelled_at->format('d/m/Y H:i') }}
        </small>
    @endif

    {{-- Lý do huỷ --}}
    @if($order->isCancelled() && $order->cancel_reason)
        <br>
        <small class="text-muted">
            Lý do: {{ $order->cancel_reason }}
        </small>
    @endif
</div>

        <div class="col-md-4">
            <p class="mb-1 text-muted">Trạng thái</p>
            <span class="badge bg-{{ $order->status_badge }}">
                {{ $order->status_name }}
            </span>
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

            <div class="border-bottom py-3">

                {{-- ===== THÔNG TIN SẢN PHẨM ===== --}}
                <div class="d-flex justify-content-between align-items-center">

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

                {{-- ================= REVIEW ================= --}}
                @if($order->status == 3)
                    <div class="mt-3 ms-5">

                        @if(!$item->review)
                            {{-- Chưa đánh giá --}}
                            <a href="{{ route('reviews.create', $item->id) }}"
                               class="btn btn-warning btn-sm">
                                Đánh giá sản phẩm
                            </a>

                        @else
                            {{-- Đã đánh giá --}}
                            <div class="border rounded p-3 bg-light">

                                {{-- ⭐ Rating --}}
                                <div class="text-warning mb-1" style="font-size:18px;">
    @for($i = 1; $i <= 5; $i++)
        @if($i <= $item->review->rating)
            ★
        @else
            ☆
        @endif
    @endfor

    <span class="text-dark ms-2" style="font-size:14px;">
        ({{ number_format($item->review->rating, 1) }})
    </span>
</div>

                                {{-- Comment --}}
                                @if($item->review->comment)
                                    <div class="mb-2">
                                        {{ $item->review->comment }}
                                    </div>
                                @endif

                                {{-- Media --}}
                                @if($item->review->media->count())
                                    <div class="d-flex gap-2 flex-wrap">
                                        @foreach($item->review->media as $media)

                                            @if($media->file_type == 'image')
                                                <img src="{{ asset('storage/'.$media->file_path) }}"
                                                     width="80"
                                                     height="80"
                                                     style="object-fit:cover;border-radius:6px">
                                            @else
                                                <video width="120" controls style="border-radius:6px">
                                                    <source src="{{ asset('storage/'.$media->file_path) }}">
                                                </video>
                                            @endif

                                        @endforeach
                                    </div>
                                @endif

                            </div>
                        @endif

                    </div>
                @endif

            </div>

        @endforeach


        {{-- ================= TỔNG TIỀN ================= --}}
        <div class="text-end mt-3">

    <div>
        Tạm tính:
        <strong>{{ number_format($order->subtotal) }}đ</strong>
    </div>

    {{-- Voucher --}}
    @if($order->voucher_discount > 0)
        <div class="text-success">
            Voucher:
            - {{ number_format($order->voucher_discount) }}đ
        </div>
    @endif

    {{-- Sinh nhật --}}
    @if($order->birthday_discount > 0)
        <div class="text-success">
            Ưu đãi sinh nhật:
            - {{ number_format($order->birthday_discount) }}đ
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

document.querySelectorAll('.btn-cancel').forEach(function(button){

button.addEventListener('click', function(){

let form = this.closest('.cancel-form');
let input = form.querySelector('.cancel-reason');

Swal.fire({
title: 'Huỷ đơn hàng',
input: 'textarea',
inputLabel: 'Lý do huỷ đơn',
inputPlaceholder: 'Ví dụ: đặt nhầm, muốn đổi sản phẩm...',
icon: 'warning',
showCancelButton: true,
confirmButtonText: 'Xác nhận huỷ',
cancelButtonText: 'Không',
confirmButtonColor: '#e74c3c',
cancelButtonColor: '#6c757d',
reverseButtons: true,

inputValidator: (value) => {
    if (!value) {
        return 'Bạn cần nhập lý do huỷ!';
    }
}

}).then((result) => {

if (result.isConfirmed){

input.value = result.value;

form.submit();

}

});

});

});

</script>
@endsection
