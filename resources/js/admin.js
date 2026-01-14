document.addEventListener('DOMContentLoaded', () => {

    /* ==================================================
        SIDEBAR TOGGLE (CODE CỦA BẠN – GIỮ NGUYÊN)
    ================================================== */
    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar   = document.getElementById('sidebar');

    if (sidebar) {
        const overlay = document.createElement('div');
        overlay.classList.add('sidebar-overlay');
        document.body.appendChild(overlay);

        const closeSidebar = () => {
            sidebar.classList.remove('show');
            overlay.classList.remove('active');
        };

        const toggleSidebar = () => {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('active');
        };

        if (toggleBtn) {
            toggleBtn.addEventListener('click', toggleSidebar);
        }

        overlay.addEventListener('click', closeSidebar);

        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                closeSidebar();
            }
        });
    }

    /* ==================================================
        THÊM ẢNH PHỤ SẢN PHẨM (+ Thêm hình ảnh)
    ================================================== */
    const btnAddImage = document.getElementById('btn-add-image');
    const imageWrapper = document.getElementById('image-wrapper');

    if (btnAddImage && imageWrapper) {
        btnAddImage.addEventListener('click', () => {
            imageWrapper.insertAdjacentHTML('beforeend', `
                <div class="d-flex align-items-center gap-2 mt-2">
                    <input type="file"
                           name="images[]"
                           class="form-control"
                           accept="image/*">

                    <button type="button"
                            class="btn btn-danger btn-sm btn-remove-image">
                        ✕
                    </button>
                </div>
            `);
        });
    }

    /* ==================================================
        THÊM / XOÁ BIẾN THỂ
    ================================================== */
    const btnAddVariant = document.getElementById('btn-add-variant');
    const variantWrapper = document.getElementById('variant-wrapper');

    let variantIndex = variantWrapper
        ? variantWrapper.children.length
        : 0;

    if (btnAddVariant && variantWrapper) {
        btnAddVariant.addEventListener('click', () => {
            variantWrapper.insertAdjacentHTML('beforeend', `
                <div class="variant-item border rounded p-3 mb-3">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <input type="text"
                                   name="variants[${variantIndex}][attribute_value]"
                                   class="form-control"
                                   placeholder="VD: 500ml">
                            <input type="hidden"
                                   name="variants[${variantIndex}][attribute_name]"
                                   value="Dung tích">
                        </div>

                        <div class="col-md-3">
                            <input type="number"
                                   name="variants[${variantIndex}][price]"
                                   class="form-control"
                                   placeholder="Giá">
                        </div>

                        <div class="col-md-3">
                            <input type="number"
                                   name="variants[${variantIndex}][stock]"
                                   class="form-control"
                                   placeholder="Số lượng">
                        </div>

                        <div class="col-md-2">
                            <input type="file"
                                   name="variants[${variantIndex}][image]"
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
    }

    /* ==================================================
        EVENT DELEGATION (XOÁ ẢNH / XOÁ BIẾN THỂ)
    ================================================== */
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-remove-image')) {
            e.target.closest('div').remove();
        }

        if (e.target.classList.contains('btn-remove-variant')) {
            e.target.closest('.variant-item').remove();
        }
    });

});

