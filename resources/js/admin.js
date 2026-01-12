document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('toggleSidebar');
    const sidebar = document.getElementById('sidebar');

    // Tạo overlay (che nền khi mở sidebar mobile)
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);

    // Mở / đóng sidebar
    toggleBtn?.addEventListener('click', () => {
        sidebar?.classList.toggle('show');
        overlay.classList.toggle('active');
    });

    // Click overlay để đóng sidebar
    overlay.addEventListener('click', () => {
        sidebar?.classList.remove('show');
        overlay.classList.remove('active');
    });
});
