@extends('layouts.admin')
@section('title', 'Chi tiết đơn hàng')

@section('content')

<style>
    .order-detail-page .page-title {
        font-size: 20px;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 4px;
        line-height: 1.4;
    }

    .order-detail-page .page-subtitle {
        font-size: 13px;
        color: #6b7280;
        line-height: 1.5;
    }

    .order-detail-page .info-card,
    .order-detail-page .action-card,
    .order-detail-page .product-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        background: #fff;
        overflow: hidden;
    }

    .order-detail-page .card-body {
        padding: 20px;
    }

    .order-detail-page .section-title {
        font-size: 15px;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 16px;
    }

    .order-detail-page .btn {
        border-radius: 10px;
        font-size: 14px;
        font-weight: 500;
        min-height: 40px;
        padding: 0 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .order-detail-page .btn-sm {
        min-height: 36px;
        padding: 0 12px;
    }

    .order-detail-page .detail-row {
        margin-bottom: 14px;
    }

    .order-detail-page .detail-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 4px;
    }

    .order-detail-page .detail-value {
        font-size: 14px;
        color: #111827;
        line-height: 1.6;
    }

    .order-detail-page .detail-muted {
        font-size: 13px;
        color: #6b7280;
        line-height: 1.5;
    }

    .order-detail-page .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 28px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 500;
        line-height: 1.2;
        white-space: nowrap;
    }

    .order-detail-page .price-big {
        font-size: 22px;
        font-weight: 700;
        color: #dc2626;
        line-height: 1.4;
    }

    .order-detail-page .form-label {
        font-size: 13px;
        font-weight: 500;
        color: #64748b;
        margin-bottom: 6px;
    }

    .order-detail-page .form-control,
    .order-detail-page .form-select {
        border-radius: 10px;
        border: 1px solid #dbe2ea;
        min-height: 40px;
        font-size: 14px;
        box-shadow: none;
    }

    .order-detail-page .form-control:focus,
    .order-detail-page .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.12rem rgba(13, 110, 253, 0.12);
    }

    .order-detail-page textarea.form-control {
        min-height: 100px;
        padding-top: 10px;
        padding-bottom: 10px;
    }

    .order-detail-page .upload-box {
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #fafafa;
    }

    .order-detail-page .upload-box:hover {
        border-color: #3b82f6;
        background: #f8fbff;
    }

    .order-detail-page .upload-text {
        font-size: 14px;
        color: #6b7280;
    }

    .order-detail-page .preview-img {
        max-width: 100%;
        max-height: 160px;
        display: none;
        margin: 12px auto 0;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
    }

    .order-detail-page .delivery-img {
        width: 100%;
        max-height: 220px;
        object-fit: cover;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        margin-top: 8px;
    }

    .order-detail-page .product-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 16px 0;
        border-bottom: 1px solid #eef2f7;
    }

    .order-detail-page .product-item:last-child {
        border-bottom: 0;
    }

    .order-detail-page .product-image {
        width: 72px;
        height: 72px;
        border-radius: 12px;
        object-fit: cover;
        border: 1px solid #e5e7eb;
        flex-shrink: 0;
    }

    .order-detail-page .product-name {
        font-size: 14px;
        font-weight: 600;
        color: #111827;
        line-height: 1.5;
        margin-bottom: 4px;
    }

    .order-detail-page .product-variant,
    .order-detail-page .product-qty,
    .order-detail-page .batch-list li {
        font-size: 13px;
        color: #6b7280;
        line-height: 1.6;
    }

    .order-detail-page .product-price {
        font-size: 15px;
        font-weight: 600;
        color: #dc2626;
        white-space: nowrap;
        flex-shrink: 0;
        text-align: right;
    }

    .order-detail-page .batch-title {
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-top: 8px;
        margin-bottom: 4px;
    }

    .order-detail-page .summary-box {
        border-top: 1px solid #eef2f7;
        margin-top: 14px;
        padding-top: 18px;
    }

    .order-detail-page .summary-row {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 10px;
        font-size: 14px;
        color: #334155;
        line-height: 1.5;
    }

    .order-detail-page .summary-row.discount {
        color: #16a34a;
    }

    .order-detail-page .summary-total {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px dashed #dbe2ea;
        font-size: 18px;
        font-weight: 700;
        color: #dc2626;
    }

    .order-detail-page .summary-note {
        margin-top: 8px;
        font-size: 12.5px;
        color: #6b7280;
        text-align: right;
        line-height: 1.5;
    }

    .order-detail-page .alert {
        border-radius: 14px;
        font-size: 14px;
        line-height: 1.6;
    }

    .order-detail-page .alert hr {
        opacity: 0.12;
    }

    @media (max-width: 767.98px) {
        .order-detail-page .product-item {
            flex-wrap: wrap;
        }

        .order-detail-page .product-price {
            width: 100%;
            text-align: left;
            margin-top: 4px;
        }

        .order-detail-page .summary-row,
        .order-detail-page .summary-total {
            flex-direction: column;
            gap: 4px;
        }

        .order-detail-page .summary-note {
            text-align: left;
        }
    }
</style>

<div class="order-detail-page">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <div class="page-title">Chi tiết đơn hàng</div>
            <div class="page-subtitle">
                Đơn hàng DH{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
            </div>
        </div>

        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>
            Quay lại
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card info-card mb-3">
                <div class="card-body">
                    <div class="section-title">Thông tin giao hàng</div>

                    <div class="detail-row">
                        <span class="detail-label">Khách hàng</span>
                        <div class="detail-value">
                            {{ $order->receiver_name ?? optional($order->user)->name }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Số điện thoại</span>
                        <div class="detail-value">
                            {{ $order->receiver_phone }}
                        </div>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Địa chỉ</span>
                        <div class="detail-value">
                            {{ $order->receiver_address }}
                        </div>
                    </div>

                    <hr>

                    <div class="detail-row">
                        <span class="detail-label">Phương thức thanh toán</span>
                        <div class="detail-value mb-2">
                            {{ $order->payment_method_name }}
                        </div>
                        <span class="badge bg-{{ $order->payment_status_badge }}">
                            {{ $order->payment_status_name }}
                        </span>
                    </div>

                    <hr>

                    <div class="detail-row">
                        <span class="detail-label">Trạng thái đơn hàng</span>
                        <span class="badge bg-{{ $order->status_badge }}">
                            {{ $order->status_name }}
                        </span>

                        @if($order->status == 3 && !$order->customer_confirmed)
                            <div class="detail-muted mt-2 text-info">
                                Chờ khách xác nhận
                            </div>
                        @endif
                    </div>

                    <hr>

                    <div class="detail-row">
                        <span class="detail-label">Ngày đặt</span>
                        <div class="detail-value">
                            {{ $order->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    @if($order->delivered_at)
                        <div class="detail-row">
                            <span class="detail-label">Ngày giao</span>
                            <div class="detail-value">
                                {{ \Carbon\Carbon::parse($order->delivered_at)->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    @endif

                    @if($order->delivery_image)
                        <div class="detail-row mb-0">
                            <span class="detail-label">Ảnh giao hàng</span>
                            <img
                                src="{{ asset('storage/' . $order->delivery_image) }}"
                                alt="Ảnh giao hàng"
                                class="delivery-img"
                            >
                        </div>
                    @endif

                    <hr>

                    <div class="detail-row mb-0">
                        <span class="detail-label">Tổng khách trả</span>
                        <div class="price-big">
                            {{ number_format($order->grand_total, 0, ',', '.') }}đ
                        </div>
                    </div>
                </div>
            </div>

            @if($order->status == 1 || $order->status == 2)
                <div class="card action-card mb-3">
                    <div class="card-body">
                        <div class="section-title">Cập nhật trạng thái</div>

                        <form method="POST"
                              action="{{ route('admin.orders.updateStatus', $order->id) }}"
                              enctype="multipart/form-data">
                            @csrf

                            <label class="form-label">Chọn trạng thái mới</label>
                            <select name="status" class="form-select mb-3" id="status_select">
                                @if($order->status == 1)
                                    <option value="2">Đang giao</option>
                                @endif

                                @if($order->status == 2)
                                    <option value="3">Đã giao</option>
                                @endif
                            </select>

                            <div id="proof_box" class="mb-3" style="display:none;">
                                <label class="form-label">Ảnh giao hàng *</label>

                                <div onclick="document.getElementById('file_input').click()" class="upload-box" id="upload_box">
                                    <div id="upload_text" class="upload-text">
                                        Bấm để chọn ảnh giao hàng
                                    </div>

                                    <img id="preview_img" class="preview-img" alt="Ảnh xem trước">
                                </div>

                                <input
                                    type="file"
                                    id="file_input"
                                    name="delivery_proof"
                                    accept="image/*"
                                    style="display:none"
                                    onchange="previewImage(event)"
                                >
                            </div>

                            <button type="submit" class="btn btn-success w-100">
                                Cập nhật
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            @if($order->status == 1)
                <div class="card action-card mb-3">
                    <div class="card-body">
                        <div class="section-title text-danger">Huỷ đơn hàng</div>

                        <form method="POST" action="{{ route('admin.orders.cancel', $order->id) }}">
                            @csrf

                            <label class="form-label">Lý do huỷ đơn</label>
                            <textarea
                                name="cancel_reason"
                                class="form-control mb-3"
                                placeholder="Nhập lý do huỷ..."
                                required
                            ></textarea>

                            <button type="submit" class="btn btn-danger w-100">
                                Huỷ đơn
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            @if($order->status == 4)
                <div class="alert alert-danger mt-3 mb-0">
                    <strong>Đơn hàng đã bị huỷ</strong>

                    <hr class="my-2">

                    <div>
                        <strong>Người huỷ:</strong>
                        {{ $order->cancelled_by == 'admin' ? 'Admin' : 'Khách hàng' }}
                    </div>

                    <div>
                        <strong>Thời gian:</strong>
                        {{ optional($order->cancelled_at)->format('d/m/Y H:i') }}
                    </div>

                    <div>
                        <strong>Lý do:</strong>
                        {{ $order->cancel_reason }}
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-8">
            <div class="card product-card">
                <div class="card-body">
                    <div class="section-title">Sản phẩm trong đơn</div>

                    @foreach($order->items as $item)
                        @php
                            $image =
                                optional($item->variant->images->first())->image_path
                                ?? optional($item->variant->product->images->first())->image_path;
                        @endphp

                        <div class="product-item">
                            <img
                                src="{{ $image ? asset('storage/' . $image) : asset('images/no-image.png') }}"
                                alt="{{ $item->variant->product->name }}"
                                class="product-image"
                            >

                            <div class="flex-grow-1">
                                <div class="product-name">
                                    {{ $item->variant->product->name }}
                                </div>

                                <div class="product-variant">
                                    {{ $item->variant->attribute_name ?? '' }}
                                    {{ $item->variant->attribute_value ?? '' }}
                                </div>

                                <div class="product-qty">
                                    Số lượng: {{ $item->quantity }}
                                </div>

                                @if($item->batches && $item->batches->count())
                                    <div class="batch-title">Lấy từ lô:</div>
                                    <ul class="mb-0 ps-3 batch-list">
                                        @foreach($item->batches as $batch)
                                            <li>
                                                Mã lô:
                                                <strong>{{ optional($batch->stockImport)->lot_code ?? ('L' . $batch->stock_import_id) }}</strong>
                                                - SL lấy: {{ $batch->quantity }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="product-price">
                                {{ number_format($item->price * $item->quantity, 0, ',', '.') }}đ
                            </div>
                        </div>
                    @endforeach

                    <div class="summary-box">
                        <div class="summary-row">
                            <span>Tạm tính</span>
                            <strong>{{ number_format($order->subtotal, 0, ',', '.') }}đ</strong>
                        </div>

                        @if($order->discount > 0)
                            <div class="summary-row discount">
                                <span>Giảm giá</span>
                                <strong>-{{ number_format($order->discount, 0, ',', '.') }}đ</strong>
                            </div>
                        @endif

                        <div class="summary-row">
                            <span>Phí vận chuyển</span>
                            <strong>{{ number_format($order->shipping_fee, 0, ',', '.') }}đ</strong>
                        </div>

                        <div class="summary-total">
                            <span>Tổng khách trả</span>
                            <span>{{ number_format($order->grand_total, 0, ',', '.') }}đ</span>
                        </div>

                        <div class="summary-note">
                            (Doanh thu: {{ number_format($order->total + $order->shipping_fee, 0, ',', '.') }}đ)
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const statusSelect = document.getElementById('status_select');
    const box = document.getElementById('proof_box');

    if (!statusSelect || !box) return;

    function toggleBox() {
        box.style.display = statusSelect.value == 3 ? 'block' : 'none';
    }

    toggleBox();
    statusSelect.addEventListener('change', toggleBox);
});

function previewImage(event) {
    const file = event.target.files[0];
    if (!file) return;

    const img = document.getElementById('preview_img');
    const text = document.getElementById('upload_text');

    img.src = URL.createObjectURL(file);
    img.style.display = 'block';

    if (text) {
        text.innerText = 'Đã chọn ảnh';
    }
}
</script>

@endsection