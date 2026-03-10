@extends('layouts.admin')

@section('title','Lịch sử nhập kho')

@section('content')

<div class="container-fluid">

<div class="card shadow border-0">

<div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">

<div>
<h5 class="mb-0">
<i class="bi bi-clock-history me-2"></i>
Lịch sử nhập kho
</h5>
<small>Danh sách các phiếu nhập</small>
</div>

<a href="{{ route('admin.stock.create') }}" class="btn btn-primary btn-sm">
<i class="bi bi-plus-lg"></i> Nhập hàng
</a>

</div>

<div class="card-body p-0">

<div class="table-responsive">

<table class="table table-bordered table-hover align-middle mb-0">

<thead class="table-light">
<tr>
<th width="80">STT</th>
<th width="180">Mã phiếu</th>
<th width="200">Nhà cung cấp</th>
<th width="120">Số SP</th>
<th width="140">Tổng SL</th>
<th width="180">Ngày nhập</th>
<th width="140">Thao tác</th>
</tr>
</thead>

<tbody>

@forelse($imports as $index => $item)

<tr>

<td>
{{ $imports->firstItem() + $index }}
</td>

<td>
<span class="badge bg-secondary">
{{ $item->code }}
</span>
</td>

<td>
{{ $item->supplier ?? '—' }}
</td>

<td>
<span class="badge bg-info">
{{ $item->total_items }}
</span>
</td>

<td>
<span class="badge bg-success">
{{ $item->total_qty }}
</span>
</td>

<td>
{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}
</td>

<td>

<a href="{{ url('admin/stock-import/'.$item->code) }}"
class="btn btn-sm btn-outline-primary">

<i class="bi bi-eye"></i> Xem

</a>

</td>

</tr>

@empty

<tr>
<td colspan="7" class="text-center py-4">
<i class="bi bi-inbox"></i>
Chưa có phiếu nhập kho
</td>
</tr>

@endforelse

</tbody>

</table>

</div>

</div>

<div class="card-footer">

{{ $imports->links('pagination::bootstrap-5') }}

</div>

</div>

</div>

@endsection