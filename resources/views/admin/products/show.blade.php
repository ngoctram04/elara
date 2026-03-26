@extends('layouts.admin')

@section('title','Chi tiết sản phẩm')

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            {{-- HEADER --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-semibold mb-0">
                    Chi tiết sản phẩm
                </h4>

                <div>
                    <a href="{{ route('admin.products.edit', $product) }}"
                       class="btn btn-warning btn-sm text-white me-2">
                        <i class="bi bi-pencil"></i> Chỉnh sửa
                    </a>

                    <a href="{{ route('admin.products.index') }}"
                       class="btn btn-light btn-sm">
                        Quay lại
                    </a>
                </div>
            </div>

            @php
                $totalStock = $product->variants->sum('stock_quantity');
                $totalSold  = $product->variants->sum('sold_quantity');
                $attributeName = $product->variants->first()?->attribute_name;
            @endphp

            <div class="row">

                {{-- LEFT INFO --}}
                <div class="col-lg-6">
                    <p><strong>Tên sản phẩm:</strong> {{ $product->name }}</p>

                    <p>
                        <strong>Danh mục:</strong>
                        @if($product->category)
                            {{ $product->category?->parent?->name }}
                            @if($product->category?->parent) → @endif
                            {{ $product->category->name }}
                        @else
                            <span class="text-muted">Chưa phân loại</span>
                        @endif
                    </p>

                    <p>
                        <strong>Thương hiệu:</strong>
                        {{ $product->brand?->name ?? '---' }}
                    </p>

                    <p>
                        <strong>Trạng thái kho:</strong>
                        @if($totalStock > 0)
                            <span class="badge bg-success">
                                Còn hàng ({{ $totalStock }})
                            </span>
                        @else
                            <span class="badge bg-danger">Hết hàng</span>
                        @endif
                    </p>

                    <p>
                        <strong>Tổng đã bán:</strong>
                        <span class="badge bg-primary">
                            {{ $totalSold }}
                        </span>
                    </p>

                    <p>
                        <strong>Nổi bật:</strong>
                        @if($product->is_featured)
                            <span class="badge bg-warning text-dark">Có</span>
                        @else
                            <span class="badge bg-secondary">Không</span>
                        @endif
                    </p>

                    @php
    $displayPrices = $product->variants->map(function ($variant) {
        return $variant->final_price;
    });

    $minDisplayPrice = $displayPrices->min();
    $maxDisplayPrice = $displayPrices->max();
@endphp

<p>
    <strong>Khoảng giá:</strong>
    @if($product->variants->count())
        {{ number_format($minDisplayPrice) }}
        @if($minDisplayPrice != $maxDisplayPrice)
            – {{ number_format($maxDisplayPrice) }}
        @endif
        đ
    @else
        ---
    @endif
</p>
                </div>

                {{-- RIGHT IMAGES --}}
                <div class="col-lg-6">
                    <strong>Ảnh sản phẩm:</strong>
                    <div class="d-flex gap-2 flex-wrap mt-2">

                        @if($product->mainImage)
                            <img src="{{ $product->mainImage->url }}"
                                 width="100"
                                 class="rounded border border-primary">
                        @endif

                        @foreach($product->subImages as $img)
                            <img src="{{ $img->url }}"
                                 width="90"
                                 class="rounded border">
                        @endforeach

                        @if(!$product->images->count())
                            <span class="text-muted">Chưa có ảnh</span>
                        @endif
                    </div>
                </div>
            </div>

            <hr>

            {{-- ================= VARIANTS ================= --}}
            <h5 class="fw-semibold text-primary">
                Biến thể
                @if($attributeName)
                    <span class="text-muted small">({{ $attributeName }})</span>
                @endif
            </h5>

            <div class="table-responsive mt-3">
                <table class="table table-bordered align-middle">
                    <thead class="table-light text-center">
                        <tr>
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
                            $salePrice = $variant->final_price < $variant->price
                                ? $variant->final_price
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
                            $isLoss = $totalRemain > 0 && $profitAvg < 0;
                        @endphp

                        <tr class="text-center">
                            <td class="fw-semibold text-muted">
                                BT{{ str_pad($variant->id, 5, '0', STR_PAD_LEFT) }}
                            </td>

                            <td class="fw-semibold">
                                {{ $variant->attribute_value }}
                            </td>

                            {{-- GIÁ --}}
                            <td class="text-center">
                                @if ($variant->final_price < $variant->price)
                                    <div class="fw-semibold text-danger">
                                        {{ number_format($variant->final_price) }}đ
                                    </div>
                                    <div class="text-muted text-decoration-line-through small">
                                        {{ number_format($variant->price) }}đ
                                    </div>
                                @else
                                    <span class="fw-semibold">
                                        {{ number_format($variant->price) }}đ
                                    </span>
                                @endif

                                @if($isLoss)
                                    <div class="mt-1">
                                        <span class="badge bg-danger">Dưới vốn</span>
                                    </div>
                                @endif
                            </td>

                            {{-- STOCK --}}
                            <td>
                                @if($variant->stock_quantity > 0)
                                    <span class="badge bg-success">
                                        {{ $variant->stock_quantity }}
                                    </span>
                                @else
                                    <span class="badge bg-danger">0</span>
                                @endif
                            </td>

                            {{-- SOLD --}}
                            <td>
                                <span class="badge bg-primary">
                                    {{ $variant->sold_quantity }}
                                </span>
                            </td>

                            {{-- IMAGE --}}
                            <td>
                                @if($variant->images->first())
                                    <img src="{{ $variant->images->first()->url }}"
                                         width="60"
                                         class="rounded border">
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            {{-- XEM --}}
                            <td>
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#lotModal{{ $variant->id }}">
                                    <i class="bi bi-eye"></i> Xem
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

{{-- ================= MODAL CHI TIẾT LÔ ================= --}}
@foreach($product->variants as $variant)
    @php
        $salePrice = $variant->final_price < $variant->price
            ? $variant->final_price
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
            <div class="modal-content border-0 shadow">

                <div class="modal-header bg-light">
                    <div>
                        <h5 class="modal-title mb-1">Chi tiết lô nhập</h5>
                        <div class="small text-muted">
                            Biến thể:
                            <strong>{{ $product->name }} - {{ $variant->attribute_value }}</strong>
                            |
                            Mã:
                            <strong>BT{{ str_pad($variant->id, 5, '0', STR_PAD_LEFT) }}</strong>
                        </div>
                    </div>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    {{-- SUMMARY --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="border rounded p-3 bg-light h-100">
                                <div class="small text-muted mb-1">Giá bán</div>
                                <div class="fw-bold text-primary">
                                    {{ number_format($salePrice) }}đ
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded p-3 bg-light h-100">
                                <div class="small text-muted mb-1">Vốn thấp nhất</div>
                                <div class="fw-bold">
                                    {{ number_format($minCost) }}đ
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded p-3 bg-light h-100">
                                <div class="small text-muted mb-1">Vốn cao nhất</div>
                                <div class="fw-bold">
                                    {{ number_format($maxCost) }}đ
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded p-3 bg-light h-100">
                                <div class="small text-muted mb-1">Vốn TB tồn</div>
                                <div class="fw-bold {{ $profitAvg < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($avgCost) }}đ
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ALERT --}}
                    <div class="mb-3">
                        @if($totalRemain > 0)
                            @if($profitAvg < 0)
                                <div class="alert alert-danger py-2 mb-0">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                    Biến thể này đang bán dưới vốn trung bình khoảng
                                    <strong>{{ number_format(abs($profitAvg)) }}đ / sản phẩm</strong>.
                                </div>
                            @else
                                <div class="alert alert-success py-2 mb-0">
                                    <i class="bi bi-check-circle-fill me-1"></i>
                                    Biến thể này đang lãi trung bình khoảng
                                    <strong>{{ number_format($profitAvg) }}đ / sản phẩm</strong>.
                                </div>
                            @endif
                        @else
                            <div class="alert alert-secondary py-2 mb-0">
                                Không có lô tồn nào cho biến thể này.
                            </div>
                        @endif
                    </div>

                    {{-- BẢNG CHI TIẾT LÔ --}}
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr class="border-bottom">
                                    <th>Biến thể</th>
                                    <th class="text-end">Giá bán</th>
                                    <th>Lô nhập</th>
                                    <th class="text-end">Giá nhập</th>
                                    <th class="text-center">Còn lại</th>
                                    <th class="text-end">Chênh lệch</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lots as $index => $lot)
                                    @php
                                        $diff = $salePrice - ($lot->cost_price ?? 0);
                                    @endphp
                                    <tr class="border-bottom">
                                        <td class="fw-semibold">
                                            @if($index === 0)
                                                {{ $product->name }} - {{ $variant->attribute_value }}
                                            @endif
                                        </td>

                                        <td class="text-end">
                                            @if($index === 0)
                                                {{ number_format($salePrice) }}
                                            @endif
                                        </td>

                                        <td class="fw-semibold">
    {{ $lot->lot_code ?? $lot->batch_code ?? ('L' . $lot->id) }}
</td>

                                        <td class="text-end">
                                            {{ number_format($lot->cost_price ?? 0) }}
                                        </td>

                                        <td class="text-center">
                                            {{ $lot->remaining_quantity ?? 0 }}
                                        </td>

                                        <td class="text-end fw-semibold {{ $diff < 0 ? 'text-danger' : 'text-success' }}">
                                            @if($diff < 0)
                                                -{{ number_format(abs($diff)) }}
                                            @else
                                                +{{ number_format($diff) }}
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            Không có dữ liệu lô nhập còn tồn
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal">
                        Đóng
                    </button>
                </div>

            </div>
        </div>
    </div>
@endforeach
@endsection