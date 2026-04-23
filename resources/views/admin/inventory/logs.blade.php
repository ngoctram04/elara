@extends('layouts.admin')

@section('title','Lịch sử thay đổi tồn kho')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">Lịch sử thay đổi tồn kho</h5>
                <small class="text-muted">
                    Theo dõi các thay đổi số lượng tồn kho trong hệ thống
                </small>
            </div>

            <span class="badge bg-secondary">
                Tổng: {{ $logs->total() }}
            </span>
        </div>

        <form method="GET" class="row g-2 mb-4 align-items-center">

            <div class="col-md-4">
                <input
                    type="text"
                    name="keyword"
                    class="form-control form-control-sm"
                    placeholder="Tìm theo tên sản phẩm hoặc mã..."
                    value="{{ request('keyword') }}"
                >
            </div>

            <div class="col-md-2">
                <select name="type" class="form-select form-select-sm">
                    <option value="">Tất cả loại</option>

                    <option value="import" {{ request('type') == 'import' ? 'selected' : '' }}>
                        Nhập kho
                    </option>

                    <option value="order" {{ request('type') == 'order' ? 'selected' : '' }}>
                        Bán hàng
                    </option>

                    <option value="cancel" {{ request('type') == 'cancel' ? 'selected' : '' }}>
                        Huỷ đơn / hoàn kho
                    </option>

                    <option value="return_restock" {{ request('type') == 'return_restock' ? 'selected' : '' }}>
                        Trả hàng nhập lại kho
                    </option>

                    <option value="return_damaged" {{ request('type') == 'return_damaged' ? 'selected' : '' }}>
                        Trả hàng hư
                    </option>

                    <option value="adjust" {{ request('type') == 'adjust' ? 'selected' : '' }}>
                        Điều chỉnh
                    </option>

                    <option value="expired_destroy" {{ request('type') == 'expired_destroy' ? 'selected' : '' }}>
                        Huỷ cận date
                    </option>
                </select>
            </div>

            <div class="col-md-2">
                <input
                    type="date"
                    name="from"
                    class="form-control form-control-sm"
                    value="{{ request('from') }}"
                >
            </div>

            <div class="col-md-2">
                <input
                    type="date"
                    name="to"
                    class="form-control form-control-sm"
                    value="{{ request('to') }}"
                >
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-search"></i>
                    Lọc
                </button>

                <a href="{{ route('admin.inventory.logs') }}"
                   class="btn btn-outline-secondary btn-sm">
                    Đặt lại
                </a>
            </div>

        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Biến thể</th>
                        <th class="text-center" style="width:170px">Loại</th>
                        <th class="text-center" style="width:120px">Thay đổi</th>
                        <th class="text-center" style="width:120px">Tồn trước</th>
                        <th class="text-center" style="width:120px">Tồn sau</th>
                        <th class="text-center" style="width:180px">Thời gian</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($logs as $log)
                        @php
                            $variant = $log->variant;
                            $product = $variant?->product;
                            $img = $variant?->images?->first()?->image_path;

                            $typeLabel = match ($log->type) {
                                'import' => 'Nhập kho',
                                'order' => 'Bán hàng',
                                'cancel' => 'Huỷ đơn / hoàn kho',
                                'return_restock' => 'Trả hàng nhập lại kho',
                                'return_damaged' => 'Trả hàng hư',
                                'adjust' => 'Điều chỉnh',
                                'expired_destroy' => 'Huỷ cận date',
                                default => $log->type,
                            };

                            $typeClass = match ($log->type) {
                                'import' => 'bg-success',
                                'order' => 'bg-primary',
                                'cancel' => 'bg-warning text-dark',
                                'return_restock' => 'bg-info text-dark',
                                'return_damaged' => 'bg-danger',
                                'adjust' => 'bg-secondary',
                                'expired_destroy' => 'bg-dark',
                                default => 'bg-secondary',
                            };

                            $typeIcon = match ($log->type) {
                                'import' => 'bi bi-box-arrow-in-down',
                                'order' => 'bi bi-cart-check',
                                'cancel' => 'bi bi-arrow-counterclockwise',
                                'return_restock' => 'bi bi-arrow-repeat',
                                'return_damaged' => 'bi bi-x-circle',
                                'adjust' => 'bi bi-tools',
                                'expired_destroy' => 'bi bi-trash',
                                default => '',
                            };
                        @endphp

                        <tr>

                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($img)
                                        <img
                                            src="{{ asset('storage/' . $img) }}"
                                            width="45"
                                            height="45"
                                            class="rounded border"
                                            style="object-fit:cover"
                                            alt="{{ $product?->name ?? 'Sản phẩm' }}"
                                        >
                                    @else
                                        <div
                                            class="bg-light border rounded d-flex align-items-center justify-content-center"
                                            style="width:45px;height:45px;"
                                        >
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    @endif

                                    <div>
                                        <div class="fw-medium">
                                            {{ $product?->name ?? '-' }}
                                        </div>

                                        <small class="text-muted">
                                            @if($product)
                                                SP{{ str_pad($product->id, 5, '0', STR_PAD_LEFT) }}
                                            @else
                                                -
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </td>

                            <td>
                                @if($variant)
                                    <div class="fw-semibold">
                                        BT{{ str_pad($variant->id, 5, '0', STR_PAD_LEFT) }}
                                    </div>

                                    <small class="text-muted">
                                        {{ $variant->attribute_name }}: {{ $variant->attribute_value }}
                                    </small>
                                @else
                                    -
                                @endif
                            </td>

                            <td class="text-center">
                                <span class="badge {{ $typeClass }}">
                                    @if($typeIcon)
                                        <i class="{{ $typeIcon }} me-1"></i>
                                    @endif
                                    {{ $typeLabel }}
                                </span>
                            </td>

                            <td class="text-center fw-bold">
                                @if($log->quantity_change > 0)
                                    <span class="text-success">+{{ $log->quantity_change }}</span>
                                @elseif($log->quantity_change < 0)
                                    <span class="text-danger">{{ $log->quantity_change }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>

                            <td class="text-center text-muted">
                                {{ $log->stock_before }}
                            </td>

                            <td class="text-center fw-semibold">
                                {{ $log->stock_after }}
                            </td>

                            <td class="text-center text-muted small">
                                {{ optional($log->created_at)->format('d/m/Y H:i') }}
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox me-1"></i>
                                Không có lịch sử tồn kho
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        @if($logs->hasPages())
            <div class="mt-4 d-flex justify-content-center">
                {{ $logs->links('vendor.pagination.custom-blue') }}
            </div>
        @endif

    </div>
</div>
@endsection