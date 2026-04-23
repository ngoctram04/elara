@extends('layouts.admin')

@section('title', 'Danh sách đơn hoàn trả / hoàn tiền')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h4 class="mb-1">Danh sách đơn hoàn trả / hoàn tiền</h4>
                    <p class="text-muted mb-0">
                        Theo dõi các đơn đã hoàn tiền trong khoảng thời gian đã chọn
                    </p>
                </div>

                <a href="{{ route('admin.reports.index', ['from' => $from, 'to' => $to]) }}"
                   class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Quay lại báo cáo
                </a>
            </div>

            <form method="GET"
                  action="{{ route('admin.reports.refundOrders') }}"
                  class="row g-3 mb-4">

                <div class="col-md-3">
                    <label class="form-label">Từ ngày</label>
                    <input type="date" name="from" value="{{ $from }}" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Đến ngày</label>
                    <input type="date" name="to" value="{{ $to }}" class="form-control">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Tìm kiếm</label>
                    <input type="text"
                           name="keyword"
                           value="{{ $keyword }}"
                           class="form-control"
                           placeholder="Tên khách hàng hoặc mã đơn hàng">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i>
                        Lọc dữ liệu
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã hoàn</th>
                            <th>Mã đơn hàng</th>
                            <th>Khách hàng</th>
                            <th class="text-center">Tiền hoàn</th>
                            <th class="text-center">Tổn thất</th>
                            <th>Lý do</th>
                            <th class="text-end">Ngày hoàn</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($refunds as $refund)
                            @php
                                $cleanReason = preg_replace(
                                    '/(Sản phẩm|Product)\s*ID\s*\d+\s*[:\-]?\s*/i',
                                    '',
                                    $refund->reason ?? ''
                                );
                            @endphp

                            <tr>
                                <td>
                                    HT{{ str_pad($refund->id ?? 0, 5, '0', STR_PAD_LEFT) }}
                                </td>

                                <td>
                                    DH{{ str_pad($refund->order_id ?? 0, 5, '0', STR_PAD_LEFT) }}
                                </td>

                                <td>{{ $refund->customer_name ?? '---' }}</td>

                                <td class="text-center">
                                    <span class="badge bg-warning text-dark">
                                        {{ number_format($refund->refund_total ?? 0) }} đ
                                    </span>
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-danger">
                                        {{ number_format($refund->loss_amount ?? 0) }} đ
                                    </span>
                                </td>

                                <td style="min-width: 220px;">
                                    {{ $cleanReason ?: '---' }}
                                </td>

                                <td class="text-end text-muted">
                                    {{ !empty($refund->refunded_at)
                                        ? \Carbon\Carbon::parse($refund->refunded_at)->format('d/m/Y H:i')
                                        : '---' }}
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-box-seam"></i>
                                    <div class="mt-1">Không có dữ liệu hoàn trả / hoàn tiền</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($refunds, 'links'))
                <div class="mt-4">
                    {{ $refunds->links() }}
                </div>
            @endif

        </div>
    </div>
</div>
@endsection