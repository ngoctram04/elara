@extends('layouts.frontend')
@section('title','Đánh giá sản phẩm')

@section('content')

<style>
.review-card{
    background:#fff;
    border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,.05);
}

/* ===== STAR ===== */
.star{
    font-size:26px;
    color:#ccc;
    cursor:pointer;
}
.star.active{ color:#ffc107; }

/* ===== QUICK TAG ===== */
.quick-tags{
    display:flex;
    flex-wrap:wrap;
    gap:8px;
}
.tag-btn{
    border:1px solid #ddd;
    padding:6px 12px;
    border-radius:20px;
    cursor:pointer;
    background:#fff;
    font-size:14px;
}
.tag-btn.active{
    border-color:#0d6efd;
    color:#0d6efd;
    background:#f0f6ff;
}

/* ===== MEDIA BUTTON ===== */
.media-btn{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:10px 18px;
    border:2px solid #ff4d4f;
    color:#ff4d4f;
    border-radius:6px;
    font-weight:600;
    cursor:pointer;
    background:#fff;
    transition:.2s;
}
.media-btn:hover{
    background:#fff5f5;
}

/* ===== PREVIEW ===== */
.preview-box{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    margin-top:10px;
}
.preview-box img{
    width:80px;
    height:80px;
    object-fit:cover;
    border-radius:6px;
    border:1px solid #ddd;
}
.preview-box video{
    width:140px;
    border-radius:6px;
    border:1px solid #ddd;
}

/* ===== PROGRESS ===== */
.progress{
    height:8px;
    margin-top:12px;
    display:none;
}
</style>

<div class="container py-4">

<h4 class="fw-bold mb-4">Đánh giá sản phẩm</h4>

@php
$variant = $orderItem->variant;
$product = $variant->product ?? null;

$image = optional($variant->mainImage)->image_path
    ?? optional($product->mainImage)->image_path;

$imageUrl = $image
    ? asset('storage/'.$image)
    : asset('images/no-image.png');
@endphp

<div class="review-card p-4">

{{-- PRODUCT --}}
<div class="d-flex mb-4">
    <img src="{{ $imageUrl }}"
         width="80"
         height="80"
         class="rounded me-3"
         style="object-fit:cover">

    <div>
        <b>{{ $product->name }}</b><br>
        <small class="text-muted">
            {{ $variant->attribute_name }}:
            {{ $variant->attribute_value }}
        </small>
    </div>
</div>

<form id="reviewForm"
      action="{{ route('reviews.store',$orderItem->id) }}"
      method="POST"
      enctype="multipart/form-data">
@csrf

{{-- STAR --}}
<div class="mb-3">
    <label class="fw-bold mb-2">Chất lượng sản phẩm</label><br>
    <div id="starBox">
        @for($i=1;$i<=5;$i++)
            <span class="star" data-value="{{ $i }}">★</span>
        @endfor
    </div>
    <input type="hidden" name="rating" id="rating" required>
</div>

{{-- QUICK TAG --}}
<div class="mb-3">
    <label class="fw-bold mb-2">Đánh giá nhanh</label>
    <div class="quick-tags">
        <span class="tag-btn">Đáng tiền</span>
        <span class="tag-btn">Giao nhanh</span>
        <span class="tag-btn">Đóng gói tốt</span>
        <span class="tag-btn">Đúng mô tả</span>
    </div>
</div>

{{-- COMMENT --}}
<div class="mb-3">
    <label class="fw-bold">Nhận xét</label>
    <textarea name="comment"
              id="comment"
              class="form-control"
              rows="4"
              placeholder="Chia sẻ trải nghiệm của bạn..."></textarea>
</div>

{{-- MEDIA --}}
<div class="mb-4">
    <label class="fw-bold mb-2">Hình ảnh / Video</label>

    <div class="d-flex gap-2">
        <label class="media-btn">
            <i class="bi bi-camera-fill"></i>
            Thêm hình ảnh
            <input type="file"
                   id="imageInput"
                   name="images[]"
                   multiple
                   accept="image/*"
                   hidden>
        </label>

        <label class="media-btn">
            <i class="bi bi-camera-video-fill"></i>
            Thêm video
            <input type="file"
                   id="videoInput"
                   name="video"
                   accept="video/*"
                   hidden>
        </label>
    </div>

    <div id="imagePreview" class="preview-box"></div>
    <div id="videoPreview" class="preview-box"></div>
</div>

{{-- PROGRESS --}}
<div class="progress">
    <div class="progress-bar" id="progressBar" style="width:0%"></div>
</div>

<button class="btn btn-primary">Gửi đánh giá</button>
<a href="{{ route('orders.show',$orderItem->order_id) }}"
   class="btn btn-secondary">Quay lại</a>

</form>
</div>
</div>

<script>

/* ===== STAR ===== */
const stars = document.querySelectorAll('.star');
const ratingInput = document.getElementById('rating');

stars.forEach(star=>{
    star.onclick = function(){
        let value = this.dataset.value;
        ratingInput.value = value;
        stars.forEach(s=>s.classList.remove('active'));
        for(let i=0;i<value;i++) stars[i].classList.add('active');
    }
});

/* ===== QUICK TAG ===== */
document.querySelectorAll('.tag-btn').forEach(tag=>{
    tag.onclick = function(){
        this.classList.toggle('active');
        updateComment();
    }
});

function updateComment(){
    let tags = '';
    document.querySelectorAll('.tag-btn.active').forEach(t=>{
        tags += t.innerText + '. ';
    });
    document.getElementById('comment').value = tags;
}

/* ===== IMAGE PREVIEW (max 5) ===== */
const imageInput = document.getElementById('imageInput');
const imagePreview = document.getElementById('imagePreview');

imageInput.addEventListener('change', function(){
    imagePreview.innerHTML = '';

    if(this.files.length > 5){
        alert('Chỉ được tối đa 5 ảnh');
        this.value = '';
        return;
    }

    Array.from(this.files).forEach(file=>{
        const reader = new FileReader();
        reader.onload = e=>{
            const img = document.createElement('img');
            img.src = e.target.result;
            imagePreview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
});

/* ===== VIDEO PREVIEW ===== */
const videoInput = document.getElementById('videoInput');
const videoPreview = document.getElementById('videoPreview');

videoInput.addEventListener('change', function(){
    videoPreview.innerHTML = '';

    if(this.files[0]){
        const video = document.createElement('video');
        video.src = URL.createObjectURL(this.files[0]);
        video.controls = true;
        videoPreview.appendChild(video);
    }
});

/* ===== PROGRESS UPLOAD ===== */
document.getElementById('reviewForm').addEventListener('submit',function(e){
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', form.action, true);
    xhr.setRequestHeader('X-CSRF-TOKEN','{{ csrf_token() }}');

    document.querySelector('.progress').style.display = 'block';

    xhr.upload.onprogress = function(e){
        if(e.lengthComputable){
            let percent = (e.loaded / e.total) * 100;
            document.getElementById('progressBar').style.width = percent + '%';
        }
    };

    xhr.onload = function(){
        if(xhr.status === 200){
            window.location.href = "{{ route('orders.show',$orderItem->order_id) }}";
        }else{
            alert('Upload thất bại');
        }
    };

    xhr.send(formData);
});
</script>

@endsection