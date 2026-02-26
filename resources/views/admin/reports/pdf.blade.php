<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 12px;
}

h2 {
    margin-bottom: 5px;
}

h4 {
    margin: 15px 0 5px;
}

p {
    margin: 0 0 10px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 15px;
}

th, td {
    border: 1px solid #ddd;
    padding: 6px;
}

th {
    background: #f2f2f2;
}

.text-right { text-align: right; }
.text-center { text-align: center; }

.section {
    margin-top: 15px;
}

.chart {
    margin-top: 10px;
    text-align: center;
}

.chart img {
    width: 100%;
    max-height: 250px;
}
</style>
</head>
<body>

<h2>BÁO CÁO KINH DOANH</h2>
<p>Từ ngày: <strong>{{ $from }}</strong> đến <strong>{{ $to }}</strong></p>


{{-- ================= BIỂU ĐỒ ================= --}}
@if(!empty($chartImage))
<div class="section">
<h4>Biểu đồ doanh thu</h4>
<div class="chart">
    <img src="{{ $chartImage }}">
</div>
</div>
@endif


{{-- ================= TỔNG QUAN ================= --}}
<div class="section">
<h4>Tổng quan kinh doanh</h4>

@php
$revenue = $finance->revenue ?? 0;
$cost = $finance->cost ?? 0;
$profit = $finance->profit ?? 0;
$shipping = $finance->shipping_total ?? 0;
$discount = $finance->discount_total ?? 0;

$ordersCompleted = $orderStats->completed ?? 0;
$totalOrders = $orderStats->total ?? 0;
$cancelled = $orderStats->cancelled ?? 0;

$aov = $ordersCompleted > 0 ? $revenue / $ordersCompleted : 0;
$margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;
@endphp

<table>
<tr>
    <th>Doanh thu</th>
    <th>Giá vốn</th>
    <th>Lợi nhuận</th>
    <th>Biên LN</th>
    <th>AOV</th>
</tr>
<tr>
    <td class="text-right">{{ number_format($revenue) }}</td>
    <td class="text-right">{{ number_format($cost) }}</td>
    <td class="text-right">{{ number_format($profit) }}</td>
    <td class="text-center">{{ number_format($margin,1) }}%</td>
    <td class="text-right">{{ number_format($aov) }}</td>
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
    <td class="text-center">{{ $totalOrders }}</td>
    <td class="text-center">{{ $ordersCompleted }}</td>
    <td class="text-center">{{ $cancelled }}</td>
    <td class="text-center">{{ number_format($cancelRate,1) }}%</td>
    <td class="text-center">{{ round($avgProcessingTime ?? 0) }} giờ</td>
</tr>
</table>

<table>
<tr>
    <th>Phí vận chuyển</th>
    <th>Giảm giá</th>
    <th>Tổng vốn đã bán</th>
    <th>Giá trị tồn kho</th>
    <th>Tổng vốn nhập</th>
</tr>
<tr>
    <td class="text-right">{{ number_format($shipping) }}</td>
    <td class="text-right">{{ number_format($discount) }}</td>
    <td class="text-right">{{ number_format($cost) }}</td>
    <td class="text-right">{{ number_format($inventory->total_value ?? 0) }}</td>
    <td class="text-right">{{ number_format($totalImport ?? 0) }}</td>
</tr>
</table>

</div>


{{-- ================= TOP SẢN PHẨM ================= --}}
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
    <td class="text-center">{{ $p->total_sold }}</td>
    <td class="text-right">{{ number_format($p->revenue) }}</td>
    <td class="text-right">{{ number_format($p->profit) }}</td>
</tr>
@empty
<tr>
<td colspan="4" class="text-center">Không có dữ liệu</td>
</tr>
@endforelse
</table>
</div>


{{-- ================= TỒN LÂU ================= --}}
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
    <td class="text-center">{{ $p->stock_quantity }}</td>
    <td class="text-center">
        {{ $p->last_sold ? \Carbon\Carbon::parse($p->last_sold)->format('d/m/Y') : 'Chưa bán' }}
    </td>
</tr>
@empty
<tr>
<td colspan="3" class="text-center">Không có dữ liệu</td>
</tr>
@endforelse
</table>
</div>


{{-- ================= WISHLIST ================= --}}
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
    <td class="text-center">{{ $p->total_wishlist }}</td>
</tr>
@empty
<tr>
<td colspan="2" class="text-center">Không có dữ liệu</td>
</tr>
@endforelse
</table>
</div>


{{-- ================= KHÁCH HÀNG ================= --}}
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
    <td class="text-center">{{ $c->orders }}</td>
    <td class="text-right">{{ number_format($c->spending) }}</td>
</tr>
@empty
<tr>
<td colspan="3" class="text-center">Không có dữ liệu</td>
</tr>
@endforelse
</table>
</div>


{{-- ================= LOW STOCK ================= --}}
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
    <td>{{ $item->attribute_value }}</td>
    <td class="text-center">{{ $item->stock_quantity }}</td>
</tr>
@empty
<tr>
<td colspan="3" class="text-center">Không có dữ liệu</td>
</tr>
@endforelse
</table>
</div>

</body>
</html>