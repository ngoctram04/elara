@extends('layouts.admin')

@section('title','Nhập kho')

@section('content')

<div class="card border-0 shadow-sm">
<div class="card-body">

{{-- HEADER --}}
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

{{-- SUCCESS --}}
@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    showToast("{{ session('success') }}", "success");
});
</script>
@endif

{{-- WARNING --}}
@if(session('warning'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    @foreach(session('warning') as $w)
        showToast("{{ $w }}", "warning");
    @endforeach
});
</script>
@endif

{{-- ERROR (🔥 chuyển sang toast) --}}
@if($errors->any())
<script>
document.addEventListener('DOMContentLoaded', function () {
    @foreach($errors->all() as $err)
        showToast("{{ $err }}", "error");
    @endforeach
});
</script>
@endif


{{-- FORM --}}
<form action="{{ route('admin.stock.store') }}" method="POST">
@csrf

<div class="row g-3 mb-4">

    <div class="col-md-6">
        <label class="form-label fw-semibold">
            <i class="bi bi-building me-1"></i>Nhà cung cấp
        </label>

        <input type="text"
               name="supplier"
               value="{{ old('supplier') }}"
               class="form-control form-control-sm"
               placeholder="Ví dụ: Cocoon Việt Nam">
    </div>

    <div class="col-md-6">
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

<div class="table-responsive">
<table class="table table-hover align-middle mb-0" id="importTable">

<thead class="table-light">
<tr>
    <th style="width:30%">Biến thể</th>
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
        <select name="variant_id[]"
                class="form-select form-select-sm variant-select"
                required>

            <option value="">-- Chọn biến thể --</option>

            @foreach($variants as $v)
            <option value="{{ $v->id }}"
                    data-stock="{{ $v->stock_quantity }}">

                {{ $v->product->name }} - {{ $v->attribute_value }}

                @if($v->stock_quantity == 0)
                    | 🔴 Hết hàng
                @elseif($v->stock_quantity <=5)
                    | 🟡 Sắp hết ({{ $v->stock_quantity }})
                @else
                    | 🟢 Tồn: {{ $v->stock_quantity }}
                @endif

            </option>
            @endforeach

        </select>

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

{{-- ADD ROW --}}
<div class="mt-3 mb-3">
<button type="button"
        id="addRow"
        class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i>
    Thêm biến thể
</button>
</div>

{{-- TOTAL --}}
<div class="text-end fw-semibold mb-4">
    Tổng tiền nhập:
    <span id="totalCost" class="text-danger">0</span> đ
</div>

{{-- ACTION --}}
<div class="d-flex gap-2">
<button class="btn btn-success btn-sm px-4">
    <i class="bi bi-check-circle me-1"></i>
    Lưu phiếu nhập
</button>
</div>

</form>

</div>
</div>

<style>

.cell-wrapper {
    display: flex;
    flex-direction: column;
    min-height: 52px;
}

.cell-helper {
    height: 16px;
}

.expiry-warning {
    font-size: 11px;
    color: #dc3545;
    height: 16px;
    line-height: 16px;
}

</style>
<script>

// ======================
// FORMAT TIỀN
// ======================
function formatMoney(number){
    return Number(number || 0).toLocaleString('vi-VN');
}


// ======================
// TÍNH TỔNG TIỀN
// ======================
function calculateTotal(){

    let total = 0;

    document.querySelectorAll('#importTable tbody tr').forEach(row=>{

        let qty = parseFloat(row.querySelector('.qty')?.value || 0);
        let price = parseFloat(row.querySelector('.price')?.value || 0);

        total += qty * price;

    });

    document.getElementById('totalCost').innerText = formatMoney(total);
}


// ======================
// CHECK TRÙNG VARIANT
// ======================
function checkDuplicateVariant(){

    let selected = [];

    document.querySelectorAll('.variant-select').forEach(select=>{

        if(select.value){

            if(selected.includes(select.value)){
                select.style.border = "2px solid red";
            }else{
                selected.push(select.value);
                select.style.border = "";
            }

        }

    });

}


// ======================
// CHECK TỒN KHO
// ======================
function checkStock(select){

    let option = select.selectedOptions[0];
    let stock = option?.dataset?.stock ?? 0;

    if(stock == 0){
        select.style.border = "2px solid red";
    }else if(stock <= 5){
        select.style.border = "2px solid orange";
    }else{
        select.style.border = "2px solid green";
    }

}


// ======================
// ⚠️ CHECK HẠN SỬ DỤNG
// ======================
function checkExpiry(input){

    let value = input.value;
    if(!value) return;

    let today = new Date();
    today.setHours(0,0,0,0);

    let expiry = new Date(value);

    let diffTime = expiry - today;
    let diffDays = diffTime / (1000 * 60 * 60 * 24);
    let diffMonths = diffDays / 30;

    let wrapper = input.closest('.cell-wrapper'); // 🔥 FIX
    let warning = wrapper.querySelector('.expiry-warning');
    let row = input.closest('tr');

    // reset
    input.style.border = "";
    warning.innerText = "";
    row.style.background = "";

    if(diffMonths <= 3){
        input.style.border = "2px solid red";
        warning.innerText = "Hạn sử dụng dưới 3 tháng";
        row.style.background = "#fff5f5";
    }
    else if(diffMonths <= 6){
        input.style.border = "2px solid orange";
        warning.innerText = "Hạn sử dụng dưới 6 tháng";
        row.style.background = "#fff8e1";
    }

}


// ======================
// ADD ROW
// ======================
document.getElementById('addRow').addEventListener('click',function(){

    let table = document.querySelector('#importTable tbody');
    let row = table.querySelector('tr').cloneNode(true);

    // reset input
    row.querySelectorAll('input').forEach(input=>{
        input.value = '';
        input.style.border = '';
    });

    // reset select
    row.querySelectorAll('select').forEach(select=>{
        select.selectedIndex = 0;
        select.style.border = '';
    });

    // reset warning
    row.querySelectorAll('.expiry-warning').forEach(el=>{
        el.innerText = '';
    });

    // reset màu dòng
    row.style.background = "";

    table.appendChild(row);

});


// ======================
// INPUT EVENT
// ======================
document.addEventListener('input',function(e){

    if(e.target.classList.contains('qty') ||
       e.target.classList.contains('price')){
        calculateTotal();
    }

});


// ======================
// REMOVE ROW
// ======================
document.addEventListener('click',function(e){

    if(e.target.closest('.removeRow')){

        let rows = document.querySelectorAll('#importTable tbody tr');

        if(rows.length > 1){
            e.target.closest('tr').remove();
            calculateTotal();
            checkDuplicateVariant(); // 🔥 cập nhật lại
        }

    }

});


// ======================
// CHANGE EVENT
// ======================
document.addEventListener('change',function(e){

    if(e.target.classList.contains('variant-select')){
        checkStock(e.target);
        checkDuplicateVariant();
    }

    if(e.target.classList.contains('exp')){
        checkExpiry(e.target);
    }

});


// ======================
// INIT LOAD
// ======================
document.addEventListener('DOMContentLoaded',function(){
    calculateTotal();
});

</script>

@endsection