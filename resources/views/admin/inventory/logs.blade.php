@extends('layouts.admin')

@section('title','Lịch sử thay đổi tồn kho')

@section('content')

<div class="container-fluid">

<div class="card shadow border-0">

<div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">

<h5 class="mb-0">
<i class="bi bi-clock-history me-2"></i>
Lịch sử thay đổi tồn kho
</h5>

<span class="badge bg-light text-dark">
Tổng: {{ $logs->total() }}
</span>

</div>


<div class="card-body">

{{-- ===================== FILTER ===================== --}}

<form method="GET" class="row g-3 mb-4">

<div class="col-md-3">

<input type="text"
name="keyword"
class="form-control"
placeholder="Tìm sản phẩm..."
value="{{ request('keyword') }}">

</div>


<div class="col-md-2">

<select name="type" class="form-select">

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
class="form-control"
value="{{ request('from') }}">

</div>


<div class="col-md-2">

<input type="date"
name="to"
class="form-control"
value="{{ request('to') }}">

</div>


<div class="col-md-3">

<button class="btn btn-primary">
<i class="bi bi-search"></i> Lọc
</button>

<a href="{{ route('admin.inventory.logs') }}"
class="btn btn-secondary">
Reset
</a>

</div>

</form>


{{-- ===================== TABLE ===================== --}}

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead class="table-light">

<tr>
<th width="70">STT</th>
<th>Sản phẩm</th>
<th>Biến thể</th>
<th width="140">Loại</th>
<th width="120">Thay đổi</th>
<th width="120">Tồn trước</th>
<th width="120">Tồn sau</th>
<th width="180">Thời gian</th>
</tr>

</thead>

<tbody>

@forelse($logs as $index => $log)

<tr>

<td>
{{ $logs->firstItem() + $index }}
</td>

<td>
{{ $log->variant->product->name ?? '-' }}
</td>

<td>
{{ $log->variant->attribute_value ?? '-' }}
</td>


<td>

@switch($log->type)

@case('import')
<span class="badge bg-success">
<i class="bi bi-box-arrow-in-down"></i> Nhập kho
</span>
@break

@case('order')
<span class="badge bg-primary">
<i class="bi bi-cart-check"></i> Bán hàng
</span>
@break

@case('cancel')
<span class="badge bg-warning text-dark">
<i class="bi bi-arrow-counterclockwise"></i> Hoàn kho
</span>
@break

@case('adjust')
<span class="badge bg-info text-dark">
<i class="bi bi-tools"></i> Điều chỉnh
</span>
@break

@default
<span class="badge bg-secondary">
{{ $log->type }}
</span>

@endswitch

</td>


<td class="fw-bold">

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


<td class="text-muted">
{{ $log->stock_before }}
</td>


<td class="fw-semibold">
{{ $log->stock_after }}
</td>


<td>
{{ $log->created_at->format('d/m/Y H:i') }}
</td>

</tr>

@empty

<tr>

<td colspan="8" class="text-center py-4 text-muted">

<i class="bi bi-inbox fs-4"></i><br>
Không có lịch sử tồn kho

</td>

</tr>

@endforelse

</tbody>

</table>

</div>


{{-- ===================== PAGINATION ===================== --}}

<div class="mt-3 d-flex justify-content-center">

{{ $logs->links() }}

</div>


</div>

</div>

</div>

@endsection