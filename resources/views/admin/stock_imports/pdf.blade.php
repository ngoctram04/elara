<!DOCTYPE html>

<html>
<head>
<meta charset="utf-8">

<style>

body{
    font-family: DejaVu Sans;
    font-size:14px;
}

.title{
    text-align:center;
    font-size:22px;
    font-weight:bold;
    margin-bottom:20px;
}

.info{
    margin-bottom:20px;
}

table{
    width:100%;
    border-collapse: collapse;
}

table th, table td{
    border:1px solid #000;
    padding:8px;
}

th{
    background:#f2f2f2;
}

.total{
    text-align:right;
    margin-top:15px;
    font-weight:bold;
    font-size:16px;
}

</style>

</head>

<body>

<div class="title">
PHIẾU NHẬP KHO
</div>

<div class="info">

<p>
<b>Mã phiếu:</b>
{{ $code }}
</p>

<p>
<b>Nhà cung cấp:</b>
{{ $items->first()->supplier ?? '-' }}
</p>

<p>
<b>Ngày nhập:</b>
{{ $items->first()->created_at->format('d/m/Y') }}
</p>

<p>
<b>Người nhập:</b>
{{ auth()->user()->name ?? 'Admin' }}
</p>

<p>
<b>Ghi chú:</b>
{{ $items->first()->note ?? '-' }}
</p>

</div>

<table>

<thead>

<tr>

<th width="50">STT</th>
<th>Sản phẩm</th>
<th>Biến thể</th>
<th width="80">SL</th>
<th width="120">Giá nhập</th>
<th width="120">Thành tiền</th>

</tr>

</thead>

<tbody>

@php
$total = 0;
$totalQty = 0;
@endphp

@foreach($items as $index => $item)

@php
$sub = $item->quantity * $item->cost_price;
$total += $sub;
$totalQty += $item->quantity;
@endphp

<tr>

<td>{{ $index + 1 }}</td>

<td>
{{ $item->variant->product->name ?? '-' }}
</td>

<td>
{{ $item->variant->attribute_value ?? '-' }}
</td>

<td>
{{ $item->quantity }}
</td>

<td>
{{ number_format($item->cost_price) }} đ
</td>

<td>
{{ number_format($sub) }} đ
</td>

</tr>

@endforeach

</tbody>

</table>

<div class="total">

<p>
Tổng số lượng: {{ $totalQty }}
</p>

<p>
Tổng tiền: {{ number_format($total) }} đ
</p>

</div>

</body>
</html>
