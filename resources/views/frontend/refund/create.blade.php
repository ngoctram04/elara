@extends('layouts.frontend')

@section('title','Yêu cầu trả hàng / hoàn tiền')

@section('content')

<div class="container py-4">

<h5 class="fw-bold mb-4">Yêu cầu trả hàng / hoàn tiền</h5>

<form action="{{ route('refund.store') }}" method="POST" enctype="multipart/form-data">
@csrf

<input type="hidden" name="order_id" value="{{ $order->id }}">
@foreach($order->items as $item)

@php
$variant = $item->variant ?? null;
$product = $variant->product ?? null;

$image = null;

if($variant && $variant->mainImage){
    $image = $variant->mainImage->path
    ?? $variant->mainImage->image_path
    ?? null;
}

if(!$image && $product && $product->mainImage){
    $image = $product->mainImage->path
    ?? $product->mainImage->image_path
    ?? null;
}

$imageUrl = $image
? asset('storage/'.$image)
: 'https://via.placeholder.com/70x70?text=No+Image';
@endphp

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body d-flex align-items-center gap-3">

        <img
        src="{{ $imageUrl }}"
        width="60"
        height="60"
        style="object-fit:cover;border-radius:8px;border:1px solid #eee"
        >

        <div>
            <div class="fw-semibold">
                {{ $product->name ?? 'Sản phẩm' }}
            </div>

            <div class="text-muted small">
                #PK{{ $variant->id ?? '' }}
                × {{ $item->quantity ?? 1 }}
            </div>
        </div>

    </div>
</div>

@endforeach


{{-- REASON --}}

<div class="mb-4">

<label class="form-label fw-semibold">
Lý do trả hàng
</label>

<textarea
name="reason"
class="form-control"
rows="4"
placeholder="Vui lòng mô tả vấn đề sản phẩm..."
required></textarea>

</div>

{{-- MEDIA --}}

<div class="mb-4">

<label class="form-label fw-semibold">
Hình ảnh / Video minh chứng
</label>

<div class="d-flex gap-3 mb-2">

<label class="upload-box">

<i class="bi bi-camera-fill"></i> <span>Thêm hình ảnh</span>

<input
type="file"
name="images[]"
id="imageInput"
accept="image/*"
multiple
hidden>

</label>

<label class="upload-box">

<i class="bi bi-camera-video-fill"></i> <span>Thêm video</span>

<input
type="file"
name="video"
id="videoInput"
accept="video/*"
hidden>

</label>

</div>

<small class="text-muted">
Tối đa 5 ảnh và 1 video
</small>

</div>

{{-- PREVIEW --}}

<div id="previewArea" class="d-flex gap-2 flex-wrap mb-4"></div>

{{-- ACTION --}}

<div class="d-flex gap-2">

<button type="submit" class="btn btn-danger px-4">
Gửi yêu cầu hoàn tiền
</button>

<a href="{{ route('orders.show',$order->id) }}"
class="btn btn-secondary">
Quay lại </a>

</div>

</form>

</div>

<style>

.upload-box{
display:flex;
align-items:center;
gap:8px;
border:2px dashed #dc3545;
color:#dc3545;
padding:12px 20px;
border-radius:8px;
cursor:pointer;
font-weight:500;
}

.upload-box:hover{
background:#fff5f5;
}

#previewArea img,
#previewArea video{
width:100px;
height:100px;
object-fit:cover;
border-radius:6px;
border:1px solid #ddd;
}

</style>

<script>

const imageInput = document.getElementById('imageInput');
const videoInput = document.getElementById('videoInput');
const preview = document.getElementById('previewArea');

let imageCount = 0;
let videoAdded = false;


/*
| IMAGE PREVIEW
*/

imageInput.addEventListener('change', function(){

const files = Array.from(this.files);

if(imageCount + files.length > 5){
alert("Chỉ được tối đa 5 ảnh");
return;
}

files.forEach(file=>{

let url = URL.createObjectURL(file);

let div = document.createElement("div");

div.innerHTML = `<img src="${url}">`;

preview.appendChild(div);

imageCount++;

});

});


/*
| VIDEO PREVIEW
*/

videoInput.addEventListener('change', function(){

if(videoAdded){
alert("Chỉ được 1 video");
return;
}

const file = this.files[0];

if(!file) return;

let url = URL.createObjectURL(file);

let div = document.createElement("div");

div.innerHTML = `
<video controls>
<source src="${url}">
</video>
`;

preview.appendChild(div);

videoAdded = true;

});

</script>

@endsection
