@extends('layouts.admin')

@section('title','Báo cáo tồn kho')

@section('content')

{{-- ===================== KPI ===================== --}}
<div class="row g-3 mb-4">

<div class="col-md-3">
<div class="card border-0 shadow-sm">
<div class="card-body">

<h6 class="text-muted mb-1">
Tổng biến thể
</h6>

<h4 class="fw-bold mb-0">
{{ $variants->total() }}
</h4>

</div>
</div>
</div>


<div class="col-md-3">
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


<div class="col-md-3">
<div class="card border-0 shadow-sm">
<div class="card-body">

<h6 class="text-muted mb-1">
Sắp hết hàng
</h6>

<h4 class="fw-bold text-warning mb-0">
{{ $variants->where('stock_quantity','<=',5)->count() }}
</h4>

</div>
</div>
</div>


<div class="col-md-3">
<div class="card border-0 shadow-sm">
<div class="card-body">

<h6 class="text-muted mb-1">
Hết hàng
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


{{-- ===================== FILTER ===================== --}}
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

<select name="status" class="form-select form-select-sm">

<option value="">Tất cả trạng thái</option>

<option value="out"
{{ request('status')=='out'?'selected':'' }}>
Hết hàng
</option>

<option value="danger"
{{ request('status')=='danger'?'selected':'' }}>
Nguy hiểm (≤2)
</option>

<option value="low"
{{ request('status')=='low'?'selected':'' }}>
Sắp hết (≤5)
</option>

<option value="ok"
{{ request('status')=='ok'?'selected':'' }}>
Còn hàng
</option>

</select>

</div>


<div class="col-md-3">

<select name="sort" class="form-select form-select-sm">

<option value="">Tồn kho thấp → cao</option>

<option value="high"
{{ request('sort')=='high'?'selected':'' }}>
Tồn kho cao → thấp
</option>

</select>

</div>


<div class="col-md-2 d-flex gap-2">

<button class="btn btn-outline-primary btn-sm">
<i class="bi bi-search"></i>
Lọc
</button>

<a href="{{ route('admin.inventory.report') }}"
class="btn btn-outline-secondary btn-sm">
Đặt lại
</a>

</div>

</form>


{{-- ===================== TABLE ===================== --}}
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
@if($v->stock_quantity == 0) bg-danger
@elseif($v->stock_quantity <=2) bg-danger
@elseif($v->stock_quantity <=5) bg-warning text-dark
@else bg-success
@endif">

{{ $v->stock_quantity }}

</span>

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


{{-- PAGINATION --}}
@if($variants->hasPages())

<div class="mt-4">

{{ $variants->links('pagination::bootstrap-5') }}

</div>

@endif


</div>

</div>

@endsection