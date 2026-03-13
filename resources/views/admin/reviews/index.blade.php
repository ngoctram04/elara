@extends('layouts.admin')

@section('title','Quản lý đánh giá')

@section('content')

<div class="card border-0 shadow-sm">
<div class="card-body">

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h5 class="fw-bold mb-1">
Quản lý đánh giá
</h5>

<small class="text-muted">
Quản lý các đánh giá của khách hàng
</small>
</div>

</div>


{{-- ================= BỘ LỌC ================= --}}
<form method="GET" class="row g-2 mb-4 align-items-center">

<div class="col-md-3">
<input type="text"
name="keyword"
class="form-control form-control-sm"
placeholder="Tìm khách hàng, sản phẩm..."
value="{{ request('keyword') }}">
</div>

<div class="col-md-2">
<select name="rating" class="form-select form-select-sm">
<option value="">Tất cả sao</option>
<option value="5" {{ request('rating')==5?'selected':'' }}>5⭐</option>
<option value="4" {{ request('rating')==4?'selected':'' }}>4⭐</option>
<option value="3" {{ request('rating')==3?'selected':'' }}>3⭐</option>
<option value="2" {{ request('rating')==2?'selected':'' }}>2⭐</option>
<option value="1" {{ request('rating')==1?'selected':'' }}>1⭐</option>
</select>
</div>

<div class="col-md-2">
<select name="visible" class="form-select form-select-sm">
<option value="">Trạng thái</option>
<option value="1" {{ request('visible')==='1'?'selected':'' }}>Hiển thị</option>
<option value="0" {{ request('visible')==='0'?'selected':'' }}>Đã ẩn</option>
</select>
</div>

<div class="col-md-2">
<select name="reply" class="form-select form-select-sm">
<option value="">--Tất cả--</option>
<option value="replied" {{ request('reply')=='replied'?'selected':'' }}>Đã trả lời</option>
<option value="pending" {{ request('reply')=='pending'?'selected':'' }}>Chưa trả lời</option>
</select>
</div>

<div class="col-md-3 d-flex gap-2">

<button class="btn btn-outline-primary btn-sm">
<i class="bi bi-search"></i> Lọc
</button>

<a href="{{ route('admin.reviews.index') }}"
class="btn btn-outline-secondary btn-sm">
Đặt lại
</a>

</div>

</form>


{{-- ================= TABLE ================= --}}
<div class="table-responsive">

<table class="table table-hover align-middle mb-0">

<thead class="table-light">

<tr>
<th>Mã</th>
<th>Khách</th>
<th>Sản phẩm</th>
<th width="110">Rating</th>
<th width="200">Nội dung</th>
<th>Trả lời</th>
<th>Trạng thái</th>
<th width="160">Hành động</th>
</tr>

</thead>

<tbody>

@forelse($reviews as $review)

<tr>

<td>#{{ $review->id }}</td>

<td>
<strong>{{ $review->user->name ?? 'N/A' }}</strong>
</td>

<td>
{{ $review->product->name ?? 'N/A' }}
</td>

<td>
<span class="text-warning">
@for($i=1;$i<=5;$i++)
@if($i <= $review->rating)
<i class="bi bi-star-fill"></i>
@else
<i class="bi bi-star"></i>
@endif
@endfor
</span>
</td>

<td>
{{ Str::limit($review->comment,80) }}
</td>

<td>

@if($review->admin_reply)

<span class="badge bg-success">
Đã trả lời
</span>

@else

<span class="badge bg-warning text-dark">
Chưa trả lời
</span>

@endif

</td>

<td>

@if($review->is_visible)

<span class="badge bg-success">
Hiển thị
</span>

@else

<span class="badge bg-secondary">
Đã ẩn
</span>

@endif

</td>

<td>

<a href="{{ route('admin.reviews.show',$review->id) }}"
class="btn btn-sm btn-outline-primary">
Xem chi tiết
</a>

<form action="{{ route('admin.reviews.toggle',$review->id) }}"
method="POST"
class="d-inline">
@csrf

<button class="btn btn-sm btn-outline-warning">
<i class="bi bi-eye-slash"></i>
</button>

</form>

</td>

</tr>

@empty

<tr>
<td colspan="8" class="text-center text-muted py-4">
Không có đánh giá
</td>
</tr>

@endforelse

</tbody>

</table>

</div>


{{-- PAGINATION --}}
<div class="mt-3">
{{ $reviews->links() }}
</div>


</div>
</div>

@endsection