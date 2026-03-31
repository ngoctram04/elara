const money = n => new Intl.NumberFormat('vi-VN').format(n) + 'đ';

let appliedCode = null;
let removeId = null;

function updateCartBadge() {
    let total = 0;

    document.querySelectorAll('.js-qty').forEach(input => {
        total += parseInt(input.value) || 0;
    });

    const badge = document.querySelector('.cart-badge');
    if (!badge) return;

    if (total <= 0) {
        badge.style.display = 'none';
        return;
    }

    badge.style.display = 'inline-block';
    badge.innerText = total > 99 ? '99+' : total;
}

function recalcTotal() {
    let subtotal = 0;
    let count = 0;

    const voucherRow = document.getElementById('summary-voucher-row');
    const voucherEl = document.getElementById('summary-voucher');
    const bdRow = document.getElementById('summary-birthday-row');
    const bdEl = document.getElementById('summary-birthday');

    document.querySelectorAll('.js-check-item:checked').forEach(cb => {
        const id = cb.value;
        const row = document.querySelector(`tr[data-row="${id}"]`);
        if (!row) return;

        const sub = row.querySelector('.js-subtotal');
        const qty = row.querySelector('.js-qty');

        subtotal += Number(sub?.dataset.value || 0);
        count += Number(qty?.value || 0);
    });

    if (subtotal === 0) {
        appliedCode = null;

        if (voucherRow) voucherRow.classList.add('d-none');
        if (voucherEl) {
            voucherEl.dataset.value = 0;
            voucherEl.innerText = '-0đ';
        }

        const appliedBox = document.getElementById('voucher-applied');
        if (appliedBox) {
            appliedBox.innerText = '';
            appliedBox.classList.add('d-none');
        }

        const hidden = document.getElementById('promotion-code-hidden');
        if (hidden) hidden.value = '';
    }

    const countEl = document.querySelector('.js-count');
    if (countEl) countEl.innerText = count;

    const subEl = document.getElementById('summary-subtotal');
    if (subEl) {
        subEl.innerText = money(subtotal);
        subEl.dataset.value = subtotal;
    }

    let voucher = appliedCode ? Number(voucherEl?.dataset.value || 0) : 0;

    if (voucher > 0 && subtotal > 0) {
        voucherRow?.classList.remove('d-none');
    } else {
        voucherRow?.classList.add('d-none');
        voucher = 0;
    }

    let percent = Number(bdEl?.dataset.percent || 0);
    let birthday = Math.round(subtotal * percent / 100);

    if (birthday > 0 && subtotal > 0) {
        bdRow?.classList.remove('d-none');
        bdEl.innerText = '-' + money(birthday);
    } else {
        bdRow?.classList.add('d-none');
        birthday = 0;
    }

    const savingRow = document.getElementById('summary-saving-row');
    const savingEl = document.getElementById('summary-saving');
    const saving = voucher + birthday;

    if (saving > 0) {
        savingRow?.classList.remove('d-none');
        savingEl.innerText = '-' + money(saving);
    } else {
        savingRow?.classList.add('d-none');
        savingEl.innerText = '-0đ';
    }

    const finalTotal = Math.max(0, subtotal - saving);

    const totalEl = document.querySelector('.js-total');
    if (totalEl) {
        totalEl.innerText = money(finalTotal);
        totalEl.dataset.value = finalTotal;
        totalEl.dataset.subtotal = subtotal;
    }
}

function updateQty(id, qty) {
    fetch(window.cartRoutes.changeQty, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.cartConfig.csrfToken,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            variant_id: id,
            quantity: qty
        })
    });
}

function applyVoucher(code) {
    const subEl = document.getElementById('summary-subtotal');
    const total = Number(subEl?.dataset.value || 0);

    if (total <= 0) {
        showToast('Vui lòng chọn sản phẩm trước khi áp dụng voucher', 'error');
        return;
    }

    fetch(window.cartRoutes.applyPromotion, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.cartConfig.csrfToken,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            code: code,
            total: total
        })
    })
    .then(res => res.json())
    .then(res => {
        if (!res.success) {
            appliedCode = null;

            const voucherRow = document.getElementById('summary-voucher-row');
            const voucherEl = document.getElementById('summary-voucher');

            if (voucherRow) voucherRow.classList.add('d-none');
            if (voucherEl) {
                voucherEl.dataset.value = 0;
                voucherEl.innerText = '-0đ';
            }

            recalcTotal();
            showToast(res.message || 'Mã không hợp lệ', 'error');
            return;
        }

        appliedCode = code;

        const voucherEl = document.getElementById('summary-voucher');
        const voucherRow = document.getElementById('summary-voucher-row');

        if (voucherEl) {
            voucherEl.dataset.value = res.discount;
            voucherEl.innerText = '-' + money(res.discount);
        }

        if (voucherRow) {
            voucherRow.classList.remove('d-none');
        }

        const box = document.getElementById('voucher-applied');
        if (box) {
            box.innerText = `Đã áp dụng: ${res.name} (-${money(res.discount)})`;
            box.classList.remove('d-none');
        }

        const hidden = document.getElementById('promotion-code-hidden');
        if (hidden) {
            hidden.value = code;
        }

        const modalEl = document.getElementById('voucherModal');
        if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal?.hide();
        }

        recalcTotal();
    })
    .catch(() => {
        showToast('Có lỗi xảy ra, vui lòng thử lại', 'error');
    });
}

function getCheckedSubtotal() {
    let subtotal = 0;

    document.querySelectorAll('.js-check-item:checked').forEach(cb => {
        const row = document.querySelector(`tr[data-row="${cb.value}"]`);
        const sub = row?.querySelector('.js-subtotal');
        subtotal += Number(sub?.dataset.value || 0);
    });

    return subtotal;
}

function calculateDiscount(voucher, subtotal) {
    const type = voucher.dataset.type;
    const value = Number(voucher.dataset.value || 0);
    const min = Number(voucher.dataset.min || 0);
    const max = Number(voucher.dataset.max || 0);

    if (subtotal < min) return 0;

    let discount = 0;

    if (type === 'percent') {
        discount = subtotal * value / 100;
        if (max > 0) {
            discount = Math.min(discount, max);
        }
    } else {
        discount = value;
    }

    return Math.round(discount);
}

document.getElementById('check-all')?.addEventListener('change', function () {
    document.querySelectorAll('.js-check-item').forEach(cb => {
        cb.checked = this.checked;
    });
    recalcTotal();
});

document.addEventListener('change', function (e) {
    if (e.target.classList.contains('js-check-item')) {
        const allItems = document.querySelectorAll('.js-check-item');
        const checkedItems = document.querySelectorAll('.js-check-item:checked');
        const checkAll = document.getElementById('check-all');

        if (checkAll) {
            checkAll.checked = allItems.length === checkedItems.length;
        }

        recalcTotal();
    }
});

document.addEventListener('click', e => {
    const plus = e.target.closest('.js-plus');
    const minus = e.target.closest('.js-minus');

    if (plus || minus) {
        const btn = plus || minus;
        const id = btn.dataset.id;
        const row = document.querySelector(`tr[data-row="${id}"]`);
        if (!row) return;

        const input = row.querySelector('.js-qty');
        let qty = parseInt(input.value) || 1;
        const stock = parseInt(input.dataset.stock);
        const price = parseInt(input.dataset.price);

        if (btn.classList.contains('js-plus')) {
            if (qty >= stock) {
                showToast('Chỉ còn ' + stock + ' sản phẩm', 'error');
                return;
            }
            qty++;
        } else {
            if (qty <= 1) {
                removeId = id;
                document.getElementById('confirm-delete-box')?.classList.remove('d-none');
                return;
            }
            qty--;
        }

        input.value = qty;
        updateQty(id, qty);

        const sub = row.querySelector('.js-subtotal');
        sub.dataset.value = price * qty;
        sub.innerText = money(price * qty);

        recalcTotal();
        updateCartBadge();
    }
});

document.addEventListener('click', function (e) {
    const removeBtn = e.target.closest('.js-remove');
    if (removeBtn) {
        removeId = removeBtn.dataset.id;
        document.getElementById('confirm-delete-box')?.classList.remove('d-none');
        return;
    }

    if (e.target.id === 'confirm-delete-no') {
        document.getElementById('confirm-delete-box')?.classList.add('d-none');
        removeId = null;
        return;
    }

    if (e.target.id === 'confirm-delete-yes' && removeId) {
        const id = removeId;

        document.getElementById('confirm-delete-box')?.classList.add('d-none');
        showToast('Đang xóa sản phẩm...', 'info');

        fetch(`${window.cartRoutes.remove}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Delete failed');
            return res.json().catch(() => ({}));
        })
        .then(() => {
            const row = document.querySelector(`tr[data-row="${id}"]`);
            if (row) row.remove();

            updateCartBadge();
            showToast('Đã xóa sản phẩm', 'success');

            const remainingRows = document.querySelectorAll('tbody tr');
            if (remainingRows.length === 0) {
                setTimeout(() => {
                    location.reload();
                }, 500);
                return;
            }

            recalcTotal();
        })
        .catch(() => {
            showToast('Không thể xóa sản phẩm', 'error');
        });

        removeId = null;
    }
});

document.addEventListener('change', e => {
    if (e.target.classList.contains('js-qty')) {
        const input = e.target;
        const id = input.dataset.id;
        const row = input.closest('tr');

        let qty = input.value.trim();
        const stock = parseInt(input.dataset.stock);
        const price = parseInt(input.dataset.price);

        if (qty === '') qty = 1;
        qty = parseInt(qty);

        if (isNaN(qty) || qty < 1) qty = 1;

        if (qty > stock) {
            qty = stock;
            showToast('Chỉ còn ' + stock + ' sản phẩm', 'error');
        }

        input.value = qty;
        updateQty(id, qty);

        const sub = row.querySelector('.js-subtotal');
        sub.dataset.value = price * qty;
        sub.innerText = money(price * qty);

        recalcTotal();
        updateCartBadge();
    }
});

document.querySelectorAll('.js-change-variant').forEach(select => {
    select.onchange = () => {
        const row = select.closest('tr');
        const oldId = select.dataset.old;
        const newId = select.value;

        if (oldId == newId) return;

        const option = select.options[select.selectedIndex];
        const stockCheck = parseInt(option.dataset.stock);

        if (stockCheck <= 0) {
            showToast('Biến thể này đã hết hàng', 'error');
            select.value = oldId;
            return;
        }

        row.style.opacity = 0.5;

        fetch(window.cartRoutes.changeVariant, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.cartConfig.csrfToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                old_variant_id: oldId,
                new_variant_id: newId
            })
        })
        .then(res => res.json())
        .then(res => {
            row.style.opacity = 1;

            if (!res.success) {
                showToast(res.message || 'Không thể đổi biến thể', 'error');
                select.value = oldId;
                return;
            }

            const price = parseInt(res.price);
            const stock = parseInt(res.stock);
            const qty = parseInt(res.quantity);
            const newIdServer = res.new_id;

            const existingRow = document.querySelector(`tr[data-row="${newIdServer}"]`);

            if (existingRow && existingRow !== row) {
                const existingInput = existingRow.querySelector('.js-qty');
                existingInput.value = qty;
                existingInput.dataset.stock = stock;
                existingInput.dataset.price = price;

                const sub = existingRow.querySelector('.js-subtotal');
                sub.dataset.value = price * qty;
                sub.innerText = money(price * qty);

                row.remove();
                recalcTotal();
                updateCartBadge();
                return;
            }

            row.dataset.row = newIdServer;
            select.dataset.old = newIdServer;

            const checkbox = row.querySelector('.js-check-item');
            if (checkbox) checkbox.value = newIdServer;

            const input = row.querySelector('.js-qty');
            input.value = qty;
            input.dataset.id = newIdServer;
            input.dataset.price = price;
            input.dataset.stock = stock;

            row.querySelector('.js-plus').dataset.id = newIdServer;
            row.querySelector('.js-minus').dataset.id = newIdServer;
            row.querySelector('.js-remove').dataset.id = newIdServer;

            const meta = row.querySelector('.product-meta');
            if (meta) {
                if (res.original_price && res.original_price > price) {
                    meta.innerHTML = `
                        <span class="old-price">${money(res.original_price)}</span>
                        <span class="final-price">${money(price)}</span>
                    `;
                } else {
                    meta.innerHTML = `
                        <span class="final-price">${money(price)}</span>
                    `;
                }
            }

            const sub = row.querySelector('.js-subtotal');
            sub.dataset.id = newIdServer;
            sub.dataset.value = price * qty;
            sub.innerText = money(price * qty);

            const variantMobile = row.querySelector('.variant-tag');
            if (variantMobile) {
                variantMobile.innerText = res.variant;
            }

            const stockText = row.querySelector('.js-stock-text');
            if (stockText) {
                if (stock <= 5) {
                    stockText.innerHTML = `<span class="badge bg-danger">Sắp hết (${stock})</span>`;
                } else {
                    stockText.innerHTML = `Còn ${stock}`;
                }
            }

            const img = row.querySelector('.cart-img');
            if (img && res.image) {
                img.src = '/storage/' + res.image;
            }

            recalcTotal();
            updateCartBadge();
        })
        .catch(() => {
            row.style.opacity = 1;
            showToast('Có lỗi xảy ra khi đổi biến thể', 'error');
            select.value = oldId;
        });
    };
});

document.getElementById('checkout-form')?.addEventListener('submit', function (e) {
    const container = document.getElementById('selected-items');
    container.innerHTML = '';

    const checked = document.querySelectorAll('.js-check-item:checked');

    if (checked.length === 0) {
        e.preventDefault();
        showToast('Vui lòng chọn sản phẩm để thanh toán', 'error');
        return;
    }

    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'variant_ids[]';
        input.value = cb.value;
        container.appendChild(input);
    });
});

document.addEventListener('click', function (e) {
    const item = e.target.closest('.voucher-item');
    if (item) {
        const code = item.dataset.code;
        applyVoucher(code);
    }
});

window.addEventListener('cartUpdated', function () {
    location.reload();
});

document.getElementById('voucherModal')?.addEventListener('show.bs.modal', function () {
    const subtotal = getCheckedSubtotal();

    if (subtotal <= 0) {
        showToast('Vui lòng chọn sản phẩm trước', 'error');
        return;
    }

    const container = this.querySelector('.modal-body');
    const vouchers = Array.from(container.querySelectorAll('.voucher-item'));

    vouchers.forEach(v => {
        v.classList.remove('border-primary');
        v.style.background = '';
        const oldBadge = v.querySelector('.best-badge');
        if (oldBadge) oldBadge.remove();
    });

    vouchers.forEach(voucher => {
        const discount = calculateDiscount(voucher, subtotal);
        voucher.dataset.discount = discount;

        const preview = voucher.querySelector('.discount-preview');

        if (discount > 0) {
            preview.innerText = `Giảm ${money(discount)}`;
            voucher.style.opacity = 1;
            voucher.style.pointerEvents = 'auto';
        } else {
            preview.innerText = `Không đủ điều kiện`;
            voucher.style.opacity = 0.5;
            voucher.style.pointerEvents = 'none';
        }
    });

    vouchers.sort((a, b) => Number(b.dataset.discount) - Number(a.dataset.discount));
    vouchers.forEach(v => container.appendChild(v));

    const bestVoucher = vouchers.find(v => Number(v.dataset.discount) > 0);

    if (bestVoucher) {
        bestVoucher.classList.add('border-primary');
        bestVoucher.style.background = '#f0f8ff';

        const badge = document.createElement('span');
        badge.className = 'badge bg-primary best-badge ms-2';
        badge.innerText = 'Phù hợp nhất';

        bestVoucher.querySelector('.voucher-name')?.appendChild(badge);
    }
});

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-check-item').forEach(cb => {
        cb.checked = false;
    });

    const checkAll = document.getElementById('check-all');
    if (checkAll) checkAll.checked = false;

    recalcTotal();
    updateCartBadge();
});