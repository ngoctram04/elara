@php
$variants = $product->variants ?? collect();

$addVariant = $variants->first(fn ($v) => $v->stock_quantity > 0);

$priceVariant = $variants
    ->sortBy(fn ($v) => $v->final_price ?? $v->price)
    ->first();

$saleVariant = $variants->first(fn ($v) => $v->is_on_sale);

$outOfStock = !$addVariant;

$imageUrl = $product->mainImage
    ? asset('storage/' . $product->mainImage->image_path)
    : asset('images/no-image.png');

$isFavorited = in_array($product->id, $favorites ?? []);
@endphp

@if ($variants->isNotEmpty() && $priceVariant)

<div class="category-card h-100 js-category-card"
     data-href="{{ route('products.show', $product->slug) }}">

<div class="category-image position-relative">

    {{-- SALE --}}
    @if ($saleVariant)
        <span class="category-badge">
            {{ $saleVariant->discount_label }}
        </span>
    @endif

    {{-- HẾT HÀNG --}}
    @if ($outOfStock)
        <span class="category-badge bg-secondary">
            Hết hàng
        </span>
    @endif

    {{-- ❤️ --}}
    <button type="button"
            class="wishlist-btn btn-wishlist"
            data-product-id="{{ $product->id }}">
        <i class="bi {{ $isFavorited ? 'bi-heart-fill text-danger' : 'bi-heart' }}"></i>
    </button>

    <img src="{{ $imageUrl }}"
         alt="{{ $product->name }}"
         loading="lazy">

    <div class="category-overlay">

        {{-- VIEW --}}
        <button type="button"
                class="category-icon left js-go-detail">
            <i class="bi bi-eye"></i>
        </button>

        {{-- BUY --}}
        <button
            type="button"
            class="category-buy btn-buy-now"
            data-variant-id="{{ $addVariant?->id }}"
            data-out-stock="{{ $outOfStock ? 1 : 0 }}">
            <i class="bi bi-lightning-charge-fill"></i>
            Mua ngay
        </button>

        {{-- CART --}}
        <button
            type="button"
            class="category-icon right btn-add-to-cart"
            data-variant-id="{{ $addVariant?->id }}"
            data-out-stock="{{ $outOfStock ? 1 : 0 }}">
            <i class="bi bi-cart-plus"></i>
        </button>

    </div>
</div>

<div class="category-info">

    <div class="category-title js-go-detail">
        {{ \Illuminate\Support\Str::limit($product->name, 50) }}
    </div>

    <div class="category-meta">

    @if ($product->reviews_count > 0)
        @php
            $avg = round($product->reviews_avg_rating, 1);
            $full = floor($avg);
            $half = ($avg - $full) >= 0.5;
        @endphp

        <span class="rating-stars">
            @for ($i = 1; $i <= 5; $i++)
                @if ($i <= $full)
                    <i class="bi bi-star-fill text-warning"></i>
                @elseif ($i == $full + 1 && $half)
                    <i class="bi bi-star-half text-warning"></i>
                @else
                    <i class="bi bi-star text-warning"></i>
                @endif
            @endfor

            <small>({{ $avg }})</small>
        </span>
    @endif

    <span>Đã bán {{ $product->total_sold }}</span>
</div>

    <div class="category-price">
        @if ($priceVariant->is_on_sale && $priceVariant->original_price)
            <span class="old">
                {{ number_format($priceVariant->original_price, 0, ',', '.') }}đ
            </span>
        @endif

        <span class="new">
            {{ number_format($priceVariant->final_price ?? $priceVariant->price, 0, ',', '.') }}đ
        </span>
    </div>

</div>

</div>
@endif


{{-- ================= STYLE ================= --}}
<style>
.rating-stars i{
    font-size:14px;
    margin-right:1px;
}

/* wishlist */
.wishlist-btn{
    position:absolute;
    top:10px;
    right:10px;
    width:34px;
    height:34px;
    border:none;
    border-radius:50%;
    background:#fff;
    box-shadow:0 3px 8px rgba(0,0,0,0.15);
    display:flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    z-index:20;
    transition:.2s;
}

.wishlist-btn:hover{
    transform:scale(1.1);
}

/* ❌ hết hàng */
[data-out-stock="1"]{
    opacity:0.6;
    cursor:not-allowed;
}

/* ảnh xám */
.category-image:has([data-out-stock="1"]) img{
    filter: grayscale(40%);
}
</style>


{{-- ================= SCRIPT ================= --}}
<script>
// toast
function showToast(message){
    const toast = document.createElement('div');
    toast.innerText = message;

    toast.style = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #dc3545;
        color: #fff;
        padding: 10px 16px;
        border-radius: 8px;
        z-index: 9999;
        font-size: 14px;
    `;

    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2000);
}

// click delegation (chuẩn cho swiper/ajax)
document.addEventListener('click', function(e){

    const btn = e.target.closest('.btn-add-to-cart, .btn-buy-now');
    if(btn){
        if(btn.dataset.outStock == "1"){
            e.preventDefault();
            e.stopPropagation();
            showToast('Sản phẩm đã hết hàng!');
            return;
        }
    }

    const card = e.target.closest('.js-category-card');
    if(card && !e.target.closest('button')){
        window.location.href = card.dataset.href;
    }

});
</script>