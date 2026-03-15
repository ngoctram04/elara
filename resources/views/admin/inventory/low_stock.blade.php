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
{{ $variants->count() }} sản phẩm
</span>

</div>

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
{{ $index + 1 }}
</td>

<td class="fw-medium">
{{ $v->product->name ?? '-' }}
</td>

<td>
{{ $v->attribute_value ?? '-' }}
</td>

<td class="text-center fw-bold
@if($v->stock_quantity <= 2) text-danger
@elseif($v->stock_quantity <=5) text-warning
@endif
">

{{ $v->stock_quantity }}

</td>

<td class="text-center">

@if($v->stock_quantity <= 2)

<span class="badge bg-danger">
Nguy hiểm
</span>

@elseif($v->stock_quantity <=5)

<span class="badge bg-warning text-dark">
Sắp hết
</span>

@endif

</td>

<td class="text-center">

<a href="{{ route('admin.stock.create') }}"
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

</div>

</div>

@endsection
