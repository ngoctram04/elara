<div class="category-card h-100 js-category-card"
     data-href="{{ route('products.show', $product->slug) }}">

    {{-- IMAGE --}}
    <div class="category-image">

        @if($product->is_flash_sale)
            <span class="category-badge">
                -{{ $product->flash_discount_percent }}%
            </span>
        @endif

        <img
            src="{{ asset('storage/'.$product->mainImage->image_path) }}"
            alt="{{ $product->name }}"
            loading="lazy"
        >

        {{-- OVERLAY --}}
        <div class="category-overlay">

            {{-- 👁 XEM NHANH --}}
            <button
                type="button"
                class="category-icon left js-go-detail"
                title="Xem nhanh">
                <i class="bi bi-eye"></i>
            </button>

            {{-- ⚡ MUA NGAY --}}
            <span class="category-buy js-go-detail">
                <i class="bi bi-lightning-charge-fill"></i>
                Mua ngay
            </span>

            {{-- 🛒 ADD TO CART --}}
            <button
                type="button"
                class="category-icon right btn-add-to-cart"
                data-product-id="{{ $product->id }}"
                title="Thêm vào giỏ"
                onclick="event.stopPropagation()">
                <i class="bi bi-cart-plus"></i>
            </button>

        </div>
    </div>

    {{-- INFO --}}
    <div class="category-info">

        {{-- CLICK TÊN → CHI TIẾT --}}
        <div class="category-title js-go-detail">
            {{ \Illuminate\Support\Str::limit($product->name, 50) }}
        </div>

        <div class="category-meta">
            <span>⭐ 5.0</span>
            <span>Đã bán{{ $product->total_sold }}</span>
        </div>

        <div class="category-price">
            {{ number_format($product->min_price) }}đ
        </div>

    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Click toàn bộ category card → chi tiết
    document.querySelectorAll('.js-category-card').forEach(card => {
        card.addEventListener('click', function () {
            window.location.href = this.dataset.href;
        });
    });

    // Click icon / title / mua ngay → chi tiết
    document.querySelectorAll('.js-go-detail').forEach(el => {
        el.addEventListener('click', function (e) {
            e.stopPropagation();
            window.location.href = this.closest('.js-category-card').dataset.href;
        });
    });

});
</script>
