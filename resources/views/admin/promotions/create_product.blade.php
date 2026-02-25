@extends('layouts.admin')

@section('title', 'Tạo khuyến mãi sản phẩm')

@section('content')
<form method="POST"
      action="{{ route('admin.promotions.store') }}"
      class="card shadow-sm border-0">

    @csrf
    <input type="hidden" name="type" value="product">
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

            <div class="col-md-6">
                <label class="form-label">Tên chương trình</label>
                <input type="text"
                       name="name"
                       class="form-control"
                       value="{{ old('name') }}"
                       required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Giá trị giảm (%)</label>
                <input type="number"
                       name="discount_value"
                       class="form-control"
                       value="{{ old('discount_value') }}"
                       min="1"
                       max="100"
                       required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Thời gian áp dụng</label>
                <div class="d-flex gap-2">
                    <input type="datetime-local"
                           name="start_date"
                           class="form-control"
                           value="{{ old('start_date') }}"
                           required>

                    <input type="datetime-local"
                           name="end_date"
                           class="form-control"
                           value="{{ old('end_date') }}"
                           required>
                </div>
            </div>

            <div class="col-12">
                <label class="form-check-label">
                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           class="form-check-input me-1"
                           {{ old('is_active', true) ? 'checked' : '' }}>
                    Kích hoạt ngay
                </label>
            </div>

        </div>

        {{-- PRODUCTS --}}
        <h5 class="fw-semibold mb-2">Chọn sản phẩm / biến thể</h5>

        @foreach ($products as $product)

            @php
                $allVariantIds = $product->variants->pluck('id')->toArray();
                $lockedCount = count(array_intersect($allVariantIds, $activeVariantIds ?? []));
                $allLocked = $lockedCount === count($allVariantIds);
            @endphp

            <div class="border rounded p-3 mb-2 product-box {{ $allLocked ? 'opacity-50' : '' }}">

                {{-- PRODUCT CHECKBOX --}}
                <label class="fw-semibold">
                    <input type="checkbox"
                           class="product-checkbox"
                           data-product="{{ $product->id }}"
                           {{ $allLocked ? 'disabled' : '' }}>
                    {{ $product->name }}

                    @if($allLocked)
                        <span class="text-danger small ms-2">
                            (Đang có khuyến mãi)
                        </span>
                    @endif
                </label>

                {{-- VARIANTS --}}
                <div class="ms-4 mt-2">
                    @foreach ($product->variants as $variant)

                        @php
                            $isLocked = in_array($variant->id, $activeVariantIds ?? []);
                        @endphp

                        <label class="d-block {{ $isLocked ? 'text-muted opacity-50' : '' }}">
                            <input type="checkbox"
                                   class="variant-checkbox"
                                   name="products[{{ $product->id }}][]"
                                   value="{{ $variant->id }}"
                                   data-product="{{ $product->id }}"
                                   {{ $isLocked ? 'disabled' : '' }}
                                   {{ in_array($variant->id, old("products.{$product->id}", [])) ? 'checked' : '' }}>

                            {{ $variant->displayName() }}

                            @if($isLocked)
                                <span class="text-danger small">
                                    (Đang khuyến mãi)
                                </span>
                            @endif
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
            Lưu khuyến mãi
        </button>
    </div>

</form>

{{-- JS: CHECK PRODUCT -> VARIANTS --}}
<script>
document.querySelectorAll('.product-checkbox').forEach(productCheckbox => {
    productCheckbox.addEventListener('change', function () {
        const productId = this.dataset.product;

        document.querySelectorAll(
            `.variant-checkbox[data-product="${productId}"]:not(:disabled)`
        ).forEach(variant => {
            variant.checked = this.checked;
        });
    });
});
</script>
@endsection