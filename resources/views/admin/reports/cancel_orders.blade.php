@extends('layouts.admin')

@section('title','Danh sách bom hàng')

@section('content')

<div class="card shadow-sm border-0">
<div class="card-body">

<h5 class="fw-bold mb-3 text-danger">
Danh sách bom hàng (đơn huỷ)
</h5>

<form class="row g-2 mb-3">

<div class="col-md-3">
<input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
</div>

<div class="col-md-3">
<input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
</div>

<div class="col-md-3">
<input type="text" name="keyword" value="{{ $keyword }}" placeholder="Tên khách..."
class="form-control form-control-sm">
</div>

<div class="col-md-2">
<button class="btn btn-primary btn-sm w-100">Lọc</button>
</div>

</form>

<table class="table table-bordered align-middle">

<thead>
<tr>
<th>#</th>
<th>Khách hàng</th>
<th class="text-center">Tiền</th>
<th class="text-center">Ngày</th>
</tr>
</thead>

<tbody>

@forelse($orders as $o)
<tr>

<td>#{{ $o->id }}</td>

<td>{{ $o->customer_name }}</td>

<td class="text-danger text-center">
{{ number_format($o->total) }} đ
</td>

<td class="text-center">
{{ \Carbon\Carbon::parse($o->created_at)->format('d/m/Y') }}
</td>

</tr>
@empty
<tr>
<td colspan="4" class="text-center text-muted">
Không có dữ liệu
</td>
</tr>
@endforelse

</tbody>

</table>

{{ $orders->links() }}

</div>
</div>

@endsection