@extends('layouts.frontend')

@section('title', $product->name)

@section('content')
@php
    $hasVariants = $product->variants->count() > 0;

    $defaultVariant = $hasVariants
        ? $product->variants->where('is_default', 1)->first()
        : null;
@endphp

{{-- ================= STYLE ================= --}}
<style>
.variant-error-show {
    animation: shake 0.35s ease-in-out 2;
}
@keyframes shake {
    0%   { transform: translateX(0); }
    25%  { transform: translateX(-4px); }
    50%  { transform: translateX(4px); }
    75%  { transform: translateX(-4px); }
    100% { transform: translateX(0); }
}
</style>

<div class="container py-4">
    <div class="row g-4">

        {{-- ================= THUMBNAILS ================= --}}
        <div class="col-md-1 d-flex flex-column gap-2">
            @foreach($product->images as $img)
                <img src="{{ asset('storage/' . $img->image_path) }}"
                     class="img-thumbnail thumb-img"
                     style="cursor:pointer">
            @endforeach
        </div>

        {{-- ================= MAIN IMAGE ================= --}}
        <div class="col-md-5 d-flex align-items-center justify-content-center">
            <img id="main-image"
                 src="{{ $defaultVariant?->images->first()
                    ? asset('storage/'.$defaultVariant->images->first()->image_path)
                    : ($product->mainImage
                        ? asset('storage/'.$product->mainImage->image_path)
                        : asset('images/no-image.png')) }}"
                 class="img-fluid border rounded"
                 style="max-height:480px;object-fit:contain;transition:opacity .2s">
        </div>

        {{-- ================= INFO ================= --}}
        <div class="col-md-6">
            <h3 class="fw-bold mb-2">{{ $product->name }}</h3>

            {{-- PRICE --}}
            <div class="mb-3">
                <div id="price-original"
                     class="text-muted text-decoration-line-through"
                     style="display:none"></div>

                <div id="price-final"
                     class="fs-3 fw-bold text-danger"></div>
            </div>

            <p><strong>Danh mục:</strong> {{ $product->category?->name ?? 'Đang cập nhật' }}</p>
            <p><strong>Thương hiệu:</strong> {{ $product->brand?->name ?? 'Đang cập nhật' }}</p>

            {{-- ================= VARIANTS ================= --}}
            @if($hasVariants)
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

                            <button type="button"
                                    class="btn btn-outline-secondary variant-btn"
                                    data-id="{{ $variant->id }}"
                                    data-final="{{ $variant->final_price ?? $variant->price }}"
                                    data-original="{{ $variant->is_on_sale ? $variant->price : '' }}"
                                    data-stock="{{ $variant->availableStock() }}"
                                    data-image="{{ $variantImage
                                        ? asset('storage/'.$variantImage->image_path)
                                        : $fallbackImage }}">
                                @if($variantImage)
                                    <img src="{{ asset('storage/'.$variantImage->image_path) }}"
                                         class="rounded mb-1"
                                         style="width:40px;height:40px;object-fit:cover">
                                @endif
                                <div class="small fw-semibold">
                                    {{ $variant->displayName() }}
                                </div>
                            </button>
                        @endforeach
                    </div>

                    <div id="stock-text" class="text-muted mt-2"></div>

                    <div id="variant-error" class="text-danger small mt-1 d-none">
                        ⚠️ Vui lòng chọn phân loại sản phẩm
                    </div>
                </div>
            @endif

            {{-- ================= ACTION ================= --}}
            <form method="POST"
                  action="{{ route('cart.add') }}"
                  id="add-to-cart-form"
                  class="d-flex gap-2 mt-4">
                @csrf
                <input type="hidden" name="variant_id" id="variant_id">

                <input type="number" name="qty" value="1" min="1"
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

    const mainImg          = document.getElementById('main-image');
    const priceFinalEl    = document.getElementById('price-final');
    const priceOriginalEl = document.getElementById('price-original');
    const stockEl         = document.getElementById('stock-text');
    const variantInput    = document.getElementById('variant_id');
    const errorEl         = document.getElementById('variant-error');
    const form            = document.getElementById('add-to-cart-form');

    // notify backend
    @if(session('success'))
        showCenterNotify(@json(session('success')), 'success');
    @endif

    @if($errors->any())
        showCenterNotify(@json($errors->first()), 'error');
    @endif

    // chọn biến thể
    document.querySelectorAll('.variant-btn').forEach(btn => {
        btn.addEventListener('click', () => {

            document.querySelectorAll('.variant-btn')
                .forEach(b => b.classList.remove('active'));

            btn.classList.add('active');

            const finalPrice    = btn.dataset.final;
            const originalPrice = btn.dataset.original;

            priceFinalEl.innerText =
                new Intl.NumberFormat('vi-VN').format(finalPrice) + 'đ';

            if (originalPrice) {
                priceOriginalEl.innerText =
                    new Intl.NumberFormat('vi-VN').format(originalPrice) + 'đ';
                priceOriginalEl.style.display = 'block';
            } else {
                priceOriginalEl.style.display = 'none';
            }

            stockEl.innerText = 'Còn ' + btn.dataset.stock + ' sản phẩm';

            mainImg.style.opacity = 0;
            setTimeout(() => {
                mainImg.src = btn.dataset.image;
                mainImg.style.opacity = 1;
            }, 120);

            variantInput.value = btn.dataset.id;
            errorEl.classList.add('d-none');
        });
    });

    // auto chọn biến thể đầu
    const firstVariant = document.querySelector('.variant-btn');
    if (firstVariant) {
        firstVariant.click();
    }

    // submit
    form.addEventListener('submit', e => {
        if (!variantInput.value) {
            e.preventDefault();
            showCenterNotify('Vui lòng chọn phân loại sản phẩm', 'error');

            errorEl.classList.remove('d-none');
            errorEl.classList.add('variant-error-show');

            setTimeout(() => {
                errorEl.classList.add('d-none');
                errorEl.classList.remove('variant-error-show');
            }, 3000);
        }
    });
});
</script>
@endpush
