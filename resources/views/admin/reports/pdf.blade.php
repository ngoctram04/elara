<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Báo cáo kinh doanh</title>

    <style>
        body{
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
            line-height: 1.45;
        }

        h2{
            margin: 0 0 6px;
            font-size: 20px;
        }

        h4{
            margin: 18px 0 8px;
            font-size: 14px;
            color: #111827;
        }

        p{
            margin: 0 0 10px;
        }

        .muted{
            color: #666;
        }

        .section{
            margin-top: 14px;
        }

        .summary-box{
            padding: 8px 10px;
            border: 1px solid #ddd;
            background: #fafafa;
            margin-bottom: 12px;
        }

        table{
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        th, td{
            border: 1px solid #dcdcdc;
            padding: 7px 8px;
            vertical-align: middle;
        }

        th{
            background: #f3f4f6;
            font-weight: bold;
        }

        .text-right{
            text-align: right;
        }

        .text-center{
            text-align: center;
        }

        .text-danger{
            color: #c62828;
        }

        .text-success{
            color: #2e7d32;
        }

        .text-warning{
            color: #b26a00;
        }

        .chart{
            margin-top: 10px;
            text-align: center;
        }

        .chart img{
            width: 100%;
            max-height: 260px;
            object-fit: contain;
        }

        .small{
            font-size: 11px;
        }

        .no-data{
            text-align: center;
            color: #777;
            padding: 10px 0;
        }
    </style>
</head>
<body>

@php
    $revenue            = $revenue ?? ($finance->revenue ?? 0);
    $totalCost          = $totalCost ?? ($finance->cost ?? 0);
    $profit             = $profit ?? 0;
    $shippingCollected  = $shippingCollected ?? ($finance->shipping_total ?? 0);
    $shippingPaidTotal  = $shippingPaidTotal ?? ($finance->shipping_cost_total ?? 0);
    $shippingDebt       = $shippingDebt ?? 0;
    $discount           = $totalDiscount ?? ($finance->discount_total ?? 0);

    $ordersCompleted    = $orderStats->completed ?? 0;
    $totalOrdersValue   = $totalOrders ?? ($orderStats->total ?? 0);
    $cancelled          = $orderStats->cancelled ?? 0;

    $inventoryValueData = $inventoryValue ?? 0;
    $totalImportData    = $totalImport ?? 0;
    $inventoryLossData  = $inventoryLoss ?? 0;

    $cancelRateValue    = $cancelRate ?? 0;
    $avgProcessing      = $avgProcessingTime ?? 0;

    $aov                = $ordersCompleted > 0 ? ($revenue / $ordersCompleted) : 0;
    $margin             = $revenue > 0 ? (($profit / $revenue) * 100) : 0;

    $totalCancelAmount  = collect($cancelList ?? [])->sum('total');
@endphp

<h2>BÁO CÁO KINH DOANH</h2>
<p class="muted">
    Từ ngày: <strong>{{ $from }}</strong> đến <strong>{{ $to }}</strong>
</p>

<div class="summary-box small">
    Báo cáo này tổng hợp các chỉ số quan trọng về doanh thu, lợi nhuận, đơn hàng, vận chuyển,
    tồn kho, sản phẩm, khách hàng và tình trạng huỷ đơn trong khoảng thời gian đã chọn.
</div>

{{-- BIỂU ĐỒ --}}
@if(!empty($chartImage))
    <div class="section">
        <h4>Biểu đồ doanh thu và lợi nhuận</h4>
        <div class="chart">
            <img src="{{ $chartImage }}" alt="Biểu đồ báo cáo">
        </div>
    </div>
@endif

{{-- TỔNG QUAN KINH DOANH --}}
<div class="section">
    <h4>Tổng quan kinh doanh</h4>

    <table>
        <tr>
            <th>Doanh thu</th>
            <th>Giá vốn</th>
            <th>Lợi nhuận</th>
            <th>Biên lợi nhuận</th>
            <th>AOV</th>
        </tr>
        <tr>
            <td class="text-right">{{ number_format($revenue) }} đ</td>
            <td class="text-right">{{ number_format($totalCost) }} đ</td>
            <td class="text-right {{ $profit >= 0 ? 'text-success' : 'text-danger' }}">
                {{ number_format($profit) }} đ
            </td>
            <td class="text-center">{{ number_format($margin, 1) }}%</td>
            <td class="text-right">{{ number_format($aov) }} đ</td>
        </tr>
    </table>

    <table>
        <tr>
            <th>Tổng đơn</th>
            <th>Đơn hoàn thành</th>
            <th>Đơn huỷ</th>
            <th>Tỷ lệ huỷ</th>
            <th>Thời gian xử lý TB</th>
        </tr>
        <tr>
            <td class="text-center">{{ number_format($totalOrdersValue) }}</td>
            <td class="text-center">{{ number_format($ordersCompleted) }}</td>
            <td class="text-center">{{ number_format($cancelled) }}</td>
            <td class="text-center text-danger">{{ number_format($cancelRateValue, 1) }}%</td>
            <td class="text-center">{{ round($avgProcessing) }} giờ</td>
        </tr>
    </table>
</div>

{{-- TÀI CHÍNH - VẬN CHUYỂN - TỒN KHO --}}
<div class="section">
    <h4>Tài chính, vận chuyển và tồn kho</h4>

    <table>
        <tr>
            <th>Phí vận chuyển thu</th>
            <th>Đã trả vận chuyển</th>
            <th>Nợ vận chuyển</th>
            <th>Giảm giá</th>
            <th>Hao hụt</th>
        </tr>
        <tr>
            <td class="text-right">{{ number_format($shippingCollected) }} đ</td>
            <td class="text-right">{{ number_format($shippingPaidTotal) }} đ</td>
            <td class="text-right text-warning">{{ number_format($shippingDebt) }} đ</td>
            <td class="text-right">{{ number_format($discount) }} đ</td>
            <td class="text-right text-danger">{{ number_format($inventoryLossData) }} đ</td>
        </tr>
    </table>

    <table>
        <tr>
            <th>Tổng vốn đã bán</th>
            <th>Giá trị tồn kho</th>
            <th>Tổng vốn nhập</th>
        </tr>
        <tr>
            <td class="text-right">{{ number_format($totalCost) }} đ</td>
            <td class="text-right">{{ number_format($inventoryValueData) }} đ</td>
            <td class="text-right">{{ number_format($totalImportData) }} đ</td>
        </tr>
    </table>
</div>

{{-- TOP SẢN PHẨM --}}
<div class="section">
    <h4>Top sản phẩm bán chạy</h4>

    <table>
        <tr>
            <th>Sản phẩm</th>
            <th>SL bán</th>
            <th>Doanh thu</th>
            <th>Lợi nhuận</th>
        </tr>

        @forelse($topProducts as $p)
            <tr>
                <td>{{ $p->name }}</td>
                <td class="text-center">{{ number_format($p->total_sold ?? 0) }}</td>
                <td class="text-right">{{ number_format($p->revenue ?? 0) }} đ</td>
                <td class="text-right">{{ number_format($p->profit ?? 0) }} đ</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="no-data">Không có dữ liệu</td>
            </tr>
        @endforelse
    </table>
</div>

{{-- TỒN LÂU --}}
<div class="section">
    <h4>Sản phẩm tồn lâu</h4>

    <table>
        <tr>
            <th>Sản phẩm</th>
            <th>Tồn kho</th>
            <th>Lần bán cuối</th>
        </tr>

        @forelse($slowMoving as $p)
            <tr>
                <td>{{ $p->name }}</td>
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
    </table>
</div>

{{-- SẢN PHẨM ĐƯỢC QUAN TÂM --}}
<div class="section">
    <h4>Sản phẩm được quan tâm</h4>

    <table>
        <tr>
            <th>Sản phẩm</th>
            <th>Lượt yêu thích</th>
        </tr>

        @forelse($mostViewed as $p)
            <tr>
                <td>{{ $p->name }}</td>
                <td class="text-center">{{ number_format($p->total_wishlist ?? 0) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="2" class="no-data">Không có dữ liệu</td>
            </tr>
        @endforelse
    </table>
</div>

{{-- TOP KHÁCH HÀNG --}}
<div class="section">
    <h4>Top khách hàng</h4>

    <table>
        <tr>
            <th>Khách hàng</th>
            <th>Số đơn</th>
            <th>Tổng chi</th>
        </tr>

        @forelse($topCustomers as $c)
            <tr>
                <td>{{ $c->name }}</td>
                <td class="text-center">{{ number_format($c->orders ?? 0) }}</td>
                <td class="text-right">{{ number_format($c->spending ?? 0) }} đ</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="no-data">Không có dữ liệu</td>
            </tr>
        @endforelse
    </table>
</div>

{{-- LOW STOCK --}}
<div class="section">
    <h4>Sản phẩm sắp hết hàng</h4>

    <table>
        <tr>
            <th>Sản phẩm</th>
            <th>Biến thể</th>
            <th>Tồn</th>
        </tr>

        @forelse($lowStock as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>{{ $item->attribute_value ?? '---' }}</td>
                <td class="text-center text-danger">{{ number_format($item->stock_quantity ?? 0) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="no-data">Không có dữ liệu</td>
            </tr>
        @endforelse
    </table>
</div>

{{-- BOM HÀNG --}}
<div class="section">
    <h4>Bom hàng (đơn huỷ)</h4>

    <table>
        <tr>
            <th>Số đơn huỷ</th>
            <th>Tổng tiền bị bom</th>
        </tr>
        <tr>
            <td class="text-center">{{ count($cancelList ?? []) }}</td>
            <td class="text-right text-danger">{{ number_format($totalCancelAmount) }} đ</td>
        </tr>
    </table>

    <table>
        <tr>
            <th>Mã đơn hàng</th>
            <th>Khách</th>
            <th>Giá trị đơn</th>
            <th>Ngày</th>
        </tr>

        @forelse($cancelList as $order)
            <tr>
                <td class="text-center">
                    DH{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                </td>
                <td>{{ $order->customer_name ?? '---' }}</td>
                <td class="text-right text-danger">{{ number_format($order->total ?? 0) }} đ</td>
                <td class="text-center">
                    {{ !empty($order->created_at) ? \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') : '---' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="no-data">Không có dữ liệu</td>
            </tr>
        @endforelse
    </table>
</div>

</body>
</html>