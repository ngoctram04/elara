@extends('layouts.frontend')

@section('title', $product->name)

@section('content')
@vite(['resources/css/detail.css', 'resources/js/detail.js'])

<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => url('/')],
    ['label' => 'Chi tiết sản phẩm']
]" />

@php
    $hasVariants = $product->variants->count() > 0;
    $totalSold = $product->variants()->sum('sold_quantity');
    $defaultVariant = $product->variants->firstWhere('stock_quantity', '>', 0) ?? $product->variants->first();

    $isFavorited = in_array((int) $product->id, $favorites ?? [], true);

    $displayFavoriteCount = 0;
    if (isset($favoritesCount)) {
        $displayFavoriteCount = $favoritesCount >= 1000
            ? str_replace('.', ',', round($favoritesCount / 1000, 1)) . 'k'
            : number_format($favoritesCount, 0, ',', '.');
    }

    $groupedVariants = $product->variants->groupBy('attribute_name');
@endphp

<div class="product-detail-page container py-4">

    {{-- ===== TOP SECTION ===== --}}
    <div class="product-top-grid">

        {{-- ===== LEFT: GALLERY ===== --}}
        <div class="gallery-area">
            <div class="gallery-grid single-column">

                <div class="main-image-box">
                    <img id="main-image"
                         src="{{ $defaultVariant?->images->first()
                                ? asset('storage/'.$defaultVariant->images->first()->image_path)
                                : ($product->mainImage
                                    ? asset('storage/'.$product->mainImage->image_path)
                                    : asset('images/no-image.png')) }}"
                         class="main-product-image"
                         alt="{{ $product->name }}">

                    <button type="button" class="zoom-image-btn" id="zoom-main-image">
                        <i class="bi bi-zoom-in"></i> Xem ảnh lớn
                    </button>
                </div>

                <div class="thumb-row">
                    @foreach($product->images as $img)
                        <img src="{{ asset('storage/'.$img->image_path) }}"
                             class="thumb-img"
                             data-image="{{ asset('storage/'.$img->image_path) }}"
                             alt="{{ $product->name }}">
                    @endforeach

                    @foreach($product->variants as $variant)
                        @foreach($variant->images as $vImg)
                            <img src="{{ asset('storage/'.$vImg->image_path) }}"
                                 class="thumb-img"
                                 data-image="{{ asset('storage/'.$vImg->image_path) }}"
                                 data-variant="{{ $variant->id }}"
                                 alt="{{ $product->name }}">
                        @endforeach
                    @endforeach
                </div>

            </div>
        </div>

        {{-- ===== RIGHT: INFO ===== --}}
        <div class="product-info-area">

            <h1 class="product-title">{{ $product->name }}</h1>

            <div class="product-meta-top">
                @if($product->brand)
                    <span class="product-chip">
                        <i class="bi bi-bookmark-star"></i>
                        {{ $product->brand->name }}
                    </span>
                @endif

                @if($product->category)
                    <span class="product-chip">
                        <i class="bi bi-grid"></i>
                        {{ $product->category->name }}
                    </span>
                @endif

                <span class="product-chip">
                    <i class="bi bi-bag-check"></i>
                    Đã bán {{ number_format($totalSold, 0, ',', '.') }}
                </span>

                <button type="button"
                        class="product-chip product-chip-btn btn-wishlist-top"
                        data-product-id="{{ $product->id }}">
                    <i class="bi {{ $isFavorited ? 'bi-heart-fill text-danger' : 'bi-heart text-danger' }}"></i>
                    <span>
                        {{ $isFavorited ? 'Đã thích' : 'Yêu thích' }}
                        (<span id="wishlist-count">{{ $displayFavoriteCount }}</span>)
                    </span>
                </button>
            </div>

            <div class="product-rating-line">
                <div class="stars">
                    {!! str_repeat('★', round($avgRating)) !!}
                    {!! str_repeat('☆', 5 - round($avgRating)) !!}
                </div>

                <span class="rating-badge">{{ number_format($avgRating, 1) }}</span>

                <a href="#tab-reviews" class="review-anchor">
                    {{ $reviewCount }} đánh giá
                </a>
            </div>

            <div class="price-box">
                <div id="price-original" class="price-original" style="display:none"></div>
                <div id="price-final" class="price-final"></div>
            </div>

            @if(!empty($product->short_description))
                <div class="short-desc">
                    {!! nl2br(e($product->short_description)) !!}
                </div>
            @elseif(!empty($product->description))
                <div class="short-desc">
                    {{ \Illuminate\Support\Str::limit(strip_tags($product->description), 220) }}
                </div>
            @endif

            {{-- VARIANTS --}}
            @if($hasVariants)
                <div class="variant-section">
                    @foreach($groupedVariants as $attributeName => $variants)
                        <div class="variant-group">
                            <div class="variant-label">{{ $attributeName }}</div>

                            <div class="variant-options">
                                @foreach($variants as $variant)
                                    @php
                                        $variantImage = $variant->images->first();
                                        $fallback = $product->mainImage
                                            ? asset('storage/'.$product->mainImage->image_path)
                                            : asset('images/no-image.png');

                                        $final = $variant->final_price ?? $variant->price;
                                        $original = $variant->is_on_sale ? $variant->price : '';
                                        $outOfStock = !$variant->isInStock();
                                    @endphp

                                    <button type="button"
                                            class="variant-btn {{ $outOfStock ? 'variant-out' : '' }}"
                                            data-id="{{ $variant->id }}"
                                            data-final="{{ $final }}"
                                            data-original="{{ $original }}"
                                            data-stock="{{ $variant->availableStock() }}"
                                            data-image="{{ $variantImage ? asset('storage/'.$variantImage->image_path) : $fallback }}"
                                            {{ $outOfStock ? 'disabled' : '' }}>
                                        {{ $variant->attribute_value }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div id="stock-text" class="stock-text"></div>
                </div>
            @endif

            {{-- ACTION --}}
            <div class="action-card">
                <form method="POST"
                      action="{{ route('cart.add') }}"
                      id="add-to-cart-form"
                      data-cart-url="{{ route('cart.add') }}"
                      data-csrf="{{ csrf_token() }}">
                    @csrf

                    <input type="hidden" name="variant_id" id="variant_id">

                    <div class="qty-buy-row qty-buy-row-equal">
                        <div class="qty-box">
                            <div class="qty-label">Số lượng</div>

                            <div class="qty-control">
                                <button type="button" class="qty-btn" id="qty-minus">−</button>
                                <input type="number" name="qty" value="1" min="1" class="qty-input">
                                <button type="button" class="qty-btn" id="qty-plus">+</button>
                            </div>
                        </div>

                        <div class="cart-btn-box">
                            <div class="qty-label qty-label-hidden">.</div>
                            <button class="btn-cart-main btn-cart-main-compact" type="submit">
                                <i class="bi bi-cart-plus"></i>
                                Thêm vào giỏ hàng
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>

    {{-- ===== CONTENT AREA ===== --}}
    <div class="detail-content-grid">

        {{-- LEFT CONTENT --}}
        <div class="detail-main-content">

            <div class="detail-tabs">
                <button class="detail-tab-btn active" data-tab="tab-description">
                    Thông tin sản phẩm
                </button>
                <button class="detail-tab-btn" data-tab="tab-reviews">
                    Đánh giá - Hỏi đáp
                </button>
            </div>

            <div class="detail-tab-panel active" id="tab-description">
                <div class="content-card product-description-card">
                    {!! nl2br(e($product->description)) !!}
                </div>
            </div>

            <div class="detail-tab-panel" id="tab-reviews">
                <div class="content-card">

                    <h5 class="section-title mb-3">Đánh giá sản phẩm ({{ $reviewCount }})</h5>

                    <div class="review-summary-box">
                        <div class="review-score-big">
                            {{ number_format($avgRating, 1) }}/5
                        </div>

                        <div class="review-filter-row">
                            <a href="{{ request()->fullUrlWithQuery(['rating' => 'all']) }}"
                               class="btn btn-sm {{ request('rating') == 'all' || !request('rating') ? 'btn-primary' : 'btn-outline-primary' }}">
                                Tất cả
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['rating' => 5]) }}"
                               class="btn btn-sm {{ request('rating') == 5 ? 'btn-primary' : 'btn-outline-primary' }}">
                                5 Sao
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['rating' => 4]) }}"
                               class="btn btn-sm {{ request('rating') == 4 ? 'btn-primary' : 'btn-outline-primary' }}">
                                4 Sao
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['rating' => 3]) }}"
                               class="btn btn-sm {{ request('rating') == 3 ? 'btn-primary' : 'btn-outline-primary' }}">
                                3 Sao
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['rating' => 2]) }}"
                               class="btn btn-sm {{ request('rating') == 2 ? 'btn-primary' : 'btn-outline-primary' }}">
                                2 Sao
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['rating' => 1]) }}"
                               class="btn btn-sm {{ request('rating') == 1 ? 'btn-primary' : 'btn-outline-primary' }}">
                                1 Sao
                            </a>
                        </div>
                    </div>

                    @php
                        $visibleReviews = $reviews->where('is_visible', 1)->values();
                    @endphp

                    <div id="reviews-list">
                        @forelse($visibleReviews as $index => $review)
                            <div class="review-card review-toggle-item {{ $index >= 2 ? 'review-hidden' : '' }}"
                                 style="{{ $index >= 2 ? 'display:none;' : '' }}">
                                <div class="d-flex align-items-start">
                                    <img
                                        src="{{ $review->user?->avatar
                                            ? asset('storage/'.$review->user->avatar)
                                            : asset('images/avatar-default.png') }}"
                                        class="review-avatar me-3"
                                        alt="{{ $review->user->name }}">

                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between gap-2">
                                            <div class="fw-semibold">{{ $review->user->name }}</div>
                                            <small class="text-muted">{{ $review->created_at->format('d/m/Y H:i') }}</small>
                                        </div>

                                        <div class="review-stars mb-1">
                                            {!! str_repeat('★', (int) $review->rating) !!}
                                            {!! str_repeat('☆', 5 - (int) $review->rating) !!}
                                        </div>
                                    </div>
                                </div>

                                @if($review->comment)
                                    <div class="review-content mt-2">{{ $review->comment }}</div>
                                @endif

                                @if($review->media && $review->media->count())
                                    <div class="review-media mt-3 d-flex gap-2 flex-wrap">
                                        @foreach($review->media as $m)
                                            @if($m->file_type == 'image')
                                                <img src="{{ asset('storage/'.$m->file_path) }}"
                                                     alt="Ảnh đánh giá">
                                            @else
                                                <video width="120" controls>
                                                    <source src="{{ asset('storage/'.$m->file_path) }}">
                                                </video>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif

                                @if($review->admin_reply)
                                    <div class="shop-reply mt-3">
                                        <div class="fw-semibold text-primary">
                                            <i class="bi bi-shop"></i> Phản hồi từ cửa hàng
                                        </div>
                                        <div class="small text-muted mb-1">
                                            {{ $review->replied_at?->format('d/m/Y H:i') }}
                                        </div>
                                        <div>{{ $review->admin_reply }}</div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-muted">Chưa có đánh giá.</div>
                        @endforelse
                    </div>

                    @if($visibleReviews->count() > 2)
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-outline-primary rounded-pill px-4" id="toggle-reviews-btn">
                                Xem thêm đánh giá
                            </button>
                        </div>
                    @endif

                    <hr class="my-4">

                    <h5 class="section-title mb-3">Hỏi đáp sản phẩm</h5>

                    @if(auth()->check())
                        <form action="{{ route('questions.store') }}" method="POST" class="mb-4">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">

                            <textarea name="question"
                                      class="form-control"
                                      rows="3"
                                      placeholder="Đặt câu hỏi về sản phẩm..."
                                      required></textarea>

                            <button class="btn btn-primary mt-2">
                                <i class="bi bi-question-circle"></i> Gửi câu hỏi
                            </button>
                        </form>
                    @else
                        <div class="alert alert-warning">
                            Bạn cần <a href="{{ route('login') }}">đăng nhập</a> để đặt câu hỏi.
                        </div>
                    @endif

                    @forelse($product->questions as $question)
                        <div class="qa-item">
                            <div class="fw-semibold">{{ $question->user->name }}</div>
                            <div class="small text-muted">{{ $question->created_at->format('d/m/Y H:i') }}</div>
                            <div class="mt-1">{{ $question->question }}</div>

                            @foreach($question->answers as $answer)
                                <div class="ms-4 border-start ps-3 mt-2">
                                    <strong>
                                        {{ $answer->user->name }}
                                        @if($answer->is_admin)
                                            <span class="badge bg-primary">Shop</span>
                                        @endif
                                    </strong>

                                    <div class="small text-muted">
                                        {{ $answer->created_at->format('d/m/Y H:i') }}
                                    </div>

                                    <div>{{ $answer->answer }}</div>
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <div class="text-muted">Chưa có câu hỏi nào về sản phẩm này.</div>
                    @endforelse

                </div>
            </div>

        </div>

        {{-- RIGHT SIDEBAR: RELATED PRODUCTS --}}
        <aside class="detail-sidebar">
            @if(!empty($relatedProducts) && $relatedProducts->count())
                <div class="related-sidebar-section">
                    <div class="related-sidebar-head">
                        <h3 class="sidebar-related-title">Sản phẩm liên quan</h3>

                        @if($relatedProducts->count() > 4)
                            <div class="related-vertical-nav">
                                <button type="button" class="related-arrow up" id="related-prev">
                                    <i class="bi bi-chevron-up"></i>
                                </button>
                                <button type="button" class="related-arrow down" id="related-next">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                            </div>
                        @endif
                    </div>

                    <div class="related-vertical-wrap">
                        <div class="related-vertical-slider" id="related-slider">
                            @foreach($relatedProducts as $item)
                                <div class="related-vertical-slide">
                                    @include('frontend.partials.product-card-category', ['product' => $item])
                                </div>
                            @endforeach

                            @if($relatedProducts->count() > 4)
                                @foreach($relatedProducts->take(4) as $item)
                                    <div class="related-vertical-slide related-vertical-slide-clone">
                                        @include('frontend.partials.product-card-category', ['product' => $item])
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </aside>
    </div>

    {{-- ===== RECENT VIEWED ===== --}}
    @if(!empty($recentProducts) && $recentProducts->count())
        <div class="recent-view-section">
            <div class="section-head">
                <h3 class="recent-title">Sản phẩm vừa xem</h3>

                @if($recentProducts->count() > 4)
                    <div class="recent-nav">
                        <button type="button" class="recent-arrow prev" id="recent-prev">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button type="button" class="recent-arrow next" id="recent-next">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                @endif
            </div>

            <div class="recent-slider-wrap">
                <div class="recent-slider" id="recent-slider">
                    @foreach($recentProducts as $item)
                        <div class="recent-slide">
                            @include('frontend.partials.product-card-category', ['product' => $item])
                        </div>
                    @endforeach

                    @if($recentProducts->count() > 4)
                        @foreach($recentProducts->take(4) as $item)
                            <div class="recent-slide recent-slide-clone">
                                @include('frontend.partials.product-card-category', ['product' => $item])
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    @endif

</div>

{{-- LIGHTBOX --}}
<div id="media-lightbox" style="display:none;">
    <span id="lightbox-close">&times;</span>
    <img id="lightbox-img" style="display:none;">
    <video id="lightbox-video" controls style="display:none;">
        <source id="lightbox-video-src">
    </video>
</div>
@endsection

@push('scripts')
<script>
    const wishlistToggleUrl = @json(route('wishlist.toggle'));
</script>
@endpush