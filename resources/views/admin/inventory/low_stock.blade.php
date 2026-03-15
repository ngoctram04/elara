@extends('layouts.admin')

@section('title','Sản phẩm sắp hết hàng')

@section('content')

<div class="card border-0 shadow-sm">

<div class="card-body">

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h5 class="fw-bold mb-1">
Sản phẩm sắp hết hàng
</h5>

<small class="text-muted">
Danh sách các biến thể có tồn kho thấp
</small>
</div>

<span class="badge bg-danger">
{{ $variants->total() }} sản phẩm
</span>

</div>


{{-- FILTER --}}
<form method="GET" class="row g-2 mb-4">

<div class="col-md-4">

<input
type="text"
name="keyword"
value="{{ request('keyword') }}"
class="form-control form-control-sm"
placeholder="Tìm sản phẩm hoặc biến thể...">

</div>


<div class="col-md-3">

<select name="sort" class="form-select form-select-sm">

<option value="">Tồn kho thấp → cao</option>

<option value="high"
{{ request('sort')=='high'?'selected':'' }}>
Tồn kho cao → thấp
</option>

<option value="all"
{{ request('sort')=='all'?'selected':'' }}>
Hiển thị tất cả
</option>

</select>

</div>


<div class="col-md-3 d-flex gap-2">

<button class="btn btn-outline-primary btn-sm">
<i class="bi bi-search"></i>
Lọc
</button>

<a href="{{ route('admin.inventory.low') }}"
class="btn btn-outline-secondary btn-sm">
Đặt lại
</a>

</div>

</form>


{{-- ALERT --}}
<div class="alert alert-warning mb-4">

<i class="bi bi-exclamation-triangle me-1"></i>

Những sản phẩm có tồn kho ≤ <b>5</b> sẽ hiển thị tại đây để quản trị viên kịp thời nhập thêm hàng.

</div>


{{-- TABLE --}}
<div class="table-responsive">

<table class="table table-hover align-middle mb-0">

<thead class="table-light">

<tr>

<th style="width:70px" class="text-center">
STT
</th>

<th>
Sản phẩm
</th>

<th>
Biến thể
</th>

<th style="width:120px" class="text-center">
Tồn kho
</th>

<th style="width:140px" class="text-center">
Trạng thái
</th>

<th style="width:150px" class="text-center">
Hành động
</th>

</tr>

</thead>

<tbody>

@forelse($variants as $index => $v)

<tr>

<td class="text-center text-muted fw-semibold">

{{ $variants->firstItem() + $index }}

</td>


<td class="fw-medium">

{{ $v->product->name ?? '-' }}

</td>


<td>

{{ $v->attribute_value ?? '-' }}

</td>


<td class="text-center">

<span class="badge
@if($v->stock_quantity <=2) bg-danger
@elseif($v->stock_quantity <=5) bg-warning text-dark
@else bg-success
@endif">

{{ $v->stock_quantity }}

</span>

</td>


<td class="text-center">

@if($v->stock_quantity <=2)

<span class="badge bg-danger">
Nguy hiểm
</span>

@elseif($v->stock_quantity <=5)

<span class="badge bg-warning text-dark">
Sắp hết
</span>

@else

<span class="badge bg-success">
Ổn định
</span>

@endif

</td>


<td class="text-center">

<a href="{{ route('admin.stock.create',['variant'=>$v->id]) }}"
class="btn btn-sm btn-primary">

<i class="bi bi-box-arrow-in-down me-1"></i>
Nhập thêm

</a>

</td>

</tr>

@empty

<tr>

<td colspan="6"
class="text-center text-muted py-4">

<i class="bi bi-inbox me-1"></i>
Không có sản phẩm sắp hết hàng

</td>

</tr>

@endforelse

</tbody>

</table>

</div>


{{-- PAGINATION --}}
@if($variants->hasPages())

<div class="mt-4">

{{ $variants->links('pagination::bootstrap-5') }}

</div>

@endif


</div>
</div>

@endsection