@extends('layouts.frontend')
@section('title', $category->name)

@section('content')

<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => url('/')],
    ['label' => 'Danh mục', 'url' => route('shop')],
    ['label' => $category->name]
]" />

{{-- ================= CATEGORY BANNER ================= --}}
<section class="category-banner mb-3">
    <h1 class="fw-bold text-uppercase">{{ $category->name }}</h1>
</section>

<div class="container">
    <div class="row">

        {{-- ================= SIDEBAR ================= --}}
        <aside class="col-md-3 mb-4">
            <form method="GET" class="sidebar-box">

                {{-- GIỮ SORT --}}
                <input type="hidden" name="sort" value="{{ request('sort') }}">

                {{-- CATEGORY --}}
                @foreach($allCategories as $parent)
                    <div class="accordion-item {{ $parent->children->pluck('id')->contains($category->id) ? 'active' : '' }}">
                        <button type="button" class="accordion-header">
                            <span>{{ strtoupper($parent->name) }}</span>
                            <i class="bi bi-chevron-down"></i>
                        </button>

                        <div class="accordion-body">
                            <ul class="sidebar-list">
                                @foreach($parent->children as $child)
                                    <li class="{{ $category->id === $child->id ? 'active' : '' }}">
                                        <a href="{{ route('category.show', $child->slug) }}">
                                            {{ $child->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endforeach

                {{-- PRICE --}}
                <div class="sidebar-section">
                    <div class="sidebar-title">Khoảng giá</div>

                    @php $price = request('price'); @endphp

                    <label class="price-pill pink">
                        <input type="radio" name="price" value="0-100" hidden {{ $price === '0-100' ? 'checked' : '' }}>
                        0 – 100.000đ
                    </label>

                    <label class="price-pill blue">
                        <input type="radio" name="price" value="100-200" hidden {{ $price === '100-200' ? 'checked' : '' }}>
                        100.000đ – 200.000đ
                    </label>

                    <label class="price-pill yellow">
                        <input type="radio" name="price" value="200-300" hidden {{ $price === '200-300' ? 'checked' : '' }}>
                        200.000đ – 300.000đ
                    </label>

                    <label class="price-pill orange">
                        <input type="radio" name="price" value="300+" hidden {{ $price === '300+' ? 'checked' : '' }}>
                        Trên 300.000đ
                    </label>
                </div>

                {{-- BRAND --}}
                @if($brands->count())
                    <div class="sidebar-section">
                        <div class="sidebar-title">Thương hiệu</div>

                        <div class="brand-list">
                            @foreach($brands as $brand)
                                <label class="brand-item">
                                    <input
                                        type="checkbox"
                                        name="brands[]"
                                        value="{{ $brand->id }}"
                                        {{ in_array($brand->id, request()->brands ?? []) ? 'checked' : '' }}
                                    >
                                    {{ $brand->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

            </form>
        </aside>

        {{-- ================= PRODUCTS ================= --}}
        <section class="col-md-9">

            {{-- ===== TOOLBAR ===== --}}
            <div class="product-toolbar mb-4">
                <div class="toolbar-left">
                    <span class="toolbar-label">Sắp xếp:</span>

                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'best_selling']) }}"
                       class="toolbar-btn {{ request('sort', 'best_selling') === 'best_selling' ? 'active' : '' }}">
                        Bán chạy
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}"
                       class="toolbar-btn {{ request('sort') === 'newest' ? 'active' : '' }}">
                        Mới nhất
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'price_asc']) }}"
                       class="toolbar-btn {{ request('sort') === 'price_asc' ? 'active' : '' }}">
                        Giá ↑
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'price_desc']) }}"
                       class="toolbar-btn {{ request('sort') === 'price_desc' ? 'active' : '' }}">
                        Giá ↓
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'rating']) }}"
                       class="toolbar-btn {{ request('sort') === 'rating' ? 'active' : '' }}">
                        Đánh giá cao
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'discount']) }}"
                       class="toolbar-btn {{ request('sort') === 'discount' ? 'active' : '' }}">
                        Đang giảm giá
                    </a>
                </div>

                {{-- LIMIT --}}
                <div class="toolbar-right">
                    <form method="GET">
                        @foreach(request()->except('limit') as $key => $value)
                            @if(is_array($value))
                                @foreach($value as $v)
                                    <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach

                        <select name="limit" class="toolbar-select" onchange="this.form.submit()">
                            <option value="9" {{ request('limit', 9) == 9 ? 'selected' : '' }}>Hiển thị 9</option>
                            <option value="18" {{ request('limit') == 18 ? 'selected' : '' }}>Hiển thị 18</option>
                            <option value="36" {{ request('limit') == 36 ? 'selected' : '' }}>Hiển thị 36</option>
                        </select>
                    </form>
                </div>
            </div>

            {{-- ===== PRODUCT GRID ===== --}}
            <div class="row g-4">
                @forelse($products as $product)
                    <div class="col-lg-4 col-md-6">
                        @include('frontend.partials.product-card-category', ['product' => $product])
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-5">
                        Không có sản phẩm
                    </div>
                @endforelse
            </div>

            {{-- ===== PAGINATION ===== --}}
            <div class="mt-4">
                {{ $products->links('vendor.pagination.custom-blue') }}
            </div>

        </section>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.accordion-header').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.closest('.accordion-item').classList.toggle('active');
        });
    });

    document.querySelectorAll('.sidebar-box input').forEach(function (input) {
        input.addEventListener('change', function () {
            this.form.submit();
        });
    });
});
</script>
@endpush