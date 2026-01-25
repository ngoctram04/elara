@php
    /**
     * ✅ BIẾN THỂ ADD TO CART
     * - LUÔN LẤY BIẾN THỂ ĐẦU TIÊN (THEO BACKEND ORDER)
     */
    $addVariant = $product->variants->first();

    /**
     * ✅ BIẾN THỂ HIỂN THỊ GIÁ
     * - LẤY GIÁ THẤP NHẤT
     * - ƯU TIÊN final_price NẾU CÓ KHUYẾN MÃI
     */
    $priceVariant = $product->variants
        ->sortBy(fn ($v) => $v->final_price ?? $v->price)
        ->first();

    /**
     * ✅ CHỈ CẦN 1 BIẾN THỂ SALE → HIỆN BADGE CHO SẢN PHẨM
     */
    $saleVariant = $product->variants->first(fn ($v) => $v->is_on_sale);
@endphp

@if ($addVariant && $priceVariant)
<div class="category-card h-100 js-category-card"
     data-href="{{ route('products.show', $product->slug) }}">

    {{-- ================= IMAGE ================= --}}
    <div class="category-image">

        {{-- 🔥 BADGE SALE (THEO SẢN PHẨM) --}}
        @if ($saleVariant)
            <span class="category-badge">
                {{ $saleVariant->discount_label }}
            </span>
        @endif

        {{-- IMAGE --}}
        <img
            src="{{ asset('storage/' . $product->mainImage->image_path) }}"
            alt="{{ $product->name }}"
            loading="lazy"
        >

        {{-- OVERLAY --}}
        <div class="category-overlay">

            {{-- 👁 VIEW --}}
            <button
                type="button"
                class="category-icon left js-go-detail"
                title="Xem nhanh">
                <i class="bi bi-eye"></i>
            </button>

            {{-- ⚡ BUY --}}
            <span class="category-buy js-go-detail">
                <i class="bi bi-lightning-charge-fill"></i>
                Mua ngay
            </span>

            {{-- 🛒 ADD TO CART – BIẾN THỂ ĐẦU TIÊN --}}
            <button
                type="button"
                class="category-icon right btn-add-to-cart"
                data-variant-id="{{ $addVariant->id }}"
                title="Thêm vào giỏ">
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

            {{-- GIÁ GỐC – CHỈ HIỆN KHI CÓ SALE --}}
            @if ($priceVariant->is_on_sale && $priceVariant->original_price)
                <span class="old">
                    {{ number_format($priceVariant->original_price, 0, ',', '.') }}đ
                </span>
            @endif

            {{-- GIÁ HIỂN THỊ = GIÁ THẤP NHẤT --}}
            <span class="new">
                {{ number_format($priceVariant->final_price ?? $priceVariant->price, 0, ',', '.') }}đ
            </span>

        </div>

    </div>
</div>
@endif
<script>
document.addEventListener('click', function (e) {

    // ADD TO CART
    const addBtn = e.target.closest('.btn-add-to-cart');
    if (addBtn) {
        e.preventDefault();
        e.stopPropagation();

        fetch("{{ route('cart.add') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: new URLSearchParams({
                variant_id: addBtn.dataset.variantId,
                quantity: 1
            })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                showCenterNotify(data.message || 'Không thể thêm sản phẩm', 'error');
                return;
            }

            addBtn.classList.add('text-success');
            setTimeout(() => addBtn.classList.remove('text-success'), 600);
            showCenterNotify('Đã thêm sản phẩm vào giỏ hàng');
        })
        .catch(() => {
            showCenterNotify('Lỗi kết nối máy chủ', 'error');
        });

        return;
    }

    // VIEW / BUY
    const goDetail = e.target.closest('.js-go-detail');
    if (goDetail) {
        const card = goDetail.closest('.js-category-card, .js-card');
        if (card) window.location.href = card.dataset.href;
        return;
    }

    // CLICK CARD
    const card = e.target.closest('.js-category-card, .js-card');
    if (card) {
        window.location.href = card.dataset.href;
    }

});
</script>
