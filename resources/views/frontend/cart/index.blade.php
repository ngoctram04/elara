@extends('layouts.frontend')

@section('title', 'Giỏ hàng')

@section('content')
<div class="container py-4">

    <h4 class="mb-4">Giỏ hàng của bạn</h4>

    @if (empty($cart))
        <div class="alert alert-info">
            Giỏ hàng của bạn đang trống.
            <a href="{{ route('shop') }}" class="alert-link">Tiếp tục mua sắm</a>
        </div>
    @else

    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>Sản phẩm</th>
                    <th>Đơn giá</th>
                    <th style="width:220px">Biến thể</th>
                    <th class="text-center" style="width:140px">Số lượng</th>
                    <th>Thành tiền</th>
                    <th style="width:60px"></th>
                </tr>
            </thead>

            <tbody>
            @foreach ($cart as $item)
                <tr>
                    {{-- SẢN PHẨM --}}
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <img
                                src="{{ $item['image']
                                    ? asset('storage/'.$item['image'])
                                    : asset('images/no-image.png') }}"
                                width="60"
                                class="border rounded"
                            >
                            <div>
                                <div class="fw-semibold">{{ $item['name'] }}</div>
                                <small class="text-muted">
                                    {{ $item['variant'] }}
                                </small>
                            </div>
                        </div>
                    </td>

                    {{-- ĐƠN GIÁ --}}
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

                    {{-- BIẾN THỂ --}}
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

                    {{-- SỐ LƯỢNG --}}
                    <td class="text-center">
                        <div class="d-flex justify-content-center align-items-center gap-1">
                            <button type="button"
                                    class="btn btn-outline-secondary btn-sm js-qty"
                                    data-type="minus"
                                    data-id="{{ $item['variant_id'] }}">−</button>

                            <input type="text"
                                   class="form-control form-control-sm text-center"
                                   value="{{ $item['quantity'] }}"
                                   style="width:45px"
                                   readonly>

                            <button type="button"
                                    class="btn btn-outline-secondary btn-sm js-qty"
                                    data-type="plus"
                                    data-id="{{ $item['variant_id'] }}">+</button>
                        </div>
                    </td>

                    {{-- THÀNH TIỀN --}}
                    <td class="fw-semibold text-danger">
                        {{ number_format($item['sub_total']) }}đ
                    </td>

                    {{-- XOÁ --}}
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

    {{-- TỔNG CỘNG --}}
    <div class="d-flex justify-content-end mt-3">
        <div class="text-end">
            <h5>
                Tổng cộng:
                <span class="text-primary fw-bold">
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

    @endif
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.js-qty').forEach(btn => {
    btn.addEventListener('click', () => {
        fetch('{{ route('cart.changeQty') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                variant_id: btn.dataset.id,
                type: btn.dataset.type
            })
        }).then(() => location.reload());
    });
});

document.querySelectorAll('.js-change-variant').forEach(select => {
    select.addEventListener('change', () => {
        fetch('{{ route('cart.changeVariant') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                old_variant_id: select.dataset.id,
                new_variant_id: select.value
            })
        }).then(() => location.reload());
    });
});
</script>
@endpush
