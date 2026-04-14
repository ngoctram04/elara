@extends('layouts.admin')

@section('title', 'Thống kê')

@push('styles')
    @vite('resources/css/reports.css')

    <style>
        .order-structure-progress {
            padding: 0 1rem 1rem;
            border-top: 1px dashed #e5e7eb;
            margin-top: 0.5rem;
        }

        .order-progress-item + .order-progress-item {
            margin-top: 14px;
        }

        .order-progress-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 6px;
        }

        .order-progress-label {
            font-size: 15px;
            font-weight: 500;
            color: #334155;
        }

        .order-progress-value {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
        }

        .order-progress-value.cancelled {
            color: #ef4444;
        }

        .order-progress-track {
            width: 100%;
            height: 12px;
            background: #e5e7eb;
            border-radius: 999px;
            overflow: hidden;
        }

        .order-progress-fill {
            height: 100%;
            border-radius: 999px;
        }

        .order-progress-fill.completed {
            background: #3b9ae1;
        }

        .order-progress-fill.returned {
            background: #f65d7e;
        }

        .order-progress-fill.cancelled {
            background: #f59a3d;
        }
    </style>
@endpush

@section('content')
@php
    $refundList = $refunds ?? collect();

    $completedOrders = $completedOrders ?? 0;
    $returnedOrders = $returnedOrders ?? 0;
    $cancelledOrders = $cancelledOrders ?? 0;

    $totalStructureOrders = $completedOrders + $returnedOrders + $cancelledOrders;

    $completedPercent = $totalStructureOrders > 0 ? ($completedOrders / $totalStructureOrders) * 100 : 0;
    $returnedPercent = $totalStructureOrders > 0 ? ($returnedOrders / $totalStructureOrders) * 100 : 0;
    $cancelledPercent = $totalStructureOrders > 0 ? ($cancelledOrders / $totalStructureOrders) * 100 : 0;
@endphp

<div class="report-page">
    {{-- HEADER --}}
    <div class="report-header-card">
        <div class="report-header-left">
            <div>
                <h4 class="report-page-title mb-1">Thống kê</h4>
                <p class="report-page-subtitle mb-0">
                    Theo dõi doanh thu, lợi nhuận, tồn kho, vận chuyển và hiệu quả bán hàng trong khoảng thời gian đã chọn
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

    {{-- BỘ LỌC --}}
    <div class="report-filter-card mb-4">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-3 align-items-end">
            <div class="col-lg-3 col-md-4">
                <label class="form-label report-label">Từ ngày</label>
                <input type="date" name="from" value="{{ $from }}" class="form-control report-input">
            </div>

            <div class="col-lg-3 col-md-4">
                <label class="form-label report-label">Đến ngày</label>
                <input type="date" name="to" value="{{ $to }}" class="form-control report-input">
            </div>

            <div class="col-lg-6 col-md-4">
                <div class="d-flex gap-2 report-filter-actions flex-wrap">
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
        <div class="col-lg-3 col-md-6">
            <div class="report-stat-card stat-primary h-100">
                <div class="report-stat-top">
                    <span class="report-stat-label">Doanh thu thuần</span>
                    <span class="report-stat-icon"><i class="bi bi-cash-stack"></i></span>
                </div>
                <div class="report-stat-value">{{ number_format($revenue ?? 0) }} đ</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="report-stat-card stat-info h-100">
                <div class="report-stat-top">
                    <span class="report-stat-label">Tiền thu trước</span>
                    <span class="report-stat-icon"><i class="bi bi-wallet2"></i></span>
                </div>
                <div class="report-stat-value">{{ number_format($paidInAdvance ?? 0) }} đ</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="report-stat-card {{ ($profit ?? 0) >= 0 ? 'stat-success' : 'stat-danger' }} h-100">
                <div class="report-stat-top">
                    <span class="report-stat-label">Lợi nhuận thực</span>
                    <span class="report-stat-icon"><i class="bi bi-graph-up-arrow"></i></span>
                </div>
                <div class="report-stat-value">{{ number_format($profit ?? 0) }} đ</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="report-stat-card stat-warning h-100">
                <div class="report-stat-top">
                    <span class="report-stat-label">Vận chuyển</span>
                    <span class="report-stat-icon"><i class="bi bi-truck"></i></span>
                </div>
                <div class="report-stat-value">{{ number_format($shippingCostTotal ?? 0) }} đ</div>
            </div>
        </div>
    </div>

    {{-- TÓM TẮT GỌN --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="report-highlight-card h-100">
                <div class="report-section-heading">
                    <h6 class="mb-0">Điều chỉnh doanh thu</h6>
                </div>

                <div class="report-ship-info mt-2">
                    <div class="report-ship-row">
                        <span>Tổng hoàn tiền</span>
                        <strong class="text-danger">{{ number_format($refundTotal ?? 0) }} đ</strong>
                    </div>

                    <div class="report-ship-row">
                        <span>Tổng giảm giá</span>
                        <strong>{{ number_format($totalDiscount ?? 0) }} đ</strong>
                    </div>

                    <div class="report-ship-row">
                        <span>Tiền thu trước</span>
                        <strong class="text-info">{{ number_format($paidInAdvance ?? 0) }} đ</strong>
                    </div>

                    <div class="report-ship-row">
                        <span>Doanh thu thuần</span>
                        <strong class="text-primary">{{ number_format($revenue ?? 0) }} đ</strong>
                    </div>

                    <div class="report-ship-row">
                        <span>Lợi nhuận thực</span>
                        <strong class="{{ ($profit ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($profit ?? 0) }} đ
                        </strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="report-highlight-card h-100">
                <div class="report-section-heading">
                    <h6 class="mb-0">Tồn kho và giá vốn</h6>
                </div>

                <div class="report-ship-info mt-2">
                    <div class="report-ship-row">
                        <span>Tồn đầu kỳ</span>
                        <strong>{{ number_format($openingInventoryValue ?? 0) }} đ</strong>
                    </div>

                    <div class="report-ship-row">
                        <span>Vốn nhập trong kỳ</span>
                        <strong>{{ number_format($totalImport ?? 0) }} đ</strong>
                    </div>

                    <div class="report-ship-row">
                        <span>Vốn đã bán trong kỳ</span>
                        <strong>{{ number_format($totalCost ?? 0) }} đ</strong>
                    </div>

                    <div class="report-ship-row">
                        <span>Hao hụt trong kỳ</span>
                        <strong class="text-danger">{{ number_format($inventoryLoss ?? 0) }} đ</strong>
                    </div>

                    <div class="report-ship-row">
                        <span>Tồn cuối kỳ</span>
                        <strong>{{ number_format($inventoryValue ?? 0) }} đ</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- BIỂU ĐỒ --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="report-panel h-100">
                <div class="report-panel-header">
                    <div>
                        <h6 class="mb-1">Doanh thu và lợi nhuận theo ngày</h6>
                        <p class="mb-0 text-muted small">
                            So sánh biến động doanh thu thuần và lợi nhuận thực tế theo từng ngày
                        </p>
                    </div>
                </div>

                <div class="report-chart-wrap" style="height: 320px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="report-panel h-100">
                <div class="report-panel-header">
                    <div>
                        <h6 class="mb-1">Cơ cấu đơn hàng</h6>
                        <p class="mb-0 text-muted small">
                            Tỷ trọng hoàn tất, hoàn trả và huỷ trong kỳ
                        </p>
                    </div>
                </div>

                <div class="report-chart-wrap d-flex align-items-center justify-content-center" style="height: 320px;">
                    <canvas id="orderStructureChart"></canvas>
                </div>

                <div class="order-structure-progress">
                    <div class="order-progress-item">
                        <div class="order-progress-head">
                            <span class="order-progress-label">Hoàn tất</span>
                            <span class="order-progress-value">
                                {{ number_format($completedOrders) }} đơn ({{ number_format($completedPercent, 1) }}%)
                            </span>
                        </div>
                        <div class="order-progress-track">
                            <div class="order-progress-fill completed" style="width: {{ $completedPercent }}%;"></div>
                        </div>
                    </div>

                    <div class="order-progress-item">
                        <div class="order-progress-head">
                            <span class="order-progress-label">Hoàn trả</span>
                            <span class="order-progress-value">
                                {{ number_format($returnedOrders) }} đơn ({{ number_format($returnedPercent, 1) }}%)
                            </span>
                        </div>
                        <div class="order-progress-track">
                            <div class="order-progress-fill returned" style="width: {{ $returnedPercent }}%;"></div>
                        </div>
                    </div>

                    <div class="order-progress-item">
                        <div class="order-progress-head">
                            <span class="order-progress-label">Huỷ</span>
                            <span class="order-progress-value cancelled">
                                {{ number_format($cancelledOrders) }} đơn ({{ number_format($cancelledPercent, 1) }}%)
                            </span>
                        </div>
                        <div class="order-progress-track">
                            <div class="order-progress-fill cancelled" style="width: {{ $cancelledPercent }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- BẢNG THỐNG KÊ 1 --}}
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
                                    <td class="text-end fw-semibold">{{ number_format($p->total_sold) }}</td>
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
                                    <td class="text-center fw-semibold">{{ number_format($p->stock_quantity) }}</td>
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

    {{-- BẢNG THỐNG KÊ 2 --}}
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
                                    <td class="text-end fw-semibold">{{ number_format($p->total_wishlist) }}</td>
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
                    <a href="{{ route('admin.reports.customers', ['from' => $from, 'to' => $to]) }}" class="report-link">
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

    {{-- BẢNG THỐNG KÊ 3 --}}
    <div class="row g-3 mb-4">
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
                                        {{ number_format($item->stock_quantity) }}
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
                    <h6 class="mb-0 text-danger">Đơn huỷ</h6>
                    <a href="{{ route('admin.reports.cancelOrders', ['from' => $from, 'to' => $to]) }}" class="report-link">
                        Xem tất cả
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table report-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
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

    {{-- BẢNG ĐƠN HOÀN TRẢ --}}
    <div class="row g-3">
        <div class="col-12">
            <div class="report-panel">
                <div class="report-panel-header">
                    <h6 class="mb-0 text-warning">Đơn hoàn trả / hoàn tiền</h6>
                    <a href="{{ route('admin.reports.refundOrders', ['from' => $from, 'to' => $to]) }}" class="report-link">
                        Xem tất cả
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table report-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Mã hoàn</th>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th class="text-center">Tiền hoàn</th>
                                <th class="text-center">Tổn thất</th>
                                <th class="text-end">Ngày hoàn</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($refundList as $refund)
                                <tr>
                                    <td>HT{{ str_pad($refund->id ?? 0, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td>DH{{ str_pad($refund->order_id ?? 0, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $refund->customer_name ?? '---' }}</td>
                                    <td class="text-center fw-semibold text-warning">
                                        {{ number_format($refund->refund_total ?? 0) }} đ
                                    </td>
                                    <td class="text-center text-danger fw-semibold">
                                        {{ number_format($refund->loss_amount ?? 0) }} đ
                                    </td>
                                    <td class="text-end text-muted">
                                        {{ !empty($refund->refunded_at) ? \Carbon\Carbon::parse($refund->refunded_at)->format('d/m/Y') : '---' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="report-empty">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(!isset($refunds))
                    <div class="px-3 pb-3 text-muted small">
                        Muốn hiện danh sách này ở dashboard thì nhớ truyền thêm biến <strong>$refunds</strong> từ controller.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const revenueCtx = document.getElementById('revenueChart');
        const orderStructureCtx = document.getElementById('orderStructureChart');

        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: @json($chartLabels ?? []),
                datasets: [
                    {
                        label: 'Doanh thu',
                        data: @json($chartRevenue ?? []),
                        tension: 0.35,
                        borderWidth: 3,
                        fill: false
                    },
                    {
                        label: 'Lợi nhuận',
                        data: @json($chartProfit ?? []),
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

        const orderStructureChart = new Chart(orderStructureCtx, {
            type: 'doughnut',
            data: {
                labels: ['Hoàn tất', 'Hoàn trả', 'Huỷ'],
                datasets: [{
                    data: [
                        {{ (int) $completedOrders }},
                        {{ (int) $returnedOrders }},
                        {{ (int) $cancelledOrders }}
                    ],
                    backgroundColor: ['#3b9ae1', '#f65d7e', '#f59a3d'],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10,
                            padding: 16
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let value = context.raw || 0;
                                return context.label + ': ' + Number(value).toLocaleString('vi-VN') + ' đơn';
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