@extends('layouts.admin')

@section('title','Thêm sản phẩm mới')

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Thêm sản phẩm mới</h4>
                    <div class="text-muted small">Nhập thông tin cơ bản, hình ảnh và biến thể sản phẩm</div>
                </div>

                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                    Quay lại
                </a>
            </div>

            <form method="POST"
                  action="{{ route('admin.products.store') }}"
                  enctype="multipart/form-data">
                @csrf

                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="border rounded-4 p-4 h-100">
                            <h6 class="fw-bold mb-3">Thông tin cơ bản</h6>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Tên sản phẩm</label>
                                <input type="text"
                                       name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       placeholder="Nhập tên sản phẩm"
                                       value="{{ old('name') }}"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Danh mục</label>
                                <select name="category_id"
                                        id="category_id"
                                        class="form-select @error('category_id') is-invalid @enderror"
                                        required>
                                    <option value="">-- Chọn danh mục --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}"
                                                data-name="{{ \Illuminate\Support\Str::lower($cat->name) }}"
                                                data-slug="{{ \Illuminate\Support\Str::lower($cat->slug ?? '') }}"
                                                {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Thương hiệu</label>
                                <select name="brand_id"
                                        class="form-select @error('brand_id') is-invalid @enderror"
                                        required>
                                    <option value="">-- Chọn thương hiệu --</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}"
                                            {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('brand_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-check">
                                <input type="checkbox"
                                       class="form-check-input"
                                       id="is_featured"
                                       name="is_featured"
                                       value="1"
                                       {{ old('is_featured') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_featured">
                                    Sản phẩm nổi bật
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="border rounded-4 p-4 h-100">
                            <h6 class="fw-bold mb-3">Mô tả và hình ảnh</h6>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Mô tả</label>
                                <textarea name="description"
                                          rows="5"
                                          class="form-control @error('description') is-invalid @enderror"
                                          placeholder="Mô tả sản phẩm">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Ảnh chính</label>
                                <input type="file"
                                       name="main_image"
                                       id="main_image"
                                       class="form-control @error('main_image') is-invalid @enderror"
                                       accept="image/*"
                                       required>
                                @error('main_image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <div class="mt-2" id="main-image-preview"></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Ảnh phụ</label>
                                <input type="file"
                                       id="sub_images"
                                       name="sub_images[]"
                                       class="form-control @error('sub_images.*') is-invalid @enderror"
                                       multiple
                                       accept="image/*">
                                @error('sub_images.*')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="row mt-2" id="image-wrapper"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="border rounded-4 p-4">
                    <h5 class="fw-bold text-primary mb-3">Biến thể sản phẩm</h5>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên thuộc tính</label>
                        <input type="text"
                               name="variant_attribute_name"
                               class="form-control @error('variant_attribute_name') is-invalid @enderror"
                               placeholder="Ví dụ: Size, Màu"
                               value="{{ old('variant_attribute_name') }}"
                               required>
                        @error('variant_attribute_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @php
                        $oldVariants = old('variants', [
                            ['attribute_value' => '', 'price' => '', 'color_code' => '#d94b70']
                        ]);
                    @endphp

                    <div id="variant-wrapper">
                        @foreach($oldVariants as $i => $variant)
                            <div class="variant-item border rounded-4 p-3 mb-3 bg-light-subtle">
                                <div class="row g-3 align-items-start">

                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold text-muted">Giá trị</label>
                                        <input type="text"
                                               name="variants[{{ $i }}][attribute_value]"
                                               class="form-control @error("variants.$i.attribute_value") is-invalid @enderror"
                                               placeholder="VD: Đỏ lạnh, Hồng đất"
                                               value="{{ old("variants.$i.attribute_value", $variant['attribute_value'] ?? '') }}"
                                               required>
                                        @error("variants.$i.attribute_value")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label small fw-semibold text-muted">Giá bán</label>
                                        <input type="number"
                                               name="variants[{{ $i }}][price]"
                                               class="form-control @error("variants.$i.price") is-invalid @enderror"
                                               placeholder="Giá bán"
                                               min="0"
                                               value="{{ old("variants.$i.price", $variant['price'] ?? '') }}"
                                               required>
                                        @error("variants.$i.price")
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3 variant-color-col d-none">
                                        <label class="form-label small fw-semibold text-muted">Màu son</label>
                                        <input type="color"
                                               name="variants[{{ $i }}][color_code]"
                                               class="form-control form-control-color w-100 variant-color-input @error("variants.$i.color_code") is-invalid @enderror"
                                               value="{{ old("variants.$i.color_code", $variant['color_code'] ?? '#d94b70') }}"
                                               title="Chọn màu son">
                                        @error("variants.$i.color_code")
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label small fw-semibold text-muted">Ảnh biến thể</label>
                                        <input type="file"
                                               name="variants[{{ $i }}][image]"
                                               class="form-control variant-image"
                                               accept="image/*">
                                        @error("variants.$i.image")
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror

                                        <div class="variant-preview mt-2"></div>
                                    </div>
                                </div>

                                <small class="text-muted d-block mt-2">
                                    Tồn kho sẽ được nhập ở màn hình Nhập hàng
                                </small>

                                <button type="button"
                                        class="btn btn-outline-danger btn-sm mt-3 btn-remove-variant">
                                    Xóa biến thể
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <button type="button"
                            class="btn btn-outline-success btn-sm mb-4"
                            id="btn-add-variant">
                        <i class="bi bi-plus-lg me-1"></i> Thêm biến thể
                    </button>
                </div>

                <div class="text-end mt-4">
                    <a href="{{ route('admin.products.index') }}" class="btn btn-light">
                        Hủy
                    </a>

                    <button class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Lưu sản phẩm
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
    const categorySelect = document.getElementById('category_id');
    const subImagesInput = document.getElementById('sub_images');
    const imageWrapper = document.getElementById('image-wrapper');
    const btnAdd = document.getElementById('btn-add-variant');
    const variantWrapper = document.getElementById('variant-wrapper');
    const mainImageInput = document.getElementById('main_image');
    const mainPreview = document.getElementById('main-image-preview');

    let variantIndex = variantWrapper.querySelectorAll('.variant-item').length;

    function isLipstickCategory() {
        if (!categorySelect) return false;
        const selected = categorySelect.options[categorySelect.selectedIndex];
        if (!selected) return false;

        const name = (selected.dataset.name || '').toLowerCase();
        const slug = (selected.dataset.slug || '').toLowerCase();

        return name.includes('son') || slug.includes('son');
    }

    function updateVariantColorVisibility() {
        const lipstick = isLipstickCategory();

        document.querySelectorAll('.variant-item').forEach(item => {
            const colorCol = item.querySelector('.variant-color-col');
            const colorInput = item.querySelector('.variant-color-input');

            if (!colorCol || !colorInput) return;

            if (lipstick) {
                colorCol.classList.remove('d-none');
            } else {
                colorCol.classList.add('d-none');
                colorInput.value = '#d94b70';
            }
        });
    }

    function buildVariantHtml(index) {
        const lipstick = isLipstickCategory();

        return `
            <div class="variant-item border rounded-4 p-3 mb-3 bg-light-subtle">
                <div class="row g-3 align-items-start">

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted">Giá trị</label>
                        <input type="text"
                               name="variants[${index}][attribute_value]"
                               class="form-control"
                               placeholder="VD: Đỏ lạnh, Hồng đất"
                               required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted">Giá bán</label>
                        <input type="number"
                               name="variants[${index}][price]"
                               class="form-control"
                               placeholder="Giá bán"
                               min="0"
                               required>
                    </div>

                    <div class="col-md-3 variant-color-col ${lipstick ? '' : 'd-none'}">
                        <label class="form-label small fw-semibold text-muted">Màu son</label>
                        <input type="color"
                               name="variants[${index}][color_code]"
                               class="form-control form-control-color w-100 variant-color-input"
                               value="#d94b70"
                               title="Chọn màu son">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted">Ảnh biến thể</label>
                        <input type="file"
                               name="variants[${index}][image]"
                               class="form-control variant-image"
                               accept="image/*">
                        <div class="variant-preview mt-2"></div>
                    </div>

                </div>

                <small class="text-muted d-block mt-2">
                    Tồn kho sẽ được nhập ở màn hình Nhập hàng
                </small>

                <button type="button"
                        class="btn btn-outline-danger btn-sm mt-3 btn-remove-variant">
                    Xóa biến thể
                </button>
            </div>
        `;
    }

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
                                 class="img-thumbnail rounded-3"
                                 style="height:90px;object-fit:cover">
                        </div>
                    `);
                };
                reader.readAsDataURL(file);
            });
        });
    }

    if (mainImageInput) {
        mainImageInput.addEventListener('change', function () {
            mainPreview.innerHTML = '';

            const file = this.files[0];
            if (!file || !file.type.startsWith('image/')) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                mainPreview.innerHTML = `
                    <img src="${e.target.result}"
                         class="img-thumbnail rounded-3"
                         style="height:120px;object-fit:cover">
                `;
            };
            reader.readAsDataURL(file);
        });
    }

    if (btnAdd) {
        btnAdd.addEventListener('click', function (e) {
            e.preventDefault();
            variantWrapper.insertAdjacentHTML('beforeend', buildVariantHtml(variantIndex));
            variantIndex++;
            updateVariantColorVisibility();
        });
    }

    variantWrapper.addEventListener('click', function (e) {
        if (e.target.classList.contains('btn-remove-variant')) {
            const items = variantWrapper.querySelectorAll('.variant-item');
            if (items.length <= 1) return;
            e.target.closest('.variant-item').remove();
        }
    });

    document.addEventListener('change', function(e) {
        if (!e.target.classList.contains('variant-image')) return;

        const input = e.target;
        const wrapper = input.closest('.variant-item');
        const preview = wrapper.querySelector('.variant-preview');

        preview.innerHTML = '';

        const file = input.files[0];
        if (!file || !file.type.startsWith('image/')) return;

        const reader = new FileReader();
        reader.onload = function(ev) {
            preview.innerHTML = `
                <img src="${ev.target.result}"
                     class="img-thumbnail rounded-3"
                     style="height:90px;object-fit:cover">
            `;
        };
        reader.readAsDataURL(file);
    });

    if (categorySelect) {
        categorySelect.addEventListener('change', updateVariantColorVisibility);
    }

    updateVariantColorVisibility();
});
</script>
@endpush