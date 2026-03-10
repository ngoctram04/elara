@extends('layouts.admin')

@section('title','Sản phẩm sắp hết hàng')

@section('content')

<div class="container-fluid">

<div class="card shadow border-0">

<div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">

<h5 class="mb-0">
<i class="bi bi-exclamation-triangle me-2"></i>
Sản phẩm sắp hết hàng
</h5>

<span class="badge bg-light text-danger">
{{ $variants->count() }} sản phẩm
</span>

</div>

<div class="card-body">

<div class="alert alert-warning mb-4">
Những sản phẩm có tồn kho ≤ <b>5</b> sẽ hiển thị tại đây để quản trị viên kịp thời nhập thêm hàng.
</div>

<div class="table-responsive">

<table class="table table-bordered align-middle">

<thead class="table-light">

<tr>
<th width="70">STT</th>
<th>Sản phẩm</th>
<th>Biến thể</th>
<th width="120">Tồn kho</th>
<th width="140">Trạng thái</th>
<th width="150">Hành động</th>
</tr>

</thead>

<tbody>

@forelse($variants as $index => $v)

<tr>

<td>{{ $index + 1 }}</td>

<td>
{{ $v->product->name ?? '-' }}
</td>

<td>
{{ $v->attribute_value ?? '-' }}
</td>

<td class="fw-bold
@if($v->stock_quantity <= 2) text-danger
@elseif($v->stock_quantity <=5) text-warning
@endif
">
{{ $v->stock_quantity }}
</td>

<td>

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

<td>

<a href="{{ route('admin.stock.create') }}"
class="btn btn-sm btn-primary">

<i class="bi bi-box-arrow-in-down"></i>
Nhập thêm

</a>

</td>

</tr>

@empty

<tr>
<td colspan="6" class="text-center text-muted">
Không có sản phẩm sắp hết hàng
</td>
</tr>

@endforelse

</tbody>

</table>

</div>

</div>

</div>

</div>

@endsection
