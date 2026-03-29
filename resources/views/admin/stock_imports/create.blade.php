@extends('layouts.admin')

@section('title','Nhập kho')

@section('content')
@vite(['resources/css/stock_import.css', 'resources/js/stock_import.js'])

<div class="card border-0 shadow-sm stock-import-card">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">Nhập hàng vào kho</h5>
                <small class="text-muted">Tạo phiếu nhập nhiều biến thể sản phẩm</small>
            </div>

            <a href="{{ route('admin.stock.history') }}"
               class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-clock-history me-1"></i>
                Lịch sử nhập
            </a>
        </div>

        @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    showToast(@json(session('success')), 'success');
                });
            </script>
        @endif

        @if(session('warning'))
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    @foreach(session('warning') as $w)
                        showToast(@json($w), 'warning');
                    @endforeach
                });
            </script>
        @endif

        @if($errors->any())
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    @foreach($errors->all() as $err)
                        showToast(@json($err), 'error');
                    @endforeach
                });
            </script>
        @endif

        <form action="{{ route('admin.stock.store') }}" method="POST" id="importForm">
            @csrf

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-building me-1"></i>Nhà cung cấp
                    </label>

                    <div class="supplier-autocomplete">
                        <input type="text"
                               name="supplier"
                               id="supplierInput"
                               value="{{ old('supplier') }}"
                               class="form-control form-control-sm"
                               placeholder="Ví dụ: Cocoon Việt Nam"
                               autocomplete="off"
                               required>

                        <div id="supplierDropdown" class="supplier-dropdown d-none"></div>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-telephone me-1"></i>Số điện thoại
                    </label>

                    <input type="text"
                           name="supplier_phone"
                           id="supplierPhone"
                           value="{{ old('supplier_phone') }}"
                           class="form-control form-control-sm"
                           placeholder="Ví dụ: 0901234567">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-geo-alt me-1"></i>Địa chỉ
                    </label>

                    <input type="text"
                           name="supplier_address"
                           id="supplierAddress"
                           value="{{ old('supplier_address') }}"
                           class="form-control form-control-sm"
                           placeholder="Ví dụ: Ninh Kiều, Cần Thơ">
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-pencil-square me-1"></i>Ghi chú
                    </label>

                    <input type="text"
                           name="note"
                           value="{{ old('note') }}"
                           class="form-control form-control-sm"
                           placeholder="Ghi chú cho lô hàng">
                </div>
            </div>

            <hr class="mb-4">

            <h6 class="fw-bold mb-3">Danh sách sản phẩm nhập</h6>

            <div class="table-responsive stock-import-table-wrap">
                <table class="table table-hover align-middle mb-0" id="importTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:34%">Biến thể</th>
                            <th style="width:120px">SL</th>
                            <th style="width:150px">Giá nhập</th>
                            <th style="width:170px">Ngày sản xuất</th>
                            <th style="width:170px">Hạn sử dụng</th>
                            <th style="width:60px"></th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr class="import-row">
                            <td>
                                <div class="cell-wrapper">
                                    <div class="variant-search-box">
                                        <input type="hidden"
                                               name="variant_id[]"
                                               class="variant-id"
                                               required>

                                        <input type="text"
                                               class="form-control form-control-sm variant-keyword"
                                               placeholder="Tìm theo tên / biến thể / SKU"
                                               autocomplete="off">

                                        <div class="variant-dropdown d-none"></div>
                                    </div>

                                    <div class="selected-variant-info small text-muted mt-2"></div>
                                    <div class="cell-helper"></div>
                                </div>
                            </td>

                            <td>
                                <div class="cell-wrapper">
                                    <input type="number"
                                           name="quantity[]"
                                           class="form-control form-control-sm qty"
                                           min="1"
                                           required>
                                    <div class="cell-helper"></div>
                                </div>
                            </td>

                            <td>
                                <div class="cell-wrapper">
                                    <input type="number"
                                           name="cost_price[]"
                                           class="form-control form-control-sm price"
                                           min="0"
                                           step="0.01"
                                           required>
                                    <div class="cell-helper"></div>
                                </div>
                            </td>

                            <td>
                                <div class="cell-wrapper">
                                    <input type="date"
                                           name="mfg_date[]"
                                           class="form-control form-control-sm mfg">
                                    <div class="cell-helper"></div>
                                </div>
                            </td>

                            <td>
                                <div class="cell-wrapper">
                                    <input type="date"
                                           name="expiry_date[]"
                                           class="form-control form-control-sm exp">
                                    <div class="expiry-warning"></div>
                                </div>
                            </td>

                            <td class="text-center align-middle">
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger removeRow">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-3 mb-3">
                <button type="button"
                        id="addRow"
                        class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>
                    Thêm biến thể
                </button>
            </div>

            <div class="text-end fw-semibold mb-4">
                Tổng tiền nhập:
                <span id="totalCost" class="text-danger">0</span> đ
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-success btn-sm px-4" type="submit">
                    <i class="bi bi-check-circle me-1"></i>
                    Lưu phiếu nhập
                </button>
            </div>
        </form>
    </div>
</div>

@php
    $variantsForJs = $variants->map(function ($v) {
        $stockText = $v->stock_quantity == 0
            ? 'Hết hàng'
            : ($v->stock_quantity <= 5
                ? 'Sắp hết (' . $v->stock_quantity . ')'
                : 'Tồn: ' . $v->stock_quantity);

        $stockClass = $v->stock_quantity == 0
            ? 'out'
            : ($v->stock_quantity <= 5 ? 'low' : 'ok');

        $rawPath = $v->image_path
            ?? $v->image
            ?? $v->product->image_path
            ?? $v->product->image
            ?? '';

        $imageUrl = $rawPath
            ? asset('storage/' . ltrim($rawPath, '/'))
            : '';

        return [
            'id' => $v->id,
            'product_name' => $v->product->name ?? '',
            'attribute_value' => $v->attribute_value ?? '',
            'sku' => $v->sku ?? '',
            'stock_quantity' => (int) $v->stock_quantity,
            'stock_text' => $stockText,
            'stock_class' => $stockClass,
            'label' => trim(($v->product->name ?? '') . ' - ' . ($v->attribute_value ?? '')),
            'image' => $imageUrl,
        ];
    })->values();
@endphp

<script>
    window.variantsData = @json($variantsForJs);
    window.stockImportRoutes = {
        searchSuppliers: @json(route('admin.stock.suppliers.search'))
    };
</script>
@endsection