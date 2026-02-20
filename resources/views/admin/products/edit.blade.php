@extends('layouts.admin')

@section('title','Chỉnh sửa sản phẩm')

@section('content')
<div class="container-fluid">
<div class="card border-0 shadow-sm">
<div class="card-body p-4">

<h4 class="fw-semibold mb-4">
    <i class="bi bi-pencil-square text-warning"></i>
    Chỉnh sửa sản phẩm
</h4>

<form method="POST"
      action="{{ route('admin.products.update', $product) }}"
      enctype="multipart/form-data">
@csrf
@method('PUT')

<div class="row">

{{-- LEFT --}}
<div class="col-lg-6">

    <div class="mb-3">
        <label class="form-label fw-semibold">Tên sản phẩm</label>
        <input type="text"
               name="name"
               class="form-control"
               value="{{ old('name', $product->name) }}"
               required>
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold">Danh mục</label>
        <select name="category_id" class="form-select" required>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}"
                    {{ $product->category_id == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold">Thương hiệu</label>
        <select name="brand_id" class="form-select" required>
            @foreach($brands as $brand)
                <option value="{{ $brand->id }}"
                    {{ $product->brand_id == $brand->id ? 'selected' : '' }}>
                    {{ $brand->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-check mb-3">
        <input type="checkbox"
               class="form-check-input"
               name="is_featured"
               value="1"
               {{ $product->is_featured ? 'checked' : '' }}>
        <label class="form-check-label">Sản phẩm nổi bật</label>
    </div>

</div>

{{-- RIGHT --}}
<div class="col-lg-6">

    <div class="mb-3">
        <label class="form-label fw-semibold">Mô tả</label>
        <textarea name="description"
                  rows="5"
                  class="form-control">{{ old('description', $product->description) }}</textarea>
    </div>

    {{-- ẢNH CHÍNH --}}
    <div class="mb-3">
        <label class="form-label fw-semibold">Ảnh đại diện</label>
        @if($product->mainImage)
            <img src="{{ $product->mainImage->url }}"
                 class="img-thumbnail d-block mb-2"
                 style="height:120px">
        @endif
        <input type="file"
               name="main_image"
               class="form-control"
               accept="image/*">
    </div>

    {{-- ẢNH PHỤ --}}
    <div class="mb-3">
        <label class="form-label fw-semibold">Ảnh phụ</label>

        <div class="row mb-2">
            @foreach($product->subImages as $img)
                <div class="col-3 mb-3 text-center">
                    <img src="{{ $img->url }}"
                         class="img-thumbnail mb-1"
                         style="height:80px;object-fit:cover">
                    <div class="form-check">
                        <input type="checkbox"
                               name="delete_images[]"
                               value="{{ $img->id }}"
                               class="form-check-input">
                        <label class="form-check-label small">Xóa</label>
                    </div>
                </div>
            @endforeach
        </div>

        <input type="file"
               id="sub_images"
               name="sub_images[]"
               class="form-control"
               multiple
               accept="image/*">

        <div class="row mt-2" id="image-wrapper"></div>
    </div>

</div>
</div>

<hr>

{{-- VARIANTS --}}
<h5 class="fw-semibold text-primary mb-3">Biến thể</h5>

<div class="mb-3">
    <label class="form-label fw-semibold">Tên thuộc tính</label>
    <input type="text"
           name="variant_attribute_name"
           class="form-control"
           value="{{ $product->variants->first()?->attribute_name }}"
           required>
</div>

<div id="variant-wrapper">
@foreach($product->variants as $i => $variant)
<div class="variant-item border rounded p-3 mb-3">

    <input type="hidden"
           name="variants[{{ $i }}][id]"
           value="{{ $variant->id }}">

    <div class="row g-2">

        <div class="col-md-4">
            <input type="text"
                   name="variants[{{ $i }}][attribute_value]"
                   class="form-control"
                   value="{{ $variant->attribute_value }}"
                   required>
        </div>

        <div class="col-md-3">
            <input type="number"
                   name="variants[{{ $i }}][price]"
                   class="form-control"
                   value="{{ $variant->price }}"
                   min="0"
                   required>
        </div>

        {{-- CHỈ HIỂN THỊ TỒN --}}
        <div class="col-md-3">
            <input type="text"
                   class="form-control bg-light"
                   value="Tồn: {{ $variant->stock_quantity }}"
                   readonly>
        </div>

        <div class="col-md-2">
            <input type="file"
                   name="variants[{{ $i }}][image]"
                   class="form-control variant-image-input"
                   accept="image/*">
        </div>

    </div>

    <small class="text-muted">
        Tồn kho chỉ thay đổi tại màn hình Nhập hàng
    </small>

    @if($variant->images->first())
        <img src="{{ $variant->images->first()->url }}"
             class="img-thumbnail mt-2 variant-preview"
             style="height:70px">
    @endif

    <button type="button"
            class="btn btn-danger btn-sm mt-2 btn-remove-variant">
        Xóa
    </button>

</div>
@endforeach
</div>

<button type="button"
        class="btn btn-outline-success btn-sm mb-4"
        id="btn-add-variant">
    + Thêm biến thể
</button>

<div class="text-end">
    <a href="{{ route('admin.products.index') }}"
       class="btn btn-light">Hủy</a>
    <button class="btn btn-primary">
        <i class="bi bi-save"></i> Cập nhật sản phẩm
    </button>
</div>

</form>
</div>
</div>
</div>
@endsection