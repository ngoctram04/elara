@extends('layouts.admin')

@section('title','Chi tiết đánh giá')

@section('content')

<div class="card border-0 shadow-sm">
<div class="card-body">

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h5 class="fw-bold mb-1">
Chi tiết đánh giá
</h5>

<small class="text-muted">
Xem và phản hồi đánh giá của khách hàng
</small>
</div>

<div class="d-flex gap-2">

@if($review->order_id)
<a href="{{ route('admin.orders.show',$review->order_id) }}"
class="btn btn-outline-primary btn-sm">
<i class="bi bi-receipt"></i>
Xem đơn hàng
</a>
@endif

<a href="{{ route('admin.reviews.index') }}"
class="btn btn-outline-secondary btn-sm">
<i class="bi bi-arrow-left"></i>
Quay lại
</a>

</div>

</div>


<div class="row g-4">

{{-- ===== THÔNG TIN ===== --}}
<div class="col-md-8">

<div class="border rounded p-3">

<div class="row mb-2">

<div class="col-md-6">
<strong>Đơn hàng:</strong>
#{{ $review->order_id ?? 'N/A' }}
</div>

<div class="col-md-6">
<strong>Khách hàng:</strong>
{{ $review->user->name ?? 'N/A' }}
</div>

</div>


<div class="row mb-2">

<div class="col-md-6">
<strong>Sản phẩm:</strong>
{{ $review->product->name ?? 'N/A' }}
</div>

<div class="col-md-6">
<strong>Phân loại:</strong>

@if($review->variant)
{{ $review->variant->attribute_name }}
:
{{ $review->variant->attribute_value }}
@else
N/A
@endif

</div>

</div>


<div class="row mb-2">

<div class="col-md-6">
<strong>Ngày đánh giá:</strong>
{{ $review->created_at->format('d/m/Y H:i') }}
</div>

<div class="col-md-6">

<strong>Rating:</strong>

<span class="text-warning ms-2">

@for($i=1;$i<=5;$i++)
@if($i <= $review->rating)
<i class="bi bi-star-fill"></i>
@else
<i class="bi bi-star"></i>
@endif
@endfor

</span>

</div>

</div>


<div class="row mb-2">

<div class="col-md-6">

<strong>Trạng thái:</strong>

@if($review->is_visible)
<span class="badge bg-success ms-2">
Hiển thị
</span>
@else
<span class="badge bg-secondary ms-2">
Đã ẩn
</span>
@endif

</div>

</div>

</div>

</div>


{{-- ===== ẨN / HIỆN ===== --}}
<div class="col-md-4 text-end">

<form action="{{ route('admin.reviews.toggle',$review->id) }}" method="POST">
@csrf

<button class="btn btn-warning">

@if($review->is_visible)
<i class="bi bi-eye-slash"></i>
Ẩn đánh giá
@else
<i class="bi bi-eye"></i>
Hiện đánh giá
@endif

</button>

</form>

</div>

</div>


<hr>


{{-- ===== NỘI DUNG REVIEW ===== --}}
<h6 class="fw-bold mb-2">
Nội dung đánh giá
</h6>

<div class="border rounded p-3 bg-light mb-4">

{{ $review->comment }}

</div>



{{-- ===== ẢNH REVIEW ===== --}}
@if($review->media->count())

<h6 class="fw-bold mb-3">
Ảnh / Video review
</h6>

<div class="row">

@foreach($review->media as $media)

<div class="col-md-3 col-6 mb-3">

<img src="{{ asset('storage/'.$media->file_path) }}"
class="img-fluid rounded border shadow-sm">

</div>

@endforeach

</div>

<hr>

@endif



{{-- ===== ADMIN REPLY ===== --}}
<h6 class="fw-bold mb-2">
Trả lời từ cửa hàng
</h6>

<form action="{{ route('admin.reviews.reply',$review->id) }}" method="POST">

@csrf

<div class="mb-3">

<textarea
name="admin_reply"
class="form-control"
rows="4"
placeholder="Nhập phản hồi cho khách hàng..."
>{{ $review->admin_reply }}</textarea>

</div>

<button class="btn btn-primary">

<i class="bi bi-send"></i>
Gửi phản hồi

</button>

</form>


@if($review->admin_reply)

<div class="mt-3 alert alert-success">

<strong>Phản hồi hiện tại:</strong>

<p class="mb-0 mt-1">
{{ $review->admin_reply }}
</p>

</div>

@endif


</div>
</div>

@endsection