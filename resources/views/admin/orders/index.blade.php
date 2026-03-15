@extends('layouts.admin')
@section('title','Quản lý đơn hàng')

@section('content')

<div class="card border-0 shadow-sm">
<div class="card-body">

<div class="d-flex justify-content-between align-items-center mb-4">
<div>
<h5 class="fw-bold mb-1">Quản lý đơn hàng</h5>
<small class="text-muted">Danh sách đơn hàng trong hệ thống</small>
</div>
</div>

<form method="GET" class="row g-2 mb-4 align-items-center">

<div class="col-md-3">
<input type="text"
name="keyword"
value="{{ request('keyword') }}"
class="form-control form-control-sm"
placeholder="Mã đơn / tên / SĐT">
</div>

<div class="col-md-2">
<select name="status" class="form-select form-select-sm">

<option value="">Trạng thái đơn</option>

<option value="1" {{ request('status') == 1 ? 'selected' : '' }}>
Đang xử lý
</option>

<option value="2" {{ request('status') == 2 ? 'selected' : '' }}>
Đang giao
</option>

<option value="3" {{ request('status') == 3 ? 'selected' : '' }}>
Đã giao
</option>

<option value="4" {{ request('status') == 4 ? 'selected' : '' }}>
Đã huỷ
</option>

</select>
</div>

<div class="col-md-2">
<select name="payment_status" class="form-select form-select-sm">

<option value="">Thanh toán</option>

<option value="paid" {{ request('payment_status')=='paid'?'selected':'' }}>
Đã thanh toán
</option>

<option value="unpaid" {{ request('payment_status')=='unpaid'?'selected':'' }}>
Chưa thanh toán
</option>

<option value="refunded" {{ request('payment_status')=='refunded'?'selected':'' }}>
Đã hoàn tiền
</option>

<option value="failed" {{ request('payment_status')=='failed'?'selected':'' }}>
Thanh toán thất bại
</option>

</select>
</div>

<div class="col-md-2">
<select name="sort" class="form-select form-select-sm">

<option value="">Sắp xếp</option>

<option value="newest" {{ request('sort')=='newest'?'selected':'' }}>
Mới → Cũ
</option>

<option value="oldest" {{ request('sort')=='oldest'?'selected':'' }}>
Cũ → Mới
</option>

</select>
</div>

<div class="col-md-3 d-flex gap-2">

<button class="btn btn-outline-primary btn-sm">
<i class="bi bi-search"></i> Lọc
</button>

<a href="{{ route('admin.orders.index') }}"
class="btn btn-outline-secondary btn-sm">
Đặt lại </a>

</div>

</form>

<div class="table-responsive">

<table class="table table-hover align-middle mb-0">

<thead class="table-light">
<tr>
<th style="width:80px">ID</th>
<th>Khách hàng</th>
<th style="width:120px">Phương thức</th>
<th style="width:140px">Tổng tiền</th>
<th style="width:140px">Thanh toán</th>
<th style="width:170px">Trạng thái</th>
<th style="width:170px">Ngày đặt</th>
<th style="width:120px"></th>
</tr>
</thead>

<tbody>

@forelse($orders as $order)

<tr>

<td class="fw-semibold">#{{ $order->id }}</td>

<td>

<strong>
{{ $order->receiver_name ?? optional($order->user)->name }}
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

<td>

@if($order->payment_method == 'cod')

<span class="badge bg-secondary">
COD
</span>

@elseif($order->payment_method == 'vnpay')

<span class="badge bg-primary">
VNPAY
</span>

@else

<span class="badge bg-info text-dark">
{{ strtoupper($order->payment_method) }}
</span>

@endif

</td>

<td class="fw-bold text-danger">

{{ number_format($order->grand_total,0,',','.') }}đ

</td>

<td>

<span class="badge bg-{{ $order->payment_status_badge }}">
{{ $order->payment_status_name }}
</span>

</td>

<td>

<span class="badge bg-{{ $order->status_badge }}">
{{ $order->status_name }}
</span>

@if($order->status == 3 && !$order->customer_confirmed)

<br>

<small class="text-info">
Chờ khách xác nhận
</small>

@endif

@if($order->status == 4 && $order->cancel_reason)

<br>

<small class="text-muted">
Lý do: {{ $order->cancel_reason }}
</small>

@endif

</td>

<td class="text-muted">

{{ $order->created_at->format('d/m/Y H:i') }}

</td>

<td>

<a href="{{ route('admin.orders.show',$order->id) }}"
class="btn btn-sm btn-primary">

Chi tiết

</a>

</td>

</tr>

@empty

<tr>

<td colspan="8"
class="text-center text-muted py-4">

Chưa có đơn hàng

</td>

</tr>

@endforelse

</tbody>

</table>

</div>

@if($orders->hasPages())

<div class="mt-4">
{{ $orders->withQueryString()->links('pagination::bootstrap-5') }}
</div>

@endif

</div>
</div>

@endsection
