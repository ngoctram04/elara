@extends('layouts.admin')

@section('title','Danh sách sản phẩm')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">Danh sách sản phẩm</h5>
                <small class="text-muted">Quản lý toàn bộ sản phẩm trong hệ thống</small>
            </div>

            <a href="{{ route('admin.products.create') }}"
               class="btn btn-success btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Thêm sản phẩm
            </a>
        </div>

        {{-- FILTER --}}
        <form class="row g-2 mb-4 align-items-center" method="GET">

            <div class="col-md-3">
                <input type="text"
                       name="keyword"
                       value="{{ request('keyword') }}"
                       class="form-control form-control-sm"
                       placeholder="Tìm tên sản phẩm...">
            </div>

            {{-- DANH MỤC (CHỈ CON) --}}
            <div class="col-md-2">
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">Danh mục</option>
                    @foreach($categories as $parent)
                        <optgroup label="{{ $parent->name }}">
                            @foreach($parent->children as $child)
                                <option value="{{ $child->id }}"
                                    {{ request('category_id') == $child->id ? 'selected' : '' }}>
                                    {{ $child->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            {{-- THƯƠNG HIỆU --}}
            <div class="col-md-2">
                <select name="brand_id" class="form-select form-select-sm">
                    <option value="">Thương hiệu</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}"
                            {{ request('brand_id') == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- TRẠNG THÁI --}}
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Trạng thái</option>
                    <option value="in_stock" {{ request('status') === 'in_stock' ? 'selected' : '' }}>
                        Còn hàng
                    </option>
                    <option value="out_stock" {{ request('status') === 'out_stock' ? 'selected' : '' }}>
                        Hết hàng
                    </option>
                </select>
            </div>

            <div class="col-md-3 text-end">
                <button class="btn btn-primary btn-sm">
                    <i class="bi bi-funnel"></i> Lọc
                </button>
                <a href="{{ route('admin.products.index') }}"
                   class="btn btn-outline-secondary btn-sm">
                    Đặt lại
                </a>
            </div>
        </form>

        {{-- TABLE --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-center">
                <tr>
                    <th width="70">Mã</th>
                    <th width="90">Ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th width="150">Giá</th>
                    <th width="140">Danh mục</th>
                    <th width="120">Thương hiệu</th>
                    <th width="80">Kho</th>
                    <th width="80">Đã bán</th>
                    <th width="100">Trạng thái</th>
                    <th width="150">Hành động</th>
                </tr>
                </thead>

                <tbody>
                @forelse($products as $product)
                    <tr>
                        {{-- ID --}}
                        <td class="text-center text-muted fw-semibold">
                            {{ $product->id }}
                        </td>

                        {{-- IMAGE --}}
                        <td class="text-center">
                            @if($product->mainImage)
                                <img src="{{ $product->mainImage->url }}"
                                     class="rounded border"
                                     width="55">
                            @else
                                <span class="text-muted small">No image</span>
                            @endif
                        </td>

                        {{-- NAME --}}
                        <td class="fw-medium">
                            {{ $product->name }}
                        </td>

<td class="text-center text-end">
@if ($product->variants->count())

    @php
        // ===== GIÁ GỐC =====
        $originMin = $product->variants->min('price');
        $originMax = $product->variants->max('price');

        // ===== GIÁ BÁN THỰC TẾ =====
        $sellPrices = $product->variants->map(function ($v) {
            return $v->final_price < $v->price
                ? $v->final_price
                : $v->price;
        });

        $sellMin = $sellPrices->min();
        $sellMax = $sellPrices->max();

        // ✅ CHỈ CÓ KM KHI TỒN TẠI BIẾN THỂ THỰC SỰ GIẢM
        $hasPromotion = $product->variants
            ->contains(fn ($v) => $v->final_price < $v->price);
    @endphp

    {{-- GIÁ KHUYẾN MÃI (CHỈ KHI CÓ KM THỰC SỰ) --}}
    @if ($hasPromotion)
        <div class="fw-semibold text-danger">
            {{ number_format($sellMin, 0, ',', '.') }}
            @if ($sellMin != $sellMax)
                – {{ number_format($sellMax, 0, ',', '.') }}
            @endif
            đ
        </div>
    @endif

    {{-- GIÁ GỐC --}}
    <div class="text-muted
        {{ $hasPromotion ? 'text-decoration-line-through small' : 'fw-semibold' }}">
        {{ number_format($originMin, 0, ',', '.') }}
        @if ($originMin != $originMax)
            – {{ number_format($originMax, 0, ',', '.') }}
        @endif
        đ
    </div>

@else
    <span class="text-muted">---</span>
@endif
</td>


                        {{-- CATEGORY --}}
                        <td class="text-center">
                            @if($product->category)
                                <small class="text-center text-muted">
                                    {{ $product->category->parent?->name }} →
                                </small>
                                {{ $product->category->name }}
                            @endif
                        </td>

                        {{-- BRAND --}}
                        <td class="text-center">{{ $product->brand?->name }}</td>

                        {{-- STOCK --}}
                        <td class="text-center fw-semibold">
                            {{ $product->total_stock }}
                        </td>

                        {{-- SOLD --}}
                        <td class="text-center">
                            {{ $product->total_sold }}
                        </td>

                        {{-- STATUS --}}
                        <td class="text-center">
                            @if($product->total_stock > 0)
                                <span class="badge bg-success">Còn hàng</span>
                            @else
                                <span class="badge bg-secondary">Hết hàng</span>
                            @endif
                        </td>

                        {{-- ACTION --}}
                        <td class="text-center">
                            <a href="{{ route('admin.products.show', $product) }}"
                               class="btn btn-sm btn-outline-primary"
                               title="Xem chi tiết">
                                <i class="bi bi-eye"></i>
                            </a>

                            <a href="{{ route('admin.products.edit', $product) }}"
                               class="btn btn-sm btn-outline-warning"
                               title="Chỉnh sửa">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <form action="{{ route('admin.products.destroy', $product) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Xóa sản phẩm này?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"
                                        title="Xóa">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10"
                            class="text-center text-muted py-4">
                            Chưa có sản phẩm nào
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-3 d-flex justify-content-between align-items-center">
            <small class="text-muted">
                @if($products->total() > 0)
                    Hiển thị {{ $products->firstItem() }} – {{ $products->lastItem() }}
                    / {{ $products->total() }} sản phẩm
                @else
                    Không có dữ liệu
                @endif
            </small>

            {{ $products->links() }}
        </div>

    </div>
</div>
@endsection
