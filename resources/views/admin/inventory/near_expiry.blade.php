@extends('layouts.admin')

@section('title','Quản lý lô hàng')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">Quản lý lô hàng</h5>
                <small class="text-muted">Theo dõi tất cả các lô hàng</small>
            </div>

            <span class="badge bg-warning text-dark">
                {{ $lots->total() }} lô
            </span>
        </div>

        <form method="GET" class="row g-2 mb-4">

            <div class="col-md-4">
                <input
                    type="text"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    class="form-control form-control-sm"
                    placeholder="Tên SP / Mã SP / Mã biến thể / Mã lô..."
                >
            </div>

            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Tất cả trạng thái</option>
                    <option value="normal" {{ request('status') == 'normal' ? 'selected' : '' }}>Bình thường</option>
                    <option value="sale" {{ request('status') == 'sale' ? 'selected' : '' }}>Nên sale</option>
                    <option value="danger" {{ request('status') == 'danger' ? 'selected' : '' }}>Sắp huỷ</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Đã huỷ</option>
                </select>
            </div>

            <div class="col-md-2">
                <select name="sort" class="form-select form-select-sm">
                    <option value="">HSD gần nhất</option>
                    <option value="far" {{ request('sort') == 'far' ? 'selected' : '' }}>
                        HSD xa nhất
                    </option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-search me-1"></i> Lọc
                </button>

                <a href="{{ route('admin.inventory.near_expiry') }}"
                   class="btn btn-outline-secondary btn-sm">
                    Đặt lại
                </a>
            </div>
        </form>

        <div class="alert alert-warning mb-4 py-2">
            <i class="bi bi-info-circle me-1"></i>
            <b>Quy tắc:</b>

            <span class="ms-2">
                <i class="bi bi-check-circle-fill text-success me-1"></i>
                &gt; 7 tháng
            </span>

            <span class="ms-3">
                <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                ≤ 7 tháng
            </span>

            <span class="ms-3">
                <i class="bi bi-x-circle-fill text-danger me-1"></i>
                ≤ 6 tháng
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">
                    <tr>
                        <th>Ảnh</th>
                        <th>Mã lô</th>
                        <th>Sản phẩm</th>
                        <th>Biến thể</th>
                        <th class="text-center">Số lượng</th>
                        <th class="text-end">Giá trị</th>
                        <th class="text-center">HSD</th>
                        <th class="text-center">Trạng thái</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($lots as $lot)
                        @php
                            $expiry = \Carbon\Carbon::parse($lot->expiry_date);
                            $now = now();
                            $days = $now->diffInDays($expiry, false);
                            $months = $days / 30;

                            $totalCost = $lot->total_cost ?? ($lot->imported_quantity * $lot->cost_price);
                            $remainingValue = $lot->remaining_value ?? ($lot->remaining_quantity * $lot->cost_price);
                            $damagedQty = $lot->damaged_quantity ?? 0;
                            $lossValue = $lot->total_loss_value ?? 0;
                        @endphp

                        <tr class="
                            @if($lot->expired_at)
                                table-secondary
                            @elseif($months >= 0 && $months <= 6)
                                table-danger
                            @elseif($months > 6 && $months <= 7)
                                table-warning
                            @endif
                        ">
                            <td>
                                @if($lot->image_path)
                                    <img
                                        src="{{ asset('storage/' . $lot->image_path) }}"
                                        width="50"
                                        height="50"
                                        class="rounded border"
                                        style="object-fit:cover"
                                        alt="{{ $lot->product_name }}"
                                    >
                                @else
                                    <div
                                        class="bg-light border rounded d-inline-flex align-items-center justify-content-center"
                                        style="width:50px;height:50px;"
                                    >
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                @endif
                            </td>

                            <td>
                                <span class="badge bg-dark">
                                    {{ $lot->lot_code }}
                                </span>
                            </td>

                            <td class="fw-medium">
                                {{ $lot->product_name }}<br>
                                <small class="text-muted">
                                    SP{{ str_pad($lot->product_id, 5, '0', STR_PAD_LEFT) }}
                                </small>
                            </td>

                            <td>
                                <div class="fw-semibold text-muted">
                                    BT{{ str_pad($lot->variant_id, 5, '0', STR_PAD_LEFT) }}
                                </div>

                                <div>
                                    {{ $lot->attribute_value }}
                                </div>

                                @if($lot->attribute_name)
                                    <small class="text-muted">
                                        {{ $lot->attribute_name }}
                                    </small>
                                @endif
                            </td>

                            <td class="text-center small">
                                <div>Nhập: <b>{{ $lot->imported_quantity }}</b></div>
                                <div class="text-success">Bán: {{ $lot->sold_quantity ?? 0 }}</div>
                                <div class="text-muted">Huỷ: {{ $damagedQty }}</div>
                            </td>

                            <td class="text-end small">
                                <div>Nhập: <b>{{ number_format($totalCost) }}đ</b></div>
                                <div class="text-success">Còn: {{ number_format($remainingValue) }}đ</div>
                                <div class="text-danger">Hao hụt: {{ number_format($lossValue) }}đ</div>
                            </td>

                            <td class="text-center small">
                                <div>{{ $expiry->format('d/m/Y') }}</div>
                                <div class="text-muted">
                                    {{ $months < 0 ? 'Hết hạn' : number_format($months, 1) . ' tháng' }}
                                </div>
                            </td>

                            <td class="text-center">
                                @if($lot->expired_at)
                                    <span class="badge bg-secondary">Đã huỷ</span>
                                @elseif($months <= 6)
                                    <span class="badge bg-danger">Sắp huỷ</span>
                                @elseif($months <= 7)
                                    <span class="badge bg-warning text-dark">Nên sale</span>
                                @else
                                    <span class="badge bg-success">Bình thường</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Không có lô hàng
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($lots->hasPages())
            <div class="mt-4 d-flex justify-content-center">
                {{ $lots->appends(request()->query())->links('vendor.pagination.custom-blue') }}
            </div>
        @endif

    </div>
</div>

@endsection