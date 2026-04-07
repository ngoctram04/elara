<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
</head>

<body style="font-family:Arial;background:#f5f5f5;padding:20px">

<div style="max-width:600px;margin:auto;background:white;padding:20px;border-radius:8px">

<h2 style="color:#e74c3c">
Yêu cầu hoàn tiền bị từ chối
</h2>

<p>Xin chào {{ $refund->user->name ?? 'Quý khách' }},</p>

<p>
Yêu cầu hoàn tiền cho đơn hàng
<b>#{{ $refund->order_id }}</b>
đã không được chấp nhận.
</p>

@if(!empty($refund->admin_note))
<p>
<b>Lý do từ chối:</b>
{{ $refund->admin_note }}
</p>
@endif

<p>
Nếu bạn cần hỗ trợ thêm, vui lòng liên hệ cửa hàng để được giải đáp.
</p>

<hr>

<p style="color:#888;font-size:13px">
Cảm ơn bạn đã mua sắm tại cửa hàng.
</p>

</div>

</body>
</html>