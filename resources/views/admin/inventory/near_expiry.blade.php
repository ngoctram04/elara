@extends('layouts.admin')

@section('content')

<div class="container-fluid">

<h3 class="mb-4">📦 Lô sản phẩm sắp hết hạn</h3>

<div class="alert alert-warning">
    <b>Quy tắc hệ thống:</b><br>
    🟡 Còn ≤ 7 tháng → nên giảm giá / sale để bán nhanh.<br>
    🔴 Còn ≤ 6 tháng → hệ thống sẽ tự động huỷ tồn kho.
</div>

<div class="card shadow-sm border-0">
<div class="card-body p-0">

<table class="table table-hover align-middle mb-0">

<thead class="table-light">
<tr>
    <th width="80">Ảnh</th>
    <th>Sản phẩm</th>
    <th>Biến thể</th>
    <th width="120">Số lượng</th>
    <th width="150">Hạn sử dụng</th>
    <th width="180">Trạng thái</th>
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
     class="rounded">
@else
<span class="text-muted">No image</span>
@endif
</td>

<td>
<b>{{ $lot->product_name }}</b>
</td>

<td>
{{ $lot->attribute_name ?? '' }}
{{ $lot->attribute_value ?? '' }}
</td>

<td>
<span class="badge bg-dark">
{{ $lot->quantity }}
</span>
</td>

<td>
{{ \Carbon\Carbon::parse($lot->expiry_date)->format('d/m/Y') }}
</td>

<td>

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
<td colspan="6" class="text-center text-muted py-4">
Không có lô sắp hết hạn
</td>
</tr>

@endforelse

</tbody>

</table>

</div>
</div>

<div class="mt-3">
{{ $lots->links() }}
</div>

</div>

@endsection