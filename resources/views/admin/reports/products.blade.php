@extends('layouts.admin')

@section('content')
<div class="container-fluid">

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Tất cả sản phẩm bán chạy</h4>
    <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
        ← Quay lại Dashboard
    </a>
</div>

{{-- FILTER + SEARCH --}}
<div class="card mb-3">
<div class="card-body">
<form method="GET" class="row g-2">

    <div class="col-md-2">
        <label>Từ ngày</label>
        <input type="date" name="from" class="form-control" value="{{ $from }}">
    </div>

    <div class="col-md-2">
        <label>Đến ngày</label>
        <input type="date" name="to" class="form-control" value="{{ $to }}">
    </div>

    <div class="col-md-3">
        <label>Tìm sản phẩm</label>
        <input type="text" name="keyword" class="form-control" 
               placeholder="Nhập tên..." value="{{ $keyword }}">
    </div>

    <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary w-100">Lọc</button>
    </div>

</form>
</div>
</div>



{{-- TABLE --}}
<div class="card">
<div class="card-body">

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Sản phẩm</th>
            <th class="text-center">Số lượng bán</th>
            <th class="text-end">Doanh thu</th>
        </tr>
    </thead>
    <tbody>
        @forelse($products as $p)
        <tr>
            <td>{{ $p->name }}</td>
            <td class="text-center">{{ $p->total_sold }}</td>
            <td class="text-end">{{ number_format($p->revenue) }} đ</td>
        </tr>
        @empty
        <tr>
            <td colspan="3" class="text-center text-muted">Không có dữ liệu</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{ $products->links() }}

</div>
</div>

</div>
@endsection