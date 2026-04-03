@extends('layouts.frontend')
@section('title','Lịch sử đơn hàng')

@section('content')

<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => url('/')],
    ['label' => 'Lịch sử đơn hàng']
]" />

<style>
body{
    background:#f6f8fb;
    color:#334155;
}

/* PAGE */
.order-history-page{
    max-width: 1120px;
    margin: 0 auto;
}

.order-page-title{
    font-size:20px;
    font-weight:600;
    color:#1e293b;
    margin-bottom:16px;
}

/* FILTER */
.order-filter-card{
    background:#fff;
    border:1px solid #e8eef5;
    border-radius:16px;
    box-shadow:0 8px 24px rgba(15, 23, 42, 0.04);
    margin-bottom:20px;
}

.order-filter-card .card-body{
    padding:16px;
}

.search-box .input-group-text{
    background:#fff;
    border:1px solid #dbe3ee;
    border-right:0;
    color:#64748b;
    border-radius:10px 0 0 10px;
    height:42px;
}

.search-box .form-control{
    border:1px solid #dbe3ee;
    border-left:0;
    border-radius:0 10px 10px 0;
    height:42px;
    font-size:14px;
    color:#334155;
    box-shadow:none;
}

.search-box .form-control:focus{
    border-color:#c9d7ea;
    box-shadow:none;
}

.order-tabs{
    display:flex;
    flex-wrap:wrap;
    gap:8px;
}

.order-tabs .tab{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-height:38px;
    padding:0 14px;
    border-radius:999px;
    border:1px solid #e2e8f0;
    background:#f8fafc;
    text-decoration:none;
    color:#475569;
    font-size:13px;
    font-weight:500;
    transition:all .2s ease;
}

.order-tabs .tab:hover{
    background:#f1f5f9;
    border-color:#cbd5e1;
    color:#334155;
}

.order-tabs .tab.active{
    background:#eff6ff;
    color:#2563eb;
    border-color:#bfdbfe;
}

/* ORDER CARD */
.order-box{
    background:#fff;
    border:1px solid #e8eef5;
    border-radius:18px;
    box-shadow:0 10px 28px rgba(15, 23, 42, 0.05);
    margin-bottom:18px;
    overflow:hidden;
    transition:all .2s ease;
}

.order-box:hover{
    box-shadow:0 14px 32px rgba(15, 23, 42, 0.07);
}

.order-header{
    padding:14px 16px;
    border-bottom:1px solid #edf2f7;
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:14px;
    font-size:13px;
    background:linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
}

.order-meta{
    color:#475569;
    line-height:1.65;
}

.order-code{
    font-weight:600;
    color:#0f172a;
}

.order-subline{
    margin-top:2px;
    font-size:12.5px;
    color:#64748b;
}

.order-subline.text-danger{
    color:#dc2626 !important;
}

/* updated badge */
.order-updated-badge{
    display:inline-flex;
    align-items:center;
    gap:6px;
    margin-left:8px;
    padding:3px 8px;
    border-radius:999px;
    font-size:12px;
    font-weight:500;
    background:#eff6ff;
    color:#2563eb;
    border:1px solid #dbeafe;
    white-space:nowrap;
}

.order-updated-badge.new{
    background:#ecfdf5;
    color:#059669;
    border-color:#bbf7d0;
}

/* status */
.order-status{
    display:flex;
    align-items:center;
    gap:8px;
    flex-wrap:wrap;
    justify-content:flex-end;
    text-align:right;
}

.order-status-text{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:6px 10px;
    border-radius:999px;
    font-size:12.5px;
    font-weight:500;
    border:1px solid transparent;
}

.status-1{
    color:#b45309;
    background:#fffbeb;
    border-color:#fde68a;
}

.status-2{
    color:#1d4ed8;
    background:#eff6ff;
    border-color:#bfdbfe;
}

.status-3{
    color:#15803d;
    background:#f0fdf4;
    border-color:#bbf7d0;
}

.status-4{
    color:#dc2626;
    background:#fef2f2;
    border-color:#fecaca;
}

/* item */
.order-item{
    display:flex;
    gap:14px;
    padding:14px 16px;
    border-bottom:1px solid #f1f5f9;
    align-items:flex-start;
}

.order-item:last-child{
    border-bottom:none;
}

.order-img{
    width:72px;
    height:72px;
    border-radius:10px;
    object-fit:cover;
    border:1px solid #e5e7eb;
    background:#fff;
    flex-shrink:0;
}

.order-item-info{
    flex:1;
    min-width:0;
}

.order-name{
    font-size:14px;
    font-weight:600;
    color:#1e293b;
    margin-bottom:5px;
    line-height:1.45;
}

.order-variant{
    font-size:13px;
    color:#64748b;
    line-height:1.55;
}

.order-price{
    min-width:110px;
    text-align:right;
    font-size:14px;
    color:#0f172a;
    font-weight:600;
    white-space:nowrap;
}

/* expand */
.order-expand-wrap{
    border-bottom:1px solid #f1f5f9;
    padding:4px 0 8px;
    text-align:center;
}

.btn-toggle-items{
    border:none;
    background:transparent;
    color:#2563eb;
    font-size:13px;
    font-weight:500;
    padding:6px 12px;
    cursor:pointer;
    transition:.2s;
}

.btn-toggle-items:hover{
    color:#1d4ed8;
    text-decoration:underline;
}

/* footer */
.order-footer{
    padding:14px 16px 16px;
    border-top:1px solid #edf2f7;
    background:#fcfdff;
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:16px;
    flex-wrap:wrap;
}

.order-payment-info{
    font-size:13px;
    color:#475569;
    line-height:1.7;
}

.order-payment-info b{
    font-weight:600;
    color:#1e293b;
}

.order-discount-line{
    color:#16a34a;
    font-size:13px;
}

.order-ship-line{
    color:#64748b;
    font-size:12.5px;
}

.order-total-wrap{
    text-align:right;
    min-width:240px;
}

.order-total-label{
    font-size:13px;
    color:#475569;
    margin-bottom:4px;
}

.order-total{
    font-size:19px;
    font-weight:600;
    color:#ee4d2d;
    line-height:1.2;
}

.order-actions{
    margin-top:12px;
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    justify-content:flex-end;
    align-items:center;
}

.btn-action{
    border-radius:10px;
    font-size:13px;
    padding:7px 14px;
    font-weight:500;
    box-shadow:none !important;
}

.badge-status-note{
    display:inline-flex;
    align-items:center;
    min-height:32px;
    padding:6px 10px;
    border-radius:999px;
    font-size:12.5px;
    font-weight:500;
}

/* empty */
.order-empty{
    background:#fff;
    border:1px dashed #cbd5e1;
    border-radius:16px;
    padding:22px 18px;
    color:#64748b;
    font-size:14px;
    text-align:center;
}

/* pagination spacing */
.pagination{
    margin-bottom:0;
}

/* mobile */
@media (max-width: 767.98px){
    .order-header{
        flex-direction:column;
        align-items:flex-start;
    }

    .order-status{
        justify-content:flex-start;
        text-align:left;
    }

    .order-footer{
        flex-direction:column;
        align-items:stretch;
    }

    .order-total-wrap{
        width:100%;
        min-width:unset;
        text-align:left;
    }

    .order-actions{
        justify-content:flex-start;
    }

    .order-price{
        min-width:auto;
        text-align:left;
        margin-top:6px;
    }

    .order-item{
        flex-wrap:wrap;
    }
}
</style>

<div class="container py-4">
    <div class="order-history-page">

        <h4 class="order-page-title">Lịch sử đơn hàng</h4>

        <div class="order-filter-card card border-0">
            <div class="card-body">
                <form method="GET" id="orderSearchForm">
                    <div class="row align-items-center g-3">

                        {{-- SEARCH --}}
                        <div class="col-lg-4 col-md-5">
                            <div class="input-group search-box">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>

                                <input
                                    type="text"
                                    name="keyword"
                                    value="{{ request('keyword') }}"
                                    class="form-control"
                                    placeholder="Nhập mã đơn..."
                                    oninput="autoSearch()"
                                >
                            </div>
                        </div>

                        {{-- TABS --}}
                        <div class="col-lg-8 col-md-7">
                            <div class="order-tabs justify-content-md-end">
                                <a href="{{ route('orders.history', ['keyword' => request('keyword')]) }}"
                                   class="tab {{ request('status') == '' ? 'active' : '' }}">
                                    Tất cả
                                </a>

                                <a href="{{ route('orders.history', ['status' => 'processing', 'keyword' => request('keyword')]) }}"
                                   class="tab {{ request('status') == 'processing' ? 'active' : '' }}">
                                    Đang xử lý
                                </a>

                                <a href="{{ route('orders.history', ['status' => 'shipping', 'keyword' => request('keyword')]) }}"
                                   class="tab {{ request('status') == 'shipping' ? 'active' : '' }}">
                                    Đang giao
                                </a>

                                <a href="{{ route('orders.history', ['status' => 'completed', 'keyword' => request('keyword')]) }}"
                                   class="tab {{ request('status') == 'completed' ? 'active' : '' }}">
                                    Đã giao
                                </a>

                                <a href="{{ route('orders.history', ['status' => 'cancelled', 'keyword' => request('keyword')]) }}"
                                   class="tab {{ request('status') == 'cancelled' ? 'active' : '' }}">
                                    Đã huỷ
                                </a>

                                <a href="{{ route('orders.history', ['status' => 'return', 'keyword' => request('keyword')]) }}"
                                   class="tab {{ request('status') == 'return' ? 'active' : '' }}">
                                    Trả hàng / Hoàn tiền
                                </a>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        @forelse($orders as $order)
            @php
                $payAmount = $order->grand_total
                    ?? ($order->total + ($order->shipping_fee ?? 0));

                $isNewOrder = $order->created_at && $order->created_at->gt(now()->subDay());

                $isUpdatedOrder = $order->updated_at
                    && $order->created_at
                    && $order->updated_at->gt($order->created_at->addMinutes(3));

                $latestTime = $order->updated_at ?? $order->created_at;
            @endphp

            <div class="order-box">

                {{-- HEADER --}}
                <div class="order-header">
                    <div class="order-meta">
                        <div>
                            Mã đơn:
                            <span class="order-code">DH{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>

                            @if($isNewOrder)
                                <span class="order-updated-badge new">
                                    <i class="bi bi-stars"></i> Mới
                                </span>
                            @elseif($isUpdatedOrder)
                                <span class="order-updated-badge">
                                    <i class="bi bi-arrow-repeat"></i> Vừa cập nhật
                                </span>
                            @endif
                        </div>

                        <div class="order-subline">
                            {{ $order->created_at->format('d/m/Y H:i') }}
                        </div>

                        @if($latestTime)
                            <div class="order-subline">
                                Cập nhật: {{ $latestTime->format('d/m/Y H:i') }}
                            </div>
                        @endif

                        @if($order->isCompleted() && $order->delivered_at)
                            <div class="order-subline">
                                Đã giao: {{ $order->delivered_at->format('d/m/Y H:i') }}
                            </div>
                        @endif

                        @if($order->isCancelled())
                            <div class="order-subline text-danger">
                                Huỷ bởi:
                                {{ $order->cancelled_by == 'admin' ? 'Admin' : 'Khách' }}
                            </div>
                        @endif
                    </div>

                    <div class="order-status">
                        @if($order->status == 1)
                            <span class="order-status-text status-1">
                                <i class="bi bi-hourglass-split"></i> Đang xử lý
                            </span>
                        @elseif($order->status == 2)
                            <span class="order-status-text status-2">
                                <i class="bi bi-truck"></i> Đang giao
                            </span>
                        @elseif($order->status == 3 && !$order->customer_confirmed)
                            <span class="order-status-text status-2">
                                <i class="bi bi-box-seam"></i> Đã giao - chờ xác nhận
                            </span>
                        @elseif($order->status == 3 && $order->customer_confirmed)
                            <span class="order-status-text status-3">
                                <i class="bi bi-check-circle"></i> Hoàn tất
                            </span>
                        @elseif($order->status == 4)
                            <span class="order-status-text status-4">
                                <i class="bi bi-x-circle"></i> Đã huỷ
                            </span>
                        @endif
                    </div>
                </div>

                {{-- ITEMS --}}
                @foreach($order->items as $index => $item)
                    @php
                        $variant = $item->variant;
                        $product = $variant->product ?? null;

                        $image = optional($variant->mainImage)->image_path
                            ?? optional($product->mainImage)->image_path;

                        $imageUrl = $image
                            ? asset('storage/' . $image)
                            : asset('images/no-image.png');
                    @endphp

                    <div class="order-item {{ $index >= 1 ? 'extra-item d-none' : '' }}">
                        <img src="{{ $imageUrl }}" class="order-img" alt="{{ $product->name ?? 'Sản phẩm' }}">

                        <div class="order-item-info">
                            <div class="order-name">
                                {{ $product->name ?? 'Sản phẩm' }}
                            </div>

                            <div class="order-variant">
                                {{ $variant->attribute_value ?? '' }}
                                x{{ $item->quantity }}
                            </div>
                        </div>

                        <div class="order-price">
                            {{ number_format($item->price) }}đ
                        </div>
                    </div>
                @endforeach

                @if($order->items->count() > 1)
                    <div class="order-expand-wrap">
                        <button type="button"
                                class="btn-toggle-items"
                                data-more="Xem thêm {{ $order->items->count() - 1 }} sản phẩm"
                                data-less="Thu gọn">
                            Xem thêm {{ $order->items->count() - 1 }} sản phẩm
                        </button>
                    </div>
                @endif

                {{-- FOOTER --}}
                <div class="order-footer">
                    <div class="order-payment-info">
                        <div>
                            Thanh toán: <b>{{ $order->payment_method_name }}</b>
                        </div>

                        @if($order->birthday_discount > 0)
                            <div class="order-discount-line">
                                Ưu đãi sinh nhật:
                                -{{ number_format($order->birthday_discount) }}đ
                            </div>
                        @endif

                        @if($order->voucher_discount > 0)
                            <div class="order-discount-line">
                                Voucher:
                                -{{ number_format($order->voucher_discount) }}đ
                            </div>
                        @endif

                        @if($order->shipping_fee > 0)
                            <div class="order-ship-line">
                                Phí ship: {{ number_format($order->shipping_fee) }}đ
                            </div>
                        @endif
                    </div>

                    <div class="order-total-wrap">
                        <div class="order-total-label">Tổng thanh toán:</div>
                        <div class="order-total">
                            {{ number_format($payAmount) }}đ
                        </div>

                        <div class="order-actions">

                            {{-- CHI TIẾT ĐƠN --}}
                            <a href="{{ route('orders.show', $order->id) }}"
                               class="btn btn-outline-secondary btn-sm btn-action">
                                Chi tiết
                            </a>

                            {{-- XEM PHIẾU HOÀN TIỀN --}}
                            @if($order->refundRequest)
                                <a href="{{ route('refund.show', $order->refundRequest->id) }}"
                                   class="btn btn-outline-danger btn-sm btn-action">
                                    Xem phiếu hoàn tiền
                                </a>
                            @endif

                            {{-- KHÁCH XÁC NHẬN ĐÃ NHẬN HÀNG --}}
                            @if($order->status == 3 && !$order->customer_confirmed)
                                <form action="{{ route('orders.confirmReceived', $order->id) }}"
                                      method="POST"
                                      class="d-inline">
                                    @csrf
                                    <button type="submit"
                                            class="btn btn-success btn-sm btn-action btn-confirm">
                                        Đã nhận hàng
                                    </button>
                                </form>
                            @endif

                            {{-- HUỶ ĐƠN --}}
                            @if($order->canCancel())
                                <form action="{{ route('orders.cancel', $order->id) }}"
                                      method="POST"
                                      class="d-inline cancel-form">
                                    @csrf
                                    @method('PUT')

                                    <input type="hidden" name="cancel_reason" class="cancel-reason">

                                    <button type="button"
                                            class="btn btn-outline-danger btn-sm btn-action btn-cancel">
                                        Huỷ đơn
                                    </button>
                                </form>
                            @endif

                            {{-- MUA LẠI --}}
                            @if($order->isCompleted() || $order->isCancelled())
                                <form action="{{ route('orders.reorder', $order->id) }}"
                                      method="POST"
                                      class="d-inline">
                                    @csrf
                                    <button type="submit"
                                            class="btn btn-outline-primary btn-sm btn-action">
                                        Mua lại
                                    </button>
                                </form>
                            @endif

                            {{-- TRẢ HÀNG / HOÀN TIỀN --}}
                            @if($order->isCompleted() && !$order->refundRequest)
                                <button type="button"
                                        class="btn btn-outline-danger btn-sm btn-action btn-refund"
                                        data-url="{{ route('refund.create', $order->id) }}">
                                    Trả hàng / Hoàn tiền
                                </button>
                            @endif

                            {{-- ĐÁNH GIÁ TẤT CẢ --}}
                            @php
                                $canReviewAll = $order->isCompleted()
                                    && !$order->refundRequest
                                    && $order->items->where('review', null)->count() > 0;
                            @endphp

                            @if($canReviewAll)
                                <a href="{{ route('reviews.create', $order->id) }}"
                                   class="btn btn-primary btn-sm btn-action">
                                    Đánh giá tất cả
                                </a>
                            @endif

                            {{-- TRẠNG THÁI HOÀN TIỀN --}}
                            @if($order->refundRequest)
                                @if($order->refundRequest->status == 'pending')
                                    <span class="badge bg-warning text-dark badge-status-note">
                                        Đang chờ xử lý hoàn tiền
                                    </span>
                                @elseif($order->refundRequest->status == 'approved')
                                    <span class="badge bg-primary badge-status-note">
                                        Yêu cầu hoàn tiền đã được duyệt
                                    </span>
                                @elseif($order->refundRequest->status == 'refunded')
                                    <span class="badge bg-success badge-status-note">
                                        Đã hoàn tiền
                                    </span>
                                @elseif($order->refundRequest->status == 'rejected')
                                    <span class="badge bg-danger badge-status-note">
                                        Yêu cầu hoàn tiền bị từ chối
                                    </span>
                                @endif
                            @endif

                        </div>

                        @if($order->refundRequest && $order->refundRequest->status == 'rejected' && $order->refundRequest->admin_note)
                            <div class="text-danger small mt-2 text-end">
                                Lý do: {{ $order->refundRequest->admin_note }}
                            </div>
                        @endif
                    </div>
                </div>

            </div>

        @empty
            <div class="order-empty">
                Bạn chưa có đơn hàng nào.
            </div>
        @endforelse

        <div class="mt-4">
            {{ $orders->withQueryString()->links('vendor.pagination.custom-blue') }}
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let searchTimer;

function autoSearch() {
    clearTimeout(searchTimer);

    searchTimer = setTimeout(function () {
        document.getElementById('orderSearchForm').submit();
    }, 500);
}

document.querySelectorAll('.btn-cancel').forEach(function(button) {
    button.addEventListener('click', function () {
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
            if (result.isConfirmed) {
                input.value = result.value;
                form.submit();
            }
        });
    });
});

document.querySelectorAll('.btn-confirm').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
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

    document.querySelectorAll('.btn-toggle-items').forEach(function(button) {
        button.addEventListener('click', function() {
            const orderBox = this.closest('.order-box');
            const extraItems = orderBox.querySelectorAll('.extra-item');
            const isHidden = Array.from(extraItems).some(item => item.classList.contains('d-none'));

            if (isHidden) {
                extraItems.forEach(item => item.classList.remove('d-none'));
                this.textContent = this.dataset.less;
            } else {
                extraItems.forEach(item => item.classList.add('d-none'));
                this.textContent = this.dataset.more;
            }
        });
    });
});
</script>
@endpush