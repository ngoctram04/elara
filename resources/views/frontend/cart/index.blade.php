@extends('layouts.frontend')

@section('title', 'Giỏ hàng')

@section('content')
<div class="container py-4">

    <h4 class="mb-4">Giỏ hàng của bạn</h4>

    {{-- EMPTY CART --}}
    <div id="cart-empty"
         class="alert alert-info {{ empty($cart) ? '' : 'd-none' }}">
        Giỏ hàng của bạn đang trống.
        <a href="{{ route('shop') }}" class="alert-link">Tiếp tục mua sắm</a>
    </div>

    {{-- CART WRAPPER --}}
    <div id="cart-wrapper" class="{{ empty($cart) ? 'd-none' : '' }}">

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Đơn giá</th>
                        <th style="width:220px">Biến thể</th>
                        <th class="text-center" style="width:180px">Số lượng</th>
                        <th>Thành tiền</th>
                        <th style="width:60px"></th>
                    </tr>
                </thead>

                <tbody>
                @foreach ($cart as $item)
                    <tr data-row="{{ $item['variant_id'] }}">
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $item['image']
                                        ? asset('storage/'.$item['image'])
                                        : asset('images/no-image.png') }}"
                                     width="64"
                                     class="border rounded-2">
                                <div>
                                    <div class="fw-semibold">{{ $item['name'] }}</div>
                                    <small class="text-muted">{{ $item['variant'] }}</small>
                                </div>
                            </div>
                        </td>

                        <td>
                            @if($item['is_on_sale'] && $item['original'])
                                <div class="text-muted text-decoration-line-through small">
                                    {{ number_format($item['original']) }}đ
                                </div>
                            @endif
                            <div class="text-danger fw-semibold">
                                {{ number_format($item['price']) }}đ
                            </div>
                        </td>

                        <td>
                            <select class="form-select form-select-sm js-change-variant"
                                    data-id="{{ $item['variant_id'] }}">
                                @foreach(
                                    \App\Models\ProductVariant::where('product_id', $item['product_id'])->get()
                                    as $variant
                                )
                                    <option value="{{ $variant->id }}"
                                        @selected($variant->id == $item['variant_id'])>
                                        {{ $variant->displayName() }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        {{-- QTY --}}
                        <td class="text-center">
                            <div class="qty-box">
                                <button type="button"
                                        class="qty-btn js-minus"
                                        data-id="{{ $item['variant_id'] }}">−</button>

                                <input type="number"
                                       class="qty-input js-qty-input"
                                       value="{{ $item['quantity'] }}"
                                       min="1"
                                       data-id="{{ $item['variant_id'] }}"
                                       data-price="{{ $item['price'] }}"
                                       data-stock="{{ $item['stock'] }}">

                                <button type="button"
                                        class="qty-btn js-plus"
                                        data-id="{{ $item['variant_id'] }}"
                                        @disabled($item['quantity'] >= $item['stock'])>+</button>
                            </div>

                            <div class="small text-muted mt-1 stock-text"
                                 data-id="{{ $item['variant_id'] }}">
                                Còn {{ $item['stock'] - $item['quantity'] }} sản phẩm
                            </div>
                        </td>

                        {{-- SUBTOTAL --}}
                        <td class="fw-semibold text-danger">
                            <span class="js-subtotal"
                                  data-id="{{ $item['variant_id'] }}"
                                  data-value="{{ $item['sub_total'] }}">
                                {{ number_format($item['sub_total']) }}đ
                            </span>
                        </td>

                        {{-- REMOVE --}}
                        <td class="text-end">
                            <form action="{{ route('cart.remove', $item['variant_id']) }}"
                                  method="POST"
                                  onsubmit="return confirm('Xóa sản phẩm này khỏi giỏ hàng?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- TOTAL --}}
        <div class="d-flex justify-content-end mt-4">
            <div class="text-end">
                <h5>
                    Tổng cộng:
                    <span class="text-primary fw-bold js-total"
                          data-value="{{ $total }}">
                        {{ number_format($total) }}đ
                    </span>
                </h5>

                <a href="{{ route('checkout.index') }}"
                   class="btn btn-success mt-2 px-4">
                    <i class="bi bi-credit-card"></i>
                    Tiến hành đặt hàng
                </a>
            </div>
        </div>

    </div>
</div>

<style>
.qty-box{display:inline-flex;align-items:center;border:1px solid #ddd;border-radius:6px;overflow:hidden}
.qty-btn{width:32px;height:32px;border:none;background:#f8f9fa;font-weight:bold;cursor:pointer}
.qty-btn:disabled{opacity:.4;cursor:not-allowed}
.qty-input{width:50px;height:32px;border:none;text-align:center;outline:none}
.qty-input::-webkit-outer-spin-button,
.qty-input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
.qty-input{-moz-appearance:textfield}
</style>
@endsection

@push('scripts')
<script>
const formatPrice = n => new Intl.NumberFormat('vi-VN').format(n) + 'đ';

// ===== HELPERS =====
const recalcTotal = () => {
    let total = 0;
    document.querySelectorAll('.js-subtotal').forEach(el => {
        total += Number(el.dataset.value);
    });
    const totalEl = document.querySelector('.js-total');
    totalEl.dataset.value = total;
    totalEl.innerText = formatPrice(total);
};

const checkEmptyCart = () => {
    if (document.querySelectorAll('tbody tr').length === 0) {
        document.getElementById('cart-wrapper').classList.add('d-none');
        document.getElementById('cart-empty').classList.remove('d-none');
    }
};

// ===== SYNC =====
const syncQty = (id, qty) => {
    return fetch('{{ route('cart.changeQty') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ variant_id: id, quantity: qty })
    });
};

// ===== PLUS =====
document.querySelectorAll('.js-plus').forEach(btn => {
    btn.onclick = () => {
        const id = btn.dataset.id;
        const input = document.querySelector(`.js-qty-input[data-id="${id}"]`);
        const stock = Number(input.dataset.stock);
        let qty = Number(input.value || 0);

        if (qty >= stock) {
            alert('Vượt quá tồn kho');
            return;
        }

        qty++;
        input.value = qty;

        syncQty(id, qty).then(() => {
            const price = Number(input.dataset.price);
            const sub = document.querySelector(`.js-subtotal[data-id="${id}"]`);
            sub.dataset.value = price * qty;
            sub.innerText = formatPrice(price * qty);
            recalcTotal();
        });
    };
});

// ===== MINUS (HỎI XÓA KHI = 1) =====
document.querySelectorAll('.js-minus').forEach(btn => {
    btn.onclick = () => {
        const id = btn.dataset.id;
        const row = document.querySelector(`tr[data-row="${id}"]`);
        const input = row.querySelector('.js-qty-input');
        let qty = Number(input.value || 0);

        if (qty === 1) {
            if (!confirm('Bạn có muốn xóa sản phẩm này khỏi giỏ hàng không?')) return;

            fetch(`{{ route('cart.remove', ':id') }}`.replace(':id', id), {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: new URLSearchParams({ _method: 'DELETE' })
            }).then(() => {
                row.remove();
                recalcTotal();
                checkEmptyCart();
            });
            return;
        }

        qty--;
        input.value = qty;

        syncQty(id, qty).then(() => {
            const price = Number(input.dataset.price);
            const sub = document.querySelector(`.js-subtotal[data-id="${id}"]`);
            sub.dataset.value = price * qty;
            sub.innerText = formatPrice(price * qty);
            recalcTotal();
        });
    };
});

// ===== INPUT HANDLING (FIX GIẬT 1 → 12) =====
const debounce = {};

document.querySelectorAll('.js-qty-input').forEach(input => {

    // 👉 Khi đang gõ: CHO PHÉP RỖNG, KHÔNG ÉP 1
    input.addEventListener('input', () => {
        const id = input.dataset.id;
        clearTimeout(debounce[id]);

        debounce[id] = setTimeout(() => {

            // nếu đang rỗng thì KHÔNG làm gì
            if (input.value === '') return;

            let qty = Number(input.value);
            const stock = Number(input.dataset.stock);

            if (qty > stock) {
                qty = stock;
                alert('Vượt quá tồn kho');
            }

            input.value = qty;

            syncQty(id, qty).then(() => {
                const price = Number(input.dataset.price);
                const sub = document.querySelector(`.js-subtotal[data-id="${id}"]`);
                sub.dataset.value = price * qty;
                sub.innerText = formatPrice(price * qty);
                recalcTotal();
            });

        }, 300);
    });

    // 👉 Khi blur (bấm ra ngoài): nếu rỗng → ép về 1
    input.addEventListener('blur', () => {
        const id = input.dataset.id;

        if (input.value === '') {
            input.value = 1;

            syncQty(id, 1).then(() => {
                const price = Number(input.dataset.price);
                const sub = document.querySelector(`.js-subtotal[data-id="${id}"]`);
                sub.dataset.value = price * 1;
                sub.innerText = formatPrice(price * 1);
                recalcTotal();
            });
        }
    });
});
</script>

@endpush
