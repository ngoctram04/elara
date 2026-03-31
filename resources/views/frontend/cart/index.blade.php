@extends('layouts.frontend')
@section('title', 'Giỏ hàng')

@push('styles')
<link rel="stylesheet" href="{{ asset('frontend/css/cart.css') }}">
@endpush

@section('content')
<div class="cart-page">
    <x-breadcrumb :items="[
        ['label' => 'Trang chủ', 'url' => url('/')],
        ['label' => 'Giỏ hàng']
    ]" />

    <div class="container py-4">
        <div class="cart-header mb-4">
            <h4 class="cart-title mb-1">Giỏ hàng của bạn</h4>
            <div class="cart-subtitle">Kiểm tra sản phẩm, chọn ưu đãi và tiến hành thanh toán</div>
        </div>

        @if(empty($cart))
            <div class="cart-empty text-center">
                <h5>Giỏ hàng của bạn đang trống</h5>
                <p>Khám phá thêm nhiều sản phẩm phù hợp với bạn nhé</p>
                <a href="{{ route('shop') }}" class="btn btn-primary px-4">
                    Mua sắm ngay
                </a>
            </div>
        @else
            <div class="cart-wrapper">
                <div class="table-responsive">
                    <table class="table align-middle cart-table mb-0">
                        <thead>
                            <tr>
                                <th width="44">
                                    <input type="checkbox" id="check-all">
                                </th>
                                <th>Sản phẩm</th>
                                <th width="160">Biến thể</th>
                                <th width="170" class="text-center">Số lượng</th>
                                <th width="150" class="text-end">Thành tiền</th>
                                <th width="60" class="text-end"></th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($cart as $item)
                                <tr data-row="{{ $item['variant_id'] }}">
                                    <td>
                                        <input type="checkbox"
                                            class="js-check-item"
                                            value="{{ $item['variant_id'] }}">
                                    </td>

                                    <td>
                                        <div class="product-cell">
                                            <a href="{{ route('products.show', $item['slug']) }}" class="product-link">
                                                <img src="{{ $item['image'] ? asset('storage/'.$item['image']) : asset('images/no-image.png') }}"
                                                    class="cart-img"
                                                    alt="{{ $item['name'] }}">
                                            </a>

                                            <div class="product-info">
                                                <a href="{{ route('products.show', $item['slug']) }}"
                                                    class="product-name product-link">
                                                    {{ $item['name'] }}
                                                </a>

                                                <div class="product-meta">
                                                    @if(isset($item['original_price']) && $item['original_price'] > $item['price'])
                                                        <span class="old-price">{{ number_format($item['original_price']) }}đ</span>
                                                    @endif
                                                    <span class="final-price">{{ number_format($item['price']) }}đ</span>
                                                </div>

                                                <small class="variant-tag d-inline d-lg-none mt-2">
                                                    {{ $item['variant'] }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="variant-box">
                                            <select class="form-select form-select-sm js-change-variant"
                                                    data-old="{{ $item['variant_id'] }}">
                                                @foreach($item['variants'] as $variant)
                                                    <option value="{{ $variant->id }}"
                                                            data-stock="{{ $variant->stock_quantity }}"
                                                            data-price="{{ $variant->final_price ?? $variant->price }}"
                                                            @selected($variant->id == $item['variant_id'])
                                                            @disabled($variant->stock_quantity == 0)>
                                                        {{ $variant->attribute_value }}
                                                        @if($variant->stock_quantity == 0)
                                                            (Hết hàng)
                                                        @else
                                                            (còn {{ $variant->stock_quantity }})
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <div class="qty-box">
                                            <button type="button" class="qty-btn js-minus" data-id="{{ $item['variant_id'] }}">−</button>

                                            <input type="number"
                                                class="qty-input js-qty"
                                                value="{{ $item['quantity'] }}"
                                                min="1"
                                                data-id="{{ $item['variant_id'] }}"
                                                data-price="{{ $item['price'] }}"
                                                data-stock="{{ $item['stock'] }}">

                                            <button type="button" class="qty-btn js-plus" data-id="{{ $item['variant_id'] }}">+</button>
                                        </div>

                                        <div class="stock-text js-stock-text">
                                            @if($item['stock'] <= 5)
                                                <span class="badge bg-danger">Sắp hết ({{ $item['stock'] }})</span>
                                            @else
                                                Còn {{ $item['stock'] }}
                                            @endif
                                        </div>
                                    </td>

                                    <td class="text-end">
                                        <span class="subtotal js-subtotal"
                                            data-id="{{ $item['variant_id'] }}"
                                            data-value="{{ $item['sub_total'] }}">
                                            {{ number_format($item['sub_total']) }}đ
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <button type="button"
                                            class="btn-remove js-remove"
                                            data-id="{{ $item['variant_id'] }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="confirm-delete-box" class="confirm-box d-none">
                <div class="confirm-content">
                    <div class="confirm-title mb-2">Xóa sản phẩm?</div>
                    <div class="confirm-text mb-3">Sản phẩm sẽ được xóa khỏi giỏ hàng của bạn.</div>
                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-danger btn-sm px-3" id="confirm-delete-yes">Xóa</button>
                        <button class="btn btn-light btn-sm px-3" id="confirm-delete-no">Hủy</button>
                    </div>
                </div>
            </div>

            <div class="cart-total-card mt-4">
                <div class="cart-total-left">
                    <a href="{{ route('shop') }}" class="btn btn-outline-secondary">
                        ← Tiếp tục mua hàng
                    </a>

                    <div class="cart-count-box">
                        Tổng sản phẩm đã chọn:
                        <strong class="js-count">{{ collect($cart)->sum('quantity') }}</strong>
                    </div>
                </div>

                <div class="cart-total-right">
                    <div class="voucher-action">
                        <button class="btn btn-outline-primary btn-sm"
                            type="button"
                            data-bs-toggle="modal"
                            data-bs-target="#voucherModal">
                            <i class="bi bi-ticket-perforated me-1"></i> Chọn mã
                        </button>

                        <div class="small text-success mt-2 d-none" id="voucher-applied"></div>
                    </div>

                    <div class="summary-checkout-group">
                        <div class="summary-box">
                            <div class="summary-line">
                                <span>Tổng tiền hàng</span>
                                <span id="summary-subtotal" data-value="0">0đ</span>
                            </div>

                            <div id="summary-voucher-row" class="summary-line text-success d-none">
                                <span>Giảm giá voucher</span>
                                <span id="summary-voucher" data-value="0">-0đ</span>
                            </div>

                            <div id="summary-birthday-row" class="summary-line text-danger d-none">
                                <span>Ưu đãi sinh nhật</span>
                                <span id="summary-birthday" data-percent="{{ $birthdayPercent ?? 0 }}">-0đ</span>
                            </div>

                            <div id="summary-saving-row" class="summary-line text-danger d-none">
                                <span>Tiết kiệm</span>
                                <span id="summary-saving">-0đ</span>
                            </div>

                            <hr class="my-2">

                            <div class="summary-total">
                                <span>Tổng thanh toán</span>
                                <span class="total-price js-total" data-value="0" data-subtotal="0">0đ</span>
                            </div>
                        </div>

                        <form id="checkout-form"
                            action="{{ route('checkout.fromCart') }}"
                            method="POST"
                            class="checkout-form">
                            @csrf
                            <div id="selected-items"></div>
                            <input type="hidden" name="promotion_code" id="promotion-code-hidden">

                            <button class="btn btn-success btn-lg fw-bold checkout-btn">
                                Thanh toán
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        @if(!empty($suggestProducts) && count($suggestProducts))
            <div class="cart-suggest-section mt-5">
                <div class="d-flex justify-content-between align-items-center mb-3 cart-suggest-head">
                    <h5 class="fw-bold mb-0">Có thể bạn cũng thích</h5>
                    <a href="{{ route('shop') }}" class="text-decoration-none small">
                        Xem thêm →
                    </a>
                </div>

                <div class="row g-3">
                    @foreach($suggestProducts as $product)
                        <div class="col-6 col-md-3">
                            @include('frontend.partials.product-card-category', [
                                'product' => $product
                            ])
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <div class="modal fade" id="voucherModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow voucher-modal">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Chọn voucher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    @if(!empty($availablePromotions) && count($availablePromotions))
                        @foreach($availablePromotions as $promo)
                            <div class="voucher-item"
                                data-code="{{ $promo->code }}"
                                data-type="{{ $promo->discount_type }}"
                                data-value="{{ $promo->discount_value }}"
                                data-min="{{ $promo->min_order_value ?? 0 }}"
                                data-max="{{ $promo->max_discount ?? 0 }}"
                                style="cursor:pointer;">

                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="fw-semibold text-danger voucher-name">
                                            {{ $promo->name }}
                                        </div>

                                        <div class="small text-muted">
                                            Mã: {{ $promo->code }}
                                        </div>

                                        @if($promo->end_date)
                                            @php
                                                $daysLeft = now()->diffInDays($promo->end_date, false);
                                            @endphp

                                            <div class="small {{ $daysLeft <= 3 ? 'text-danger fw-semibold' : 'text-muted' }}">
                                                Hết hạn: {{ $promo->end_date->format('d/m/Y') }}
                                            </div>
                                        @endif

                                        @if(!empty($promo->min_order_value))
                                            <div class="small text-muted">
                                                Đơn tối thiểu: {{ number_format($promo->min_order_value) }}đ
                                            </div>
                                        @endif
                                    </div>

                                    <div class="text-end small fw-semibold discount-preview text-success"></div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-muted text-center py-3">
                            Không có voucher khả dụng
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.cartConfig = {
        csrfToken: "{{ csrf_token() }}"
    };

    window.cartRoutes = {
        changeQty: "{{ route('cart.changeQty') }}",
        changeVariant: "{{ route('cart.changeVariant') }}",
        applyPromotion: "{{ route('cart.applyPromotion') }}",
        remove: "{{ url('/cart/remove') }}"
    };
</script>
@endpush