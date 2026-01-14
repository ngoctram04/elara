@extends('layouts.admin')

@section('title', 'Tạo mã giảm giá đơn hàng')

@section('content')
<form method="POST"
      action="{{ route('admin.promotions.store') }}"
      class="card shadow-sm border-0">

    @csrf
    <input type="hidden" name="type" value="order">

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
                    value="{{ old('name') }}"
                    placeholder="VD: Giảm giá khai trương"
                    required
                >
            </div>

            {{-- CODE --}}
            <div class="col-md-6">
                <label class="form-label">Mã giảm giá</label>
                <input
                    type="text"
                    name="code"
                    class="form-control text-uppercase"
                    value="{{ old('code') }}"
                    placeholder="VD: SALE50"
                    required
                >
                <small class="text-muted">
                    Mỗi đơn hàng chỉ áp dụng được 1 mã
                </small>
            </div>

            {{-- DISCOUNT TYPE --}}
            <div class="col-md-6">
                <label class="form-label">Kiểu giảm</label>
                <select name="discount_type" class="form-select">
                    <option value="percent" @selected(old('discount_type') === 'percent')>
                        Giảm theo %
                    </option>
                    <option value="fixed" @selected(old('discount_type') === 'fixed')>
                        Giảm theo tiền (VNĐ)
                    </option>
                </select>
            </div>

            {{-- DISCOUNT VALUE --}}
            <div class="col-md-6">
                <label class="form-label">Giá trị giảm</label>
                <input
                    type="number"
                    name="discount_value"
                    class="form-control"
                    value="{{ old('discount_value') }}"
                    min="0"
                    required
                >
            </div>

            {{-- MIN ORDER --}}
            <div class="col-md-6">
                <label class="form-label">Đơn hàng tối thiểu</label>
                <input
                    type="number"
                    name="min_order_value"
                    class="form-control"
                    value="{{ old('min_order_value') }}"
                    min="0"
                    placeholder="VD: 200000"
                >
            </div>

            {{-- MAX DISCOUNT --}}
            <div class="col-md-6">
                <label class="form-label">Giảm tối đa</label>
                <input
                    type="number"
                    name="max_discount"
                    class="form-control"
                    value="{{ old('max_discount') }}"
                    min="0"
                    placeholder="VD: 50000"
                >
            </div>

            {{-- USAGE LIMIT --}}
            <div class="col-md-6">
                <label class="form-label">Số lượt sử dụng</label>
                <input
                    type="number"
                    name="usage_limit"
                    class="form-control"
                    value="{{ old('usage_limit') }}"
                    min="1"
                    placeholder="VD: 100"
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
                        value="{{ old('start_date') }}"
                        required
                    >
                    <input
                        type="datetime-local"
                        name="end_date"
                        class="form-control"
                        value="{{ old('end_date') }}"
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
                        {{ old('is_active', true) ? 'checked' : '' }}
                    >
                    Kích hoạt ngay
                </label>
            </div>

        </div>
    </div>

    {{-- FOOTER --}}
    <div class="card-footer text-end">
        <a href="{{ route('admin.promotions.index') }}"
           class="btn btn-light">
            Quay lại
        </a>

        <button class="btn btn-primary">
            Tạo mã giảm giá
        </button>
    </div>

</form>

{{-- AUTO UPPERCASE CODE --}}
<script>
    document.querySelector('input[name="code"]')
        ?.addEventListener('input', function () {
            this.value = this.value.toUpperCase();
        });
</script>
@endsection
