@extends('layouts.admin')

@section('title', 'Chỉnh sửa mã giảm giá đơn hàng')

@section('content')
<form method="POST"
      action="{{ route('admin.promotions.update', $promotion) }}"
      class="card shadow-sm border-0">

    @csrf
    @method('PUT')

    {{-- 🔒 FIX CỨNG GIẢM THEO % --}}
    <input type="hidden" name="discount_type" value="percent">

    <div class="card-body">

        {{-- ERROR --}}
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

            {{-- NAME --}}
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

            {{-- CODE (READ ONLY) --}}
            <div class="col-md-6">
                <label class="form-label">Mã giảm giá</label>
                <input
                    type="text"
                    class="form-control"
                    value="{{ $promotion->code }}"
                    disabled
                >
            </div>

            {{-- DISCOUNT VALUE --}}
            <div class="col-md-6">
                <label class="form-label">Giá trị giảm (%)</label>
                <input
                    type="number"
                    name="discount_value"
                    class="form-control"
                    value="{{ old('discount_value', $promotion->discount_value) }}"
                    min="1"
                    max="100"
                    required
                >
                <small class="text-muted">
                    Nhập từ 1 đến 100 (%)
                </small>
            </div>

            {{-- MIN ORDER --}}
            <div class="col-md-6">
                <label class="form-label">Đơn tối thiểu</label>
                <input
                    type="number"
                    name="min_order_value"
                    class="form-control"
                    value="{{ old('min_order_value', $promotion->min_order_value) }}"
                    min="0"
                >
            </div>

            {{-- MAX DISCOUNT --}}
            <div class="col-md-6">
                <label class="form-label">Giảm tối đa</label>
                <input
                    type="number"
                    name="max_discount"
                    class="form-control"
                    value="{{ old('max_discount', $promotion->max_discount) }}"
                    min="0"
                >
            </div>

            {{-- USAGE LIMIT --}}
            <div class="col-md-6">
                <label class="form-label">Giới hạn lượt dùng</label>
                <input
                    type="number"
                    name="usage_limit"
                    class="form-control"
                    value="{{ old('usage_limit', $promotion->usage_limit) }}"
                    min="1"
                >
            </div>

            {{-- DATE --}}
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

            {{-- ACTIVE --}}
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
