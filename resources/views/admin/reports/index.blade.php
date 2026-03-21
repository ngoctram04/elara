@extends('layouts.admin')

@section('title','Dashboard báo cáo')

@section('content')

<div class="card border-0 shadow-sm">
<div class="card-body">

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h5 class="fw-bold mb-1">
<i class="bi bi-bar-chart me-1"></i>
Dashboard báo cáo
</h5>

<small class="text-muted">
Phân tích hoạt động kinh doanh
</small>
</div>

<form method="POST"
action="{{ route('admin.reports.exportPdf') }}"
id="exportForm">

@csrf

<input type="hidden" name="from" value="{{ $from }}">
<input type="hidden" name="to" value="{{ $to }}">
<input type="hidden" name="chart_image" id="chart_image">

<button type="button"
onclick="exportPdf()"
class="btn btn-danger btn-sm">

<i class="bi bi-file-earmark-pdf"></i>
Xuất PDF

</button>

</form>

</div>



{{-- FILTER --}}
<form method="GET"
action="{{ route('admin.reports.index') }}"
class="row g-2 mb-4 align-items-end">

<div class="col-md-3">

<label class="small text-muted">
Từ ngày
</label>

<input type="date"
name="from"
value="{{ $from }}"
class="form-control form-control-sm">

</div>

<div class="col-md-3">

<label class="small text-muted">
Đến ngày
</label>

<input type="date"
name="to"
value="{{ $to }}"
class="form-control form-control-sm">

</div>

<div class="col-md-2 d-flex gap-2">

<button class="btn btn-outline-primary btn-sm">

<i class="bi bi-search"></i>
Xem

</button>

<a href="{{ route('admin.reports.index') }}"
class="btn btn-outline-secondary btn-sm">

Đặt lại

</a>

</div>

</form>



{{-- KPI --}}
<div class="row g-3 mb-4">

<div class="col-md-3">
<div class="card border-0 shadow-sm">
<div class="card-body">

<small class="text-muted">
Doanh thu
</small>

<h5 class="fw-bold text-primary mb-0">
{{ number_format($revenue) }} đ
</h5>

</div>
</div>
</div>


<div class="col-md-3">
<div class="card border-0 shadow-sm">
<div class="card-body">

<small class="text-muted">
Lợi nhuận
</small>

<h5 class="fw-bold {{ $profit>=0?'text-success':'text-danger' }} mb-0">
{{ number_format($profit) }} đ
</h5>

</div>
</div>
</div>


<div class="col-md-3">
<div class="card border-0 shadow-sm">
<div class="card-body">

<small class="text-muted">
Đơn thành công
</small>

<h5 class="fw-bold mb-0">
{{ number_format($totalOrders) }}
</h5>

</div>
</div>
</div>


<div class="col-md-3">
<div class="card border-0 shadow-sm">
<div class="card-body">

<small class="text-muted">
Tỷ lệ huỷ
</small>

<h5 class="fw-bold text-danger mb-0">
{{ number_format($cancelRate,1) }}%
</h5>

</div>
</div>
</div>

</div>



{{-- KPI 2 --}}
<div class="row g-3 mb-4">

<div class="col-md-3">
<div class="card border-0 shadow-sm">
<div class="card-body">

<small class="text-muted">
Tổng vốn đã bán
</small>

<h6 class="fw-bold mb-0">
{{ number_format($totalCost) }} đ
</h6>

</div>
</div>
</div>


<div class="col-md-3">
<div class="card border-0 shadow-sm">
<div class="card-body">

<small class="text-muted">
Giá trị tồn kho
</small>

<h6 class="fw-bold mb-0">
{{ number_format($inventoryValue) }} đ
</h6>

</div>
</div>
</div>


<div class="col-md-3">
<div class="card border-0 shadow-sm">
<div class="card-body">

<small class="text-muted">
Tổng vốn nhập
</small>

<h6 class="fw-bold mb-0">
{{ number_format($totalImport) }} đ
</h6>

</div>
</div>
</div>


<div class="col-md-3">
<div class="card border-0 shadow-sm">
<div class="card-body">

<small class="text-muted">
Tổng hao hụt
</small>

<h6 class="fw-bold text-danger mb-0">
{{ number_format($inventoryLoss) }} đ
</h6>

</div>
</div>
</div>

</div>



{{-- SHIPPING --}}
<div class="row g-3 mb-4">

<div class="col-md-6">

<div class="card border-0 shadow-sm">
<div class="card-body">

<small class="text-muted">
Nợ đơn vị vận chuyển
</small>

<h5 class="fw-bold text-warning mb-2">
{{ number_format($shippingDebt) }} đ
</h5>

@if($shippingDebt>0)

<form method="POST"
action="{{ route('admin.reports.payShipping') }}">

@csrf

<input type="hidden"
name="amount"
value="{{ $shippingDebt }}">

<button class="btn btn-warning btn-sm">

Trả tiền ship

</button>

</form>

@endif

</div>
</div>

</div>


<div class="col-md-6">

<div class="card border-0 shadow-sm">
<div class="card-body">

<small class="text-muted">
Đã trả vận chuyển
</small>

<h5 class="fw-bold text-success mb-0">
{{ number_format($shippingPaidTotal) }} đ
</h5>

</div>
</div>

</div>

</div>



{{-- CHART --}}
<div class="card border-0 shadow-sm mb-4">

<div class="card-body">

<h6 class="fw-semibold mb-3">
Doanh thu theo ngày
</h6>

<canvas id="revenueChart" height="80"></canvas>

</div>

</div>


{{-- TOP SẢN PHẨM & TỒN --}}
<div class="row mb-4">

<div class="col-md-6">
<div class="card shadow-sm">
<div class="card-body">

<div class="d-flex justify-content-between mb-2">
<h6 class="mb-0">Top bán chạy</h6>
<a href="{{ route('admin.reports.products', ['from'=>$from,'to'=>$to]) }}" class="small">Xem tất cả</a>
</div>

<table class="table table-sm">
@forelse($topProducts as $p)
<tr>
<td>{{ $p->name }}</td>
<td class="text-end">{{ $p->total_sold }}</td>
</tr>
@empty
<tr><td class="text-center text-muted">Không có dữ liệu</td></tr>
@endforelse
</table>

</div>
</div>
</div>


<div class="col-md-6">
<div class="card shadow-sm">
<div class="card-body">

<div class="d-flex justify-content-between mb-2">
<h6 class="mb-0">Sản phẩm tồn lâu</h6>
<a href="{{ route('admin.reports.slowProducts') }}" class="small">Xem tất cả</a>
</div>

<table class="table table-sm">
@forelse($slowMoving as $p)
<tr>
<td>{{ $p->name }}</td>
<td class="text-center">{{ $p->stock_quantity }}</td>
<td class="text-muted text-end">
{{ $p->last_sold ? \Carbon\Carbon::parse($p->last_sold)->format('d/m/Y') : 'Chưa bán' }}
</td>
</tr>
@empty
<tr><td class="text-center text-muted">Không có dữ liệu</td></tr>
@endforelse
</table>

</div>
</div>
</div>

</div>


{{-- KHÁCH HÀNG --}}
<div class="row mb-4">

<div class="col-md-6">
<div class="card shadow-sm">
<div class="card-body">

<div class="d-flex justify-content-between mb-2">
    <h6 class="mb-0">Sản phẩm được quan tâm</h6>
    <a href="{{ route('admin.reports.wishlist') }}" class="small">
        Xem tất cả
    </a>
</div>

<table class="table table-sm">
@forelse($mostViewed as $p)
<tr>
<td>{{ $p->name }}</td>
<td class="text-end">{{ $p->total_wishlist }}</td>
</tr>
@empty
<tr><td class="text-center text-muted">Không có dữ liệu</td></tr>
@endforelse
</table>

</div>
</div>
</div>


<div class="col-md-6">
<div class="card shadow-sm">
<div class="card-body">

<div class="d-flex justify-content-between mb-2">
<h6 class="mb-0">Top khách hàng</h6>
<a href="{{ route('admin.reports.customers') }}" class="small">Xem tất cả</a>
</div>

<table class="table table-sm">
@forelse($topCustomers as $c)
<tr>
<td>{{ $c->name }}</td>
<td class="text-end">{{ number_format($c->spending) }} đ</td>
</tr>
@empty
<tr><td class="text-center text-muted">Không có dữ liệu</td></tr>
@endforelse
</table>

</div>
</div>
</div>

</div>

{{-- LOW STOCK --}}
<div class="row">

{{-- SẮP HẾT HÀNG --}}
<div class="col-md-6">
<div class="card border-0 shadow-sm">
<div class="card-body">

<div class="d-flex justify-content-between mb-2">
<h6 class="fw-semibold mb-0">
Sắp hết hàng
</h6>

<a href="{{ route('admin.reports.lowStock') }}"
class="small">
Xem tất cả
</a>
</div>

<table class="table table-sm align-middle mb-0">

@forelse($lowStock as $item)
<tr>
<td>{{ $item->name }}</td>
<td>{{ $item->attribute_value }}</td>
<td class="text-danger text-center fw-semibold">
{{ $item->stock_quantity }}
</td>
</tr>
@empty
<tr>
<td class="text-center text-muted py-3">
Không có dữ liệu
</td>
</tr>
@endforelse

</table>

</div>
</div>
</div>



{{-- BOM HÀNG --}}
<div class="col-md-6">
<div class="card border-0 shadow-sm">
<div class="card-body">

<div class="d-flex justify-content-between mb-2">

<h6 class="fw-semibold mb-0 text-danger">
Bom hàng (đơn huỷ)
</h6>

<a href="{{ route('admin.orders.index', ['status' => 4]) }}"
class="small">
Xem tất cả
</a>

</div>

<table class="table table-sm align-middle mb-0">

<thead>
<tr>
<th>#</th>
<th>Khách</th>
<th class="text-center">Tiền</th>
<th class="text-end">Ngày</th>
</tr>
</thead>

<tbody>

@forelse($cancelList as $order)
<tr>

<td>#{{ $order->id }}</td>

<td>
{{ $order->customer_name ?? '---' }}
</td>

<td class="text-danger text-center">
{{ number_format($order->total) }} đ
</td>

<td class="text-muted text-end">
{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}
</td>

</tr>
@empty
<tr>
<td colspan="4" class="text-center text-muted py-3">
Không có dữ liệu
</td>
</tr>
@endforelse

</tbody>
</table>

</div>
</div>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

let revenueChart=new Chart(document.getElementById('revenueChart'),{

type:'line',

data:{

labels:@json($dailyRevenue->pluck('date')->toArray()),

datasets:[{

label:'Doanh thu',

data:@json($dailyRevenue->pluck('revenue')->toArray()),

tension:0.3,

borderWidth:2,

fill:false

}]

}

})



function exportPdf(){

const img=revenueChart.toBase64Image()

document.getElementById('chart_image').value=img

document.getElementById('exportForm').submit()

}

</script>

@endsection