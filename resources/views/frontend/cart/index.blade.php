@extends('layouts.frontend')
@section('title', 'Giỏ hàng')

@section('content')

<div class="container py-4">

<h4 class="mb-4 fw-bold d-flex justify-content-between align-items-center">
    Giỏ hàng của bạn
</h4>

@if(empty($cart))

<div class="cart-empty text-center py-5">
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
<th width="40">
    <input type="checkbox" id="check-all">
</th>
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
    <input type="checkbox"
       class="js-check-item"
       value="{{ $item['variant_id'] }}">
</td>

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

<td class="price">
@if(isset($item['original_price']) && $item['original_price'] > $item['price'])
    <div class="text-muted text-decoration-line-through small">
        {{ number_format($item['original_price']) }}đ
    </div>
@endif

<div class="text-danger fw-semibold">
    {{ number_format($item['price']) }}đ
</div>
</td>
<td>
<select class="form-select form-select-sm js-change-variant"
        data-old="{{ $item['variant_id'] }}">
@foreach($item['variants'] as $variant)
<option value="{{ $variant->id }}"
        data-stock="{{ $variant->stock_quantity }}"
        data-price="{{ $variant->final_price ?? $variant->price }}"
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
<!-- Confirm Delete -->
<div id="confirm-delete-box" class="confirm-box d-none">
    <div class="confirm-content">
        <div class="mb-3 fw-semibold">
            Bạn có muốn xóa sản phẩm này?
        </div>
        <div class="d-flex justify-content-center gap-2">
            <button class="btn btn-danger btn-sm" id="confirm-delete-yes">Xóa</button>
            <button class="btn btn-secondary btn-sm" id="confirm-delete-no">Hủy</button>
        </div>
    </div>
</div>
{{-- TOTAL --}}
<div class="cart-total-card shadow-sm rounded-3 mt-4 p-4 bg-white sticky-total">
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

    {{-- LEFT --}}
    <div class="d-flex align-items-center gap-3 flex-wrap">

        <a href="{{ route('shop') }}" class="btn btn-outline-secondary">
            ← Tiếp tục mua hàng
        </a>

        <div class="text-muted small">
            Tổng sản phẩm:
            <strong class="js-count">
                {{ collect($cart)->sum('quantity') }}
            </strong>
        </div>

    </div>

    {{-- RIGHT (Voucher + Total + Checkout) --}}
    <div class="d-flex align-items-center gap-4 flex-wrap">

        {{-- VOUCHER (nằm bên trái nút Thanh toán) --}}
        <div class="text-end">
            <button class="btn btn-outline-primary btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#voucherModal">
                Chọn mã
            </button>

            <div class="small text-success mt-1 d-none" id="voucher-applied"></div>
        </div>

        {{-- TOTAL --}}
        <div class="text-end" style="min-width:220px">

    {{-- ================= SUMMARY ================= --}}
<div class="summary-box">

    {{-- Tổng tiền hàng --}}
    <div class="d-flex justify-content-between small">
        <span>Tổng tiền hàng</span>
        <span id="summary-subtotal" data-value="0">0đ</span>
    </div>

    {{-- Giảm giá (voucher) --}}
    <div id="summary-voucher-row"
         class="d-flex justify-content-between small text-success d-none">
        <span>Giảm giá voucher</span>
        <span id="summary-voucher" data-value="0">-0đ</span>
    </div>

    {{-- Giảm giá sinh nhật --}}
    <div id="summary-birthday-row"
         class="d-flex justify-content-between small text-danger d-none">
        <span>Ưu đãi sinh nhật</span>
        <span id="summary-birthday" data-percent="{{ $birthdayPercent ?? 0 }}">-0đ</span>
    </div>

    {{-- Tổng tiết kiệm --}}
    <div id="summary-saving-row"
         class="d-flex justify-content-between small text-danger d-none">
        <span>Tiết kiệm</span>
        <span id="summary-saving">-0đ</span>
    </div>

    <hr class="my-2">

    {{-- Tổng thanh toán --}}
    <div class="d-flex justify-content-between fw-bold">
        <span>Tổng thanh toán</span>
        <span class="total-price js-total" data-value="0">0đ</span>
    </div>

</div>

</div>
        {{-- CHECKOUT --}}
        <form id="checkout-form"
              action="{{ route('checkout.fromCart') }}"
              method="POST"
              class="d-flex align-items-center">
            @csrf
            <div id="selected-items"></div>
            <input type="hidden" name="promotion_code" id="promotion-code-hidden">

            <button class="btn btn-success btn-lg px-5 fw-bold">
                Thanh toán
            </button>
        </form>

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
.total-price{
    color:#0d6efd;
    font-size:26px;
    font-weight:700;
    white-space:nowrap;
}

#voucher-applied{
    max-width: 280px;          /* chỉnh theo layout của bạn */
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;     /* tối đa 2 dòng */
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.voucher-mini{
    display:flex;
    align-items:center;
    gap:6px;
    padding:8px 14px;
    border:1px solid #dee2e6;
    border-radius:8px;
    background:#f8f9fa;
    cursor:pointer;
    font-size:14px;
    transition:0.2s;
}

.voucher-mini:hover{
    border-color:#0d6efd;
    background:#eef5ff;
    color:#0d6efd;
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
.confirm-box{
    position: fixed;
    top:0;
    left:0;
    right:0;
    bottom:0;
    background: rgba(0,0,0,0.4);
    display:flex;
    align-items:center;
    justify-content:center;
    z-index:9999;
}

.confirm-content{
    background:#fff;
    padding:20px 25px;
    border-radius:10px;
    box-shadow:0 10px 25px rgba(0,0,0,0.2);
    text-align:center;
    min-width:260px;
}

.d-none{
    display:none !important;
}
.voucher-item:hover{
    border-color:#0d6efd;
    background:#f8fbff;
}
.voucher-item.border-primary{
    border-width:2px !important;
}
.best-badge{
    font-size:11px;
    margin-top:4px;
}
</style>
{{-- ================= MODAL VOUCHER ================= --}}
<div class="modal fade" id="voucherModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">

            <div class="modal-header">
                <h5 class="modal-title fw-bold">Chọn voucher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                @if(!empty($availablePromotions) && count($availablePromotions))
                    @foreach($availablePromotions as $promo)

                        <div class="voucher-item border rounded-3 p-3 mb-3"
                             data-code="{{ $promo->code }}"
                             data-type="{{ $promo->discount_type }}"
                             data-value="{{ $promo->discount_value }}"
                             data-min="{{ $promo->min_order_value ?? 0 }}"
                             data-max="{{ $promo->max_discount ?? 0 }}"
                             style="cursor:pointer; transition:0.2s;">

                            <div class="d-flex justify-content-between align-items-start">

                                <div>
                                    <div class="fw-semibold text-danger">
                                        {{ $promo->name }}
                                    </div>

                                    <div class="small text-muted">
                                        Mã: {{ $promo->code }}
                                    </div>
                                    @if($promo->end_date)
    @php
        $daysLeft = now()->diffInDays($promo->end_date, false);
    @endphp

    <div class="small {{ $daysLeft <= 3 ? 'text-danger fw-semibold' : 'text-muted' }}">
        Hết hạn: {{ $promo->end_date->format('d/m/Y') }}
    </div>
@endif
                                    @if(!empty($promo->min_order_value))
                                        <div class="small text-muted">
                                            Đơn tối thiểu: {{ number_format($promo->min_order_value) }}đ
                                        </div>
                                    @endif
                                </div>

                                {{-- Preview giảm bao nhiêu --}}
                                <div class="text-end small fw-semibold discount-preview text-success">
                                </div>

                            </div>

                        </div>

                    @endforeach
                @else
                    <div class="text-muted text-center py-3">
                        Không có voucher khả dụng
                    </div>
                @endif

            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const money = n => new Intl.NumberFormat('vi-VN').format(n) + 'đ';
function updateCartBadge(){

    let total = 0;

    document.querySelectorAll('.js-qty').forEach(input=>{
        total += parseInt(input.value) || 0;
    });

    const badge = document.querySelector('.cart-badge');

    if(!badge) return;

    if(total <= 0){
        badge.style.display = 'none';
        return;
    }

    badge.style.display = 'inline-block';
    badge.innerText = total > 99 ? '99+' : total;

}
function recalcTotal(){

    let subtotal = 0;
    let count = 0;

    const voucherRow = document.getElementById('summary-voucher-row');
    const voucherEl = document.getElementById('summary-voucher');
    const bdRow = document.getElementById('summary-birthday-row');
    const bdEl = document.getElementById('summary-birthday');

    document.querySelectorAll('.js-check-item:checked').forEach(cb=>{
        const id = cb.value;
        const row = document.querySelector(`tr[data-row="${id}"]`);
        if(!row) return;

        const sub = row.querySelector('.js-subtotal');
        const qty = row.querySelector('.js-qty');

        subtotal += Number(sub?.dataset.value || 0);
        count += Number(qty?.value || 0);
    });

    // Nếu không còn item nào được chọn -> bỏ voucher
    if(subtotal === 0){
        appliedCode = null;

        if(voucherRow) voucherRow.classList.add('d-none');
        if(voucherEl) voucherEl.dataset.value = 0;
    }

    // ===== Cập nhật số lượng =====
    const countEl = document.querySelector('.js-count');
    if(countEl) countEl.innerText = count;

    // ===== SUMMARY =====
    const subEl = document.getElementById('summary-subtotal');

    if(subEl){
        subEl.innerText = money(subtotal);
        subEl.dataset.value = subtotal;
    }

    // ===== Voucher =====
    let voucher = appliedCode ? Number(voucherEl?.dataset.value || 0) : 0;

    if(voucher > 0 && subtotal > 0){
        voucherRow?.classList.remove('d-none');
    }else{
        voucherRow?.classList.add('d-none');
        voucher = 0;
    }

    // ===== Birthday =====
    let percent = Number(bdEl?.dataset.percent || 0);
    let birthday = Math.round(subtotal * percent / 100);

    if(birthday > 0 && subtotal > 0){
        bdRow?.classList.remove('d-none');
        bdEl.innerText = '-' + money(birthday);
    }else{
        bdRow?.classList.add('d-none');
        birthday = 0;
    }

    // ===== Saving =====
    const savingRow = document.getElementById('summary-saving-row');
    const savingEl = document.getElementById('summary-saving');

    const saving = voucher + birthday;

    if(saving > 0){
        savingRow?.classList.remove('d-none');
        savingEl.innerText = '-' + money(saving);
    }else{
        savingRow?.classList.add('d-none');
    }

    // ===== FINAL TOTAL =====
    const finalTotal = Math.max(0, subtotal - saving);

    const totalEl = document.querySelector('.js-total');
    if(totalEl){
        totalEl.innerText = money(finalTotal);
        totalEl.dataset.value = subtotal;
    }
}

/* ================= CHECK ALL ================= */
document.getElementById('check-all')?.addEventListener('change', function(){
    document.querySelectorAll('.js-check-item').forEach(cb=>{
        cb.checked = this.checked;
    });
    recalcTotal();
});
/* ================= CHECK ITEM ================= */
document.addEventListener('change', function(e){
    if(e.target.classList.contains('js-check-item')){

        const allItems = document.querySelectorAll('.js-check-item');
        const checkedItems = document.querySelectorAll('.js-check-item:checked');
        const checkAll = document.getElementById('check-all');

        // Đồng bộ checkbox trên cùng
        if(checkAll){
            checkAll.checked = allItems.length === checkedItems.length;
        }

        recalcTotal();
    }
});
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

        /* ===== PLUS ===== */
        if(btn.classList.contains('js-plus')){
            if(qty >= stock){
                showToast('Chỉ còn ' + stock + ' sản phẩm', 'error');
                return;
            }
            qty++;
        }

        /* ===== MINUS ===== */
        else{
            if(qty <= 1){
                // Hiện confirm custom
                removeId = id;
                document.getElementById('confirm-delete-box')
                    .classList.remove('d-none');
                return;
            }
            qty--;
        }

        /* ===== UPDATE UI ===== */
        input.value = qty;
        updateQty(id, qty);

        const sub = row.querySelector('.js-subtotal');
        sub.dataset.value = price * qty;
        sub.innerText = money(price * qty);

        recalcTotal();
updateCartBadge();
    }

});
/* ================= REMOVE (Confirm Box chuẩn) ================= */
let removeId = null;

// Click icon thùng rác
document.addEventListener('click', function(e){

    const removeBtn = e.target.closest('.js-remove');
    if(removeBtn){
        removeId = removeBtn.dataset.id;
        document.getElementById('confirm-delete-box')
            .classList.remove('d-none');
        return;
    }

    // Hủy
    if(e.target.id === 'confirm-delete-no'){
        document.getElementById('confirm-delete-box')
            .classList.add('d-none');
        removeId = null;
        return;
    }

    // Đồng ý xóa
    if(e.target.id === 'confirm-delete-yes' && removeId){

        const id = removeId;

        document.getElementById('confirm-delete-box')
            .classList.add('d-none');

        showToast('Đang xóa sản phẩm...', 'info');

        fetch(`/cart/remove/${id}`, {
    method: 'DELETE',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
    }
})
.then(res => {
    if (!res.ok) throw new Error('Delete failed');
    return res.json().catch(() => ({}));
})
.then(() => {
    const row = document.querySelector(`tr[data-row="${id}"]`);
    if (row) row.remove();
updateCartBadge();
    showToast('Đã xóa sản phẩm', 'success');

    // ===== KIỂM TRA GIỎ CÒN KHÔNG =====
    const remainingRows = document.querySelectorAll('tbody tr');

    if (remainingRows.length === 0) {
        // Reload để Blade hiển thị "Giỏ hàng trống"
        setTimeout(() => {
            location.reload();
        }, 500);
        return;
    }

    recalcTotal();
})
.catch(() => {
    showToast('Không thể xóa sản phẩm', 'error');
});

        removeId = null;
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
            showToast('Chỉ còn ' + stock + ' sản phẩm', 'error');
        }

        input.value = qty;
        updateQty(id, qty);

        const sub = row.querySelector('.js-subtotal');
        sub.dataset.value = price * qty;
        sub.innerText = money(price * qty);

        recalcTotal();
updateCartBadge();
    }
});

/* ================= ĐỔI BIẾN THỂ (FULL – GỘP REALTIME) ================= */
document.querySelectorAll('.js-change-variant').forEach(select=>{
    select.onchange = ()=>{

        const row = select.closest('tr');
        const oldId = select.dataset.old;
        const newId = select.value;

        if(oldId == newId) return;

        // ===== CHẶN HẾT HÀNG =====
        const option = select.options[select.selectedIndex];
        const stockCheck = parseInt(option.dataset.stock);
        if(stockCheck <= 0){
            showToast('Biến thể này đã hết hàng', 'error');
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
                old_variant_id: oldId,
                new_variant_id: newId
            })
        })
        .then(res=>res.json())
        .then(res=>{
            row.style.opacity = 1;

            if(!res.success){
                showToast(res.message || 'Không thể đổi biến thể', 'error');
                select.value = oldId;
                return;
            }

            const price = parseInt(res.price);
            const stock = parseInt(res.stock);
            const qty   = parseInt(res.quantity);
            const newIdServer = res.new_id;

            /* =====================================================
               QUAN TRỌNG: NẾU VARIANT MỚI ĐÃ CÓ TRÊN BẢNG → GỘP
            ====================================================== */
            const existingRow = document.querySelector(
                `tr[data-row="${newIdServer}"]`
            );

            if(existingRow && existingRow !== row){

    const existingInput = existingRow.querySelector('.js-qty');
    existingInput.value = qty;
    existingInput.dataset.stock = stock;
    existingInput.dataset.price = price;

    const sub = existingRow.querySelector('.js-subtotal');
    sub.dataset.value = price * qty;
    sub.innerText = money(price * qty);

    row.remove();
    recalcTotal();
    return;
}

            /* ================= UPDATE DÒNG HIỆN TẠI ================= */

            // Update ID chuẩn theo server
            row.dataset.row = newIdServer;
            select.dataset.old = newIdServer;
            const checkbox = row.querySelector('.js-check-item');
if(checkbox){
    checkbox.value = newIdServer;
}

            const input = row.querySelector('.js-qty');
            input.value = qty;
            input.dataset.id = newIdServer;
            input.dataset.price = price;
            input.dataset.stock = stock;

            row.querySelector('.js-plus').dataset.id = newIdServer;
            row.querySelector('.js-minus').dataset.id = newIdServer;
            row.querySelector('.js-remove').dataset.id = newIdServer;

            // Giá
            const priceCol = row.querySelector('.price');
if(priceCol){

    if(res.original_price && res.original_price > price){
        priceCol.innerHTML = `
            <div class="text-muted text-decoration-line-through small">
                ${money(res.original_price)}
            </div>
            <div class="text-danger fw-semibold">
                ${money(price)}
            </div>
        `;
    }else{
        priceCol.innerHTML = `
            <div class="text-danger fw-semibold">
                ${money(price)}
            </div>
        `;
    }
}
            // Subtotal
            const sub = row.querySelector('.js-subtotal');
            sub.dataset.id = newIdServer;
            sub.dataset.value = price * qty;
            sub.innerText = money(price * qty);

            // Tên biến thể
            const variantName = row.querySelector('.variant-name');
            if(variantName){
                variantName.innerText = res.variant;
            }

            // Stock text
            const stockText = row.querySelector('.js-stock-text');
            if(stockText){
                if(stock <= 5){
                    stockText.innerHTML =
                        `<span class="badge bg-danger">Sắp hết (${stock})</span>`;
                }else{
                    stockText.innerHTML = `Còn ${stock}`;
                }
            }

            // Ảnh
            const img = row.querySelector('.cart-img');
            if(img && res.image){
                img.src = '/storage/' + res.image;
            }

            recalcTotal();
updateCartBadge();
        });
    };
});
/* ================= SUBMIT CHECKED ITEMS ================= */
document.getElementById('checkout-form')?.addEventListener('submit', function(e){

    const container = document.getElementById('selected-items');
    container.innerHTML = '';

    const checked = document.querySelectorAll('.js-check-item:checked');

    if(checked.length === 0){
        e.preventDefault();
        showToast('Vui lòng chọn sản phẩm để thanh toán', 'error');
        return;
    }

    checked.forEach(cb=>{
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'variant_ids[]';
        input.value = cb.value;
        container.appendChild(input);
    });
});
/* ================= INIT ================= */
document.addEventListener('DOMContentLoaded', function(){

    // Mặc định không chọn gì
    document.querySelectorAll('.js-check-item').forEach(cb=>{
        cb.checked = false;
    });

    const checkAll = document.getElementById('check-all');
    if(checkAll) checkAll.checked = false;

    recalcTotal();
    updateCartBadge();
});
/* ================= VOUCHER ================= */

let appliedCode = null;
let originalTotal = null;

function applyVoucher(code){

    const totalEl = document.querySelector('.js-total');
    if(!totalEl){
        showToast('Không tìm thấy tổng tiền', 'error');
        return;
    }

    const total = Number(totalEl.dataset.value || 0);

    if(total <= 0){
        showToast('Vui lòng chọn sản phẩm trước khi áp dụng voucher', 'error');
        return;
    }

    fetch("{{ route('cart.applyPromotion') }}",{
        method:'POST',
        headers:{
            'X-CSRF-TOKEN':'{{ csrf_token() }}',
            'Content-Type':'application/json'
        },
        body:JSON.stringify({
            code: code,
            total: total
        })
    })
    .then(res => res.json())
    .then(res => {

    if(!res.success){

        // reset voucher nếu server báo lỗi
        appliedCode = null;

        const voucherRow = document.getElementById('summary-voucher-row');
        const voucherEl = document.getElementById('summary-voucher');

        if(voucherRow) voucherRow.classList.add('d-none');
        if(voucherEl) voucherEl.dataset.value = 0;

        recalcTotal();

        showToast(res.message || 'Mã không hợp lệ', 'error');
        return;
    }

        appliedCode = code;

        // Hiển thị tổng sau giảm (UI)
        totalEl.innerText = money(res.final_total);

        // ===== QUAN TRỌNG: LƯU VOUCHER CHO recalcTotal =====
        const voucherEl = document.getElementById('summary-voucher');
        const voucherRow = document.getElementById('summary-voucher-row');

        if(voucherEl){
            voucherEl.dataset.value = res.discount;
            voucherEl.innerText = '-' + money(res.discount);
        }

        if(voucherRow){
            voucherRow.classList.remove('d-none');
        }

        // Hiển thị mã
        const voucherText = document.getElementById('voucher-text');
        if(voucherText){
            voucherText.innerText = code;
        }

        // Box thông báo
        const box = document.getElementById('voucher-applied');
        if(box){
            box.innerText = `Đã áp dụng: ${res.name} (-${money(res.discount)})`;
            box.classList.remove('d-none');
        }

        // Lưu vào form checkout
        const hidden = document.getElementById('promotion-code-hidden');
        if(hidden){
            hidden.value = code;
        }

        // Đóng modal
        const modalEl = document.getElementById('voucherModal');
        if(modalEl){
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal?.hide();
        }

        // Tính lại tổng chuẩn (voucher + birthday)
        recalcTotal();
    })
    .catch(()=>{
        showToast('Có lỗi xảy ra, vui lòng thử lại', 'error');
    });
}
/* ===== CLICK VOUCHER (ổn định cho modal) ===== */
document.addEventListener('click', function(e){
    const item = e.target.closest('.voucher-item');
    if(item){
        const code = item.dataset.code;
        applyVoucher(code);
    }
});
document.getElementById('btn-apply-voucher')?.addEventListener('click', ()=>{
    const code = document.getElementById('voucher-code').value.trim();

    if(!code){
        showToast('Vui lòng nhập mã khuyến mãi', 'error');
        return;
    }

    applyVoucher(code);
});
// ===== TỰ RELOAD NẾU CART BỊ THAY ĐỔI TỪ NƠI KHÁC =====
window.addEventListener('cartUpdated', function () {
    location.reload();
});
/* ================= VOUCHER SORT THEO GIẢM NHIỀU NHẤT ================= */

function getCheckedSubtotal(){
    let subtotal = 0;

    document.querySelectorAll('.js-check-item:checked').forEach(cb=>{
        const row = document.querySelector(`tr[data-row="${cb.value}"]`);
        const sub = row?.querySelector('.js-subtotal');
        subtotal += Number(sub?.dataset.value || 0);
    });

    return subtotal;
}

function calculateDiscount(voucher, subtotal){

    const type = voucher.dataset.type;
    const value = Number(voucher.dataset.value || 0);
    const min = Number(voucher.dataset.min || 0);
    const max = Number(voucher.dataset.max || 0);

    if(subtotal < min) return 0;

    let discount = 0;

    if(type === 'percent'){
        discount = subtotal * value / 100;
        if(max > 0){
            discount = Math.min(discount, max);
        }
    }else{
        discount = value;
    }

    return Math.round(discount);
}
document.getElementById('voucherModal')
?.addEventListener('show.bs.modal', function(){

    const subtotal = getCheckedSubtotal();

    if(subtotal <= 0){
        showToast('Vui lòng chọn sản phẩm trước', 'error');
        return;
    }

    const container = this.querySelector('.modal-body');
    const vouchers = Array.from(container.querySelectorAll('.voucher-item'));

    // ===== RESET toàn bộ trước =====
    vouchers.forEach(v => {
        v.classList.remove('border-primary');
        v.style.background = '';
        const oldBadge = v.querySelector('.best-badge');
        if(oldBadge) oldBadge.remove();
    });

    // ===== TÍNH GIẢM =====
    vouchers.forEach(voucher => {

        const discount = calculateDiscount(voucher, subtotal);
        voucher.dataset.discount = discount;

        const preview = voucher.querySelector('.discount-preview');

        if(discount > 0){
            preview.innerText = `Giảm ${money(discount)}`;
            voucher.style.opacity = 1;
            voucher.style.pointerEvents = 'auto';
        }else{
            preview.innerText = `Không đủ điều kiện`;
            voucher.style.opacity = 0.5;
            voucher.style.pointerEvents = 'none';
        }
    });

    // ===== SORT =====
    vouchers.sort((a,b)=>{
        return Number(b.dataset.discount) - Number(a.dataset.discount);
    });

    vouchers.forEach(v => container.appendChild(v));

    // ===== GẮN NHÃN CHO VOUCHER TỐT NHẤT =====
    const bestVoucher = vouchers.find(v => Number(v.dataset.discount) > 0);

    if(bestVoucher){
        bestVoucher.classList.add('border-primary');
        bestVoucher.style.background = '#f0f8ff';

        const badge = document.createElement('span');
        badge.className = 'badge bg-primary best-badge ms-2';
        badge.innerText = 'Phù hợp nhất';

        bestVoucher.querySelector('.fw-semibold')
            .appendChild(badge);
    }

});

</script>
@endpush
