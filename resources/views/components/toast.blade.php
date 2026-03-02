{{-- ================= SESSION TOAST ================= --}}
@if (session('success') || session('error') || session('warning') || session('info'))
    @php
        $type = session('success') ? 'success' : 
                (session('error') ? 'error' : 
                (session('warning') ? 'warning' : 'info'));

        $message = session($type);

        $icons = [
            'success' => 'bi-check',
            'error' => 'bi-x',
            'warning' => 'bi-exclamation',
            'info' => 'bi-info',
        ];
    @endphp

    <div id="toast-overlay" class="toast-overlay">
        <div class="toast-box toast-{{ $type }}">
            <div class="toast-icon">
                <i class="bi {{ $icons[$type] }}"></i>
            </div>

            <div class="toast-text">
                {{ $message }}
            </div>

            <div class="toast-hint">
                Nhấn để đóng
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const overlay = document.getElementById('toast-overlay');
            if (!overlay) return;

            overlay.addEventListener('click', hideToast);
            setTimeout(hideToast, 4000);

            function hideToast() {
                overlay.style.opacity = '0';
                setTimeout(() => overlay.remove(), 300);
            }
        });
    </script>
@endif


{{-- ================= AJAX TOAST (GLOBAL) ================= --}}
<script>
/**
 * Gọi ở JS:
 * showToast('Nội dung', 'success|error|warning|info')
 */
window.showToast = function(message, type = 'success') {

    const icons = {
        success: 'bi-check',
        error: 'bi-x',
        warning: 'bi-exclamation',
        info: 'bi-info'
    };

    // Nếu đang có toast → xóa trước
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

            <div class="toast-text">
                ${message}
            </div>

            <div class="toast-hint">
                Nhấn để đóng
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    overlay.addEventListener('click', hideToast);
    setTimeout(hideToast, 4000);

    function hideToast() {
        overlay.style.opacity = '0';
        setTimeout(() => overlay.remove(), 300);
    }
};
</script>


{{-- ================= STYLE ================= --}}
<style>
.toast-overlay{
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.2);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    animation: fadeOverlay .2s ease;
}

.toast-box{
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 20px 50px rgba(0,0,0,.15);
    text-align: center;
    max-width: 380px;
    width: 90%;
    padding: 28px 24px;
    border: 2px solid;
    animation: toastScale .25s ease;
}

/* Icon */
.toast-icon{
    width: 42px;
    height: 42px;
    margin: 0 auto 10px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: #fff;
}

/* Text */
.toast-text{
    font-weight: 600;
    font-size: 16px;
    color: #333;
}

/* Hint */
.toast-hint{
    font-size: 12px;
    color: #888;
    margin-top: 6px;
}

/* Colors */
.toast-success{ border-color:#16a34a; }
.toast-success .toast-icon{ background:#16a34a; }

.toast-error{ border-color:#dc2626; }
.toast-error .toast-icon{ background:#dc2626; }

.toast-warning{ border-color:#f59e0b; }
.toast-warning .toast-icon{ background:#f59e0b; }

.toast-info{ border-color:#3b82f6; }
.toast-info .toast-icon{ background:#3b82f6; }

/* Animations */
@keyframes toastScale{
    from{ opacity:0; transform:scale(.9); }
    to{ opacity:1; transform:scale(1); }
}

@keyframes fadeOverlay{
    from{opacity:0}
    to{opacity:1}
}
</style>