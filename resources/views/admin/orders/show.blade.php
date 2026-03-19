@extends('layouts.admin')
@section('title','Chi tiết đơn hàng')

@section('content')

<div class="card border-0 shadow-sm">
<div class="card-body">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h5 class="fw-bold mb-1">Chi tiết đơn hàng</h5>
<small class="text-muted">Đơn hàng #{{ $order->id }}</small>
</div>

<a href="{{ route('admin.orders.index') }}"
class="btn btn-outline-secondary btn-sm"> <i class="bi bi-arrow-left"></i> Quay lại </a>

</div>

<div class="row g-4">

{{-- ================= LEFT COLUMN ================= --}}

<div class="col-md-4">

<div class="card border-0 shadow-sm">
<div class="card-body">

<h6 class="fw-bold mb-3">Thông tin giao hàng</h6>

<p class="mb-1">
<strong>Khách:</strong>
{{ $order->receiver_name ?? optional($order->user)->name }}
</p>

<p class="mb-1">
<strong>SĐT:</strong>
{{ $order->receiver_phone }}
</p>

<p class="mb-2">
<strong>Địa chỉ:</strong>
{{ $order->receiver_address }}
</p>

<hr>

<p class="mb-2">

<strong>Thanh toán:</strong><br>

{{ $order->payment_method_name }}

<br>

<span class="badge bg-{{ $order->payment_status_badge }}">
{{ $order->payment_status_name }}
</span>

</p>

<hr>

<p class="mb-2">

<strong>Trạng thái:</strong><br>

<span class="badge bg-{{ $order->status_badge }}">
{{ $order->status_name }}
</span>

@if($order->status == 3 && !$order->customer_confirmed) <br> <small class="text-info">Chờ khách xác nhận</small>
@endif

</p>

<hr>

<p class="mb-1">
<strong>Ngày đặt:</strong><br>
{{ $order->created_at->format('d/m/Y H:i') }}
</p>

@if($order->delivered_at)

<p class="mb-2">
<strong>Ngày giao:</strong><br>
{{ \Carbon\Carbon::parse($order->delivered_at)->format('d/m/Y H:i') }}
</p>

@endif
@if($order->delivery_image)
<div class="mt-2">
    <strong>Ảnh giao hàng:</strong><br>

    <img src="{{ asset('storage/' . $order->delivery_image) }}"
         class="rounded border mt-1"
         style="max-width:100%; max-height:200px;">
</div>
@endif
<hr>

<p class="mb-0">

<strong>Tổng khách trả:</strong><br>

<span class="fs-5 text-danger fw-bold">
{{ number_format($order->grand_total,0,',','.') }}đ
</span>

</p>

</div>
</div>

{{-- ===== UPDATE STATUS ===== --}}

@if($order->status == 1 || $order->status == 2)

<div class="card border-0 shadow-sm mt-3">
<div class="card-body">

<form method="POST"
action="{{ route('admin.orders.updateStatus',$order->id) }}"
enctype="multipart/form-data">
@csrf

<label class="mb-2 fw-semibold">
Cập nhật trạng thái
</label>

<select name="status"
class="form-select form-select-sm mb-3"
id="status_select">

@if($order->status == 1)

<option value="2">Đang giao</option>

@endif

@if($order->status == 2)

<option value="3">Đã giao</option>

@endif

</select>
<div id="proof_box" class="mb-3" style="display:none;">

    <label class="mb-2 fw-semibold">Ảnh giao hàng *</label>

    {{-- BOX CHỌN ẢNH --}}
    <div onclick="document.getElementById('file_input').click()"
         style="
            border:2px dashed #ccc;
            border-radius:10px;
            padding:20px;
            text-align:center;
            cursor:pointer;
         "
         id="upload_box">

        <div id="upload_text" class="text-muted">
            📷 Bấm để chọn ảnh giao hàng
        </div>

        <img id="preview_img"
             style="max-width:100%; max-height:150px; display:none; margin-top:10px; border-radius:8px;">
    </div>

    {{-- INPUT ẨN --}}
    <input type="file"
           id="file_input"
           name="delivery_proof"
           accept="image/*"
           style="display:none"
           onchange="previewImage(event)">

</div>
<button class="btn btn-success btn-sm w-100">
Cập nhật
</button>

</form>

</div>
</div>

@endif

{{-- ===== CANCEL ORDER ===== --}}

@if($order->status == 1)

<div class="card border-0 shadow-sm mt-3">
<div class="card-body">

<form method="POST"
action="{{ route('admin.orders.cancel',$order->id) }}">

@csrf

<label class="mb-2 text-danger fw-semibold">
Huỷ đơn (Admin)
</label>

<textarea name="cancel_reason"
class="form-control form-control-sm mb-3"
placeholder="Nhập lý do huỷ..."
required></textarea>

<button class="btn btn-danger btn-sm w-100">
Huỷ đơn
</button>

</form>

</div>
</div>

@endif

@if($order->status == 4)

<div class="alert alert-danger mt-3">

<strong>Đơn hàng đã bị huỷ</strong>

<hr class="my-2">

<div>
<strong>Người huỷ:</strong>
{{ $order->cancelled_by == 'admin' ? 'Admin' : 'Khách hàng' }}
</div>

<div>
<strong>Thời gian:</strong>
{{ optional($order->cancelled_at)->format('d/m/Y H:i') }}
</div>

<div>
<strong>Lý do:</strong>
{{ $order->cancel_reason }}
</div>

</div>

@endif

</div>

{{-- ================= RIGHT COLUMN ================= --}}

<div class="col-md-8">

<div class="card border-0 shadow-sm">
<div class="card-body">

<h6 class="fw-bold mb-3">Sản phẩm</h6>

@foreach($order->items as $item)

@php

$image =
optional($item->variant->images->first())->image_path
?? optional($item->variant->product->images->first())->image_path;

@endphp

<div class="d-flex align-items-center border-bottom py-3">

<div class="me-3">

<img src="{{ $image ? asset('storage/'.$image) : asset('images/no-image.png') }}"
width="70"
height="70"
class="rounded"
style="object-fit:cover">

</div>

<div class="flex-grow-1">

<div class="fw-semibold">
{{ $item->variant->product->name }}
</div>

<small class="text-muted">

{{ $item->variant->attribute_name ?? '' }}
{{ $item->variant->attribute_value ?? '' }}

</small>

<div class="text-muted">
Số lượng: {{ $item->quantity }}
</div>

</div>

<div class="text-end fw-bold text-danger">

{{ number_format($item->price * $item->quantity,0,',','.') }}đ

</div>

</div>

@endforeach

<hr>

<div class="text-end">

<div>
Tạm tính:
<strong>{{ number_format($order->subtotal,0,',','.') }}đ</strong>
</div>

@if($order->discount > 0)

<div class="text-success">
Giảm giá:
-{{ number_format($order->discount,0,',','.') }}đ
</div>

@endif

<div>
Phí vận chuyển:
<strong>{{ number_format($order->shipping_fee,0,',','.') }}đ</strong>
</div>

<h5 class="text-danger fw-bold mt-2">
Tổng khách trả:
{{ number_format($order->grand_total,0,',','.') }}đ
</h5>

<small class="text-muted">
(Doanh thu:
{{ number_format($order->total + $order->shipping_fee,0,',','.') }}đ)
</small>

</div>

</div>
</div>

</div>

</div>

</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const statusSelect = document.getElementById('status_select');
    const box = document.getElementById('proof_box');

    if (!statusSelect || !box) return;

    function toggleBox() {
        if (statusSelect.value == 3) {
            box.style.display = 'block';
        } else {
            box.style.display = 'none';
        }
    }

    // chạy lần đầu
    toggleBox();

    // change
    statusSelect.addEventListener('change', toggleBox);
});

function previewImage(event) {
    const file = event.target.files[0];
    if (!file) return;

    const img = document.getElementById('preview_img');
    const text = document.getElementById('upload_text');

    if (!img) return;

    // set ảnh
    img.src = URL.createObjectURL(file);

    // 🔥 FIX QUAN TRỌNG (hiện ảnh)
    img.style.display = 'block';

    // đổi text cho đẹp
    if (text) {
        text.innerText = "Đã chọn ảnh ✔";
    }
}
</script>
@endsection
