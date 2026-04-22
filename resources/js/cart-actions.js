document.addEventListener('click', function (e) {

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    const toast = (msg, type = 'success') => {
        if (window.showToast) window.showToast(msg, type);
        if (window.showCenterNotify) window.showCenterNotify(msg, type);
    };

    const isCartPage = () => {
        const path = window.location.pathname;
        return path.includes('cart') || path.includes('gio-hang');
    };

    const clickable = e.target.closest(
        '.js-go-detail, .js-card, .js-category-card'
    );

    if (
        clickable &&
        !e.target.closest('.btn-add-to-cart') &&
        !e.target.closest('.btn-buy-now') &&
        !e.target.closest('.btn-wishlist')
    ) {
        const card = clickable.closest('.js-card, .js-category-card');
        const url = card?.dataset.href;

        if (url) {
            window.location.href = url;
            return;
        }
    }


    const wishBtn = e.target.closest('.btn-wishlist');
    if (wishBtn) {

        e.preventDefault();
        e.stopPropagation();

        const productId = wishBtn.dataset.productId;

        if (!productId) return;

        fetch('/wishlist/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: new URLSearchParams({
                product_id: productId
            })
        })
        .then(res => {
            if (!res.ok) throw new Error();
            return res.json();
        })
        .then(data => {


            const icon = wishBtn.querySelector('i');
            if (icon) {
                if (data.favorited) {
                    icon.classList.remove('bi-heart');
                    icon.classList.add('bi-heart-fill', 'text-danger');
                } else {
                    icon.classList.remove('bi-heart-fill');
                    icon.classList.add('bi-heart');
                }
            }

      
            const countEl = document.getElementById('wishlist-count');
            if (countEl && data.count !== undefined) {
                countEl.innerText = data.count;
            }

            toast(data.message || 'Đã cập nhật yêu thích', 'info');
        })
        .catch(() => {
            toast('Có lỗi xảy ra', 'error');
        });

        return;
    }



    const addBtn = e.target.closest('.btn-add-to-cart');
    if (addBtn) {

        e.preventDefault();
        e.stopPropagation();

        if (addBtn.classList.contains('loading')) return;
        addBtn.classList.add('loading');

        const variantId = addBtn.dataset.variantId;

        if (!variantId) {
            toast('Sản phẩm đã hết hàng', 'error');
            addBtn.classList.remove('loading');
            return;
        }

        fetch('/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: new URLSearchParams({
                variant_id: variantId,
                quantity: 1
            })
        })
        .then(res => {
            if (!res.ok) throw new Error();
            return res.json();
        })
        .then(data => {

            if (data.success) {

                toast(data.message || 'Đã thêm vào giỏ', 'success');

                // Update badge
                const badge =
                    document.querySelector('.cart-count') ||
                    document.querySelector('.cart-badge');

                if (badge && data.cart_count !== undefined) {
                    badge.innerText = data.cart_count;
                }

                // Nếu đang ở trang cart → reload
                if (isCartPage()) {
                    setTimeout(() => location.reload(), 600);
                }

            } else {
                toast(data.message || 'Không thể thêm', 'error');
            }
        })
        .catch(() => {
            toast('Có lỗi xảy ra', 'error');
        })
        .finally(() => {
            addBtn.classList.remove('loading');
        });

        return;
    }


    /* =====================================================
       BUY NOW (AJAX)
    ===================================================== */
    const buyBtn = e.target.closest('.btn-buy-now');
    if (buyBtn) {

        e.preventDefault();
        e.stopPropagation();

        if (buyBtn.classList.contains('loading')) return;
        buyBtn.classList.add('loading');

        const variantId = buyBtn.dataset.variantId;

        if (!variantId) {
            toast('Sản phẩm đã hết hàng', 'error');
            buyBtn.classList.remove('loading');
            return;
        }

        fetch('/checkout/buy-now', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: new URLSearchParams({
                variant_id: variantId
            })
        })
        .then(res => {
            if (!res.ok) throw new Error();
            return res.json();
        })
        .then(data => {

            if (data.success && data.redirect) {
                window.location.href = data.redirect;
            } else {
                toast(data.message || 'Không thể mua ngay', 'error');
            }
        })
        .catch(() => {
            toast('Có lỗi xảy ra', 'error');
        })
        .finally(() => {
            buyBtn.classList.remove('loading');
        });

        return;
    }

});