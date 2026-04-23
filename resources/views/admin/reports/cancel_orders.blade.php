@extends('layouts.admin')

@section('title','Danh sách bom hàng')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i>
                    Danh sách bom hàng
                </h5>

                <small class="text-muted">
                    Danh sách các đơn hàng đã bị huỷ trong khoảng thời gian đã chọn
                </small>
            </div>

            <a href="{{ route('admin.reports.index') }}"
               class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i>
                Quay lại Dashboard
            </a>
        </div>

        <form method="GET" class="row g-2 mb-4 align-items-end">
            <div class="col-md-2">
                <label class="small text-muted">Từ ngày</label>
                <input type="date"
                       name="from"
                       value="{{ $from }}"
                       class="form-control form-control-sm">
            </div>

            <div class="col-md-2">
                <label class="small text-muted">Đến ngày</label>
                <input type="date"
                       name="to"
                       value="{{ $to }}"
                       class="form-control form-control-sm">
            </div>

            <div class="col-md-4">
                <label class="small text-muted">Tìm khách hàng</label>
                <input type="text"
                       name="keyword"
                       value="{{ $keyword }}"
                       placeholder="Nhập tên khách..."
                       class="form-control form-control-sm">
            </div>

            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-search"></i>
                    Lọc
                </button>

                <a href="{{ route('admin.reports.cancelOrders') }}"
                   class="btn btn-outline-secondary btn-sm">
                    Đặt lại
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 80px;" class="text-center">STT</th>
                        <th style="width: 180px;">Mã đơn hàng</th>
                        <th>Khách hàng</th>
                        <th style="width: 160px;" class="text-center">Tiền</th>
                        <th style="width: 140px;" class="text-center">Ngày</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($orders as $o)
                        <tr>
                            <td class="text-center text-muted fw-semibold">
                                {{ ($orders->currentPage() - 1) * $orders->perPage() + $loop->iteration }}
                            </td>

                            <td class="fw-medium">
                                DH{{ str_pad($o->id, 5, '0', STR_PAD_LEFT) }}
                            </td>

                            <td>
                                {{ $o->customer_name }}
                            </td>

                            <td class="text-center">
                                <span class="badge bg-danger">
                                    {{ number_format($o->total) }} đ
                                </span>
                            </td>

                            <td class="text-center text-muted">
                                {{ \Carbon\Carbon::parse($o->cancelled_at)->format('d/m/Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-5"></i>
                                <div class="mt-1">
                                    Không có dữ liệu
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="mt-4">
                {{ $orders->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif

    </div>
</div>

@endsection