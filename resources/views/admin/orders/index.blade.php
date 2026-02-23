@extends('layouts.admin')
@section('title','Quản lý đơn hàng')

@section('content')

<h4 class="mb-3 fw-bold">
    <i class="bi bi-cart me-2"></i> Quản lý đơn hàng
</h4>

<div class="card shadow-sm">
    <div class="card-body">

        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#ID</th>
                    <th>Khách hàng</th>
                    <th>Tổng tiền</th>
                    <th>Thanh toán</th>
                    <th>Trạng thái</th>
                    <th>Ngày</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td>#{{ $order->id }}</td>

                    <td>
                        {{ $order->receiver_name ?? $order->user->name }}
                    </td>

                    <td class="fw-bold text-danger">
                        {{ number_format($order->total) }}đ
                    </td>

                    <td>
                        <span class="badge bg-{{ $order->payment_status ? 'success' : 'secondary' }}">
                            {{ $order->payment_status_name }}
                        </span>
                    </td>

                    <td>
                        <span class="badge bg-{{ $order->status_badge }}">
                            {{ $order->status_name }}
                        </span>
                    </td>

                    <td>
                        {{ $order->created_at->format('d/m/Y H:i') }}
                    </td>

                    <td>
                        <a href="{{ route('admin.orders.show', $order->id) }}"
                           class="btn btn-sm btn-primary">
                            Chi tiết
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        Chưa có đơn hàng
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{ $orders->links() }}

    </div>
</div>

@endsection