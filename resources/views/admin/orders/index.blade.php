@extends('layouts.admin')
@section('title', 'Quản lý đơn hàng')

@section('content')

@php
    $pendingCount = $pendingCount ?? 0;
    $processingCount = $processingCount ?? 0;
    $completedCount = $completedCount ?? 0;
    $cancelledCount = $cancelledCount ?? 0;
    $returnedCount = $returnedCount ?? 0;
@endphp

<style>
    .order-page .page-title {
        font-size: 20px;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 4px;
        line-height: 1.4;
    }

    .order-page .page-subtitle {
        font-size: 13px;
        color: #6b7280;
        line-height: 1.5;
    }

    .order-page .filter-card,
    .order-page .table-card {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        background: #fff;
        overflow: hidden;
    }

    .order-page .form-label {
        font-size: 13px;
        font-weight: 500;
        color: #64748b;
        margin-bottom: 6px;
    }

    .order-page .form-control,
    .order-page .form-select {
        height: 42px;
        border-radius: 10px;
        border: 1px solid #dbe2ea;
        font-size: 14px;
        color: #334155;
        box-shadow: none;
    }

    .order-page .form-control:focus,
    .order-page .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.12rem rgba(13, 110, 253, 0.12);
    }

    .order-page .btn {
        height: 42px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 500;
        padding: 0 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .order-page .btn i {
        font-size: 14px;
        line-height: 1;
    }

    .order-page .table {
        margin-bottom: 0;
    }

    .order-page .table thead th {
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        padding: 15px 14px;
        vertical-align: middle;
        white-space: nowrap;
        text-align: left;
    }

    .order-page .table thead th.text-center {
        text-align: center !important;
    }

    .order-page .table tbody td {
        padding: 16px 14px;
        border-color: #eef2f7;
        vertical-align: middle;
        font-size: 14px;
        color: #334155;
        line-height: 1.5;
    }

    .order-page .table tbody tr:hover {
        background: #fafcff;
    }

    .order-page .order-code {
        font-size: 14px;
        font-weight: 600;
        color: #0f172a;
        line-height: 1.4;
        white-space: nowrap;
    }

    .order-page .customer-name {
        font-size: 14px;
        font-weight: 500;
        color: #111827;
        line-height: 1.5;
        margin-bottom: 2px;
    }

    .order-page .customer-phone {
        font-size: 13px;
        color: #6b7280;
        line-height: 1.5;
    }

    .order-page .cancel-info,
    .order-page .reason-text,
    .order-page .confirm-text {
        font-size: 12.5px;
        line-height: 1.5;
        margin-top: 4px;
    }

    .order-page .price-text {
        font-size: 14px;
        font-weight: 500;
        color: #dc2626;
        line-height: 1.4;
        white-space: nowrap;
    }

    .order-page .date-text {
        font-size: 13px;
        color: #64748b;
        line-height: 1.4;
        white-space: nowrap;
    }

    .order-page .badge {
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

    .order-page .action-cell {
        text-align: center;
        vertical-align: middle;
    }

    .order-page .action-btn {
        min-width: 110px;
        height: 38px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 16px;
        line-height: 1;
    }

    .order-page .empty-box {
        padding: 36px 16px;
        text-align: center;
        color: #94a3b8;
        font-size: 14px;
    }

    .order-page .table-header {
        padding: 18px 20px 12px;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
    }

    .order-page .table-title {
        font-size: 16px;
        font-weight: 600;
        color: #0f172a;
        margin: 0;
    }

    .order-page .table-desc {
        font-size: 13px;
        color: #64748b;
        margin-top: 4px;
    }

    @media (max-width: 991.98px) {
        .order-page .table thead th,
        .order-page .table tbody td {
            white-space: nowrap;
        }

        .order-page .btn {
            width: 100%;
        }
    }
</style>

<div class="order-page">
    <div class="mb-4">
        <div class="page-title">Quản lý đơn hàng</div>
        <div class="page-subtitle">Danh sách đơn hàng trong hệ thống</div>
    </div>

    <div class="card filter-card mb-4">
        <div class="card-body p-3 p-md-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Từ khóa</label>
                    <input
                        type="text"
                        name="keyword"
                        value="{{ request('keyword') }}"
                        class="form-control"
                        placeholder="Tìm mã đơn hoặc tên khách hàng"
                    >
                </div>

                <div class="col-md-2">
                    <label class="form-label">Trạng thái đơn</label>
                    <select name="status" class="form-select">
                        <option value="">
                            Tất cả
                            ({{ $pendingCount + $processingCount + $completedCount + $cancelledCount + $returnedCount }})
                        </option>
                        <option value="1" {{ (string) request('status') === '1' ? 'selected' : '' }}>
                            Đang xử lý ({{ $pendingCount }})
                        </option>
                        <option value="2" {{ (string) request('status') === '2' ? 'selected' : '' }}>
                            Đang giao ({{ $processingCount }})
                        </option>
                        <option value="3" {{ (string) request('status') === '3' ? 'selected' : '' }}>
                            Đã giao ({{ $completedCount }})
                        </option>
                        <option value="4" {{ (string) request('status') === '4' ? 'selected' : '' }}>
                            Đã huỷ ({{ $cancelledCount }})
                        </option>
                        <option value="5" {{ (string) request('status') === '5' ? 'selected' : '' }}>
                            Trả hàng ({{ $returnedCount }})
                        </option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Thanh toán</label>
                    <select name="payment_status" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                        <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Chưa thanh toán</option>
                        <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>Đã hoàn tiền</option>
                        <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Thanh toán thất bại</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Sắp xếp</label>
                    <select name="sort" class="form-select">
                        <option value="">Mặc định</option>
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Mới → Cũ</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Cũ → Mới</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>
                            Lọc dữ liệu
                        </button>

                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                            Đặt lại
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card table-card">
        <div class="table-header">
            <h5 class="table-title mb-0">Danh sách đơn hàng</h5>
            <div class="table-desc">Hiển thị các đơn phù hợp với bộ lọc hiện tại</div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 95px;">Mã</th>
                            <th>Khách hàng</th>
                            <th style="width: 130px;">Phương thức</th>
                            <th style="width: 150px;">Tổng tiền</th>
                            <th style="width: 150px;">Thanh toán</th>
                            <th style="width: 190px;">Trạng thái</th>
                            <th style="width: 165px;">Ngày đặt</th>
                            <th style="width: 130px;" class="text-center">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>
                                    <div class="order-code">
                                        DH{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                    </div>
                                </td>

                                <td>
                                    <div class="customer-name">
                                        {{ $order->receiver_name ?? optional($order->user)->name }}
                                    </div>

                                    <div class="customer-phone">
                                        {{ $order->receiver_phone }}
                                    </div>

                                    @if($order->status == 4)
                                        <div class="cancel-info text-danger">
                                            Huỷ bởi:
                                            {{ $order->cancelled_by == 'admin' ? 'Admin' : 'Khách' }}
                                            @if($order->cancelledByUser)
                                                ({{ $order->cancelledByUser->name }})
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    @if($order->payment_method == 'cod')
                                        <span class="badge bg-secondary">COD</span>
                                    @elseif($order->payment_method == 'vnpay')
                                        <span class="badge bg-primary">VNPAY</span>
                                    @else
                                        <span class="badge bg-info text-dark">
                                            {{ strtoupper($order->payment_method) }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="price-text">
                                        {{ number_format($order->grand_total, 0, ',', '.') }}đ
                                    </div>
                                </td>

                                <td>
                                    <span class="badge bg-{{ $order->payment_status_badge }}">
                                        {{ $order->payment_status_name }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge bg-{{ $order->status_badge }}">
                                        {{ $order->status_name }}
                                    </span>

                                    @if($order->status == 3 && !$order->customer_confirmed)
                                        <div class="confirm-text text-info">
                                            Chờ khách xác nhận
                                        </div>
                                    @endif

                                    @if($order->status == 4 && $order->cancel_reason)
                                        <div class="reason-text text-muted">
                                            Lý do: {{ $order->cancel_reason }}
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <div class="date-text">
                                        {{ $order->created_at->format('d/m/Y H:i') }}
                                    </div>
                                </td>

                                <td class="action-cell">
                                    <a href="{{ route('admin.orders.show', $order->id) }}"
                                       class="btn btn-sm btn-outline-primary action-btn">
                                        Chi tiết
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-box">
                                        Chưa có đơn hàng phù hợp
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($orders->hasPages())
        <div class="mt-4">
            {{ $orders->withQueryString()->links('vendor.pagination.custom-blue') }}
        </div>
    @endif
</div>

@endsection