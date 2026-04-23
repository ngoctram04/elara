document.addEventListener('DOMContentLoaded', () => {


const toggleBtn   = document.getElementById('toggleSidebar');
const sidebar     = document.getElementById('sidebar');
const mainContent = document.querySelector('.main-content');

if (sidebar) {

    const overlay = document.createElement('div');
    overlay.classList.add('sidebar-overlay');
    document.body.appendChild(overlay);



    const savedState = localStorage.getItem('sidebar');

    if (savedState === 'hidden' && window.innerWidth > 768) {
        sidebar.classList.add('hidden');
        mainContent?.classList.add('full');
    }

    const closeSidebar = () => {
        sidebar.classList.remove('show');
        overlay.classList.remove('active');
    };


    const toggleSidebar = () => {
        if (window.innerWidth <= 768) {

            sidebar.classList.toggle('show');
            overlay.classList.toggle('active');

        }

        else {

            const isHidden = sidebar.classList.toggle('hidden');

            if (mainContent) {
                mainContent.classList.toggle('full', isHidden);
            }

            localStorage.setItem(
                'sidebar',
                isHidden ? 'hidden' : 'show'
            );

        }
    };


    if (toggleBtn) {
        toggleBtn.addEventListener('click', toggleSidebar);
    }



    overlay.addEventListener('click', closeSidebar);


    window.addEventListener('resize', () => {

        if (window.innerWidth > 768) {
            overlay.classList.remove('active');
            sidebar.classList.remove('show');
        }

    });

}




const btnAddImage  = document.getElementById('btn-add-image');
const imageWrapper = document.getElementById('image-wrapper');

if (btnAddImage && imageWrapper) {

    btnAddImage.addEventListener('click', () => {

        const html = `
            <div class="d-flex align-items-center gap-2 mt-2 image-item">

                <input 
                    type="file"
                    name="images[]"
                    class="form-control"
                    accept="image/*">

                <button 
                    type="button"
                    class="btn btn-danger btn-sm btn-remove-image">
                    ✕
                </button>

            </div>
        `;

        imageWrapper.insertAdjacentHTML('beforeend', html);

    });

}


document.addEventListener('click', (e) => {

    if (e.target.classList.contains('btn-remove-image')) {

        const item = e.target.closest('.image-item');

        if (item) {
            item.remove();
        }

    }

});

});