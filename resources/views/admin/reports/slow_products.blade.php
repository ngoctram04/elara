@extends('layouts.admin')

@section('content')

<div class="container-fluid">

<div class="card shadow-sm">
<div class="card-body">

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">
        <i class="bi bi-clock-history text-warning me-2"></i>
        Sản phẩm tồn lâu
    </h5>

<a href="{{ route('admin.reports.index') }}"
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
           name="days"
           class="form-control"
           placeholder="Số ngày chưa bán (vd: 30)"
           value="{{ $days ?? '' }}">
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
            <th class="text-center">Tồn kho</th>
            <th class="text-center">Lần bán cuối</th>
            <th class="text-center">Trạng thái</th>
        </tr>
    </thead>
    <tbody>
        @forelse($products as $p)

    @php
        $lastSold = $p->last_sold ? \Carbon\Carbon::parse($p->last_sold) : null;
        $daysDiff = $lastSold ? $lastSold->diffInDays(now()) : null;
    @endphp

    <tr>
        <td>{{ $p->name }}</td>

        <td class="text-center fw-semibold">
            {{ $p->stock_quantity }}
        </td>

        <td class="text-center">
            {{ $lastSold ? $lastSold->format('d/m/Y') : 'Chưa bán' }}
        </td>

        <td class="text-center">
            @if(!$lastSold)
                <span class="badge bg-danger">Chưa từng bán</span>
            @elseif($daysDiff >= 60)
                <span class="badge bg-danger">Tồn rất lâu</span>
            @elseif($daysDiff >= 30)
                <span class="badge bg-warning text-dark">Tồn lâu</span>
            @else
                <span class="badge bg-success">Bình thường</span>
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
