
@php
    $addVariant = $product->variants->first();

    $priceVariant = $product->variants
        ->sortBy(fn ($v) => $v->final_price ?? $v->price)
        ->first();

    // 🔥 CHỈ CẦN 1 BIẾN THỂ SALE → HIỆN BADGE
    $saleVariant = $product->variants->first(fn ($v) => $v->is_on_sale);
@endphp

@if ($addVariant && $priceVariant)
<div class="col-lg-3 col-md-4 col-sm-6 mb-4">
    <div class="fs-card js-card"
         data-href="{{ route('products.show', $product->slug) }}">

        <div class="fs-image">

            {{-- 🔥 BADGE SALE THEO SẢN PHẨM --}}
            @if ($saleVariant)
                <span class="fs-badge">
                    {{ $saleVariant->discount_label }}
                </span>
            @endif

            <img
                src="{{ $product->main_image_url }}"
                alt="{{ $product->name }}"
                loading="lazy"
            >

            <div class="fs-overlay">

                <span class="fs-icon fs-left js-go-detail">
                    <i class="bi bi-eye"></i>
                </span>

                <span class="fs-buy js-go-detail">
                    <i class="bi bi-lightning-charge-fill"></i>
                    Mua ngay
                </span>

                {{-- 🛒 ADD TO CART – BIẾN THỂ ĐẦU TIÊN --}}
                <button
                    type="button"
                    class="fs-icon fs-right btn-add-to-cart"
                    data-variant-id="{{ $addVariant->id }}">
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
                <span>⭐⭐⭐⭐⭐ (5.0)</span>
                <span>Đã bán {{ $product->total_sold }}</span>
            </div>

            {{-- 🔥 GIÁ THẤP NHẤT (CÓ/KO KM ĐỀU OK) --}}
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

{{-- ================= JS ================= --}}
<script>
document.addEventListener('click', function (e) {

    /* ========= ADD TO CART (ƯU TIÊN CAO NHẤT) ========= */
    const addBtn = e.target.closest('.btn-add-to-cart');
    if (addBtn) {
        e.preventDefault();
        e.stopImmediatePropagation();

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

            // ✔ hiệu ứng icon
            addBtn.classList.add('text-success');
            setTimeout(() => addBtn.classList.remove('text-success'), 600);

            // ✔ thông báo giữa màn hình
            showCenterNotify('Đã thêm sản phẩm vào giỏ hàng');
        })
        .catch(() => {
            showCenterNotify('Lỗi kết nối máy chủ', 'error');
        });

        return;
    }

    /* ========= VIEW / BUY ========= */
    const goDetail = e.target.closest('.js-go-detail');
    if (goDetail) {
        e.stopImmediatePropagation();
        const card = goDetail.closest('.js-card');
        if (card) {
            window.location.href = card.dataset.href;
        }
        return;
    }

    /* ========= CLICK CARD ========= */
    const card = e.target.closest('.js-card');
    if (card) {
        window.location.href = card.dataset.href;
    }

});
</script>
