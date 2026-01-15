@extends('layouts.admin')

@section('title', 'Tạo khuyến mãi sản phẩm')

@section('content')
<form method="POST"
      action="{{ route('admin.promotions.store') }}"
      class="card shadow-sm border-0">

    @csrf
    <input type="hidden" name="type" value="product">

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

        {{-- INFO --}}
        <h5 class="fw-semibold mb-3">Thông tin khuyến mãi</h5>

        <div class="row g-3 mb-4">

            {{-- NAME --}}
            <div class="col-md-6">
                <label class="form-label">Tên chương trình</label>
                <input
                    type="text"
                    name="name"
                    class="form-control"
                    value="{{ old('name') }}"
                    placeholder="VD: Sale mùa hè"
                    required
                >
            </div>

            {{-- DISCOUNT VALUE --}}
            <div class="col-md-6">
                <label class="form-label">Giá trị giảm (%)</label>
                <input
                    type="number"
                    name="discount_value"
                    class="form-control"
                    value="{{ old('discount_value') }}"
                    min="1"
                    max="100"
                    placeholder="VD: 20"
                    required
                >
                <small class="text-muted">
                    Nhập từ 1 đến 100 (%)
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

        {{-- PRODUCTS --}}
        <h5 class="fw-semibold mb-2">Chọn sản phẩm / biến thể</h5>

        @foreach ($products as $product)
            <div class="border rounded p-3 mb-2 product-box">

                <label class="fw-semibold">
                    <input
                        type="checkbox"
                        class="product-checkbox"
                        data-product="{{ $product->id }}"
                    >
                    {{ $product->name }}
                </label>

                <div class="ms-4 mt-2">
                    @foreach ($product->variants as $variant)
                        <label class="d-block">
                            <input
                                type="checkbox"
                                class="variant-checkbox"
                                name="products[{{ $product->id }}][]"
                                value="{{ $variant->id }}"
                                data-product="{{ $product->id }}"
                                {{ in_array($variant->id, old("products.{$product->id}", [])) ? 'checked' : '' }}
                            >
                            {{ $variant->displayName() }}
                        </label>
                    @endforeach
                </div>

            </div>
        @endforeach

    </div>

    {{-- FOOTER --}}
    <div class="card-footer text-end">
        <a href="{{ route('admin.promotions.index') }}"
           class="btn btn-light">
            Quay lại
        </a>

        <button class="btn btn-primary">
            Lưu khuyến mãi
        </button>
    </div>

</form>

{{-- JS: AUTO CHECK VARIANTS --}}
<script>
    document.querySelectorAll('.product-checkbox').forEach(productCheckbox => {
        productCheckbox.addEventListener('change', function () {
            const productId = this.dataset.product;
            const variants = document.querySelectorAll(
                `.variant-checkbox[data-product="${productId}"]`
            );

            variants.forEach(variant => {
                variant.checked = this.checked;
            });
        });
    });
</script>
@endsection
