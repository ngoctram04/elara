@extends('layouts.admin')

@section('title','Chi tiết sản phẩm')

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">

            {{-- HEADER --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h4 class="fw-bold mb-1">Chi tiết sản phẩm</h4>
                    <div class="text-muted small">Thông tin tổng quan và chi tiết biến thể sản phẩm</div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('admin.products.edit', $product) }}"
                       class="btn btn-warning btn-sm text-white px-3">
                        <i class="bi bi-pencil me-1"></i> Chỉnh sửa
                    </a>

                    <a href="{{ route('admin.products.index') }}"
                       class="btn btn-outline-secondary btn-sm px-3">
                        Quay lại
                    </a>
                </div>
            </div>

            @php
                $totalStock = $product->variants->sum('stock_quantity');
                $totalSold  = $product->variants->sum('sold_quantity');
                $attributeName = $product->variants->first()?->attribute_name;

                $displayPrices = $product->variants->map(function ($variant) {
                    return $variant->final_price ?? $variant->price;
                });

                $minDisplayPrice = $displayPrices->min();
                $maxDisplayPrice = $displayPrices->max();
            @endphp

            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="border rounded-4 p-4 h-100 bg-light-subtle">
                        <h6 class="fw-bold mb-3">Thông tin sản phẩm</h6>

                        <div class="mb-3">
                            <div class="small text-muted mb-1">Tên sản phẩm</div>
                            <div class="fw-semibold">{{ $product->name }}</div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted mb-1">Danh mục</div>
                            <div class="fw-semibold">
                                @if($product->category)
                                    {{ $product->category?->parent?->name }}
                                    @if($product->category?->parent) → @endif
                                    {{ $product->category->name }}
                                @else
                                    <span class="text-muted">Chưa phân loại</span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted mb-1">Thương hiệu</div>
                            <div class="fw-semibold">{{ $product->brand?->name ?? '---' }}</div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted mb-1">Khoảng giá</div>
                            <div class="fw-semibold text-primary">
                                @if($product->variants->count())
                                    {{ number_format($minDisplayPrice, 0, ',', '.') }}
                                    @if($minDisplayPrice != $maxDisplayPrice)
                                        – {{ number_format($maxDisplayPrice, 0, ',', '.') }}
                                    @endif
                                    đ
                                @else
                                    ---
                                @endif
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-3">
                            @if($totalStock > 0)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                    Còn hàng ({{ $totalStock }})
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2">
                                    Hết hàng
                                </span>
                            @endif

                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                                Đã bán: {{ $totalSold }}
                            </span>

                            @if($product->is_featured)
                                <span class="badge bg-warning-subtle text-dark border px-3 py-2">
                                    Sản phẩm nổi bật
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-dark border px-3 py-2">
                                    Không nổi bật
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="border rounded-4 p-4 h-100">
                        <h6 class="fw-bold mb-3">Ảnh sản phẩm</h6>

                        <div class="d-flex flex-wrap gap-3">
                            @if($product->mainImage)
                                <div>
                                    <div class="small text-muted mb-2">Ảnh chính</div>
                                    <img src="{{ $product->mainImage->url }}"
                                         width="120"
                                         class="rounded-3 border">
                                </div>
                            @endif

                            @foreach($product->subImages as $img)
                                <div>
                                    <div class="small text-muted mb-2">Ảnh phụ</div>
                                    <img src="{{ $img->url }}"
                                         width="95"
                                         class="rounded-3 border">
                                </div>
                            @endforeach

                            @if(!$product->images->count())
                                <span class="text-muted">Chưa có ảnh</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- VARIANTS --}}
            <div class="border rounded-4 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-primary mb-0">
                        Biến thể
                        @if($attributeName)
                            <span class="text-muted small">({{ $attributeName }})</span>
                        @endif
                    </h5>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th width="150">Mã biến thể</th>
                                <th>Giá trị</th>
                                <th width="160">Giá bán</th>
                                <th width="110">Tồn kho</th>
                                <th width="110">Đã bán</th>
                                <th width="100">Ảnh</th>
                                <th width="150">Chi tiết vốn/Lô</th>
                            </tr>
                        </thead>

                        <tbody>
                        @forelse($product->variants as $variant)
                            @php
                                $salePrice = ($variant->final_price ?? $variant->price) < $variant->price
                                    ? ($variant->final_price ?? $variant->price)
                                    : $variant->price;

                                $lots = collect($variant->stockImports ?? [])
                                    ->filter(function ($lot) {
                                        return ($lot->remaining_quantity ?? 0) > 0;
                                    })
                                    ->sortBy('created_at')
                                    ->values();

                                $totalRemain = $lots->sum('remaining_quantity');

                                $avgCost = $totalRemain > 0
                                    ? $lots->sum(function ($lot) {
                                        return ($lot->remaining_quantity ?? 0) * ($lot->cost_price ?? 0);
                                    }) / $totalRemain
                                    : 0;

                                $profitAvg = $salePrice - $avgCost;
                                $isLoss = $totalRemain > 0 && $profitAvg < 0;
                            @endphp

                            <tr class="text-center">
                                <td class="fw-semibold text-muted">
                                    BT{{ str_pad($variant->id, 5, '0', STR_PAD_LEFT) }}
                                </td>

                                <td class="fw-semibold">
                                    {{ $variant->attribute_value }}
                                </td>

                                <td>
                                    @if (($variant->final_price ?? $variant->price) < $variant->price)
                                        <div class="fw-bold text-danger">
                                            {{ number_format($variant->final_price, 0, ',', '.') }}đ
                                        </div>
                                        <div class="small text-muted text-decoration-line-through">
                                            {{ number_format($variant->price, 0, ',', '.') }}đ
                                        </div>
                                    @else
                                        <div class="fw-bold">
                                            {{ number_format($variant->price, 0, ',', '.') }}đ
                                        </div>
                                    @endif

                                    @if($isLoss)
                                        <div class="mt-1">
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                                Dưới vốn
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    @if($variant->stock_quantity > 0)
                                        <span class="badge bg-success px-3 py-2">
                                            {{ $variant->stock_quantity }}
                                        </span>
                                    @else
                                        <span class="badge bg-danger px-3 py-2">0</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge bg-primary px-3 py-2">
                                        {{ $variant->sold_quantity }}
                                    </span>
                                </td>

                                <td>
                                    @if($variant->images->first())
                                        <img src="{{ $variant->images->first()->url }}"
                                             width="60"
                                             class="rounded-3 border">
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>

                                <td>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary rounded-3"
                                            data-bs-toggle="modal"
                                            data-bs-target="#lotModal{{ $variant->id }}">
                                        <i class="bi bi-eye me-1"></i> Xem
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    Chưa có biến thể nào
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- MODAL CHI TIẾT LÔ --}}
@foreach($product->variants as $variant)
    @php
        $salePrice = ($variant->final_price ?? $variant->price) < $variant->price
            ? ($variant->final_price ?? $variant->price)
            : $variant->price;

        $lots = collect($variant->stockImports ?? [])
            ->filter(function ($lot) {
                return ($lot->remaining_quantity ?? 0) > 0;
            })
            ->sortBy('created_at')
            ->values();

        $totalRemain = $lots->sum('remaining_quantity');

        $avgCost = $totalRemain > 0
            ? $lots->sum(function ($lot) {
                return ($lot->remaining_quantity ?? 0) * ($lot->cost_price ?? 0);
            }) / $totalRemain
            : 0;

        $minCost = $lots->count() ? $lots->min('cost_price') : 0;
        $maxCost = $lots->count() ? $lots->max('cost_price') : 0;
        $profitAvg = $salePrice - $avgCost;
    @endphp

    <div class="modal fade" id="lotModal{{ $variant->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0 shadow rounded-4">

                <div class="modal-header bg-light">
                    <div>
                        <h5 class="modal-title mb-1">Chi tiết lô nhập</h5>
                        <div class="small text-muted">
                            <strong>{{ $product->name }} - {{ $variant->attribute_value }}</strong>
                            | Mã:
                            <strong>BT{{ str_pad($variant->id, 5, '0', STR_PAD_LEFT) }}</strong>
                        </div>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="border rounded-4 p-3 bg-light h-100">
                                <div class="small text-muted mb-1">Giá bán</div>
                                <div class="fw-bold text-primary">{{ number_format($salePrice, 0, ',', '.') }}đ</div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded-4 p-3 bg-light h-100">
                                <div class="small text-muted mb-1">Vốn thấp nhất</div>
                                <div class="fw-bold">{{ number_format($minCost, 0, ',', '.') }}đ</div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded-4 p-3 bg-light h-100">
                                <div class="small text-muted mb-1">Vốn cao nhất</div>
                                <div class="fw-bold">{{ number_format($maxCost, 0, ',', '.') }}đ</div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded-4 p-3 bg-light h-100">
                                <div class="small text-muted mb-1">Vốn TB tồn</div>
                                <div class="fw-bold {{ $profitAvg < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($avgCost, 0, ',', '.') }}đ
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        @if($totalRemain > 0)
                            @if($profitAvg < 0)
                                <div class="alert alert-danger rounded-4 py-3 mb-0">
                                    Biến thể này đang bán dưới vốn trung bình khoảng
                                    <strong>{{ number_format(abs($profitAvg), 0, ',', '.') }}đ / sản phẩm</strong>.
                                </div>
                            @else
                                <div class="alert alert-success rounded-4 py-3 mb-0">
                                    Biến thể này đang lãi trung bình khoảng
                                    <strong>{{ number_format($profitAvg, 0, ',', '.') }}đ / sản phẩm</strong>.
                                </div>
                            @endif
                        @else
                            <div class="alert alert-secondary rounded-4 py-3 mb-0">
                                Không có lô tồn nào cho biến thể này.
                            </div>
                        @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Biến thể</th>
                                    <th class="text-end">Giá bán</th>
                                    <th>Lô nhập</th>
                                    <th class="text-center">Ngày sản xuất</th>
                                    <th class="text-center">HSD</th>
                                    <th class="text-end">Giá nhập</th>
                                    <th class="text-center">Còn lại</th>
                                    <th class="text-end">Chênh lệch</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lots as $index => $lot)
                                    @php
                                        $diff = $salePrice - ($lot->cost_price ?? 0);

                                        $manufacturedAt = $lot->manufactured_at
                                            ?? $lot->manufacture_date
                                            ?? $lot->production_date
                                            ?? null;

                                        $expiredAt = $lot->expired_at
                                            ?? $lot->expiry_date
                                            ?? $lot->expiration_date
                                            ?? null;
                                    @endphp
                                    <tr>
                                        <td class="fw-semibold">
                                            @if($index === 0)
                                                {{ $product->name }} - {{ $variant->attribute_value }}
                                            @endif
                                        </td>

                                        <td class="text-end">
                                            @if($index === 0)
                                                {{ number_format($salePrice, 0, ',', '.') }}
                                            @endif
                                        </td>

                                        <td class="fw-semibold">
                                            {{ $lot->lot_code ?? $lot->batch_code ?? ('L' . $lot->id) }}
                                        </td>

                                        <td class="text-center">
                                            {{ $manufacturedAt ? \Carbon\Carbon::parse($manufacturedAt)->format('d/m/Y') : '--' }}
                                        </td>

                                        <td class="text-center">
                                            {{ $expiredAt ? \Carbon\Carbon::parse($expiredAt)->format('d/m/Y') : '--' }}
                                        </td>

                                        <td class="text-end">
                                            {{ number_format($lot->cost_price ?? 0, 0, ',', '.') }}
                                        </td>

                                        <td class="text-center">
                                            {{ $lot->remaining_quantity ?? 0 }}
                                        </td>

                                        <td class="text-end fw-semibold {{ $diff < 0 ? 'text-danger' : 'text-success' }}">
                                            @if($diff < 0)
                                                -{{ number_format(abs($diff), 0, ',', '.') }}
                                            @else
                                                +{{ number_format($diff, 0, ',', '.') }}
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            Không có dữ liệu lô nhập còn tồn
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Đóng
                    </button>
                </div>

            </div>
        </div>
    </div>
@endforeach
@endsection