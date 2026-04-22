document.addEventListener('click', function (e) {

    const btn = e.target.closest('.btn-wishlist');
    if (!btn) return;

    e.preventDefault();
    e.stopImmediatePropagation();

    const productId = btn.dataset.productId;

    fetch('/wishlist/toggle', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: new URLSearchParams({
            product_id: productId
        })
    })
    .then(res => res.json())
    .then(data => {

        if (!data.success) {
            window.showToast?.(data.message || 'Vui lòng đăng nhập', 'error');
            return;
        }

        document.querySelectorAll('.btn-wishlist[data-product-id="'+productId+'"]')
        .forEach(button => {
            const icon = button.querySelector('i');

            if (data.favorited) {
                icon.classList.remove('bi-heart');
                icon.classList.add('bi-heart-fill','text-danger');
            } else {
                icon.classList.remove('bi-heart-fill','text-danger');
                icon.classList.add('bi-heart');
            }
        });

        if (data.favorited) {
            window.showToast?.('Đã thêm vào yêu thích', 'success');
        } else {
            window.showToast?.('Đã bỏ khỏi yêu thích', 'info');
        }
    })
    .catch(() => {
        window.showToast?.('Có lỗi xảy ra', 'error');
    });

});