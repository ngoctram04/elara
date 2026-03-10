@extends('layouts.admin')

@section('title','Nhập kho')

@section('content')

<div class="container-fluid">

@if(session('success'))

<div class="alert alert-success alert-dismissible fade show">
{{ session('success') }}
<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card shadow border-0">

<div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">

<div>
<h5 class="mb-0">
<i class="bi bi-box-arrow-in-down me-2"></i>
Nhập hàng vào kho
</h5>
<small>Tạo phiếu nhập nhiều biến thể</small>
</div>

<a href="{{ route('admin.stock.history') }}" class="btn btn-light btn-sm">
<i class="bi bi-clock-history"></i> Lịch sử nhập
</a>

</div>

<div class="card-body p-4">

<form action="{{ route('admin.stock.store') }}" method="POST">
@csrf

<div class="row g-4 mb-4">

<div class="col-md-6">
<label class="form-label fw-semibold">
<i class="bi bi-building"></i> Nhà cung cấp
</label>

<input type="text"
name="supplier"
class="form-control"
placeholder="Ví dụ: Cocoon Việt Nam">

</div>

<div class="col-md-6">
<label class="form-label fw-semibold">
<i class="bi bi-pencil-square"></i> Ghi chú
</label>

<input type="text"
name="note"
class="form-control"
placeholder="Ghi chú cho lô hàng">

</div>

</div>

<hr class="mb-4">

<h6 class="fw-bold mb-3">
<i class="bi bi-box-seam"></i>
Danh sách sản phẩm nhập
</h6>

<div class="table-responsive">

<table class="table table-bordered align-middle" id="importTable">

<thead class="table-light">

<tr>
<th width="30%">Biến thể</th>
<th width="120">SL</th>
<th width="150">Giá nhập</th>
<th width="170">Ngày sản xuất</th>
<th width="170">Hạn sử dụng</th>
<th width="60"></th>
</tr>

</thead>

<tbody>

<tr>

<td>

<select name="variant_id[]" class="form-select variant-select" required>

<option value="">-- Chọn biến thể --</option>

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
class="form-control qty"
min="1"
required>
</td>

<td>
<input type="number"
name="cost_price[]"
class="form-control price"
min="0"
required>
</td>

<td>
<input type="date"
name="mfg_date[]"
class="form-control">
</td>

<td>
<input type="date"
name="expiry_date[]"
class="form-control">
</td>

<td class="text-center">
<button type="button"
class="btn btn-danger btn-sm removeRow">
<i class="bi bi-x-lg"></i>
</button>
</td>

</tr>

</tbody>

</table>

</div>

<div class="mb-3">

<button type="button"
id="addRow"
class="btn btn-primary btn-sm">

<i class="bi bi-plus-lg"></i>
Thêm biến thể

</button>

</div>

<div class="text-end fw-bold mb-4">

Tổng tiền nhập: <span id="totalCost">0</span> đ

</div>

<div class="d-flex gap-3">

<button class="btn btn-success px-4">
<i class="bi bi-check-circle"></i>
Lưu phiếu nhập
</button>

<a href="{{ route('admin.stock.history') }}"
class="btn btn-outline-secondary"> <i class="bi bi-clock-history"></i>
Xem lịch sử </a>

</div>

</form>

</div>

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
