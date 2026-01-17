@extends('layouts.frontend')

@section('title', $product->name)

@section('content')
@php
    // 🔥 BIẾN THỂ MẶC ĐỊNH
    $defaultVariant = $product->variants
        ->where('is_default', 1)
        ->first()
        ?? $product->variants->first();
@endphp

<div class="container py-4">
    <div class="row g-4">

        {{-- ================= THUMBNAILS ================= --}}
        <div class="col-md-1 d-flex flex-column gap-2">
            @foreach($product->images as $img)
                <img
                    src="{{ asset('storage/' . $img->image_path) }}"
                    class="img-thumbnail thumb-img"
                    style="cursor:pointer"
                >
            @endforeach
        </div>

        {{-- ================= MAIN IMAGE ================= --}}
        <div class="col-md-5 d-flex align-items-center justify-content-center">
            <img
                id="main-image"
                src="{{ $defaultVariant?->images->first()
                        ? asset('storage/'.$defaultVariant->images->first()->image_path)
                        : ($product->mainImage
                            ? asset('storage/'.$product->mainImage->image_path)
                            : asset('images/no-image.png')) }}"
                class="img-fluid border rounded"
                style="max-height:480px;object-fit:contain;transition:opacity .2s"
            >
        </div>

        {{-- ================= INFO ================= --}}
        <div class="col-md-6">

            <h3 class="fw-bold mb-2">{{ $product->name }}</h3>

            {{-- PRICE (LẤY GIÁ BIẾN THỂ MẶC ĐỊNH) --}}
            <div class="mb-3">
                <span id="price" class="fs-3 fw-bold text-danger">
                    {{ number_format($defaultVariant?->final_price ?? 0, 0, ',', '.') }}đ
                </span>
            </div>

            <p class="mb-1">
                <strong>Danh mục:</strong>
                {{ $product->category?->name ?? 'Đang cập nhật' }}
            </p>

            <p class="mb-3">
                <strong>Thương hiệu:</strong>
                {{ $product->brand?->name ?? 'Đang cập nhật' }}
            </p>

            {{-- ================= VARIANTS ================= --}}
            @if($product->variants->count())
                <div class="mb-4">
                    <strong>Chọn phân loại:</strong>

                    <div class="d-flex flex-wrap gap-2 mt-2">
                        @foreach($product->variants as $variant)
                            @php
                                $variantImage = $variant->images->first();
                                $fallbackImage = $product->mainImage
                                    ? asset('storage/'.$product->mainImage->image_path)
                                    : asset('images/no-image.png');
                            @endphp

                            <button
                                type="button"
                                class="btn btn-outline-secondary variant-btn text-center
                                    {{ $defaultVariant && $variant->id === $defaultVariant->id ? 'active' : '' }}"
                                data-id="{{ $variant->id }}"
                                data-price="{{ $variant->final_price }}"
                                data-stock="{{ $variant->availableStock() }}"
                                data-image="{{ $variantImage
                                    ? asset('storage/'.$variantImage->image_path)
                                    : $fallbackImage }}"
                            >
                                @if($variantImage)
                                    <img
                                        src="{{ asset('storage/'.$variantImage->image_path) }}"
                                        class="rounded mb-1"
                                        style="width:40px;height:40px;object-fit:cover"
                                    >
                                @endif

                                <div class="small fw-semibold">
                                    {{ $variant->displayName() }}
                                </div>
                            </button>
                        @endforeach
                    </div>

                    <div id="stock-text" class="text-muted mt-2">
                        @if($defaultVariant)
                            Còn {{ $defaultVariant->availableStock() }} sản phẩm
                        @endif
                    </div>
                </div>
            @endif

            {{-- ================= ACTION ================= --}}
            <form method="POST"
                  action="{{ route('cart.add') }}"
                  class="d-flex gap-2 mt-4">
                @csrf
                <input type="hidden"
                       name="variant_id"
                       id="variant_id"
                       value="{{ $defaultVariant?->id }}">

                <input type="number"
                       name="qty"
                       value="1"
                       min="1"
                       class="form-control w-auto">

                <button class="btn btn-primary">
                    <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                </button>
            </form>

        </div>
    </div>

    {{-- ================= DESCRIPTION ================= --}}
    <div class="mt-5">
        <h5 class="fw-bold mb-3">Mô tả sản phẩm</h5>
        <div class="border rounded p-4 bg-white">
            {!! nl2br(e($product->description)) !!}
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    const mainImg      = document.getElementById('main-image');
    const priceEl      = document.getElementById('price');
    const stockEl      = document.getElementById('stock-text');
    const variantInput = document.getElementById('variant_id');

    document.querySelectorAll('.variant-btn').forEach(btn => {
        btn.addEventListener('click', () => {

            document.querySelectorAll('.variant-btn')
                .forEach(b => b.classList.remove('active'));

            btn.classList.add('active');

            const price = btn.dataset.price;
            const stock = btn.dataset.stock;
            const img   = btn.dataset.image;
            const id    = btn.dataset.id;

            priceEl.innerText =
                new Intl.NumberFormat('vi-VN').format(price) + 'đ';

            stockEl.innerText = 'Còn ' + stock + ' sản phẩm';

            if (img) {
                mainImg.style.opacity = 0;
                setTimeout(() => {
                    mainImg.src = img;
                    mainImg.style.opacity = 1;
                }, 120);
            }

            variantInput.value = id;
        });
    });

    // click thumbnail
    document.querySelectorAll('.thumb-img').forEach(img => {
        img.addEventListener('click', () => {
            mainImg.src = img.src;
        });
    });

});
</script>
@endpush
