@extends('layouts.frontend')

@section('title','Yêu cầu trả hàng / hoàn tiền')

@section('content')

<div class="container py-4">

    <h5 class="fw-bold mb-4">Yêu cầu trả hàng / hoàn tiền</h5>

    <form action="{{ route('refund.store') }}" method="POST" enctype="multipart/form-data" id="refundForm">
        @csrf

        <input type="hidden" name="order_id" value="{{ $order->id }}">

        <div class="mb-3">
            <label class="form-label fw-semibold">Chọn sản phẩm muốn trả</label>
            <div class="text-muted small">
                Vui lòng chọn đúng sản phẩm và khai tình trạng thực tế của từng món.
            </div>
        </div>

        @foreach($order->items as $item)
            @php
                $variant = $item->variant ?? null;
                $product = $variant->product ?? null;

                $image = null;

                if ($variant && $variant->mainImage) {
                    $image = $variant->mainImage->path
                        ?? $variant->mainImage->image_path
                        ?? null;
                }

                if (!$image && $product && $product->mainImage) {
                    $image = $product->mainImage->path
                        ?? $product->mainImage->image_path
                        ?? null;
                }

                $imageUrl = $image
                    ? asset('storage/' . $image)
                    : asset('images/no-image.png');

                $checked = in_array($item->id, old('items', []));
                $oldCondition = old("item_conditions.{$item->id}", 'sealed');
                $oldNote = old("item_notes.{$item->id}");
            @endphp

            <div class="card border-0 shadow-sm mb-3 refund-item-card">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3">
                        <div class="pt-2">
                            <input
                                class="form-check-input item-checkbox"
                                type="checkbox"
                                name="items[]"
                                value="{{ $item->id }}"
                                id="item_{{ $item->id }}"
                                {{ $checked ? 'checked' : '' }}>
                        </div>

                        <img
                            src="{{ $imageUrl }}"
                            width="60"
                            height="60"
                            alt="{{ $product->name ?? 'Sản phẩm' }}"
                            style="object-fit:cover;border-radius:8px;border:1px solid #eee">

                        <div class="flex-grow-1">
                            <label for="item_{{ $item->id }}" class="fw-semibold mb-1 d-block cursor-pointer">
                                {{ $product->name ?? 'Sản phẩm' }}
                            </label>

                            <div class="text-muted small mb-3">
                                BT{{ str_pad($variant->id ?? 0, 5, '0', STR_PAD_LEFT) }}
                                × {{ $item->quantity ?? 1 }}
                            </div>

                            <div class="refund-item-extra {{ $checked ? '' : 'd-none' }}">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-semibold">
                                            Tình trạng sản phẩm
                                        </label>

                                        <select name="item_conditions[{{ $item->id }}]" class="form-select form-select-sm">
                                            <option value="sealed" {{ $oldCondition === 'sealed' ? 'selected' : '' }}>
                                                Còn nguyên seal
                                            </option>
                                            <option value="broken" {{ $oldCondition === 'broken' ? 'selected' : '' }}>
                                                Bị vỡ
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-md-8">
                                        <label class="form-label small fw-semibold">
                                            Mô tả riêng cho sản phẩm này
                                        </label>

                                        <input
                                            type="text"
                                            name="item_notes[{{ $item->id }}]"
                                            class="form-control form-control-sm"
                                            value="{{ $oldNote }}"
                                            placeholder="Ví dụ: còn nguyên seal, hộp còn đẹp / bị vỡ nắp, móp hộp...">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        @error('items')
            <div class="alert alert-danger py-2">{{ $message }}</div>
        @enderror

        <div class="mb-4">
            <label class="form-label fw-semibold">
                Lý do trả hàng
            </label>

            <textarea
                name="reason"
                class="form-control"
                rows="4"
                placeholder="Vui lòng mô tả lý do chung của yêu cầu hoàn tiền..."
                required>{{ old('reason') }}</textarea>

            @error('reason')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold">
                Hình ảnh / Video minh chứng
            </label>

            <div class="d-flex gap-3 mb-2 flex-wrap">
                <label class="upload-box">
                    <i class="bi bi-camera-fill"></i>
                    <span>Thêm hình ảnh</span>

                    <input
                        type="file"
                        name="images[]"
                        id="imageInput"
                        accept="image/*"
                        multiple
                        hidden>
                </label>

                <label class="upload-box">
                    <i class="bi bi-camera-video-fill"></i>
                    <span>Thêm video</span>

                    <input
                        type="file"
                        name="video"
                        id="videoInput"
                        accept="video/*"
                        hidden>
                </label>
            </div>

            <small class="text-muted">
                Tối đa 5 ảnh và 1 video
            </small>

            @error('images.*')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror

            @error('video')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div id="previewArea" class="d-flex gap-2 flex-wrap mb-4"></div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-danger px-4">
                Gửi yêu cầu hoàn tiền
            </button>

            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-secondary">
                Quay lại
            </a>
        </div>
    </form>

</div>

<style>
.upload-box{
    display:flex;
    align-items:center;
    gap:8px;
    border:2px dashed #dc3545;
    color:#dc3545;
    padding:12px 20px;
    border-radius:8px;
    cursor:pointer;
    font-weight:500;
    transition:all .2s ease;
}

.upload-box:hover{
    background:#fff5f5;
}

#previewArea img,
#previewArea video{
    width:100px;
    height:100px;
    object-fit:cover;
    border-radius:6px;
    border:1px solid #ddd;
}

.cursor-pointer{
    cursor:pointer;
}
</style>

<script>
const imageInput = document.getElementById('imageInput');
const videoInput = document.getElementById('videoInput');
const preview = document.getElementById('previewArea');
const refundForm = document.getElementById('refundForm');

function renderPreview() {
    preview.innerHTML = '';

    const imageFiles = Array.from(imageInput.files || []);
    const videoFile = videoInput.files[0];

    imageFiles.forEach(file => {
        const url = URL.createObjectURL(file);
        const div = document.createElement('div');
        div.innerHTML = `<img src="${url}" alt="preview">`;
        preview.appendChild(div);
    });

    if (videoFile) {
        const url = URL.createObjectURL(videoFile);
        const div = document.createElement('div');
        div.innerHTML = `
            <video controls>
                <source src="${url}">
            </video>
        `;
        preview.appendChild(div);
    }
}

document.querySelectorAll('.item-checkbox').forEach(function (checkbox) {
    checkbox.addEventListener('change', function () {
        const card = this.closest('.refund-item-card');
        const extra = card.querySelector('.refund-item-extra');

        if (this.checked) {
            extra.classList.remove('d-none');
        } else {
            extra.classList.add('d-none');
        }
    });
});

imageInput.addEventListener('change', function () {
    const files = Array.from(this.files || []);

    if (files.length > 5) {
        alert('Chỉ được tối đa 5 ảnh');
        this.value = '';
        renderPreview();
        return;
    }

    renderPreview();
});

videoInput.addEventListener('change', function () {
    renderPreview();
});

refundForm.addEventListener('submit', function (e) {
    const checkedItems = document.querySelectorAll('.item-checkbox:checked');

    if (checkedItems.length === 0) {
        e.preventDefault();
        alert('Vui lòng chọn ít nhất 1 sản phẩm cần trả.');
        return;
    }
});
</script>

@endsection