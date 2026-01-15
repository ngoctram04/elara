<div class="col-lg-3 col-md-4 col-sm-6 mb-4">
    <div class="card h-100 shadow-sm border-0 product-card">

        <img src="{{ $product->main_image_url }}"
             class="card-img-top"
             alt="{{ $product->name }}">

        <div class="card-body">
            <h6 class="card-title small">
                {{ Str::limit($product->name, 50) }}
            </h6>

            <div class="fw-bold text-danger mb-2">
                {{ number_format($product->min_price) }}đ
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Đã bán {{ $product->total_sold }}
                </small>

                <a href="#" class="btn btn-primary btn-sm">
                    Mua ngay
                </a>
            </div>
        </div>
    </div>
</div>
