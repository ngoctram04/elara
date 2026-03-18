@extends('layouts.frontend')

@section('title', $product->name)

@section('content')
@php
    $hasVariants = $product->variants->count() > 0;

    // Variant mặc định: ưu tiên còn hàng
    $defaultVariant = $product->variants->firstWhere('stock_quantity', '>', 0)
                        ?? $product->variants->first();

    // ===== Wishlist =====
    $isFavorited = in_array((int)$product->id, $favorites ?? [], true);

    // Hiển thị số lượt thích
    $displayFavoriteCount = 0;
    if(isset($favoritesCount)){
        $displayFavoriteCount = $favoritesCount >= 1000
            ? str_replace('.', ',', round($favoritesCount/1000,1)).'k'
            : number_format($favoritesCount,0,',','.');
    }
@endphp

<style>
/* ===== THUMB ===== */
.thumb-wrapper{max-height:480px;overflow-y:auto;padding-right:6px}
.thumb-img{width:100%;aspect-ratio:1/1;object-fit:contain;background:#fff;cursor:pointer;transition:.2s}
.thumb-img:hover{border:2px solid #0d6efd}
.thumb-img.active{border:2px solid #dc3545}

/* ===== VARIANT ===== */
.variant-btn{position:relative}
.variant-btn.active{border:2px solid #dc3545}

.variant-out{
    opacity:.5;
    pointer-events:none;
}
.variant-out::after{
    content:"Hết hàng";
    position:absolute;
    top:2px;
    right:2px;
    background:#dc3545;
    color:#fff;
    font-size:10px;
    padding:2px 4px;
    border-radius:3px;
}

/* ===== Wishlist button tròn ===== */
.wishlist-detail-btn{
    width:44px;
    height:44px;
    border:none;
    border-radius:50%;
    background:#fff;
    box-shadow:0 6px 16px rgba(0,0,0,0.12);
    display:flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    transition:all .25s ease;
    position:relative;
}

/* Icon */
.wishlist-detail-btn i{
    font-size:20px;
    transition:transform .25s ease;
}

/* Hover */
.wishlist-detail-btn:hover{
    transform:translateY(-2px) scale(1.05);
    box-shadow:0 10px 22px rgba(0,0,0,0.18);
}

/* Active */
.wishlist-detail-btn.active{
    background:#ffe5e5;
}

/* Pop animation */
.wishlist-detail-btn.animate i{
    transform:scale(1.35);
}
/* ===== Wishlist + Sold nằm ngang ===== */
.product-meta{
    display:flex;
    align-items:center;
    gap:24px;
    margin-top:6px;
}

/* từng item */
.wishlist-inline,
.sold-inline{
    display:flex;
    align-items:center;
    gap:6px;
    font-size:15px;
    color:#555;
    white-space:nowrap;
}

/* icon */
.wishlist-inline i,
.sold-inline i{
    font-size:18px;
    line-height:1;
}

/* số nổi bật */
.sold-inline strong{
    font-weight:600;
    color:#333;
}
/* ===== Wishlist text dưới nút giỏ ===== */
.wishlist-inline{
    cursor:pointer;
    font-size:15px;
    font-weight:500;
    display:inline-flex;
    align-items:center;
    gap:6px;
    margin-top:6px;
}
.wishlist-inline i{
    font-size:18px;
}
/* ===== REVIEW CARD ===== */
.review-card{
    background:#fff;
    border-radius:12px;
    border:1px solid #eee;
    padding:16px;
    margin-bottom:16px;
    transition:.2s;
}

.review-card:hover{
    box-shadow:0 6px 18px rgba(0,0,0,.06);
}

/* Header */
.review-header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:6px;
}

.review-user{
    display:flex;
    align-items:center;
    gap:10px;
}

.review-avatar{
    width:38px;
    height:38px;
    border-radius:50%;
    background:#f3f4f6;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:600;
    color:#555;
    font-size:14px;
}

/* Stars */
.review-stars{
    color:#f59e0b;
    font-size:15px;
}

/* Content */
.review-content{
    margin-top:6px;
    font-size:15px;
    color:#333;
    line-height:1.5;
}

/* Media */
.review-media img{
    width:80px;
    height:80px;
    object-fit:cover;
    border-radius:8px;
    border:1px solid #eee;
}

.review-media video{
    width:120px;
    border-radius:8px;
}
/* ===== SECTION BOX ===== */

.section-box{
    background:#fff;
    border:1px solid #e9ecef;
    border-radius:12px;
    padding:20px 22px;
    margin-top:30px;
}

/* title section */

.section-title{
    font-size:18px;
    font-weight:700;
    margin-bottom:16px;
}

/* hỏi đáp item */

.qa-item{
    border:1px solid #eee;
    border-radius:10px;
    padding:14px;
    margin-bottom:12px;
    background:#fafafa;
}
/* ===== LIGHTBOX STYLE ===== */
#media-lightbox{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.85);
    display:flex;
    align-items:center;
    justify-content:center;
    z-index:9999;
}

#media-lightbox img,
#media-lightbox video{
    max-width:90%;
    max-height:90%;
    border-radius:10px;
    box-shadow:0 10px 30px rgba(0,0,0,.4);
}

#lightbox-close{
    position:absolute;
    top:20px;
    right:30px;
    font-size:40px;
    color:#fff;
    cursor:pointer;
    font-weight:bold;
}
#product-reviews{
    scroll-margin-top:150px;
}
.shop-reply{
background:#f8f9fa;
border-left:4px solid #0d6efd;
padding:10px 12px;
border-radius:6px;
font-size:14px;
}
</style>
<div class="container py-4">
<div class="row g-4">

{{-- THUMBNAILS --}}

<div class="col-md-2">
    <div class="d-flex flex-column gap-2 thumb-wrapper">
        @foreach($product->images as $img)
            <img src="{{ asset('storage/'.$img->image_path) }}"
                 class="img-thumbnail thumb-img"
                 data-image="{{ asset('storage/'.$img->image_path) }}">
        @endforeach

    @foreach($product->variants as $variant)
        @foreach($variant->images as $vImg)
            <img src="{{ asset('storage/'.$vImg->image_path) }}"
                 class="img-thumbnail thumb-img"
                 data-image="{{ asset('storage/'.$vImg->image_path) }}"
                 data-variant="{{ $variant->id }}">
        @endforeach
    @endforeach
</div>

</div>

{{-- MAIN IMAGE --}}

<div class="col-md-5 d-flex align-items-center justify-content-center">
    <img id="main-image"
         src="{{ $defaultVariant?->images->first()
                ? asset('storage/'.$defaultVariant->images->first()->image_path)
                : ($product->mainImage
                    ? asset('storage/'.$product->mainImage->image_path)
                    : asset('images/no-image.png')) }}"
         class="img-fluid border rounded"
         style="max-height:480px;object-fit:contain">
</div>

{{-- INFO --}}

<div class="col-md-5">

{{-- Title + Wishlist --}}

{{-- TÊN SẢN PHẨM --}}
    <h4 class="fw-bold mb-2">
        {{ $product->name }}
    </h4>

    {{-- GIÁ --}}
<div class="mb-3">
    <div id="price-original"
         class="text-muted text-decoration-line-through"
         style="display:none"></div>

    <div id="price-final" class="fs-3 fw-bold text-danger"></div>
</div>

<p><strong>Danh mục:</strong> {{ $product->category?->name }}</p>
<p><strong>Thương hiệu:</strong> {{ $product->brand?->name }}</p>

{{-- VARIANTS --}}
@php
    $groupedVariants = $product->variants->groupBy('attribute_name');
@endphp
@if($hasVariants)
<div class="mb-4">

    @foreach($groupedVariants as $attributeName => $variants)
        <div class="mb-3">

            {{-- Tên phân loại --}}
            <strong>{{ $attributeName }}:</strong>

            <div class="d-flex flex-wrap gap-2 mt-2">
                @foreach($variants as $variant)
                    @php
                        $variantImage = $variant->images->first();
                        $fallback = $product->mainImage
                            ? asset('storage/'.$product->mainImage->image_path)
                            : asset('images/no-image.png');

                        $final = $variant->final_price ?? $variant->price;
                        $original = $variant->is_on_sale ? $variant->price : '';
                        $outOfStock = $variant->stock_quantity <= 0;
                    @endphp

                    <button type="button"
                            class="btn btn-outline-secondary variant-btn {{ $outOfStock ? 'variant-out' : '' }}"
                            data-id="{{ $variant->id }}"
                            data-final="{{ $final }}"
                            data-original="{{ $original }}"
                            data-stock="{{ $variant->stock_quantity }}"
                            data-image="{{ $variantImage ? asset('storage/'.$variantImage->image_path) : $fallback }}"
                            {{ $outOfStock ? 'disabled' : '' }}>

                        @if($variantImage)
                            <img src="{{ asset('storage/'.$variantImage->image_path) }}"
                                 style="width:40px;height:40px;object-fit:cover">
                        @endif

                        <div class="small fw-semibold">
                            {{ $variant->attribute_value }}
                        </div>
                    </button>
                @endforeach
            </div>

        </div>
    @endforeach

    <div id="stock-text" class="text-muted mt-2"></div>
</div>
@endif

{{-- ACTION --}}
<form method="POST"
      action="{{ route('cart.add') }}"
      id="add-to-cart-form"
      class="d-flex gap-2">
    @csrf
    <input type="hidden" name="variant_id" id="variant_id">

    <input type="number"
           name="qty"
           value="1"
           min="1"
           class="form-control w-auto">

    <button class="btn btn-primary">
        <i class="bi bi-cart-plus"></i> Thêm vào giỏ
    </button>
</form>

{{-- Wishlist count dưới nút giỏ --}}
@php
    $displayFavoriteCount = isset($favoritesCount)
        ? ($favoritesCount >= 1000
            ? str_replace('.', ',', round($favoritesCount/1000,1)).'k'
            : number_format($favoritesCount,0,',','.'))
        : 0;
@endphp
<div class="product-meta">

    {{-- Wishlist --}}
    <div class="meta-item btn-wishlist"
         data-product-id="{{ $product->id }}">
        <i class="bi {{ $isFavorited ? 'bi-heart-fill text-danger' : 'bi-heart text-danger' }}"></i>
        <span>
            {{ $isFavorited ? 'Đã thích' : 'Yêu thích' }}
            (<span id="wishlist-count">{{ $displayFavoriteCount }}</span>)
        </span>
    </div>

    {{-- Đã bán --}}
    <div class="meta-item">
        <i class="bi bi-bag-check text-success"></i>
        <span>
            Đã bán:
            <strong>{{ number_format($totalSold,0,',','.') }}</strong>
        </span>
    </div>

</div>

</div>
</div>

{{-- DESCRIPTION --}}

<div class="mt-5">
    <h5 class="fw-bold mb-3">Mô tả sản phẩm</h5>
    <div class="border rounded p-4 bg-white">
        {!! nl2br(e($product->description)) !!}
    </div>
</div>
{{-- ================= REVIEW ================= --}}
<div class="section-box mt-5" id="product-reviews">

<h5 class="section-title">
Đánh giá sản phẩm ({{ $reviewCount }})
</h5>

{{-- SUMMARY --}}
<div class="review-summary">

<div>
<div class="fs-2 text-danger fw-bold">
{{ number_format($avgRating,1) }}
<span class="text-muted fs-6">/5</span>
</div>

<div class="review-stars">
{!! str_repeat('★', round($avgRating)) !!}
{!! str_repeat('☆', 5-round($avgRating)) !!}
</div>
</div>

<div class="d-flex flex-wrap gap-2">

<a href="{{ request()->fullUrlWithQuery(['rating'=>'all']) }}#product-reviews"
class="btn btn-sm {{ request('rating')=='all' || !request('rating') ? 'btn-dark' : 'btn-outline-secondary' }}">
Tất cả
</a>

<a href="{{ request()->fullUrlWithQuery(['rating'=>5]) }}#product-reviews"
class="btn btn-sm {{ request('rating')==5 ? 'btn-dark' : 'btn-outline-secondary' }}">
5 Sao ({{ $ratingStats[5] }})
</a>

<a href="{{ request()->fullUrlWithQuery(['rating'=>4]) }}#product-reviews"
class="btn btn-sm {{ request('rating')==4 ? 'btn-dark' : 'btn-outline-secondary' }}">
4 Sao ({{ $ratingStats[4] }})
</a>

<a href="{{ request()->fullUrlWithQuery(['rating'=>3]) }}#product-reviews"
class="btn btn-sm {{ request('rating')==3 ? 'btn-dark' : 'btn-outline-secondary' }}">
3 Sao ({{ $ratingStats[3] }})
</a>

<a href="{{ request()->fullUrlWithQuery(['rating'=>2]) }}#product-reviews"
class="btn btn-sm {{ request('rating')==2 ? 'btn-dark' : 'btn-outline-secondary' }}">
2 Sao ({{ $ratingStats[2] }})
</a>

<a href="{{ request()->fullUrlWithQuery(['rating'=>1]) }}#product-reviews"
class="btn btn-sm {{ request('rating')==1 ? 'btn-dark' : 'btn-outline-secondary' }}">
1 Sao ({{ $ratingStats[1] }})
</a>

</div>

</div>


{{-- REVIEW LIST --}}
@forelse($reviews->where('is_visible',1) as $review)

<div class="review-card">

<div class="d-flex align-items-start">

<img
src="{{ $review->user?->avatar 
? asset('storage/'.$review->user->avatar) 
: asset('images/avatar-default.png') }}"
class="review-avatar me-3">

<div class="flex-grow-1">

<div class="d-flex justify-content-between">

<div class="fw-semibold">
{{ $review->user->name }}
</div>

<small class="text-muted">
{{ $review->created_at->format('d/m/Y H:i') }}
</small>

</div>

<div class="review-stars mb-1">
{!! str_repeat('★',(int)$review->rating) !!}
{!! str_repeat('☆',5-(int)$review->rating) !!}
</div>

</div>

</div>


@if($review->comment)
<div class="review-content mt-2">
{{ $review->comment }}
</div>
@endif


@if($review->media && $review->media->count())
<div class="review-media mt-2 d-flex gap-2 flex-wrap">

@foreach($review->media as $m)

@if($m->file_type == 'image')
<img src="{{ asset('storage/'.$m->file_path) }}">
@else
<video width="120" controls>
<source src="{{ asset('storage/'.$m->file_path) }}">
</video>
@endif

@endforeach

</div>
@endif


@if($review->admin_reply)

<div class="shop-reply mt-2">

<div class="fw-semibold text-primary">
<i class="bi bi-shop"></i>
Phản hồi từ cửa hàng
</div>

<div class="small text-muted mb-1">
{{ $review->replied_at?->format('d/m/Y H:i') }}
</div>

<div>
{{ $review->admin_reply }}
</div>

</div>

@endif

</div>

@empty

<div class="text-center py-4 text-muted">
<i class="bi bi-chat-left-text fs-3"></i>
<div class="mt-2">
Chưa có đánh giá
</div>
</div>

@endforelse


<div class="mt-4 d-flex justify-content-center">
{{ $reviews->withQueryString()->fragment('product-reviews')->links() }}
</div>

</div>



{{-- ================= HỎI ĐÁP ================= --}}
<div class="section-box">

<h5 class="section-title">
Hỏi đáp sản phẩm
</h5>


@if(auth()->check())

<form action="{{ route('questions.store') }}" method="POST" class="mb-4">
@csrf

<input type="hidden" name="product_id" value="{{ $product->id }}">

<textarea
name="question"
class="form-control"
rows="3"
placeholder="Đặt câu hỏi về sản phẩm..."
required></textarea>

<button class="btn btn-primary mt-2">
<i class="bi bi-question-circle"></i>
Gửi câu hỏi
</button>

</form>

@else

<div class="alert alert-warning">
Bạn cần <a href="{{ route('login') }}">đăng nhập</a> để đặt câu hỏi.
</div>

@endif



@forelse($product->questions as $index => $question)

<div class="qa-item qa-hidden" 
     style="{{ $index >= 2 ? 'display:none' : '' }}">

<div class="fw-semibold">
{{ $question->user->name }}
</div>

<div class="small text-muted">
{{ $question->created_at->format('d/m/Y H:i') }}
</div>

<div class="mt-1">
{{ $question->question }}
</div>


@foreach($question->answers as $answer)

<div class="ms-4 border-start ps-3 mt-2">

<strong>
{{ $answer->user->name }}

@if($answer->is_admin)
<span class="badge bg-danger">Shop</span>
@endif

</strong>

<div class="small text-muted">
{{ $answer->created_at->format('d/m/Y H:i') }}
</div>

<div>
{{ $answer->answer }}
</div>

</div>

@endforeach

@if(auth()->check())

<form action="{{ route('questions.answer') }}"
method="POST"
class="mt-3 ms-4 d-flex gap-2">

@csrf

<input type="hidden"
name="question_id"
value="{{ $question->id }}">

<input type="text"
name="answer"
class="form-control form-control-sm"
placeholder="Trả lời câu hỏi..."
required>

<button class="btn btn-sm btn-primary">
<i class="bi bi-send"></i>
Gửi
</button>

</form>

@endif

</div>

@empty

<div class="text-muted">
Chưa có câu hỏi nào về sản phẩm này.
</div>

@endforelse
@if($product->questions->count() > 2)

<div class="text-center mt-3">

<button id="toggle-qa" class="btn btn-outline-secondary btn-sm">
Xem tất cả câu hỏi
</button>

</div>

@endif
</div>

</div>
{{-- ================= SẢN PHẨM LIÊN QUAN ================= --}}
@if(!empty($relatedProducts) && $relatedProducts->count())
<div class="mt-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Sản phẩm tương tự</h5>
        <a href="{{ route('shop', ['category' => $product->category_id]) }}"
           class="text-decoration-none small">
            Xem thêm →
        </a>
    </div>

    <div class="row g-3">
        @foreach($relatedProducts as $item)
            <div class="col-6 col-md-3">
                @include('frontend.partials.product-card-category', [
                    'product' => $item
                ])
            </div>
        @endforeach
    </div>

</div>
@endif
</div>

</div>
</div>
<!-- ===== LIGHTBOX ===== -->
<div id="media-lightbox" style="display:none;">
    <span id="lightbox-close">&times;</span>

    <img id="lightbox-img" style="display:none;">
    
    <video id="lightbox-video" controls style="display:none;">
        <source id="lightbox-video-src">
    </video>
</div>
@endsection

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', () => {


    /* =====================
       VARIANT
    ===================== */
    const mainImg  = document.getElementById('main-image');
    const qtyInput = document.querySelector('input[name="qty"]');
    const addBtn   = document.querySelector('#add-to-cart-form button');

    document.querySelectorAll('.variant-btn:not(.variant-out)').forEach(btn => {
        btn.addEventListener('click', () => {

            document.querySelectorAll('.variant-btn')
                .forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const final = parseFloat(btn.dataset.final);
            const original = btn.dataset.original;
            const stock = parseInt(btn.dataset.stock);

            document.getElementById('price-final').innerText =
                new Intl.NumberFormat('vi-VN').format(final) + 'đ';

            if (original) {
                document.getElementById('price-original').style.display = 'block';
                document.getElementById('price-original').innerText =
                    new Intl.NumberFormat('vi-VN').format(original) + 'đ';
            } else {
                document.getElementById('price-original').style.display = 'none';
            }

            document.getElementById('stock-text').innerText =
                'Còn ' + stock + ' sản phẩm';

            qtyInput.max = stock;
            if (qtyInput.value > stock) qtyInput.value = stock;

            addBtn.disabled = false;
            mainImg.src = btn.dataset.image;
            document.getElementById('variant_id').value = btn.dataset.id;
        });
    });

    const firstAvailable = document.querySelector('.variant-btn:not(.variant-out)');
    if(firstAvailable){
        firstAvailable.click();
    }else{
        addBtn.disabled = true;
        addBtn.innerText = 'Hết hàng';
        document.getElementById('stock-text').innerText = 'Sản phẩm đã hết hàng';
    }

});
/* =====================
   THUMB CLICK
===================== */
document.querySelectorAll('.thumb-img').forEach(img => {
    img.addEventListener('click', function () {

        // đổi ảnh chính
        document.getElementById('main-image').src = this.dataset.image;

        // active border
        document.querySelectorAll('.thumb-img')
            .forEach(i => i.classList.remove('active'));
        this.classList.add('active');

        // Nếu thumbnail thuộc variant → active luôn variant
        const variantId = this.dataset.variant;
        if (variantId) {
            const variantBtn = document.querySelector(
                '.variant-btn[data-id="' + variantId + '"]'
            );
            if (variantBtn && !variantBtn.classList.contains('variant-out')) {
                variantBtn.click();
            }
        }
    });
});
/* =====================
   ADD TO CART (DETAIL PAGE)
===================== */
const form = document.getElementById('add-to-cart-form');

if (form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);

        fetch("{{ route('cart.add') }}", {
            method: "POST",
            headers: {
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: formData
        })
        .then(async res => {
            const data = await res.json().catch(() => null);
            if (!res.ok || !data) throw new Error();
            return data;
        })
        .then(data => {
            if (data.success) {
                // Thông báo
                showToast('Đã thêm vào giỏ','success');

                // Cập nhật badge giỏ nếu có
                if (data.cart_count !== undefined) {
                    const badge = document.querySelector('.cart-badge');

if (badge) {
    badge.innerText = data.cart_count > 99 ? '99+' : data.cart_count;
}
                }
            } else {
                showToast('Số lượng sản phẩm trong giỏ đã đạt tối đa tồn kho', 'error');
            }
        })
        .catch(() => {
            showToast('Có lỗi hệ thống, vui lòng thử lại','error');
        });
    });
}

/* =====================
   LIGHTBOX IMAGE/VIDEO
===================== */
const lightbox = document.getElementById('media-lightbox');
const lightImg = document.getElementById('lightbox-img');
const lightVideo = document.getElementById('lightbox-video');
const lightVideoSrc = document.getElementById('lightbox-video-src');
const closeBtn = document.getElementById('lightbox-close');

// Mở ảnh
document.querySelectorAll('.review-media img').forEach(img => {
    img.style.cursor = 'pointer';
    img.addEventListener('click', function(){
        lightbox.style.display = 'flex';
        lightImg.src = this.src;
        lightImg.style.display = 'block';
        lightVideo.style.display = 'none';
    });
});

// Mở video
document.querySelectorAll('.review-media video').forEach(video => {
    video.style.cursor = 'pointer';
    video.addEventListener('click', function(){
        lightbox.style.display = 'flex';
        lightVideoSrc.src = this.querySelector('source').src;
        lightVideo.load();
        lightVideo.style.display = 'block';
        lightImg.style.display = 'none';
    });
});

// Đóng
function closeLightbox(){
    lightbox.style.display = 'none';
    lightVideo.pause();
}

closeBtn.addEventListener('click', closeLightbox);

// Click nền để đóng
lightbox.addEventListener('click', function(e){
    if(e.target === lightbox){
        closeLightbox();
    }
});

// ESC để đóng
document.addEventListener('keydown', function(e){
    if(e.key === 'Escape'){
        closeLightbox();
    }
});
/* =====================
   QA TOGGLE
===================== */

const qaBtn = document.getElementById('toggle-qa');

if(qaBtn){

let expanded = false;

qaBtn.addEventListener('click', () => {

const hiddenItems = document.querySelectorAll('.qa-hidden');

expanded = !expanded;

hiddenItems.forEach(item => {
item.style.display = expanded ? 'block' : 'none';
});

qaBtn.innerText = expanded
? 'Thu gọn câu hỏi'
: 'Xem tất cả câu hỏi';

});

}
</script>

@endpush
