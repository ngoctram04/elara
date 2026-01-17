@extends('layouts.admin')

@section('title', 'Chỉnh sửa khuyến mãi sản phẩm')

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

            {{-- ✅ DISCOUNT VALUE – FIX 10,00 --}}
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

                <label class="fw-semibold">
                    {{ $product->name }}
                </label>

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
