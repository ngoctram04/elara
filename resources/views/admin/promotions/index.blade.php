@extends('layouts.admin')

@section('title', 'Quản lý khuyến mãi')

@section('content')

<div class="card border-0 shadow-sm">
<div class="card-body">

{{-- HEADER --}}

<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h5 class="fw-bold mb-1">
Quản lý khuyến mãi
</h5>

<small class="text-muted">
Quản lý các chương trình khuyến mãi và voucher đổi điểm
</small>
</div>

<div class="d-flex gap-2">

<a href="{{ route('admin.promotions.create') }}"
class="btn btn-primary btn-sm">

<i class="bi bi-plus-lg"></i>
Thêm khuyến mãi

</a>

<a href="{{ route('admin.promotions.createReward') }}"
class="btn btn-success btn-sm">

<i class="bi bi-gift"></i>
Voucher đổi điểm

</a>

</div>

</div>

{{-- ================= KHUYẾN MÃI HỆ THỐNG ================= --}}

<h6 class="fw-semibold mb-3 text-primary">

<i class="bi bi-tag"></i>
Khuyến mãi hệ thống

</h6>

<div class="table-responsive mb-5">

<table class="table table-hover align-middle mb-0">

<thead class="table-light text-center">

<tr>

<th style="width:60px">Mã</th>

<th>Tên</th>

<th style="width:120px">Loại</th>

<th style="width:110px">Giảm</th>

<th style="width:180px">Thời gian</th>

<th style="width:120px">Trạng thái</th>

<th style="width:140px">Tình trạng</th>

<th style="width:150px">Hành động</th>

</tr>

</thead>

<tbody>

@forelse ($promotions as $promo)

<tr>

<td class="text-center text-muted fw-semibold">
#{{ $promo->id }}
</td>

<td>

<strong>
{{ $promo->name }}
</strong>

@if ($promo->code)

<br>

<span class="badge bg-info mt-1">
{{ $promo->code }}
</span>

@endif

</td>

<td class="text-center">

<span class="badge bg-secondary">

{{ $promo->type === 'order' ? 'Đơn hàng' : 'Sản phẩm' }}

</span>

</td>

<td class="text-center text-danger fw-semibold">

-{{ $promo->discount_value }}

{{ $promo->discount_type === 'percent' ? '%' : 'đ' }}

</td>

<td class="small text-center">

{{ \Carbon\Carbon::parse($promo->start_date)->format('d/m/Y') }}

<br>

→

{{ \Carbon\Carbon::parse($promo->end_date)->format('d/m/Y') }}

</td>

<td class="text-center">

<span class="badge {{ $promo->is_active ? 'bg-success' : 'bg-danger' }}">

{{ $promo->is_active ? 'Đang bật' : 'Đã tắt' }}

</span>

</td>

<td class="text-center">

@php

$now = now();

if ($now->lt($promo->start_date)) {

$color = 'secondary';
$label = 'Chưa bắt đầu';

} elseif ($now->gt($promo->end_date)) {

$color = 'dark';
$label = 'Đã hết hạn';

} else {

$color = 'success';
$label = 'Đang diễn ra';

}

@endphp

<span class="badge bg-{{ $color }}">

{{ $label }}

</span>

</td>

<td class="text-center">

<a href="{{ route('admin.promotions.edit', $promo->id) }}"
class="btn btn-outline-warning btn-sm">

<i class="bi bi-pencil"></i>

</a>

<form action="{{ route('admin.promotions.toggle', $promo->id) }}"
method="POST"
class="d-inline">

@csrf
@method('PATCH')

<button class="btn btn-outline-secondary btn-sm">

{{ $promo->is_active ? 'Tắt' : 'Bật' }}

</button>

</form>

</td>

</tr>

@empty

<tr>

<td colspan="8"
class="text-center text-muted py-4">

<i class="bi bi-inbox"></i>

<br>

Chưa có khuyến mãi nào

</td>

</tr>

@endforelse

</tbody>

</table>

@if($promotions->hasPages())

<div class="mt-4">

{{ $promotions->links('pagination::bootstrap-5') }}

</div>

@endif

</div>

{{-- ================= VOUCHER ĐỔI ĐIỂM ================= --}}

<h6 class="fw-semibold mb-3 text-success">

<i class="bi bi-gift"></i>

Voucher đổi điểm

</h6>

<div class="table-responsive">

<table class="table table-hover align-middle mb-0">

<thead class="table-light text-center">

<tr>

<th style="width:60px">#</th>

<th>Tên</th>

<th style="width:150px">Điểm cần</th>

<th style="width:120px">Giảm</th>

<th style="width:120px">Hiệu lực</th>

</tr>

</thead>

<tbody>

@forelse ($rewards as $reward)

<tr>

<td class="text-center text-muted fw-semibold">

{{ $reward->id }}

</td>

<td>

{{ $reward->title }}

</td>

<td class="text-center text-primary fw-semibold">

{{ number_format($reward->points_required) }}

điểm

</td>

<td class="text-center text-danger">

-{{ $reward->discount_value }}

{{ $reward->discount_type === 'percent' ? '%' : 'đ' }}

</td>

<td class="text-center">

{{ $reward->valid_days }} ngày

</td>

</tr>

@empty

<tr>

<td colspan="5"
class="text-center text-muted py-4">

<i class="bi bi-inbox"></i>

<br>

Chưa có voucher đổi điểm

</td>

</tr>

@endforelse

</tbody>

</table>

@if($rewards->hasPages())

<div class="mt-4">

{{ $rewards->links('pagination::bootstrap-5') }}

</div>

@endif

</div>

</div>
</div>

@endsection
