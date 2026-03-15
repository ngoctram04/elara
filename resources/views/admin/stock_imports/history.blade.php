@extends('layouts.admin')

@section('title','Lịch sử nhập kho')

@section('content')

<div class="card border-0 shadow-sm">

<div class="card-body">

{{-- HEADER --}}

<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h5 class="fw-bold mb-1">
Lịch sử nhập kho
</h5>

<small class="text-muted">
Danh sách các phiếu nhập kho trong hệ thống
</small>
</div>

<a href="{{ route('admin.stock.create') }}"
class="btn btn-primary btn-sm">

<i class="bi bi-plus-lg me-1"></i>
Nhập hàng

</a>

</div>

{{-- TABLE --}}

<div class="table-responsive">

<table class="table table-hover align-middle mb-0">

<thead class="table-light">

<tr>

<th style="width:80px" class="text-center">
STT
</th>

<th style="width:180px">
Mã phiếu
</th>

<th>
Nhà cung cấp
</th>

<th style="width:120px" class="text-center">
Số SP
</th>

<th style="width:140px" class="text-center">
Tổng SL
</th>

<th style="width:180px" class="text-center">
Ngày nhập
</th>

<th style="width:140px" class="text-center">
Thao tác
</th>

</tr>

</thead>

<tbody>

@forelse($imports as $index => $item)

<tr>

<td class="text-center text-muted fw-semibold">
{{ $imports->firstItem() + $index }}
</td>

<td>
<span class="badge bg-secondary">
{{ $item->code }}
</span>
</td>

<td class="fw-medium">
{{ $item->supplier ?? '—' }}
</td>

<td class="text-center">

<span class="badge bg-info">
{{ $item->total_items }}
</span>

</td>

<td class="text-center">

<span class="badge bg-success">
{{ $item->total_qty }}
</span>

</td>

<td class="text-center text-muted small">

{{ optional($item->created_at)->format('d/m/Y H:i') }}

</td>

<td class="text-center">

<a href="{{ url('admin/stock-import/'.$item->code) }}"
class="btn btn-sm btn-outline-primary">

<i class="bi bi-eye"></i>

</a>

</td>

</tr>

@empty

<tr>

<td colspan="7"
class="text-center text-muted py-4">

<i class="bi bi-inbox me-1"></i>
Chưa có phiếu nhập kho

</td>

</tr>

@endforelse

</tbody>

</table>

</div>

{{-- PAGINATION --}}
@if($imports->hasPages())

<div class="mt-4">

{{ $imports->links('pagination::bootstrap-5') }}

</div>

@endif

</div>
</div>

@endsection
