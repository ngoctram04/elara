@php
$variants = $product->variants ?? collect();

$addVariant = $variants->first(fn ($v) => $v->stock_quantity > 0);
$outOfStock = !$addVariant;

$imageUrl = $product->mainImage
    ? asset('storage/' . $product->mainImage->image_path)
    : asset('images/no-image.png');

$isFavorited = in_array($product->id, $favorites ?? []);

$saleVariant = $variants->first(fn ($v) => $v->is_on_sale);

/*
|--------------------------------------------------------------------------
| TÍNH GIÁ KIỂU SHOPEE
|--------------------------------------------------------------------------
| - final_prices: giá bán thực tế (sau giảm nếu có)
| - original_prices: giá gốc
| - min/max để hiện khoảng giá
*/
$finalPrices = $variants
    ->map(fn ($v) => (float) ($v->final_price ?? $v->price ?? 0))
    ->filter(fn ($price) => $price > 0)
    ->sort()
    ->values();

$originalPrices = $variants
    ->map(fn ($v) => (float) ($v->price ?? 0))
    ->filter(fn ($price) => $price > 0)
    ->sort()
    ->values();

$minFinalPrice = $finalPrices->first();
$maxFinalPrice = $finalPrices->last();

$minOriginalPrice = $originalPrices->first();
$maxOriginalPrice = $originalPrices->last();

$hasPriceRange = $minFinalPrice && $maxFinalPrice && $minFinalPrice != $maxFinalPrice;
$hasSalePrice = $saleVariant && $minOriginalPrice > $minFinalPrice;

/*
|--------------------------------------------------------------------------
| FORMAT GIÁ
|--------------------------------------------------------------------------
*/
$finalPriceText = $hasPriceRange
    ? number_format($minFinalPrice, 0, ',', '.') . 'đ - ' . number_format($maxFinalPrice, 0, ',', '.') . 'đ'
    : number_format($minFinalPrice, 0, ',', '.') . 'đ';

$originalPriceText = '';
if ($hasSalePrice) {
    $originalPriceText = ($minOriginalPrice != $maxOriginalPrice)
        ? number_format($minOriginalPrice, 0, ',', '.') . 'đ - ' . number_format($maxOriginalPrice, 0, ',', '.') . 'đ'
        : number_format($minOriginalPrice, 0, ',', '.') . 'đ';
}
@endphp

@if ($variants->isNotEmpty() && $minFinalPrice)
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
            @if (($product->reviews_count ?? 0) > 0)
    @php
        $avg = round($product->reviews_avg_rating ?? 0, 1);
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
            @else
                <span class="no-rating">Chưa đánh giá</span>
            @endif

            <span>Đã bán {{ $product->total_sold }}</span>
        </div>

        <div class="category-price">
            <span class="new">{{ $finalPriceText }}</span>

            @if ($hasSalePrice)
                <span class="old">{{ $originalPriceText }}</span>
            @endif
        </div>

    </div>
</div>
@endif

<style>
.rating-stars i{
    font-size:14px;
    margin-right:1px;
}

.no-rating{
    font-size:13px;
    color:#6b7280;
    font-weight:500;
}

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

[data-out-stock="1"]{
    opacity:0.6;
    cursor:not-allowed;
}

.category-image:has([data-out-stock="1"]) img{
    filter: grayscale(40%);
}

.category-price{
    display:flex;
    flex-direction:column;
    align-items:flex-start;
    gap:4px;
    margin-top:6px;
}

.category-price .new{
    font-size:18px;
    font-weight:700;
    color:#ee4d2d;
    line-height:1.3;
}

.category-price .old{
    font-size:13px;
    color:#9ca3af;
    text-decoration:line-through;
    line-height:1.2;
}
</style>
