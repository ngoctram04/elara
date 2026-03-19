@extends('layouts.admin')

@section('title','Lịch sử thay đổi tồn kho')

@section('content')

<div class="card border-0 shadow-sm">

<div class="card-body">

{{-- HEADER --}}

<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h5 class="fw-bold mb-1">
Lịch sử thay đổi tồn kho
</h5>

<small class="text-muted">
Theo dõi các thay đổi số lượng tồn kho trong hệ thống
</small>
</div>

<span class="badge bg-secondary">
Tổng: {{ $logs->total() }}
</span>

</div>

{{-- ===================== FILTER ===================== --}}

<form method="GET" class="row g-2 mb-4 align-items-center">

<div class="col-md-4">

<input type="text"
name="keyword"
class="form-control form-control-sm"
placeholder="Tìm theo tên sản phẩm..."
value="{{ request('keyword') }}">

</div>

<div class="col-md-2">

<select name="type"
class="form-select form-select-sm">

<option value="">Tất cả loại</option>

<option value="import"
{{ request('type')=='import'?'selected':'' }}>
Nhập kho
</option>

<option value="order"
{{ request('type')=='order'?'selected':'' }}>
Bán hàng
</option>

<option value="cancel"
{{ request('type')=='cancel'?'selected':'' }}>
Hoàn kho
</option>

<option value="adjust"
{{ request('type')=='adjust'?'selected':'' }}>
Điều chỉnh
</option>

</select>

</div>

<div class="col-md-2">

<input type="date"
name="from"
class="form-control form-control-sm"
value="{{ request('from') }}">

</div>

<div class="col-md-2">

<input type="date"
name="to"
class="form-control form-control-sm"
value="{{ request('to') }}">

</div>

<div class="col-md-2 d-flex gap-2">

<button class="btn btn-outline-primary btn-sm">

<i class="bi bi-search"></i>
Lọc

</button>

<a href="{{ route('admin.inventory.logs') }}"
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


<th>
Sản phẩm
</th>

<th>
Biến thể
</th>

<th style="width:150px" class="text-center">
Loại
</th>

<th style="width:120px" class="text-center">
Thay đổi
</th>

<th style="width:120px" class="text-center">
Tồn trước
</th>

<th style="width:120px" class="text-center">
Tồn sau
</th>

<th style="width:180px" class="text-center">
Thời gian
</th>

</tr>

</thead>

<tbody>

@forelse($logs as $index => $log)

<tr>



<td class="fw-medium">
{{ $log->variant->product->name ?? '-' }}
</td>

<td>
{{ $log->variant->attribute_value ?? '-' }}
</td>

<td class="text-center">

@switch($log->type)

@case('import') <span class="badge bg-success"> <i class="bi bi-box-arrow-in-down me-1"></i>
Nhập kho </span>
@break

@case('order') <span class="badge bg-primary"> <i class="bi bi-cart-check me-1"></i>
Bán hàng </span>
@break

@case('cancel') <span class="badge bg-warning text-dark"> <i class="bi bi-arrow-counterclockwise me-1"></i>
Hoàn kho </span>
@break

@case('adjust') <span class="badge bg-info text-dark"> <i class="bi bi-tools me-1"></i>
Điều chỉnh </span>
@break
@case('expired_destroy')
<span class="badge bg-danger">
    <i class="bi bi-trash me-1"></i>
    Huỷ cận date
</span>
@break
@default <span class="badge bg-secondary">
{{ $log->type }} </span>

@endswitch

</td>

<td class="text-center fw-bold">

@if($log->quantity_change > 0)

<span class="text-success">
+{{ $log->quantity_change }}
</span>

@else

<span class="text-danger">
{{ $log->quantity_change }}
</span>

@endif

</td>

<td class="text-center text-muted">
{{ $log->stock_before }}
</td>

<td class="text-center fw-semibold">
{{ $log->stock_after }}
</td>

<td class="text-center text-muted small">
{{ optional($log->created_at)->format('d/m/Y H:i') }}
</td>

</tr>

@empty

<tr>

<td colspan="8"
class="text-center text-muted py-4">

<i class="bi bi-inbox me-1"></i>
Không có lịch sử tồn kho

</td>

</tr>

@endforelse

</tbody>

</table>

</div>

{{-- ===================== PAGINATION ===================== --}}

@if($logs->hasPages())

<div class="mt-4 d-flex justify-content-center">

{{ $logs->links('pagination::bootstrap-5') }}

</div>

@endif

</div>

</div>

@endsection
