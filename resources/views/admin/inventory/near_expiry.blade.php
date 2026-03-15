@extends('layouts.admin')

@section('title','Lô sản phẩm sắp hết hạn')

@section('content')

<div class="card border-0 shadow-sm">

<div class="card-body">

{{-- HEADER --}}

<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h5 class="fw-bold mb-1">
Lô sản phẩm sắp hết hạn
</h5>

<small class="text-muted">
Theo dõi các lô hàng gần đến hạn sử dụng
</small>
</div>

<span class="badge bg-warning text-dark">
{{ $lots->total() }} lô
</span>

</div>

{{-- ALERT --}}

<div class="alert alert-warning mb-4">

<b>Quy tắc hệ thống:</b><br>

🟡 Còn ≤ <b>7 tháng</b> → nên giảm giá / sale để bán nhanh.<br>
🔴 Còn ≤ <b>6 tháng</b> → hệ thống sẽ tự động huỷ tồn kho.

</div>

{{-- TABLE --}}

<div class="table-responsive">

<table class="table table-hover align-middle mb-0">

<thead class="table-light">

<tr>

<th style="width:80px">
Ảnh
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

<th style="width:150px" class="text-center">
Hạn sử dụng
</th>

<th style="width:180px" class="text-center">
Trạng thái
</th>

</tr>

</thead>

<tbody>

@forelse($lots as $lot)

@php
$months = \Carbon\Carbon::now()->diffInMonths($lot->expiry_date, false);
@endphp

<tr>

<td>

@if($lot->image_path)

<img src="{{ asset('storage/'.$lot->image_path) }}"
width="50"
class="rounded border">

@else

<span class="text-muted small">
No image
</span>

@endif

</td>

<td class="fw-medium">
{{ $lot->product_name }}
</td>

<td>
{{ $lot->attribute_name ?? '' }}
{{ $lot->attribute_value ?? '' }}
</td>

<td class="text-center">

<span class="badge bg-dark">
{{ $lot->quantity }}
</span>

</td>

<td class="text-center text-muted">

{{ \Carbon\Carbon::parse($lot->expiry_date)->format('d/m/Y') }}

</td>

<td class="text-center">

@if($months <= 6)

<span class="badge bg-danger">
⚠ Sắp bị huỷ
</span>

@elseif($months <= 7)

<span class="badge bg-warning text-dark">
💰 Nên sale
</span>

@else

<span class="badge bg-success">
Bình thường
</span>

@endif

</td>

</tr>

@empty

<tr>

<td colspan="6"
class="text-center text-muted py-4">

<i class="bi bi-inbox me-1"></i>
Không có lô sắp hết hạn

</td>

</tr>

@endforelse

</tbody>

</table>

</div>

{{-- PAGINATION --}}
@if($lots->hasPages())

<div class="mt-4 d-flex justify-content-center">

{{ $lots->links('pagination::bootstrap-5') }}

</div>

@endif

</div>

</div>

@endsection
