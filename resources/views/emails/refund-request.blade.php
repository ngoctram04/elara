<!DOCTYPE html>

<html>
<head>
<meta charset="UTF-8">
<title>Yêu cầu hoàn tiền mới</title>
</head>

<body style="margin:0;padding:0;background:#f5f6fa;font-family:Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="padding:30px 0;">
<tr>
<td align="center">

<table width="600" cellpadding="0" cellspacing="0"
style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 4px 10px rgba(0,0,0,0.05);">

<!-- HEADER -->

<tr>
<td style="background:#2563eb;color:#fff;padding:20px;font-size:20px;font-weight:bold;text-align:center;">
ELARA - Yêu cầu hoàn tiền mới
</td>
</tr>

<!-- CONTENT -->

<tr>
<td style="padding:25px;color:#333;font-size:14px;line-height:1.6;">

<p>Có một <b>yêu cầu hoàn tiền mới</b> từ khách hàng.</p>

<table width="100%" style="border-collapse:collapse;margin-top:15px;">
<tr>
<td style="padding:8px;border-bottom:1px solid #eee;"><b>Mã đơn</b></td>
<td style="padding:8px;border-bottom:1px solid #eee;">#{{ $order->id }}</td>
</tr>

<tr>
<td style="padding:8px;border-bottom:1px solid #eee;"><b>Khách hàng</b></td>
<td style="padding:8px;border-bottom:1px solid #eee;">{{ $order->user->name }}</td>
</tr>

<tr>
<td style="padding:8px;border-bottom:1px solid #eee;"><b>Email</b></td>
<td style="padding:8px;border-bottom:1px solid #eee;">{{ $order->user->email }}</td>
</tr>

<tr>
<td style="padding:8px;border-bottom:1px solid #eee;"><b>Lý do hoàn tiền</b></td>
<td style="padding:8px;border-bottom:1px solid #eee;">{{ $refund->reason }}</td>
</tr>
</table>

<p style="margin-top:25px;">
Vui lòng vào trang quản trị để xem và xử lý yêu cầu này.
</p>

{{-- MEDIA --}}
@if($refund->media && $refund->media->count())

<p style="margin-top:20px;"><b>Hình ảnh / video minh chứng:</b></p>

@foreach($refund->media as $media)

{{-- IMAGE --}}
@if($media->type == 'image')

<img src="{{ $message->embed(storage_path('app/public/'.$media->file_path)) }}"
style="max-width:120px;margin:5px;border-radius:6px;">

@endif

{{-- VIDEO --}}
@if($media->type == 'video')

<div style="margin:5px 0;">
<a href="{{ asset('storage/'.$media->file_path) }}"
style="color:#2563eb;text-decoration:none;">
Xem video minh chứng
</a>
</div>

@endif

@endforeach

@endif

<!-- BUTTON -->

<div style="text-align:center;margin-top:25px;">

<a href="{{ url('/admin/refunds') }}"
style="display:inline-block;background:#2563eb;color:#fff;padding:12px 24px;text-decoration:none;border-radius:6px;font-weight:bold;">
Xem yêu cầu hoàn tiền </a>

</div>

</td>
</tr>

<!-- FOOTER -->

<tr>
<td style="background:#f1f5f9;text-align:center;padding:15px;font-size:12px;color:#777;">
Email này được gửi tự động từ hệ thống ELARA.
</td>
</tr>

</table>

</td>
</tr>
</table>

</body>
</html>
