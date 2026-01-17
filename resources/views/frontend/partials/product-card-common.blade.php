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
<div class="col-lg-3 col-md-4 col-sm-6 mb-4">
    <div class="fs-card js-card"
         data-href="{{ route('products.show', $product->slug) }}">

        {{-- ================= IMAGE ================= --}}
        <div class="fs-image">

            {{-- BADGE: CHỈ HIỆN KHI BIẾN THỂ RẺ NHẤT ĐANG SALE --}}
            @if($displayVariant->is_on_sale)
                <span class="fs-badge">
                    {{ $displayVariant->discount_label }}
                </span>
            @endif

            {{-- ẢNH ĐẠI DIỆN PRODUCT --}}
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

                {{-- 🛒 ADD TO CART (THEO BIẾN THỂ ĐẠI DIỆN) --}}
                <button
                    type="button"
                    class="fs-icon fs-right btn-add-to-cart"
                    data-variant-id="{{ $displayVariant->id }}"
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
                    Đã bán {{ $product->total_sold }}
                </div>
            </div>

            {{-- ================= PRICE ================= --}}
            <div class="fs-price">

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
