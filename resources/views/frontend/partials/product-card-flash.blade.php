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

<div class="col-lg-3 col-md-4 col-sm-6 mb-4">

    <div class="fs-card js-card"
         data-href="{{ route('products.show', $product->slug) }}"
         style="cursor:pointer;">

        <div class="fs-image position-relative">

            {{-- SALE --}}
            @if ($saleVariant)
                <span class="fs-badge">
                    {{ $saleVariant->discount_label }}
                </span>
            @endif

            {{-- OUT OF STOCK --}}
            @if ($outOfStock)
                <span class="fs-badge bg-secondary">Hết hàng</span>
            @endif

            {{-- Wishlist --}}
            <button type="button"
                    class="wishlist-btn btn-wishlist"
                    data-product-id="{{ $product->id }}">
                <i class="bi {{ $isFavorited ? 'bi-heart-fill text-danger' : 'bi-heart' }}"></i>
            </button>

            {{-- IMAGE (click được) --}}
            <img src="{{ $product->main_image_url }}"
                 alt="{{ $product->name }}"
                 loading="lazy"
                 class="js-go-detail">

            {{-- OVERLAY --}}
            <div class="fs-overlay">

                {{-- XEM CHI TIẾT --}}
                <span class="fs-icon fs-left js-go-detail">
                    <i class="bi bi-eye"></i>
                </span>

                {{-- BUY NOW --}}
                <button type="button"
                        class="fs-buy btn-buy-now"
                        data-variant-id="{{ $addVariant?->id }}"
                        {{ $outOfStock ? 'disabled' : '' }}>
                    <i class="bi bi-lightning-charge-fill"></i>
                    Mua ngay
                </button>

                {{-- ADD CART --}}
                <button type="button"
                        class="fs-icon fs-right btn-add-to-cart"
                        data-variant-id="{{ $addVariant?->id }}"
                        {{ $outOfStock ? 'disabled' : '' }}>
                    <i class="bi bi-cart-plus"></i>
                </button>

            </div>
        </div>

        <div class="fs-info">

            <div class="fs-brand">
                {{ $product->brand->name ?? 'Thương hiệu' }}
            </div>

            {{-- TITLE CLICK --}}
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

                <span>Đã bán {{ $product->total_sold }}</span>
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