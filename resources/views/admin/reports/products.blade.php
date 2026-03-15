@extends('layouts.admin')

@section('title','Tất cả sản phẩm bán chạy')

@section('content')

<div class="card border-0 shadow-sm">
<div class="card-body">

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h5 class="fw-bold mb-1">
<i class="bi bi-fire text-danger me-1"></i>
Tất cả sản phẩm bán chạy
</h5>

<small class="text-muted">
Danh sách sản phẩm bán chạy trong khoảng thời gian đã chọn
</small>
</div>

<a href="{{ route('admin.reports.index') }}"
class="btn btn-outline-secondary btn-sm">

<i class="bi bi-arrow-left"></i>
Quay lại Dashboard

</a>

</div>



{{-- FILTER --}}
<form method="GET"
class="row g-2 mb-4 align-items-end">

<div class="col-md-2">

<label class="small text-muted">
Từ ngày
</label>

<input type="date"
name="from"
value="{{ $from }}"
class="form-control form-control-sm">

</div>


<div class="col-md-2">

<label class="small text-muted">
Đến ngày
</label>

<input type="date"
name="to"
value="{{ $to }}"
class="form-control form-control-sm">

</div>


<div class="col-md-4">

<label class="small text-muted">
Tìm sản phẩm
</label>

<input type="text"
name="keyword"
value="{{ $keyword }}"
placeholder="Nhập tên sản phẩm..."
class="form-control form-control-sm">

</div>


<div class="col-md-4 d-flex gap-2">

<button class="btn btn-outline-primary btn-sm">

<i class="bi bi-search"></i>
Lọc

</button>

<a href="{{ route('admin.reports.products') }}"
class="btn btn-outline-secondary btn-sm">

Đặt lại

</a>

</div>

</form>



{{-- TABLE --}}
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

<th style="width:160px" class="text-center">
Số lượng bán
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


<td class="text-center">

<span class="badge bg-success">

{{ number_format($p->total_sold) }}

</span>

</td>

</tr>

@empty

<tr>

<td colspan="3"
class="text-center text-muted py-4">

<i class="bi bi-box-seam fs-5"></i>

<div class="mt-1">
Không có dữ liệu
</div>

</td>

</tr>

@endforelse

</tbody>

</table>

</div>



{{-- PAGINATION --}}
@if($products->hasPages())

<div class="mt-4">

{{ $products->withQueryString()->links('pagination::bootstrap-5') }}

</div>

@endif


</div>
</div>

@endsection