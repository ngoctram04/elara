@if($product->is_flash_sale)
<div class="col-lg-3 col-md-4 col-sm-6 mb-4">
    <div class="fs-card">

        {{-- Badge giảm giá --}}
        <span class="fs-badge">
            -{{ $product->flash_discount_percent }}%
        </span>

        {{-- Ảnh --}}
        <div class="fs-image">
            <img src="{{ $product->main_image_url }}"
                 alt="{{ $product->name }}">
        </div>

        {{-- Tên --}}
        <h6 class="fs-title">
            {{ \Illuminate\Support\Str::limit($product->name, 40) }}
        </h6>

        {{-- Đã bán --}}
        <div class="fs-sold">
            <i class="bi bi-fire text-danger"></i>
            Đã bán {{ $product->total_sold }}
        </div>

        {{-- Giá --}}
        <div class="fs-price">
            <span class="old">
                {{ number_format($product->flash_original_price) }}đ
            </span>

            <span class="new">
                {{ number_format($product->flash_sale_price) }}đ
            </span>
        </div>

        {{-- Hành động --}}
        <div class="fs-actions">
            <button class="fs-icon" title="Thêm vào giỏ">
                <i class="bi bi-cart-plus"></i>
            </button>

            <a href="{{ route('shop') }}"
               class="fs-buy">
                <i class="bi bi-lightning-charge-fill me-1"></i>
                Mua ngay
            </a>

            <button class="fs-icon eye" title="Xem nhanh">
                <i class="bi bi-eye"></i>
            </button>
        </div>

    </div>
</div>
@endif
