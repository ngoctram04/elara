<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
h2 { margin-bottom: 5px; }
table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
th, td { border: 1px solid #ddd; padding: 6px; }
th { background: #f2f2f2; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.section { margin-top: 20px; }
</style>
</head>
<body>

<h2>BÁO CÁO KINH DOANH</h2>
<p>Từ ngày: <strong>{{ $from }}</strong> đến <strong>{{ $to }}</strong></p>

<div class="section">
<h4>Tổng quan tài chính</h4>
<table>
<tr>
    <th>Doanh thu</th>
    <th>Giá vốn</th>
    <th>Lợi nhuận</th>
    <th>Phí ship</th>
    <th>Giảm giá</th>
</tr>
<tr>
    <td class="text-right">{{ number_format($finance->revenue ?? 0) }}</td>
    <td class="text-right">{{ number_format($finance->cost ?? 0) }}</td>
    <td class="text-right">{{ number_format($finance->profit ?? 0) }}</td>
    <td class="text-right">{{ number_format($finance->shipping_total ?? 0) }}</td>
    <td class="text-right">{{ number_format($finance->discount_total ?? 0) }}</td>
</tr>
</table>
</div>

<div class="section">
<h4>Thống kê đơn hàng</h4>
<table>
<tr>
    <th>Đang xử lý</th>
    <th>Đang giao</th>
    <th>Hoàn thành</th>
    <th>Đã huỷ</th>
</tr>
<tr class="text-center">
    <td>{{ $orderStats->pending ?? 0 }}</td>
    <td>{{ $orderStats->shipping ?? 0 }}</td>
    <td>{{ $orderStats->completed ?? 0 }}</td>
    <td>{{ $orderStats->cancelled ?? 0 }}</td>
</tr>
</table>
</div>

<div class="section">
<h4>Top bán chạy</h4>
<table>
<tr>
    <th>Sản phẩm</th>
    <th>SL</th>
    <th>Doanh thu</th>
</tr>
@foreach($topProducts as $p)
<tr>
    <td>{{ $p->name }}</td>
    <td class="text-center">{{ $p->total_sold }}</td>
    <td class="text-right">{{ number_format($p->revenue) }}</td>
</tr>
@endforeach
</table>
</div>

<div class="section">
<h4>Sản phẩm sắp hết</h4>
<table>
<tr>
    <th>Sản phẩm</th>
    <th>Biến thể</th>
    <th>Tồn</th>
</tr>
@foreach($lowStock as $item)
<tr>
    <td>{{ $item->name }}</td>
    <td>{{ $item->attribute_value }}</td>
    <td class="text-center">{{ $item->stock_quantity }}</td>
</tr>
@endforeach
</table>
</div>

</body>
</html>