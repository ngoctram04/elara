@extends('layouts.admin')

@section('title','Chỉnh sửa sản phẩm')

@section('content')
<div class="container-fluid">
<div class="card border-0 shadow-sm">
<div class="card-body p-4">

<h4 class="fw-semibold mb-4">
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
       <input type="hidden" name="is_featured" value="0">

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
            <img id="main-image-preview"
     src="{{ $product->mainImage?->url }}"
     class="img-thumbnail d-block mb-2"
     style="height:120px;object-fit:cover">
        @endif
        <input type="file"
       id="main_image"
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
        <img src="{{ $variant->images->first()?->url }}"
     class="img-thumbnail mt-2 variant-preview"
     style="height:70px;object-fit:cover">
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
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ======================================
        PREVIEW ẢNH PHỤ
    ====================================== */
    const subImagesInput = document.getElementById('sub_images');
    const imageWrapper = document.getElementById('image-wrapper');

    if (subImagesInput) {
        subImagesInput.addEventListener('change', function () {
            imageWrapper.innerHTML = '';

            Array.from(this.files).forEach(file => {
                if (!file.type.startsWith('image/')) return;

                const reader = new FileReader();
                reader.onload = function (e) {
                    imageWrapper.insertAdjacentHTML('beforeend', `
                        <div class="col-3 mb-2">
                            <img src="${e.target.result}"
                                 class="img-thumbnail"
                                 style="height:90px;object-fit:cover">
                        </div>
                    `);
                };
                reader.readAsDataURL(file);
            });
        });
    }


    /* ======================================
        VARIANT
    ====================================== */

    const wrapper = document.getElementById('variant-wrapper');
    const btnAdd = document.getElementById('btn-add-variant');

    // Index bắt đầu từ số variant hiện có
    let variantIndex = wrapper.querySelectorAll('.variant-item').length;

    // Chống bind nhiều lần
    if (btnAdd && !btnAdd.dataset.bound) {
        btnAdd.dataset.bound = "true";

        btnAdd.addEventListener('click', function () {

            const html = `
            <div class="variant-item border rounded p-3 mb-3">

                <div class="row g-2">

                    <div class="col-md-4">
                        <input type="text"
                               name="variants[${variantIndex}][attribute_value]"
                               class="form-control"
                               placeholder="Giá trị"
                               required>
                    </div>

                    <div class="col-md-3">
                        <input type="number"
                               name="variants[${variantIndex}][price]"
                               class="form-control"
                               placeholder="Giá bán"
                               min="0"
                               required>
                    </div>

                    <div class="col-md-3">
                        <input type="text"
                               class="form-control bg-light"
                               value="Tồn: 0"
                               readonly>
                    </div>

                    <div class="col-md-2">
                        <input type="file"
                               name="variants[${variantIndex}][image]"
                               class="form-control variant-image-input"
                               accept="image/*">
                        <img class="img-thumbnail mt-2 variant-preview d-none"
                             style="height:70px">
                    </div>

                </div>

                <small class="text-muted">
                    Tồn kho chỉ thay đổi tại màn hình Nhập hàng
                </small>

                <button type="button"
                        class="btn btn-danger btn-sm mt-2 btn-remove-variant">
                    Xóa
                </button>

            </div>
            `;

            wrapper.insertAdjacentHTML('beforeend', html);
            variantIndex++;
        });
    }


    /* ======================================
        XOÁ VARIANT
    ====================================== */
    wrapper.addEventListener('click', function (e) {
        if (e.target.classList.contains('btn-remove-variant')) {
            e.target.closest('.variant-item').remove();
        }
    });


    /* ======================================
        PREVIEW ẢNH VARIANT
    ====================================== */
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('variant-image-input')) {
            const file = e.target.files[0];
            if (!file || !file.type.startsWith('image/')) return;

            const preview = e.target.closest('.variant-item')
                                    .querySelector('.variant-preview');

            const reader = new FileReader();
            reader.onload = function (ev) {
                preview.src = ev.target.result;
                preview.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        }
    });

});
/* ======================================
   PREVIEW ẢNH CHÍNH
====================================== */
const mainImageInput = document.getElementById('main_image');
const mainPreview = document.getElementById('main-image-preview');

if (mainImageInput) {

    mainImageInput.addEventListener('change', function () {

        const file = this.files[0];
        if (!file || !file.type.startsWith('image/')) return;

        const reader = new FileReader();

        reader.onload = function(e){
            mainPreview.src = e.target.result;
        }

        reader.readAsDataURL(file);

    });

}

</script>
@endpush