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

        .text-primary {
            color: #1d4ed8;
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

        .structure-box {
            border: 1px solid #dcdcdc;
            background: #fafafa;
            padding: 12px;
            margin-top: 8px;
        }

        .legend {
            font-size: 11px;
            margin-bottom: 10px;
        }

        .legend span {
            display: inline-block;
            margin-right: 14px;
        }

        .dot {
            display: inline-block;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            margin-right: 4px;
            vertical-align: middle;
        }

        .dot-completed {
            background: #3b9ae1;
        }

        .dot-returned {
            background: #f65d7e;
        }

        .dot-cancelled {
            background: #f59a3d;
        }

        .progress-group {
            margin-top: 8px;
        }

        .progress-item {
            margin-bottom: 12px;
        }

        .progress-head {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }

        .progress-head td {
            border: none;
            padding: 0;
            font-size: 11px;
        }

        .progress-label {
            color: #334155;
        }

        .progress-value {
            text-align: right;
            font-weight: bold;
            color: #111827;
        }

        .progress-value.cancelled {
            color: #c62828;
        }

        .progress-track {
            width: 100%;
            height: 10px;
            background: #e5e7eb;
            border-radius: 999px;
            overflow: hidden;
        }

        .progress-fill {
            height: 10px;
            border-radius: 999px;
        }

        .fill-completed {
            background: #3b9ae1;
        }

        .fill-returned {
            background: #f65d7e;
        }

        .fill-cancelled {
            background: #f59a3d;
        }

        .compact-table th,
        .compact-table td {
            padding: 6px 8px;
        }
    </style>
</head>
<body>

@php
    $revenue            = (float) ($revenue ?? 0);
    $profit             = (float) ($profit ?? 0);
    $totalOrdersValue   = (int) ($totalOrders ?? 0);
    $cancelRateValue    = (float) ($cancelRate ?? 0);

    $refundTotalValue   = (float) ($refundTotal ?? 0);
    $totalDiscountValue = (float) ($totalDiscount ?? 0);

    $openingInventory   = (float) ($openingInventoryValue ?? 0);
    $totalImportData    = (float) ($totalImport ?? 0);
    $totalCostValue     = (float) ($totalCost ?? 0);
    $inventoryLossData  = (float) ($inventoryLoss ?? 0);
    $inventoryValueData = (float) ($inventoryValue ?? 0);

    $shippingCollectedValue = (float) ($shippingCollected ?? 0);
    $shippingCostTotalValue = (float) ($shippingCostTotal ?? 0);
    $freeShippingLossValue  = (float) ($freeShippingLoss ?? 0);

    $cancelListData = collect($cancelList ?? []);
    $refundListData = isset($refunds) ? collect($refunds) : collect();

    $totalCancelAmount = $cancelListData->sum(function ($item) {
        return (float) ($item->total ?? 0);
    });

    $cancelled    = (int) $cancelListData->count();
    $refundCount  = (int) $refundListData->count();
    $margin       = $revenue > 0 ? (($profit / $revenue) * 100) : 0;

    $completedOrders = (int) ($completedOrders ?? 0);
    $returnedOrders  = (int) ($returnedOrders ?? 0);
    $cancelledOrders = (int) ($cancelledOrders ?? 0);

    $totalStructureOrders = $completedOrders + $returnedOrders + $cancelledOrders;

    $completedPercent = $totalStructureOrders > 0 ? ($completedOrders / $totalStructureOrders) * 100 : 0;
    $returnedPercent  = $totalStructureOrders > 0 ? ($returnedOrders / $totalStructureOrders) * 100 : 0;
    $cancelledPercent = $totalStructureOrders > 0 ? ($cancelledOrders / $totalStructureOrders) * 100 : 0;
@endphp

<h2>BÁO CÁO KINH DOANH</h2>
<p class="muted">
    Từ ngày: <strong>{{ $from }}</strong> đến <strong>{{ $to }}</strong>
</p>

<div class="summary-box small">
    Báo cáo này tổng hợp các chỉ số về doanh thu, lợi nhuận, tồn kho, vận chuyển,
    hoàn tiền, sản phẩm và khách hàng trong khoảng thời gian đã chọn.
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
                <th>Doanh thu thuần</th>
                <th>Lợi nhuận thực</th>
                <th>Đơn thành công</th>
                <th>Tỷ lệ huỷ</th>
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
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th>Biên lợi nhuận</th>
                <th>Đơn huỷ</th>
                <th>Đơn hoàn trả</th>
                <th>Tổng tiền đơn huỷ</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">{{ number_format($margin, 1) }}%</td>
                <td class="text-center">{{ number_format($cancelled) }}</td>
                <td class="text-center">{{ number_format($refundCount) }}</td>
                <td class="text-right text-danger">{{ number_format($totalCancelAmount) }} đ</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="section">
    <h4>Điều chỉnh doanh thu</h4>

    <table>
        <thead>
            <tr>
                <th>Tổng hoàn tiền</th>
                <th>Tổng giảm giá</th>
                <th>Doanh thu thuần</th>
                <th>Lợi nhuận thực</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-right text-danger">{{ number_format($refundTotalValue) }} đ</td>
                <td class="text-right">{{ number_format($totalDiscountValue) }} đ</td>
                <td class="text-right text-primary">{{ number_format($revenue) }} đ</td>
                <td class="text-right {{ $profit >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($profit) }} đ
                </td>
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
                <th>Vốn nhập trong kỳ</th>
                <th>Vốn đã bán trong kỳ</th>
                <th>Hao hụt trong kỳ</th>
                <th>Tồn cuối kỳ</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-right">{{ number_format($openingInventory) }} đ</td>
                <td class="text-right">{{ number_format($totalImportData) }} đ</td>
                <td class="text-right">{{ number_format($totalCostValue) }} đ</td>
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
                <th>Tổng chi phí ship</th>
                <th>Khách trả</th>
                <th>Shop bù phí ship</th>
                <th>Trạng thái</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-right">{{ number_format($shippingCostTotalValue) }} đ</td>
                <td class="text-right">{{ number_format($shippingCollectedValue) }} đ</td>
                <td class="text-right {{ $freeShippingLossValue > 0 ? 'text-danger' : 'text-success' }}">
                    {{ number_format($freeShippingLossValue) }} đ
                </td>
                <td class="text-center {{ $freeShippingLossValue > 0 ? 'text-danger' : 'text-success' }}">
                    {{ $freeShippingLossValue > 0 ? 'Có bù phí ship' : 'Không bị lỗ phí ship' }}
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div class="section">
    <h4>Cơ cấu đơn hàng</h4>

    <div class="structure-box">
        @if($totalStructureOrders > 0)
            <div class="legend">
                <span><i class="dot dot-completed"></i>Hoàn tất</span>
                <span><i class="dot dot-returned"></i>Hoàn trả</span>
                <span><i class="dot dot-cancelled"></i>Huỷ</span>
            </div>

            <div class="progress-group">
                <div class="progress-item">
                    <table class="progress-head">
                        <tr>
                            <td class="progress-label">Hoàn tất</td>
                            <td class="progress-value">
                                {{ number_format($completedOrders) }} đơn ({{ number_format($completedPercent, 1) }}%)
                            </td>
                        </tr>
                    </table>
                    <div class="progress-track">
                        <div class="progress-fill fill-completed" style="width: {{ $completedPercent }}%;"></div>
                    </div>
                </div>

                <div class="progress-item">
                    <table class="progress-head">
                        <tr>
                            <td class="progress-label">Hoàn trả</td>
                            <td class="progress-value">
                                {{ number_format($returnedOrders) }} đơn ({{ number_format($returnedPercent, 1) }}%)
                            </td>
                        </tr>
                    </table>
                    <div class="progress-track">
                        <div class="progress-fill fill-returned" style="width: {{ $returnedPercent }}%;"></div>
                    </div>
                </div>

                <div class="progress-item">
                    <table class="progress-head">
                        <tr>
                            <td class="progress-label">Huỷ</td>
                            <td class="progress-value cancelled">
                                {{ number_format($cancelledOrders) }} đơn ({{ number_format($cancelledPercent, 1) }}%)
                            </td>
                        </tr>
                    </table>
                    <div class="progress-track">
                        <div class="progress-fill fill-cancelled" style="width: {{ $cancelledPercent }}%;"></div>
                    </div>
                </div>
            </div>

            <table class="compact-table">
                <thead>
                    <tr>
                        <th>Trạng thái</th>
                        <th class="text-center">Số đơn</th>
                        <th class="text-center">Tỷ lệ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Hoàn tất</td>
                        <td class="text-center">{{ number_format($completedOrders) }}</td>
                        <td class="text-center">{{ number_format($completedPercent, 1) }}%</td>
                    </tr>
                    <tr>
                        <td>Hoàn trả</td>
                        <td class="text-center">{{ number_format($returnedOrders) }}</td>
                        <td class="text-center">{{ number_format($returnedPercent, 1) }}%</td>
                    </tr>
                    <tr>
                        <td>Huỷ</td>
                        <td class="text-center">{{ number_format($cancelledOrders) }}</td>
                        <td class="text-center text-danger">{{ number_format($cancelledPercent, 1) }}%</td>
                    </tr>
                    <tr>
                        <th>Tổng</th>
                        <th class="text-center">{{ number_format($totalStructureOrders) }}</th>
                        <th class="text-center">100%</th>
                    </tr>
                </tbody>
            </table>
        @else
            <div class="no-data">Không có dữ liệu cơ cấu đơn hàng</div>
        @endif
    </div>
</div>

<div class="section">
    <h4>Top bán chạy</h4>

    <table>
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th class="text-center">Đã bán</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topProducts ?? [] as $p)
                <tr>
                    <td>{{ $p->name ?? '---' }}</td>
                    <td class="text-center">{{ number_format($p->total_sold ?? 0) }}</td>
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
    <h4>Sản phẩm tồn lâu</h4>

    <table>
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th class="text-center">Tồn kho</th>
                <th class="text-center">Lần bán cuối</th>
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
                <th class="text-center">Lượt yêu thích</th>
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
                <th class="text-right">Chi tiêu</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topCustomers ?? [] as $c)
                <tr>
                    <td>{{ $c->name ?? '---' }}</td>
                    <td class="text-right">{{ number_format($c->spending ?? 0) }} đ</td>
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
    <h4>Sắp hết hàng</h4>

    <table>
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Phân loại</th>
                <th class="text-center">Tồn kho</th>
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
    <h4>Đơn huỷ</h4>

    <table>
        <thead>
            <tr>
                <th>Mã đơn</th>
                <th>Khách hàng</th>
                <th class="text-center">Tiền</th>
                <th class="text-center">Ngày huỷ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cancelListData as $order)
                <tr>
                    <td class="text-center">
                        DH{{ str_pad($order->id ?? 0, 5, '0', STR_PAD_LEFT) }}
                    </td>
                    <td>{{ $order->customer_name ?? '---' }}</td>
                    <td class="text-center text-danger">
                        {{ number_format($order->total ?? 0) }} đ
                    </td>
                    <td class="text-center">
                        {{
                            !empty($order->cancelled_at)
                                ? \Carbon\Carbon::parse($order->cancelled_at)->format('d/m/Y')
                                : '---'
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

<div class="section">
    <h4>Đơn hoàn trả / hoàn tiền</h4>

    <table>
        <thead>
            <tr>
                <th>Mã hoàn</th>
                <th>Mã đơn</th>
                <th>Khách hàng</th>
                <th class="text-center">Tiền hoàn</th>
                <th class="text-center">Tổn thất</th>
                <th class="text-center">Ngày hoàn</th>
            </tr>
        </thead>
        <tbody>
            @forelse($refundListData as $refund)
                <tr>
                    <td class="text-center">
                        HT{{ str_pad($refund->id ?? 0, 5, '0', STR_PAD_LEFT) }}
                    </td>
                    <td class="text-center">
                        DH{{ str_pad($refund->order_id ?? 0, 5, '0', STR_PAD_LEFT) }}
                    </td>
                    <td>{{ $refund->customer_name ?? '---' }}</td>
                    <td class="text-center text-warning">
                        {{ number_format($refund->refund_total ?? 0) }} đ
                    </td>
                    <td class="text-center text-danger">
                        {{ number_format($refund->loss_amount ?? 0) }} đ
                    </td>
                    <td class="text-center">
                        {{
                            !empty($refund->refunded_at)
                                ? \Carbon\Carbon::parse($refund->refunded_at)->format('d/m/Y')
                                : (!empty($refund->updated_at)
                                    ? \Carbon\Carbon::parse($refund->updated_at)->format('d/m/Y')
                                    : '---')
                        }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="no-data">Không có dữ liệu</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

</body>
</html>