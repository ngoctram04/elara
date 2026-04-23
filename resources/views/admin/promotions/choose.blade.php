@extends('layouts.admin')

@section('title','Chọn loại khuyến mãi')

@section('content')

<div class="card border-0 shadow-sm">
<div class="card-body">


<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h5 class="fw-bold mb-1">
Chọn loại khuyến mãi
</h5>

<small class="text-muted">
Tạo chương trình khuyến mãi mới cho hệ thống
</small>
</div>

<a href="{{ route('admin.promotions.index') }}"
class="btn btn-outline-secondary btn-sm">

<i class="bi bi-arrow-left"></i>
Quay lại

</a>

</div>

<div class="row g-4">


<div class="col-md-6">

<a href="{{ route('admin.promotions.createProduct') }}"
class="card border-0 shadow-sm h-100 text-center p-4 promotion-card text-decoration-none">

<i class="bi bi-box-seam display-5 text-primary"></i>

<h5 class="mt-3 fw-semibold">

Khuyến mãi sản phẩm

</h5>

<p class="text-muted mb-0">

Áp dụng cho sản phẩm hoặc biến thể cụ thể

</p>

</a>

</div>


<div class="col-md-6">

<a href="{{ route('admin.promotions.createOrder') }}"
class="card border-0 shadow-sm h-100 text-center p-4 promotion-card text-decoration-none">

<i class="bi bi-cart-check display-5 text-success"></i>

<h5 class="mt-3 fw-semibold">

Mã giảm giá đơn hàng

</h5>

<p class="text-muted mb-0">

Áp dụng cho toàn bộ đơn hàng

</p>

</a>

</div>

</div>

</div>
</div>

<style>

.promotion-card{

transition: all .2s ease;

cursor: pointer;

}

.promotion-card:hover{

transform: translateY(-6px);

box-shadow: 0 12px 28px rgba(0,0,0,.08);

}

</style>

@endsection
