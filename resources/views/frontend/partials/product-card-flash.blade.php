@if($product->is_flash_sale)
<div class="col-lg-3 col-md-4 col-sm-6 mb-4">
    <div class="fs-card">

        {{-- IMAGE --}}
        <div class="fs-image">

            {{-- BADGE --}}
            <span class="fs-badge">
                -{{ $product->flash_discount_percent }}%
            </span>

            <img
                src="{{ $product->main_image_url }}"
                alt="{{ $product->name }}"
                loading="lazy"
            >

            {{-- OVERLAY --}}
            <div class="fs-overlay">

                {{-- LEFT ICON --}}
                <button
                    type="button"
                    class="fs-icon fs-left"
                    title="Xem nhanh"
                >
                    <i class="bi bi-eye"></i>
                </button>

                {{-- BUY --}}
                <a href="{{ route('products.show', $product->slug) }}"
                   class="fs-buy">
                    <i class="bi bi-lightning-charge-fill"></i>
                    Mua ngay
                </a>

                {{-- RIGHT ICON --}}
                <button class="fs-icon fs-right" title="Thêm vào giỏ">
                    <i class="bi bi-cart-plus"></i>
                </button>

            </div>
        </div>

        {{-- INFO --}}
        <div class="fs-info">

            <div class="fs-brand">
                {{ $product->brand->name ?? 'Thương hiệu' }}
            </div>

            <div class="fs-title">
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
                <span class="old">
                    {{ number_format($product->flash_original_price) }}đ
                </span>
                <span class="new">
                    {{ number_format($product->flash_sale_price) }}đ
                </span>
            </div>

        </div>
    </div>
</div>
@endif
