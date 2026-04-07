@extends('layouts.admin')

@section('title', 'Dashboard báo cáo')

@push('styles')
    @vite('resources/css/reports.css')
@endpush

@section('content')
<div class="report-page">
    <div class="report-header-card">
        <div class="report-header-left">
            <div>
                <h4 class="report-page-title mb-1">Dashboard báo cáo</h4>
                <p class="report-page-subtitle mb-0">
                    Phân tích hoạt động kinh doanh, tồn kho, vận chuyển và hiệu quả bán hàng
                </p>
            </div>
        </div>

        <form method="POST"
              action="{{ route('admin.reports.exportPdf') }}"
              id="exportForm"
              class="report-export-form">
            @csrf
            <input type="hidden" name="from" value="{{ $from }}">
            <input type="hidden" name="to" value="{{ $to }}">
            <input type="hidden" name="chart_image" id="chart_image">

            <button type="button" onclick="exportPdf()" class="btn btn-danger report-btn">
                <i class="bi bi-file-earmark-pdf me-1"></i>
                Xuất PDF
            </button>
        </form>
    </div>

    <div class="report-filter-card">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-3 align-items-end">
            <div class="col-lg-3 col-md-4">
                <label class="form-label report-label">Từ ngày</label>
                <input type="date" name="from" value="{{ $from }}" class="form-control report-input">
            </div>

            <div class="col-lg-3 col-md-4">
                <label class="form-label report-label">Đến ngày</label>
                <input type="date" name="to" value="{{ $to }}" class="form-control report-input">
            </div>

            <div class="col-lg-4 col-md-4">
                <div class="d-flex gap-2 report-filter-actions">
                    <button class="btn btn-primary report-btn">
                        <i class="bi bi-search me-1"></i>
                        Xem báo cáo
                    </button>

                    <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary report-btn">
                        <i class="bi bi-arrow-clockwise me-1"></i>
                        Đặt lại
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- KPI CHÍNH --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="report-stat-card stat-primary">
                <div class="report-stat-top">
                    <span class="report-stat-label">Doanh thu</span>
                    <span class="report-stat-icon"><i class="bi bi-cash-stack"></i></span>
                </div>
                <div class="report-stat-value">{{ number_format($revenue) }} đ</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="report-stat-card {{ $profit >= 0 ? 'stat-success' : 'stat-danger' }}">
                <div class="report-stat-top">
                    <span class="report-stat-label">Lợi nhuận</span>
                    <span class="report-stat-icon"><i class="bi bi-graph-up-arrow"></i></span>
                </div>
                <div class="report-stat-value">{{ number_format($profit) }} đ</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="report-stat-card stat-neutral">
                <div class="report-stat-top">
                    <span class="report-stat-label">Đơn thành công</span>
                    <span class="report-stat-icon"><i class="bi bi-bag-check"></i></span>
                </div>
                <div class="report-stat-value">{{ number_format($totalOrders) }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="report-stat-card stat-warning">
                <div class="report-stat-top">
                    <span class="report-stat-label">Tỷ lệ huỷ</span>
                    <span class="report-stat-icon"><i class="bi bi-x-octagon"></i></span>
                </div>
                <div class="report-stat-value">{{ number_format($cancelRate, 1) }}%</div>
            </div>
        </div>
    </div>

    {{-- KHO / VỐN / HAO HỤT --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="report-mini-card">
                <div class="report-mini-label">Tồn đầu kỳ</div>
                <div class="report-mini-value">{{ number_format($openingInventoryValue) }} đ</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="report-mini-card">
                <div class="report-mini-label">Tổng vốn nhập trong kỳ</div>
                <div class="report-mini-value">{{ number_format($totalImport) }} đ</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="report-mini-card">
                <div class="report-mini-label">Tổng vốn đã bán trong kỳ</div>
                <div class="report-mini-value">{{ number_format($totalCost) }} đ</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="report-mini-card report-mini-danger">
                <div class="report-mini-label">Tổng hao hụt trong kỳ</div>
                <div class="report-mini-value">{{ number_format($inventoryLoss) }} đ</div>
            </div>
        </div>
    </div>

    {{-- TỒN CUỐI KỲ + SHIP --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="report-highlight-card">
                <div class="report-section-heading">
                    <h6 class="mb-0">Tồn cuối kỳ</h6>
                </div>
                <div class="report-highlight-value">{{ number_format($inventoryValue) }} đ</div>
                <div class="report-highlight-note">
                    Giá trị hàng tồn kho còn lại tại cuối khoảng thời gian lọc báo cáo
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="report-highlight-card">
                <div class="report-section-heading">
                    <h6 class="mb-0">Chi phí vận chuyển</h6>
                </div>

                <div class="report-highlight-value text-danger">
                    {{ number_format($shippingCostTotal) }} đ
                </div>

                <div class="report-ship-info">
                    <div class="report-ship-row">
                        <span>Khách trả</span>
                        <strong>{{ number_format($shippingCollected) }} đ</strong>
                    </div>

                    @if($freeShippingLoss > 0)
                        <div class="report-ship-row text-danger">
                            <span>Shop bù phí ship</span>
                            <strong>{{ number_format($freeShippingLoss) }} đ</strong>
                        </div>
                    @else
                        <div class="report-ship-row text-success">
                            <span>Trạng thái</span>
                            <strong>Phí ship không bị lỗ</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- CHART --}}
    <div class="report-panel mb-4">
        <div class="report-panel-header">
            <div>
                <h6 class="mb-1">Doanh thu và lợi nhuận theo ngày</h6>
                <p class="mb-0 text-muted small">So sánh biến động doanh thu và lợi nhuận theo từng ngày</p>
            </div>
        </div>

        <div class="report-chart-wrap">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    {{-- CÁC BẢNG 1 --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="report-panel h-100">
                <div class="report-panel-header">
                    <h6 class="mb-0">Top bán chạy</h6>
                    <a href="{{ route('admin.reports.products', ['from' => $from, 'to' => $to]) }}" class="report-link">
                        Xem tất cả
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table report-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th class="text-end">Đã bán</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $p)
                                <tr>
                                    <td>{{ $p->name }}</td>
                                    <td class="text-end fw-semibold">{{ $p->total_sold }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="report-empty">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="report-panel h-100">
                <div class="report-panel-header">
                    <h6 class="mb-0">Sản phẩm tồn lâu</h6>
                    <a href="{{ route('admin.reports.slowProducts') }}" class="report-link">
                        Xem tất cả
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table report-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th class="text-center">Tồn</th>
                                <th class="text-end">Lần bán cuối</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($slowMoving as $p)
                                <tr>
                                    <td>{{ $p->name }}</td>
                                    <td class="text-center fw-semibold">{{ $p->stock_quantity }}</td>
                                    <td class="text-end text-muted">
                                        {{ $p->last_sold ? \Carbon\Carbon::parse($p->last_sold)->format('d/m/Y') : 'Chưa bán' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="report-empty">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- CÁC BẢNG 2 --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="report-panel h-100">
                <div class="report-panel-header">
                    <h6 class="mb-0">Sản phẩm được quan tâm</h6>
                    <a href="{{ route('admin.reports.wishlist') }}" class="report-link">
                        Xem tất cả
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table report-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th class="text-end">Lượt yêu thích</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mostViewed as $p)
                                <tr>
                                    <td>{{ $p->name }}</td>
                                    <td class="text-end fw-semibold">{{ $p->total_wishlist }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="report-empty">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="report-panel h-100">
                <div class="report-panel-header">
                    <h6 class="mb-0">Top khách hàng</h6>
                    <a href="{{ route('admin.reports.customers') }}" class="report-link">
                        Xem tất cả
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table report-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Khách hàng</th>
                                <th class="text-end">Chi tiêu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topCustomers as $c)
                                <tr>
                                    <td>{{ $c->name }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($c->spending) }} đ</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="report-empty">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- CÁC BẢNG 3 --}}
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="report-panel h-100">
                <div class="report-panel-header">
                    <h6 class="mb-0">Sắp hết hàng</h6>
                    <a href="{{ route('admin.reports.lowStock') }}" class="report-link">
                        Xem tất cả
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table report-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Phân loại</th>
                                <th class="text-center">Tồn kho</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStock as $item)
                                <tr>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->attribute_value }}</td>
                                    <td class="text-danger text-center fw-bold">
                                        {{ $item->stock_quantity }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="report-empty">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="report-panel h-100">
                <div class="report-panel-header">
                    <h6 class="mb-0 text-danger">Bom hàng (đơn huỷ)</h6>
                    <a href="{{ route('admin.reports.cancelOrders', ['status' => 4]) }}" class="report-link">
                        Xem tất cả
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table report-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Mã đơn hàng</th>
                                <th>Khách</th>
                                <th class="text-center">Tiền</th>
                                <th class="text-end">Ngày</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cancelList as $cancelOrder)
                                <tr>
                                    <td>DH{{ str_pad($cancelOrder->id ?? 0, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $cancelOrder->customer_name ?? '---' }}</td>
                                    <td class="text-danger text-center fw-semibold">
                                        {{ number_format($cancelOrder->total ?? 0) }} đ
                                    </td>
                                    <td class="text-end text-muted">
                                        {{ !empty($cancelOrder->cancelled_at) ? \Carbon\Carbon::parse($cancelOrder->cancelled_at)->format('d/m/Y') : '---' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="report-empty">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const revenueCtx = document.getElementById('revenueChart');

        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [
                    {
                        label: 'Doanh thu',
                        data: @json($chartRevenue),
                        tension: 0.35,
                        borderWidth: 3,
                        fill: false
                    },
                    {
                        label: 'Lợi nhuận',
                        data: @json($chartProfit),
                        tension: 0.35,
                        borderWidth: 3,
                        fill: false
                    }
                ]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let value = context.raw || 0;
                                return context.dataset.label + ': ' + Number(value).toLocaleString('vi-VN') + ' đ';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: function(value) {
                                return Number(value).toLocaleString('vi-VN') + ' đ';
                            }
                        }
                    }
                }
            }
        });

        function exportPdf() {
            const img = revenueChart.toBase64Image();
            document.getElementById('chart_image').value = img;
            document.getElementById('exportForm').submit();
        }
    </script>
@endpush