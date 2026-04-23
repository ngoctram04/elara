@php
    $variants = $product->variants ?? collect();

    $addVariant = $variants->first(fn ($v) => (int) $v->stock_quantity > 0);
    $outOfStock = !$addVariant;
    $isFavorited = in_array($product->id, $favorites ?? []);

    $pricedVariants = $variants
        ->filter(fn ($v) => (float) ($v->price ?? 0) > 0)
        ->values();

    $finalPrices = $pricedVariants
        ->map(fn ($v) => (float) ($v->final_price ?? $v->price ?? 0))
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
<div class="product-item">
    <div class="fs-card js-card {{ $outOfStock ? 'is-out-of-stock' : '' }}"
         data-href="{{ route('products.show', $product->slug) }}">

        <div class="fs-image position-relative">

            @if ($saleBadgeText)
                <span class="fs-badge">
                    {{ $saleBadgeText }}
                </span>
            @endif

            @if ($outOfStock)
                <span class="fs-badge fs-badge-stockout">Hết hàng</span>
            @endif

            <button type="button"
                    class="wishlist-btn btn-wishlist"
                    data-product-id="{{ $product->id }}"
                    aria-label="Yêu thích">
                <i class="bi {{ $isFavorited ? 'bi-heart-fill text-danger' : 'bi-heart' }}"></i>
            </button>

            <img src="{{ $product->main_image_url }}"
                 alt="{{ $product->name }}"
                 loading="lazy"
                 class="fs-main-image js-go-detail">

            <div class="fs-overlay">
                <span class="fs-icon fs-left js-go-detail" aria-label="Xem chi tiết">
                    <i class="bi bi-eye"></i>
                </span>

                @unless($outOfStock)
                    <button type="button"
                            class="fs-buy btn-buy-now"
                            data-variant-id="{{ $addVariant?->id }}"
                            data-out-stock="0">
                        <i class="bi bi-lightning-charge-fill"></i>
                        Mua ngay
                    </button>

                    <button type="button"
                            class="fs-icon fs-right btn-add-to-cart"
                            data-variant-id="{{ $addVariant?->id }}"
                            data-out-stock="0"
                            aria-label="Thêm vào giỏ">
                        <i class="bi bi-cart-plus"></i>
                    </button>
                @endunless
            </div>
        </div>

        <div class="fs-info">
            <div class="fs-brand">
                {{ $product->brand->name ?? 'Thương hiệu' }}
            </div>

            <div class="fs-title js-go-detail">
                {{ \Illuminate\Support\Str::limit($product->name, 48) }}
            </div>

            <div class="fs-meta">
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
                    <span class="rating-stars rating-empty">
                        <small>Chưa có đánh giá</small>
                    </span>
                @endif

                <span class="fs-sold">Đã bán {{ $product->total_sold }}</span>
            </div>

            <div class="fs-price">
                <span class="new">
                    {{ $finalPriceText }}
                </span>

                @if ($hasSalePrice && $originalPriceText)
                    <span class="old">
                        {{ $originalPriceText }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>
@endif