@extends('layouts.admin')

@section('title','Quản lý đánh giá')

@section('content')

<div class="container-fluid">

<h4 class="mb-4">
<i class="bi bi-star-fill text-warning"></i>
Quản lý đánh giá
</h4>


{{-- ================= THỐNG KÊ ================= --}}
<div class="row mb-4">

@for($i=5;$i>=1;$i--)

<div class="col-md-2">

<div class="card text-center shadow-sm border-0">

<div class="card-body">

<h6 class="text-warning">{{ $i }}⭐</h6>

<h4>
{{ $reviews->where('rating',$i)->count() }}
</h4>

</div>

</div>

</div>

@endfor

</div>



{{-- ================= BỘ LỌC ================= --}}
<div class="card shadow-sm border-0 mb-3">

<div class="card-body">

<form method="GET">

<div class="row g-2">

{{-- SEARCH --}}
<div class="col-md-3">

<input type="text"
name="keyword"
class="form-control"
placeholder="Tìm khách hàng, sản phẩm..."
value="{{ request('keyword') }}">

</div>


{{-- RATING --}}
<div class="col-md-2">

<select name="rating" class="form-select">

<option value="">Tất cả sao</option>

<option value="5" {{ request('rating')==5?'selected':'' }}>5⭐</option>
<option value="4" {{ request('rating')==4?'selected':'' }}>4⭐</option>
<option value="3" {{ request('rating')==3?'selected':'' }}>3⭐</option>
<option value="2" {{ request('rating')==2?'selected':'' }}>2⭐</option>
<option value="1" {{ request('rating')==1?'selected':'' }}>1⭐</option>

</select>

</div>


{{-- TRẠNG THÁI HIỂN THỊ --}}
<div class="col-md-2">

<select name="visible" class="form-select">

<option value="">Trạng thái</option>

<option value="1" {{ request('visible')==='1'?'selected':'' }}>
Hiển thị
</option>

<option value="0" {{ request('visible')==='0'?'selected':'' }}>
Đã ẩn
</option>

</select>

</div>


{{-- LỌC TRẢ LỜI --}}
<div class="col-md-2">

<select name="reply" class="form-select">

<option value="">--Tất cả--</option>

<option value="replied"
{{ request('reply')=='replied'?'selected':'' }}>
Đã trả lời
</option>

<option value="pending"
{{ request('reply')=='pending'?'selected':'' }}>
Chưa trả lời
</option>

</select>

</div>


{{-- BUTTON --}}
<div class="col-md-1">

<button class="btn btn-primary w-100">

<i class="bi bi-search"></i>

</button>

</div>


<div class="col-md-2">

<a href="{{ route('admin.reviews.index') }}"
class="btn btn-secondary w-100">

Reset

</a>

</div>

</div>

</form>

</div>

</div>



{{-- ================= TABLE ================= --}}
<div class="card shadow-sm border-0">

<div class="card-body">

<table class="table table-hover align-middle">

<thead class="table-light">

<tr>

<th>ID</th>
<th>Khách</th>
<th>Sản phẩm</th>
<th width="110">Rating</th>
<th width="200">Nội dung</th>
<th>Trả lời</th>
<th>Trạng thái</th>
<th width="110">Hành động</th>

</tr>

</thead>

<tbody>

@forelse($reviews as $review)

<tr>

<td>{{ $review->id }}</td>


<td>

<strong>
{{ $review->user->name ?? 'N/A' }}
</strong>

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
class="btn btn-sm btn-primary">

<i class="bi bi-eye"></i>

</a>


<form action="{{ route('admin.reviews.toggle',$review->id) }}"
method="POST"
class="d-inline">

@csrf

<button class="btn btn-sm btn-warning">

<i class="bi bi-eye-slash"></i>

</button>

</form>

</td>

</tr>

@empty

<tr>

<td colspan="8" class="text-center text-muted">

Không có đánh giá

</td>

</tr>

@endforelse

</tbody>

</table>


{{-- PAGINATION --}}
<div class="mt-3">

{{ $reviews->links() }}

</div>


</div>

</div>

</div>

@endsection