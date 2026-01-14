@extends('layouts.admin')

@section('title', 'Chỉnh sửa khuyến mãi sản phẩm')

@section('content')
<form method="POST"
      action="{{ route('admin.promotions.update', $promotion) }}"
      class="card shadow-sm border-0">

    @csrf
    @method('PUT')

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

        <h5 class="fw-semibold mb-3">Thông tin khuyến mãi</h5>

        <div class="row g-3 mb-4">

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

            {{-- DISCOUNT TYPE --}}
            <div class="col-md-6">
                <label class="form-label">Kiểu giảm</label>
                <select name="discount_type" class="form-select">
                    <option value="percent"
                        @selected(old('discount_type', $promotion->discount_type) === 'percent')>
                        Giảm %
                    </option>
                    <option value="fixed"
                        @selected(old('discount_type', $promotion->discount_type) === 'fixed')>
                        Giảm tiền
                    </option>
                </select>
            </div>

            {{-- VALUE --}}
            <div class="col-md-6">
                <label class="form-label">Giá trị giảm</label>
                <input
                    type="number"
                    name="discount_value"
                    class="form-control"
                    value="{{ old('discount_value', $promotion->discount_value) }}"
                    min="0"
                    required
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

        <h5 class="fw-semibold mb-2">Sản phẩm / biến thể áp dụng</h5>

        @foreach ($products as $product)
            <div class="border rounded p-3 mb-2">

                <strong>{{ $product->name }}</strong>

                <div class="ms-4 mt-2">
                    @foreach ($product->variants as $variant)
                        <label class="d-block">
                            <input
                                type="checkbox"
                                name="products[{{ $product->id }}][]"
                                value="{{ $variant->id }}"
                                {{ $selected->where('variant_id', $variant->id)->count() ? 'checked' : '' }}
                            >
                            {{ $variant->displayName() }}
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach

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
