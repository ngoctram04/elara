@extends('layouts.admin')

@section('title','Nhập kho')

@section('content')

<div class="card border-0 shadow-sm stock-import-card">
    <div class="card-body">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">Nhập hàng vào kho</h5>
                <small class="text-muted">Tạo phiếu nhập nhiều biến thể sản phẩm</small>
            </div>

            <a href="{{ route('admin.stock.history') }}"
               class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-clock-history me-1"></i>
                Lịch sử nhập
            </a>
        </div>

        {{-- SUCCESS --}}
        @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                showToast(@json(session('success')), "success");
            });
        </script>
        @endif

        {{-- WARNING --}}
        @if(session('warning'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @foreach(session('warning') as $w)
                    showToast(@json($w), "warning");
                @endforeach
            });
        </script>
        @endif

        {{-- ERROR --}}
        @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                @foreach($errors->all() as $err)
                    showToast(@json($err), "error");
                @endforeach
            });
        </script>
        @endif

        {{-- FORM --}}
        <form action="{{ route('admin.stock.store') }}" method="POST" id="importForm">
    @csrf

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label fw-semibold">
                <i class="bi bi-building me-1"></i>Nhà cung cấp
            </label>

            <input type="text"
                   name="supplier"
                   value="{{ old('supplier') }}"
                   class="form-control form-control-sm"
                   placeholder="Ví dụ: Cocoon Việt Nam"
                   required>
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold">
                <i class="bi bi-telephone me-1"></i>Số điện thoại
            </label>

            <input type="text"
                   name="supplier_phone"
                   value="{{ old('supplier_phone') }}"
                   class="form-control form-control-sm"
                   placeholder="Ví dụ: 0901234567">
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold">
                <i class="bi bi-geo-alt me-1"></i>Địa chỉ
            </label>

            <input type="text"
                   name="supplier_address"
                   value="{{ old('supplier_address') }}"
                   class="form-control form-control-sm"
                   placeholder="Ví dụ: Ninh Kiều, Cần Thơ">
        </div>

        <div class="col-12">
            <label class="form-label fw-semibold">
                <i class="bi bi-pencil-square me-1"></i>Ghi chú
            </label>

            <input type="text"
                   name="note"
                   value="{{ old('note') }}"
                   class="form-control form-control-sm"
                   placeholder="Ghi chú cho lô hàng">
        </div>
    </div>

    <hr class="mb-4">

    <h6 class="fw-bold mb-3">Danh sách sản phẩm nhập</h6>

    <div class="table-responsive stock-import-table-wrap">
        <table class="table table-hover align-middle mb-0" id="importTable">
            <thead class="table-light">
                <tr>
                    <th style="width:34%">Biến thể</th>
                    <th style="width:120px">SL</th>
                    <th style="width:150px">Giá nhập</th>
                    <th style="width:170px">Ngày sản xuất</th>
                    <th style="width:170px">Hạn sử dụng</th>
                    <th style="width:60px"></th>
                </tr>
            </thead>

            <tbody>
                <tr class="import-row">
                    <td>
                        <div class="cell-wrapper">
                            <div class="variant-search-box">
                                <input type="hidden"
                                       name="variant_id[]"
                                       class="variant-id"
                                       required>

                                <input type="text"
                                       class="form-control form-control-sm variant-keyword"
                                       placeholder="Tìm theo tên / biến thể"
                                       autocomplete="off">

                                <div class="variant-dropdown d-none"></div>
                            </div>

                            <div class="selected-variant-info small text-muted mt-2"></div>
                            <div class="cell-helper"></div>
                        </div>
                    </td>

                    <td>
                        <div class="cell-wrapper">
                            <input type="number"
                                   name="quantity[]"
                                   class="form-control form-control-sm qty"
                                   min="1"
                                   required>
                            <div class="cell-helper"></div>
                        </div>
                    </td>

                    <td>
                        <div class="cell-wrapper">
                            <input type="number"
                                   name="cost_price[]"
                                   class="form-control form-control-sm price"
                                   min="0"
                                   required>
                            <div class="cell-helper"></div>
                        </div>
                    </td>

                    <td>
                        <div class="cell-wrapper">
                            <input type="date"
                                   name="mfg_date[]"
                                   class="form-control form-control-sm mfg">
                            <div class="cell-helper"></div>
                        </div>
                    </td>

                    <td>
                        <div class="cell-wrapper">
                            <input type="date"
                                   name="expiry_date[]"
                                   class="form-control form-control-sm exp">
                            <div class="expiry-warning"></div>
                        </div>
                    </td>

                    <td class="text-center align-middle">
                        <button type="button"
                                class="btn btn-sm btn-outline-danger removeRow">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-3 mb-3">
        <button type="button"
                id="addRow"
                class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>
            Thêm biến thể
        </button>
    </div>

    <div class="text-end fw-semibold mb-4">
        Tổng tiền nhập:
        <span id="totalCost" class="text-danger">0</span> đ
    </div>

    <div class="d-flex gap-2">
        <button class="btn btn-success btn-sm px-4">
            <i class="bi bi-check-circle me-1"></i>
            Lưu phiếu nhập
        </button>
    </div>
</form>

    </div>
</div>
@php
$variantsForJs = $variants->map(function ($v) {

    $stockText = $v->stock_quantity == 0
        ? '🔴 Hết hàng'
        : ($v->stock_quantity <= 5
            ? '🟡 Sắp hết (' . $v->stock_quantity . ')'
            : '🟢 Tồn: ' . $v->stock_quantity);

    // 🔥 LẤY ĐƯỜNG DẪN ẢNH THÔ
    $rawPath = $v->image_path
        ?? $v->image
        ?? $v->product->image_path
        ?? $v->product->image
        ?? '';

    // 🔥 CHUYỂN THÀNH URL PUBLIC
    $imageUrl = $rawPath
        ? asset('storage/' . ltrim($rawPath, '/'))
        : '';

    return [
        'id' => $v->id,
        'product_name' => $v->product->name ?? '',
        'attribute_value' => $v->attribute_value ?? '',
        'sku' => $v->sku ?? '',
        'stock_quantity' => (int) $v->stock_quantity,
        'stock_text' => $stockText,
        'label' => trim(($v->product->name ?? '') . ' - ' . ($v->attribute_value ?? '')),
        'image' => $imageUrl, // ⭐ URL ĐÚNG
    ];

})->values();
@endphp

<style>
    .stock-import-card,
    .stock-import-card .card-body,
    .stock-import-table-wrap,
    #importTable,
    #importTable tbody,
    #importTable tr,
    #importTable td {
        overflow: visible !important;
    }

    .stock-import-card {
        border-radius: 14px;
    }

    .stock-import-card .card-body {
        padding: 1.25rem 1.25rem 1rem;
    }

    .stock-import-table-wrap {
        position: relative;
    }

    #importTable {
        table-layout: fixed;
    }

    #importTable td,
    #importTable th {
        vertical-align: top;
    }

    .cell-wrapper {
        display: flex;
        flex-direction: column;
        min-height: 76px;
        position: relative;
    }

    .cell-helper {
        height: 18px;
    }

    .expiry-warning {
        font-size: 11px;
        color: #dc3545;
        min-height: 16px;
        line-height: 16px;
        margin-top: 4px;
    }

    .variant-search-box {
        position: relative;
        width: 100%;
    }

    .variant-keyword {
        position: relative;
        z-index: 2;
        background: #fff;
        min-height: 38px;
    }

    .variant-dropdown {
        position: absolute;
        left: 0;
        right: 0;
        bottom: calc(100% + 8px);
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 14px;
        box-shadow: 0 16px 40px rgba(0,0,0,.16);
        z-index: 99999;
        max-height: 380px;
        overflow-y: auto !important;
        padding: 8px 0;
        min-width: 100%;
    }

    .variant-dropdown.d-none {
        display: none !important;
    }

    .variant-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        cursor: pointer;
        border-bottom: 1px solid #f1f3f5;
        background: #fff;
        transition: .15s ease;
    }

    .variant-item:last-child {
        border-bottom: none;
    }

    .variant-item:hover,
    .variant-item.active {
        background: #e9f2ff;
    }

    .variant-thumb {
        width: 52px;
        height: 52px;
        min-width: 52px;
        border-radius: 10px;
        object-fit: cover;
        border: 1px solid #e9ecef;
        background: #f8f9fa;
    }

    .variant-thumb-placeholder {
        width: 52px;
        height: 52px;
        min-width: 52px;
        border-radius: 10px;
        border: 1px dashed #ced4da;
        background: #f8f9fa;
        color: #adb5bd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        text-align: center;
        padding: 4px;
    }

    .variant-item-content {
        flex: 1;
        min-width: 0;
    }

    .variant-item-title {
        font-size: 13px;
        font-weight: 600;
        color: #212529;
        line-height: 1.35;
        word-break: break-word;
    }

    .variant-item-meta {
        font-size: 12px;
        color: #6c757d;
        margin-top: 4px;
        line-height: 1.35;
        word-break: break-word;
    }

    .selected-variant-info {
        min-height: 52px;
        line-height: 1.4;
    }

    .selected-variant-card {
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid #eef1f4;
        background: #fafbfc;
        border-radius: 12px;
        padding: 8px 10px;
    }

    .selected-variant-thumb {
        width: 44px;
        height: 44px;
        min-width: 44px;
        border-radius: 10px;
        object-fit: cover;
        border: 1px solid #e9ecef;
        background: #fff;
    }

    .selected-variant-thumb-placeholder {
        width: 44px;
        height: 44px;
        min-width: 44px;
        border-radius: 10px;
        border: 1px dashed #ced4da;
        background: #fff;
        color: #adb5bd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        text-align: center;
        padding: 4px;
    }

    .selected-variant-text {
        min-width: 0;
        flex: 1;
    }

    .selected-variant-name {
        font-size: 12px;
        font-weight: 600;
        color: #212529;
        line-height: 1.35;
        word-break: break-word;
        margin-bottom: 2px;
    }

    .selected-variant-stock {
        font-size: 12px;
        line-height: 1.35;
    }

    .variant-selected-ok .selected-variant-stock {
        color: #198754 !important;
        font-weight: 600;
    }

    .variant-selected-low .selected-variant-stock {
        color: #fd7e14 !important;
        font-weight: 600;
    }

    .variant-selected-out .selected-variant-stock {
        color: #dc3545 !important;
        font-weight: 600;
    }

    .variant-empty {
        padding: 12px 14px;
        font-size: 13px;
        color: #6c757d;
    }

    .variant-keyword.variant-invalid {
        border: 2px solid #dc3545 !important;
    }

    .variant-keyword.variant-stock-ok {
        border: 2px solid #198754 !important;
    }

    .variant-keyword.variant-stock-low {
        border: 2px solid #fd7e14 !important;
    }

    .variant-keyword.variant-stock-out {
        border: 2px solid #dc3545 !important;
    }

    @media (max-width: 991.98px) {
        .variant-dropdown {
            max-height: 320px;
        }

        .variant-thumb,
        .variant-thumb-placeholder {
            width: 46px;
            height: 46px;
            min-width: 46px;
        }
    }
</style>

<script>
    const variantsData = @json($variantsForJs);

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

    function escapeHtml(str) {
        return (str || '')
            .toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function formatMoney(number) {
        return Number(number || 0).toLocaleString('vi-VN');
    }

    function calculateTotal() {
        let total = 0;

        document.querySelectorAll('#importTable tbody tr').forEach(row => {
            let qty = parseFloat(row.querySelector('.qty')?.value || 0);
            let price = parseFloat(row.querySelector('.price')?.value || 0);
            total += qty * price;
        });

        document.getElementById('totalCost').innerText = formatMoney(total);
    }

    function getVariantSearchText(item) {
        return normalizeText([
            item.product_name,
            item.attribute_value,
            item.sku,
            item.label
        ].join(' '));
    }

    function searchVariants(keyword) {
    const q = normalizeText(keyword);

    if (!q) {
        return [...variantsData]
            .sort((a, b) => {
                // Hết hàng lên đầu
                if (a.stock_quantity === 0 && b.stock_quantity > 0) return -1;
                if (a.stock_quantity > 0 && b.stock_quantity === 0) return 1;

                // Sau đó ưu tiên tồn ít lên trước
                return a.stock_quantity - b.stock_quantity;
            })
            .slice(0, 20);
    }

    const tokens = q.split(' ').filter(Boolean);

    let results = variantsData
        .map(item => {
            const haystack = getVariantSearchText(item);
            const label = normalizeText(item.label);
            const startsWithFull = label.startsWith(q) ? 1 : 0;
            const exactWords = tokens.filter(token => haystack.includes(token)).length;

            return {
                ...item,
                score: (startsWithFull * 100) + exactWords
            };
        })
        .filter(item => tokens.every(token => getVariantSearchText(item).includes(token)))
        .sort((a, b) => {
            // Khi search cũng ưu tiên hết hàng trước
            if (a.stock_quantity === 0 && b.stock_quantity > 0) return -1;
            if (a.stock_quantity > 0 && b.stock_quantity === 0) return 1;

            // Cùng trạng thái kho thì sort theo độ khớp
            if (b.score !== a.score) return b.score - a.score;

            // Nếu score bằng nhau thì tồn ít lên trước
            return a.stock_quantity - b.stock_quantity;
        });

    return results.slice(0, 15);
}

    function getImageHtml(src, className = 'variant-thumb', placeholderClass = 'variant-thumb-placeholder') {
        if (src) {
            return `<img src="${escapeHtml(src)}" alt="variant-image" class="${className}">`;
        }

        return `<div class="${placeholderClass}">No image</div>`;
    }

    function renderDropdown(row, items) {
        const dropdown = row.querySelector('.variant-dropdown');

        if (!items.length) {
            dropdown.innerHTML = `<div class="variant-empty">Không tìm thấy biến thể phù hợp</div>`;
            dropdown.classList.remove('d-none');
            return;
        }

        dropdown.innerHTML = items.map(item => `
            <div class="variant-item" data-id="${item.id}">
                ${getImageHtml(item.image)}
                <div class="variant-item-content">
                    <div class="variant-item-title">
                        ${escapeHtml(item.product_name)} - ${escapeHtml(item.attribute_value)}
                    </div>
                    <div class="variant-item-meta">
                        ${item.sku ? 'SKU: ' + escapeHtml(item.sku) + ' | ' : ''}
                        ${escapeHtml(item.stock_text)}
                    </div>
                </div>
            </div>
        `).join('');

        dropdown.classList.remove('d-none');
    }

    function closeAllDropdowns(exceptBox = null) {
        document.querySelectorAll('.variant-search-box').forEach(box => {
            if (exceptBox && box === exceptBox) return;
            const dropdown = box.querySelector('.variant-dropdown');
            if (dropdown) dropdown.classList.add('d-none');
        });
    }

    function updateVariantBorder(row, type = '') {
        const input = row.querySelector('.variant-keyword');
        input.classList.remove(
            'variant-invalid',
            'variant-stock-ok',
            'variant-stock-low',
            'variant-stock-out'
        );

        if (type) {
            input.classList.add(type);
        }
    }

    function renderSelectedVariantInfo(row, variant) {
        const info = row.querySelector('.selected-variant-info');

        info.className = 'selected-variant-info small mt-2';

        if (variant.stock_quantity == 0) {
            info.classList.add('variant-selected-out');
            updateVariantBorder(row, 'variant-stock-out');
        } else if (variant.stock_quantity <= 5) {
            info.classList.add('variant-selected-low');
            updateVariantBorder(row, 'variant-stock-low');
        } else {
            info.classList.add('variant-selected-ok');
            updateVariantBorder(row, 'variant-stock-ok');
        }

        info.innerHTML = `
            <div class="selected-variant-card">
                ${getImageHtml(variant.image, 'selected-variant-thumb', 'selected-variant-thumb-placeholder')}
                <div class="selected-variant-text">
                    <div class="selected-variant-name">
                        ${escapeHtml(variant.product_name)} - ${escapeHtml(variant.attribute_value)}
                    </div>
                    <div class="selected-variant-stock">
                        ${escapeHtml(variant.stock_text)}
                        ${variant.sku ? ' | SKU: ' + escapeHtml(variant.sku) : ''}
                    </div>
                </div>
            </div>
        `;
    }

    function selectVariant(row, variantId) {
        const variant = variantsData.find(v => String(v.id) === String(variantId));
        if (!variant) return;

        const hiddenInput = row.querySelector('.variant-id');
        const textInput = row.querySelector('.variant-keyword');
        const dropdown = row.querySelector('.variant-dropdown');

        hiddenInput.value = variant.id;
        textInput.value = `${variant.product_name} - ${variant.attribute_value}`;
        textInput.dataset.stock = variant.stock_quantity;

        renderSelectedVariantInfo(row, variant);
        dropdown.classList.add('d-none');

        checkDuplicateVariant();
    }

    function resetVariant(row, keepText = true) {
        const hiddenInput = row.querySelector('.variant-id');
        const textInput = row.querySelector('.variant-keyword');
        const info = row.querySelector('.selected-variant-info');
        const dropdown = row.querySelector('.variant-dropdown');

        hiddenInput.value = '';
        textInput.dataset.stock = '';
        info.innerHTML = '';
        info.className = 'selected-variant-info small text-muted mt-2';

        if (!keepText) {
            textInput.value = '';
        }

        updateVariantBorder(row, '');
        dropdown.innerHTML = '';
        dropdown.classList.add('d-none');

        checkDuplicateVariant();
    }

    function checkDuplicateVariant() {
        const rows = document.querySelectorAll('#importTable tbody tr');
        const countMap = {};

        rows.forEach(row => {
            const value = row.querySelector('.variant-id')?.value;
            if (value) {
                countMap[value] = (countMap[value] || 0) + 1;
            }
        });

        rows.forEach(row => {
            const hiddenInput = row.querySelector('.variant-id');
            const textInput = row.querySelector('.variant-keyword');
            const value = hiddenInput?.value;

            if (!value) return;

            const stock = parseInt(textInput.dataset.stock || 0);

            if (countMap[value] > 1) {
                updateVariantBorder(row, 'variant-invalid');
            } else {
                if (stock === 0) {
                    updateVariantBorder(row, 'variant-stock-out');
                } else if (stock <= 5) {
                    updateVariantBorder(row, 'variant-stock-low');
                } else {
                    updateVariantBorder(row, 'variant-stock-ok');
                }
            }
        });
    }

    function checkExpiry(input) {
        let value = input.value;
        let wrapper = input.closest('.cell-wrapper');
        let warning = wrapper.querySelector('.expiry-warning');
        let row = input.closest('tr');

        input.style.border = "";
        warning.innerText = "";
        row.style.background = "";

        if (!value) return;

        let today = new Date();
        today.setHours(0,0,0,0);

        let expiry = new Date(value);
        let diffTime = expiry - today;
        let diffDays = diffTime / (1000 * 60 * 60 * 24);
        let diffMonths = diffDays / 30;

        if (diffMonths <= 3) {
            input.style.border = "2px solid red";
            warning.innerText = "Hạn sử dụng dưới 3 tháng";
            row.style.background = "#fff5f5";
        } else if (diffMonths <= 6) {
            input.style.border = "2px solid orange";
            warning.innerText = "Hạn sử dụng dưới 6 tháng";
            row.style.background = "#fff8e1";
        }
    }

    function bindVariantSearch(row) {
        const box = row.querySelector('.variant-search-box');
        const textInput = row.querySelector('.variant-keyword');
        const dropdown = row.querySelector('.variant-dropdown');

        textInput.addEventListener('focus', function () {
            closeAllDropdowns(box);
            renderDropdown(row, searchVariants(textInput.value));
        });

        textInput.addEventListener('click', function (e) {
            e.stopPropagation();
            closeAllDropdowns(box);
            renderDropdown(row, searchVariants(textInput.value));
        });

        textInput.addEventListener('input', function () {
            const currentText = textInput.value.trim();

            if (!currentText) {
                resetVariant(row, true);
                closeAllDropdowns(box);
                renderDropdown(row, searchVariants(''));
                return;
            }

            const selectedId = row.querySelector('.variant-id').value;
            if (selectedId) {
                row.querySelector('.variant-id').value = '';
                row.querySelector('.selected-variant-info').innerHTML = '';
                row.querySelector('.selected-variant-info').className = 'selected-variant-info small text-muted mt-2';
                updateVariantBorder(row, '');
            }

            closeAllDropdowns(box);
            renderDropdown(row, searchVariants(currentText));
        });

        textInput.addEventListener('keydown', function (e) {
            const items = dropdown.querySelectorAll('.variant-item');
            let index = Array.from(items).findIndex(item => item.classList.contains('active'));

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (!items.length) return;

                index = (index + 1) % items.length;
                items.forEach(i => i.classList.remove('active'));
                items[index].classList.add('active');
                items[index].scrollIntoView({ block: 'nearest' });
            }

            if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (!items.length) return;

                index = index <= 0 ? items.length - 1 : index - 1;
                items.forEach(i => i.classList.remove('active'));
                items[index].classList.add('active');
                items[index].scrollIntoView({ block: 'nearest' });
            }

            if (e.key === 'Enter') {
                const active = dropdown.querySelector('.variant-item.active');
                if (active) {
                    e.preventDefault();
                    selectVariant(row, active.dataset.id);
                }
            }

            if (e.key === 'Escape') {
                dropdown.classList.add('d-none');
            }
        });

        dropdown.addEventListener('click', function (e) {
            const item = e.target.closest('.variant-item');
            if (!item) return;

            selectVariant(row, item.dataset.id);
        });
    }

    function createNewRow() {
        const firstRow = document.querySelector('#importTable tbody tr');
        const newRow = firstRow.cloneNode(true);

        newRow.querySelectorAll('input').forEach(input => {
            input.value = '';
            input.style.border = '';
            if (input.dataset) {
                delete input.dataset.stock;
            }
        });

        newRow.querySelectorAll('.expiry-warning').forEach(el => {
            el.innerText = '';
        });

        newRow.querySelectorAll('.selected-variant-info').forEach(el => {
            el.innerHTML = '';
            el.className = 'selected-variant-info small text-muted mt-2';
        });

        newRow.querySelectorAll('.variant-dropdown').forEach(el => {
            el.innerHTML = '';
            el.classList.add('d-none');
        });

        newRow.style.background = '';
        bindVariantSearch(newRow);

        return newRow;
    }

    document.getElementById('addRow').addEventListener('click', function () {
        const tbody = document.querySelector('#importTable tbody');
        const row = createNewRow();
        tbody.appendChild(row);
    });

    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('qty') || e.target.classList.contains('price')) {
            calculateTotal();
        }
    });

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.removeRow');
        if (btn) {
            const row = btn.closest('tr');
            const tbody = document.querySelector('#importTable tbody');

            if (tbody.querySelectorAll('tr').length > 1) {
                row.remove();
                calculateTotal();
                checkDuplicateVariant();
            }
            return;
        }

        if (!e.target.closest('.variant-search-box')) {
            closeAllDropdowns();
        }
    });

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('exp')) {
            checkExpiry(e.target);
        }
    });

    document.getElementById('importForm').addEventListener('submit', function (e) {
        let invalid = false;

        document.querySelectorAll('#importTable tbody tr').forEach(row => {
            const hiddenInput = row.querySelector('.variant-id');

            if (!hiddenInput.value) {
                invalid = true;
                updateVariantBorder(row, 'variant-invalid');
            }
        });

        if (invalid) {
            e.preventDefault();
            showToast('Vui lòng chọn đúng biến thể từ danh sách gợi ý', 'error');
            return;
        }

        const ids = Array.from(document.querySelectorAll('.variant-id'))
            .map(i => i.value)
            .filter(Boolean);

        const uniqueIds = [...new Set(ids)];

        if (ids.length !== uniqueIds.length) {
            e.preventDefault();
            showToast('Có biến thể bị trùng, vui lòng kiểm tra lại', 'error');
            checkDuplicateVariant();
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('#importTable tbody tr').forEach(row => {
            bindVariantSearch(row);
        });

        calculateTotal();
    });
</script>

@endsection