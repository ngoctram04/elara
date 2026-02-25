@extends('layouts.admin')
@section('title','Quản lý đơn hàng')

@section('content')

<h4 class="mb-3 fw-bold">
    <i class="bi bi-cart me-2"></i> Quản lý đơn hàng
</h4>

<div class="card shadow-sm">
    <div class="card-body">

        {{-- Bộ lọc --}}
        <form method="GET" class="row g-2 mb-3">
            {{-- Trạng thái đơn --}}
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="1" {{ request('status') == 1 ? 'selected' : '' }}>Đang xử lý</option>
                    <option value="2" {{ request('status') == 2 ? 'selected' : '' }}>Đang giao</option>
                    <option value="3" {{ request('status') == 3 ? 'selected' : '' }}>Đã giao</option>
                    <option value="4" {{ request('status') == 4 ? 'selected' : '' }}>Đã huỷ</option>
                </select>
            </div>

            {{-- Trạng thái thanh toán --}}
            <div class="col-md-3">
                <select name="payment_status" class="form-select">
                    <option value="">-- Trạng thái thanh toán --</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>
                        Đã thanh toán
                    </option>
                    <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>
                        Chưa thanh toán
                    </option>
                    <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>
                        Đã hoàn tiền
                    </option>
                    <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>
                        Thanh toán thất bại
                    </option>
                </select>
            </div>

            <div class="col-md-3">
                <button class="btn btn-primary">Lọc</button>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                    Đặt lại
                </a>
            </div>
        </form>


        {{-- Danh sách --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#ID</th>
                        <th>Khách hàng</th>
                        <th>Phương thức</th>
                        <th>Tổng tiền</th>
                        <th>Thanh toán</th>
                        <th>Trạng thái</th>
                        <th>Ngày đặt</th>
                        <th width="110"></th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        {{-- ID --}}
                        <td>#{{ $order->id }}</td>

                        {{-- Khách --}}
                        <td>
                            <strong>
                                {{ $order->receiver_name ?? $order->user->name }}
                            </strong>
                            <br>
                            <small class="text-muted">
                                {{ $order->receiver_phone }}
                            </small>

                            @if($order->status == 4)
                                <br>
                                <small class="text-danger">
                                    Huỷ bởi:
                                    {{ $order->cancelled_by == 'admin' ? 'Admin' : 'Khách' }}

                                    @if($order->cancelledByUser)
                                        ({{ $order->cancelledByUser->name }})
                                    @endif
                                </small>
                            @endif
                        </td>

                        {{-- Phương thức --}}
                        <td>
                            @if($order->payment_method == 'cod')
                                <span class="badge bg-secondary">COD</span>
                            @elseif($order->payment_method == 'vnpay')
                                <span class="badge bg-primary">VNPAY</span>
                            @else
                                <span class="badge bg-info text-dark">
                                    {{ strtoupper($order->payment_method) }}
                                </span>
                            @endif
                        </td>

                        {{-- Tổng --}}
                        <td class="fw-bold text-danger">
                            {{ number_format($order->total) }}đ
                        </td>

                        {{-- Thanh toán (logic chuẩn từ Model) --}}
                        <td>
                            <span class="badge bg-{{ $order->payment_status_badge }}">
                                {{ $order->payment_status_name }}
                            </span>
                        </td>

                        {{-- Trạng thái đơn --}}
                        <td>
                            <span class="badge bg-{{ $order->status_badge }}">
                                {{ $order->status_name }}
                            </span>

                            @if($order->status == 4 && $order->cancel_reason)
                                <br>
                                <small class="text-muted">
                                    Lý do: {{ $order->cancel_reason }}
                                </small>
                            @endif
                        </td>

                        {{-- Ngày --}}
                        <td>
                            {{ $order->created_at->format('d/m/Y H:i') }}
                        </td>

                        {{-- Action --}}
                        <td>
                            <a href="{{ route('admin.orders.show', $order->id) }}"
                               class="btn btn-sm btn-primary">
                                Chi tiết
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            Chưa có đơn hàng
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $orders->withQueryString()->links() }}
        </div>

    </div>
</div>

@endsection