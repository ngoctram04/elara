@extends('layouts.admin')

@section('title','Quản lý lô hàng')

@section('content')

<div class="card border-0 shadow-sm">
<div class="card-body">

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Quản lý lô hàng</h5>
        <small class="text-muted">Theo dõi tất cả các lô hàng</small>
    </div>

    <span class="badge bg-warning text-dark">
        {{ $lots->total() }} lô
    </span>
</div>

{{-- FILTER --}}
<form method="GET" class="row g-2 mb-4">

    <div class="col-md-4">
        <input type="text" name="keyword"
            value="{{ request('keyword') }}"
            class="form-control form-control-sm"
            placeholder="Tên SP / Mã SP / Mã lô...">
    </div>

    <div class="col-md-3">
        <select name="status" class="form-select form-select-sm">
            <option value="">Tất cả trạng thái</option>
            <option value="normal" {{ request('status')=='normal'?'selected':'' }}>Bình thường</option>
            <option value="sale" {{ request('status')=='sale'?'selected':'' }}>Nên sale</option>
            <option value="danger" {{ request('status')=='danger'?'selected':'' }}>Sắp huỷ</option>
            <option value="expired" {{ request('status')=='expired'?'selected':'' }}>Đã huỷ</option>
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
            <i class="bi bi-search me-1"></i> Lọc
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
    🟢 Bình thường (> 7 tháng)<br>
    🟡 ≤ 7 tháng → nên sale<br>
    🔴 ≤ 6 tháng → sắp huỷ<br>
    ⚫ Lô huỷ vẫn hiển thị
</div>

{{-- TABLE --}}
<div class="table-responsive">
<table class="table table-hover align-middle mb-0">

<thead class="table-light">
<tr>
    <th>Ảnh</th>
    <th>Mã lô</th>
    <th>Sản phẩm</th>
    <th>Biến thể</th>
    <th class="text-center">Số lượng</th>
    <th class="text-end">Giá trị</th>
    <th class="text-center">HSD</th>
    <th class="text-center">Trạng thái</th>
</tr>
</thead>

<tbody>

@forelse($lots as $lot)

@php
$expiry = \Carbon\Carbon::parse($lot->expiry_date);
$now = now();
$days = $now->diffInDays($expiry, false);
$months = $days / 30;

// 💰 TÍNH TIỀN
$totalCost = $lot->imported_quantity * $lot->cost_price;
$remainingValue = $lot->remaining_quantity * $lot->cost_price;
$expiredValue = $lot->expired_quantity * $lot->cost_price;
@endphp

<tr class="
@if($lot->expired_at) table-secondary
@elseif($months >= 0 && $months <= 6) table-danger
@elseif($months > 6 && $months <= 7) table-warning
@endif
">

<td>
    @if($lot->image_path)
        <img src="{{ asset('storage/'.$lot->image_path) }}" width="50" class="rounded border">
    @endif
</td>

<td>
    <span class="badge bg-dark">
        {{ $lot->code }}
    </span>
</td>

<td class="fw-medium">
    {{ $lot->product_name }}<br>
    <small class="text-muted">#{{ $lot->product_id }}</small>
</td>

<td>{{ $lot->attribute_value }}</td>

{{-- SỐ LƯỢNG --}}
<td class="text-center small">
    <div>Nhập: <b>{{ $lot->imported_quantity }}</b></div>
    <div class="text-success">Bán: {{ $lot->sold_quantity }}</div>
    <div class="text-muted">Huỷ: {{ $lot->expired_quantity }}</div>
</td>

{{-- GIÁ TRỊ --}}
<td class="text-end small">
    <div>Nhập: <b>{{ number_format($totalCost) }}đ</b></div>
    <div class="text-success">Còn: {{ number_format($remainingValue) }}đ</div>
    <div class="text-danger">Hao hụt: {{ number_format($expiredValue) }}đ</div>
</td>

{{-- HSD --}}
<td class="text-center small">
    <div>{{ $expiry->format('d/m/Y') }}</div>
    <div class="text-muted">
        {{ $months < 0 ? 'Hết hạn' : number_format($months,1).' tháng' }}
    </div>
</td>

{{-- TRẠNG THÁI --}}
<td class="text-center">
    @if($lot->expired_at)
        <span class="badge bg-secondary">Đã huỷ</span>
    @elseif($months <= 6)
        <span class="badge bg-danger">Sắp huỷ</span>
    @elseif($months <= 7)
        <span class="badge bg-warning text-dark">Nên sale</span>
    @else
        <span class="badge bg-success">Bình thường</span>
    @endif
</td>

</tr>

@empty
<tr>
<td colspan="8" class="text-center text-muted py-4">
    Không có lô hàng
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