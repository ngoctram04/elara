@extends('layouts.admin')

@section('title','Chi tiết sản phẩm')

@section('content')
<div class="container-fluid">
<div class="card border-0 shadow-sm">
<div class="card-body p-4">

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-semibold mb-0">
        <i class="bi bi-box-seam text-primary me-2"></i>
        Chi tiết sản phẩm
    </h4>

    <div>
        <a href="{{ route('admin.products.edit', $product) }}"
           class="btn btn-warning btn-sm text-white me-2">
            <i class="bi bi-pencil"></i> Chỉnh sửa
        </a>

        <a href="{{ route('admin.products.index') }}"
           class="btn btn-light btn-sm">
            Quay lại
        </a>
    </div>
</div>

@php
    $totalStock = $product->variants->sum('stock_quantity');
    $totalSold = $product->variants->sum('sold_quantity');
@endphp

<div class="row">

{{-- LEFT --}}
<div class="col-lg-6">

    <p><strong>Tên sản phẩm:</strong> {{ $product->name }}</p>

    <p>
        <strong>Danh mục:</strong>
        {{ $product->category?->parent?->name }}
        @if($product->category?->parent)
            →
        @endif
        {{ $product->category?->name }}
    </p>

    <p><strong>Thương hiệu:</strong> {{ $product->brand?->name }}</p>

    <p>
        <strong>Trạng thái kho:</strong>
        @if($totalStock > 0)
            <span class="badge bg-success">Còn hàng ({{ $totalStock }})</span>
        @else
            <span class="badge bg-danger">Hết hàng</span>
        @endif
    </p>

    <p>
        <strong>Tổng đã bán:</strong>
        <span class="badge bg-primary">{{ $totalSold }}</span>
    </p>

    <p>
        <strong>Nổi bật:</strong>
        {{ $product->is_featured ? 'Có' : 'Không' }}
    </p>

</div>

{{-- RIGHT --}}
<div class="col-lg-6">
    <strong>Ảnh sản phẩm:</strong>
    <div class="d-flex gap-2 flex-wrap mt-2">
        @forelse($product->images as $img)
            <img src="{{ $img->url }}"
                 width="90"
                 class="rounded border">
        @empty
            <span class="text-muted">Chưa có ảnh</span>
        @endforelse
    </div>
</div>

</div>

<hr>

{{-- ================= VARIANTS ================= --}}
@php
    $attributeName = $product->variants->first()?->attribute_name;
@endphp

<h5 class="fw-semibold text-primary">
    Biến thể
    @if($attributeName)
        <span class="text-muted small">({{ $attributeName }})</span>
    @endif
</h5>

<div class="table-responsive mt-3">
<table class="table table-bordered align-middle">
    <thead class="table-light text-center">
        <tr>
            <th width="60">#</th>
            <th>Giá trị</th>
            <th width="140">Giá bán</th>
            <th width="110">Tồn kho</th>
            <th width="110">Đã bán</th>
            <th width="100">Ảnh</th>
        </tr>
    </thead>
    <tbody>

    @forelse($product->variants as $index => $variant)
        <tr class="text-center">
            <td>{{ $index + 1 }}</td>

            <td class="fw-semibold">
                {{ $variant->attribute_value }}
            </td>

            {{-- GIÁ --}}
            <td class="text-end">
                @if ($variant->isOnSale())
                    <div class="fw-semibold text-danger">
                        {{ number_format($variant->final_price) }}đ
                    </div>
                    <div class="text-muted text-decoration-line-through small">
                        {{ number_format($variant->price) }}đ
                    </div>
                @else
                    <span class="fw-semibold">
                        {{ number_format($variant->price) }}đ
                    </span>
                @endif
            </td>

            {{-- TỒN --}}
            <td>
                @if($variant->stock_quantity > 0)
                    <span class="badge bg-success">
                        {{ $variant->stock_quantity }}
                    </span>
                @else
                    <span class="badge bg-danger">0</span>
                @endif
            </td>

            {{-- ĐÃ BÁN --}}
            <td>
                <span class="badge bg-primary">
                    {{ $variant->sold_quantity }}
                </span>
            </td>

            {{-- ẢNH --}}
            <td>
                @if($variant->images->first())
                    <img src="{{ $variant->images->first()->url }}"
                         width="60"
                         class="rounded border">
                @else
                    <span class="text-muted">-</span>
                @endif
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6"
                class="text-center text-muted py-4">
                Chưa có biến thể nào
            </td>
        </tr>
    @endforelse

    </tbody>
</table>
</div>

</div>
</div>
</div>
@endsection