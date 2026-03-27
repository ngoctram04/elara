@extends('layouts.frontend')
@section('title','Chi tiết đơn hàng')

@section('content')

<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => url('/')],
    ['label' => 'Lịch sử đơn hàng', 'url' => route('orders.history')],
    ['label' => 'Chi tiết đơn hàng']
]" />

<div class="container order-detail-page pb-4">

    {{-- HEADER --}}
    <div class="order-header mb-3">
        <div>
            <h4 class="order-title mb-1">
                Đơn hàng DH{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
            </h4>
            <div class="order-subtitle">
                Theo dõi trạng thái, sản phẩm và thông tin thanh toán của đơn hàng
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
    @php
        $hasUnreviewedItems = $order->isCompleted() && $order->items->contains(function ($item) {
            return !$item->review;
        });
    @endphp

    {{-- NÚT ĐÁNH GIÁ TẤT CẢ --}}
    @if($hasUnreviewedItems)
        <a href="{{ route('reviews.create', $order->id) }}"
           class="btn btn-primary btn-sm px-3">
            <i class="bi bi-star me-1"></i> Đánh giá tất cả
        </a>
    @endif

    {{-- NÚT NHẬN HÀNG --}}
    @if($order->status == 3 && !$order->customer_confirmed)
        <form action="{{ route('orders.confirmReceived',$order->id) }}"
              method="POST"
              class="confirm-form">
            @csrf
            <button type="submit" class="btn btn-success btn-sm px-3 btn-confirm">
                <i class="bi bi-check-circle me-1"></i> Đã nhận hàng
            </button>
        </form>
    @endif

    {{-- NÚT HUỶ --}}
    @if($order->canCancel())
        <form action="{{ route('orders.cancel', $order->id) }}"
              method="POST"
              class="cancel-form">
            @csrf
            @method('PUT')

            <input type="hidden" name="cancel_reason" class="cancel-reason">

            <button type="button" class="btn btn-danger btn-sm px-3 btn-cancel">
                <i class="bi bi-x-circle me-1"></i> Huỷ đơn
            </button>
        </form>
    @endif
</div>
    </div>

    {{-- ================= THÔNG TIN CHUNG ================= --}}
    <div class="card order-card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="info-box">
                        <div class="info-label">Ngày đặt</div>
                        <div class="info-value">{{ $order->created_at->format('d/m/Y H:i') }}</div>

                        @if($order->isCompleted() && $order->delivered_at)
                            <div class="info-meta text-success mt-2">
                                <i class="bi bi-truck me-1"></i>
                                Ngày giao: {{ $order->delivered_at->format('d/m/Y H:i') }}
                            </div>
                        @endif

                        @if($order->isCancelled() && $order->cancelled_at)
                            <div class="info-meta text-danger mt-2">
                                <i class="bi bi-x-circle me-1"></i>
                                Thời gian huỷ: {{ $order->cancelled_at->format('d/m/Y H:i') }}
                            </div>
                        @endif

                        @if($order->isCancelled() && $order->cancel_reason)
                            <div class="info-meta text-muted mt-1">
                                Lý do: {{ $order->cancel_reason }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-box">
                        <div class="info-label">Trạng thái đơn hàng</div>
                        <div class="mt-1">
                            <span class="badge rounded-pill bg-{{ $order->status_badge }} px-3 py-2">
                                {{ $order->status_name }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="info-box">
                        <div class="info-label">Thanh toán</div>
                        <div class="info-value">{{ $order->payment_method_name }}</div>

                        <div class="mt-2">
                            <span class="badge rounded-pill bg-{{ $order->payment_status_badge }} px-3 py-2">
                                {{ $order->payment_status_name }}
                            </span>
                        </div>

                        <div class="info-label mt-3">Khách thanh toán</div>
                        <div class="price-highlight">
                            {{ number_format($order->grand_total) }}đ
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= TIMELINE ================= --}}
    <div class="card order-card mb-3">
        <div class="card-body">
            <h6 class="section-title mb-3">Trạng thái đơn hàng</h6>

            <div class="order-timeline">
                <div class="timeline-line"></div>

                <div class="timeline-step">
                    <div class="status-circle {{ $order->status >= 1 ? 'active' : '' }}">1</div>
                    <div class="timeline-text">Đang xử lý</div>
                </div>

                <div class="timeline-step">
                    <div class="status-circle {{ $order->status >= 2 ? 'active' : '' }}">2</div>
                    <div class="timeline-text">Đang giao</div>
                </div>

                <div class="timeline-step">
                    <div class="status-circle {{ $order->status >= 3 ? 'active' : '' }}">3</div>
                    <div class="timeline-text">Đã giao</div>
                </div>

                <div class="timeline-step">
                    <div class="status-circle {{ $order->status == 4 ? 'cancel' : '' }}">4</div>
                    <div class="timeline-text">Đã huỷ</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= THÔNG TIN NHẬN HÀNG ================= --}}
    <div class="card order-card mb-3">
        <div class="card-body">
            <h6 class="section-title mb-3">Thông tin nhận hàng</h6>

            <div class="shipping-info">
                <div class="shipping-row">
                    <span class="shipping-label">Người nhận</span>
                    <span class="shipping-value">{{ $order->receiver_name }}</span>
                </div>

                <div class="shipping-row">
                    <span class="shipping-label">SĐT</span>
                    <span class="shipping-value">{{ $order->receiver_phone }}</span>
                </div>

                <div class="shipping-row">
                    <span class="shipping-label">Địa chỉ</span>
                    <span class="shipping-value">{{ $order->receiver_address }}</span>
                </div>

                @if($order->delivery_image)
                    <div class="mt-3">
                        <a href="javascript:void(0)"
                           class="delivery-image-link view-delivery-image"
                           data-src="{{ asset('storage/' . $order->delivery_image) }}">
                            <i class="bi bi-image me-1"></i>
                            Xem ảnh xác nhận giao hàng
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ================= DANH SÁCH SẢN PHẨM ================= --}}
    <div class="card order-card">
        <div class="card-body">
            <h6 class="section-title mb-3">Sản phẩm trong đơn</h6>

            @foreach($order->items as $item)
                @php
                    $variant = $item->variant;
                    $product = $variant->product ?? null;

                    $image = optional($variant->mainImage)->image_path
                        ?? optional($product->mainImage)->image_path;

                    $imageUrl = $image
                        ? asset('storage/'.$image)
                        : asset('images/no-image.png');
                @endphp

                <div class="product-item">
                    <div class="product-main">
                        <div class="product-left">
                            <img src="{{ $imageUrl }}"
                                 class="product-image"
                                 alt="{{ $product->name ?? 'Sản phẩm' }}">

                            <div class="product-info">
                                <div class="product-name">{{ $product->name }}</div>
                                <div class="product-variant">
                                    {{ $variant->attribute_name }}: {{ $variant->attribute_value }}
                                </div>
                                <div class="product-qty">
                                    Số lượng: x{{ $item->quantity }}
                                </div>
                            </div>
                        </div>

                        <div class="product-right">
                            <div class="product-unit-price">{{ number_format($item->price) }}đ</div>
                            <div class="product-total-price">
                                {{ number_format($item->price * $item->quantity) }}đ
                            </div>
                        </div>
                    </div>

                    {{-- REVIEW --}}
@if($order->status == 3)
    <div class="review-wrap">
        @if(!$item->review)
            <div class="review-empty">
                <i class="bi bi-star me-1"></i>
                Sản phẩm này chưa được đánh giá
            </div>
        @else
            <div class="review-box">
                <div class="review-rating">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= $item->review->rating)
                            ★
                        @else
                            ☆
                        @endif
                    @endfor

                    <span class="review-score">
                        ({{ number_format($item->review->rating, 1) }})
                    </span>
                </div>

                @if($item->review->comment)
                    <div class="review-comment">
                        {{ $item->review->comment }}
                    </div>
                @endif

                @if($item->review->media->count())
                    <div class="review-media">
                        @foreach($item->review->media as $media)
                            @if($media->file_type == 'image')
                                <img src="{{ asset('storage/'.$media->file_path) }}"
                                     class="review-media-image"
                                     alt="review-image">
                            @else
                                <video class="review-media-video" controls>
                                    <source src="{{ asset('storage/'.$media->file_path) }}">
                                </video>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
@endif
                </div>
            @endforeach

            {{-- ================= TỔNG TIỀN ================= --}}
            <div class="summary-box mt-3">
                <div class="summary-row">
                    <span>Tạm tính</span>
                    <strong>{{ number_format($order->subtotal) }}đ</strong>
                </div>

                @if($order->voucher_discount > 0)
                    <div class="summary-row text-success">
                        <span>Voucher</span>
                        <strong>- {{ number_format($order->voucher_discount) }}đ</strong>
                    </div>
                @endif

                @if($order->birthday_discount > 0)
                    <div class="summary-row text-success">
                        <span>Ưu đãi sinh nhật</span>
                        <strong>- {{ number_format($order->birthday_discount) }}đ</strong>
                    </div>
                @endif

                <div class="summary-row">
                    <span>Phí vận chuyển</span>
                    <strong>{{ number_format($order->shipping_fee) }}đ</strong>
                </div>

                <div class="summary-total">
                    <span>Tổng thanh toán</span>
                    <span class="summary-total-price">{{ number_format($order->grand_total) }}đ</span>
                </div>

                {{-- ================= REFUND ================= --}}
                @if($order->isCompleted() || $order->refundRequest)
                    @php
                        $refund = $order->refundRequest;
                    @endphp

                    <div class="refund-box">
                        @if(!$refund)
                            <button type="button"
                                    class="btn btn-outline-danger btn-sm px-3 btn-refund"
                                    data-url="{{ route('refund.create',$order->id) }}">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                Yêu cầu trả hàng / hoàn tiền
                            </button>
                        @else
                            @if($refund->status == 'pending')
                                <div class="refund-alert refund-warning">
                                    <b>Đã gửi yêu cầu hoàn tiền</b><br>
                                    Vui lòng chờ cửa hàng phản hồi trong vòng <b>24 giờ</b>.
                                </div>
                            @elseif($refund->status == 'approved')
                                <div class="refund-alert refund-primary">
                                    <b>Yêu cầu hoàn tiền đã được chấp nhận</b><br>
                                    Cửa hàng đang tiến hành xử lý hoàn tiền.
                                </div>
                            @elseif($refund->status == 'refunded')
                                <div class="refund-alert refund-success">
                                    <b>Đã hoàn tiền thành công</b>
                                </div>
                            @elseif($refund->status == 'rejected')
                                <div class="refund-alert refund-danger">
                                    <b>Yêu cầu hoàn tiền đã bị từ chối</b>
                                    @if($refund->admin_note)
                                        <br>Lý do: {{ $refund->admin_note }}
                                    @endif
                                </div>
                            @endif
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.order-detail-page{
    --card-border:#eef2f7;
    --muted:#6b7280;
    --text:#111827;
    --soft:#f8fafc;
    --soft-blue:#eef6ff;
}

.order-header{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:16px;
    flex-wrap:wrap;
}

.order-title{
    font-size:26px;
    font-weight:800;
    color:var(--text);
}

.order-subtitle{
    color:var(--muted);
    font-size:14px;
}

.order-card{
    border:none;
    border-radius:18px;
    box-shadow:0 10px 28px rgba(15,23,42,.06);
    overflow:hidden;
}

.order-card .card-body{
    padding:20px;
}

.section-title{
    font-size:16px;
    font-weight:700;
    color:var(--text);
    margin:0;
}

.info-box{
    height:100%;
    padding:14px 16px;
    border:1px solid var(--card-border);
    border-radius:14px;
    background:#fff;
}

.info-label{
    font-size:13px;
    color:var(--muted);
    margin-bottom:4px;
}

.info-value{
    font-size:15px;
    font-weight:700;
    color:var(--text);
}

.info-meta{
    font-size:13px;
    line-height:1.6;
}

.price-highlight{
    font-size:24px;
    font-weight:800;
    color:#dc3545;
    line-height:1.2;
}

.order-timeline{
    position:relative;
    display:flex;
    justify-content:space-between;
    gap:12px;
    text-align:center;
}

.timeline-line{
    position:absolute;
    top:18px;
    left:10%;
    right:10%;
    height:3px;
    background:#e9ecef;
    z-index:0;
    border-radius:999px;
}

.timeline-step{
    position:relative;
    z-index:1;
    flex:1;
}

.status-circle{
    width:38px;
    height:38px;
    line-height:38px;
    border-radius:50%;
    background:#e9ecef;
    color:#6c757d;
    margin:0 auto 8px;
    font-weight:700;
    transition:all .2s ease;
}

.status-circle.active{
    background:#0d6efd;
    color:#fff;
    box-shadow:0 8px 16px rgba(13,110,253,.2);
}

.status-circle.cancel{
    background:#dc3545;
    color:#fff;
    box-shadow:0 8px 16px rgba(220,53,69,.18);
}

.timeline-text{
    font-size:13px;
    color:#4b5563;
    line-height:1.4;
}

.shipping-info{
    border:1px solid var(--card-border);
    border-radius:14px;
    background:#fff;
    overflow:hidden;
}

.shipping-row{
    display:flex;
    gap:16px;
    padding:12px 14px;
    border-bottom:1px solid #f1f5f9;
}

.shipping-row:last-child{
    border-bottom:none;
}

.shipping-label{
    width:110px;
    flex-shrink:0;
    color:var(--muted);
    font-size:14px;
}

.shipping-value{
    color:var(--text);
    font-weight:600;
    font-size:14px;
}

.delivery-image-link{
    display:inline-flex;
    align-items:center;
    color:#0d6efd;
    font-weight:600;
    text-decoration:none;
    font-size:14px;
    padding:0 14px 14px;
}

.delivery-image-link:hover{
    text-decoration:underline;
}

.product-item{
    padding:16px 0;
    border-bottom:1px solid #edf2f7;
}

.product-item:last-of-type{
    border-bottom:none;
}

.product-main{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:16px;
    flex-wrap:wrap;
}

.product-left{
    display:flex;
    align-items:center;
    gap:14px;
    min-width:0;
}

.product-image{
    width:78px;
    height:78px;
    border-radius:12px;
    object-fit:cover;
    border:1px solid #eef2f7;
    background:#fff;
    flex-shrink:0;
}

.product-info{
    min-width:0;
}

.product-name{
    font-size:15px;
    font-weight:700;
    color:var(--text);
    margin-bottom:4px;
}

.product-variant,
.product-qty{
    font-size:13px;
    color:var(--muted);
    line-height:1.5;
}

.product-right{
    text-align:right;
    min-width:120px;
}

.product-unit-price{
    font-size:14px;
    color:#6b7280;
}

.product-total-price{
    font-size:18px;
    font-weight:800;
    color:#dc3545;
    margin-top:2px;
}

.review-wrap{
    margin-top:14px;
    margin-left:92px;
}
.review-empty{
    display:inline-flex;
    align-items:center;
    gap:6px;
    background:#fff7e6;
    border:1px solid #ffe7ba;
    color:#ad6800;
    font-size:14px;
    font-weight:600;
    padding:10px 14px;
    border-radius:12px;
}
.review-box{
    background:#f8fafc;
    border:1px solid #e5e7eb;
    border-radius:14px;
    padding:14px;
}

.review-rating{
    color:#f59e0b;
    font-size:18px;
    margin-bottom:8px;
    line-height:1;
}

.review-score{
    color:#111827;
    font-size:14px;
    margin-left:8px;
}

.review-comment{
    color:#374151;
    font-size:14px;
    line-height:1.6;
    margin-bottom:10px;
}

.review-media{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
}

.review-media-image{
    width:84px;
    height:84px;
    object-fit:cover;
    border-radius:10px;
    border:1px solid #e5e7eb;
    background:#fff;
}

.review-media-video{
    width:140px;
    border-radius:10px;
    border:1px solid #e5e7eb;
    background:#fff;
}

.summary-box{
    margin-left:auto;
    max-width:420px;
    background:#fbfcfe;
    border:1px solid #eef2f7;
    border-radius:16px;
    padding:16px;
}

.summary-row{
    display:flex;
    justify-content:space-between;
    gap:12px;
    font-size:14px;
    padding:6px 0;
    color:#374151;
}

.summary-total{
    display:flex;
    justify-content:space-between;
    gap:12px;
    margin-top:10px;
    padding-top:12px;
    border-top:1px dashed #dbe3ee;
    font-size:16px;
    font-weight:700;
    color:#111827;
}

.summary-total-price{
    color:#dc3545;
    font-size:22px;
    font-weight:800;
}

.refund-box{
    margin-top:14px;
}

.refund-alert{
    border-radius:12px;
    padding:12px 14px;
    font-size:14px;
    line-height:1.6;
    text-align:left;
}

.refund-warning{
    background:#fff8e1;
    border:1px solid #fde68a;
    color:#92400e;
}

.refund-primary{
    background:#eef6ff;
    border:1px solid #bfdbfe;
    color:#1d4ed8;
}

.refund-success{
    background:#ecfdf3;
    border:1px solid #bbf7d0;
    color:#15803d;
}

.refund-danger{
    background:#fef2f2;
    border:1px solid #fecaca;
    color:#b91c1c;
}

@media (max-width: 767.98px){
    .order-title{
        font-size:22px;
    }

    .order-card .card-body{
        padding:16px;
    }

    .timeline-line{
        left:12%;
        right:12%;
    }

    .timeline-text{
        font-size:12px;
    }

    .shipping-row{
        flex-direction:column;
        gap:4px;
    }

    .shipping-label{
        width:auto;
        font-size:13px;
    }

    .shipping-value{
        font-size:13px;
    }

    .product-main{
        align-items:flex-start;
    }

    .product-left{
        align-items:flex-start;
    }

    .product-image{
        width:70px;
        height:70px;
    }

    .product-right{
        width:100%;
        text-align:left;
        padding-left:84px;
    }

    .review-wrap{
        margin-left:0;
    }

    .summary-box{
        max-width:100%;
    }

    .summary-total-price{
        font-size:20px;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.querySelectorAll('.btn-cancel').forEach(function(button){
    button.addEventListener('click', function(){
        let form = this.closest('.cancel-form');
        let input = form.querySelector('.cancel-reason');

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
                if (!value) {
                    return 'Bạn cần nhập lý do huỷ!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed){
                input.value = result.value;
                form.submit();
            }
        });
    });
});

// XÁC NHẬN ĐÃ NHẬN HÀNG
document.querySelectorAll('.btn-confirm').forEach(function(btn){
    btn.addEventListener('click', function(e){
        e.preventDefault();
        let form = this.closest('form');

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

document.querySelectorAll('.view-delivery-image').forEach(function(link){
    link.addEventListener('click', function(){
        Swal.fire({
            imageUrl: this.dataset.src,
            imageAlt: 'Ảnh giao hàng',
            showConfirmButton: false,
            showCloseButton: true,
            width: 'auto',
            background: '#fff'
        });
    });
});

// POPUP ĐIỀU KHOẢN TRẢ HÀNG
document.addEventListener('DOMContentLoaded', function () {
    const refundButtons = document.querySelectorAll('.btn-refund');

    refundButtons.forEach(function (btn) {
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
});
</script>
@endsection