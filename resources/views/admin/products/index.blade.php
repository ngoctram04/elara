@extends('layouts.admin')

@section('title','Danh sách sản phẩm')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">Danh sách sản phẩm</h5>
                <small class="text-muted">Quản lý toàn bộ sản phẩm trong hệ thống</small>
            </div>

            <a href="{{ route('admin.products.create') }}"
               class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Thêm sản phẩm
            </a>
        </div>

        {{-- FILTER --}}
        <form class="mb-4" method="GET">
            <div class="d-flex flex-wrap gap-2 align-items-center">

                <div style="min-width:280px; flex:1;">
                    <input type="text"
                           name="keyword"
                           value="{{ request('keyword') }}"
                           class="form-control form-control-sm"
                           placeholder="Tìm tên sản phẩm hoặc mã...">
                </div>

                <select name="category_id" class="form-select form-select-sm" style="width:170px;">
                    <option value="">Danh mục</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ (string) request('category_id') === (string) $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                <select name="brand_id" class="form-select form-select-sm" style="width:160px;">
                    <option value="">Thương hiệu</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}"
                            {{ (string) request('brand_id') === (string) $brand->id ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>

                <select name="status" class="form-select form-select-sm" style="width:150px;">
                    <option value="">Kho</option>
                    <option value="in_stock" {{ request('status') === 'in_stock' ? 'selected' : '' }}>
                        Còn hàng
                    </option>
                    <option value="out_stock" {{ request('status') === 'out_stock' ? 'selected' : '' }}>
                        Hết hàng
                    </option>
                </select>

                <select name="profit_status" class="form-select form-select-sm" style="width:160px;">
                    <option value="">Giá</option>
                    <option value="safe" {{ request('profit_status') === 'safe' ? 'selected' : '' }}>
                        Ổn
                    </option>
                    <option value="under_cost" {{ request('profit_status') === 'under_cost' ? 'selected' : '' }}>
                        Dưới vốn
                    </option>
                </select>

                <button class="btn btn-primary btn-sm">
                    <i class="bi bi-search"></i>
                </button>

                <a href="{{ route('admin.products.index') }}"
                   class="btn btn-outline-secondary btn-sm">
                    Đặt lại
                </a>
            </div>
        </form>

        {{-- TABLE --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-center">
                    <tr>
                        <th width="70">Mã</th>
                        <th width="80">Ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th width="170">Giá</th>
                        <th width="150">Danh mục</th>
                        <th width="120">Kho</th>
                        <th width="140">Tình trạng</th>
                        <th width="185">Hành động</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($products as $product)
                        @php
                            $variants = $product->variants ?? collect();

                            $originMin = $variants->min('price');
                            $originMax = $variants->max('price');

                            $displayPrices = $variants->map(fn($variant) => $variant->final_price);
                            $sellMin = $displayPrices->min();
                            $sellMax = $displayPrices->max();

                            $hasPromotion = $variants->contains(function ($variant) {
                                return $variant->final_price < $variant->price;
                            });

                            $warningLevel = 'safe';

                            if (($product->total_stock ?? 0) > 0) {
                                foreach ($variants as $variant) {
                                    $salePrice = $variant->final_price;

                                    $remainingLots = collect($variant->stockImports ?? [])
                                        ->filter(function ($lot) {
                                            return (int) ($lot->remaining_quantity ?? 0) > 0;
                                        });

                                    $totalRemain = $remainingLots->sum('remaining_quantity');

                                    if ($totalRemain <= 0) {
                                        continue;
                                    }

                                    $avgCost = $remainingLots->sum(function ($lot) {
                                        return ((int) ($lot->remaining_quantity ?? 0)) * ((float) ($lot->cost_price ?? 0));
                                    }) / $totalRemain;

                                    if ($salePrice < $avgCost) {
                                        $warningLevel = 'danger';
                                        break;
                                    }
                                }
                            }
                        @endphp

                        <tr>
                            <td class="text-center text-muted fw-semibold">
                                SP{{ str_pad($product->id, 5, '0', STR_PAD_LEFT) }}
                            </td>

                            <td class="text-center">
                                @if($product->mainImage)
                                    <img src="{{ $product->mainImage->url }}"
                                         class="rounded border"
                                         width="55"
                                         alt="{{ $product->name }}">
                                @else
                                    <span class="text-muted small">No image</span>
                                @endif
                            </td>

                            <td>
                                <div class="fw-semibold">{{ $product->name }}</div>
                                <div class="small text-muted mt-1">
                                    {{ $product->brand?->name ?? '---' }}
                                </div>
                            </td>

                            {{-- GIÁ --}}
                            <td class="text-end">
                                @if($originMin)
                                    @if($hasPromotion)
                                        <div class="fw-semibold text-danger">
                                            {{ number_format($sellMin, 0, ',', '.') }}
                                            @if($sellMin != $sellMax)
                                                – {{ number_format($sellMax, 0, ',', '.') }}
                                            @endif
                                            đ
                                        </div>

                                        <div class="text-muted text-decoration-line-through small">
                                            {{ number_format($originMin, 0, ',', '.') }}
                                            @if($originMin != $originMax)
                                                – {{ number_format($originMax, 0, ',', '.') }}
                                            @endif
                                            đ
                                        </div>
                                    @else
                                        <div class="fw-semibold">
                                            {{ number_format($sellMin, 0, ',', '.') }}
                                            @if($sellMin != $sellMax)
                                                – {{ number_format($sellMax, 0, ',', '.') }}
                                            @endif
                                            đ
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted">---</span>
                                @endif
                            </td>

                            <td class="text-center">
                                {{ $product->category?->name ?? '---' }}
                            </td>

                            {{-- KHO --}}
                            <td class="text-center">
                                <div class="fw-semibold">
                                    Tồn: {{ $product->total_stock ?? 0 }}
                                </div>
                                <div class="fw-semibold">
                                    Bán: {{ $product->total_sold ?? 0 }}
                                </div>
                            </td>

                            {{-- TÌNH TRẠNG --}}
                            <td class="text-center">
                                <div class="fw-semibold">
    @if(($product->total_stock ?? 0) > 0)
        <span class="text-success">Còn hàng</span>
    @else
        <span class="text-danger">Hết hàng</span>
    @endif
</div>

                                <div class="fw-semibold">
                                    @if($warningLevel === 'danger')
                                        <span class="text-danger fw-semibold">Dưới vốn</span>
                                    @else
                                        <span class="fw-semibold">Ổn</span>
                                    @endif
                                </div>
                            </td>

                            <td class="text-center">
                                <a href="{{ route('admin.products.show', $product) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <a href="{{ route('admin.products.edit', $product) }}"
                                   class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <form action="{{ route('admin.products.toggle', $product) }}"
                                      method="POST"
                                      class="d-inline">
                                    @csrf
                                    @method('PATCH')

                                    <button class="btn btn-sm {{ $product->is_active ? 'btn-secondary' : 'btn-success' }}">
                                        @if($product->is_active)
                                            <i class="bi bi-eye-slash"></i>
                                        @else
                                            <i class="bi bi-eye"></i>
                                        @endif
                                    </button>
                                </form>

                                <form action="{{ route('admin.products.destroy', $product) }}"
                                      method="POST"
                                      class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Chưa có sản phẩm nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <small class="text-muted">
                @if($products->total() > 0)
                    Hiển thị {{ $products->firstItem() }} – {{ $products->lastItem() }}
                    / {{ $products->total() }} sản phẩm
                @else
                    Không có dữ liệu
                @endif
            </small>

            {{ $products->links('vendor.pagination.custom-blue') }}
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'Xóa sản phẩm?',
                text: 'Hành động này không thể hoàn tác',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#dc3545'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
@endpush