@extends('layouts.frontend')

@section('title','Yêu cầu trả hàng / hoàn tiền')

@section('content')
<div class="container refund-page py-4">

    <div class="refund-header mb-4">
        <h4 class="refund-title mb-1">Yêu cầu trả hàng / hoàn tiền</h4>
        <div class="refund-subtitle">
            Chọn đúng sản phẩm cần hỗ trợ và cung cấp thông tin minh chứng rõ ràng để cửa hàng xử lý nhanh hơn.
        </div>
    </div>

    <form action="{{ route('refund.store') }}" method="POST" enctype="multipart/form-data" id="refundForm">
        @csrf
        <input type="hidden" name="order_id" value="{{ $order->id }}">

        <div class="card refund-card mb-4">
            <div class="card-body">
                <div class="section-head mb-3">
                    <div>
                        <div class="section-subtitle">
                            Vui lòng chọn đúng sản phẩm và khai tình trạng thực tế của từng món.
                        </div>
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

                    <div class="refund-item-card {{ $checked ? 'checked' : '' }}">
                        <div class="refund-item-main">
                            <div class="refund-check-wrap">
                                <input
                                    class="form-check-input item-checkbox"
                                    type="checkbox"
                                    name="items[]"
                                    value="{{ $item->id }}"
                                    id="item_{{ $item->id }}"
                                    {{ $checked ? 'checked' : '' }}>
                            </div>

                            <div class="refund-item-image-wrap">
                                <img
                                    src="{{ $imageUrl }}"
                                    alt="{{ $product->name ?? 'Sản phẩm' }}"
                                    class="refund-item-image">
                            </div>

                            <div class="refund-item-content">
                                <label for="item_{{ $item->id }}" class="refund-item-name cursor-pointer">
                                    {{ $product->name ?? 'Sản phẩm' }}
                                </label>

                                <div class="refund-item-meta">
                                    BT{{ str_pad($variant->id ?? 0, 5, '0', STR_PAD_LEFT) }}
                                    <span class="dot">•</span>
                                    Số lượng: x{{ $item->quantity ?? 1 }}
                                </div>

                                <div class="refund-item-extra {{ $checked ? '' : 'd-none' }}">
                                    <div class="row g-3 mt-1">
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
                @endforeach

                @error('items')
                    <div class="alert alert-danger py-2 mt-3 mb-0">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="card refund-card mb-4">
            <div class="card-body">
                <h6 class="section-title mb-3">Thông tin yêu cầu</h6>

                <div class="mb-0">
                    <label class="form-label fw-semibold">Lý do trả hàng</label>
                    <textarea
                        name="reason"
                        class="form-control refund-textarea"
                        rows="5"
                        placeholder="Vui lòng mô tả lý do chung của yêu cầu hoàn tiền..."
                        required>{{ old('reason') }}</textarea>

                    @error('reason')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card refund-card mb-4">
            <div class="card-body">
                <h6 class="section-title mb-3">Hình ảnh / video minh chứng</h6>

                <div class="upload-grid">
                    <label class="upload-box">
                        <div class="upload-icon">
                            <i class="bi bi-image"></i>
                        </div>
                        <div class="upload-text">
                            <strong>Thêm hình ảnh</strong>
                            <span>Chọn tối đa 5 ảnh</span>
                        </div>

                        <input
                            type="file"
                            name="images[]"
                            id="imageInput"
                            accept="image/*"
                            multiple
                            hidden>
                    </label>

                    <label class="upload-box">
                        <div class="upload-icon">
                            <i class="bi bi-camera-video"></i>
                        </div>
                        <div class="upload-text">
                            <strong>Thêm video</strong>
                            <span>Chọn 1 video minh chứng</span>
                        </div>

                        <input
                            type="file"
                            name="video"
                            id="videoInput"
                            accept="video/*"
                            hidden>
                    </label>
                </div>

                <div class="upload-note mt-2">
                    Hỗ trợ tối đa <b>5 ảnh</b> và <b>1 video</b>. Nên chụp rõ seal, hộp, nắp, tem và tình trạng thực tế sản phẩm.
                </div>

                @error('images.*')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror

                @error('video')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror

                <div id="previewArea" class="preview-area mt-3"></div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-send me-1"></i>
                Gửi yêu cầu hoàn tiền
            </button>

            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-outline-secondary px-4">
                Quay lại
            </a>
        </div>
    </form>
</div>

<style>
.refund-page{
    --rf-border:#e6edf5;
    --rf-text:#0f172a;
    --rf-muted:#64748b;
    --rf-soft:#f8fbff;
    --rf-soft-2:#eef6ff;
    --rf-primary:#0d6efd;
    --rf-primary-2:#2563eb;
}

.refund-header{
    margin-bottom:20px;
}

.refund-title{
    font-size:26px;
    font-weight:800;
    color:var(--rf-text);
}

.refund-subtitle{
    font-size:14px;
    color:var(--rf-muted);
    line-height:1.6;
}

.refund-card{
    border:none;
    border-radius:18px;
    box-shadow:0 10px 26px rgba(15, 23, 42, 0.06);
    overflow:hidden;
}

.refund-card .card-body{
    padding:20px;
}

.section-head{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:12px;
}

.section-title{
    font-size:16px;
    font-weight:700;
    color:var(--rf-text);
}

.section-subtitle{
    font-size:13px;
    color:var(--rf-muted);
    line-height:1.6;
}

.refund-item-card{
    border:1px solid var(--rf-border);
    border-radius:16px;
    padding:16px;
    background:#fff;
    transition:all .2s ease;
    margin-bottom:14px;
}

.refund-item-card:last-child{
    margin-bottom:0;
}

.refund-item-card.checked{
    border-color:#bfd8ff;
    background:linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    box-shadow:0 8px 18px rgba(37, 99, 235, 0.06);
}

.refund-item-main{
    display:flex;
    align-items:flex-start;
    gap:14px;
}

.refund-check-wrap{
    padding-top:4px;
}

.refund-item-image-wrap{
    flex-shrink:0;
}

.refund-item-image{
    width:72px;
    height:72px;
    object-fit:cover;
    border-radius:12px;
    border:1px solid var(--rf-border);
    background:#fff;
}

.refund-item-content{
    flex:1;
    min-width:0;
}

.refund-item-name{
    display:block;
    font-size:15px;
    font-weight:700;
    color:var(--rf-text);
    margin-bottom:4px;
    line-height:1.5;
}

.refund-item-meta{
    font-size:13px;
    color:var(--rf-muted);
    margin-bottom:10px;
}

.refund-item-meta .dot{
    margin:0 6px;
}

.refund-item-extra{
    background:var(--rf-soft);
    border:1px solid #e4eefb;
    border-radius:12px;
    padding:12px;
}

.refund-textarea{
    border-radius:14px;
    border:1px solid var(--rf-border);
    box-shadow:none !important;
}

.refund-textarea:focus,
.form-control:focus,
.form-select:focus{
    border-color:#9ec5fe;
    box-shadow:0 0 0 .2rem rgba(13,110,253,.12) !important;
}

.upload-grid{
    display:grid;
    grid-template-columns:repeat(2, minmax(0, 1fr));
    gap:14px;
}

.upload-box{
    display:flex;
    align-items:center;
    gap:14px;
    border:1.5px dashed #93c5fd;
    background:linear-gradient(180deg, #f8fbff 0%, #eef6ff 100%);
    color:#1d4ed8;
    padding:16px;
    border-radius:16px;
    cursor:pointer;
    transition:all .2s ease;
}

.upload-box:hover{
    border-color:#60a5fa;
    transform:translateY(-1px);
    box-shadow:0 10px 18px rgba(37, 99, 235, 0.08);
}

.upload-icon{
    width:42px;
    height:42px;
    border-radius:12px;
    background:#dbeafe;
    color:#2563eb;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:18px;
    flex-shrink:0;
}

.upload-text{
    display:flex;
    flex-direction:column;
    gap:2px;
}

.upload-text strong{
    font-size:14px;
    color:#1e3a8a;
}

.upload-text span{
    font-size:12.5px;
    color:#64748b;
}

.upload-note{
    font-size:13px;
    color:var(--rf-muted);
    line-height:1.6;
}

.preview-area{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.preview-item{
    width:104px;
    height:104px;
    border-radius:12px;
    overflow:hidden;
    border:1px solid var(--rf-border);
    background:#fff;
    box-shadow:0 4px 10px rgba(15, 23, 42, 0.04);
}

.preview-item img,
.preview-item video{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
}

.cursor-pointer{
    cursor:pointer;
}

@media (max-width: 767.98px){
    .refund-title{
        font-size:22px;
    }

    .refund-card .card-body{
        padding:16px;
    }

    .refund-item-card{
        padding:14px;
    }

    .refund-item-main{
        gap:12px;
    }

    .refund-item-image{
        width:64px;
        height:64px;
    }

    .upload-grid{
        grid-template-columns:1fr;
    }

    .upload-box{
        padding:14px;
    }

    .preview-item{
        width:88px;
        height:88px;
    }
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
        div.className = 'preview-item';
        div.innerHTML = `<img src="${url}" alt="preview">`;
        preview.appendChild(div);
    });

    if (videoFile) {
        const url = URL.createObjectURL(videoFile);
        const div = document.createElement('div');
        div.className = 'preview-item';
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
            card.classList.add('checked');
            extra.classList.remove('d-none');
        } else {
            card.classList.remove('checked');
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