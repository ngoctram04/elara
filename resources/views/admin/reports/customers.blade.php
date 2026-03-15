@extends('layouts.admin')

@section('title','Top khách hàng')

@section('content')

<div class="card border-0 shadow-sm">
<div class="card-body">

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h5 class="fw-bold mb-1">
<i class="bi bi-people text-primary me-1"></i>
Top khách hàng
</h5>

<small class="text-muted">
Danh sách khách hàng có tổng chi tiêu cao nhất
</small>
</div>

<a href="{{ route('admin.reports.index', ['from'=>$from,'to'=>$to]) }}"
class="btn btn-outline-secondary btn-sm">

<i class="bi bi-arrow-left"></i>
Quay lại Dashboard

</a>

</div>



{{-- FILTER --}}
<form method="GET"
class="row g-2 mb-4 align-items-end">

<div class="col-md-3">

<label class="small text-muted">
Từ ngày
</label>

<input type="date"
name="from"
value="{{ $from }}"
class="form-control form-control-sm">

</div>


<div class="col-md-3">

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
Tìm khách hàng
</label>

<input type="text"
name="keyword"
value="{{ $keyword }}"
placeholder="Nhập tên khách hàng..."
class="form-control form-control-sm">

</div>


<div class="col-md-2 d-flex gap-2">

<button class="btn btn-outline-primary btn-sm w-100">

<i class="bi bi-search"></i>
Lọc

</button>

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
Khách hàng
</th>

<th style="width:120px" class="text-center">
Số đơn
</th>

<th style="width:180px" class="text-end">
Tổng chi tiêu
</th>

</tr>

</thead>

<tbody>

@forelse($customers as $c)

<tr>

<td class="text-center text-muted fw-semibold">

{{ ($customers->currentPage()-1)*$customers->perPage()+$loop->iteration }}

</td>


<td class="fw-medium">

<i class="bi bi-person-circle text-muted me-1"></i>
{{ $c->name }}

</td>


<td class="text-center">

<span class="badge bg-primary">

{{ number_format($c->orders) }}

</span>

</td>


<td class="text-end fw-semibold text-success">

{{ number_format($c->spending) }} đ

</td>

</tr>

@empty

<tr>

<td colspan="4"
class="text-center text-muted py-4">

<i class="bi bi-people fs-4"></i>

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
@if($customers->hasPages())

<div class="mt-4">

{{ $customers->withQueryString()->links('pagination::bootstrap-5') }}

</div>

@endif


</div>
</div>

@endsection