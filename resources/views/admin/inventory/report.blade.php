@extends('layouts.admin')

@section('title','Báo cáo tồn kho')

@section('content')

{{-- ===================== KPI ===================== --}}

<div class="row g-3 mb-4">

<div class="col-md-4">

<div class="card border-0 shadow-sm">
<div class="card-body">

<h6 class="text-muted mb-1">
Tổng biến thể
</h6>

<h4 class="fw-bold mb-0">
{{ $variants->count() }}
</h4>

</div>
</div>

</div>

<div class="col-md-4">

<div class="card border-0 shadow-sm">
<div class="card-body">

<h6 class="text-muted mb-1">
Tổng tồn kho
</h6>

<h4 class="fw-bold mb-0">
{{ $variants->sum('stock_quantity') }}
</h4>

</div>
</div>

</div>

<div class="col-md-4">

<div class="card border-0 shadow-sm">
<div class="card-body">

<h6 class="text-muted mb-1">
Sản phẩm hết hàng
</h6>

<h4 class="fw-bold text-danger mb-0">
{{ $variants->where('stock_quantity',0)->count() }}
</h4>

</div>
</div>

</div>

</div>

{{-- ===================== REPORT TABLE ===================== --}}

<div class="card border-0 shadow-sm">

<div class="card-body">

{{-- HEADER --}}

<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h5 class="fw-bold mb-1">
Báo cáo tồn kho
</h5>

<small class="text-muted">
Danh sách tồn kho của các biến thể sản phẩm
</small>
</div>

</div>

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

@if($v->stock_quantity == 0)

<span class="badge bg-danger">
Hết hàng
</span>

@elseif($v->stock_quantity <= 2)

<span class="badge bg-danger">
Nguy hiểm
</span>

@elseif($v->stock_quantity <= 5)

<span class="badge bg-warning text-dark">
Sắp hết
</span>

@else

<span class="badge bg-success">
Còn hàng
</span>

@endif

</td>

</tr>

@empty

<tr>

<td colspan="5"
class="text-center text-muted py-4">

<i class="bi bi-inbox me-1"></i>
Không có dữ liệu tồn kho

</td>

</tr>

@endforelse

</tbody>

</table>

</div>

</div>

</div>

@endsection
