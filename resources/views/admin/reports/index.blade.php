@extends('layouts.admin')

@section('content')
<div class="container-fluid">

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-semibold mb-0">
            <i class="bi bi-bar-chart text-primary me-2"></i>
            Dashboard Báo Cáo
        </h4>
        <small class="text-muted">Phân tích hoạt động kinh doanh</small>
    </div>

    <form method="POST" action="{{ route('admin.reports.exportPdf') }}" id="exportForm">
        @csrf
        <input type="hidden" name="from" value="{{ $from }}">
        <input type="hidden" name="to" value="{{ $to }}">
        <input type="hidden" name="chart_image" id="chart_image">

        <button type="button" onclick="exportPdf()" class="btn btn-danger">
            Xuất PDF
        </button>
    </form>
</div>


{{-- FILTER --}}
<div class="card shadow-sm mb-4">
<div class="card-body">
<form method="GET" action="{{ route('admin.reports.index') }}" class="row g-3">
    <div class="col-md-3">
        <label>Từ ngày</label>
        <input type="date" name="from" class="form-control" value="{{ $from }}">
    </div>

    <div class="col-md-3">
        <label>Đến ngày</label>
        <input type="date" name="to" class="form-control" value="{{ $to }}">
    </div>

    <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary w-100">Xem</button>
    </div>
</form>
</div>
</div>


{{-- KPI HÀNG 1: HIỆU QUẢ --}}
<div class="row mb-4">

    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <small class="text-muted">Doanh thu</small>
                <h5 class="text-primary fw-bold">
                    {{ number_format($revenue) }} đ
                </h5>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <small class="text-muted">Lợi nhuận</small>
                <h5 class="text-success fw-bold">
                    {{ number_format($profit) }} đ
                </h5>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <small class="text-muted">Đơn thành công</small>
                <h5 class="fw-bold">
                    {{ number_format($totalOrders) }}
                </h5>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <small class="text-muted">Tỷ lệ huỷ</small>
                <h5 class="text-danger fw-bold">
                    {{ number_format($cancelRate,1) }}%
                </h5>
            </div>
        </div>
    </div>

</div>
{{-- KPI HÀNG 2: DÒNG TIỀN --}}
<div class="row mb-4">

    {{-- Giá vốn --}}
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <small class="text-muted">Tổng vốn đã bán</small>
                <h5 class="fw-bold">
                    {{ number_format($totalCost) }} đ
                </h5>
            </div>
        </div>
    </div>

    {{-- Ship khách trả --}}
    <div class="col-md-2">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <small class="text-muted">Ship khách trả</small>
                <h6 class="fw-bold">
                    {{ number_format($shippingCollected) }} đ
                </h6>
            </div>
        </div>
    </div>

    {{-- Ship shop trả --}}
    <div class="col-md-2">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <small class="text-muted">Ship shop trả</small>
                <h6 class="text-danger fw-bold">
                    {{ number_format($shippingPaid) }} đ
                </h6>
            </div>
        </div>
    </div>

    {{-- Chi phí freeship --}}
    <div class="col-md-2">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <small class="text-muted">Chi phí freeship</small>
                <h6 class="text-warning fw-bold">
                    {{ number_format($freeShippingLoss) }} đ
                </h6>
            </div>
        </div>
    </div>

    {{-- Lãi / lỗ vận chuyển --}}
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <small class="text-muted">Lãi / lỗ vận chuyển</small>
                <h5 class="fw-bold {{ $shippingProfit < 0 ? 'text-danger' : 'text-success' }}">
                    {{ number_format($shippingProfit) }} đ
                </h5>
            </div>
        </div>
    </div>

</div>

{{-- HÀNG KPI PHỤ --}}
<div class="row mb-4">

    {{-- Giá trị tồn kho --}}
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <small class="text-muted">Giá trị tồn kho</small>
                <h5 class="fw-bold">
                    {{ number_format($inventoryValue) }} đ
                </h5>
            </div>
        </div>
    </div>

    {{-- Tổng vốn nhập --}}
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <small class="text-muted">Tổng vốn nhập</small>
                <h5 class="fw-bold">
                    {{ number_format($totalImport) }} đ
                </h5>
            </div>
        </div>
    </div>

</div>


{{-- BIỂU ĐỒ --}}
<div class="card shadow-sm mb-4">
<div class="card-body">
<h6>Doanh thu theo ngày</h6>
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
<div class="card shadow-sm">
<div class="card-body">

<div class="d-flex justify-content-between mb-2">
<h6 class="mb-0">Sắp hết hàng</h6>
<a href="{{ route('admin.reports.lowStock') }}" class="small">Xem tất cả</a>
</div>

<table class="table table-sm">
@forelse($lowStock as $item)
<tr>
<td>{{ $item->name }}</td>
<td>{{ $item->attribute_value }}</td>
<td class="text-danger text-center">{{ $item->stock_quantity }}</td>
</tr>
@empty
<tr><td class="text-center text-muted">Không có dữ liệu</td></tr>
@endforelse
</table>

</div>
</div>
</div>


{{-- CHART + EXPORT --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let revenueChart = new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: @json($dailyRevenue->pluck('date')),
        datasets: [{
            label: 'Doanh thu',
            data: @json($dailyRevenue->pluck('revenue')),
            tension: 0.3,
            borderWidth: 2,
            fill: false
        }]
    }
});

function exportPdf() {
    const img = revenueChart.toBase64Image();
    document.getElementById('chart_image').value = img;
    document.getElementById('exportForm').submit();
}
</script>

@endsection