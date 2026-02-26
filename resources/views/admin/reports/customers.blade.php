@extends('layouts.admin')

@section('content')

<div class="container-fluid">

<div class="card shadow-sm">
<div class="card-body">

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">
        <i class="bi bi-people me-2 text-primary"></i>
        Top khách hàng
    </h5>

<a href="{{ route('admin.reports.index', ['from'=>$from,'to'=>$to]) }}"
   class="btn btn-secondary btn-sm">
    ← Quay lại
</a>

</div>

{{-- FILTER --}}

<form method="GET" class="row g-2 mb-3">

<div class="col-md-3">
    <input type="date" name="from" class="form-control"
           value="{{ $from }}">
</div>

<div class="col-md-3">
    <input type="date" name="to" class="form-control"
           value="{{ $to }}">
</div>

<div class="col-md-4">
    <input type="text" name="keyword" class="form-control"
           placeholder="Tìm tên khách hàng..."
           value="{{ $keyword }}">
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
            <th>Khách hàng</th>
            <th class="text-center">Số đơn</th>
            <th class="text-end">Tổng chi tiêu</th>
        </tr>
    </thead>
    <tbody>
        @forelse($customers as $c)
        <tr>
            <td>{{ $c->name }}</td>
            <td class="text-center">{{ $c->orders }}</td>
            <td class="text-end text-success fw-semibold">
                {{ number_format($c->spending) }} đ
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
    {{ $customers->links() }}
</div>

</div>
</div>

</div>
@endsection
