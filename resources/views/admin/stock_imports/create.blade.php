@extends('layouts.admin')

@section('content')
<div class="container-fluid">

    <div class="row justify-content-center">
        <div class="col-xl-7 col-lg-8">

            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">

                    {{-- HEADER --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-semibold mb-0">
                            <i class="bi bi-box-seam text-primary me-2"></i>
                            Nhập hàng vào kho
                        </h4>

                        <a href="{{ route('admin.stock.history') }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-clock-history"></i> Lịch sử
                        </a>
                    </div>

                    {{-- SUCCESS --}}
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- FORM --}}
                    <form method="POST" action="{{ route('admin.stock.store') }}">
                        @csrf

                        {{-- BIẾN THỂ --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Biến thể sản phẩm</label>

                            <select name="variant_id"
                                    id="variantSelect"
                                    class="form-select shadow-sm @error('variant_id') is-invalid @enderror"
                                    required>
                                <option value="">-- Chọn biến thể --</option>

                                @foreach($variants as $v)
                                    <option value="{{ $v->id }}"
                                        data-stock="{{ (int) $v->stock_quantity }}"
                                        data-cost="{{ (float) $v->cost_price }}"
                                        {{ old('variant_id') == $v->id ? 'selected' : '' }}>
                                        #{{ $v->id }}
                                        | {{ $v->product->name }}
                                        | {{ $v->attribute_value }}
                                        (Tồn: {{ $v->stock_quantity }})
                                    </option>
                                @endforeach
                            </select>

                            @error('variant_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- THÔNG TIN HIỆN TẠI --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="alert alert-light border mb-0">
                                    Tồn hiện tại:
                                    <span class="fw-bold text-primary" id="currentStock">0</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="alert alert-light border mb-0">
                                    Giá vốn TB:
                                    <span class="fw-bold text-primary" id="currentCost">0 đ</span>
                                </div>
                            </div>
                        </div>

                        {{-- INPUT --}}
                        <div class="row">

                            <div class="col-md-4 mb-4">
                                <label class="form-label fw-semibold">Số lượng nhập</label>
                                <input type="number"
                                       name="quantity"
                                       id="quantityInput"
                                       value="{{ old('quantity') }}"
                                       class="form-control form-control-lg @error('quantity') is-invalid @enderror"
                                       min="1"
                                       required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label fw-semibold">Giá nhập</label>
                                <input type="number"
                                       name="cost_price"
                                       value="{{ old('cost_price') }}"
                                       class="form-control form-control-lg @error('cost_price') is-invalid @enderror"
                                       min="0"
                                       required>
                                @error('cost_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- HẠN --}}
                            <div class="col-md-4 mb-4">
                                <label class="form-label fw-semibold">
                                    Hạn sử dụng
                                </label>
                                <input type="date"
                                       name="expiry_date"
                                       value="{{ old('expiry_date', date('Y-m-d')) }}"
                                       class="form-control form-control-lg @error('expiry_date') is-invalid @enderror"
                                       min="{{ date('Y-m-d') }}">
                                @error('expiry_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        {{-- PREVIEW --}}
                        <div class="alert alert-info d-none" id="previewBox">
                            Tồn sau khi nhập:
                            <span class="fw-bold" id="previewStock"></span>
                        </div>

                        {{-- ACTION --}}
                        <div class="d-flex justify-content-between mt-3">
                            <a href="{{ url()->previous() }}"
                               class="btn btn-light px-4">
                                Quay lại
                            </a>

                            <button class="btn btn-primary px-4">
                                Nhập hàng
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>

</div>
@endsection


@push('scripts')
<script>
const variantSelect = document.getElementById('variantSelect');
const currentStockEl = document.getElementById('currentStock');
const currentCostEl = document.getElementById('currentCost');
const quantityInput = document.getElementById('quantityInput');
const previewBox = document.getElementById('previewBox');
const previewStockEl = document.getElementById('previewStock');

let currentStock = 0;

function formatMoney(value) {
    return Number(value).toLocaleString('vi-VN') + ' đ';
}

function updateVariantInfo() {
    const option = variantSelect.options[variantSelect.selectedIndex];
    if (!option || !option.value) return;

    currentStock = parseInt(option.dataset.stock || 0);
    const cost = parseFloat(option.dataset.cost || 0);

    currentStockEl.innerText = currentStock;
    currentCostEl.innerText = formatMoney(cost);
}

// Khi chọn biến thể
variantSelect.addEventListener('change', function () {
    updateVariantInfo();
    previewBox.classList.add('d-none');
});

// Preview tồn sau nhập
quantityInput.addEventListener('input', function () {
    const qty = parseInt(this.value || 0);

    if (qty > 0 && variantSelect.value) {
        previewStockEl.innerText = currentStock + qty;
        previewBox.classList.remove('d-none');
    } else {
        previewBox.classList.add('d-none');
    }
});

// Nếu form reload (old data)
document.addEventListener('DOMContentLoaded', function () {
    if (variantSelect.value) {
        updateVariantInfo();
    }
});
</script>
@endpush