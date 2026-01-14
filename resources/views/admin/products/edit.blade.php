@extends('layouts.admin')

@section('title','Chỉnh sửa sản phẩm')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">

        <h5 class="fw-semibold mb-4">
            <i class="bi bi-pencil-square text-warning"></i>
            Chỉnh sửa sản phẩm
        </h5>

        <form method="POST"
              action="{{ route('admin.products.update', $product) }}"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                {{-- LEFT --}}
                <div class="col-md-6">

                    <div class="mb-3">
                        <label class="form-label">Tên sản phẩm</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               value="{{ old('name', $product->name) }}"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Danh mục</label>
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
                        <label class="form-label">Thương hiệu</label>
                        <select name="brand_id" class="form-select" required>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}"
                                    {{ $product->brand_id == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select name="is_active" class="form-select">
                            <option value="1" {{ $product->is_active ? 'selected' : '' }}>Còn hàng</option>
                            <option value="0" {{ !$product->is_active ? 'selected' : '' }}>Ẩn</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sản phẩm nổi bật</label>
                        <select name="is_featured" class="form-select">
                            <option value="0" {{ !$product->is_featured ? 'selected' : '' }}>Không</option>
                            <option value="1" {{ $product->is_featured ? 'selected' : '' }}>Có</option>
                        </select>
                    </div>
                </div>

                {{-- RIGHT --}}
                <div class="col-md-6">

                    <div class="mb-3">
                        <label class="form-label">Mô tả sản phẩm</label>
                        <textarea name="description"
                                  rows="5"
                                  class="form-control">{{ old('description', $product->description) }}</textarea>
                    </div>

                    {{-- ẢNH --}}
                    <div class="mb-3">
                        <label class="form-label">Ảnh đại diện</label>

                        @if($product->mainImage)
                            <div class="mb-2">
                                <img src="{{ $product->mainImage->url }}"
                                     width="120"
                                     class="rounded border">
                            </div>
                        @endif

                        <input type="file" name="main_image" class="form-control mb-2">

                        {{-- ẢNH PHỤ --}}
                        <div id="image-wrapper"></div>

                        <button type="button"
                                class="btn btn-outline-primary btn-sm mt-2"
                                id="btn-add-image">
                            + Thêm hình ảnh
                        </button>

                        <small class="text-muted d-block mt-1">
                            Không chọn ảnh mới → giữ ảnh cũ
                        </small>
                    </div>
                </div>
            </div>

            <hr>

            {{-- BIẾN THỂ --}}
            <h6 class="fw-semibold text-primary mb-3">Biến thể</h6>

            <div id="variant-wrapper">
                @foreach($product->variants as $index => $variant)
                    <div class="variant-item border rounded p-3 mb-3">
                        <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id }}">

                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="text"
                                       name="variants[{{ $index }}][attribute_value]"
                                       class="form-control"
                                       value="{{ $variant->attribute_value }}">
                                <input type="hidden"
                                       name="variants[{{ $index }}][attribute_name]"
                                       value="{{ $variant->attribute_name }}">
                            </div>

                            <div class="col-md-3">
                                <input type="number"
                                       name="variants[{{ $index }}][price]"
                                       class="form-control"
                                       value="{{ $variant->price }}">
                            </div>

                            <div class="col-md-3">
                                <input type="number"
                                       name="variants[{{ $index }}][stock]"
                                       class="form-control"
                                       value="{{ $variant->stock }}">
                            </div>

                            <div class="col-md-2">
                                <input type="file"
                                       name="variants[{{ $index }}][image]"
                                       class="form-control">
                            </div>
                        </div>

                        @if($variant->images->first())
                            <img src="{{ $variant->images->first()->url }}"
                                 width="80"
                                 class="border rounded mt-2">
                        @endif

                        <button type="button"
                                class="btn btn-danger btn-sm mt-2 btn-remove-variant">
                            Xóa biến thể
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
                   class="btn btn-outline-danger">✕ Hủy</a>

                <button class="btn btn-primary">
                    <i class="bi bi-save"></i> Cập nhật sản phẩm
                </button>
            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ========== ẢNH PHỤ ========== */
    const btnAddImage = document.getElementById('btn-add-image');
    const imageWrapper = document.getElementById('image-wrapper');

    btnAddImage?.addEventListener('click', () => {
        imageWrapper.insertAdjacentHTML('beforeend', `
            <div class="d-flex gap-2 mt-2">
                <input type="file" name="images[]" class="form-control">
                <button type="button" class="btn btn-danger btn-sm btn-remove-image">✕</button>
            </div>
        `);
    });

    /* ========== BIẾN THỂ ========== */
    const variantWrapper = document.getElementById('variant-wrapper');
    const btnAddVariant = document.getElementById('btn-add-variant');
    let variantIndex = variantWrapper.children.length;

    btnAddVariant?.addEventListener('click', () => {
        variantWrapper.insertAdjacentHTML('beforeend', `
            <div class="variant-item border rounded p-3 mb-3">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="variants[${variantIndex}][attribute_value]"
                               class="form-control" placeholder="VD: 500ml">
                        <input type="hidden" name="variants[${variantIndex}][attribute_name]"
                               value="Dung tích">
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="variants[${variantIndex}][price]"
                               class="form-control">
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="variants[${variantIndex}][stock]"
                               class="form-control">
                    </div>
                    <div class="col-md-2">
                        <input type="file" name="variants[${variantIndex}][image]"
                               class="form-control">
                    </div>
                </div>
                <button type="button"
                        class="btn btn-danger btn-sm mt-2 btn-remove-variant">
                    Xóa biến thể
                </button>
            </div>
        `);
        variantIndex++;
    });

    document.addEventListener('click', e => {
        if (e.target.classList.contains('btn-remove-image')) {
            e.target.parentElement.remove();
        }
        if (e.target.classList.contains('btn-remove-variant')) {
            e.target.closest('.variant-item').remove();
        }
    });

});
</script>
@endpush
