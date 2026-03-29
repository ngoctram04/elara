@extends('layouts.frontend')
@section('title','Chi tiết đơn hàng')

@section('content')
@vite(['resources/css/order.css', 'resources/js/order.js'])

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

            @if($hasUnreviewedItems)
                <a href="{{ route('reviews.create', $order->id) }}"
                   class="btn btn-primary btn-sm px-3">
                    <i class="bi bi-star me-1"></i> Đánh giá tất cả
                </a>
            @endif

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

    {{-- THÔNG TIN CHUNG --}}
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

    {{-- TIMELINE --}}
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

    {{-- THÔNG TIN NHẬN HÀNG --}}
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

    {{-- DANH SÁCH SẢN PHẨM --}}
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
                        ? asset('storage/' . $image)
                        : asset('images/no-image.png');

                    $originalPrice = $variant->price ?? $item->price;
                    $finalPrice = $item->price;
                    $hasDiscount = $originalPrice > $finalPrice;
                    $lineTotal = $finalPrice * $item->quantity;
                @endphp

                <div class="product-item">
                    <div class="product-main">
                        <div class="product-left">
                            <img src="{{ $imageUrl }}"
                                 class="product-image"
                                 alt="{{ $product->name ?? 'Sản phẩm' }}">

                            <div class="product-info">
                                <div class="product-name">{{ $product->name ?? 'Sản phẩm' }}</div>

                                <div class="product-variant">
                                    {{ $variant->attribute_name ?? 'Phân loại' }}: {{ $variant->attribute_value ?? 'N/A' }}
                                </div>

                                <div class="product-qty">
                                    Số lượng: x{{ $item->quantity }}
                                </div>
                            </div>
                        </div>

                        <div class="product-right">
                            @if($hasDiscount)
                                <div class="product-old-price">
                                    {{ number_format($originalPrice) }}đ
                                </div>
                                <div class="product-sale-price">
                                    {{ number_format($finalPrice) }}đ
                                </div>
                            @else
                                <div class="product-single-price">
                                    {{ number_format($finalPrice) }}đ
                                </div>
                            @endif

                            @if($item->quantity > 1)
                                <div class="product-line-total">
                                    Tạm tính: {{ number_format($lineTotal) }}đ
                                </div>
                            @endif
                        </div>
                    </div>

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
                                                    <img src="{{ asset('storage/' . $media->file_path) }}"
                                                         class="review-media-image"
                                                         alt="review-image">
                                                @else
                                                    <video class="review-media-video" controls>
                                                        <source src="{{ asset('storage/' . $media->file_path) }}">
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

            {{-- TỔNG TIỀN --}}
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

                @if($order->isCompleted() || $order->refundRequest)
                    @php
                        $refund = $order->refundRequest;
                    @endphp

                    <div class="refund-box">
                        @if(!$refund)
                            <button type="button"
                                    class="btn btn-outline-danger btn-sm px-3 btn-refund"
                                    data-url="{{ route('refund.create', $order->id) }}">
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
@endsection