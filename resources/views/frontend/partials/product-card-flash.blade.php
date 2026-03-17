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

            {{-- IMAGE --}}
            <img src="{{ $product->main_image_url }}"
                 alt="{{ $product->name }}"
                 loading="lazy"
                 class="js-go-detail">

            {{-- OVERLAY --}}
            <div class="fs-overlay">

                <span class="fs-icon fs-left js-go-detail">
                    <i class="bi bi-eye"></i>
                </span>

                {{-- BUY --}}
                <button type="button"
                        class="fs-buy btn-buy-now"
                        data-variant-id="{{ $addVariant?->id }}"
                        data-out-stock="{{ $outOfStock ? 1 : 0 }}">
                    <i class="bi bi-lightning-charge-fill"></i>
                    Mua ngay
                </button>

                {{-- CART --}}
                <button type="button"
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


{{-- ================= STYLE ================= --}}
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
    position: relative;
}

.fs-card:hover {
    transform: translateY(-6px);
}

.fs-image img {
    width: 100%;
    height: 220px;
    object-fit: cover;
}

/* overlay */
.fs-overlay {
    position: absolute;
    inset: 0;
    z-index: 10;
    background: rgba(0,0,0,0.2);
    opacity: 0;
    transition: 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

.fs-card:hover .fs-overlay {
    opacity: 1;
}

/* button */
.fs-icon {
    width: 36px;
    height: 36px;
    background: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.fs-buy {
    background: #1677ff;
    color: #fff;
    border: none;
    padding: 8px 14px;
    border-radius: 20px;
    font-size: 13px;
}

/* wishlist */
.wishlist-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #fff;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 30;
    box-shadow: 0 4px 12px rgba(0,0,0,.15);
}

/* rating */
.rating-stars i {
    font-size: 14px;
}

/* ❌ HẾT HÀNG */
[data-out-stock="1"]{
    opacity: 0.6;
    cursor: not-allowed;
}

/* blur ảnh */
.fs-image:has([data-out-stock="1"]) img {
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
    `;

    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2000);
}

// 🔥 dùng delegation để không bị lỗi khi load ajax/swiper
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

    // click card → chuyển trang
    const card = e.target.closest('.js-card');
    if(card && !e.target.closest('button')){
        window.location.href = card.dataset.href;
    }

});
</script>