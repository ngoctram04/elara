@extends('layouts.admin')

@section('title','Sản phẩm được yêu thích')

@section('content')

<div class="card border-0 shadow-sm">
<div class="card-body">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h5 class="fw-bold mb-1">
<i class="bi bi-heart-fill text-danger me-1"></i>
Sản phẩm được yêu thích
</h5>

<small class="text-muted">
Danh sách sản phẩm được khách hàng thêm vào yêu thích
</small>
</div>

<a href="{{ route('admin.reports.index') }}"
class="btn btn-outline-secondary btn-sm">

<i class="bi bi-arrow-left"></i>
Quay lại Dashboard

</a>

</div>

<form method="GET"
class="row g-2 mb-4 align-items-end">

<div class="col-md-6">

<label class="small text-muted">
Tìm sản phẩm
</label>

<input type="text"
name="keyword"
value="{{ $keyword ?? '' }}"
placeholder="Nhập tên sản phẩm..."
class="form-control form-control-sm">

</div>


<div class="col-md-6 d-flex gap-2">

<button class="btn btn-outline-primary btn-sm">

<i class="bi bi-search"></i>
Tìm kiếm

</button>

<a href="{{ route('admin.reports.wishlist') }}"
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

<th style="width:150px" class="text-center">
Lượt yêu thích
</th>

<th style="width:160px" class="text-center">
Mức độ
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


<td class="text-center fw-semibold">

<span class="badge bg-danger">

<i class="bi bi-heart-fill"></i>
{{ number_format($p->total_wishlist) }}

</span>

</td>


<td class="text-center">

@if($p->total_wishlist >= 50)

<span class="badge bg-danger">
Rất hot
</span>

@elseif($p->total_wishlist >= 20)

<span class="badge bg-warning text-dark">
Quan tâm cao
</span>

@elseif($p->total_wishlist >= 5)

<span class="badge bg-info text-dark">
Quan tâm
</span>

@else

<span class="badge bg-secondary">
Ít quan tâm
</span>

@endif

</td>

</tr>

@empty

<tr>

<td colspan="4"
class="text-center text-muted py-4">

<i class="bi bi-heart fs-4"></i>

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