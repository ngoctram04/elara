@extends('layouts.admin')

@section('title','Chi tiết khách hàng')

@section('content')

<div class="card shadow-sm border-0">
<div class="card-body">

<div class="d-flex justify-content-between align-items-center mb-4">
<h5 class="fw-semibold mb-0">Chi tiết khách hàng</h5>

<a href="{{ route('admin.customers.index') }}"
class="btn btn-sm btn-secondary">
← Quay lại
</a>
</div>

<div class="row">

{{-- AVATAR --}}
<div class="col-md-3 text-center">

<img src="{{ $user->avatar
? asset('storage/'.$user->avatar)
: asset('images/default-avatar.png') }}"
class="rounded-circle border mb-3"
width="120" height="120">

<h6 class="fw-semibold">{{ $user->name }}</h6>

@if($user->is_active)
<span class="badge bg-success">Hoạt động</span>
@else
<span class="badge bg-danger">Đã khóa</span>
@endif

</div>


{{-- THÔNG TIN --}}
<div class="col-md-9">

<table class="table table-borderless">

<tr>
<th width="180">Email</th>
<td>{{ $user->email }}</td>
</tr>

<tr>
<th>Số điện thoại</th>
<td>{{ $user->phone ?? '—' }}</td>
</tr>

<tr>
<th>Ngày tham gia</th>
<td>{{ $user->created_at->format('d/m/Y') }}</td>
</tr>

<tr>
<th>Hạng thành viên</th>
<td>

@switch($user->member_level)

@case('bronze')
<span class="badge bg-secondary">Đồng</span>
@break

@case('silver')
<span class="badge bg-info text-dark">Bạc</span>
@break

@case('gold')
<span class="badge bg-warning text-dark">Vàng</span>
@break

@case('diamond')
<span class="badge bg-primary">Kim cương</span>
@break

@endswitch

</td>
</tr>

<tr>
<th>Điểm hiện có</th>
<td>{{ number_format($user->loyalty_points) }}</td>
</tr>

<tr>
<th>Tổng chi tiêu</th>
<td>{{ number_format($user->total_spent) }} đ</td>
</tr>

</table>

</div>

</div>

</div>
</div>


{{-- LỊCH SỬ MUA HÀNG --}}
<div class="card shadow-sm border-0 mt-4">
<div class="card-body">

<h6 class="fw-semibold mb-3">Lịch sử mua hàng</h6>

<table class="table table-hover">

<thead>
<tr>
<th>Mã đơn</th>
<th>Ngày</th>
<th>Tổng tiền</th>
<th>Trạng thái</th>
<th></th>
</tr>
</thead>

<tbody>

@forelse($orders as $order)

<tr>

<td>#{{ $order->id }}</td>

<td>{{ $order->created_at->format('d/m/Y') }}</td>

<td>{{ number_format($order->grand_total) }} đ</td>

<td>

@switch($order->status)

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

@endswitch

</td>

<td>
<a href="{{ route('admin.orders.show',$order->id) }}"
class="btn btn-sm btn-primary">
Xem
</a>
</td>

</tr>

@empty

<tr>
<td colspan="5" class="text-center text-muted">
Chưa có đơn hàng
</td>
</tr>

@endforelse

</tbody>

</table>

</div>
</div>


{{-- SẢN PHẨM ĐÃ ĐÁNH GIÁ --}}
<div class="card shadow-sm border-0 mt-4">
<div class="card-body">

<h6 class="fw-semibold mb-3">Sản phẩm đã đánh giá</h6>

<table class="table table-hover">

<thead>
<tr>
<th>Sản phẩm</th>
<th>Điểm</th>
<th>Nội dung</th>
<th>Ngày</th>
</tr>
</thead>

<tbody>

@forelse($reviews as $review)

<tr>

<td>{{ $review->product->name }}</td>

<td>

@for($i=1;$i<=5;$i++)
@if($i <= $review->rating)
⭐
@else
☆
@endif
@endfor

</td>

<td>{{ $review->content }}</td>

<td>{{ $review->created_at->format('d/m/Y') }}</td>

</tr>

@empty

<tr>
<td colspan="4" class="text-center text-muted">
Chưa có đánh giá
</td>
</tr>

@endforelse

</tbody>

</table>

</div>
</div>

@endsection