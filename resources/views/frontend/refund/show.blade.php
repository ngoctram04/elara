@extends('layouts.frontend')

@section('title', 'Chi tiết phiếu hoàn tiền')

@push('styles')
<style>
.refund-wrapper{max-width:900px;margin:auto}
.refund-card{border-radius:16px;border:0;box-shadow:0 8px 20px rgba(0,0,0,.06)}
.refund-header{border-bottom:1px solid #eee;padding-bottom:12px;margin-bottom:18px}
.refund-code{font-size:20px;font-weight:700;color:#0d6efd}
.refund-row{margin-bottom:12px}
.refund-label{font-weight:600;color:#555;width:160px;display:inline-block}

.product-box{
    display:flex;gap:14px;
    border:1px solid #eee;
    border-radius:12px;
    padding:10px;margin-bottom:10px;
    align-items:center;
}
.product-box img{
    width:70px;height:70px;
    object-fit:cover;border-radius:10px;
}

.gallery img{
    width:120px;height:120px;
    object-fit:cover;
    border-radius:10px;
    border:1px solid #eee;
    margin-right:10px;margin-bottom:10px;
}
</style>
@endpush

@section('content')
<div class="container py-4 refund-wrapper">

<h4 class="mb-3 fw-bold">Phiếu yêu cầu hoàn tiền</h4>

<div class="card refund-card">
<div class="card-body">

{{-- HEADER --}}
<div class="refund-header d-flex justify-content-between align-items-center">
    <div>
        <div class="refund-code">
            RH{{ str_pad($refund->id,5,'0',STR_PAD_LEFT) }}
        </div>
        <small class="text-muted">
            Ngày gửi: {{ $refund->created_at->format('d/m/Y H:i') }}
        </small>
    </div>

    <div>
        <span class="badge {{ $refund->status_badge_class }}">
            {{ $refund->status_label }}
        </span>
    </div>
</div>

{{-- ĐƠN HÀNG --}}
<div class="refund-row">
    <span class="refund-label">Đơn hàng:</span>
    <a href="{{ route('orders.show',$refund->order_id) }}">
        #{{ $refund->order_id }}
    </a>
</div>

{{-- ⭐ SẢN PHẨM HOÀN --}}
@if($refund->items->count())
<div class="mb-3">
    <div class="fw-semibold mb-2">Sản phẩm hoàn tiền:</div>

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
            'damaged' => 'Bị vỡ',
            default   => 'Không xác định'
        };

        $refundMoney = $item->pivot->refund_amount ?? 0;
    @endphp

    <div class="product-box">

        <img src="{{ $img ? asset('storage/'.$img) : 'https://via.placeholder.com/70' }}">

        <div>

            <div class="fw-semibold">
                {{ $product->name ?? 'Sản phẩm' }}
            </div>

            <small class="text-muted">
                {{ $variant->attribute_name ?? 'Phân loại' }}:
                {{ $variant->attribute_value ?? '-' }}
            </small><br>

            <small>Số lượng hoàn: {{ $item->pivot->quantity }}</small><br>

            <small>Tình trạng: {{ $conditionText }}</small><br>

            <span class="text-danger fw-bold">
                Hoàn: {{ number_format($refundMoney) }}₫
            </span>

        </div>

    </div>

    @endforeach
</div>

@endif


{{-- ⭐ TỔNG TIỀN HOÀN --}}
@php
$totalRefund = $refund->items->sum(fn($i) => $i->pivot->refund_amount ?? 0);
@endphp

<div class="refund-row">
    <span class="refund-label">Tổng tiền hoàn:</span>
    <span class="text-danger fw-bold">
        {{ number_format($totalRefund) }}₫
    </span>
</div>


{{-- LÝ DO --}}
<div class="refund-row">
    <span class="refund-label">Lý do:</span>
    {{ $refund->reason }}
</div>


{{-- ẢNH MINH CHỨNG --}}
@if($refund->media->where('type','image')->count())

<div class="mt-3">
    <div class="fw-semibold mb-2">Ảnh minh chứng:</div>

    <div class="gallery">
        @foreach($refund->media->where('type','image') as $media)
            <img src="{{ asset('storage/'.$media->file_path) }}">
        @endforeach
    </div>
</div>

@endif

</div>
</div>

</div>
@endsection