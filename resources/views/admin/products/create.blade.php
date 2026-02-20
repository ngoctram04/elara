@extends('layouts.admin')

@section('title','Thêm sản phẩm mới')

@section('content')
<div class="container-fluid">
<div class="card border-0 shadow-sm">
<div class="card-body p-4">

<h4 class="fw-semibold mb-4">
    <i class="bi bi-plus-square text-primary"></i>
    Thêm sản phẩm mới
</h4>

<form method="POST"
      action="{{ route('admin.products.store') }}"
      enctype="multipart/form-data">
@csrf

<div class="row">

{{-- LEFT --}}
<div class="col-lg-6">

    <div class="mb-3">
        <label class="form-label fw-semibold">Tên sản phẩm</label>
        <input type="text"
               name="name"
               class="form-control"
               placeholder="Nhập tên sản phẩm"
               required>
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold">Danh mục</label>
        <select name="category_id" class="form-select" required>
            <option value="">-- Chọn danh mục --</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold">Thương hiệu</label>
        <select name="brand_id" class="form-select" required>
            <option value="">-- Chọn thương hiệu --</option>
            @foreach($brands as $brand)
                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-check mb-3">
        <input type="checkbox"
               class="form-check-input"
               id="is_featured"
               name="is_featured"
               value="1">
        <label class="form-check-label" for="is_featured">
            Sản phẩm nổi bật
        </label>
    </div>

</div>

{{-- RIGHT --}}
<div class="col-lg-6">

    <div class="mb-3">
        <label class="form-label fw-semibold">Mô tả</label>
        <textarea name="description"
                  rows="5"
                  class="form-control"
                  placeholder="Mô tả sản phẩm"></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold">Ảnh chính</label>
        <input type="file"
               name="main_image"
               class="form-control"
               accept="image/*"
               required>
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold">Ảnh phụ</label>
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

{{-- VARIANT --}}
<h5 class="fw-semibold text-primary mb-3">Biến thể sản phẩm</h5>

<div class="mb-3">
    <label class="form-label fw-semibold">Tên thuộc tính</label>
    <input type="text"
           name="variant_attribute_name"
           class="form-control"
           placeholder="Ví dụ: Size, Màu"
           required>
</div>

<div id="variant-wrapper">

    <div class="variant-item border rounded p-3 mb-3">
        <div class="row g-2">

            <div class="col-md-5">
                <input type="text"
                       name="variants[0][attribute_value]"
                       class="form-control"
                       placeholder="Giá trị (VD: M, L, Đỏ)"
                       required>
            </div>

            <div class="col-md-5">
                <input type="number"
                       name="variants[0][price]"
                       class="form-control"
                       placeholder="Giá bán"
                       min="0"
                       required>
            </div>

            <div class="col-md-2">
                <input type="file"
                       name="variants[0][image]"
                       class="form-control"
                       accept="image/*">
            </div>

        </div>

        <small class="text-muted">
            Tồn kho sẽ được nhập ở màn hình Nhập hàng
        </small>

        <button type="button"
                class="btn btn-danger btn-sm mt-2 btn-remove-variant">
            Xóa
        </button>
    </div>

</div>

<button type="button"
        class="btn btn-outline-success btn-sm mb-4"
        id="btn-add-variant">
    + Thêm biến thể
</button>

<div class="text-end">
    <a href="{{ route('admin.products.index') }}"
       class="btn btn-light">
        Hủy
    </a>

    <button class="btn btn-primary">
        <i class="bi bi-save"></i> Lưu sản phẩm
    </button>
</div>

</form>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
/* Preview ảnh phụ */
document.getElementById('sub_images').addEventListener('change', function () {
    const wrapper = document.getElementById('image-wrapper');
    wrapper.innerHTML = '';

    [...this.files].forEach(file => {
        if (!file.type.startsWith('image/')) return;

        const reader = new FileReader();
        reader.onload = e => {
            wrapper.insertAdjacentHTML('beforeend', `
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

/* Variant */
let variantIndex = 1;

document.getElementById('btn-add-variant').addEventListener('click', () => {
    document.getElementById('variant-wrapper').insertAdjacentHTML('beforeend', `
        <div class="variant-item border rounded p-3 mb-3">
            <div class="row g-2">
                <div class="col-md-5">
                    <input type="text"
                           name="variants[${variantIndex}][attribute_value]"
                           class="form-control"
                           placeholder="Giá trị"
                           required>
                </div>

                <div class="col-md-5">
                    <input type="number"
                           name="variants[${variantIndex}][price]"
                           class="form-control"
                           placeholder="Giá bán"
                           min="0"
                           required>
                </div>

                <div class="col-md-2">
                    <input type="file"
                           name="variants[${variantIndex}][image]"
                           class="form-control"
                           accept="image/*">
                </div>
            </div>

            <small class="text-muted">
                Tồn kho nhập tại màn hình Nhập hàng
            </small>

            <button type="button"
                    class="btn btn-danger btn-sm mt-2 btn-remove-variant">
                Xóa
            </button>
        </div>
    `);
    variantIndex++;
});

document.addEventListener('click', e => {
    if (e.target.classList.contains('btn-remove-variant')) {
        e.target.closest('.variant-item').remove();
    }
});
</script>
@endpush