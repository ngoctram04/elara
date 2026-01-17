@php
    /**
     * 🔥 BIẾN THỂ ĐẠI DIỆN CHO CARD
     * - LẤY BIẾN THỂ CÓ final_price NHỎ NHẤT
     * - final_price đã bao gồm khuyến mãi (nếu có)
     */
    $displayVariant = $product->variants
        ->sortBy(fn ($v) => $v->final_price)
        ->first();
@endphp

@if($displayVariant)
<div class="category-card h-100 js-category-card"
     data-href="{{ route('products.show', $product->slug) }}">

    {{-- ================= IMAGE ================= --}}
    <div class="category-image">

        {{-- BADGE: CHỈ HIỆN KHI BIẾN THỂ RẺ NHẤT ĐANG SALE --}}
        @if($displayVariant->is_on_sale)
            <span class="category-badge">
                {{ $displayVariant->discount_label }}
            </span>
        @endif

        {{-- ẢNH ĐẠI DIỆN PRODUCT --}}
        <img
            src="{{ asset('storage/'.$product->mainImage->image_path) }}"
            alt="{{ $product->name }}"
            loading="lazy"
        >

        {{-- OVERLAY --}}
        <div class="category-overlay">

            {{-- ICON VIEW --}}
            <button
                type="button"
                class="category-icon left js-go-detail"
                title="Xem nhanh">
                <i class="bi bi-eye"></i>
            </button>

            {{-- BUY --}}
            <span class="category-buy js-go-detail">
                <i class="bi bi-lightning-charge-fill"></i>
                Mua ngay
            </span>

            {{-- ICON CART (THEO BIẾN THỂ ĐẠI DIỆN) --}}
            <button
                type="button"
                class="category-icon right btn-add-to-cart"
                data-variant-id="{{ $displayVariant->id }}"
                title="Thêm vào giỏ"
                onclick="event.stopPropagation()">
                <i class="bi bi-cart-plus"></i>
            </button>

        </div>
    </div>

    {{-- ================= INFO ================= --}}
    <div class="category-info">

        <div class="category-title js-go-detail">
            {{ \Illuminate\Support\Str::limit($product->name, 50) }}
        </div>

        <div class="category-meta">
            <span>⭐ ⭐ ⭐ ⭐ ⭐ (5.0)</span>
            <span>Đã bán {{ $product->total_sold }}</span>
        </div>

        {{-- ================= PRICE ================= --}}
        <div class="category-price">

            {{-- GIÁ GỐC CHỈ HIỆN KHI BIẾN THỂ RẺ NHẤT ĐANG SALE --}}
            @if($displayVariant->is_on_sale)
                <span class="old">
                    {{ number_format($displayVariant->price, 0, ',', '.') }}đ
                </span>
            @endif

            {{-- GIÁ CUỐI CÙNG (LUÔN LÀ GIÁ NHỎ NHẤT) --}}
            <span class="new">
                {{ number_format($displayVariant->final_price, 0, ',', '.') }}đ
            </span>

        </div>

    </div>
</div>
@endif
<script>
document.addEventListener('click', function (e) {

    // CLICK TOÀN CARD
    const card = e.target.closest('.js-category-card, .js-card');
    if (card && !e.target.closest('.btn-add-to-cart')) {
        window.location.href = card.dataset.href;
        return;
    }

    // CLICK ICON 👁 / MUA NGAY
    const goDetail = e.target.closest('.js-go-detail');
    if (goDetail) {
        e.stopPropagation();
        const card = goDetail.closest('.js-category-card, .js-card');
        if (card) {
            window.location.href = card.dataset.href;
        }
    }

});
</script>
