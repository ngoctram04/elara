@extends('layouts.admin')

@section('content')

<div class="container-fluid">

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-semibold mb-0">
            <i class="bi bi-bar-chart text-primary me-2"></i>
            Báo cáo & Thống kê chi tiết
        </h4>
        <small class="text-muted">
            Phân tích hoạt động kinh doanh theo thời gian
        </small>
    </div>

    {{-- Xuất PDF --}}
    <a href="{{ route('admin.reports.export', ['from'=>$from,'to'=>$to]) }}"
       class="btn btn-danger">
        <i class="bi bi-file-earmark-pdf"></i> Xuất PDF
    </a>
</div>


{{-- FILTER --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-3 align-items-end">

            <div class="col-md-3">
                <label class="form-label">Từ ngày</label>
                <input type="date" name="from" class="form-control" value="{{ $from }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">Đến ngày</label>
                <input type="date" name="to" class="form-control" value="{{ $to }}">
            </div>

            <div class="col-md-2">
                <button class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Xem báo cáo
                </button>
            </div>

        </form>
    </div>
</div>


{{-- =======================
1. TÀI CHÍNH
======================= --}}
<div class="row mb-4">

@php
$finance = $finance ?? (object)[];
$inventory = $inventory ?? (object)[];
@endphp

<div class="col-md-2">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <small class="text-muted">Doanh thu</small>
            <h6 class="fw-bold text-primary mb-0">
                {{ number_format($finance->revenue ?? 0) }} đ
            </h6>
        </div>
    </div>
</div>

<div class="col-md-2">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <small class="text-muted">Giá vốn</small>
            <h6 class="fw-bold text-danger mb-0">
                {{ number_format($finance->cost ?? 0) }} đ
            </h6>
        </div>
    </div>
</div>

<div class="col-md-2">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <small class="text-muted">Lợi nhuận</small>
            <h6 class="fw-bold {{ ($finance->profit ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                {{ number_format($finance->profit ?? 0) }} đ
            </h6>
        </div>
    </div>
</div>

<div class="col-md-2">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <small class="text-muted">Phí vận chuyển</small>
            <h6 class="fw-bold text-secondary mb-0">
                {{ number_format($finance->shipping_total ?? 0) }} đ
            </h6>
        </div>
    </div>
</div>

<div class="col-md-2">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <small class="text-muted">Giảm giá</small>
            <h6 class="fw-bold text-warning mb-0">
                {{ number_format($finance->discount_total ?? 0) }} đ
            </h6>
        </div>
    </div>
</div>

<div class="col-md-2">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <small class="text-muted">Giá trị tồn kho</small>
            <h6 class="fw-bold text-info mb-0">
                {{ number_format($inventory->total_value ?? 0) }} đ
            </h6>
        </div>
    </div>
</div>

</div>


{{-- =======================
2. BIỂU ĐỒ
======================= --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h5 class="fw-semibold mb-3">
            <i class="bi bi-graph-up text-success me-2"></i>
            Doanh thu theo ngày
        </h5>

        <canvas id="revenueChart" height="80"></canvas>
    </div>
</div>


{{-- =======================
3. ĐƠN HÀNG
======================= --}}
@php
$orderStats = $orderStats ?? (object)[];
@endphp

<div class="row mb-4">

<div class="col-md-3">
    <div class="card border-0 shadow-sm text-center">
        <div class="card-body">
            <small class="text-muted">Đang xử lý</small>
            <h5 class="fw-bold text-warning">{{ $orderStats->pending ?? 0 }}</h5>
        </div>
    </div>
</div>

<div class="col-md-3">
    <div class="card border-0 shadow-sm text-center">
        <div class="card-body">
            <small class="text-muted">Đang giao</small>
            <h5 class="fw-bold text-primary">{{ $orderStats->shipping ?? 0 }}</h5>
        </div>
    </div>
</div>

<div class="col-md-3">
    <div class="card border-0 shadow-sm text-center">
        <div class="card-body">
            <small class="text-muted">Đã giao</small>
            <h5 class="fw-bold text-success">{{ $orderStats->completed ?? 0 }}</h5>
        </div>
    </div>
</div>

<div class="col-md-3">
    <div class="card border-0 shadow-sm text-center">
        <div class="card-body">
            <small class="text-muted">Đã huỷ</small>
            <h5 class="fw-bold text-danger">{{ $orderStats->cancelled ?? 0 }}</h5>
        </div>
    </div>
</div>

</div>


{{-- =======================
4. TOP SẢN PHẨM
======================= --}}
<div class="row mb-4">

{{-- Top bán --}}
<div class="col-md-6">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="fw-semibold mb-3">Top bán chạy</h5>

            <table class="table table-sm table-bordered">
                <thead class="table-light">
                <tr>
                    <th>Sản phẩm</th>
                    <th class="text-center">SL</th>
                    <th class="text-end">Doanh thu</th>
                </tr>
                </thead>
                <tbody>
                @foreach($topProducts as $p)
                    <tr>
                        <td>{{ $p->name }}</td>
                        <td class="text-center">{{ $p->total_sold }}</td>
                        <td class="text-end">{{ number_format($p->revenue) }} đ</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Top lợi nhuận --}}
<div class="col-md-6">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="fw-semibold mb-3">Top lợi nhuận</h5>

            <table class="table table-sm table-bordered">
                <thead class="table-light">
                <tr>
                    <th>Sản phẩm</th>
                    <th class="text-end">Lợi nhuận</th>
                </tr>
                </thead>
                <tbody>
                @foreach($topProfitProducts as $p)
                    <tr>
                        <td>{{ $p->name }}</td>
                        <td class="text-end text-success fw-semibold">
                            {{ number_format($p->profit) }} đ
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>


{{-- =======================
5. SẮP HẾT
======================= --}}
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h5 class="fw-semibold mb-3">Sản phẩm sắp hết</h5>

        <table class="table table-sm table-bordered">
            <thead class="table-light">
            <tr>
                <th>Sản phẩm</th>
                <th>Biến thể</th>
                <th class="text-center">Tồn</th>
            </tr>
            </thead>
            <tbody>
            @foreach($lowStock as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->attribute_value }}</td>
                    <td class="text-center text-danger fw-bold">
                        {{ $item->stock_quantity }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

</div>


{{-- =======================
CHART JS
======================= --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('revenueChart');

const labels = @json($dailyRevenue->pluck('date'));
const data = @json($dailyRevenue->pluck('revenue'));

new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Doanh thu',
            data: data,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: true }
        }
    }
});
</script>

@endsection