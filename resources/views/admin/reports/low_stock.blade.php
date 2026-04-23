@extends('layouts.admin')

@section('title','Sản phẩm sắp hết hàng')

@section('content')

<div class="card border-0 shadow-sm">
<div class="card-body">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h5 class="fw-bold mb-1">
<i class="bi bi-exclamation-triangle text-danger me-1"></i>
Sản phẩm sắp hết hàng
</h5>

<small class="text-muted">
Danh sách sản phẩm có tồn kho thấp cần nhập thêm
</small>
</div>

<a href="{{ route('admin.reports.index', ['from'=>$from ?? null,'to'=>$to ?? null]) }}"
class="btn btn-outline-secondary btn-sm">

<i class="bi bi-arrow-left"></i>
Quay lại Dashboard

</a>

</div>

<form method="GET"
class="row g-2 mb-4 align-items-end">

<div class="col-md-4">

<label class="small text-muted">
Tìm sản phẩm
</label>

<input type="text"
name="keyword"
value="{{ $keyword ?? '' }}"
placeholder="Nhập tên sản phẩm..."
class="form-control form-control-sm">

</div>


<div class="col-md-3">

<label class="small text-muted">
Tồn tối đa
</label>

<input type="number"
name="max_stock"
value="{{ $max_stock ?? '' }}"
placeholder="Ví dụ: 5"
class="form-control form-control-sm">

</div>


<div class="col-md-5 d-flex gap-2">

<button class="btn btn-outline-primary btn-sm">

<i class="bi bi-search"></i>
Lọc

</button>

<a href="{{ route('admin.reports.lowStock') }}"
class="btn btn-outline-secondary btn-sm">

Đặt lại

</a>

</div>

</form>


<div class="table-responsive">

<table class="table table-hover align-middle mb-0">

<thead class="table-light">

<tr>

<th style="width:80px" class="text-center">
STT
</th>

<th>
Sản phẩm
</th>

<th>
Biến thể
</th>

<th style="width:120px" class="text-center">
Số lượng
</th>

<th style="width:160px" class="text-center">
Trạng thái
</th>

</tr>

</thead>

<tbody>

@forelse($products as $p)

<tr>

<td class="text-center text-muted fw-semibold">

{{ ($products->currentPage()-1)*$products->perPage()+$loop->iteration }}

</td>


<td class="fw-medium">

{{ $p->name }}

</td>


<td class="text-muted">

{{ $p->attribute_value }}

</td>


<td class="text-center fw-semibold
{{ $p->stock_quantity <= 2 ? 'text-danger' : 'text-warning' }}">

{{ $p->stock_quantity }}

</td>


<td class="text-center">

@if($p->stock_quantity == 0)

<span class="badge bg-danger">
Hết hàng
</span>

@elseif($p->stock_quantity <= 2)

<span class="badge bg-danger">
Rất thấp
</span>

@elseif($p->stock_quantity <= 5)

<span class="badge bg-warning text-dark">
Sắp hết
</span>

@else

<span class="badge bg-success">
Ổn định
</span>

@endif

</td>

</tr>

@empty

<tr>

<td colspan="5"
class="text-center text-muted py-4">

<i class="bi bi-box-seam fs-4"></i>

<div class="mt-1">
Không có dữ liệu
</div>

</td>

</tr>

@endforelse

</tbody>

</table>

</div>


@if($products->hasPages())

<div class="mt-4">

{{ $products->withQueryString()->links('pagination::bootstrap-5') }}

</div>

@endif


</div>
</div>

@endsection