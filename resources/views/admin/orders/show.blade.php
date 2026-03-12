@extends('layouts.admin')
@section('title','Chi tiết đơn')

@section('content')

<h4 class="mb-3 fw-bold">
    Đơn hàng #{{ $order->id }}
</h4>

<div class="row">

{{-- ======================================
    CỘT TRÁI - THÔNG TIN ĐƠN
====================================== --}}
<div class="col-md-4">

    {{-- Thông tin giao hàng --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body">

            <h6 class="fw-bold mb-3">Thông tin giao hàng</h6>

            <p class="mb-1">
                <strong>Khách:</strong>
                {{ $order->receiver_name ?? $order->user->name }}
            </p>

            <p class="mb-1">
                <strong>SĐT:</strong>
                {{ $order->receiver_phone }}
            </p>

            <p class="mb-2">
                <strong>Địa chỉ:</strong>
                {{ $order->receiver_address }}
            </p>

            <hr>

            <p class="mb-2">
                <strong>Thanh toán:</strong><br>
                {{ $order->payment_method_name }}<br>
                <span class="badge bg-{{ $order->payment_status_badge }}">
                    {{ $order->payment_status_name }}
                </span>
            </p>

            @if($order->status == 4)

<div class="alert alert-danger mt-3">

<strong>Đơn hàng đã bị huỷ</strong>

<hr class="my-2">

<div>
<strong>Người huỷ:</strong>
@if($order->cancelled_by == 'admin')
    Admin
@else
    Khách hàng
@endif
</div>

<div>
<strong>Thời gian:</strong>
{{ \Carbon\Carbon::parse($order->cancelled_at)->format('d/m/Y H:i') }}
</div>

<div>
<strong>Lý do:</strong>
{{ $order->cancel_reason }}
</div>

</div>

@endif

            <hr>

            <p class="mb-1">
                <strong>Ngày đặt:</strong><br>
                {{ $order->created_at->format('d/m/Y H:i') }}
            </p>

            @if($order->status == 3 && $order->delivered_at)
            <p class="mb-2">
                <strong>Ngày giao:</strong><br>
                {{ \Carbon\Carbon::parse($order->delivered_at)->format('d/m/Y H:i') }}
            </p>
            @endif

            <hr>

            <p class="mb-0">
                <strong>Tổng khách trả:</strong><br>
                <span class="fs-5 text-danger fw-bold">
                    {{ number_format($order->grand_total) }}đ
                </span>
            </p>

        </div>
    </div>


    {{-- Cập nhật trạng thái --}}
    @if($order->status == 1 || $order->status == 2)
    <div class="card shadow-sm mb-3">
        <div class="card-body">

            <form method="POST"
                  action="{{ route('admin.orders.updateStatus', $order->id) }}">
                @csrf

                <label class="mb-2 fw-bold">Cập nhật trạng thái</label>

                <select name="status" class="form-select mb-3">
                    @if($order->status == 1)
                        <option value="2">Đang giao</option>
                    @endif

                    @if($order->status == 2)
                        <option value="3">Đã giao</option>
                    @endif
                </select>

                <button class="btn btn-success w-100">
                    Cập nhật
                </button>
            </form>

        </div>
    </div>
    @endif


    {{-- Admin huỷ đơn --}}
    @if($order->status == 1)
    <div class="card shadow-sm">
        <div class="card-body">

            <form method="POST"
                  action="{{ route('admin.orders.cancel', $order->id) }}">
                @csrf

                <label class="mb-2 text-danger fw-bold">
                    Huỷ đơn (Admin)
                </label>

                <textarea name="cancel_reason"
                          class="form-control mb-3"
                          placeholder="Nhập lý do huỷ..."
                          required></textarea>

                <button class="btn btn-danger w-100">
                    Huỷ đơn
                </button>

            </form>

        </div>
    </div>
    @endif

</div>


{{-- ======================================
    CỘT PHẢI - SẢN PHẨM + TỔNG TIỀN
====================================== --}}
<div class="col-md-8">
    <div class="card shadow-sm">
        <div class="card-body">

            <h6 class="fw-bold mb-3">Sản phẩm</h6>

            @foreach($order->items as $item)

            @php
                $variantImage = optional($item->variant->images->first())->image_path
                    ?? optional($item->variant->product->images->first())->image_path
                    ?? 'no-image.png';
            @endphp

            <div class="d-flex align-items-center border-bottom py-3">

                <div class="me-3">
                    <img src="{{ asset('storage/' . $variantImage) }}"
                         width="70"
                         height="70"
                         style="object-fit:cover; border-radius:6px;">
                </div>

                <div class="flex-grow-1">
                    <div class="fw-bold">
                        {{ $item->variant->product->name }}
                    </div>

                    <small class="text-muted">
                        {{ $item->variant->attribute_name }}:
                        {{ $item->variant->attribute_value }}
                    </small>

                    <div class="text-muted">
                        Số lượng: {{ $item->quantity }}
                    </div>
                </div>

                <div class="text-end fw-bold text-danger">
                    {{ number_format($item->price * $item->quantity) }}đ
                </div>

            </div>

            @endforeach

            <hr>

            {{-- Tổng tiền chuẩn --}}
            <div class="text-end">

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

                <h5 class="text-danger fw-bold mt-2">
                    Tổng khách trả:
                    {{ number_format($order->grand_total) }}đ
                </h5>

                <small class="text-muted">
                    (Doanh thu: {{ number_format($order->total + $order->shipping_fee) }}đ)
                </small>

            </div>

        </div>
    </div>
</div>


</div>

@endsection
