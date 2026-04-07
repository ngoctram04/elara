@extends('layouts.admin')

@section('title', 'Danh sách khách hàng')

@section('content')
<div class="customer-page">
    <div class="card customer-card border-0 shadow-sm">
        <div class="card-body p-3 p-md-4">

            <div class="customer-header mb-3">
                <div>
                    <h5 class="customer-title mb-1">Danh sách khách hàng</h5>
                    <p class="customer-subtitle mb-0">Quản lý thông tin khách hàng, trạng thái tài khoản và mức chi tiêu</p>
                </div>
            </div>

            {{-- ALERT --}}
            @if($errors->any())
                <div class="alert alert-danger customer-alert">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- FILTER --}}
            <form method="GET" class="filter-box mb-3">
                <div class="row g-2">
                    <div class="col-12 col-md-4 col-lg-4">
                        <input
                            type="text"
                            name="keyword"
                            class="form-control form-control-sm"
                            placeholder="Tìm theo tên, mã hoặc email"
                            value="{{ request('keyword') }}"
                        >
                    </div>

                    <div class="col-6 col-md-3 col-lg-2">
                        <select name="member_level" class="form-select form-select-sm">
                            <option value="">Hạng thành viên</option>
                            <option value="bronze" {{ request('member_level') == 'bronze' ? 'selected' : '' }}>Đồng</option>
                            <option value="silver" {{ request('member_level') == 'silver' ? 'selected' : '' }}>Bạc</option>
                            <option value="gold" {{ request('member_level') == 'gold' ? 'selected' : '' }}>Vàng</option>
                            <option value="diamond" {{ request('member_level') == 'diamond' ? 'selected' : '' }}>Kim cương</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3 col-lg-2">
                        <select name="sort" class="form-select form-select-sm">
                            <option value="">Sắp xếp theo</option>
                            <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Mới nhất</option>
                            <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Cũ nhất</option>
                            <option value="active" {{ request('sort') === 'active' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="blocked" {{ request('sort') === 'blocked' ? 'selected' : '' }}>Đã khóa</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-2 col-lg-4">
                        <div class="d-flex gap-2 justify-content-md-end flex-wrap">
                            <button type="submit" class="btn btn-sm btn-primary filter-btn">
                                <i class="bi bi-search me-1"></i> Lọc
                            </button>

                            <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-outline-secondary filter-btn">
                                Đặt lại
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            {{-- TABLE --}}
            <div class="table-responsive customer-table-wrap">
                <table class="table customer-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="72">Mã</th>
                            <th>Họ và tên</th>
                            <th>Email</th>
                            <th width="140">Hạng</th>
                            <th width="130">Tổng chi tiêu</th>
                            <th width="130">Chi tiêu năm nay</th>
                            <th width="70">Điểm hiện tại</th>
                            <th width="130">Trạng thái</th>
                            <th width="130">Cảnh báo</th>
                            <th width="75" class="text-center">Chi tiết</th>
                            <th width="75" class="text-center">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td>
                                    <span class="customer-code">
                                        KH{{ str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>

                                <td>{{ $customer->name }}</td>

                                <td class="text-muted">{{ $customer->email }}</td>

                                {{-- HẠNG THÀNH VIÊN --}}
                                <td>
                                    @switch($customer->member_level)
                                        @case('bronze')
                                            <span class="badge rounded-pill text-bg-secondary badge-level">Đồng</span>
                                            @break

                                        @case('silver')
                                            <span class="badge rounded-pill bg-light text-dark border badge-level">Bạc</span>
                                            @break

                                        @case('gold')
                                            <span class="badge rounded-pill bg-warning-subtle text-dark border badge-level">Vàng</span>
                                            @break

                                        @case('diamond')
                                            <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis border badge-level">Kim cương</span>
                                            @break

                                        @default
                                            <span class="badge rounded-pill bg-light text-muted border badge-level">Chưa có</span>
                                    @endswitch
                                </td>

                                {{-- TỔNG CHI TIÊU --}}
                                <td class="text-nowrap">
                                    {{ number_format($customer->spending ?? 0, 0, ',', '.') }} ₫
                                </td>

                                <td class="text-nowrap">
                                    {{ number_format($customer->yearly_spending ?? 0, 0, ',', '.') }} ₫
                                </td>

                                {{-- ĐIỂM --}}
                                <td>
                                    {{ number_format($customer->loyalty_points ?? 0, 0, ',', '.') }}
                                </td>

                                {{-- TRẠNG THÁI --}}
                                <td>
                                    @if($customer->is_active)
                                        <span class="badge rounded-pill bg-success-subtle text-success-emphasis border badge-status">
                                            Hoạt động
                                        </span>
                                    @else
                                        <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis border badge-status">
                                            Đã khóa
                                        </span>
                                    @endif
                                </td>

                                {{-- CẢNH BÁO --}}
                                <td>
                                    @if($customer->cancel_count >= 5)
                                        <span class="badge bg-danger-subtle text-danger-emphasis border badge-warning">
                                            Hủy {{ $customer->cancel_count }} đơn / 7 ngày
                                        </span>
                                    @elseif($customer->cancel_count >= 3)
                                        <span class="badge bg-warning-subtle text-dark border badge-warning">
                                            {{ $customer->cancel_count }} đơn hủy
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>

                                {{-- CHI TIẾT --}}
                                <td class="text-center">
                                    <a
                                        href="{{ route('admin.customers.show', $customer) }}"
                                        class="btn btn-sm btn-outline-primary action-btn"
                                    >
                                        Xem
                                    </a>
                                </td>

                                {{-- THAO TÁC --}}
                                <td class="text-center">
                                    @if($customer->is_active)
                                        <button
                                            class="btn btn-sm btn-outline-warning action-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#blockModal{{ $customer->id }}"
                                        >
                                            Khóa
                                        </button>

                                        {{-- MODAL --}}
                                        <div class="modal fade" id="blockModal{{ $customer->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <form
                                                    method="POST"
                                                    action="{{ route('admin.customers.toggle-status', $customer) }}"
                                                    class="modal-content border-0 shadow-sm"
                                                >
                                                    @csrf

                                                    <div class="modal-header border-0 pb-2">
                                                        <h6 class="modal-title mb-0">Khóa tài khoản</h6>
                                                        <button
                                                            type="button"
                                                            class="btn-close"
                                                            data-bs-dismiss="modal"
                                                            aria-label="Close"
                                                        ></button>
                                                    </div>

                                                    <div class="modal-body pt-0">
                                                        <p class="mb-3 text-muted">
                                                            Bạn đang khóa tài khoản:
                                                            <span class="text-dark">{{ $customer->name }}</span>
                                                        </p>

                                                        <div>
                                                            <label class="form-label mb-2">
                                                                Lý do khóa <span class="text-danger">*</span>
                                                            </label>
                                                            <textarea
                                                                name="blocked_reason"
                                                                class="form-control"
                                                                rows="3"
                                                                required
                                                                placeholder="Nhập lý do khóa tài khoản..."
                                                            ></textarea>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                                                            Hủy
                                                        </button>
                                                        <button type="submit" class="btn btn-sm btn-warning">
                                                            Xác nhận khóa
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @else
                                        <form
                                            method="POST"
                                            id="unblock-form-{{ $customer->id }}"
                                            action="{{ route('admin.customers.toggle-status', $customer) }}"
                                            class="d-inline"
                                        >
                                            @csrf
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-success action-btn btn-unblock"
                                                data-id="{{ $customer->id }}"
                                            >
                                                Mở
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    Không có khách hàng
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="mt-3 customer-pagination">
                {{ $customers->links() }}
            </div>

        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .customer-page {
        font-size: 14px;
        color: #334155;
    }

    .customer-card {
        border-radius: 14px;
        background: #fff;
    }

    .customer-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .customer-title {
        font-size: 18px;
        font-weight: 600;
        color: #1e293b;
    }

    .customer-subtitle {
        font-size: 13px;
        color: #64748b;
    }

    .customer-alert {
        border-radius: 10px;
        font-size: 13px;
    }

    .filter-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
    }

    .filter-box .form-control,
    .filter-box .form-select {
        border-radius: 10px;
        min-height: 36px;
        font-size: 13px;
        border-color: #dbe2ea;
        box-shadow: none;
    }

    .filter-box .form-control:focus,
    .filter-box .form-select:focus {
        border-color: #94a3b8;
        box-shadow: none;
    }

    .filter-btn {
        min-width: 90px;
        border-radius: 10px;
        font-size: 13px;
        padding: 7px 14px;
    }

    .customer-table-wrap {
        border: 1px solid #e9eef5;
        border-radius: 12px;
        overflow: hidden;
    }

    .customer-table {
        margin-bottom: 0;
        font-size: 13.5px;
    }

    .customer-table thead th {
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
        white-space: nowrap;
        border-bottom: 1px solid #e2e8f0;
        padding: 13px 12px;
        vertical-align: middle;
    }

    .customer-table tbody td {
        padding: 13px 12px;
        border-color: #eef2f7;
        vertical-align: middle;
    }

    .customer-table tbody tr:hover {
        background: #fcfdff;
    }

    .customer-code {
        color: #334155;
        font-size: 13px;
    }

    .badge-level,
    .badge-status,
    .badge-warning {
        font-size: 12px;
        font-weight: 500;
        padding: 6px 10px;
        line-height: 1.2;
        white-space: normal;
    }

    .action-btn {
        min-width: 58px;
        border-radius: 9px;
        font-size: 12.5px;
        padding: 5px 10px;
    }

    .modal-content {
        border-radius: 14px;
    }

    .modal-title {
        font-size: 16px;
        font-weight: 600;
        color: #1e293b;
    }

    .modal-body,
    .modal-footer,
    .modal-header,
    .modal-body .form-label,
    .modal-body .form-control {
        font-size: 14px;
    }

    .modal-body .form-control {
        border-radius: 10px;
        box-shadow: none;
    }

    .modal-body .form-control:focus {
        box-shadow: none;
        border-color: #94a3b8;
    }

    .customer-pagination nav {
        margin-bottom: 0;
    }

    .customer-pagination svg {
        width: 14px;
        height: 14px;
    }

    @media (max-width: 768px) {
        .customer-title {
            font-size: 16px;
        }

        .customer-subtitle {
            font-size: 12px;
        }

        .customer-table {
            font-size: 13px;
        }

        .customer-table thead th,
        .customer-table tbody td {
            padding: 11px 10px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.querySelectorAll('.btn-unblock').forEach(function (btn) {
        btn.addEventListener('click', function () {
            let id = this.dataset.id;

            Swal.fire({
                title: 'Mở lại tài khoản?',
                text: 'Bạn có chắc muốn mở lại tài khoản này?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                cancelButtonText: 'Hủy',
                confirmButtonText: 'Mở tài khoản'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('unblock-form-' + id).submit();
                }
            });
        });
    });
</script>
@endpush