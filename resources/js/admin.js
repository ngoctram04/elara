document.addEventListener('DOMContentLoaded', () => {

    /* ==================================================
        SIDEBAR TOGGLE
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
        THÊM ẢNH PHỤ SẢN PHẨM
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
        EVENT DELEGATION (XOÁ ẢNH)
    ================================================== */
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-remove-image')) {
            e.target.closest('div').remove();
        }
    });

});