<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Đơn hàng đã được giao</title>
</head>

<body style="margin:0; padding:0; background:#f4f6f8; font-family:Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="padding:30px 0; background:#f4f6f8;">
<tr>
<td align="center">

<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden;">

{{-- HEADER --}}
<tr>
<td style="background:#111827; padding:25px 30px; text-align:center;">
<h2 style="color:#ffffff; margin:0; font-weight:600; letter-spacing:1px;">
ELARA
</h2>

<p style="color:#d1d5db; margin:8px 0 0 0; font-size:14px;">
Đơn hàng của bạn đã được giao thành công
</p>
</td>
</tr>


{{-- GREETING --}}
<tr>
<td style="padding:30px;">
<p style="margin:0 0 10px 0; font-size:16px;">
Xin chào <strong>{{ $user->name }}</strong>,
</p>

<p style="margin:0; color:#555; line-height:1.6;">
Cảm ơn bạn đã mua sắm tại <strong>ELARA</strong>.  
Đơn hàng của bạn đã được giao thành công đến địa chỉ nhận hàng.

Để hoàn tất đơn hàng, vui lòng truy cập vào <strong>lịch sử đơn hàng</strong> và bấm xác nhận <strong>"Đã nhận hàng"</strong>.
</p>
</td>
</tr>


{{-- ORDER INFO --}}
<tr>
<td style="padding:0 30px 20px 30px;">

<table width="100%" cellpadding="8" cellspacing="0" style="background:#f9fafb; border-radius:6px;">
<tr>

<td style="font-size:14px;">
<strong>Mã đơn hàng:</strong> #{{ $order->id }}
</td>

<td align="right" style="font-size:14px;">
<strong>Ngày giao:</strong>
{{ $order->delivered_at
? \Carbon\Carbon::parse($order->delivered_at)->format('d/m/Y H:i')
: now()->format('d/m/Y H:i') }}
</td>

</tr>
</table>

</td>
</tr>


{{-- PRODUCT TABLE --}}
<tr>
<td style="padding:0 30px 30px 30px;">

<table width="100%" cellpadding="10" cellspacing="0" style="border-collapse:collapse; font-size:14px;">

<thead>
<tr style="background:#f3f4f6; text-align:left;">
<th style="border-bottom:1px solid #e5e7eb;">Sản phẩm</th>
<th align="center" style="border-bottom:1px solid #e5e7eb;">SL</th>
<th align="right" style="border-bottom:1px solid #e5e7eb;">Giá</th>
</tr>
</thead>

<tbody>

@forelse($order->items ?? [] as $item)

@php
$variant = $item->variant;
$product = $variant?->product;
$image   = $product?->images?->first();

$imagePath = $image
? public_path('storage/' . $image->image_path)
: null;
@endphp

<tr>

<td style="border-bottom:1px solid #f1f1f1; padding-top:15px; padding-bottom:15px;">

<table cellpadding="0" cellspacing="0">
<tr>

<td width="70" valign="top">

@if($imagePath && file_exists($imagePath))
<img
src="{{ $message->embed($imagePath) }}"
width="60"
height="60"
style="display:block; border-radius:4px;"
>
@else
<div style="width:60px; height:60px; background:#e5e7eb;"></div>
@endif

</td>

<td valign="top" style="padding-left:10px;">
<strong style="color:#111827;">
{{ $product->name ?? 'Sản phẩm' }}
</strong>

@if($variant)
<br>
<span style="color:#6b7280; font-size:13px;">
{{ $variant->attribute_name }}:
{{ $variant->attribute_value }}
</span>
@endif

</td>

</tr>
</table>

</td>

<td align="center" style="border-bottom:1px solid #f1f1f1;">
{{ $item->quantity }}
</td>

<td align="right" style="border-bottom:1px solid #f1f1f1;">
{{ number_format($item->price) }} đ
</td>

</tr>

@empty

<tr>
<td colspan="3" align="center" style="padding:20px; color:#777;">
Không có sản phẩm trong đơn hàng.
</td>
</tr>

@endforelse

</tbody>
</table>

</td>
</tr>


{{-- TOTAL --}}
<tr>
<td style="padding:0 30px 30px 30px;">

<table width="100%" cellpadding="10" cellspacing="0" style="background:#f9fafb; border-radius:6px;">
<tr>

<td style="font-size:15px;">
<strong>Tổng thanh toán</strong>
</td>

<td align="right" style="font-size:18px; color:#e11d48; font-weight:bold;">
{{ number_format($order->grand_total) }} đ
</td>

</tr>
</table>

</td>
</tr>


{{-- CONFIRM RECEIVE MESSAGE --}}
<tr>
<td style="padding:0 30px 20px 30px;">

<p style="font-size:14px; color:#555; line-height:1.6;">
Sau khi nhận được hàng, vui lòng vào trang <strong>Lịch sử đơn hàng</strong>  
và nhấn nút <strong>"Đã nhận hàng"</strong> để xác nhận hoàn tất đơn hàng.
</p>

</td>
</tr>


{{-- BUTTON --}}
<tr>
<td style="padding:0 30px 40px 30px; text-align:center;">

<a href="http://127.0.0.1:8000/orders"
style="background:#111827;
color:#ffffff;
padding:14px 30px;
text-decoration:none;
font-size:14px;
border-radius:4px;
display:inline-block;
font-weight:600;">

Xem lịch sử đơn hàng

</a>

</td>
</tr>


{{-- FOOTER --}}
<tr>
<td style="background:#f3f4f6; padding:20px 30px; text-align:center; font-size:12px; color:#6b7280;">

<p style="margin:0;">
Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi.
</p>

<p style="margin:8px 0 0 0;">
© {{ date('Y') }} ELARA. All rights reserved.
</p>

</td>
</tr>

</table>

</td>
</tr>
</table>

</body>
</html>