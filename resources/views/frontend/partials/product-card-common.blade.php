@php
$variants = $product->variants ?? collect();

$addVariant = $variants->first(fn ($v) => (int) $v->stock_quantity > 0);
$outOfStock = !$addVariant;

$imageUrl = $product->mainImage
    ? asset('storage/' . $product->mainImage->image_path)
    : asset('images/no-image.png');

$isFavorited = in_array($product->id, $favorites ?? []);

/*
|--------------------------------------------------------------------------
| GIÁ & KHUYẾN MÃI KIỂU SHOPEE
|--------------------------------------------------------------------------
*/
$pricedVariants = $variants
    ->filter(fn ($v) => (float) ($v->price ?? 0) > 0)
    ->values();

$finalPrices = $pricedVariants
    ->map(fn ($v) => (float) ($v->final_price ?? $v->price))
    ->filter(fn ($price) => $price > 0)
    ->sort()
    ->values();

$originalPrices = $pricedVariants
    ->map(fn ($v) => (float) ($v->price ?? 0))
    ->filter(fn ($price) => $price > 0)
    ->sort()
    ->values();

$minFinalPrice = $finalPrices->first();
$maxFinalPrice = $finalPrices->last();

$minOriginalPrice = $originalPrices->first();
$maxOriginalPrice = $originalPrices->last();

$hasPriceRange = $minFinalPrice !== null && $maxFinalPrice !== null && $minFinalPrice != $maxFinalPrice;
$hasOriginalRange = $minOriginalPrice !== null && $maxOriginalPrice !== null && $minOriginalPrice != $maxOriginalPrice;

$hasSalePrice = false;
$maxDiscountPercent = 0;

foreach ($pricedVariants as $variant) {
    $price = (float) ($variant->price ?? 0);
    $final = (float) ($variant->final_price ?? $variant->price ?? 0);

    if ($price > 0 && $final > 0 && $final < $price) {
        $hasSalePrice = true;
        $discountPercent = round((($price - $final) / $price) * 100);
        $maxDiscountPercent = max($maxDiscountPercent, $discountPercent);
    }
}

$finalPriceText = '';
if ($minFinalPrice !== null) {
    $finalPriceText = $hasPriceRange
        ? number_format($minFinalPrice, 0, ',', '.') . 'đ - ' . number_format($maxFinalPrice, 0, ',', '.') . 'đ'
        : number_format($minFinalPrice, 0, ',', '.') . 'đ';
}

$originalPriceText = '';
if ($hasSalePrice && $minOriginalPrice !== null) {
    $originalPriceText = $hasOriginalRange
        ? number_format($minOriginalPrice, 0, ',', '.') . 'đ - ' . number_format($maxOriginalPrice, 0, ',', '.') . 'đ'
        : number_format($minOriginalPrice, 0, ',', '.') . 'đ';
}

$saleBadgeText = $maxDiscountPercent > 0 ? 'Giảm đến ' . $maxDiscountPercent . '%' : null;
@endphp

@if ($pricedVariants->isNotEmpty() && $minFinalPrice)
<div class="category-card h-100 js-category-card"
     data-href="{{ route('products.show', $product->slug) }}">

    <div class="category-image position-relative {{ $outOfStock ? 'out-of-stock' : '' }}">

        {{-- SALE --}}
        @if ($saleBadgeText)
            <span class="category-badge sale-badge">
                {{ $saleBadgeText }}
            </span>
        @endif

        {{-- HẾT HÀNG --}}
        @if ($outOfStock)
            <span class="category-badge stock-badge">
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

            @unless($outOfStock)
                {{-- BUY --}}
                <button
                    type="button"
                    class="category-buy btn-buy-now"
                    data-variant-id="{{ $addVariant?->id }}"
                    data-out-stock="0">
                    <i class="bi bi-lightning-charge-fill"></i>
                    Mua ngay
                </button>

                {{-- CART --}}
                <button
                    type="button"
                    class="category-icon right btn-add-to-cart"
                    data-variant-id="{{ $addVariant?->id }}"
                    data-out-stock="0">
                    <i class="bi bi-cart-plus"></i>
                </button>
            @endunless
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

            @if ($hasSalePrice && $originalPriceText)
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
    transform:scale(1.08);
}

.category-badge{
    position:absolute;
    top:10px;
    left:10px;
    z-index:15;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-height:28px;
    padding:5px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:700;
    line-height:1;
    box-shadow:0 4px 10px rgba(0,0,0,.12);
}

.sale-badge{
    background:linear-gradient(135deg, #ff7337, #ee4d2d);
    color:#fff;
}

.stock-badge{
    top:44px;
    background:#6b7280;
    color:#fff;
}

.category-image.out-of-stock img{
    filter:grayscale(40%);
    opacity:.85;
}

.category-price{
    display:flex;
    flex-direction:column;
    align-items:flex-start;
    gap:3px;
    margin-top:6px;
}

.category-price .new{
    font-size:16px;
    font-weight:700;
    color:#ee4d2d;
    line-height:1.35;
    word-break:break-word;
}

.category-price .old{
    font-size:13px;
    font-weight:400;
    color:#9ca3af;
    text-decoration:line-through;
    line-height:1.2;
    word-break:break-word;
}

.category-overlay{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;
}

.category-buy{
    white-space:nowrap;
}

.category-card{
    cursor:pointer;
    transition:.25s ease;
}

.category-card:hover{
    transform:translateY(-2px);
}

.category-image img{
    transition:.3s ease;
}

.category-card:hover .category-image img{
    transform:scale(1.03);
}
</style>
