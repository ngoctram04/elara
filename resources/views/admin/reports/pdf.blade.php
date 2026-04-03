<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Báo cáo kinh doanh</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
            line-height: 1.45;
        }

        h2 {
            margin: 0 0 6px;
            font-size: 20px;
        }

        h4 {
            margin: 18px 0 8px;
            font-size: 14px;
            color: #111827;
        }

        p {
            margin: 0 0 10px;
        }

        .muted {
            color: #666;
        }

        .section {
            margin-top: 14px;
        }

        .summary-box {
            padding: 8px 10px;
            border: 1px solid #ddd;
            background: #fafafa;
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        th, td {
            border: 1px solid #dcdcdc;
            padding: 7px 8px;
            vertical-align: middle;
        }

        th {
            background: #f3f4f6;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-danger {
            color: #c62828;
        }

        .text-success {
            color: #2e7d32;
        }

        .text-warning {
            color: #b26a00;
        }

        .chart {
            margin-top: 10px;
            text-align: center;
        }

        .chart img {
            width: 100%;
            max-height: 260px;
        }

        .small {
            font-size: 11px;
        }

        .no-data {
            text-align: center;
            color: #777;
            padding: 10px 0;
        }
    </style>
</head>
<body>

@php
    // =========================
    // KPI CHÍNH
    // =========================
    $revenue             = (float) ($revenue ?? 0);
    $profit              = (float) ($profit ?? 0);
    $totalOrdersValue    = (int) ($totalOrders ?? 0);
    $cancelRateValue     = (float) ($cancelRate ?? 0);

    // =========================
    // KHO / VỐN / HAO HỤT
    // =========================
    $openingInventory    = (float) ($openingInventoryValue ?? 0);
    $totalImportData     = (float) ($totalImport ?? 0);
    $totalCost           = (float) ($totalCost ?? 0);
    $inventoryLossData   = (float) ($inventoryLoss ?? 0);
    $inventoryValueData  = (float) ($inventoryValue ?? 0);

    // =========================
    // SHIP
    // =========================
    $shippingCollected   = (float) ($shippingCollected ?? 0);
    $shippingCostTotal   = (float) ($shippingCostTotal ?? 0);
    $freeShippingLoss    = (float) ($freeShippingLoss ?? max(0, $shippingCostTotal - $shippingCollected));

    // =========================
    // THỐNG KÊ ĐƠN
    // =========================
    $ordersCompleted     = (int) (($orderStats->completed ?? null) ?? $totalOrdersValue);
    $cancelled           = (int) ($orderStats->cancelled ?? count($cancelList ?? []));

    // =========================
    // CHỈ SỐ PHỤ
    // =========================
    $aov                 = $ordersCompleted > 0 ? ($revenue / $ordersCompleted) : 0;
    $margin              = $revenue > 0 ? (($profit / $revenue) * 100) : 0;

    $totalCancelAmount   = collect($cancelList ?? [])->sum(function ($item) {
        return (float) ($item->total ?? 0);
    });
@endphp

<h2>BÁO CÁO KINH DOANH</h2>
<p class="muted">
    Từ ngày: <strong>{{ $from }}</strong> đến <strong>{{ $to }}</strong>
</p>

<div class="summary-box small">
    Báo cáo này tổng hợp các chỉ số chính về doanh thu, lợi nhuận, đơn hàng, tồn kho,
    vận chuyển, sản phẩm và khách hàng trong khoảng thời gian đã chọn.
</div>

@if(!empty($chartImage))
    <div class="section">
        <h4>Biểu đồ doanh thu và lợi nhuận theo ngày</h4>
        <div class="chart">
            <img src="{{ $chartImage }}" alt="Biểu đồ báo cáo">
        </div>
    </div>
@endif

<div class="section">
    <h4>Tổng quan kinh doanh</h4>

    <table>
        <thead>
            <tr>
                <th>Doanh thu</th>
                <th>Lợi nhuận</th>
                <th>Đơn thành công</th>
                <th>Tỷ lệ huỷ</th>
                <th>AOV</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-right">{{ number_format($revenue) }} đ</td>
                <td class="text-right {{ $profit >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($profit) }} đ
                </td>
                <td class="text-center">{{ number_format($totalOrdersValue) }}</td>
                <td class="text-center text-danger">{{ number_format($cancelRateValue, 1) }}%</td>
                <td class="text-right">{{ number_format($aov) }} đ</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th>Biên lợi nhuận</th>
                <th>Đơn hoàn thành</th>
                <th>Đơn huỷ</th>
                <th>Tổng tiền đơn huỷ</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">{{ number_format($margin, 1) }}%</td>
                <td class="text-center">{{ number_format($ordersCompleted) }}</td>
                <td class="text-center">{{ number_format($cancelled) }}</td>
                <td class="text-right text-danger">{{ number_format($totalCancelAmount) }} đ</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="section">
    <h4>Tồn kho và giá vốn</h4>

    <table>
        <thead>
            <tr>
                <th>Tồn đầu kỳ</th>
                <th>Tổng vốn nhập trong kỳ</th>
                <th>Tổng vốn đã bán trong kỳ</th>
                <th>Hao hụt trong kỳ</th>
                <th>Tồn cuối kỳ</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-right">{{ number_format($openingInventory) }} đ</td>
                <td class="text-right">{{ number_format($totalImportData) }} đ</td>
                <td class="text-right">{{ number_format($totalCost) }} đ</td>
                <td class="text-right text-danger">{{ number_format($inventoryLossData) }} đ</td>
                <td class="text-right">{{ number_format($inventoryValueData) }} đ</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="section">
    <h4>Vận chuyển</h4>

    <table>
        <thead>
            <tr>
                <th>Tổng chi phí vận chuyển</th>
                <th>Khách trả</th>
                <th>Shop bù phí ship</th>
                <th>Trạng thái</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-right">{{ number_format($shippingCostTotal) }} đ</td>
                <td class="text-right">{{ number_format($shippingCollected) }} đ</td>
                <td class="text-right {{ $freeShippingLoss > 0 ? 'text-danger' : 'text-success' }}">
                    {{ number_format($freeShippingLoss) }} đ
                </td>
                <td class="text-center {{ $freeShippingLoss > 0 ? 'text-danger' : 'text-success' }}">
                    {{ $freeShippingLoss > 0 ? 'Có bù phí ship' : 'Không bị lỗ ship' }}
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div class="section">
    <h4>Top sản phẩm bán chạy</h4>

    <table>
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Đã bán</th>
                <th>Doanh thu</th>
                <th>Lợi nhuận</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topProducts ?? [] as $p)
                <tr>
                    <td>{{ $p->name ?? '---' }}</td>
                    <td class="text-center">{{ number_format($p->total_sold ?? 0) }}</td>
                    <td class="text-right">{{ number_format($p->revenue ?? 0) }} đ</td>
                    <td class="text-right">{{ number_format($p->profit ?? 0) }} đ</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="no-data">Không có dữ liệu</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="section">
    <h4>Sản phẩm tồn lâu</h4>

    <table>
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Tồn kho</th>
                <th>Lần bán cuối</th>
            </tr>
        </thead>
        <tbody>
            @forelse($slowMoving ?? [] as $p)
                <tr>
                    <td>{{ $p->name ?? '---' }}</td>
                    <td class="text-center">{{ number_format($p->stock_quantity ?? 0) }}</td>
                    <td class="text-center">
                        {{ !empty($p->last_sold) ? \Carbon\Carbon::parse($p->last_sold)->format('d/m/Y') : 'Chưa bán' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="no-data">Không có dữ liệu</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="section">
    <h4>Sản phẩm được quan tâm</h4>

    <table>
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Lượt yêu thích</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mostViewed ?? [] as $p)
                <tr>
                    <td>{{ $p->name ?? '---' }}</td>
                    <td class="text-center">{{ number_format($p->total_wishlist ?? 0) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="no-data">Không có dữ liệu</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="section">
    <h4>Top khách hàng</h4>

    <table>
        <thead>
            <tr>
                <th>Khách hàng</th>
                <th>Số đơn</th>
                <th>Tổng chi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topCustomers ?? [] as $c)
                <tr>
                    <td>{{ $c->name ?? '---' }}</td>
                    <td class="text-center">{{ number_format($c->orders ?? 0) }}</td>
                    <td class="text-right">{{ number_format($c->spending ?? 0) }} đ</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="no-data">Không có dữ liệu</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="section">
    <h4>Sản phẩm sắp hết hàng</h4>

    <table>
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Phân loại</th>
                <th>Tồn kho</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lowStock ?? [] as $item)
                <tr>
                    <td>{{ $item->name ?? '---' }}</td>
                    <td>{{ $item->attribute_value ?? '---' }}</td>
                    <td class="text-center text-danger">{{ number_format($item->stock_quantity ?? 0) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="no-data">Không có dữ liệu</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="section">
    <h4>Bom hàng (đơn huỷ)</h4>

    <table>
        <thead>
            <tr>
                <th>Số đơn huỷ</th>
                <th>Tổng tiền bị bom</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">{{ count($cancelList ?? []) }}</td>
                <td class="text-right text-danger">{{ number_format($totalCancelAmount) }} đ</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th>Mã đơn hàng</th>
                <th>Khách</th>
                <th>Giá trị đơn</th>
                <th>Ngày huỷ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cancelList ?? [] as $order)
                <tr>
                    <td class="text-center">
                        DH{{ str_pad($order->id ?? 0, 5, '0', STR_PAD_LEFT) }}
                    </td>
                    <td>{{ $order->customer_name ?? '---' }}</td>
                    <td class="text-right text-danger">{{ number_format($order->total ?? 0) }} đ</td>
                    <td class="text-center">
                        {{
                            !empty($order->cancelled_at)
                                ? \Carbon\Carbon::parse($order->cancelled_at)->format('d/m/Y')
                                : (!empty($order->created_at)
                                    ? \Carbon\Carbon::parse($order->created_at)->format('d/m/Y')
                                    : '---')
                        }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="no-data">Không có dữ liệu</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

</body>
</html>