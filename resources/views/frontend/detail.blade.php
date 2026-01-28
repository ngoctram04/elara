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
    0% {transform:translateX(0)}
    25% {transform:translateX(-4px)}
    50% {transform:translateX(4px)}
    75% {transform:translateX(-4px)}
    100% {transform:translateX(0)}
}

/* ===== THUMBNAIL COLUMN ===== */
.thumb-wrapper {
    max-height: 480px;
    overflow-y: auto;
    padding-right: 6px;
}

.thumb-img {
    width: 100%;
    aspect-ratio: 1 / 1;
    object-fit: contain;
    background: #fff;
    cursor: pointer;
    transition: .2s;
}

.thumb-img:hover {
    border: 2px solid #0d6efd;
}

.thumb-img.active {
    border: 2px solid #dc3545;
}
</style>

<div class="container py-4">
    <div class="row g-4">

        {{-- ================= THUMBNAILS ================= --}}
        <div class="col-md-2">
            <div class="d-flex flex-column gap-2 thumb-wrapper">

                {{-- ảnh sản phẩm --}}
                @foreach($product->images as $img)
                    <img src="{{ asset('storage/'.$img->image_path) }}"
                         class="img-thumbnail thumb-img"
                         data-image="{{ asset('storage/'.$img->image_path) }}">
                @endforeach

                {{-- ảnh biến thể --}}
                @foreach($product->variants as $variant)
                    @foreach($variant->images as $vImg)
                        <img src="{{ asset('storage/'.$vImg->image_path) }}"
                             class="img-thumbnail thumb-img"
                             data-image="{{ asset('storage/'.$vImg->image_path) }}"
                             data-variant="{{ $variant->id }}">
                    @endforeach
                @endforeach

            </div>
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
                 style="max-height:480px;object-fit:contain;transition:.2s">
        </div>

        {{-- ================= INFO ================= --}}
        <div class="col-md-5">
            <h3 class="fw-bold mb-2">{{ $product->name }}</h3>

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
                                $fallback = $product->mainImage
                                    ? asset('storage/'.$product->mainImage->image_path)
                                    : asset('images/no-image.png');
                            @endphp

                            <button type="button"
                                    class="btn btn-outline-secondary variant-btn"
                                    data-id="{{ $variant->id }}"
                                    data-final="{{ $variant->final_price ?? $variant->price }}"
                                    data-original="{{ $variant->is_on_sale ? $variant->price : '' }}"
                                    data-stock="{{ $variant->availableStock() }}"
                                    data-image="{{ $variantImage ? asset('storage/'.$variantImage->image_path) : $fallback }}">
                                @if($variantImage)
                                    <img src="{{ asset('storage/'.$variantImage->image_path) }}"
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
                        Vui lòng chọn phân loại sản phẩm
                    </div>
                </div>
            @endif

            {{-- ================= ACTION ================= --}}
            <form method="POST"
                  action="{{ route('cart.add') }}"
                  id="add-to-cart-form"
                  class="d-flex gap-2">
                @csrf
                <input type="hidden" name="variant_id" id="variant_id">

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

    const mainImg  = document.getElementById('main-image');
    const qtyInput = document.querySelector('input[name="qty"]');
    const addBtn   = document.querySelector('#add-to-cart-form button');

    // click thumbnail
    document.querySelectorAll('.thumb-img').forEach(img => {
        img.addEventListener('click', () => {

            document.querySelectorAll('.thumb-img')
                .forEach(i => i.classList.remove('active'));
            img.classList.add('active');

            mainImg.style.opacity = 0;
            setTimeout(() => {
                mainImg.src = img.dataset.image;
                mainImg.style.opacity = 1;
            }, 120);

            if (img.dataset.variant) {
                document.querySelector(
                    `.variant-btn[data-id="${img.dataset.variant}"]`
                )?.click();
            }
        });
    });

    // ===== VARIANT LOGIC (ĐÃ FIX QTY) =====
    document.querySelectorAll('.variant-btn').forEach(btn => {
        btn.addEventListener('click', () => {

            document.querySelectorAll('.variant-btn')
                .forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // price
            document.getElementById('price-final').innerText =
                new Intl.NumberFormat('vi-VN').format(btn.dataset.final) + 'đ';

            if (btn.dataset.original) {
                document.getElementById('price-original').style.display = 'block';
                document.getElementById('price-original').innerText =
                    new Intl.NumberFormat('vi-VN').format(btn.dataset.original) + 'đ';
            } else {
                document.getElementById('price-original').style.display = 'none';
            }

            // stock
            const stock = parseInt(btn.dataset.stock);
            document.getElementById('stock-text').innerText =
                'Còn ' + stock + ' sản phẩm';

            // 🔥 FIX QTY
            qtyInput.max = stock;

            if (parseInt(qtyInput.value) > stock) {
                qtyInput.value = stock;
            }

            if (stock <= 0) {
                qtyInput.value = 0;
                addBtn.disabled = true;
                addBtn.innerHTML = '<i class="bi bi-x-circle"></i> Hết hàng';
            } else {
                addBtn.disabled = false;
                addBtn.innerHTML = '<i class="bi bi-cart-plus"></i> Thêm vào giỏ';
                if (qtyInput.value == 0) qtyInput.value = 1;
            }

            // image + variant
            mainImg.src = btn.dataset.image;
            document.getElementById('variant_id').value = btn.dataset.id;
        });
    });

    // auto chọn variant đầu
    document.querySelector('.variant-btn')?.click();
});
</script>
@endpush
