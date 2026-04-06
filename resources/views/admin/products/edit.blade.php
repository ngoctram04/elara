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
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $product->name) }}"
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
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                            data-name="{{ \Illuminate\Support\Str::lower($cat->name) }}"
                                            data-slug="{{ \Illuminate\Support\Str::lower($cat->slug ?? '') }}"
                                            {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
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
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}"
                                        {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('brand_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input type="hidden" name="is_featured" value="0">
                            <input type="checkbox"
                                   class="form-check-input"
                                   name="is_featured"
                                   id="is_featured"
                                   value="1"
                                   {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">Sản phẩm nổi bật</label>
                        </div>

                    </div>

                    {{-- RIGHT --}}
                    <div class="col-lg-6">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mô tả</label>
                            <textarea name="description"
                                      rows="5"
                                      class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- ẢNH CHÍNH --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Ảnh đại diện</label>
                            <div class="mb-2">
                                <img id="main-image-preview"
                                     src="{{ $product->mainImage?->url }}"
                                     class="img-thumbnail {{ $product->mainImage ? '' : 'd-none' }}"
                                     style="height:120px;object-fit:cover">
                            </div>

                            <input type="file"
                                   id="main_image"
                                   name="main_image"
                                   class="form-control @error('main_image') is-invalid @enderror"
                                   accept="image/*">
                            @error('main_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                                   class="form-check-input"
                                                   id="delete_image_{{ $img->id }}">
                                            <label class="form-check-label small" for="delete_image_{{ $img->id }}">Xóa</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

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

                <hr>

                {{-- VARIANTS --}}
                <h5 class="fw-semibold text-primary mb-3">Biến thể</h5>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tên thuộc tính</label>
                    <input type="text"
                           name="variant_attribute_name"
                           class="form-control @error('variant_attribute_name') is-invalid @enderror"
                           value="{{ old('variant_attribute_name', $product->variants->first()?->attribute_name) }}"
                           required>
                    @error('variant_attribute_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @php
                    $oldVariants = old('variants');
                    $variantsData = $oldVariants ?? $product->variants->map(function ($variant) {
                        return [
                            'id' => $variant->id,
                            'attribute_value' => $variant->attribute_value,
                            'price' => $variant->price,
                            'color_code' => $variant->color_code ?: '#d94b70',
                            'stock_quantity' => $variant->stock_quantity,
                            'image_url' => $variant->images->first()?->url,
                        ];
                    })->toArray();
                @endphp

                <div id="variant-wrapper">
                    @foreach($variantsData as $i => $variant)
                        <div class="variant-item border rounded p-3 mb-3">

                            @if(!empty($variant['id']))
                                <input type="hidden"
                                       name="variants[{{ $i }}][id]"
                                       value="{{ $variant['id'] }}">
                            @endif

                            <div class="row g-2 align-items-start">

                                <div class="col-md-3">
                                    <input type="text"
                                           name="variants[{{ $i }}][attribute_value]"
                                           class="form-control @error("variants.$i.attribute_value") is-invalid @enderror"
                                           value="{{ old("variants.$i.attribute_value", $variant['attribute_value'] ?? '') }}"
                                           placeholder="Giá trị"
                                           required>
                                    @error("variants.$i.attribute_value")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-2">
                                    <input type="number"
                                           name="variants[{{ $i }}][price]"
                                           class="form-control @error("variants.$i.price") is-invalid @enderror"
                                           value="{{ old("variants.$i.price", $variant['price'] ?? '') }}"
                                           placeholder="Giá bán"
                                           min="0"
                                           required>
                                    @error("variants.$i.price")
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-2">
                                    <input type="text"
                                           class="form-control bg-light"
                                           value="Tồn: {{ $variant['stock_quantity'] ?? 0 }}"
                                           readonly>
                                </div>

                                <div class="col-md-3 variant-color-col d-none">
                                    <input type="color"
                                           name="variants[{{ $i }}][color_code]"
                                           class="form-control form-control-color w-100 variant-color-input"
                                           value="{{ old("variants.$i.color_code", $variant['color_code'] ?? '#d94b70') }}"
                                           title="Chọn màu son">
                                    <small class="text-muted d-block mt-1">Màu son</small>
                                    @error("variants.$i.color_code")
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-2">
                                    <input type="file"
                                           name="variants[{{ $i }}][image]"
                                           class="form-control variant-image-input"
                                           accept="image/*">
                                    @error("variants.$i.image")
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>

                            <small class="text-muted">
                                Tồn kho chỉ thay đổi tại màn hình Nhập hàng
                            </small>

                            <div class="mt-2">
                                <img src="{{ $variant['image_url'] ?? '' }}"
                                     class="img-thumbnail variant-preview {{ !empty($variant['image_url']) ? '' : 'd-none' }}"
                                     style="height:70px;object-fit:cover">
                            </div>

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
    const categorySelect = document.getElementById('category_id');
    const subImagesInput = document.getElementById('sub_images');
    const imageWrapper = document.getElementById('image-wrapper');
    const wrapper = document.getElementById('variant-wrapper');
    const btnAdd = document.getElementById('btn-add-variant');
    const mainImageInput = document.getElementById('main_image');
    const mainPreview = document.getElementById('main-image-preview');

    let variantIndex = wrapper.querySelectorAll('.variant-item').length;

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
            <div class="variant-item border rounded p-3 mb-3">
                <div class="row g-2 align-items-start">

                    <div class="col-md-3">
                        <input type="text"
                               name="variants[${index}][attribute_value]"
                               class="form-control"
                               placeholder="Giá trị"
                               required>
                    </div>

                    <div class="col-md-2">
                        <input type="number"
                               name="variants[${index}][price]"
                               class="form-control"
                               placeholder="Giá bán"
                               min="0"
                               required>
                    </div>

                    <div class="col-md-2">
                        <input type="text"
                               class="form-control bg-light"
                               value="Tồn: 0"
                               readonly>
                    </div>

                    <div class="col-md-3 variant-color-col ${lipstick ? '' : 'd-none'}">
                        <input type="color"
                               name="variants[${index}][color_code]"
                               class="form-control form-control-color w-100 variant-color-input"
                               value="#d94b70"
                               title="Chọn màu son">
                        <small class="text-muted d-block mt-1">Màu son</small>
                    </div>

                    <div class="col-md-2">
                        <input type="file"
                               name="variants[${index}][image]"
                               class="form-control variant-image-input"
                               accept="image/*">
                    </div>

                </div>

                <small class="text-muted">
                    Tồn kho chỉ thay đổi tại màn hình Nhập hàng
                </small>

                <div class="mt-2">
                    <img class="img-thumbnail variant-preview d-none"
                         style="height:70px;object-fit:cover">
                </div>

                <button type="button"
                        class="btn btn-danger btn-sm mt-2 btn-remove-variant">
                    Xóa
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
                                 class="img-thumbnail"
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
            const file = this.files[0];
            if (!file || !file.type.startsWith('image/')) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                mainPreview.src = e.target.result;
                mainPreview.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        });
    }

    if (btnAdd) {
        btnAdd.addEventListener('click', function () {
            wrapper.insertAdjacentHTML('beforeend', buildVariantHtml(variantIndex));
            variantIndex++;
            updateVariantColorVisibility();
        });
    }

    wrapper.addEventListener('click', function (e) {
        if (e.target.classList.contains('btn-remove-variant')) {
            const items = wrapper.querySelectorAll('.variant-item');
            if (items.length <= 1) return;
            e.target.closest('.variant-item').remove();
        }
    });

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('variant-image-input')) {
            const file = e.target.files[0];
            if (!file || !file.type.startsWith('image/')) return;

            const preview = e.target.closest('.variant-item').querySelector('.variant-preview');

            const reader = new FileReader();
            reader.onload = function (ev) {
                preview.src = ev.target.result;
                preview.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        }
    });

    if (categorySelect) {
        categorySelect.addEventListener('change', updateVariantColorVisibility);
    }

    updateVariantColorVisibility();
});
</script>
@endpush