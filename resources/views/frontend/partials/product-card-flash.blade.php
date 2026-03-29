@php
    $addVariant = $product->variants->first(fn ($v) => $v->stock_quantity > 0);

    $priceVariant = $product->variants
        ->sortBy(fn ($v) => $v->final_price ?? $v->price)
        ->first();

    $saleVariant = $product->variants->first(fn ($v) => $v->is_on_sale);

    $outOfStock = !$addVariant;

    $isFavorited = in_array($product->id, $favorites ?? []);
@endphp

@if ($priceVariant)
<div class="product-item">
    <div class="fs-card js-card {{ $outOfStock ? 'is-out-of-stock' : '' }}"
         data-href="{{ route('products.show', $product->slug) }}">

        <div class="fs-image position-relative">

            @if ($saleVariant)
                <span class="fs-badge">
                    {{ $saleVariant->discount_label }}
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

                <button type="button"
                        class="fs-buy btn-buy-now"
                        data-variant-id="{{ $addVariant?->id }}"
                        data-out-stock="{{ $outOfStock ? 1 : 0 }}">
                    <i class="bi bi-lightning-charge-fill"></i>
                    Mua ngay
                </button>

                <button type="button"
                        class="fs-icon fs-right btn-add-to-cart"
                        data-variant-id="{{ $addVariant?->id }}"
                        data-out-stock="{{ $outOfStock ? 1 : 0 }}"
                        aria-label="Thêm vào giỏ">
                    <i class="bi bi-cart-plus"></i>
                </button>
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

                <span class="fs-sold">Đã bán {{ $product->total_sold }}</span>
            </div>

            <div class="fs-price">
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
</div>
@endif