@extends('layouts.admin')

@section('title','Chi tiết đánh giá')

@section('content')

<div class="container-fluid">

<h4 class="mb-4">
<i class="bi bi-star-fill text-warning"></i>
Chi tiết đánh giá
</h4>

<div class="card shadow-sm border-0">

<div class="card-body">

<div class="row">

{{-- ===== THÔNG TIN REVIEW ===== --}}
<div class="col-md-6">

<p>
<strong>ID:</strong>
{{ $review->id }}
</p>

<p>
<strong>Khách hàng:</strong>
{{ $review->user->name ?? 'N/A' }}
</p>

<p>
<strong>Sản phẩm:</strong>
{{ $review->product->name ?? 'N/A' }}
</p>

<p>
<strong>Rating:</strong>

<span class="text-warning">
@for($i=1;$i<=5;$i++)
@if($i <= $review->rating)
<i class="bi bi-star-fill"></i>
@else
<i class="bi bi-star"></i>
@endif
@endfor
</span>

</p>

<p>
<strong>Ngày đánh giá:</strong>
{{ $review->created_at->format('d/m/Y H:i') }}
</p>

<p>
<strong>Trạng thái:</strong>

@if($review->is_visible)
<span class="badge bg-success">Hiển thị</span>
@else
<span class="badge bg-secondary">Đã ẩn</span>
@endif

</p>

</div>

{{-- ===== ẨN / HIỆN ===== --}}
<div class="col-md-6 text-end">

<form action="{{ route('admin.reviews.toggle',$review->id) }}" method="POST">

@csrf

<button class="btn btn-warning">

@if($review->is_visible)
Ẩn đánh giá
@else
Hiện đánh giá
@endif

</button>

</form>

</div>

</div>


<hr>

{{-- ===== NỘI DUNG REVIEW ===== --}}
<h6>Nội dung đánh giá</h6>

<div class="border rounded p-3 bg-light">

{{ $review->comment }}

</div>

<hr>

{{-- ===== ẢNH REVIEW ===== --}}
@if($review->media->count())

<h6>Ảnh / Video review</h6>

<div class="row">

@foreach($review->media as $media)

<div class="col-md-3 mb-3">

<img src="{{ asset('storage/'.$media->file_path) }}"
class="img-fluid rounded border">

</div>

@endforeach

</div>

<hr>

@endif


{{-- ===== ADMIN REPLY ===== --}}
<h6>Trả lời từ cửa hàng</h6>

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

<p class="mb-0">
{{ $review->admin_reply }}
</p>

</div>

@endif


</div>
</div>

</div>

@endsection