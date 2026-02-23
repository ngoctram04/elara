@extends('layouts.admin')
@section('title','Chi tiết đơn')

@section('content')

<h4 class="mb-3 fw-bold">
    Đơn hàng #{{ $order->id }}
</h4>

<div class="row">

    {{-- Thông tin đơn --}}
    <div class="col-md-4">
        <div class="card shadow-sm mb-3">
            <div class="card-body">

                <p><strong>Khách:</strong> {{ $order->receiver_name }}</p>
                <p><strong>SĐT:</strong> {{ $order->receiver_phone }}</p>
                <p><strong>Địa chỉ:</strong> {{ $order->receiver_address }}</p>

                <hr>

                <p>
                    <strong>Thanh toán:</strong><br>
                    {{ $order->payment_method_name }}<br>
                    <span class="badge bg-{{ $order->payment_status ? 'success' : 'secondary' }}">
                        {{ $order->payment_status_name }}
                    </span>
                </p>

                <p>
                    <strong>Trạng thái:</strong><br>
                    <span class="badge bg-{{ $order->status_badge }}">
                        {{ $order->status_name }}
                    </span>
                </p>

            </div>
        </div>

        {{-- Cập nhật trạng thái --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST"
                      action="{{ route('admin.orders.updateStatus', $order->id) }}">
                    @csrf

                    <label class="mb-2">Cập nhật trạng thái</label>

                    <select name="status" class="form-select mb-3">
                        <option value="1" {{ $order->status==1?'selected':'' }}>Chờ xử lý</option>
                        <option value="2" {{ $order->status==2?'selected':'' }}>Đang giao</option>
                        <option value="3" {{ $order->status==3?'selected':'' }}>Hoàn thành</option>
                        <option value="4" {{ $order->status==4?'selected':'' }}>Đã huỷ</option>
                    </select>

                    <button class="btn btn-success w-100">
                        Cập nhật
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Danh sách sản phẩm --}}
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">

                <h6 class="mb-3">Sản phẩm</h6>

                @foreach($order->items as $item)
                <div class="d-flex justify-content-between border-bottom py-2">
                    <div>
                        <strong>
                            {{ $item->variant->product->name }}
                        </strong><br>

                        <small>
                            {{ $item->variant->attribute_value ?? '' }}
                            × {{ $item->quantity }}
                        </small>
                    </div>

                    <div class="fw-bold">
                        {{ number_format($item->price * $item->quantity) }}đ
                    </div>
                </div>
                @endforeach

                <hr>

                <h5 class="text-end text-danger">
                    Tổng: {{ number_format($order->total) }}đ
                </h5>

            </div>
        </div>
    </div>

</div>

@endsection