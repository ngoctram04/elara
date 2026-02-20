@extends('layouts.admin')

@section('content')
<div class="container-fluid">

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            {{-- HEADER --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-semibold mb-0">
                        <i class="bi bi-clock-history text-primary me-2"></i>
                        Lịch sử nhập kho
                    </h4>
                    <small class="text-muted">
                        Quản lý các lần nhập hàng theo lô
                    </small>
                </div>

                <a href="{{ route('admin.stock.create') }}"
                   class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Nhập hàng
                </a>
            </div>

            {{-- TABLE --}}
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th width="70">ID</th>
                            <th>Sản phẩm</th>
                            <th width="140">Biến thể</th>
                            <th width="100">SL</th>
                            <th width="140">Giá nhập</th>
                            <th width="160">Thành tiền</th>
                            <th width="160">Hạn dùng</th>
                            <th width="160">Thời gian</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($imports as $import)

                            @php
                                // Nếu Model đã cast date thì không cần parse
                                $expiryDate = $import->expiry_date;
                                $status = null;

                                if ($expiryDate) {
                                    if ($expiryDate->isPast()) {
                                        $status = 'expired';
                                    } elseif (now()->diffInDays($expiryDate) <= 7) {
                                        $status = 'warning';
                                    } else {
                                        $status = 'ok';
                                    }
                                }
                            @endphp

                            <tr>
                                {{-- ID --}}
                                <td class="text-center text-muted fw-semibold">
                                    #{{ $import->id }}
                                </td>

                                {{-- PRODUCT --}}
                                <td>
                                    {{ $import->variant->product->name ?? '-' }}
                                </td>

                                {{-- VARIANT --}}
                                <td class="text-center">
                                    {{ $import->variant->attribute_value ?? '-' }}
                                </td>

                                {{-- QTY --}}
                                <td class="text-center fw-semibold">
                                    {{ $import->quantity }}
                                </td>

                                {{-- COST --}}
                                <td class="text-end">
                                    {{ number_format($import->cost_price) }} đ
                                </td>

                                {{-- TOTAL --}}
                                <td class="text-end fw-semibold">
                                    {{ number_format($import->quantity * $import->cost_price) }} đ
                                </td>

                                {{-- EXPIRY --}}
                                <td class="text-center">
                                    @if($expiryDate)
                                        {{ $expiryDate->format('d/m/Y') }}

                                        <div class="mt-1">
                                            @if($status == 'expired')
                                                <span class="badge bg-danger">Hết hạn</span>
                                            @elseif($status == 'warning')
                                                <span class="badge bg-warning text-dark">Sắp hết hạn</span>
                                            @else
                                                <span class="badge bg-success">Còn hạn</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">Không áp dụng</span>
                                    @endif
                                </td>

                                {{-- CREATED --}}
                                <td class="text-center text-muted">
                                    {{ $import->created_at ? $import->created_at->format('d/m/Y H:i') : '-' }}
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    Chưa có lịch sử nhập
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    @if($imports->total() > 0)
                        Hiển thị {{ $imports->firstItem() }} – {{ $imports->lastItem() }}
                        / {{ $imports->total() }} bản ghi
                    @endif
                </small>

                {{ $imports->links() }}
            </div>

        </div>
    </div>

</div>
@endsection