@extends('layouts.frontend')

@section('title', 'Giỏ hàng')

@section('content')
<div class="container py-4">

    <h2 class="mb-4">Giỏ hàng của bạn</h2>

    @if (empty($cart))
        <div class="alert alert-info">
            Giỏ hàng của bạn đang trống.
            <a href="{{ route('home') }}" class="alert-link">Tiếp tục mua sắm</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Giá</th>
                        <th width="140">Số lượng</th>
                        <th>Tạm tính</th>
                        <th width="80"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cart as $item)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img
                                        src="{{ $item['image']
                                            ? asset('storage/' . $item['image'])
                                            : asset('images/no-image.png') }}"
                                        width="70"
                                        class="border rounded"
                                    >
                                    <div>
                                        <strong>{{ $item['name'] }}</strong>
                                        <div class="text-muted small">
                                            {{ $item['variant'] }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                @if($item['is_on_sale'] && $item['original'])
                                    <div class="text-muted text-decoration-line-through small">
                                        {{ number_format($item['original']) }} đ
                                    </div>
                                @endif
                                <strong>{{ number_format($item['price']) }} đ</strong>
                            </td>

                            <td>
                                <form action="{{ route('cart.update') }}" method="POST" class="d-flex">
                                    @csrf
                                    <input type="hidden" name="variant_id" value="{{ $item['variant_id'] }}">
                                    <input
                                        type="number"
                                        name="qty"
                                        value="{{ $item['quantity'] }}"
                                        min="1"
                                        class="form-control form-control-sm"
                                    >
                                    <button class="btn btn-sm btn-outline-primary ms-2">
                                        Cập nhật
                                    </button>
                                </form>
                            </td>

                            <td>
                                <strong>{{ number_format($item['sub_total']) }} đ</strong>
                            </td>

                            <td>
                                <form action="{{ route('cart.remove', $item['variant_id']) }}"
                                      method="POST"
                                      onsubmit="return confirm('Xóa sản phẩm này khỏi giỏ hàng?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">✕</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row justify-content-end mt-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3">Tổng cộng</h5>

                        <div class="d-flex justify-content-between mb-3">
                            <span>Tổng tiền:</span>
                            <strong>{{ number_format($total) }} đ</strong>
                        </div>

                        <a href="{{ route('shop') }}"
                           class="btn btn-outline-secondary w-100 mb-2">
                            ← Tiếp tục mua sắm
                        </a>

                        <a href="{{ route('checkout.index') }}"
                           class="btn btn-primary w-100">
                            Tiến hành thanh toán
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
