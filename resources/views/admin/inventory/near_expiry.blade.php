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

{{-- FILTER --}}

<form method="GET" class="row g-2 mb-4">

<div class="col-md-4">

<input
type="text"
name="keyword"
value="{{ request('keyword') }}"
class="form-control form-control-sm"
placeholder="Tìm sản phẩm...">

</div>

<div class="col-md-3">

<select name="status" class="form-select form-select-sm">

<option value="">Tất cả trạng thái</option>

<option value="sale" {{ request('status')=='sale'?'selected':'' }}>
Nên sale
</option>

<option value="danger" {{ request('status')=='danger'?'selected':'' }}>
Sắp huỷ
</option>

<option value="expired" {{ request('status')=='expired'?'selected':'' }}>
Đã huỷ
</option>

</select>

</div>

<div class="col-md-2">

<select name="sort" class="form-select form-select-sm">

<option value="">HSD gần nhất</option>

<option value="far" {{ request('sort')=='far'?'selected':'' }}>
HSD xa nhất
</option>

</select>

</div>

<div class="col-md-3 d-flex gap-2">

<button class="btn btn-outline-primary btn-sm">

<i class="bi bi-search me-1"></i>
Lọc

</button>

<a href="{{ route('admin.inventory.near_expiry') }}"
class="btn btn-outline-secondary btn-sm">

Đặt lại
</a>

</div>

</form>

{{-- ALERT --}}

<div class="alert alert-warning mb-4">

<b>Quy tắc hệ thống:</b><br>

🟡 Còn ≤ <b>7 tháng</b> → nên giảm giá / sale để bán nhanh.<br>
🔴 Còn ≤ <b>6 tháng</b> → hệ thống sẽ tự động huỷ tồn kho.<br>
⚫ Lô đã huỷ vẫn hiển thị để admin theo dõi.

</div>

{{-- TABLE --}}

<div class="table-responsive">

<table class="table table-hover align-middle mb-0">

<thead class="table-light">

<tr>
<th style="width:80px">Ảnh</th>
<th>Sản phẩm</th>
<th>Biến thể</th>
<th style="width:120px" class="text-center">Số lượng</th>
<th style="width:150px" class="text-center">Hạn sử dụng</th>
<th style="width:120px" class="text-center">Còn lại</th>
<th style="width:180px" class="text-center">Trạng thái</th>
</tr>

</thead>

<tbody>

@forelse($lots as $lot)

@php
$expiry = \Carbon\Carbon::parse($lot->expiry_date);
$now = \Carbon\Carbon::now();
$months = $now->diffInMonths($expiry, false);
@endphp

<tr class="
@if($lot->expired_at) table-secondary
@elseif($months <= 6) table-danger
@elseif($months <= 7) table-warning
@endif
">

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

@if($lot->expired_at)

<span class="badge bg-secondary">
0
</span>

<small class="text-danger d-block">
Huỷ {{ $lot->quantity }}
</small>

@else

<span class="badge bg-dark">
{{ $lot->quantity }}
</span>

@endif

</td>

<td class="text-center text-muted">
{{ $expiry->format('d/m/Y') }}
</td>

<td class="text-center">

@if($months < 0)

<span class="text-danger fw-bold">
Đã hết hạn
</span>

@else

{{ round($months,1) }} tháng

@endif

</td>

<td class="text-center">

@if($lot->expired_at)

<span class="badge bg-secondary">
❌ Đã huỷ
</span>

@elseif($months <= 6)

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

<td colspan="7"
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

{{ $lots->appends(request()->query())->links('pagination::bootstrap-5') }}

</div>

@endif

</div>

</div>

@endsection
