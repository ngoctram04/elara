@extends('layouts.admin')

@section('title', 'Chỉnh sửa khuyến mãi sản phẩm')

@section('content')
<form method="POST"
      action="{{ route('admin.promotions.update', $promotion) }}"
      class="card shadow-sm border-0">

    @csrf
    @method('PUT')

    <input type="hidden" name="discount_type" value="percent">

    <div class="card-body">

        {{-- ERROR --}}
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm">
                <div class="fw-semibold mb-2">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Có lỗi xảy ra
                </div>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <h5 class="fw-semibold mb-3">Thông tin khuyến mãi</h5>

        <div class="row g-3 mb-4">

            <div class="col-md-6">
                <label class="form-label">Tên chương trình</label>
                <input
                    type="text"
                    name="name"
                    class="form-control"
                    value="{{ old('name', $promotion->name) }}"
                    required
                >
            </div>

            <div class="col-md-6">
                <label class="form-label">Giá trị giảm (%)</label>
                <input
                    type="number"
                    name="discount_value"
                    class="form-control"
                    min="1"
                    max="100"
                    step="1"
                    value="{{ old('discount_value', (int) $promotion->discount_value) }}"
                    required
                >
                <small class="text-muted">
                    Nhập số nguyên từ 1 đến 100 (%)
                </small>
            </div>

            <div class="col-md-6">
                <label class="form-label">Thời gian áp dụng</label>
                <div class="d-flex gap-2">
                    <input
                        type="datetime-local"
                        name="start_date"
                        class="form-control"
                        value="{{ old('start_date', $promotion->start_date->format('Y-m-d\TH:i')) }}"
                        required
                    >
                    <input
                        type="datetime-local"
                        name="end_date"
                        class="form-control"
                        value="{{ old('end_date', $promotion->end_date->format('Y-m-d\TH:i')) }}"
                        required
                    >
                </div>
            </div>

            <div class="col-12">
                <label class="form-check-label">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        class="form-check-input me-1"
                        {{ old('is_active', $promotion->is_active) ? 'checked' : '' }}
                    >
                    Kích hoạt
                </label>
            </div>
        </div>

        <hr>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h5 class="fw-semibold mb-0">Sản phẩm / biến thể áp dụng</h5>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="expandAllBtn">
                    <i class="bi bi-arrows-expand me-1"></i>Mở tất cả
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="collapseAllBtn">
                    <i class="bi bi-arrows-collapse me-1"></i>Thu gọn
                </button>
            </div>
        </div>

        <div class="mb-3">
            <div class="input-group">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text"
                       id="promotionSearch"
                       class="form-control"
                       placeholder="Tìm theo tên sản phẩm hoặc biến thể...">
            </div>
            <small class="text-muted">
                Gõ từ khóa để lọc nhanh danh sách sản phẩm và biến thể.
            </small>
        </div>

        <div id="productList">
            @foreach ($products as $product)
                @php
                    $productVariantIds = $product->variants->pluck('id')->toArray();

                    $selectedVariantIds = collect(old("products.{$product->id}", []))->isNotEmpty()
                        ? collect(old("products.{$product->id}", []))->map(fn($id) => (int) $id)->toArray()
                        : $selected->where('product_id', $product->id)->pluck('variant_id')->map(fn($id) => (int) $id)->toArray();

                    $checkedCount = count($selectedVariantIds);

                    $lockedCount = collect($productVariantIds)
                        ->filter(fn($id) => in_array($id, $activeVariantIds ?? []))
                        ->count();

                    $allLocked = count($productVariantIds) > 0 && $lockedCount === count($productVariantIds);

                    $shouldOpen = $checkedCount > 0;
                @endphp

                <div class="border rounded-3 p-3 mb-3 product-box {{ $allLocked ? 'opacity-75 bg-light' : '' }}"
                     data-product-name="{{ \Illuminate\Support\Str::lower($product->name) }}"
                     data-product-id="{{ $product->id }}">

                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div class="flex-grow-1">
                            <label class="fw-semibold d-flex align-items-center gap-2 mb-1">
                                <input type="checkbox"
                                       class="form-check-input product-checkbox mt-0"
                                       data-product="{{ $product->id }}"
                                       {{ $allLocked ? 'disabled' : '' }}>

                                <span>{{ $product->name }}</span>

                                @if($allLocked)
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                        Đang có khuyến mãi khác
                                    </span>
                                @endif
                            </label>

                            <div class="small text-muted">
                                {{ $product->variants->count() }} biến thể
                                @if($checkedCount > 0)
                                    • Đã chọn {{ $checkedCount }}
                                @endif
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary select-all-variants"
                                    data-product="{{ $product->id }}"
                                    {{ $allLocked ? 'disabled' : '' }}>
                                <i class="bi bi-check2-square me-1"></i>Chọn tất cả
                            </button>

                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary toggle-variants">
                                <i class="bi {{ $shouldOpen ? 'bi-chevron-up' : 'bi-chevron-down' }} me-1 toggle-icon"></i>
                                <span class="toggle-text">{{ $shouldOpen ? 'Ẩn biến thể' : 'Xem biến thể' }}</span>
                            </button>
                        </div>
                    </div>

                    <div class="variants-wrap ms-0 mt-3 {{ $shouldOpen ? '' : 'd-none' }}">
                        @foreach ($product->variants as $variant)
                            @php
                                $isLocked = in_array($variant->id, $activeVariantIds ?? []);
                                $isChecked = in_array($variant->id, $selectedVariantIds);
                            @endphp

                            <label class="d-flex align-items-start gap-2 py-2 px-2 rounded variant-row {{ $isLocked ? 'text-muted opacity-50 bg-light' : '' }}"
                                   data-variant-name="{{ \Illuminate\Support\Str::lower($variant->displayName()) }}">
                                <input
                                    type="checkbox"
                                    class="form-check-input mt-1 variant-checkbox"
                                    name="products[{{ $product->id }}][]"
                                    value="{{ $variant->id }}"
                                    data-product="{{ $product->id }}"
                                    {{ $isLocked ? 'disabled' : '' }}
                                    {{ $isChecked ? 'checked' : '' }}
                                >

                                <span class="flex-grow-1">
                                    <span class="d-block">{{ $variant->displayName() }}</span>

                                    @if($isLocked)
                                        <span class="small text-danger">
                                            <i class="bi bi-lock me-1"></i>Biến thể này đang có khuyến mãi khác
                                        </span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div id="emptySearchResult" class="text-center text-muted py-4 d-none">
            <i class="bi bi-search fs-4 d-block mb-2"></i>
            Không tìm thấy sản phẩm hoặc biến thể phù hợp
        </div>
    </div>

    <div class="card-footer text-end">
        <a href="{{ route('admin.promotions.index') }}"
           class="btn btn-light">
            Quay lại
        </a>

        <button class="btn btn-primary">
            <i class="bi bi-save me-1"></i>
            Cập nhật
        </button>
    </div>
</form>

<style>
    .product-box {
        transition: .2s ease;
    }

    .product-box:hover {
        box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, .06);
    }

    .variant-row {
        border: 1px solid transparent;
        transition: .15s ease;
    }

    .variant-row:hover {
        background: #f8f9fa;
        border-color: #e9ecef;
    }

    .variants-wrap {
        border-top: 1px dashed #e9ecef;
        padding-top: 12px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('promotionSearch');
    const productBoxes = document.querySelectorAll('.product-box');
    const emptyResult = document.getElementById('emptySearchResult');

    function normalizeText(str) {
        return (str || '')
            .toString()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/đ/g, 'd')
            .replace(/[^a-z0-9\s]/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function updateProductCheckboxState(productId) {
        const productCheckbox = document.querySelector(`.product-checkbox[data-product="${productId}"]`);
        const variants = document.querySelectorAll(`.variant-checkbox[data-product="${productId}"]:not(:disabled)`);

        if (!productCheckbox || !variants.length) return;

        const checkedVariants = Array.from(variants).filter(v => v.checked);

        productCheckbox.checked = checkedVariants.length > 0 && checkedVariants.length === variants.length;
        productCheckbox.indeterminate = checkedVariants.length > 0 && checkedVariants.length < variants.length;
    }

    function updateAllProductCheckboxStates() {
        document.querySelectorAll('.product-checkbox').forEach(productCheckbox => {
            updateProductCheckboxState(productCheckbox.dataset.product);
        });
    }

    function setVariantsWrapState(box, open) {
        const wrap = box.querySelector('.variants-wrap');
        const toggleText = box.querySelector('.toggle-text');
        const toggleIcon = box.querySelector('.toggle-icon');

        if (!wrap) return;

        wrap.classList.toggle('d-none', !open);

        if (toggleText) {
            toggleText.textContent = open ? 'Ẩn biến thể' : 'Xem biến thể';
        }

        if (toggleIcon) {
            toggleIcon.classList.remove('bi-chevron-down', 'bi-chevron-up');
            toggleIcon.classList.add(open ? 'bi-chevron-up' : 'bi-chevron-down');
        }
    }

    document.querySelectorAll('.product-checkbox').forEach(productCheckbox => {
        productCheckbox.addEventListener('change', function () {
            const productId = this.dataset.product;

            document.querySelectorAll(`.variant-checkbox[data-product="${productId}"]:not(:disabled)`)
                .forEach(variant => {
                    variant.checked = this.checked;
                });

            updateProductCheckboxState(productId);
        });
    });

    document.querySelectorAll('.variant-checkbox').forEach(variantCheckbox => {
        variantCheckbox.addEventListener('change', function () {
            const productId = this.dataset.product;
            updateProductCheckboxState(productId);
        });
    });

    document.querySelectorAll('.select-all-variants').forEach(button => {
        button.addEventListener('click', function () {
            const productId = this.dataset.product;
            const variants = document.querySelectorAll(`.variant-checkbox[data-product="${productId}"]:not(:disabled)`);
            const unchecked = Array.from(variants).filter(v => !v.checked);
            const shouldCheck = unchecked.length > 0;

            variants.forEach(variant => {
                variant.checked = shouldCheck;
            });

            updateProductCheckboxState(productId);
        });
    });

    document.querySelectorAll('.toggle-variants').forEach(button => {
        button.addEventListener('click', function () {
            const box = this.closest('.product-box');
            const wrap = box.querySelector('.variants-wrap');
            const isOpen = !wrap.classList.contains('d-none');

            setVariantsWrapState(box, !isOpen);
        });
    });

    document.getElementById('expandAllBtn')?.addEventListener('click', function () {
        document.querySelectorAll('.product-box').forEach(box => {
            if (box.style.display !== 'none') {
                setVariantsWrapState(box, true);
            }
        });
    });

    document.getElementById('collapseAllBtn')?.addEventListener('click', function () {
        document.querySelectorAll('.product-box').forEach(box => {
            setVariantsWrapState(box, false);
        });
    });

    searchInput?.addEventListener('input', function () {
        const keyword = normalizeText(this.value);
        let visibleCount = 0;

        productBoxes.forEach(box => {
            const productName = normalizeText(box.dataset.productName || '');
            const variantRows = box.querySelectorAll('.variant-row');
            let productMatched = !keyword || productName.includes(keyword);
            let variantMatched = false;

            variantRows.forEach(row => {
                const variantName = normalizeText(row.dataset.variantName || '');
                const matched = !keyword || productMatched || variantName.includes(keyword);

                row.style.display = matched ? '' : 'none';

                if (matched) {
                    variantMatched = true;
                }
            });

            const shouldShow = !keyword || productMatched || variantMatched;
            box.style.display = shouldShow ? '' : 'none';

            if (shouldShow) {
                visibleCount++;
            }

            if (keyword && shouldShow) {
                setVariantsWrapState(box, true);
            }
        });

        emptyResult.classList.toggle('d-none', visibleCount > 0);
    });

    updateAllProductCheckboxStates();
});
</script>
@endsection