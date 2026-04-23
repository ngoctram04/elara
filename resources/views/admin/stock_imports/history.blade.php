@extends('layouts.admin')

@section('title','Lịch sử nhập kho')

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold mb-1">Lịch sử nhập kho</h4>
                    <small class="text-muted">
                        Danh sách các phiếu nhập kho trong hệ thống
                    </small>
                </div>

                <a href="{{ route('admin.stock.create') }}"
                   class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>
                    Nhập hàng
                </a>
            </div>

            <div class="border rounded-4 p-3 bg-light-subtle mb-4">
                <form method="GET" class="row g-3 align-items-end">

                    <div class="col-lg-4 col-md-6">
                        <label class="form-label small fw-semibold text-muted">Từ khóa</label>
                        <input
                            type="text"
                            name="keyword"
                            value="{{ request('keyword') }}"
                            class="form-control"
                            placeholder="Tìm mã phiếu hoặc nhà cung cấp..."
                        >
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label class="form-label small fw-semibold text-muted">Từ ngày</label>
                        <input
                            type="date"
                            name="from"
                            value="{{ request('from') }}"
                            class="form-control"
                        >
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label class="form-label small fw-semibold text-muted">Đến ngày</label>
                        <input
                            type="date"
                            name="to"
                            value="{{ request('to') }}"
                            class="form-control"
                        >
                    </div>

                    <div class="col-lg-4 col-md-12 d-flex gap-2">
                        <button class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>
                            Lọc
                        </button>

                        <a href="{{ route('admin.stock.history') }}"
                           class="btn btn-outline-secondary">
                            Đặt lại
                        </a>
                    </div>

                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="align-middle text-center">
                            <th style="width:80px">STT</th>
                            <th style="width:180px" class="text-start">Mã phiếu</th>
                            <th class="text-start">Nhà cung cấp</th>
                            <th style="width:120px">Số SP</th>
                            <th style="width:140px">Tổng SL</th>
                            <th style="width:180px">Ngày nhập</th>
                            <th style="width:140px">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($imports as $index => $item)
                            <tr>
                                <td class="text-center text-muted fw-semibold">
                                    {{ $imports->firstItem() + $index }}
                                </td>

                                <td class="fw-semibold text-dark">
                                    {{ $item->code }}
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $item->supplier ?? '—' }}
                                    </div>
                                </td>

                                <td class="text-center fw-semibold text-dark">
                                    {{ $item->total_items }}
                                </td>

                                <td class="text-center fw-semibold text-dark">
                                    {{ $item->total_qty }}
                                </td>

                                <td class="text-center text-muted small">
                                    {{ optional($item->created_at)->format('d/m/Y H:i') }}
                                </td>

                                <td class="text-center">
                                    <a href="{{ url('admin/stock-import/' . $item->code) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        Chi tiết
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    Chưa có phiếu nhập kho
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted">
                    @if($imports->total() > 0)
                        Hiển thị {{ $imports->firstItem() }} – {{ $imports->lastItem() }}
                        / {{ $imports->total() }} phiếu nhập
                    @else
                        Không có dữ liệu
                    @endif
                </small>

                @if($imports->hasPages())
                    {{ $imports->links('vendor.pagination.custom-blue') }}
                @endif
            </div>

        </div>
    </div>
</div>
@endsection