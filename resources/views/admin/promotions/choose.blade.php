@extends('layouts.admin')
@section('title', 'Chọn loại khuyến mãi')

@section('content')
<div class="row g-4">

    <div class="col-md-6">
        <a href="{{ route('admin.promotions.create.product') }}"
           class="card text-center p-4 h-100 shadow-sm text-decoration-none">
            <i class="bi bi-box-seam display-4 text-primary"></i>
            <h5 class="mt-3">Khuyến mãi sản phẩm</h5>
            <p class="text-muted">
                Áp dụng cho sản phẩm hoặc biến thể cụ thể
            </p>
        </a>
    </div>

    <div class="col-md-6">
        <a href="{{ route('admin.promotions.create.order') }}"
           class="card text-center p-4 h-100 shadow-sm text-decoration-none">
            <i class="bi bi-cart-check display-4 text-success"></i>
            <h5 class="mt-3">Mã giảm giá đơn hàng</h5>
            <p class="text-muted">
                Áp dụng cho toàn bộ đơn hàng
            </p>
        </a>
    </div>

</div>
@endsection
