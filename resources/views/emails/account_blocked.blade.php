<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Tài khoản bị khóa</title>
</head>

<body style="font-family: Arial, sans-serif; background:#f4f6f8; padding:20px;">

<table width="100%" cellspacing="0" cellpadding="0">
<tr>
<td align="center">

<table width="600" style="background:#ffffff;border-radius:10px;padding:30px">

<tr>
<td align="center" style="padding-bottom:20px">

<h2 style="color:#dc3545;margin:0">
Tài khoản ELARA của bạn đã bị khóa
</h2>

</td>
</tr>


<tr>
<td style="font-size:15px;color:#333;line-height:1.6">

<p>Xin chào <strong>{{ $user->name }}</strong>,</p>

<p>
Hệ thống <strong>ELARA Shop</strong> phát hiện tài khoản của bạn có hành vi vi phạm chính sách.
Do đó tài khoản của bạn đã bị <strong>tạm khóa</strong>.
</p>

<hr style="border:none;border-top:1px solid #eee;margin:20px 0">

<p><strong>Lý do khóa:</strong></p>

<p style="background:#fff3cd;padding:10px;border-radius:6px">
{{ $reason }}
</p>


<p><strong>Thời gian khóa:</strong></p>

<table style="background:#f8f9fa;border-radius:6px;padding:12px;width:100%">
<tr>
<td><strong>Bắt đầu:</strong></td>
<td>{{ $locked_from }}</td>
</tr>

<tr>
<td><strong>Kết thúc:</strong></td>
<td>{{ $locked_until }}</td>
</tr>
</table>


<p style="margin-top:20px">
Trong thời gian này bạn sẽ <strong>không thể đăng nhập hoặc thực hiện giao dịch</strong> trên hệ thống.
</p>


<hr style="border:none;border-top:1px solid #eee;margin:20px 0">

<p>
Nếu bạn cho rằng đây là nhầm lẫn hoặc muốn yêu cầu xem xét mở lại tài khoản,
bạn có thể liên hệ với quản trị viên bằng một trong các cách sau:
</p>

<ul>
<li>Gửi email: <strong>elara.shop26@gmail.com</strong></li>
<li>Tiêu đề email: <strong>Yêu cầu mở khóa tài khoản</strong></li>
<li>Nội dung: ghi rõ email tài khoản và lý do yêu cầu mở khóa</li>
</ul>


<p style="margin-top:20px">
Chúng tôi sẽ xem xét yêu cầu của bạn trong thời gian sớm nhất.
</p>


<p>Trân trọng,</p>

<p><strong>ELARA Shop</strong></p>

</td>
</tr>


<tr>
<td style="padding-top:25px;border-top:1px solid #eee;font-size:12px;color:#888;text-align:center">

Email này được gửi tự động từ hệ thống ELARA.  
Vui lòng không trả lời trực tiếp email này.

</td>
</tr>

</table>

</td>
</tr>
</table>

</body>
</html>