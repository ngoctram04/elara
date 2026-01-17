<div class="col-lg-3 col-md-4 col-sm-6 mb-4">
    <div class="fs-card js-card"
         data-href="{{ route('products.show', $product->slug) }}">

        {{-- ================= IMAGE ================= --}}
        <div class="fs-image">

            {{-- BADGE --}}
            @if($product->is_flash_sale)
                <span class="fs-badge">
                    -{{ $product->flash_discount_percent }}%
                </span>
            @endif

            <img
                src="{{ $product->main_image_url }}"
                alt="{{ $product->name }}"
                loading="lazy"
            >

            {{-- OVERLAY --}}
            <div class="fs-overlay">

                {{-- 👁 XEM NHANH --}}
                <span class="fs-icon fs-left js-go-detail"
                      title="Xem chi tiết">
                    <i class="bi bi-eye"></i>
                </span>

                {{-- ⚡ MUA NGAY --}}
                <span class="fs-buy js-go-detail">
                    <i class="bi bi-lightning-charge-fill"></i>
                    Mua ngay
                </span>

                {{-- 🛒 ADD TO CART --}}
                <button
                    type="button"
                    class="fs-icon fs-right btn-add-to-cart"
                    data-product-id="{{ $product->id }}"
                    title="Thêm vào giỏ"
                    onclick="event.stopPropagation()">
                    <i class="bi bi-cart-plus"></i>
                </button>

            </div>
        </div>

        {{-- ================= INFO ================= --}}
        <div class="fs-info">

            <div class="fs-brand">
                {{ $product->brand->name ?? 'Thương hiệu' }}
            </div>

            <div class="fs-title js-go-detail">
                {{ \Illuminate\Support\Str::limit($product->name, 48) }}
            </div>

            <div class="fs-meta">
                <div class="fs-rating">
                    ⭐⭐⭐⭐⭐ <span>(5.0)</span>
                </div>
                <div class="fs-sold">
                    🔥 {{ $product->total_sold }}
                </div>
            </div>

            <div class="fs-price">
                @if($product->is_flash_sale)
                    <span class="old">
                        {{ number_format($product->flash_original_price) }}đ
                    </span>
                    <span class="new">
                        {{ number_format($product->flash_sale_price) }}đ
                    </span>
                @else
                    <span class="new">
                        {{ number_format($product->min_price) }}đ
                    </span>
                @endif
            </div>

        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Click toàn bộ card → chi tiết
    document.querySelectorAll('.js-card').forEach(card => {
        card.addEventListener('click', function () {
            window.location.href = this.dataset.href;
        });
    });

    // Click icon / title → chi tiết
    document.querySelectorAll('.js-go-detail').forEach(el => {
        el.addEventListener('click', function (e) {
            e.stopPropagation();
            window.location.href = this.closest('.js-card').dataset.href;
        });
    });

});
</script>
