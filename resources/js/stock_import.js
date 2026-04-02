const variantsData = window.variantsData || [];
const stockImportRoutes = window.stockImportRoutes || {};

let supplierResults = [];
let supplierActiveIndex = -1;
let supplierFetchTimer = null;

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
        const qty = parseFloat(row.querySelector('.qty')?.value || 0);
        const price = parseFloat(row.querySelector('.price')?.value || 0);
        total += qty * price;
    });

    const totalCost = document.getElementById('totalCost');
    if (totalCost) {
        totalCost.innerText = formatMoney(total);
    }
}

function getVariantSearchText(item) {
    return normalizeText([
        item.product_name,
        item.attribute_value,
        item.label
    ].join(' '));
}

function getStockBadgeHtml(item) {
    const badgeClass = item.stock_class === 'out'
        ? 'stock-badge stock-badge-out'
        : item.stock_class === 'low'
            ? 'stock-badge stock-badge-low'
            : 'stock-badge stock-badge-ok';

    const iconClass = item.stock_class === 'out'
        ? 'bi-x-circle'
        : item.stock_class === 'low'
            ? 'bi-exclamation-circle'
            : 'bi-check-circle';

    return `
        <span class="${badgeClass}">
            <i class="bi ${iconClass}"></i>
            ${escapeHtml(item.stock_text)}
        </span>
    `;
}

function searchVariants(keyword) {
    const q = normalizeText(keyword);

    if (!q) {
        return [...variantsData]
            .sort((a, b) => {
                if (a.stock_quantity === 0 && b.stock_quantity > 0) return -1;
                if (a.stock_quantity > 0 && b.stock_quantity === 0) return 1;
                return a.stock_quantity - b.stock_quantity;
            })
            .slice(0, 20);
    }

    const tokens = q.split(' ').filter(Boolean);

    const results = variantsData
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
            if (a.stock_quantity === 0 && b.stock_quantity > 0) return -1;
            if (a.stock_quantity > 0 && b.stock_quantity === 0) return 1;
            if (b.score !== a.score) return b.score - a.score;
            return a.stock_quantity - b.stock_quantity;
        });

    return results.slice(0, 15);
}

function getImageHtml(src, className = 'variant-thumb', placeholderClass = 'variant-thumb-placeholder') {
    if (src) {
        return `<img src="${escapeHtml(src)}" alt="variant-image" class="${className}">`;
    }

    return `
        <div class="${placeholderClass}">
            <i class="bi bi-image"></i>
        </div>
    `;
}

function renderDropdown(row, items) {
    const dropdown = row.querySelector('.variant-dropdown');
    if (!dropdown) return;

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
    ${getStockBadgeHtml(item)}
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
    if (!input) return;

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
    if (!info) return;

    info.className = 'selected-variant-info small mt-2';

    if (variant.stock_quantity === 0) {
        updateVariantBorder(row, 'variant-stock-out');
    } else if (variant.stock_quantity <= 5) {
        updateVariantBorder(row, 'variant-stock-low');
    } else {
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
    ${getStockBadgeHtml(variant)}
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

    if (!hiddenInput || !textInput || !dropdown) return;

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

    if (!hiddenInput || !textInput || !info || !dropdown) return;

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

        if (!value || !textInput) return;

        const stock = parseInt(textInput.dataset.stock || 0, 10);

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
    const value = input.value;
    const wrapper = input.closest('.cell-wrapper');
    const warning = wrapper?.querySelector('.expiry-warning');
    const row = input.closest('tr');

    input.style.border = '';
    if (warning) warning.innerText = '';
    if (row) row.style.background = '';

    if (!value) return;

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const expiry = new Date(value);
    const diffTime = expiry - today;
    const diffDays = diffTime / (1000 * 60 * 60 * 24);
    const diffMonths = diffDays / 30;

    if (diffMonths <= 3) {
        input.style.border = '2px solid red';
        if (warning) warning.innerText = 'Hạn sử dụng dưới 3 tháng';
        if (row) row.style.background = '#fff5f5';
    } else if (diffMonths <= 6) {
        input.style.border = '2px solid orange';
        if (warning) warning.innerText = 'Hạn sử dụng dưới 6 tháng';
        if (row) row.style.background = '#fff8e1';
    }
}

function bindVariantSearch(row) {
    const box = row.querySelector('.variant-search-box');
    const textInput = row.querySelector('.variant-keyword');
    const dropdown = row.querySelector('.variant-dropdown');

    if (!box || !textInput || !dropdown) return;

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

        const selectedId = row.querySelector('.variant-id')?.value;
        if (selectedId) {
            const hiddenInput = row.querySelector('.variant-id');
            const info = row.querySelector('.selected-variant-info');

            if (hiddenInput) hiddenInput.value = '';
            if (info) {
                info.innerHTML = '';
                info.className = 'selected-variant-info small text-muted mt-2';
            }

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

/* Supplier autocomplete */
function renderSupplierDropdown(items) {
    const dropdown = document.getElementById('supplierDropdown');
    if (!dropdown) return;

    if (!items.length) {
        dropdown.innerHTML = '';
        dropdown.classList.add('d-none');
        supplierActiveIndex = -1;
        return;
    }

    dropdown.innerHTML = items.map((item, index) => `
        <div class="supplier-item" data-index="${index}">
            <div class="supplier-name">
                <i class="bi bi-building me-1"></i>${escapeHtml(item.supplier || '')}
            </div>

            <div class="supplier-meta-row">
                <i class="bi bi-telephone"></i>
                <span>${escapeHtml(item.supplier_phone || 'Chưa có số điện thoại')}</span>
            </div>

            <div class="supplier-meta-row">
                <i class="bi bi-geo-alt"></i>
                <span>${escapeHtml(item.supplier_address || 'Chưa có địa chỉ')}</span>
            </div>
        </div>
    `).join('');

    dropdown.classList.remove('d-none');
    supplierActiveIndex = -1;
}

function closeSupplierDropdown() {
    const dropdown = document.getElementById('supplierDropdown');
    if (!dropdown) return;

    dropdown.classList.add('d-none');
    supplierActiveIndex = -1;
}

function fillSupplierInfo(item) {
    const supplierInput = document.getElementById('supplierInput');
    const supplierPhone = document.getElementById('supplierPhone');
    const supplierAddress = document.getElementById('supplierAddress');

    if (supplierInput) supplierInput.value = item.supplier || '';
    if (supplierPhone) supplierPhone.value = item.supplier_phone || '';
    if (supplierAddress) supplierAddress.value = item.supplier_address || '';

    closeSupplierDropdown();
}

function highlightSupplierItem() {
    const items = document.querySelectorAll('#supplierDropdown .supplier-item');

    items.forEach((item, index) => {
        item.classList.toggle('active', index === supplierActiveIndex);
    });

    if (supplierActiveIndex >= 0 && items[supplierActiveIndex]) {
        items[supplierActiveIndex].scrollIntoView({ block: 'nearest' });
    }
}

async function fetchSuppliers(keyword) {
    if (!stockImportRoutes.searchSuppliers) return;

    try {
        const response = await fetch(
            `${stockImportRoutes.searchSuppliers}?q=${encodeURIComponent(keyword)}`,
            {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        );

        if (!response.ok) {
            throw new Error('Không thể tải danh sách nhà cung cấp');
        }

        const data = await response.json();
        supplierResults = Array.isArray(data) ? data : [];
        renderSupplierDropdown(supplierResults);
    } catch (error) {
        console.error(error);
        supplierResults = [];
        closeSupplierDropdown();
    }
}

function bindSupplierAutocomplete() {
    const supplierInput = document.getElementById('supplierInput');
    const dropdown = document.getElementById('supplierDropdown');

    if (!supplierInput || !dropdown) return;

    supplierInput.addEventListener('input', function () {
        const keyword = this.value.trim();

        clearTimeout(supplierFetchTimer);

        if (!keyword) {
            closeSupplierDropdown();
            return;
        }

        supplierFetchTimer = setTimeout(() => {
            fetchSuppliers(keyword);
        }, 250);
    });

    supplierInput.addEventListener('focus', function () {
        const keyword = this.value.trim();
        if (keyword) {
            fetchSuppliers(keyword);
        }
    });

    supplierInput.addEventListener('keydown', function (e) {
        const items = document.querySelectorAll('#supplierDropdown .supplier-item');
        if (!items.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            supplierActiveIndex = (supplierActiveIndex + 1) % items.length;
            highlightSupplierItem();
        }

        if (e.key === 'ArrowUp') {
            e.preventDefault();
            supplierActiveIndex = supplierActiveIndex <= 0 ? items.length - 1 : supplierActiveIndex - 1;
            highlightSupplierItem();
        }

        if (e.key === 'Enter') {
            if (supplierActiveIndex >= 0 && supplierResults[supplierActiveIndex]) {
                e.preventDefault();
                fillSupplierInfo(supplierResults[supplierActiveIndex]);
            }
        }

        if (e.key === 'Escape') {
            closeSupplierDropdown();
        }
    });

    dropdown.addEventListener('click', function (e) {
        const item = e.target.closest('.supplier-item');
        if (!item) return;

        const index = Number(item.dataset.index);
        if (supplierResults[index]) {
            fillSupplierInfo(supplierResults[index]);
        }
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

document.addEventListener('DOMContentLoaded', function () {
    bindSupplierAutocomplete();

    document.querySelectorAll('#importTable tbody tr').forEach(row => {
        bindVariantSearch(row);
    });

    calculateTotal();

    const addRowBtn = document.getElementById('addRow');
    if (addRowBtn) {
        addRowBtn.addEventListener('click', function () {
            const tbody = document.querySelector('#importTable tbody');
            const row = createNewRow();
            tbody.appendChild(row);
        });
    }

    const importForm = document.getElementById('importForm');
    if (importForm) {
        importForm.addEventListener('submit', function (e) {
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
    }
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

    if (!e.target.closest('.supplier-autocomplete')) {
        closeSupplierDropdown();
    }
});

document.addEventListener('change', function (e) {
    if (e.target.classList.contains('exp')) {
        checkExpiry(e.target);
    }
});