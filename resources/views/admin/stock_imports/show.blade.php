@extends('layouts.admin')

@section('title','Phiếu nhập kho')

@section('content')

<div class="container-fluid">

<div class="card shadow border-0">

<div class="card-body">

<div class="d-flex justify-content-between align-items-center mb-4">

<h4 class="fw-bold">
PHIẾU NHẬP KHO
</h4>

<div>

<a href="{{ route('admin.stock.history') }}"
class="btn btn-secondary btn-sm me-2">
Quay lại </a>

<a href="{{ route('admin.stock.exportPdf',$code) }}"
class="btn btn-danger btn-sm"> <i class="bi bi-file-earmark-pdf"></i>
Xuất PDF </a>

</div>

</div>

<div class="row mb-4">

<div class="col-md-6">

<p>
<b>Mã phiếu:</b>
{{ $code }}
</p>

<p>
<b>Nhà cung cấp:</b>
{{ $items->first()->supplier ?? '---' }}
</p>

<p>
<b>Ngày nhập:</b>
{{ $items->first()->created_at->format('d/m/Y') }}
</p>

<p>
<b>Người nhập:</b>
{{ auth()->user()->name ?? 'Admin' }}
</p>

<p>
<b>Ghi chú:</b>
{{ $items->first()->note ?? '---' }}
</p>

</div>

<div class="col-md-6 text-end">

@php
$total = 0;
@endphp

</div>

</div>

<div class="table-responsive">

<table class="table table-bordered align-middle">

<thead class="table-light">

<tr>

<th width="70">STT</th>

<th>Sản phẩm</th>

<th width="220">Biến thể (SKU)</th>

<th width="120">Số lượng</th>

<th width="140">Giá nhập</th>

<th width="160">Thành tiền</th>

</tr>

</thead>

<tbody>

@foreach($items as $index => $item)

@php
$sub = $item->quantity * $item->cost_price;
$total += $sub;
@endphp

<tr>

<td>{{ $index + 1 }}</td>

<td>
{{ $item->variant->product->name ?? '-' }}
</td>

<td>
{{ $item->variant->attribute_value ?? '-' }}
</td>

<td>
{{ $item->quantity }}
</td>

<td>
{{ number_format($item->cost_price) }} đ
</td>

<td>
{{ number_format($sub) }} đ
</td>

</tr>

@endforeach

</tbody>

</table>

</div>

<div class="text-end mt-3">

<h5 class="fw-bold text-danger">

Tổng tiền:
{{ number_format($total) }} đ

</h5>

</div>

</div>

</div>

</div>

@endsection
