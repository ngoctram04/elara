@extends('layouts.frontend')
@section('title', 'Sản phẩm')

@section('content')

<x-breadcrumb :items="[
    ['label' => 'Danh mục', 'url' => route('shop')],
    ['label' => 'Tất cả sản phẩm']
]" />

{{-- ================= PAGE BANNER ================= --}}
<section class="category-banner mb-3">
    <h1 class="fw-bold text-uppercase">TẤT CẢ SẢN PHẨM</h1>
</section>

<div class="container">

    <div class="row">

        {{-- ================= SIDEBAR ================= --}}
        <aside class="col-md-3 mb-4">
            <form method="GET" class="sidebar-box">

                {{-- giữ sort + limit --}}
                <input type="hidden" name="sort" value="{{ request('sort') }}">
                <input type="hidden" name="limit" value="{{ request('limit', 20) }}">

                {{-- CATEGORY --}}
                @foreach($categories as $parent)
                    <div class="accordion-item
                        {{ $parent->children->pluck('id')->contains(request('category')) ? 'active' : '' }}">

                        <button type="button" class="accordion-header">
                            <span>{{ strtoupper($parent->name) }}</span>
                            <i class="bi bi-chevron-down"></i>
                        </button>

                        <div class="accordion-body">
                            <ul class="sidebar-list">
                                @foreach($parent->children as $child)
                                    <li class="{{ request('category') == $child->id ? 'active' : '' }}">
                                        <label>
                                            <input type="radio"
                                                   name="category"
                                                   value="{{ $child->id }}"
                                                   {{ request('category') == $child->id ? 'checked' : '' }}>
                                            {{ $child->name }}
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endforeach

                {{-- PRICE --}}
                <div class="sidebar-section">
                    <div class="sidebar-title">Khoảng giá</div>

                    <label class="price-pill pink">
                        <input type="radio" name="price" value="0-500" hidden {{ request('price')==='0-500'?'checked':'' }}>
                        0 – 500.000đ
                    </label>

                    <label class="price-pill blue">
                        <input type="radio" name="price" value="500-1000" hidden {{ request('price')==='500-1000'?'checked':'' }}>
                        500.000đ – 1.000.000đ
                    </label>

                    <label class="price-pill yellow">
                        <input type="radio" name="price" value="1000+" hidden {{ request('price')==='1000+'?'checked':'' }}>
                        Trên 1.000.000đ
                    </label>
                </div>

                {{-- BRAND --}}
                @if($brands->count())
                    <div class="sidebar-section">
                        <div class="sidebar-title">Thương hiệu</div>
                        <div class="brand-list">
                            @foreach($brands as $brand)
                                <label class="brand-item">
                                    <input type="checkbox"
                                           name="brands[]"
                                           value="{{ $brand->id }}"
                                           {{ in_array($brand->id, request()->brands ?? []) ? 'checked' : '' }}>
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

            {{-- ===== TOOLBAR (GIỐNG TRANG DANH MỤC) ===== --}}
            <div class="product-toolbar mb-4">

                <div class="toolbar-left">
                    <span class="toolbar-label">Sắp xếp:</span>

                    <a href="{{ request()->fullUrlWithQuery(['sort'=>null]) }}"
                       class="toolbar-btn {{ !request('sort') ? 'active' : '' }}">
                        Bán chạy
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'newest']) }}"
                       class="toolbar-btn {{ request('sort')==='newest' ? 'active' : '' }}">
                        Mới nhất
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'price_asc']) }}"
                       class="toolbar-btn {{ request('sort')==='price_asc' ? 'active' : '' }}">
                        Giá ↑
                    </a>

                    <a href="{{ request()->fullUrlWithQuery(['sort'=>'price_desc']) }}"
                       class="toolbar-btn {{ request('sort')==='price_desc' ? 'active' : '' }}">
                        Giá ↓
                    </a>
                </div>

                <div class="toolbar-right">
                    <form method="GET">
                        @foreach(request()->except('limit') as $key=>$value)
                            @if(is_array($value))
                                @foreach($value as $v)
                                    <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach

                        <select name="limit" class="toolbar-select" onchange="this.form.submit()">
                            <option value="20" {{ request('limit',20)==20?'selected':'' }}>Hiển thị 20</option>
                            <option value="40" {{ request('limit')==40?'selected':'' }}>Hiển thị 40</option>
                            <option value="60" {{ request('limit')==60?'selected':'' }}>Hiển thị 60</option>
                        </select>
                    </form>
                </div>

            </div>

            {{-- ===== PRODUCT GRID ===== --}}
            <div class="row g-4">
                @forelse($products as $product)
                    <div class="col-lg-4 col-md-6">
                        @include('frontend.partials.product-card-category', ['product'=>$product])
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-5">
                        Không có sản phẩm
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

@push('scripts')
<script>
document.querySelectorAll('.accordion-header').forEach(btn=>{
    btn.addEventListener('click',()=>{
        btn.closest('.accordion-item').classList.toggle('active')
    })
})
document.querySelectorAll('.sidebar-box input').forEach(i=>{
    i.addEventListener('change',()=>i.form.submit())
})
</script>
@endpush
