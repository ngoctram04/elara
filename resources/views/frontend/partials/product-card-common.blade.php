@php
// Variant còn hàng đầu tiên
$addVariant = $product->variants
->first(fn ($v) => $v->stock_quantity > 0);

// Giá thấp nhất
$priceVariant = $product->variants
    ->sortBy(fn ($v) => $v->final_price ?? $v->price)
    ->first();

// Badge sale
$saleVariant = $product->variants
    ->first(fn ($v) => $v->is_on_sale);

// Hết hàng
$outOfStock = !$addVariant;

// Wishlist
$isFavorited = in_array($product->id, $favorites ?? []);

@endphp

@if ($priceVariant)

<div class="col-lg-3 col-md-4 col-sm-6 mb-4">
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

        {{-- ❤️ WISHLIST --}}
        <button type="button"
                class="wishlist-btn btn-wishlist"
                data-product-id="{{ $product->id }}">
            <i class="bi {{ $isFavorited ? 'bi-heart-fill text-danger' : 'bi-heart' }}"></i>
        </button>

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
                {{ $outOfStock ? 'disabled' : '' }}
                title="Thêm vào giỏ">
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

<style>
/* Nút wishlist */
.wishlist-btn{
    position:absolute;
    top:12px;
    right:12px;
    width:36px;
    height:36px;
    border:none;
    border-radius:50%;
    background:#fff;
    box-shadow:0 3px 8px rgba(0,0,0,0.15);
    display:flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    z-index:20;
    transition:all .2s ease;
}

.wishlist-btn i{
    font-size:18px;
    color:#555;
}

.wishlist-btn:hover{
    transform:scale(1.1);
}
</style>

<script>
document.addEventListener('click', function (e) {

    /* ========= WISHLIST ========= */
    const wishBtn = e.target.closest('.btn-wishlist');
    if (wishBtn) {
        e.preventDefault();
        e.stopImmediatePropagation();

        const productId = wishBtn.dataset.productId;
        const icon = wishBtn.querySelector('i');

        fetch("{{ route('wishlist.toggle') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: new URLSearchParams({
                product_id: productId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                showCenterNotify(data.message || 'Vui lòng đăng nhập', 'error');
                return;
            }

            if (data.favorited) {
                icon.classList.remove('bi-heart');
                icon.classList.add('bi-heart-fill','text-danger');
            } else {
                icon.classList.remove('bi-heart-fill','text-danger');
                icon.classList.add('bi-heart');
            }
        });

        return;
    }


    /* ========= ADD TO CART ========= */
const addBtn = e.target.closest('.btn-add-to-cart');
if (addBtn) {
    e.preventDefault();
    e.stopImmediatePropagation();

    const variantId = addBtn.dataset.variantId;

    // Hết hàng
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
    .then(async res => {
        // Nếu server lỗi (500, 419, 422...) vẫn cố đọc JSON
        const data = await res.json().catch(() => null);

        if (!res.ok || !data) {
            throw new Error('Server error');
        }

        return data;
    })
    .then(data => {
        if (data.success) {
            // Thông báo thành công
            showCenterNotify(data.message || 'Đã thêm vào giỏ hàng', 'success');

            // Cập nhật badge giỏ nếu có
            if (data.cart_count !== undefined) {
                const badge = document.querySelector('.cart-count');
                if (badge) badge.innerText = data.cart_count;
            }

        } else {
            showCenterNotify(data.message || 'Không thể thêm vào giỏ', 'error');
        }
    })
    .catch(() => {
        showCenterNotify('Có lỗi hệ thống, vui lòng thử lại', 'error');
    });

    return;
}


    /* ========= BUY NOW ========= */
    const buyBtn = e.target.closest('.btn-buy-now');
    if (buyBtn) {
        e.preventDefault();
        e.stopImmediatePropagation();

        const variantId = buyBtn.dataset.variantId;

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
            if (data.success) {
                window.location.href = data.redirect;
            }
        });

        return;
    }


    /* ========= CLICK CARD ========= */
    const goDetail = e.target.closest('.js-go-detail');
    if (goDetail) {
        e.stopImmediatePropagation();
        const card = goDetail.closest('.js-card');
        if (card) window.location.href = card.dataset.href;
        return;
    }

    const card = e.target.closest('.js-card');
    if (card) {
        window.location.href = card.dataset.href;
    }

});
</script>
