@extends('layouts.admin')

@section('title', 'Chỉnh sửa mã giảm giá đơn hàng')

@section('content')
<form method="POST"
      action="{{ route('admin.promotions.update', $promotion) }}"
      class="card shadow-sm border-0">

    @csrf
    @method('PUT')

    <input type="hidden" name="discount_type" value="percent">

    <div class="card-body">

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <h5 class="fw-semibold mb-3">Thông tin mã giảm giá</h5>

        <div class="row g-3">

            <div class="col-md-6">
                <label class="form-label">Tên chương trình</label>
                <input
                    type="text"
                    name="name"
                    class="form-control"
                    value="{{ old('name', $promotion->name) }}"
                    required
                >
            </div>

            <div class="col-md-6">
                <label class="form-label">Mã giảm giá</label>
                <input
                    type="text"
                    class="form-control"
                    value="{{ $promotion->code }}"
                    disabled
                >
            </div>

            <div class="col-md-6">
                <label class="form-label">Giá trị giảm (%)</label>
                <input
                    type="number"
                    name="discount_value"
                    class="form-control"
                    min="1"
                    max="100"
                    step="1"
                    value="{{ old('discount_value', (int) $promotion->discount_value) }}"
                    required
                >
                <small class="text-muted">
                    Nhập số nguyên từ 1 đến 100 (%)
                </small>
            </div>

            <div class="col-md-6">
                <label class="form-label">Đơn tối thiểu</label>
                <input
                    type="number"
                    name="min_order_value"
                    class="form-control"
                    min="0"
                    value="{{ old('min_order_value', $promotion->min_order_value) }}"
                >
            </div>

            <div class="col-md-6">
                <label class="form-label">Giảm tối đa</label>
                <input
                    type="number"
                    name="max_discount"
                    class="form-control"
                    min="0"
                    value="{{ old('max_discount', $promotion->max_discount) }}"
                >
            </div>

            <div class="col-md-6">
                <label class="form-label">Giới hạn lượt dùng</label>
                <input
                    type="number"
                    name="usage_limit"
                    class="form-control"
                    min="1"
                    value="{{ old('usage_limit', $promotion->usage_limit) }}"
                >
            </div>

            <div class="col-md-6">
                <label class="form-label">Thời gian áp dụng</label>
                <div class="d-flex gap-2">
                    <input
                        type="datetime-local"
                        name="start_date"
                        class="form-control"
                        value="{{ old('start_date', $promotion->start_date->format('Y-m-d\TH:i')) }}"
                        required
                    >
                    <input
                        type="datetime-local"
                        name="end_date"
                        class="form-control"
                        value="{{ old('end_date', $promotion->end_date->format('Y-m-d\TH:i')) }}"
                        required
                    >
                </div>
            </div>

            <div class="col-12">
                <label class="form-check-label">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        class="form-check-input me-1"
                        {{ old('is_active', $promotion->is_active) ? 'checked' : '' }}
                    >
                    Kích hoạt
                </label>
            </div>

        </div>
    </div>

    <div class="card-footer text-end">
        <a href="{{ route('admin.promotions.index') }}"
           class="btn btn-light">
            Quay lại
        </a>

        <button class="btn btn-primary">
            Cập nhật
        </button>
    </div>
</form>
@endsection
