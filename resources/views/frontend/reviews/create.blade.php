@extends('layouts.frontend')
@section('title','Đánh giá sản phẩm')

@section('content')

<style>
.review-card{
    background:#fff;
    border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,.05);
}

.star{
    font-size:26px;
    color:#ccc;
    cursor:pointer;
}

.star.active{
    color:#ffc107;
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

    {{-- SẢN PHẨM --}}
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

    <form action="{{ route('reviews.store',$orderItem->id) }}"
      method="POST"
      enctype="multipart/form-data">
    @csrf

    {{-- ĐÁNH GIÁ SAO --}}
    <div class="mb-3">
        <label class="fw-bold mb-2">Đánh giá chung</label><br>

        <div id="starBox">
            @for($i=1;$i<=5;$i++)
                <span class="star" data-value="{{ $i }}">★</span>
            @endfor
        </div>

        <input type="hidden" name="rating" id="rating" required>
    </div>

    {{-- NHẬN XÉT --}}
    <div class="mb-3">
        <label class="fw-bold">Nhận xét</label>
        <textarea name="comment"
                  class="form-control"
                  rows="4"
                  placeholder="Chia sẻ trải nghiệm của bạn..."></textarea>
    </div>

    {{-- ẢNH --}}
    <div class="mb-3">
        <label class="fw-bold">Ảnh (tối đa 5)</label>
        <input type="file"
               name="images[]"
               class="form-control"
               multiple
               accept="image/*">
    </div>

    {{-- VIDEO --}}
    <div class="mb-4">
        <label class="fw-bold">Video (1 file)</label>
        <input type="file"
               name="video"
               class="form-control"
               accept="video/*">
    </div>

    <button class="btn btn-primary">
        Gửi đánh giá
    </button>

    <a href="{{ route('orders.show',$orderItem->order_id) }}"
       class="btn btn-secondary">
        Quay lại
    </a>

</form>

</div>

</div>


{{-- SCRIPT SAO --}}
<script>
let stars = document.querySelectorAll('.star');
let ratingInput = document.getElementById('rating');

stars.forEach(star => {
    star.addEventListener('click', function(){
        let value = this.getAttribute('data-value');
        ratingInput.value = value;

        stars.forEach(s => s.classList.remove('active'));

        for(let i=0;i<value;i++){
            stars[i].classList.add('active');
        }
    });
});
</script>

@endsection