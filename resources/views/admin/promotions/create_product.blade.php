@extends('layouts.admin')

@section('title', 'Tạo khuyến mãi sản phẩm')

@section('content')
<form method="POST"
      action="{{ route('admin.promotions.store') }}"
      class="card border-0 shadow-sm">
    @csrf

    <input type="hidden" name="type" value="product">

    <div class="card-body p-4">

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm mb-4">
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

        <div class="mb-4">
            <h5 class="fw-semibold mb-3">Thông tin khuyến mãi</h5>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Tên chương trình</label>
                    <input type="text"
                           name="name"
                           class="form-control"
                           value="{{ old('name') }}"
                           required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Giá trị giảm (%)</label>
                    <input type="number"
                           name="discount_value"
                           class="form-control"
                           value="{{ old('discount_value') }}"
                           min="1"
                           max="100"
                           required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Thời gian bắt đầu</label>
                    <input type="datetime-local"
                           name="start_date"
                           class="form-control"
                           value="{{ old('start_date') }}"
                           required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Thời gian kết thúc</label>
                    <input type="datetime-local"
                           name="end_date"
                           class="form-control"
                           value="{{ old('end_date') }}"
                           required>
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input type="checkbox"
                               name="is_active"
                               id="is_active"
                               value="1"
                               class="form-check-input"
                               {{ old('is_active', true) ? 'checked' : '' }}>
                        <label for="is_active" class="form-check-label">
                            Kích hoạt ngay
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h5 class="fw-semibold mb-1">Chọn sản phẩm / biến thể</h5>
                <small class="text-muted">
                    Danh sách sẽ chỉ hiện khi bạn nhập từ khóa tìm kiếm để tránh xổ quá dài.
                </small>
            </div>

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
                <button type="button" class="btn btn-outline-secondary d-none" id="clearSearchBtn">
                    Xóa
                </button>
            </div>
            <small class="text-muted">Ví dụ: son, serum, 3CE, đỏ nâu, 01, 50ml...</small>
        </div>

        <div id="searchGuide" class="search-guide-box">
            <div class="search-guide-icon">
                <i class="bi bi-search"></i>
            </div>
            <div class="fw-semibold mb-1">Nhập từ khóa để tìm sản phẩm</div>
            <div class="text-muted small">
                Hệ thống sẽ chỉ hiển thị các sản phẩm hoặc biến thể khớp với từ khóa bạn nhập.
            </div>
        </div>

        <div id="productList" class="d-none">
            @foreach ($products as $product)
                @php
                    $oldSelected = collect(old("products.{$product->id}", []))
                        ->map(fn($id) => (int) $id)
                        ->toArray();

                    $checkedCount = count($oldSelected);
                    $shouldOpen = $checkedCount > 0;
                @endphp

                <div class="promotion-product-card d-none"
                     data-product-id="{{ $product->id }}"
                     data-product-name="{{ \Illuminate\Support\Str::lower($product->name) }}">

                    <div class="promotion-product-header">
                        <div class="promotion-product-info d-flex align-items-start gap-3">
                            <div class="promotion-product-thumb">
                                <img src="{{ $product->main_image_url }}"
                                     alt="{{ $product->name }}"
                                     onerror="this.src='{{ asset('images/no-image.png') }}'">
                            </div>

                            <div class="flex-grow-1 min-w-0">
                                <label class="d-flex align-items-center gap-2 mb-1 fw-semibold flex-wrap">
                                    <input type="checkbox"
                                           class="form-check-input product-checkbox"
                                           data-product="{{ $product->id }}">

                                    <span class="product-title">{{ $product->name }}</span>
                                </label>

                                <div class="small text-muted">
                                    {{ $product->variants->count() }} biến thể
                                    <span class="selected-count-wrap d-none">
                                        • Đã chọn <span class="selected-count">0</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="promotion-product-actions">
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary select-all-btn"
                                    data-product="{{ $product->id }}">
                                <i class="bi bi-check2-square me-1"></i>Chọn tất cả kết quả
                            </button>

                            <button type="button"
                                    class="btn btn-sm btn-outline-secondary toggle-btn">
                                <i class="bi {{ $shouldOpen ? 'bi-chevron-up' : 'bi-chevron-down' }} me-1 toggle-icon"></i>
                                <span class="toggle-text">{{ $shouldOpen ? 'Ẩn biến thể' : 'Xem biến thể' }}</span>
                            </button>
                        </div>
                    </div>

                    <div class="promotion-variants {{ $shouldOpen ? '' : 'd-none' }}">
                        @foreach ($product->variants as $variant)
                            @php
                                $isChecked = in_array((int) $variant->id, $oldSelected);
                            @endphp

                            <label class="promotion-variant-row"
                                   data-variant-name="{{ \Illuminate\Support\Str::lower($variant->displayName()) }}">
                                <input type="checkbox"
                                       class="form-check-input mt-1 variant-checkbox"
                                       name="products[{{ $product->id }}][]"
                                       value="{{ $variant->id }}"
                                       data-product="{{ $product->id }}"
                                       {{ $isChecked ? 'checked' : '' }}>

                                <span class="flex-grow-1">
                                    <span class="d-block">{{ $variant->displayName() }}</span>
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

    <div class="card-footer bg-white text-end">
        <a href="{{ route('admin.promotions.index') }}" class="btn btn-light">
            Quay lại
        </a>

        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>
            Lưu khuyến mãi
        </button>
    </div>
</form>

<style>
    .promotion-product-card {
        border: 1px solid #e9ecef;
        border-radius: 14px;
        padding: 16px;
        margin-bottom: 14px;
        background: #fff;
        transition: all .2s ease;
    }

    .promotion-product-card:hover {
        box-shadow: 0 0.125rem 0.6rem rgba(0, 0, 0, .05);
    }

    .promotion-product-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
    }

    .promotion-product-info {
        flex: 1 1 420px;
        min-width: 280px;
    }

    .promotion-product-thumb {
        width: 68px;
        height: 68px;
        flex-shrink: 0;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e9ecef;
        background: #fff;
    }

    .promotion-product-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .product-title {
        word-break: break-word;
    }

    .promotion-product-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .promotion-variants {
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px dashed #e9ecef;
    }

    .promotion-variant-row {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 10px;
        transition: all .15s ease;
        cursor: pointer;
    }

    .promotion-variant-row:hover {
        background: #f8f9fa;
    }

    .search-guide-box {
        border: 1px dashed #d0d7de;
        border-radius: 14px;
        padding: 28px 20px;
        text-align: center;
        background: #fafbfc;
        margin-bottom: 10px;
    }

    .search-guide-icon {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #e9ecef;
        font-size: 22px;
        margin-bottom: 10px;
    }

    @media (max-width: 768px) {
        .promotion-product-thumb {
            width: 56px;
            height: 56px;
        }

        .promotion-product-actions {
            width: 100%;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('promotionSearch');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const searchGuide = document.getElementById('searchGuide');
    const productList = document.getElementById('productList');
    const productBoxes = [...document.querySelectorAll('.promotion-product-card')];
    const emptyResult = document.getElementById('emptySearchResult');
    const expandAllBtn = document.getElementById('expandAllBtn');
    const collapseAllBtn = document.getElementById('collapseAllBtn');

    const normalizeText = (text = '') => {
        return text
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/đ/g, 'd')
            .replace(/[^a-z0-9\s]/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    };

    const getBoxByProductId = (productId) => {
        return document.querySelector(`.promotion-product-card[data-product-id="${productId}"]`);
    };

    const getAvailableVariants = (productId) => {
        const box = getBoxByProductId(productId);
        if (!box) return [];

        return [...box.querySelectorAll(`.variant-checkbox:not(:disabled)`)]
            .filter(item => item.closest('.promotion-variant-row').style.display !== 'none');
    };

    const getAllVariants = (productId) => {
        const box = getBoxByProductId(productId);
        if (!box) return [];

        return [...box.querySelectorAll(`.variant-checkbox:not(:disabled)`)];
    };

    const updateSelectedCount = (productId) => {
        const box = getBoxByProductId(productId);
        if (!box) return;

        const selectedCountWrap = box.querySelector('.selected-count-wrap');
        const selectedCountEl = box.querySelector('.selected-count');
        const allVariants = getAllVariants(productId);
        const checkedCount = allVariants.filter(item => item.checked).length;

        if (checkedCount > 0) {
            selectedCountWrap?.classList.remove('d-none');
            if (selectedCountEl) {
                selectedCountEl.textContent = checkedCount;
            }
        } else {
            selectedCountWrap?.classList.add('d-none');
        }
    };

    const updateProductCheckbox = (productId) => {
        const productCheckbox = document.querySelector(`.product-checkbox[data-product="${productId}"]`);
        const variants = getAvailableVariants(productId);

        if (!productCheckbox) return;

        if (!variants.length) {
            productCheckbox.checked = false;
            productCheckbox.indeterminate = false;
            updateSelectedCount(productId);
            return;
        }

        const checkedCount = variants.filter(item => item.checked).length;

        productCheckbox.checked = checkedCount > 0 && checkedCount === variants.length;
        productCheckbox.indeterminate = checkedCount > 0 && checkedCount < variants.length;

        updateSelectedCount(productId);
    };

    const setBoxOpen = (box, isOpen) => {
        const wrap = box.querySelector('.promotion-variants');
        const icon = box.querySelector('.toggle-icon');
        const text = box.querySelector('.toggle-text');

        if (!wrap) return;

        wrap.classList.toggle('d-none', !isOpen);

        if (icon) {
            icon.classList.remove('bi-chevron-down', 'bi-chevron-up');
            icon.classList.add(isOpen ? 'bi-chevron-up' : 'bi-chevron-down');
        }

        if (text) {
            text.textContent = isOpen ? 'Ẩn biến thể' : 'Xem biến thể';
        }
    };

    const updateAllProducts = () => {
        document.querySelectorAll('.product-checkbox').forEach(item => {
            updateProductCheckbox(item.dataset.product);
        });
    };

    const resetSearchState = () => {
        productList.classList.add('d-none');
        searchGuide.classList.remove('d-none');
        emptyResult.classList.add('d-none');
        clearSearchBtn?.classList.add('d-none');

        productBoxes.forEach(box => {
            box.classList.add('d-none');

            const variantRows = [...box.querySelectorAll('.promotion-variant-row')];
            variantRows.forEach(row => {
                row.style.display = '';
            });

            setBoxOpen(box, false);
        });
    };

    document.querySelectorAll('.product-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const productId = this.dataset.product;
            const variants = getAvailableVariants(productId);

            variants.forEach(item => {
                item.checked = this.checked;
            });

            updateProductCheckbox(productId);
        });
    });

    document.querySelectorAll('.variant-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            updateProductCheckbox(this.dataset.product);
        });
    });

    document.querySelectorAll('.select-all-btn').forEach(button => {
        button.addEventListener('click', function () {
            const productId = this.dataset.product;
            const variants = getAvailableVariants(productId);
            const hasUnchecked = variants.some(item => !item.checked);

            variants.forEach(item => {
                item.checked = hasUnchecked;
            });

            updateProductCheckbox(productId);
        });
    });

    document.querySelectorAll('.toggle-btn').forEach(button => {
        button.addEventListener('click', function () {
            const box = this.closest('.promotion-product-card');
            const wrap = box.querySelector('.promotion-variants');
            const isOpen = !wrap.classList.contains('d-none');

            setBoxOpen(box, !isOpen);
        });
    });

    expandAllBtn?.addEventListener('click', () => {
        productBoxes.forEach(box => {
            if (!box.classList.contains('d-none')) {
                setBoxOpen(box, true);
            }
        });
    });

    collapseAllBtn?.addEventListener('click', () => {
        productBoxes.forEach(box => {
            if (!box.classList.contains('d-none')) {
                setBoxOpen(box, false);
            }
        });
    });

    clearSearchBtn?.addEventListener('click', () => {
        searchInput.value = '';
        resetSearchState();
        searchInput.focus();
    });

    searchInput?.addEventListener('input', function () {
        const keyword = normalizeText(this.value);
        let visibleCount = 0;

        if (!keyword) {
            resetSearchState();
            return;
        }

        productList.classList.remove('d-none');
        searchGuide.classList.add('d-none');
        clearSearchBtn?.classList.remove('d-none');

        productBoxes.forEach(box => {
            const productName = normalizeText(box.dataset.productName);
            const variantRows = [...box.querySelectorAll('.promotion-variant-row')];

            const productMatched = productName.includes(keyword);
            let hasVisibleVariant = false;

            variantRows.forEach(row => {
                const variantName = normalizeText(row.dataset.variantName);
                const matched = productMatched || variantName.includes(keyword);

                row.style.display = matched ? '' : 'none';

                if (matched) {
                    hasVisibleVariant = true;
                }
            });

            const shouldShow = productMatched || hasVisibleVariant;

            box.classList.toggle('d-none', !shouldShow);

            if (shouldShow) {
                visibleCount++;
                setBoxOpen(box, true);
            }
        });

        emptyResult.classList.toggle('d-none', visibleCount > 0);
    });

    updateAllProducts();
    resetSearchState();

    @if(old('products'))
        const hasOldProducts = true;
        if (hasOldProducts) {
            productList.classList.remove('d-none');
            searchGuide.classList.add('d-none');
            emptyResult.classList.add('d-none');

            productBoxes.forEach(box => {
                const checked = box.querySelectorAll('.variant-checkbox:checked').length > 0;
                box.classList.toggle('d-none', !checked);
                if (checked) {
                    setBoxOpen(box, true);
                }
            });
        }
    @endif
});
</script>
@endsection