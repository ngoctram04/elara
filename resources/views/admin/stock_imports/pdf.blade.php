<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>
body{
    font-family: DejaVu Sans;
    font-size:13px;
}

.title{
    text-align:center;
    font-size:22px;
    font-weight:bold;
    margin-bottom:20px;
}

.info{
    margin-bottom:20px;
    line-height:1.6;
}

.info p{
    margin:2px 0;
}

table{
    width:100%;
    border-collapse: collapse;
}

table th, table td{
    border:1px solid #000;
    padding:6px;
    vertical-align:middle;
}

th{
    background:#f2f2f2;
    text-align:center;
}

.text-center{
    text-align:center;
}

.text-right{
    text-align:right;
}

.total{
    text-align:right;
    margin-top:15px;
    font-weight:bold;
    font-size:15px;
}

.footer{
    margin-top:40px;
    text-align:right;
    font-size:13px;
}
</style>

</head>

<body>

<div class="title">
    PHIẾU NHẬP KHO
</div>

@php
    $first = $items->first();
@endphp

<div class="info">

<p>
<b>Mã phiếu:</b>
{{ $code }}
</p>

<p>
<b>Nhà cung cấp:</b>
{{ $first->supplier ?? '-' }}
</p>

<p>
<b>Số điện thoại:</b>
{{ $first->supplier_phone ?? '-' }}
</p>

<p>
<b>Địa chỉ:</b>
{{ $first->supplier_address ?? '-' }}
</p>

<p>
<b>Ngày nhập:</b>
{{ optional($first->created_at)->format('d/m/Y H:i') ?? '-' }}
</p>

<p>
<b>Người nhập:</b>
{{ $first->user->name ?? 'Admin' }}
</p>

<p>
<b>Ghi chú:</b>
{{ $first->note ?? '-' }}
</p>

</div>

<table>

<thead>
<tr>
<th width="50">STT</th>
<th>Sản phẩm</th>
<th>Biến thể</th>
<th width="90">Số lượng</th>
<th width="120">Giá nhập</th>
<th width="140">Thành tiền</th>
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

<td class="text-center">
{{ $index + 1 }}
</td>

<td>
{{ $item->variant->product->name ?? '-' }}
</td>

<td>
{{ $item->variant->attribute_value ?? '-' }}
</td>

<td class="text-center">
{{ number_format($item->quantity) }}
</td>

<td class="text-right">
{{ number_format($item->cost_price) }} đ
</td>

<td class="text-right">
{{ number_format($sub) }} đ
</td>

</tr>

@endforeach

</tbody>

</table>

<div class="total">

<p>
Tổng số lượng: {{ number_format($totalQty) }}
</p>

<p>
Tổng tiền: {{ number_format($total) }} đ
</p>

</div>

<div class="footer">
Ngày in: {{ now()->format('d/m/Y H:i') }}
</div>

</body>
</html>