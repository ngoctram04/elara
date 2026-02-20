@extends('layouts.frontend')
@section('title', 'Giỏ hàng')

@section('content')
<div class="container py-4">

<h4 class="mb-4">Giỏ hàng của bạn</h4>

@if(empty($cart))
<div class="alert alert-info">
    Giỏ hàng trống.
    <a href="{{ route('shop') }}">Mua sắm ngay</a>
</div>
@else

<div class="table-responsive">
<table class="table align-middle">
<thead class="table-light">
<tr>
<th>Sản phẩm</th>
<th>Đơn giá</th>
<th width="220">Biến thể</th>
<th width="180" class="text-center">Số lượng</th>
<th>Thành tiền</th>
<th width="60"></th>
</tr>
</thead>

<tbody>
@foreach($cart as $item)
<tr data-row="{{ $item['variant_id'] }}">

<td>
<div class="d-flex gap-3 align-items-center">
<img src="{{ $item['image'] ? asset('storage/'.$item['image']) : asset('images/no-image.png') }}"
     width="64" class="border rounded">
<div>
<div class="fw-semibold">{{ $item['name'] }}</div>
<small class="text-muted">{{ $item['variant'] }}</small>
</div>
</div>
</td>

<td class="text-danger fw-semibold">
{{ number_format($item['price']) }}đ
</td>

{{-- VARIANT SELECT --}}
<td>
<select class="form-select form-select-sm js-change-variant"
        data-old="{{ $item['variant_id'] }}">

@foreach($item['variants'] as $variant)
<option value="{{ $variant->id }}"
        data-stock="{{ $variant->stock_quantity }}"
        @selected($variant->id == $item['variant_id'])>
    {{ $variant->attribute_value }} ({{ $variant->stock_quantity }})
</option>
@endforeach

</select>
</td>

{{-- QTY --}}
<td class="text-center">
<div class="qty-box">
<button class="qty-btn js-minus" data-id="{{ $item['variant_id'] }}">−</button>

<input type="number"
       class="qty-input js-qty"
       value="{{ $item['quantity'] }}"
       min="1"
       data-id="{{ $item['variant_id'] }}"
       data-price="{{ $item['price'] }}"
       data-stock="{{ $item['stock'] }}">

<button class="qty-btn js-plus"
        data-id="{{ $item['variant_id'] }}"
        @disabled($item['quantity'] >= $item['stock'])>+</button>
</div>

<div class="small text-muted">
Còn {{ $item['stock'] }}
</div>
</td>

<td class="fw-semibold text-danger">
<span class="js-subtotal"
      data-id="{{ $item['variant_id'] }}"
      data-value="{{ $item['sub_total'] }}">
{{ number_format($item['sub_total']) }}đ
</span>
</td>

<td class="text-end">
<button class="btn btn-outline-danger btn-sm js-remove"
        data-id="{{ $item['variant_id'] }}">
<i class="bi bi-trash"></i>
</button>
</td>

</tr>
@endforeach
</tbody>
</table>
</div>

<div class="text-end mt-4">
<h5>
Tổng:
<span class="text-primary js-total"
      data-value="{{ $total }}">
{{ number_format($total) }}đ
</span>
</h5>

<a href="{{ route('checkout.index') }}" class="btn btn-success">
Thanh toán
</a>
</div>

@endif
</div>

<style>
.qty-box{display:inline-flex;border:1px solid #ddd;border-radius:6px;overflow:hidden}
.qty-btn{width:32px;height:32px;border:none;background:#f8f9fa}
.qty-input{width:50px;text-align:center;border:none}
</style>
@endsection
@push('scripts')
<script>
const money = n => new Intl.NumberFormat('vi-VN').format(n)+'đ';

const recalcTotal = () => {
    let total = 0;
    document.querySelectorAll('.js-subtotal').forEach(el=>{
        total += Number(el.dataset.value);
    });
    const t = document.querySelector('.js-total');
    t.dataset.value = total;
    t.innerText = money(total);
};

const updateQty = (id, qty) => {
    fetch("{{ route('cart.changeQty') }}",{
        method:'POST',
        headers:{
            'X-CSRF-TOKEN':'{{ csrf_token() }}',
            'Content-Type':'application/json'
        },
        body:JSON.stringify({variant_id:id, quantity:qty})
    });
};

document.addEventListener('click', e=>{

// PLUS
if(e.target.closest('.js-plus')){
    const id = e.target.closest('.js-plus').dataset.id;
    const input = document.querySelector(`.js-qty[data-id="${id}"]`);
    let qty = +input.value;
    const stock = +input.dataset.stock;
    if(qty>=stock) return alert('Hết hàng');

    qty++;
    input.value = qty;
    updateQty(id, qty);

    const price = +input.dataset.price;
    const sub = document.querySelector(`.js-subtotal[data-id="${id}"]`);
    sub.dataset.value = price*qty;
    sub.innerText = money(price*qty);
    recalcTotal();
}

// MINUS
if(e.target.closest('.js-minus')){
    const id = e.target.closest('.js-minus').dataset.id;
    const input = document.querySelector(`.js-qty[data-id="${id}"]`);
    let qty = +input.value;

    if(qty<=1){
        if(!confirm('Xóa sản phẩm?')) return;
        removeItem(id);
        return;
    }

    qty--;
    input.value = qty;
    updateQty(id, qty);

    const price = +input.dataset.price;
    const sub = document.querySelector(`.js-subtotal[data-id="${id}"]`);
    sub.dataset.value = price*qty;
    sub.innerText = money(price*qty);
    recalcTotal();
}

// REMOVE
if(e.target.closest('.js-remove')){
    const id = e.target.closest('.js-remove').dataset.id;
    if(confirm('Xóa sản phẩm?')) removeItem(id);
}

});

// REMOVE AJAX
function removeItem(id){
    fetch(`/cart/remove/${id}`,{
        method:'DELETE',
        headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}
    }).then(()=>{
        document.querySelector(`tr[data-row="${id}"]`)?.remove();
        recalcTotal();
    });
}

// CHANGE VARIANT
document.querySelectorAll('.js-change-variant').forEach(select=>{
    select.onchange = () => {
        const oldId = select.dataset.old;
        const newId = select.value;

        fetch("{{ route('cart.changeVariant') }}",{
            method:'POST',
            headers:{
                'X-CSRF-TOKEN':'{{ csrf_token() }}',
                'Content-Type':'application/json'
            },
            body:JSON.stringify({
                old_variant_id: oldId,
                new_variant_id: newId
            })
        }).then(()=> location.reload());
    };
});
</script>
@endpush