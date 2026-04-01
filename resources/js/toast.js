window.showToast = function (message, type = 'success') {
    const icons = {
        success: 'bi-check',
        error: 'bi-x',
        warning: 'bi-exclamation',
        info: 'bi-info',
    };

    const old = document.getElementById('toast-overlay');
    if (old) old.remove();

    const overlay = document.createElement('div');
    overlay.id = 'toast-overlay';
    overlay.className = 'toast-overlay';

    overlay.innerHTML = `
        <div class="toast-box toast-${type}">
            <div class="toast-icon">
                <i class="bi ${icons[type] || 'bi-info'}"></i>
            </div>
            <div class="toast-text">${message}</div>
            <div class="toast-hint">Nhấn để đóng</div>
        </div>
    `;

    document.body.appendChild(overlay);

    overlay.addEventListener('click', hideToast);
    setTimeout(hideToast, 4000);

    function hideToast() {
        overlay.style.opacity = '0';
        setTimeout(() => {
            if (overlay.parentNode) overlay.remove();
        }, 300);
    }
};
window.showToast = function(message, type = 'success') {
    alert('Toast đang chạy: ' + message);
};