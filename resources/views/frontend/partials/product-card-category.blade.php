<div class="category-card h-100">

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

            <button class="category-icon left" title="Xem nhanh">
                <i class="bi bi-eye"></i>
            </button>

            <a href="{{ route('products.show', $product->slug) }}"
               class="category-buy">
                <i class="bi bi-lightning-charge-fill"></i>
                Mua ngay
            </a>

            <button class="category-icon right" title="Thêm vào giỏ">
                <i class="bi bi-cart-plus"></i>
            </button>

        </div>
    </div>

    {{-- INFO --}}
    <div class="category-info">

        <div class="category-title">
            {{ \Illuminate\Support\Str::limit($product->name, 50) }}
        </div>

        <div class="category-meta">
            <span>⭐ 5.0</span>
            <span>🔥 {{ $product->total_sold }}</span>
        </div>

        <div class="category-price">
            {{ number_format($product->min_price) }}đ
        </div>

    </div>
</div>
