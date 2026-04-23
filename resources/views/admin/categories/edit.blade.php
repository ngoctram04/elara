@extends('layouts.admin')

@section('title', 'Chỉnh sửa danh mục')

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

    .category-image-group{
        display:flex;
        flex-wrap:wrap;
        gap:16px;
        width:100%;
    }

    .category-image-card{
        min-width:180px;
        background:#f8fafc;
        border:1px solid #e2e8f0;
        border-radius:14px;
        padding:14px;
    }

    .category-image-label{
        font-size:12.5px;
        color:#64748b;
        margin-bottom:10px;
        font-weight:500;
    }

    .category-preview-image{
        width:100px;
        height:100px;
        object-fit:contain;
        background:#fff;
        border:1px solid #e2e8f0;
        border-radius:12px;
        padding:6px;
        display:block;
    }

    .category-preview-box{
        display:none;
    }

    .category-preview-box.show{
        display:block;
    }

    .category-preview-name{
        margin-top:10px;
        font-size:12.5px;
        color:#64748b;
        word-break:break-word;
        line-height:1.5;
    }

    .category-remove-image{
        margin-top:8px;
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

        .category-image-card{
            width:100%;
        }
    }
</style>

<div class="category-form-page">
    <div class="card shadow-sm border-0 category-form-card">
        <div class="card-body p-3 p-md-4">

            <div class="mb-4">
                <h5 class="category-form-title mb-1">Chỉnh sửa danh mục</h5>
                <div class="category-form-subtext">Cập nhật thông tin danh mục hiện tại</div>
            </div>

            <form method="POST"
                  action="{{ route('admin.categories.update', $category) }}"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="category-form-box">

                    <div class="mb-3">
                        <label for="name" class="form-label">
                            Tên danh mục <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $category->name) }}"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="Nhập tên danh mục"
                            autofocus
                        >

                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($category->parent_id)
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

                                <div class="category-image-group">
                                    @if($category->image)
                                        <div class="category-image-card" id="currentImageBox">
                                            <div class="category-image-label">Ảnh hiện tại</div>
                                            <img src="{{ asset('storage/' . $category->image) }}"
                                                 alt="{{ $category->name }}"
                                                 class="category-preview-image">
                                        </div>
                                    @endif

                                    <div class="category-image-card category-preview-box" id="previewWrapper">
                                        <div class="category-image-label">Ảnh mới</div>
                                        <img id="previewImage"
                                             src=""
                                             alt="Preview"
                                             class="category-preview-image">

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

                    <div class="category-action">
                        <button type="submit" class="btn btn-primary category-btn">
                            <i class="bi bi-save me-1"></i> Cập nhật
                        </button>

                        <a href="{{ $category->parent_id
                                    ? route('admin.categories.show', $category->parent_id)
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
@if($category->parent_id)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('image');
    const previewWrapper = document.getElementById('previewWrapper');
    const previewImage = document.getElementById('previewImage');
    const previewName = document.getElementById('previewName');
    const removeImageBtn = document.getElementById('removeImageBtn');

    if (!imageInput) return;

    function resetPreview() {
        imageInput.value = '';
        previewImage.src = '';
        previewName.textContent = '';
        previewWrapper.classList.remove('show');
    }

    imageInput.addEventListener('change', function (e) {
        const file = e.target.files[0];

        if (!file) {
            resetPreview();
            return;
        }

        previewImage.src = URL.createObjectURL(file);
        previewName.textContent = file.name;
        previewWrapper.classList.add('show');
    });

    removeImageBtn?.addEventListener('click', function () {
        resetPreview();
    });
});
</script>
@endif
@endpush