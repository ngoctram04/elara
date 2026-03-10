@extends('layouts.admin')

@section('title','Báo cáo tồn kho')

@section('content')

<div class="container-fluid">

<div class="row mb-4">

<div class="col-md-4">
<div class="card border-0 shadow-sm">
<div class="card-body">
<h6 class="text-muted">Tổng biến thể</h6>
<h4 class="fw-bold">{{ $variants->count() }}</h4>
</div>
</div>
</div>

<div class="col-md-4">
<div class="card border-0 shadow-sm">
<div class="card-body">
<h6 class="text-muted">Tổng tồn kho</h6>
<h4 class="fw-bold">
{{ $variants->sum('stock_quantity') }}
</h4>
</div>
</div>
</div>

<div class="col-md-4">
<div class="card border-0 shadow-sm">
<div class="card-body">
<h6 class="text-muted">Sản phẩm hết hàng</h6>
<h4 class="fw-bold text-danger">
{{ $variants->where('stock_quantity',0)->count() }}
</h4>
</div>
</div>
</div>

</div>

<div class="card shadow border-0">

<div class="card-header bg-primary text-white">

<h5 class="mb-0">
<i class="bi bi-bar-chart me-2"></i>
Báo cáo tồn kho
</h5>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered align-middle">

<thead class="table-light">

<tr>
<th width="70">STT</th>
<th>Sản phẩm</th>
<th>Biến thể</th>
<th width="120">Tồn kho</th>
<th width="140">Trạng thái</th>
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

@if($v->stock_quantity == 0)

<span class="badge bg-danger">
Hết hàng
</span>

@elseif($v->stock_quantity <=2)

<span class="badge bg-danger">
Nguy hiểm
</span>

@elseif($v->stock_quantity <=5)

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
<td colspan="5" class="text-center">
Không có dữ liệu tồn kho
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
