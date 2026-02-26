@extends('layouts.admin')

@section('content')

<div class="container-fluid">

<div class="card shadow-sm">
<div class="card-body">

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">
        <i class="bi bi-heart-fill text-danger me-2"></i>
        Sản phẩm được yêu thích
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

<div class="col-md-2">
    <button class="btn btn-primary w-100">
        Tìm kiếm
    </button>
</div>

</form>

{{-- TABLE --}}

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle">
    <thead class="table-light">
        <tr>
            <th>Sản phẩm</th>
            <th class="text-center">Lượt yêu thích</th>
            <th class="text-center">Mức độ</th>
        </tr>
    </thead>
    <tbody>
        @forelse($products as $p)
        <tr>
            <td>{{ $p->name }}</td>

        <td class="text-center fw-semibold">
            {{ $p->total_wishlist }}
        </td>

        <td class="text-center">
            @if($p->total_wishlist >= 50)
                <span class="badge bg-danger">Rất hot</span>
            @elseif($p->total_wishlist >= 20)
                <span class="badge bg-warning text-dark">Quan tâm cao</span>
            @elseif($p->total_wishlist >= 5)
                <span class="badge bg-info text-dark">Quan tâm</span>
            @else
                <span class="badge bg-secondary">Ít quan tâm</span>
            @endif
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="3" class="text-center text-muted">
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
