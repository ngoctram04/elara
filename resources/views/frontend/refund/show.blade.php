@extends('layouts.frontend')

@section('title', 'Chi tiết phiếu hoàn tiền')

@push('styles')
<style>
.refund-detail-page{
    --rf-border:#e7eef6;
    --rf-text:#0f172a;
    --rf-muted:#64748b;
    --rf-soft:#f8fbff;
    --rf-primary:#0d6efd;
    --rf-danger:#dc3545;
    --rf-success:#15803d;
    --rf-warning:#ea580c;
}

.refund-wrapper{
    max-width:920px;
    margin:auto;
}

.refund-page-title{
    font-size:26px;
    font-weight:800;
    color:var(--rf-text);
    margin-bottom:4px;
}

.refund-page-subtitle{
    font-size:14px;
    color:var(--rf-muted);
    margin-bottom:20px;
}

.refund-card{
    border:none;
    border-radius:20px;
    box-shadow:0 12px 28px rgba(15, 23, 42, 0.06);
    overflow:hidden;
}

.refund-card .card-body{
    padding:22px;
}

.refund-header{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:16px;
    padding-bottom:16px;
    margin-bottom:18px;
    border-bottom:1px solid var(--rf-border);
}

.refund-code{
    font-size:24px;
    font-weight:800;
    color:var(--rf-primary);
    line-height:1.2;
}

.refund-created{
    font-size:13px;
    color:var(--rf-muted);
    margin-top:4px;
}

.refund-section{
    margin-bottom:18px;
}

.refund-section:last-child{
    margin-bottom:0;
}

.refund-info-box{
    background:var(--rf-soft);
    border:1px solid var(--rf-border);
    border-radius:16px;
    padding:14px 16px;
}

.refund-row{
    display:flex;
    gap:16px;
    padding:10px 0;
    border-bottom:1px dashed #e6edf5;
}

.refund-row:last-child{
    border-bottom:none;
    padding-bottom:0;
}

.refund-label{
    width:220px;
    flex-shrink:0;
    font-weight:700;
    color:#334155;
    font-size:14px;
}

.refund-value{
    color:var(--rf-text);
    font-size:14px;
    line-height:1.6;
    min-width:0;
}

.refund-order-link{
    text-decoration:none;
    font-weight:700;
    color:var(--rf-primary);
}

.refund-order-link:hover{
    text-decoration:underline;
}

.section-title{
    font-size:16px;
    font-weight:700;
    color:var(--rf-text);
    margin-bottom:12px;
    text-align:center;
}

.product-list{
    display:flex;
    flex-direction:column;
    gap:12px;
}

.product-box{
    display:flex;
    gap:14px;
    border:1px solid var(--rf-border);
    background:#fff;
    border-radius:16px;
    padding:14px;
    align-items:flex-start;
    transition:all .2s ease;
}

.product-box:hover{
    box-shadow:0 8px 18px rgba(37, 99, 235, 0.06);
    border-color:#cfe0ff;
}

.product-box img{
    width:78px;
    height:78px;
    object-fit:cover;
    border-radius:12px;
    border:1px solid var(--rf-border);
    background:#fff;
    flex-shrink:0;
}

.product-content{
    flex:1;
    min-width:0;
}

.product-name{
    font-size:15px;
    font-weight:700;
    color:var(--rf-text);
    margin-bottom:4px;
    line-height:1.5;
}

.product-meta{
    font-size:13px;
    color:var(--rf-muted);
    line-height:1.6;
    margin-bottom:2px;
}

.product-bottom{
    margin-top:8px;
    display:flex;
    flex-wrap:wrap;
    gap:8px 14px;
    align-items:center;
}

.info-chip{
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:6px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:600;
    background:#f8fafc;
    color:#334155;
    border:1px solid #e5e7eb;
}

.info-chip.sealed{
    background:#ecfdf3;
    color:#15803d;
    border-color:#bbf7d0;
}

.info-chip.broken{
    background:#fef2f2;
    color:#b91c1c;
    border-color:#fecaca;
}

.refund-money{
    font-size:15px;
    font-weight:800;
    color:var(--rf-danger);
}

.amount-positive{
    font-weight:800;
    color:var(--rf-danger);
}

.amount-deduction{
    font-weight:800;
    color:var(--rf-warning);
}

.amount-final{
    font-weight:800;
    color:var(--rf-success);
}

.total-refund-box{
    background:linear-gradient(180deg, #fff 0%, #f8fbff 100%);
    border:1px solid #dbeafe;
    border-radius:16px;
    padding:16px 18px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
}

.total-refund-label{
    font-size:14px;
    font-weight:700;
    color:#334155;
}

.total-refund-value{
    font-size:24px;
    font-weight:800;
    color:var(--rf-danger);
    line-height:1.2;
}

.gallery{
    display:flex;
    flex-wrap:wrap;
    gap:12px;
}

.gallery-item{
    width:132px;
    height:132px;
    border-radius:14px;
    overflow:hidden;
    border:1px solid var(--rf-border);
    background:#fff;
    box-shadow:0 4px 10px rgba(15, 23, 42, 0.04);
}

.gallery-item img{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
}

@media (max-width: 767.98px){
    .refund-page-title{
        font-size:22px;
    }

    .refund-card .card-body{
        padding:16px;
    }

    .refund-header{
        flex-direction:column;
        align-items:flex-start;
        margin-bottom:16px;
        padding-bottom:14px;
    }

    .refund-code{
        font-size:22px;
    }

    .refund-row{
        flex-direction:column;
        gap:4px;
        padding:10px 0;
    }

    .refund-label{
        width:auto;
        font-size:13px;
    }

    .refund-value{
        font-size:13px;
    }

    .product-box{
        padding:12px;
    }

    .product-box img{
        width:68px;
        height:68px;
    }

    .product-name{
        font-size:14px;
    }

    .product-meta{
        font-size:12.5px;
    }

    .total-refund-box{
        flex-direction:column;
        align-items:flex-start;
    }

    .total-refund-value{
        font-size:22px;
    }

    .gallery-item{
        width:96px;
        height:96px;
    }
}
</style>
@endpush

@section('content')
<div class="container py-4 refund-wrapper refund-detail-page">

    @php
        $productRefundTotal = (float) $refund->items->sum(function ($i) {
            return (float) ($i->pivot->refund_amount ?? 0);
        });

        $finalRefundTotal = (float) ($refund->refund_total ?? 0);

        $orderShippingFee = (float) (optional($refund->order)->shipping_fee ?? 0);

        /*
        |--------------------------------------------------------------------------
        | XỬ LÝ HIỂN THỊ PHÍ SHIP
        |--------------------------------------------------------------------------
        | 1. Nếu tiền hoàn thực tế nhỏ hơn tiền sản phẩm hoàn
        |    => có khoản ship bị khấu trừ trực tiếp
        | 2. Nếu không bị khấu trừ trực tiếp nhưng đơn gốc có ship
        |    => ship không hoàn
        | 3. Nếu không có ship thật
        |    => hiển thị 0đ
        */
        $shippingDeducted = max(0, $productRefundTotal - $finalRefundTotal);

        $shippingLabel = 'Phí vận chuyển đã khấu trừ';
        $shippingDisplayAmount = $shippingDeducted;

        if ($shippingDeducted > 0) {
            $shippingLabel = 'Phí vận chuyển đã khấu trừ';
            $shippingDisplayAmount = $shippingDeducted;
        } elseif ($orderShippingFee > 0) {
            $shippingLabel = 'Phí vận chuyển không hoàn';
            $shippingDisplayAmount = $orderShippingFee;
        } else {
            $shippingLabel = 'Phí vận chuyển đã khấu trừ';
            $shippingDisplayAmount = 0;
        }

        $refundReason = trim((string) \Illuminate\Support\Str::before(
            (string) ($refund->reason ?? ''),
            'Chi tiết sản phẩm khách chọn:'
        ));

        $isFullOrderRefund = false;

        if ($refund->order && $refund->order->relationLoaded('items') && $refund->order->items) {
            $refundQty = (int) $refund->items->sum(function ($item) {
                return (int) ($item->pivot->quantity ?? 0);
            });

            $orderQty = (int) $refund->order->items->sum(function ($orderItem) {
                return (int) ($orderItem->quantity ?? 0);
            });

            $isFullOrderRefund = $refundQty > 0 && $refundQty === $orderQty;
        }
    @endphp

    <h4 class="refund-page-title">Chi tiết phiếu hoàn tiền</h4>
    <div class="refund-page-subtitle">
        Theo dõi sản phẩm yêu cầu hoàn, số tiền hoàn và trạng thái xử lý của cửa hàng
    </div>

    <div class="card refund-card">
        <div class="card-body">

            <div class="refund-header">
                <div>
                    <div class="refund-code">
                        RH{{ str_pad($refund->id, 5, '0', STR_PAD_LEFT) }}
                    </div>
                    <div class="refund-created">
                        Ngày gửi: {{ optional($refund->created_at)->format('d/m/Y H:i') }}
                    </div>
                </div>

                <div>
                    <span class="badge {{ $refund->status_badge_class }}">
                        {{ $refund->status_label }}
                    </span>
                </div>
            </div>

            <div class="refund-section">
                <div class="refund-info-box">
                    <div class="refund-row">
                        <div class="refund-label">Đơn hàng</div>
                        <div class="refund-value">
                            <a href="{{ route('orders.show', $refund->order_id) }}" class="refund-order-link">
                                DH{{ str_pad($refund->order_id, 5, '0', STR_PAD_LEFT) }}
                            </a>
                        </div>
                    </div>

                    <div class="refund-row">
                        <div class="refund-label">Lý do yêu cầu</div>
                        <div class="refund-value">
                            {{ $refundReason !== '' ? $refundReason : 'Không có lý do' }}
                        </div>
                    </div>
                </div>
            </div>

            @if($refund->items->count())
                <div class="refund-section">
                    <div class="section-title">Sản phẩm hoàn tiền</div>

                    <div class="product-list">
                        @foreach($refund->items as $item)
                            @php
                                $variant = $item->variant ?? null;
                                $product = $variant->product ?? null;
                                $img = $variant->mainImage->image_path
                                    ?? $product->mainImage->image_path
                                    ?? null;

                                $condition = $item->pivot->condition_status ?? 'sealed';

                                $conditionText = match($condition) {
                                    'sealed'  => 'Còn nguyên seal',
                                    'damaged' => 'Bị hư hỏng',
                                    'broken'  => 'Bị vỡ',
                                    default   => 'Không xác định'
                                };

                                $refundMoney = (float) ($item->pivot->refund_amount ?? 0);
                            @endphp

                            <div class="product-box">
                                <img
                                    src="{{ $img ? asset('storage/' . $img) : 'https://via.placeholder.com/78' }}"
                                    alt="product"
                                >

                                <div class="product-content">
                                    <div class="product-name">
                                        {{ $product->name ?? 'Sản phẩm' }}
                                    </div>

                                    <div class="product-meta">
                                        {{ $variant->attribute_name ?? 'Phân loại' }}:
                                        {{ $variant->attribute_value ?? '-' }}
                                    </div>

                                    <div class="product-meta">
                                        Số lượng hoàn: {{ $item->pivot->quantity ?? 0 }}
                                    </div>

                                    <div class="product-bottom">
                                        <span class="info-chip {{ in_array($condition, ['damaged', 'broken']) ? 'broken' : 'sealed' }}">
                                            <i class="bi {{ in_array($condition, ['damaged', 'broken']) ? 'bi-exclamation-octagon' : 'bi-shield-check' }}"></i>
                                            {{ $conditionText }}
                                        </span>

                                        <span class="refund-money">
                                            Tiền sản phẩm: {{ number_format($refundMoney, 0, ',', '.') }}₫
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="refund-section">
                <div class="refund-info-box">
                    <div class="refund-row">
                        <div class="refund-label">Tổng tiền sản phẩm hoàn</div>
                        <div class="refund-value amount-positive">
                            {{ number_format($productRefundTotal, 0, ',', '.') }}₫
                        </div>
                    </div>

                    <div class="refund-row">
                        <div class="refund-label">{{ $shippingLabel }}</div>
                        <div class="refund-value amount-deduction">
                            {{ number_format($shippingDisplayAmount, 0, ',', '.') }}₫
                        </div>
                    </div>

                    <div class="refund-row">
                        <div class="refund-label">Số tiền hoàn thực tế</div>
                        <div class="refund-value amount-final">
                            {{ number_format($finalRefundTotal, 0, ',', '.') }}₫
                        </div>
                    </div>

                    <div class="refund-row">
                        <div class="refund-label">Hình thức hoàn</div>
                        <div class="refund-value">
                            {{ $isFullOrderRefund ? 'Hoàn toàn bộ đơn hàng' : 'Hoàn một phần đơn hàng' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="refund-section">
                <div class="total-refund-box">
                    <div class="total-refund-label">
                        Tổng số tiền hoàn
                    </div>
                    <div class="total-refund-value">
                        {{ number_format($finalRefundTotal, 0, ',', '.') }}₫
                    </div>
                </div>
            </div>

            @if($refund->media->where('type', 'image')->count())
                <div class="refund-section">
                    <div class="section-title">Ảnh minh chứng</div>

                    <div class="gallery">
                        @foreach($refund->media->where('type', 'image') as $media)
                            <div class="gallery-item">
                                <img src="{{ asset('storage/' . $media->file_path) }}" alt="refund-proof">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection