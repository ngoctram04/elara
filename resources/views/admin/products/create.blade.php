@extends('layouts.admin')

@section('title','Thêm sản phẩm mới')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">

        <h5 class="fw-semibold mb-4">
            <i class="bi bi-plus-square text-primary"></i>
            Thêm sản phẩm mới
        </h5>

        <form method="POST"
              action="{{ route('admin.products.store') }}"
              enctype="multipart/form-data">
            @csrf

            <div class="row">
                {{-- LEFT --}}
                <div class="col-md-6">

                    <div class="mb-3">
                        <label class="form-label">Tên sản phẩm</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Danh mục</label>
                        <select name="category_id"
                                class="form-select"
                                required>
                            <option value="">-- Chọn danh mục --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Thương hiệu</label>
                        <select name="brand_id"
                                class="form-select"
                                required>
                            <option value="">-- Chọn thương hiệu --</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}">
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 🔥 NỔI BẬT --}}
                    <div class="mb-3 form-check">
                        <input type="checkbox"
                               class="form-check-input"
                               id="is_featured"
                               name="is_featured"
                               value="1">
                        <label class="form-check-label"
                               for="is_featured">
                            Sản phẩm nổi bật
                        </label>
                    </div>

                </div>

                {{-- RIGHT --}}
                <div class="col-md-6">

                    <div class="mb-3">
                        <label class="form-label">Mô tả</label>
                        <textarea name="description"
                                  rows="5"
                                  class="form-control"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Hình ảnh sản phẩm</label>

                        <input type="file"
                               name="main_image"
                               class="form-control mb-2"
                               required>

                        <div id="image-wrapper"></div>
                    </div>

                </div>
            </div>

            <hr>

            {{-- ================= BIẾN THỂ ================= --}}
            <h6 class="fw-semibold text-primary mb-3">Biến thể</h6>

            <div class="mb-3">
                <label class="form-label">Loại biến thể</label>
                <input type="text"
                       name="variant_attribute_name"
                       class="form-control"
                       placeholder="VD: Màu sắc / Công dụng / Loại da"
                       required>
            </div>

            <div id="variant-wrapper">

                <div class="variant-item border rounded p-3 mb-3">
                    <div class="row g-2">

                        <div class="col-md-4">
                            <label class="form-label">Giá trị</label>
                            <input type="text"
                                   name="variants[0][attribute_value]"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Giá</label>
                            <input type="number"
                                   name="variants[0][price]"
                                   class="form-control"
                                   min="0"
                                   required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Số lượng</label>
                            <input type="number"
                                   name="variants[0][stock]"
                                   class="form-control"
                                   min="0"
                                   required>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Ảnh</label>
                            <input type="file"
                                   name="variants[0][image]"
                                   class="form-control">
                        </div>

                    </div>

                    <button type="button"
                            class="btn btn-danger btn-sm mt-2 btn-remove-variant">
                        Xóa
                    </button>
                </div>

            </div>

            <button type="button"
                    class="btn btn-outline-success btn-sm mb-4"
                    id="btn-add-variant">
                + Thêm giá trị
            </button>

            <div class="text-end">
                <a href="{{ route('admin.products.index') }}"
                   class="btn btn-outline-danger">
                    Hủy
                </a>

                <button class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Thêm sản phẩm
                </button>
            </div>

        </form>

    </div>
</div>

@push('scripts')
<script>
let variantIndex = 1;

document.getElementById('btn-add-variant').addEventListener('click', () => {
    document.getElementById('variant-wrapper').insertAdjacentHTML('beforeend', `
        <div class="variant-item border rounded p-3 mb-3">
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text"
                           name="variants[${variantIndex}][attribute_value]"
                           class="form-control"
                           required>
                </div>
                <div class="col-md-3">
                    <input type="number"
                           name="variants[${variantIndex}][price]"
                           class="form-control"
                           min="0" required>
                </div>
                <div class="col-md-3">
                    <input type="number"
                           name="variants[${variantIndex}][stock]"
                           class="form-control"
                           min="0" required>
                </div>
                <div class="col-md-2">
                    <input type="file"
                           name="variants[${variantIndex}][image]"
                           class="form-control">
                </div>
            </div>
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
@endsection
