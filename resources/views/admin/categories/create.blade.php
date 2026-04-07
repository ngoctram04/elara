@extends('layouts.admin')

@section('title', 'Thêm danh mục')

@section('content')
<style>
    .category-form-page{
        font-size:14px;
        color:#334155;
    }

    .category-form-card{
        border-radius:16px;
        overflow:hidden;
        border:1px solid #edf2f7;
    }

    .category-form-title{
        font-size:18px;
        font-weight:600;
        color:#1e293b;
    }

    .category-form-subtext{
        font-size:13px;
        color:#64748b;
    }

    .category-parent-box{
        display:flex;
        align-items:center;
        gap:10px;
        background:#eff6ff;
        border:1px solid #dbeafe;
        color:#1e40af;
        border-radius:12px;
        padding:12px 14px;
        margin-bottom:18px;
        font-size:13px;
    }

    .category-parent-icon{
        width:34px;
        height:34px;
        border-radius:10px;
        background:#dbeafe;
        display:flex;
        align-items:center;
        justify-content:center;
        flex-shrink:0;
    }

    .category-form-box{
        background:#fff;
        border:1px solid #e9eef5;
        border-radius:14px;
        padding:20px;
    }

    .form-label{
        font-size:13px;
        font-weight:500;
        color:#334155;
        margin-bottom:7px;
    }

    .form-control,
    .form-select{
        border-radius:10px;
        border:1px solid #dbe3ee;
        font-size:14px;
        color:#1e293b;
        padding:10px 12px;
        box-shadow:none !important;
    }

    .form-control:focus,
    .form-select:focus{
        border-color:#93c5fd;
        box-shadow:0 0 0 3px rgba(59, 130, 246, 0.10) !important;
    }

    .form-text{
        font-size:12.5px;
        color:#64748b;
    }

    .category-upload-area{
        display:flex;
        flex-direction:column;
        align-items:flex-start;
        gap:14px;
    }

    .category-upload-trigger{
        display:inline-flex;
        align-items:center;
        gap:10px;
        padding:14px 22px;
        border:1.5px solid #6ea8fe;
        border-radius:18px;
        background:#f3f4f6;
        color:#0d6efd;
        font-size:16px;
        font-weight:600;
        cursor:pointer;
        transition:all .2s ease;
        user-select:none;
    }

    .category-upload-trigger:hover{
        background:#eaf2ff;
        border-color:#3b82f6;
    }

    .category-upload-trigger i{
        font-size:18px;
        line-height:1;
    }

    .category-upload-input{
        display:none;
    }

    .category-preview-box{
        display:none;
        align-items:center;
        gap:14px;
        padding:12px 14px;
        border:1px solid #e2e8f0;
        border-radius:14px;
        background:#f8fafc;
        min-width:320px;
        max-width:100%;
    }

    .category-preview-box.show{
        display:flex;
    }

    .category-preview-image{
        width:88px;
        height:88px;
        object-fit:contain;
        background:#fff;
        border:1px solid #e2e8f0;
        border-radius:12px;
        padding:6px;
        flex-shrink:0;
    }

    .category-preview-info{
        min-width:0;
        flex:1;
    }

    .category-preview-name{
        font-size:13px;
        font-weight:500;
        color:#1e293b;
        word-break:break-word;
        margin-bottom:8px;
    }

    .category-remove-image{
        border:none;
        background:none;
        padding:0;
        color:#dc2626;
        font-size:12.5px;
        font-weight:500;
    }

    .category-remove-image:hover{
        text-decoration:underline;
    }

    .category-action{
        margin-top:22px;
        display:flex;
        gap:10px;
        flex-wrap:wrap;
    }

    .category-btn{
        font-size:13px;
        font-weight:500;
        border-radius:10px;
        padding:9px 16px;
    }

    .invalid-feedback{
        font-size:12.5px;
    }

    @media (max-width: 768px){
        .category-form-title{
            font-size:16px;
        }

        .category-form-box{
            padding:16px;
        }

        .category-upload-trigger{
            width:100%;
            justify-content:center;
            font-size:15px;
            padding:13px 18px;
        }

        .category-preview-box{
            min-width:100%;
            align-items:flex-start;
        }
    }
</style>

<div class="category-form-page">
    <div class="card shadow-sm border-0 category-form-card">
        <div class="card-body p-3 p-md-4">

            <div class="mb-4">
                <h5 class="category-form-title mb-1">
                    {{ !empty($parent) ? 'Thêm danh mục nhỏ' : 'Thêm danh mục' }}
                </h5>
                <div class="category-form-subtext">
                    {{ !empty($parent) ? 'Tạo mới danh mục con thuộc danh mục cha' : 'Tạo mới danh mục sản phẩm' }}
                </div>
            </div>

            @if (!empty($parent))
                <div class="category-parent-box">
                    <div class="category-parent-icon">
                        <i class="bi bi-folder-fill"></i>
                    </div>
                    <div>
                        Danh mục cha:
                        <strong>{{ $parent->name }}</strong>
                    </div>
                </div>
            @endif

            <form method="POST"
                  action="{{ route('admin.categories.store') }}"
                  enctype="multipart/form-data">
                @csrf

                <div class="category-form-box">

                    <div class="mb-3">
                        <label for="name" class="form-label">Tên danh mục</label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="Nhập tên danh mục"
                            autofocus
                        >

                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if (!empty($parent))
                        <div class="mb-3">
                            <label class="form-label d-block">Hình ảnh danh mục nhỏ</label>

                            <div class="category-upload-area">
                                <label for="image" class="category-upload-trigger">
                                    <i class="bi bi-camera-fill"></i>
                                    <span>Thêm hình ảnh</span>
                                </label>

                                <input
                                    type="file"
                                    id="image"
                                    name="image"
                                    accept="image/*"
                                    class="category-upload-input @error('image') is-invalid @enderror"
                                >

                                <div class="category-preview-box" id="previewBox">
                                    <img
                                        id="previewImage"
                                        src=""
                                        alt="Preview"
                                        class="category-preview-image"
                                    >

                                    <div class="category-preview-info">
                                        <div class="category-preview-name" id="previewName"></div>

                                        <button type="button" class="category-remove-image" id="removeImageBtn">
                                            Xóa ảnh đã chọn
                                        </button>
                                    </div>
                                </div>
                            </div>

                            @error('image')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    @if (!empty($parent))
                        <input type="hidden" name="parent_id" value="{{ $parent->id }}">
                    @endif

                    <div class="category-action">
                        <button type="submit" class="btn btn-primary category-btn">
                            <i class="bi bi-save me-1"></i> Lưu
                        </button>

                        <a href="{{ !empty($parent)
                                    ? route('admin.categories.show', $parent->id)
                                    : route('admin.categories.index') }}"
                           class="btn btn-outline-secondary category-btn">
                            Quay lại
                        </a>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection

@push('scripts')
@if (!empty($parent))
<script>
document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('image');
    const previewBox = document.getElementById('previewBox');
    const previewImage = document.getElementById('previewImage');
    const previewName = document.getElementById('previewName');
    const removeImageBtn = document.getElementById('removeImageBtn');

    if (!imageInput) return;

    function resetPreview() {
        imageInput.value = '';
        previewImage.src = '';
        previewName.textContent = '';
        previewBox.classList.remove('show');
    }

    imageInput.addEventListener('change', function (e) {
        const file = e.target.files[0];

        if (!file) {
            resetPreview();
            return;
        }

        previewImage.src = URL.createObjectURL(file);
        previewName.textContent = file.name;
        previewBox.classList.add('show');
    });

    removeImageBtn?.addEventListener('click', function () {
        resetPreview();
    });
});
</script>
@endif
@endpush