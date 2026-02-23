@extends('layouts.frontend')
@section('title', 'Giỏ hàng')

@section('content')

<div class="container py-4">

<h4 class="mb-4 fw-bold d-flex justify-content-between align-items-center">
    Giỏ hàng của bạn
</h4>

@if(empty($cart))

<div class="cart-empty text-center py-5">
    <img src="{{ asset('images/no-cart.png') }}" width="120" class="mb-3">
    <h5 class="mb-2">Giỏ hàng trống</h5>
    <a href="{{ route('shop') }}" class="btn btn-primary">
        Mua sắm ngay
    </a>
</div>

@else

<div class="cart-wrapper shadow-sm rounded-3 p-3 bg-white">

<div class="table-responsive">
<table class="table align-middle cart-table mb-0">
<thead>
<tr>
<th>Sản phẩm</th>
<th width="140">Đơn giá</th>
<th width="150">Biến thể</th>
<th width="180" class="text-center">Số lượng</th>
<th width="120">Thành tiền</th>
<th width="50"></th>
</tr>
</thead>

<tbody>
@foreach($cart as $item)
<tr data-row="{{ $item['variant_id'] }}">

<td>
<div class="d-flex gap-3 align-items-center">

<a href="{{ route('products.show', $item['slug']) }}" class="product-link">
<img src="{{ $item['image'] ? asset('storage/'.$item['image']) : asset('images/no-image.png') }}"
     class="cart-img">
</a>

<div>
<a href="{{ route('products.show', $item['slug']) }}"
   class="fw-semibold product-name product-link">
   {{ $item['name'] }}
</a>
<small class="text-muted variant-name">
    {{ $item['variant'] }}
</small>
</div>

</div>
</td>

<td class="price">{{ number_format($item['price']) }}đ</td>

<td>
<select class="form-select form-select-sm js-change-variant"
        data-old="{{ $item['variant_id'] }}">
@foreach($item['variants'] as $variant)
<option value="{{ $variant->id }}"
        data-stock="{{ $variant->stock_quantity }}"
        data-price="{{ $variant->price }}"
        @selected($variant->id == $item['variant_id'])
        @disabled($variant->stock_quantity == 0)
        class="{{ $variant->stock_quantity == 0 ? 'text-muted' : '' }}">
        
    {{ $variant->attribute_value }}
    @if($variant->stock_quantity == 0)
        (Hết hàng)
    @else
        (còn {{ $variant->stock_quantity }})
    @endif

</option>
@endforeach
</select>
</td>

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
data-id="{{ $item['variant_id'] }}">+</button>
</div>

<div class="stock-text js-stock-text">
@if($item['stock'] <= 5)
<span class="badge bg-danger">Sắp hết ({{ $item['stock'] }})</span>
@else
Còn {{ $item['stock'] }}
@endif
</div>
</td>

<td class="subtotal">
<span class="js-subtotal"
data-id="{{ $item['variant_id'] }}"
data-value="{{ $item['sub_total'] }}">
{{ number_format($item['sub_total']) }}đ
</span>
</td>

<td class="text-end">
<button class="btn-remove js-remove"
data-id="{{ $item['variant_id'] }}">
<i class="bi bi-trash"></i>
</button>
</td>

</tr>
@endforeach
</tbody>
</table>
</div>
</div>

{{-- TOTAL --}}
<div class="cart-total-card shadow-sm rounded-3 mt-4 p-4 bg-white sticky-total">
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

<div>
<a href="{{ route('shop') }}" class="btn btn-outline-secondary mb-2">
← Tiếp tục mua hàng
</a>

<div class="text-muted small">
Tổng sản phẩm:
<strong class="js-count">
{{ collect($cart)->sum('quantity') }}
</strong>
</div>
</div>

<div class="text-end">
<h4 class="mb-2">
Tổng tiền:
<span class="total-price js-total"
data-value="{{ $total }}">
{{ number_format($total) }}đ
</span>
</h4>

<a href="{{ route('checkout.index') }}"
class="btn btn-success btn-lg px-5 fw-bold">
Thanh toán
</a>
</div>

</div>
</div>

@endif


{{-- ================= GỢI Ý SẢN PHẨM ================= --}}
@if(!empty($suggestProducts) && count($suggestProducts))
<div class="mt-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Có thể bạn cũng thích</h5>
        <a href="{{ route('shop') }}" class="text-decoration-none small">
            Xem thêm →
        </a>
    </div>

    <div class="row g-3">
@foreach($suggestProducts as $product)
    <div class="col-6 col-md-3">
        @include('frontend.partials.product-card-category', [
            'product' => $product
        ])
    </div>
@endforeach
</div>

</div>
@endif

</div>

<style>
/* ===== ẢNH VUÔNG ===== */
.cart-img{
    width:120px;
    height:120px;
    min-width:120px;      /* giữ kích thước cố định */
    aspect-ratio:1/1;     /* luôn vuông */
    object-fit:cover;     /* cắt ảnh cho đầy khung */
    object-position:center;
    border-radius:12px;
    border:1px solid #eee;
    background:#f8f9fa;
    display:block;
    flex-shrink:0;        /* không bị co khi flex */
    transition:0.2s;
}

.cart-img:hover{
    transform:scale(1.05);
}
select option:disabled{
    color:#999;
}
.product-link{text-decoration:none;color:inherit;}
.price{color:#dc3545;font-weight:600;}

.qty-box{
    display:inline-flex;
    border:1px solid #ddd;
    border-radius:8px;
    overflow:hidden;
}

.qty-btn{
    width:36px;
    height:36px;
    border:none;
    background:#f5f5f5;
    font-size:18px;
}

/* Ô số lượng */
.qty-input{
    width:60px;
    border:none;
    text-align:center;
    font-weight:600;
    outline:none;
}

/* Ẩn mũi tên */
.qty-input::-webkit-outer-spin-button,
.qty-input::-webkit-inner-spin-button{
    -webkit-appearance:none;
    margin:0;
}
.qty-input[type=number]{
    -moz-appearance:textfield;
}

.subtotal{font-weight:600;color:#dc3545;}
.total-price{color:#0d6efd;font-size:26px;font-weight:700;}
.sticky-total{position:sticky;bottom:10px;}

.suggest-img{height:180px;object-fit:cover;}
</style>

@endsection

@push('scripts')
<script>
const money = n => new Intl.NumberFormat('vi-VN').format(n) + 'đ';

/* ================= TOTAL ================= */
function recalcTotal(){
    let total = 0, count = 0;

    document.querySelectorAll('.js-subtotal').forEach(el=>{
        total += Number(el.dataset.value || 0);
    });

    document.querySelectorAll('.js-qty').forEach(el=>{
        count += Number(el.value || 0);
    });

    const totalEl = document.querySelector('.js-total');
    if(totalEl) totalEl.innerText = money(total);

    const countEl = document.querySelector('.js-count');
    if(countEl) countEl.innerText = count;
}

/* ================= UPDATE QTY SERVER ================= */
function updateQty(id, qty){
    fetch("{{ route('cart.changeQty') }}",{
        method:'POST',
        headers:{
            'X-CSRF-TOKEN':'{{ csrf_token() }}',
            'Content-Type':'application/json'
        },
        body:JSON.stringify({
            variant_id:id,
            quantity:qty
        })
    });
}

/* ================= PLUS / MINUS ================= */
document.addEventListener('click', e=>{

    const plus = e.target.closest('.js-plus');
    const minus = e.target.closest('.js-minus');

    if(plus || minus){
        const btn = plus || minus;
        const id = btn.dataset.id;
        const row = document.querySelector(`tr[data-row="${id}"]`);
        if(!row) return;

        const input = row.querySelector('.js-qty');
        let qty = parseInt(input.value) || 1;
        const stock = parseInt(input.dataset.stock);
        const price = parseInt(input.dataset.price);

        if(btn.classList.contains('js-plus')){
            if(qty >= stock){
                alert('Chỉ còn ' + stock + ' sản phẩm');
                return;
            }
            qty++;
        }else{
            if(qty <= 1) return;
            qty--;
        }

        input.value = qty;
        updateQty(id, qty);

        const sub = row.querySelector('.js-subtotal');
        sub.dataset.value = price * qty;
        sub.innerText = money(price * qty);

        recalcTotal();
    }

    /* ================= REMOVE ================= */
    const remove = e.target.closest('.js-remove');
    if(remove){
        const id = remove.dataset.id;
        if(!confirm('Xóa sản phẩm?')) return;

        fetch(`/cart/remove/${id}`,{
            method:'DELETE',
            headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}
        }).then(()=>{
            const row = document.querySelector(`tr[data-row="${id}"]`);
            if(row) row.remove();
            recalcTotal();
        });
    }
});

/* ================= NHẬP TAY ================= */
document.addEventListener('change', e=>{
    if(e.target.classList.contains('js-qty')){
        const input = e.target;
        const id = input.dataset.id;
        const row = input.closest('tr');

        let qty = input.value.trim();
        const stock = parseInt(input.dataset.stock);
        const price = parseInt(input.dataset.price);

        if(qty === '') qty = 1;
        qty = parseInt(qty);

        if(isNaN(qty) || qty < 1) qty = 1;

        if(qty > stock){
            qty = stock;
            alert('Chỉ còn ' + stock + ' sản phẩm');
        }

        input.value = qty;
        updateQty(id, qty);

        const sub = row.querySelector('.js-subtotal');
        sub.dataset.value = price * qty;
        sub.innerText = money(price * qty);

        recalcTotal();
    }
});

/* ================= ĐỔI BIẾN THỂ (FULL UPDATE A+++++) ================= */
document.querySelectorAll('.js-change-variant').forEach(select=>{
    select.onchange = ()=>{

        const row = select.closest('tr');
        const oldId = select.dataset.old;
        const newId = select.value;

        if(oldId == newId) return;

        /* ===== CHẶN HẾT HÀNG ===== */
        const option = select.options[select.selectedIndex];
        const stockCheck = parseInt(option.dataset.stock);
        if(stockCheck <= 0){
            alert('Biến thể này đã hết hàng');
            select.value = oldId;
            return;
        }

        row.style.opacity = 0.5;

        fetch("{{ route('cart.changeVariant') }}",{
            method:'POST',
            headers:{
                'X-CSRF-TOKEN':'{{ csrf_token() }}',
                'Content-Type':'application/json'
            },
            body:JSON.stringify({
                old_variant_id:oldId,
                new_variant_id:newId
            })
        })
        .then(res=>res.json())
        .then(res=>{
            row.style.opacity = 1;

            if(!res.success){
                alert('Không thể đổi biến thể');
                select.value = oldId;
                return;
            }

            const price = parseInt(res.price);
            const stock = parseInt(res.stock);
            const qty   = parseInt(res.quantity) || 1;

            /* ===== UPDATE ID ===== */
            row.dataset.row = newId;
            select.dataset.old = newId;

            const input = row.querySelector('.js-qty');
            input.value = qty;
            input.dataset.id = newId;
            input.dataset.price = price;
            input.dataset.stock = stock;

            row.querySelector('.js-plus').dataset.id = newId;
            row.querySelector('.js-minus').dataset.id = newId;
            row.querySelector('.js-remove').dataset.id = newId;

            /* ===== UPDATE GIÁ ===== */
            const priceCol = row.querySelector('.price');
            if(priceCol) priceCol.innerText = money(price);

            /* ===== UPDATE SUBTOTAL ===== */
            const sub = row.querySelector('.js-subtotal');
            sub.dataset.id = newId;
            sub.dataset.value = price * qty;
            sub.innerText = money(price * qty);

            /* ===== UPDATE TÊN BIẾN THỂ ===== */
            const variantName = row.querySelector('.variant-name');
            if(variantName){
                variantName.innerText = res.variant;
            }

            /* ===== UPDATE TỒN KHO TEXT ===== */
            const stockText = row.querySelector('.js-stock-text');
            if(stockText){
                if(stock <= 5){
                    stockText.innerHTML =
                        `<span class="badge bg-danger">Sắp hết (${stock})</span>`;
                }else{
                    stockText.innerHTML = `Còn ${stock}`;
                }
            }

            /* ===== UPDATE ẢNH ===== */
            const img = row.querySelector('.cart-img');
            if(img && res.image){
                img.src = '/storage/' + res.image;
            }

            recalcTotal();
        });
    };
});
</script>
@endpush