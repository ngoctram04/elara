@extends('layouts.admin')

@section('title', 'Phiếu nhập kho')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-body">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">Phiếu nhập kho</h5>
                <small class="text-muted">Chi tiết phiếu nhập kho</small>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('admin.stock.history') }}"
                   class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>
                    Quay lại
                </a>

                <a href="{{ route('admin.stock.exportPdf', $code) }}"
                   class="btn btn-danger btn-sm">
                    <i class="bi bi-file-earmark-pdf me-1"></i>
                    Xuất PDF
                </a>
            </div>
        </div>

        @php
            $firstItem = $items->first();
            $total = 0;
        @endphp

        {{-- INFO --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <p class="mb-1">
                    <strong>Mã phiếu:</strong>
                    <span class="badge bg-secondary">
                        {{ $code }}
                    </span>
                </p>

                <p class="mb-1">
                    <strong>Nhà cung cấp:</strong>
                    {{ $firstItem->supplier ?? '---' }}
                </p>

                <p class="mb-1">
                    <strong>Số điện thoại:</strong>
                    {{ $firstItem->supplier_phone ?? '---' }}
                </p>

                <p class="mb-1">
                    <strong>Địa chỉ:</strong>
                    {{ $firstItem->supplier_address ?? '---' }}
                </p>

                <p class="mb-1">
                    <strong>Ngày nhập:</strong>
                    {{ optional($firstItem->created_at)->format('d/m/Y H:i') ?? '---' }}
                </p>

                <p class="mb-1">
                    <strong>Người nhập:</strong>
                    {{ $firstItem->user->name ?? 'Admin' }}
                </p>

                <p class="mb-0">
                    <strong>Ghi chú:</strong>
                    {{ $firstItem->note ?? '---' }}
                </p>
            </div>

            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <div class="text-muted small">
                    Tổng số dòng: <strong>{{ $items->count() }}</strong>
                </div>
                <div class="text-muted small">
                    Tổng số lượng:
                    <strong>{{ number_format($items->sum('quantity')) }}</strong>
                </div>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px" class="text-center">STT</th>
                        <th>Sản phẩm</th>
                        <th style="width:180px">Biến thể</th>
                        <th style="width:80px" class="text-center">Số lượng</th>
                        <th style="width:110px" class="text-end">Giá nhập</th>
                        <th style="width:130px" class="text-center">NSX</th>
                        <th style="width:130px" class="text-center">HSD</th>
                        <th style="width:140px" class="text-end">Thành tiền</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($items as $index => $item)
                        @php
                            $sub = $item->quantity * $item->cost_price;
                            $total += $sub;
                        @endphp

                        <tr>
                            <td class="text-center text-muted fw-semibold">
                                {{ $index + 1 }}
                            </td>

                            <td class="fw-medium">
                                {{ $item->variant->product->name ?? '-' }}
                            </td>

                            <td>
    <div>{{ $item->variant->attribute_value ?? '-' }}</div>
</td>

                            <td class="text-center">
                                {{ number_format($item->quantity) }}
                            </td>

                            <td class="text-end">
                                {{ number_format($item->cost_price) }} đ
                            </td>

                            <td class="text-center">
                                {{ optional($item->manufacture_date)->format('d/m/Y') ?? '---' }}
                            </td>

                            <td class="text-center">
                                {{ optional($item->expiry_date)->format('d/m/Y') ?? '---' }}
                            </td>

                            <td class="text-end fw-semibold">
                                {{ number_format($sub) }} đ
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Không có dữ liệu phiếu nhập
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- TOTAL --}}
        <div class="text-end mt-4">
            <h5 class="fw-bold text-danger mb-0">
                Tổng tiền: {{ number_format($total) }} đ
            </h5>
        </div>

    </div>
</div>

@endsection