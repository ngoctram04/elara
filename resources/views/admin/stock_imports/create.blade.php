@extends('layouts.admin')

@section('title','Nhập kho')

@section('content')

<div class="card border-0 shadow-sm">

<div class="card-body">

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h5 class="fw-bold mb-1">
Nhập hàng vào kho
</h5>

<small class="text-muted">
Tạo phiếu nhập nhiều biến thể sản phẩm
</small>
</div>

<a href="{{ route('admin.stock.history') }}"
class="btn btn-outline-secondary btn-sm">

<i class="bi bi-clock-history me-1"></i>
Lịch sử nhập

</a>

</div>


@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
{{ session('success') }}
<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif


{{-- FORM --}}
<form action="{{ route('admin.stock.store') }}" method="POST">
@csrf

<div class="row g-3 mb-4">

<div class="col-md-6">

<label class="form-label fw-semibold">
<i class="bi bi-building me-1"></i>
Nhà cung cấp
</label>

<input type="text"
name="supplier"
class="form-control form-control-sm"
placeholder="Ví dụ: Cocoon Việt Nam">

</div>

<div class="col-md-6">

<label class="form-label fw-semibold">
<i class="bi bi-pencil-square me-1"></i>
Ghi chú
</label>

<input type="text"
name="note"
class="form-control form-control-sm"
placeholder="Ghi chú cho lô hàng">

</div>

</div>

<hr class="mb-4">

<h6 class="fw-bold mb-3">
Danh sách sản phẩm nhập
</h6>


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

<tr>

<td>

<select name="variant_id[]"
class="form-select form-select-sm variant-select"
required>

<option value="">
-- Chọn biến thể --
</option>

@foreach($variants as $v)

<option value="{{ $v->id }}"
data-stock="{{ $v->stock_quantity }}">

{{ $v->product->name }} -
{{ $v->attribute_value }}

@if($v->stock_quantity == 0)
| 🔴 Hết hàng
@elseif($v->stock_quantity <=5)
| 🟡 Sắp hết ({{ $v->stock_quantity }})
@else
| tồn: {{ $v->stock_quantity }}
@endif

</option>

@endforeach

</select>

</td>


<td>

<input type="number"
name="quantity[]"
class="form-control form-control-sm qty"
min="1"
required>

</td>


<td>

<input type="number"
name="cost_price[]"
class="form-control form-control-sm price"
min="0"
required>

</td>


<td>

<input type="date"
name="mfg_date[]"
class="form-control form-control-sm">

</td>


<td>

<input type="date"
name="expiry_date[]"
class="form-control form-control-sm">

</td>


<td class="text-center">

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

<span id="totalCost" class="text-danger">
0
</span> đ

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

<script>

function calculateTotal(){

let total = 0;

document.querySelectorAll('#importTable tbody tr').forEach(row=>{

let qty = row.querySelector('.qty').value;
let price = row.querySelector('.price').value;

if(qty && price){
total += qty * price;
}

});

document.getElementById('totalCost').innerText =
total.toLocaleString();

}


document.getElementById('addRow').addEventListener('click',function(){

let table = document.querySelector('#importTable tbody');
let row = table.querySelector('tr').cloneNode(true);

row.querySelectorAll('input').forEach(input=>{
input.value='';
});

row.querySelectorAll('select').forEach(select=>{
select.selectedIndex = 0;
select.style.border = '';
});

table.appendChild(row);

});


document.addEventListener('input',function(e){

if(e.target.classList.contains('qty') ||
e.target.classList.contains('price')){
calculateTotal();
}

});


document.addEventListener('click',function(e){

if(e.target.closest('.removeRow')){

let rows = document.querySelectorAll('#importTable tbody tr');

if(rows.length>1){
e.target.closest('tr').remove();
calculateTotal();
}

}

});


document.addEventListener('change',function(e){

if(e.target.classList.contains('variant-select')){

let option = e.target.selectedOptions[0];
let stock = option.dataset.stock;

if(stock == 0){
e.target.style.border = "2px solid red";
}else if(stock <=5){
e.target.style.border = "2px solid orange";
}else{
e.target.style.border = "";
}

}

});

</script>

@endsection