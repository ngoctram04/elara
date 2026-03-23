@extends('layouts.admin')

@section('title','Chi tiết sản phẩm')

@section('content')

<div class="container-fluid">
<div class="card border-0 shadow-sm">
<div class="card-body p-4">

{{-- HEADER --}}

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-semibold mb-0">
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
$totalSold  = $product->variants->sum('sold_quantity');
$attributeName = $product->variants->first()?->attribute_name;
@endphp

<div class="row">

{{-- LEFT INFO --}}

<div class="col-lg-6">

<p><strong>Tên sản phẩm:</strong> {{ $product->name }}</p>

<p>
    <strong>Danh mục:</strong>
    @if($product->category)
        {{ $product->category?->parent?->name }}
        @if($product->category?->parent) → @endif
        {{ $product->category->name }}
    @else
        <span class="text-muted">Chưa phân loại</span>
    @endif
</p>

<p>
    <strong>Thương hiệu:</strong>
    {{ $product->brand?->name ?? '---' }}
</p>

<p>
    <strong>Trạng thái kho:</strong>
    @if($totalStock > 0)
        <span class="badge bg-success">
            Còn hàng ({{ $totalStock }})
        </span>
    @else
        <span class="badge bg-danger">Hết hàng</span>
    @endif
</p>

<p>
    <strong>Tổng đã bán:</strong>
    <span class="badge bg-primary">
        {{ $totalSold }}
    </span>
    <small class="text-muted"></small>
</p>

<p>
    <strong>Nổi bật:</strong>
    @if($product->is_featured)
        <span class="badge bg-warning text-dark">Có</span>
    @else
        <span class="badge bg-secondary">Không</span>
    @endif
</p>

<p>
    <strong>Khoảng giá:</strong>
    @if($product->variants->count())
        {{ number_format($product->variants->min('price')) }}
        @if($product->variants->min('price') != $product->variants->max('price'))
            – {{ number_format($product->variants->max('price')) }}
        @endif
        đ
    @else
        ---
    @endif
</p>


</div>

{{-- RIGHT IMAGES --}}

<div class="col-lg-6">
    <strong>Ảnh sản phẩm:</strong>
    <div class="d-flex gap-2 flex-wrap mt-2">

    {{-- Main image first --}}
    @if($product->mainImage)
        <img src="{{ $product->mainImage->url }}"
             width="100"
             class="rounded border border-primary">
    @endif

    @foreach($product->subImages as $img)
        <img src="{{ $img->url }}"
             width="90"
             class="rounded border">
    @endforeach

    @if(!$product->images->count())
        <span class="text-muted">Chưa có ảnh</span>
    @endif
</div>


</div>

</div>

<hr>

{{-- ================= VARIANTS ================= --}}

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
    <th width="150">Mã biến thể</th>
    <th>Giá trị</th>
    <th width="160">Giá bán</th>
    <th width="110">Tồn kho</th>
    <th width="110">Đã bán</th>
    <th width="100">Ảnh</th>
</tr>
</thead>

<tbody>
@forelse($product->variants as $index => $variant)
<tr class="text-center">

<td class="fw-semibold text-muted">
    BT{{ str_pad($variant->id, 5, '0', STR_PAD_LEFT) }}
</td>

<td class="fw-semibold">
    {{ $variant->attribute_value }}
</td>

{{-- GIÁ --}}
<td class="text-end">
    @if ($variant->final_price < $variant->price)
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

{{-- STOCK --}}
<td>
    @if($variant->stock_quantity > 0)
        <span class="badge bg-success">
            {{ $variant->stock_quantity }}
        </span>
    @else
        <span class="badge bg-danger">0</span>
    @endif
</td>

{{-- SOLD --}}
<td>
    <span class="badge bg-primary">
        {{ $variant->sold_quantity }}
    </span>
</td>

{{-- IMAGE --}}
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
<td colspan="6" class="text-center text-muted py-4">
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
