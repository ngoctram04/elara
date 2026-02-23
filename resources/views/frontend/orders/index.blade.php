@extends('layouts.frontend')
@section('title','Lịch sử đơn hàng')

@section('content')
<div class="container py-4">

    <h4 class="mb-4 fw-bold">Lịch sử đơn hàng</h4>

    @forelse($orders as $order)
        <div class="card mb-3 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">

                <div>
                    <div><b>Mã đơn:</b> #{{ $order->id }}</div>
                    <div><b>Ngày:</b> {{ $order->created_at->format('d/m/Y H:i') }}</div>
                    <div>
                        <b>Trạng thái:</b>
                        <span class="badge bg-secondary">
                            {{ $order->status_name }}
                        </span>
                    </div>
                </div>

                <div class="text-end">
                    <div class="fw-bold text-danger mb-2">
                        {{ number_format($order->total) }}đ
                    </div>

                    <a href="{{ route('orders.show', $order->id) }}"
                       class="btn btn-sm btn-primary">
                        Xem chi tiết
                    </a>
                </div>

            </div>
        </div>
    @empty
        <div class="alert alert-info">
            Bạn chưa có đơn hàng nào.
        </div>
    @endforelse

    {{ $orders->links() }}

</div>
@endsection