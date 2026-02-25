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


<div class="mb-3">
    <div id="price-original"
         class="text-muted text-decoration-line-through"
         style="display:none"></div>

    <div id="price-final" class="fs-3 fw-bold text-danger"></div>
</div>

<p><strong>Danh mục:</strong> {{ $product->category?->name }}</p>
<p><strong>Thương hiệu:</strong> {{ $product->brand?->name }}</p>

{{-- VARIANTS --}}
@if($hasVariants)
<div class="mb-4">
    <strong>Chọn phân loại:</strong>

    <div class="d-flex flex-wrap gap-2 mt-2">
        @foreach($product->variants as $variant)
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

<div class="wishlist-inline btn-wishlist"
     data-product-id="{{ $product->id }}">
    <i class="bi {{ $isFavorited ? 'bi-heart-fill text-danger' : 'bi-heart text-danger' }}"></i>
    <span>
        {{ $isFavorited ? 'Đã thích' : 'Yêu thích' }}
        (<span id="wishlist-count">{{ $displayFavoriteCount }}</span>)
    </span>
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

</div>
@endsection

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', () => {

    /* =====================
       WISHLIST
    ===================== */
    document.querySelectorAll('.btn-wishlist').forEach(btn => {
        btn.addEventListener('click', function(e){
            e.preventDefault();

            const productId = this.dataset.productId;

            fetch("{{ route('wishlist.toggle') }}", {
    method: "POST",
    headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        "Accept": "application/json",
        "X-CSRF-TOKEN": "{{ csrf_token() }}"
    },
    body: new URLSearchParams({
        product_id: productId
    })
})
            .then(res => res.json())
            .then(data => {
                if(!data.success){
                    showCenterNotify(data.message || 'Vui lòng đăng nhập', 'error');
                    return;
                }

                // ===== Update tất cả nút wishlist trên trang =====
                document.querySelectorAll('.btn-wishlist').forEach(el => {

                    const icon = el.querySelector('i');

                    // animation nếu là nút tròn
                    el.classList.add('animate');
                    setTimeout(()=>el.classList.remove('animate'), 200);

                    if(data.favorited){
                        icon.classList.remove('bi-heart');
                        icon.classList.add('bi-heart-fill','text-danger');
                        el.classList.add('active');
                    }else{
                        icon.classList.remove('bi-heart-fill');
                        icon.classList.add('bi-heart','text-danger');
                        el.classList.remove('active');
                    }
                });

                // ===== Update số lượt thích =====
                const countEl = document.getElementById('wishlist-count');
                if(countEl){
                    countEl.innerText = data.count;
                }
            });
        });
    });


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
</script>

@endpush
