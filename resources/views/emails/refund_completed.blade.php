<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
</head>

<body style="font-family:Arial;background:#f5f5f5;padding:20px">

<div style="max-width:600px;margin:auto;background:white;padding:20px;border-radius:8px">

<h2 style="color:#2ecc71">
Hoàn tiền thành công
</h2>

<p>Xin chào {{ $order->user->name }},</p>

<p>
Yêu cầu hoàn tiền cho đơn hàng
<b>#{{ $order->id }}</b>
đã được cửa hàng xử lý thành công.
</p>

<p>
<b>Số tiền hoàn:</b>
{{ number_format($amount) }}đ
</p>

<p>
Tiền sẽ được hoàn về phương thức thanh toán ban đầu của bạn.
Thời gian nhận tiền có thể mất từ <b>1 - 3 ngày làm việc</b>
tuỳ ngân hàng hoặc ví điện tử.
</p>

<p>
Nếu sau thời gian trên bạn vẫn chưa nhận được tiền,
vui lòng liên hệ với cửa hàng để được hỗ trợ.
</p>

<hr>

<p style="color:#888;font-size:13px">
Cảm ơn bạn đã mua sắm tại cửa hàng.
</p>

</div>

</body>
</html>