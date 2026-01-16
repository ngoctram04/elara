@extends('layouts.frontend')
@section('title', $category->name)

@section('content')

{{-- ================= CATEGORY BANNER ================= --}}
<section class="category-banner my-4">
    <h1 class="text-center fw-bold">
        {{ strtoupper($category->name) }}
    </h1>
</section>

<div class="container my-4">

    {{-- ================= HEADER ================= --}}
    <div class="row mb-4 align-items-end">
        <div class="col-md-3">
            <h4 class="fw-bold mb-0">{{ $category->name }}</h4>
            <small class="text-muted">Bạn đang tìm gì?</small>
        </div>

        <div class="col-md-9">
            <div class="sort-bar">
                <span>Sắp xếp:</span>
                <a class="sort-btn active">Bán chạy</a>
                <a class="sort-btn">Mới nhất</a>
                <a class="sort-btn">Giá thấp → cao</a>
                <a class="sort-btn">Giá cao → thấp</a>

                <select class="ms-auto form-select form-select-sm w-auto">
                    <option>Hiển thị 20</option>
                    <option>Hiển thị 40</option>
                </select>
            </div>
        </div>
    </div>

    <div class="row">

        {{-- ================= SIDEBAR ================= --}}
        <aside class="col-md-3 mb-4">
            <div class="sidebar-box">

                @foreach($allCategories as $parent)
                    <div class="sidebar-section">
                        <div class="sidebar-title">
                            {{ strtoupper($parent->name) }}
                        </div>

                        @if($parent->children->count())
                            <ul class="sidebar-list">
                                @foreach($parent->children as $child)
                                    <li class="{{ $category->id === $child->id ? 'active' : '' }}">
                                        <a href="{{ route('category.show', $child->slug) }}">
                                            {{ $child->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach

                <div class="sidebar-section">
                    <div class="sidebar-title">KHOẢNG GIÁ</div>
                    <div class="price-pill pink">0 – 500.000đ</div>
                    <div class="price-pill blue">500.000đ – 1.000.000đ</div>
                    <div class="price-pill yellow">Trên 1.000.000đ</div>
                </div>

            </div>
        </aside>

        {{-- ================= PRODUCT GRID ================= --}}
        <section class="col-md-9">
            <div class="row g-4">

                @forelse($products as $product)
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        @include('frontend.partials.product-card-category', [
                            'product' => $product
                        ])
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-5">
                        Không có sản phẩm trong danh mục này
                    </div>
                @endforelse

            </div>

            <div class="mt-4 d-flex justify-content-center">
                {{ $products->links('pagination::bootstrap-5') }}
            </div>
        </section>

    </div>
</div>

@endsection
