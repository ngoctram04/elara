@extends('layouts.frontend')
@section('title','Chi tiết đơn hàng')

@section('content')
<div class="container py-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Đơn hàng #{{ $order->id }}</h4>

        @if($order->status == 1)
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
                <span class="badge 
                    @if($order->status == 1) bg-warning
                    @elseif($order->status == 2) bg-info
                    @elseif($order->status == 3) bg-success
                    @else bg-danger
                    @endif">
                    {{ $order->status_name }}
                </span>
            </div>

            <div class="col-md-4">
                <p class="mb-1 text-muted">Tổng tiền</p>
                <b class="text-danger fs-5">
                    {{ number_format($order->total) }}đ
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

            <p class="mb-1">
                <b>Người nhận:</b> 
                {{ $order->receiver_name ?? 'Chưa cập nhật' }}
            </p>

            <p class="mb-1">
                <b>SĐT:</b> 
                {{ $order->receiver_phone ?? 'Chưa cập nhật' }}
            </p>

            <p class="mb-1">
                <b>Địa chỉ:</b> 
                {{ $order->receiver_address ?? 'Chưa cập nhật' }}
            </p>

            <p class="mb-0">
                <b>Thanh toán:</b>
                {{ $order->payment_method == 'cod' ? 'Thanh toán khi nhận hàng' : 'Chuyển khoản' }}
            </p>
        </div>
    </div>


    {{-- ================= DANH SÁCH SẢN PHẨM ================= --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">

            <h6 class="fw-bold mb-3">Sản phẩm</h6>

            @foreach($order->items as $item)
                <div class="d-flex justify-content-between align-items-center border-bottom py-3">

                    <div class="d-flex align-items-center">

                        {{-- ẢNH VARIANT --}}
                        <img 
                            src="{{ $item->variant->mainImage 
                                    ? asset('storage/' . $item->variant->mainImage->image_path) 
                                    : asset('images/no-image.png') }}"
                            width="70"
                            height="70"
                            class="rounded me-3"
                            style="object-fit:cover"
                        >

                        <div>
                            <b>{{ $item->variant->product->name }}</b><br>

                            <small class="text-muted">
                                {{ $item->variant->attribute_name }}:
                                {{ $item->variant->attribute_value }}
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

            {{-- Tổng --}}
            <div class="d-flex justify-content-between mt-3 fs-5">
                <b>Tổng cộng</b>
                <b class="text-danger">{{ number_format($order->total) }}đ</b>
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