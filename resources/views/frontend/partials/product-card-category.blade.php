@php
// Đảm bảo luôn có collection
$variants = $product->variants ?? collect();

// Variant còn hàng đầu tiên
$addVariant = $variants->first(fn ($v) => $v->stock_quantity > 0);

// Variant giá thấp nhất
$priceVariant = $variants
    ->sortBy(fn ($v) => $v->final_price ?? $v->price)
    ->first();

// Variant có khuyến mãi
$saleVariant = $variants->first(fn ($v) => $v->is_on_sale);

// Hết hàng toàn bộ
$outOfStock = !$addVariant;

// Ảnh chính
$imageUrl = $product->mainImage
    ? asset('storage/' . $product->mainImage->image_path)
    : asset('images/no-image.png');

// Wishlist
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

    {{-- ❤️ WISHLIST --}}
    <button type="button"
            class="wishlist-btn btn-wishlist"
            data-product-id="{{ $product->id }}">
        <i class="bi {{ $isFavorited ? 'bi-heart-fill text-danger' : 'bi-heart' }}"></i>
    </button>

    <img
        src="{{ $imageUrl }}"
        alt="{{ $product->name }}"
        loading="lazy"
    >

    <div class="category-overlay">

        {{-- VIEW --}}
        <button
            type="button"
            class="category-icon left js-go-detail">
            <i class="bi bi-eye"></i>
        </button>

        {{-- BUY NOW --}}
        <button
            type="button"
            class="category-buy btn-buy-now"
            data-variant-id="{{ $addVariant?->id }}"
            {{ $outOfStock ? 'disabled' : '' }}>
            <i class="bi bi-lightning-charge-fill"></i>
            Mua ngay
        </button>

        {{-- ADD TO CART --}}
        <button
            type="button"
            class="category-icon right btn-add-to-cart"
            data-variant-id="{{ $addVariant?->id }}"
            {{ $outOfStock ? 'disabled' : '' }}>
            <i class="bi bi-cart-plus"></i>
        </button>

    </div>
</div>

<div class="category-info">

    <div class="category-title js-go-detail">
        {{ \Illuminate\Support\Str::limit($product->name, 50) }}
    </div>

    <div class="category-meta">

    {{-- Hiển thị sao trung bình --}}
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

<style>
    .rating-stars i{
    font-size:14px;
    margin-right:1px;
}
/* Nút wishlist */
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
    transition:all .2s ease;
}

.wishlist-btn i{
    font-size:17px;
    color:#555;
}

.wishlist-btn:hover{
    transform:scale(1.1);
}
</style>
