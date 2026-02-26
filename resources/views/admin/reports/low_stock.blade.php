@extends('layouts.admin')

@section('content')

<div class="container-fluid">

<div class="card shadow-sm">
<div class="card-body">

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">
        <i class="bi bi-exclamation-triangle text-danger me-2"></i>
        Sản phẩm sắp hết hàng
    </h5>

<a href="{{ route('admin.reports.index', ['from'=>$from ?? null,'to'=>$to ?? null]) }}"
   class="btn btn-secondary btn-sm">
    ← Quay lại
</a>


</div>

{{-- FILTER --}}

<form method="GET" class="row g-2 mb-3">

<div class="col-md-4">
    <input type="text"
           name="keyword"
           class="form-control"
           placeholder="Tìm sản phẩm..."
           value="{{ $keyword ?? '' }}">
</div>

<div class="col-md-3">
    <input type="number"
           name="max_stock"
           class="form-control"
           placeholder="Tồn tối đa (vd: 5)"
           value="{{ $max_stock ?? '' }}">
</div>

<div class="col-md-2">
    <button class="btn btn-primary w-100">
        Lọc
    </button>
</div>


</form>

{{-- TABLE --}}

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle">
    <thead class="table-light">
        <tr>
            <th>Sản phẩm</th>
            <th>Biến thể</th>
            <th class="text-center">Số lượng</th>
            <th class="text-center">Trạng thái</th>
        </tr>
    </thead>
    <tbody>
        @forelse($products as $p)
        <tr>
            <td>{{ $p->name }}</td>
            <td>{{ $p->attribute_value }}</td>
            <td class="text-center fw-semibold {{ $p->stock_quantity <= 2 ? 'text-danger' : 'text-warning' }}">
                {{ $p->stock_quantity }}
            </td>
            <td class="text-center">
                @if($p->stock_quantity == 0)
                    <span class="badge bg-danger">Hết hàng</span>
                @elseif($p->stock_quantity <= 2)
                    <span class="badge bg-danger">Rất thấp</span>
                @else
                    <span class="badge bg-warning text-dark">Sắp hết</span>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="text-center text-muted">
                Không có dữ liệu
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
</div>

{{-- PAGINATION --}}

<div class="mt-3">
    {{ $products->links() }}
</div>

</div>
</div>

</div>
@endsection
