@php
$variants = $product->variants ?? collect();

$addVariant = $variants->first(fn ($v) => $v->stock_quantity > 0);

$priceVariant = $variants
    ->sortBy(fn ($v) => $v->final_price ?? $v->price)
    ->first();

$saleVariant = $variants->first(fn ($v) => $v->is_on_sale);

$outOfStock = !$addVariant;

$isFavorited = in_array($product->id, $favorites ?? []);

$finalPrice = $priceVariant?->final_price ?? $priceVariant?->price;
$originalPrice = ($priceVariant && $priceVariant->is_on_sale) ? $priceVariant->price : null;
$hasSalePrice = $originalPrice && $originalPrice > $finalPrice;
@endphp

@if ($priceVariant)

<div class="product-item">

    <div class="fs-card js-card"
         data-href="{{ route('products.show', $product->slug) }}">

        <div class="fs-image position-relative">

            {{-- SALE --}}
            @if ($saleVariant)
                <span class="fs-badge">
                    {{ $saleVariant->discount_label }}
                </span>
            @endif

            {{-- HẾT HÀNG --}}
            @if ($outOfStock)
                <span class="fs-badge bg-secondary">Hết hàng</span>
            @endif

            {{-- ❤️ --}}
            <button type="button"
                    class="wishlist-btn btn-wishlist"
                    data-product-id="{{ $product->id }}">
                <i class="bi {{ $isFavorited ? 'bi-heart-fill text-danger' : 'bi-heart' }}"></i>
            </button>

            <img src="{{ $product->main_image_url }}"
                 alt="{{ $product->name }}"
                 loading="lazy">

            <div class="fs-overlay">

                <span class="fs-icon fs-left js-go-detail">
                    <i class="bi bi-eye"></i>
                </span>

                {{-- BUY --}}
                <button
                    type="button"
                    class="fs-buy btn-buy-now"
                    data-variant-id="{{ $addVariant?->id }}"
                    data-out-stock="{{ $outOfStock ? 1 : 0 }}">
                    <i class="bi bi-lightning-charge-fill"></i>
                    Mua ngay
                </button>

                {{-- CART --}}
                <button
                    type="button"
                    class="fs-icon fs-right btn-add-to-cart"
                    data-variant-id="{{ $addVariant?->id }}"
                    data-out-stock="{{ $outOfStock ? 1 : 0 }}">
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
                @if (($product->reviews_count ?? 0) > 0)
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
                @else
                    <span class="no-rating">Chưa đánh giá</span>
                @endif

                <span>Đã bán {{ $product->total_sold }}</span>
            </div>

            <div class="fs-price">
                @if ($hasSalePrice)
                    <span class="new">
                        {{ number_format($finalPrice, 0, ',', '.') }}đ
                    </span>

                    <span class="old">
                        {{ number_format($originalPrice, 0, ',', '.') }}đ
                    </span>
                @else
                    <span class="new">
                        {{ number_format($finalPrice, 0, ',', '.') }}đ
                    </span>
                @endif
            </div>

        </div>
    </div>

</div>

@endif

<style>
.product-item {
    width: 250px;
    flex-shrink: 0;
}

.swiper-slide {
    width: auto !important;
}

.fs-card {
    border-radius: 16px;
    overflow: hidden;
    background: #fff;
    transition: 0.3s;
}

.fs-card:hover {
    transform: translateY(-6px);
}

.fs-image img {
    width: 100%;
    height: 220px;
    object-fit: cover;
}

.rating-stars i {
    font-size: 14px;
    margin-right: 1px;
}

.no-rating{
    font-size:13px;
    color:#6b7280;
    font-weight:500;
}

.wishlist-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #fff;
    border: none;
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
    opacity: 0.6;
    cursor: not-allowed;
}

.fs-image:has([data-out-stock="1"]) img {
    filter: grayscale(30%);
}

.fs-price{
    display:flex;
    align-items:center;
    gap:8px;
    flex-wrap:wrap;
    margin-top:6px;
}

.fs-price .new{
    font-size:18px;
    font-weight:700;
    color:#dc3545;
    line-height:1;
}

.fs-price .old{
    font-size:14px;
    color:#9ca3af;
    text-decoration:line-through;
    line-height:1;
}
</style>

<script>
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
    `;

    document.body.appendChild(toast);

    setTimeout(() => toast.remove(), 2000);
}

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

    const card = e.target.closest('.js-card');
    if(card && !e.target.closest('button')){
        window.location.href = card.dataset.href;
    }
});
</script>