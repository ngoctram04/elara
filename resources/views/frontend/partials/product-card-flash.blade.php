@php
    // Variant còn hàng đầu tiên
    $addVariant = $product->variants
        ->first(fn ($v) => $v->stock_quantity > 0);

    // Giá thấp nhất
    $priceVariant = $product->variants
        ->sortBy(fn ($v) => $v->final_price ?? $v->price)
        ->first();

    // Badge sale
    $saleVariant = $product->variants->first(fn ($v) => $v->is_on_sale);

    $outOfStock = !$addVariant;
@endphp

@if ($priceVariant)
<div class="col-lg-3 col-md-4 col-sm-6 mb-4">
    <div class="fs-card js-card"
         data-href="{{ route('products.show', $product->slug) }}">

        <div class="fs-image">

            {{-- SALE --}}
            @if ($saleVariant)
                <span class="fs-badge">
                    {{ $saleVariant->discount_label }}
                </span>
            @endif

            {{-- Hết hàng --}}
            @if ($outOfStock)
                <span class="fs-badge bg-secondary">Hết hàng</span>
            @endif

            <img
                src="{{ $product->main_image_url }}"
                alt="{{ $product->name }}"
                loading="lazy"
            >

            <div class="fs-overlay">

                {{-- Xem chi tiết --}}
                <span class="fs-icon fs-left js-go-detail">
                    <i class="bi bi-eye"></i>
                </span>

                {{-- MUA NGAY --}}
                <button
                    type="button"
                    class="fs-buy btn-buy-now"
                    data-variant-id="{{ $addVariant?->id }}"
                    {{ $outOfStock ? 'disabled' : '' }}>
                    <i class="bi bi-lightning-charge-fill"></i>
                    Mua ngay
                </button>

                {{-- ADD TO CART --}}
                <button
                    type="button"
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

            <div class="fs-title js-go-detail">
                {{ \Illuminate\Support\Str::limit($product->name, 48) }}
            </div>

            <div class="fs-meta">
                <span>⭐⭐⭐⭐⭐ (5.0)</span>
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

<script>
document.addEventListener('click', function (e) {

    /* ========= ADD TO CART ========= */
    const addBtn = e.target.closest('.btn-add-to-cart');
    if (addBtn) {
        e.preventDefault();
        e.stopImmediatePropagation();

        const variantId = addBtn.dataset.variantId;
        if (!variantId) {
            showCenterNotify('Sản phẩm đã hết hàng', 'error');
            return;
        }

        fetch("{{ route('cart.add') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: new URLSearchParams({
                variant_id: variantId,
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

            showCenterNotify('Đã thêm vào giỏ hàng');
        })
        .catch(() => {
            showCenterNotify('Lỗi kết nối máy chủ', 'error');
        });

        return;
    }


    /* ========= BUY NOW (không dùng giỏ) ========= */
    const buyBtn = e.target.closest('.btn-buy-now');
    if (buyBtn) {
        e.preventDefault();
        e.stopImmediatePropagation();

        const variantId = buyBtn.dataset.variantId;
        if (!variantId) {
            showCenterNotify('Sản phẩm đã hết hàng', 'error');
            return;
        }

        fetch("{{ route('checkout.buyNow') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: new URLSearchParams({
                variant_id: variantId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                showCenterNotify(data.message || 'Không thể mua sản phẩm', 'error');
                return;
            }

            // 👉 chuyển tới checkout chỉ sản phẩm này
            window.location.href = data.redirect;
        })
        .catch(() => {
            showCenterNotify('Lỗi kết nối máy chủ', 'error');
        });

        return;
    }


    /* ========= XEM CHI TIẾT ========= */
    const goDetail = e.target.closest('.js-go-detail');
    if (goDetail) {
        e.stopImmediatePropagation();
        const card = goDetail.closest('.js-card');
        if (card) window.location.href = card.dataset.href;
        return;
    }

    /* ========= CLICK CARD ========= */
    const card = e.target.closest('.js-card');
    if (card) {
        window.location.href = card.dataset.href;
    }

});
</script>