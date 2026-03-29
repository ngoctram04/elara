import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

document.addEventListener('DOMContentLoaded', function () {
    initCancelOrder();
    initConfirmReceived();
    initViewDeliveryImage();
    initRefundPolicyPopup();
});

function initCancelOrder() {
    const cancelButtons = document.querySelectorAll('.btn-cancel');

    cancelButtons.forEach((button) => {
        button.addEventListener('click', function () {
            const form = this.closest('.cancel-form');
            const input = form?.querySelector('.cancel-reason');

            if (!form || !input) return;

            Swal.fire({
                title: 'Huỷ đơn hàng',
                input: 'textarea',
                inputLabel: 'Lý do huỷ đơn',
                inputPlaceholder: 'Ví dụ: đặt nhầm, muốn đổi sản phẩm...',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Xác nhận huỷ',
                cancelButtonText: 'Không',
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                inputValidator: (value) => {
                    if (!value || !value.trim()) {
                        return 'Bạn cần nhập lý do huỷ!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    input.value = result.value.trim();
                    form.submit();
                }
            });
        });
    });
}

function initConfirmReceived() {
    const confirmButtons = document.querySelectorAll('.btn-confirm');

    confirmButtons.forEach((btn) => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();

            const form = this.closest('form');
            if (!form) return;

            Swal.fire({
                title: 'Xác nhận đã nhận hàng?',
                html: `
                    Sau khi xác nhận, đơn hàng sẽ hoàn tất.<br><br>
                    <div style="
                        background:#f8f9fa;
                        border:1px solid #dee2e6;
                        padding:14px;
                        border-radius:8px;
                        font-size:14px;
                        text-align:left;
                        line-height:1.5;
                    ">
                        <b>Lưu ý:</b><br>
                        Vui lòng quay video quá trình mở kiện hàng để làm bằng chứng
                        trong trường hợp sản phẩm bị lỗi, thiếu hoặc hư hỏng.
                        Cửa hàng có thể từ chối hỗ trợ nếu không có bằng chứng.
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Đã nhận',
                cancelButtonText: 'Chưa',
                confirmButtonColor: '#2ecc71',
                cancelButtonColor: '#6c757d',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
}

function initViewDeliveryImage() {
    const imageLinks = document.querySelectorAll('.view-delivery-image');

    imageLinks.forEach((link) => {
        link.addEventListener('click', function () {
            const imageSrc = this.dataset.src;
            if (!imageSrc) return;

            Swal.fire({
                imageUrl: imageSrc,
                imageAlt: 'Ảnh giao hàng',
                showConfirmButton: false,
                showCloseButton: true,
                width: 'auto',
                background: '#fff'
            });
        });
    });
}

function initRefundPolicyPopup() {
    const refundButtons = document.querySelectorAll('.btn-refund');

    refundButtons.forEach((btn) => {
        btn.addEventListener('click', function () {
            const url = this.getAttribute('data-url');
            if (!url) return;

            Swal.fire({
                title: 'Điều khoản trả hàng / hoàn tiền',
                html: `
                    <div style="text-align:left;font-size:14px;line-height:1.6">
                        <p><b>Vui lòng đọc kỹ trước khi tiếp tục:</b></p>
                        <p>• Chỉ áp dụng cho đơn hàng đã giao thành công</p>
                        <p>• Khách hàng có thể gửi yêu cầu trả hàng / hoàn tiền khi sản phẩm có vấn đề hoặc không đúng như mô tả</p>
                        <p>• Nếu sản phẩm còn nguyên seal, shop sẽ kiểm tra để xem xét nhập lại kho theo quy định</p>
                        <p>• Nếu sản phẩm bị vỡ, hư hỏng hoặc không đủ điều kiện nhập lại kho, shop vẫn xem xét hoàn tiền theo chính sách nhưng sẽ không hoàn kho</p>
                        <p>• Vui lòng cung cấp hình ảnh/video rõ ràng để xác minh tình trạng sản phẩm</p>
                        <p>• Shop sẽ kiểm tra và phản hồi kết quả trong thời gian sớm nhất</p>
                        <p style="margin-top:10px;color:#666">
                            Khi nhấn "Đồng ý & Tiếp tục", bạn xác nhận thông tin cung cấp là chính xác.
                        </p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Đồng ý & Tiếp tục',
                cancelButtonText: 'Không đồng ý',
                confirmButtonColor: '#1677a0',
                cancelButtonColor: '#6c757d',
                allowOutsideClick: false,
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    });
}