@extends('layouts.admin')

@section('title','Chi tiết khách hàng')

@section('content')

{{-- ================= KHÁCH HÀNG ================= --}}

<div class="card shadow-sm border-0 mb-4">
<div class="card-body">

<div class="d-flex justify-content-between align-items-center mb-4">

<h5 class="fw-semibold mb-0">
Chi tiết khách hàng
</h5>

<a href="{{ route('admin.customers.index') }}"
class="btn btn-sm btn-outline-secondary">
← Quay lại </a>

</div>

<div class="row align-items-center">

{{-- AVATAR --}}

<div class="col-md-3 text-center">

<img
src="{{ $user->avatar ? asset('storage/'.$user->avatar) : asset('images/default-avatar.png') }}"
class="rounded-circle shadow-sm mb-3"
style="width:120px;height:120px;object-fit:cover"

>

<h6 class="fw-semibold mb-1">
{{ $user->name }}
</h6>

@if($user->is_active) <span class="badge bg-success">Hoạt động</span>
@else <span class="badge bg-danger">Đã khóa</span>
@endif

</div>

{{-- THÔNG TIN --}}

<div class="col-md-9">

<div class="row g-3">

<div class="col-md-6">
<div class="bg-light rounded p-3">

<div class="text-muted small">
Email
</div>

<div class="fw-semibold">
{{ $user->email }}
</div>

</div>
</div>

<div class="col-md-6">
<div class="bg-light rounded p-3">

<div class="text-muted small">
Số điện thoại
</div>

<div class="fw-semibold">
{{ $user->phone ?? '—' }}
</div>

</div>
</div>

<div class="col-md-6">
<div class="bg-light rounded p-3">

<div class="text-muted small">
Ngày tham gia
</div>

<div class="fw-semibold">
{{ $user->created_at->format('d/m/Y') }}
</div>

</div>
</div>

<div class="col-md-6">
<div class="bg-light rounded p-3">

<div class="text-muted small">
Hạng thành viên
</div>

<div>

@switch($user->member_level)

@case('bronze') <span class="badge bg-secondary">Đồng</span>
@break

@case('silver') <span class="badge bg-info text-dark">Bạc</span>
@break

@case('gold') <span class="badge bg-warning text-dark">Vàng</span>
@break

@case('diamond') <span class="badge bg-primary">Kim cương</span>
@break

@default <span class="text-muted">—</span>

@endswitch

</div>

</div>
</div>

<div class="col-md-6">
<div class="bg-light rounded p-3">

<div class="text-muted small">
Điểm hiện có
</div>

<div class="fw-semibold">
{{ number_format($user->loyalty_points) }}
</div>

</div>
</div>

<div class="col-md-6">
<div class="bg-light rounded p-3">

<div class="text-muted small">
Tổng chi tiêu
</div>

<div class="fw-semibold text-success">
{{ number_format($user->total_spent) }} đ
</div>

</div>
</div>

</div>

</div>

</div>

</div>
</div>

{{-- ================= LỊCH SỬ MUA ================= --}}

<div class="card shadow-sm border-0 mb-4">
<div class="card-body">

<h6 class="fw-semibold mb-3">
Lịch sử mua hàng
</h6>

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead>
<tr>
<th>Mã đơn</th>
<th>Ngày</th>
<th>Tổng tiền</th>
<th>Trạng thái</th>
<th></th>
</tr>
</thead>

<tbody id="orderTable">

@foreach($orders as $key => $order)

<tr class="order-row {{ $key >= 5 ? 'd-none extra-order' : '' }}">

<td>#{{ $order->id }}</td>

<td>
{{ $order->created_at->format('d/m/Y') }}
</td>

<td>
{{ number_format($order->grand_total) }} đ
</td>

<td>

@switch((int)$order->status)

@case(1)
<span class="badge bg-secondary">Đang xử lý</span>
@break

@case(2)
<span class="badge bg-info">Đang giao</span>
@break

@case(3)
<span class="badge bg-success">Đã giao</span>
@break

@case(4)
<span class="badge bg-danger">Đã huỷ</span>
@break

@case(5)
<span class="badge bg-warning text-dark">Hoàn hàng</span>
@break

@default
<span class="badge bg-light text-dark">
{{ $order->status }}
</span>

@endswitch

</td>

<td>

<a href="{{ route('admin.orders.show',$order->id) }}"
class="btn btn-sm btn-primary">
Xem </a>

</td>

</tr>

@endforeach

</tbody>

</table>

</div>

@if($orders->count() > 5)

<div class="text-center">

<button class="btn btn-sm btn-outline-primary"
onclick="toggleOrders()"
id="toggleOrderBtn">

Xem tất cả

</button>

</div>

@endif

</div>
</div>

{{-- ================= ĐÁNH GIÁ ================= --}}

<div class="card shadow-sm border-0">
<div class="card-body">

<h6 class="fw-semibold mb-3">
Sản phẩm đã đánh giá
</h6>

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead>
<tr>
<th>Sản phẩm</th>
<th>Điểm</th>
<th>Nội dung</th>
<th>Ngày</th>
</tr>
</thead>

<tbody>

@foreach($reviews as $key => $review)

<tr class="{{ $key >= 5 ? 'd-none extra-review' : '' }}">

<td>
{{ $review->product->name }}
</td>

<td>

@for($i=1;$i<=5;$i++)
@if($i <= $review->rating)
⭐
@else
☆
@endif
@endfor

</td>

<td>
{{ $review->content }}
</td>

<td>
{{ $review->created_at->format('d/m/Y') }}
</td>

</tr>

@endforeach

</tbody>

</table>

</div>

@if($reviews->count() > 5)

<div class="text-center">

<button class="btn btn-sm btn-outline-primary"
onclick="toggleReviews()"
id="toggleReviewBtn">

Xem tất cả

</button>

</div>

@endif

</div>
</div>

@endsection

@push('scripts')

<script>

function toggleOrders(){

let rows = document.querySelectorAll('.extra-order')
let btn = document.getElementById('toggleOrderBtn')

rows.forEach(row => row.classList.toggle('d-none'))

btn.innerText = btn.innerText === 'Xem tất cả'
? 'Thu gọn'
: 'Xem tất cả'

}


function toggleReviews(){

let rows = document.querySelectorAll('.extra-review')
let btn = document.getElementById('toggleReviewBtn')

rows.forEach(row => row.classList.toggle('d-none'))

btn.innerText = btn.innerText === 'Xem tất cả'
? 'Thu gọn'
: 'Xem tất cả'

}

</script>

@endpush
