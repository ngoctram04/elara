@extends('layouts.admin')

@section('title','Danh sách sản phẩm')

@section('content')

<div class="card border-0 shadow-sm">
<div class="card-body">

{{-- HEADER --}}

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">
            Danh sách sản phẩm
        </h5>
        <small class="text-muted">Quản lý toàn bộ sản phẩm trong hệ thống</small>
    </div>

<a href="{{ route('admin.products.create') }}"
   class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i> Thêm sản phẩm
</a>


</div>

{{-- FILTER --}}

<form class="row g-2 mb-4" method="GET">

<div class="col-md-3">
    <input type="text"
           name="keyword"
           value="{{ request('keyword') }}"
           class="form-control form-control-sm"
           placeholder="Tìm tên sản phẩm...">
</div>

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

<div class="col-md-3 d-flex gap-2">

<button class="btn btn-outline-primary btn-sm">
    <i class="bi bi-search"></i> Lọc
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
    <th width="60">Mã</th>
    <th width="80">Ảnh</th>
    <th>Tên sản phẩm</th>
    <th width="160">Giá</th>
    <th width="160">Danh mục</th>
    <th width="100">Thương hiệu</th>
    <th width="80">Tồn</th>
    <th width="80">Đã bán</th>
    <th width="80">Trạng thái</th>
    <th width="185">Hành động</th>
</tr>
</thead>

<tbody>
@forelse($products as $product)
<tr>

<td class="text-center text-muted fw-semibold">
    #{{ $product->id }}
</td>

<td class="text-center">
    @if($product->mainImage)
        <img src="{{ $product->mainImage->url }}"
             class="rounded border"
             width="55">
    @else
        <span class="text-muted small">No image</span>
    @endif
</td>

<td class="fw-medium">
    {{ $product->name }}
</td>

{{-- GIÁ --}}

<td class="text-end">
@php
$originMin = $product->variants->min('price');
$originMax = $product->variants->max('price');

$sellMin = $product->variants->min('final_price');
$sellMax = $product->variants->max('final_price');

$hasPromotion = $sellMin < $originMin;
@endphp

@if($originMin)
@if($hasPromotion) <div class="fw-semibold text-danger">
{{ number_format($sellMin, 0, ',', '.') }}
@if($sellMin != $sellMax)
– {{ number_format($sellMax, 0, ',', '.') }}
@endif
đ </div>

    <div class="text-muted text-decoration-line-through small">
        {{ number_format($originMin, 0, ',', '.') }}
        @if($originMin != $originMax)
            – {{ number_format($originMax, 0, ',', '.') }}
        @endif
        đ
    </div>
@else
    <div class="fw-semibold">
        {{ number_format($originMin, 0, ',', '.') }}
        @if($originMin != $originMax)
            – {{ number_format($originMax, 0, ',', '.') }}
        @endif
        đ
    </div>
@endif

@else <span class="text-muted">---</span>
@endif

</td>

<td class="text-center">
    @if($product->category)
        <small class="text-muted">
            {{ $product->category->parent?->name }} →
        </small>
        {{ $product->category->name }}
    @endif
</td>

<td class="text-center">
    {{ $product->brand?->name }}
</td>

<td class="text-center">
    @if($product->total_stock > 0)
        <span class="badge bg-success">{{ $product->total_stock }}</span>
    @else
        <span class="badge bg-danger">0</span>
    @endif
</td>

<td class="text-center">
    <span class="badge bg-primary">
        {{ $product->total_sold }}
    </span>
</td>

<td class="text-center">
    @if($product->total_stock > 0)
        <span class="badge bg-success">Còn hàng</span>
    @else
        <span class="badge bg-danger">Hết hàng</span>
    @endif
</td>

<td class="text-center">

<a href="{{ route('admin.products.show', $product) }}"
   class="btn btn-sm btn-outline-primary">
    <i class="bi bi-eye"></i>
</a>

<a href="{{ route('admin.products.edit', $product) }}"
   class="btn btn-sm btn-outline-warning">
    <i class="bi bi-pencil"></i>
</a>

{{-- NÚT ẨN / HIỆN --}}
<form action="{{ route('admin.products.toggle',$product) }}"
      method="POST"
      class="d-inline">
@csrf
@method('PATCH')

<button class="btn btn-sm {{ $product->is_active ? 'btn-secondary' : 'btn-success' }}">

@if($product->is_active)
<i class="bi bi-eye-slash"></i>
@else
<i class="bi bi-eye"></i>
@endif

</button>

</form>

<form action="{{ route('admin.products.destroy', $product) }}"
      method="POST"
      class="d-inline delete-form">
@csrf
@method('DELETE')

<button class="btn btn-sm btn-outline-danger">
<i class="bi bi-trash"></i>
</button>

</form>

</td>

</tr>
@empty
<tr>
<td colspan="10" class="text-center text-muted py-4">
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
@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function(){

document.querySelectorAll('.delete-form').forEach(form => {

form.addEventListener('submit', function(e){

e.preventDefault();

Swal.fire({
title: 'Xóa sản phẩm?',
text: "Hành động này không thể hoàn tác",
icon: 'warning',
showCancelButton: true,
confirmButtonText: 'Xóa',
cancelButtonText: 'Hủy',
confirmButtonColor: '#dc3545'
}).then((result) => {

if(result.isConfirmed){
form.submit();
}

});

});

});

});
</script>
@endpush