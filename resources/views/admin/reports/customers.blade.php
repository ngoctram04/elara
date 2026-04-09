@extends('layouts.admin')

@section('title', 'Top khách hàng')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">
                    <i class="bi bi-people text-primary me-1"></i>
                    Top khách hàng
                </h5>

                <small class="text-muted">
                    Danh sách khách hàng có tổng chi tiêu cao nhất
                </small>
            </div>

            <a href="{{ route('admin.reports.index', ['from' => $from, 'to' => $to]) }}"
               class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i>
                Quay lại Dashboard
            </a>
        </div>

        {{-- FILTER --}}
        <form method="GET" class="row g-2 mb-4 align-items-end">
            <div class="col-md-3">
                <label class="small text-muted">Từ ngày</label>
                <input type="date"
                       name="from"
                       value="{{ $from }}"
                       class="form-control form-control-sm">
            </div>

            <div class="col-md-3">
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
                       value="{{ $keyword ?? '' }}"
                       placeholder="Nhập tên khách hàng..."
                       class="form-control form-control-sm">
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-search"></i>
                    Lọc
                </button>
            </div>
        </form>

        {{-- TABLE --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:80px" class="text-center">STT</th>
                        <th>Khách hàng</th>
                        <th style="width:120px" class="text-center">Số đơn</th>
                        <th style="width:180px" class="text-end">Tổng chi tiêu</th>
                        <th style="width:140px" class="text-center">Chi tiết</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($customers as $c)
                        <tr>
                            <td class="text-center text-muted fw-semibold">
                                {{ ($customers->currentPage() - 1) * $customers->perPage() + $loop->iteration }}
                            </td>

                            <td class="fw-medium">
                                <i class="bi bi-person-circle text-muted me-1"></i>
                                {{ $c->name ?? 'Không xác định' }}
                            </td>

                            <td class="text-center">
                                <span class="badge bg-primary">
                                    {{ number_format($c->orders ?? 0) }}
                                </span>
                            </td>

                            <td class="text-end fw-semibold text-success">
                                {{ number_format($c->spending ?? 0) }} đ
                            </td>

                            <td class="text-center">
                                @if(isset($c->id))
                                    <button type="button"
                                            class="btn btn-sm btn-outline-info view-customer-orders"
                                            data-customer-id="{{ $c->id }}"
                                            data-customer-name="{{ $c->name ?? 'Khách hàng' }}">
                                        <i class="bi bi-eye"></i>
                                        Xem đơn
                                    </button>
                                @else
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary"
                                            disabled>
                                        <i class="bi bi-eye"></i>
                                        Không khả dụng
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-people fs-4"></i>
                                <div class="mt-1">Không có dữ liệu</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($customers->hasPages())
            <div class="mt-4">
                {{ $customers->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif

    </div>
</div>

{{-- MODAL --}}
<div class="modal fade" id="customerOrdersModal" tabindex="-1" aria-labelledby="customerOrdersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header">
                <h5 class="modal-title" id="customerOrdersModalLabel">Danh sách đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div id="customerOrdersLoading" class="text-center py-4 d-none">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted">Đang tải dữ liệu...</div>
                </div>

                <div id="customerOrdersContent">
                    <div class="text-center text-muted py-4">
                        Chọn khách hàng để xem danh sách đơn
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('customerOrdersModal');
    const loadingEl = document.getElementById('customerOrdersLoading');
    const contentEl = document.getElementById('customerOrdersContent');
    const titleEl = document.getElementById('customerOrdersModalLabel');

    if (!modalElement || typeof bootstrap === 'undefined') return;

    const modal = new bootstrap.Modal(modalElement);
    const from = @json($from);
    const to = @json($to);

    document.querySelectorAll('.view-customer-orders').forEach(function (button) {
        button.addEventListener('click', function () {
            const customerId = this.dataset.customerId;
            const customerName = this.dataset.customerName || 'Khách hàng';

            if (!customerId) {
                contentEl.innerHTML = `
                    <div class="text-center text-danger py-4">
                        Không tìm thấy mã khách hàng
                    </div>
                `;
                modal.show();
                return;
            }

            titleEl.textContent = 'Đơn hàng của ' + customerName;
            contentEl.innerHTML = '';
            loadingEl.classList.remove('d-none');
            modal.show();

            const url = `/admin/reports/customers/${encodeURIComponent(customerId)}/orders?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`;

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                loadingEl.classList.add('d-none');

                if (!data.orders || data.orders.length === 0) {
                    contentEl.innerHTML = `
                        <div class="text-center text-muted py-4">
                            Khách hàng này không có đơn trong khoảng thời gian đã chọn
                        </div>
                    `;
                    return;
                }

                let html = `
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã đơn</th>
                                    <th>Ngày đặt</th>
                                    <th class="text-end">Tổng tiền</th>
                                    <th class="text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                data.orders.forEach(order => {
                    html += `
                        <tr>
                            <td>DH${String(order.id).padStart(5, '0')}</td>
                            <td>${order.created_at ?? ''}</td>
                            <td class="text-end fw-semibold">${order.total ?? ''}</td>
                            <td class="text-center">${order.status_label ?? ''}</td>
                        </tr>
                    `;
                });

                html += `
                            </tbody>
                        </table>
                    </div>
                `;

                contentEl.innerHTML = html;
            })
            .catch(() => {
                loadingEl.classList.add('d-none');
                contentEl.innerHTML = `
                    <div class="text-center text-danger py-4">
                        Không thể tải dữ liệu đơn hàng
                    </div>
                `;
            });
        });
    });
});
</script>
@endpush