@extends('layouts.admin')

@section('title', 'Thêm thương hiệu')

@section('content')
<style>
    .brand-form-page{
        font-size:14px;
        color:#334155;
    }

    .brand-form-card{
        border-radius:16px;
        overflow:hidden;
        border:1px solid #edf2f7;
    }

    .brand-form-title{
        font-size:18px;
        font-weight:600;
        color:#1e293b;
    }

    .brand-form-subtext{
        font-size:13px;
        color:#64748b;
    }

    .brand-form-box{
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

    .brand-upload-area{
        display:flex;
        flex-direction:column;
        align-items:flex-start;
        gap:14px;
    }

    .brand-upload-trigger{
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

    .brand-upload-trigger:hover{
        background:#eaf2ff;
        border-color:#3b82f6;
    }

    .brand-upload-trigger i{
        font-size:18px;
        line-height:1;
    }

    .brand-upload-input{
        display:none;
    }

    .brand-preview-box{
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

    .brand-preview-box.show{
        display:flex;
    }

    .brand-preview-image{
        width:88px;
        height:88px;
        object-fit:contain;
        background:#fff;
        border:1px solid #e2e8f0;
        border-radius:12px;
        padding:6px;
        flex-shrink:0;
    }

    .brand-preview-info{
        min-width:0;
        flex:1;
    }

    .brand-preview-name{
        font-size:13px;
        font-weight:500;
        color:#1e293b;
        word-break:break-word;
        margin-bottom:8px;
    }

    .brand-remove-image{
        border:none;
        background:none;
        padding:0;
        color:#dc2626;
        font-size:12.5px;
        font-weight:500;
    }

    .brand-remove-image:hover{
        text-decoration:underline;
    }

    .brand-action{
        margin-top:22px;
        display:flex;
        gap:10px;
        flex-wrap:wrap;
    }

    .brand-btn{
        font-size:13px;
        font-weight:500;
        border-radius:10px;
        padding:9px 16px;
    }

    .invalid-feedback{
        font-size:12.5px;
    }

    @media (max-width: 768px){
        .brand-form-title{
            font-size:16px;
        }

        .brand-form-box{
            padding:16px;
        }

        .brand-upload-trigger{
            width:100%;
            justify-content:center;
            font-size:15px;
            padding:13px 18px;
        }

        .brand-preview-box{
            min-width:100%;
            align-items:flex-start;
        }
    }
</style>

<div class="brand-form-page">
    <div class="card shadow-sm border-0 brand-form-card">
        <div class="card-body p-3 p-md-4">

            <div class="mb-4">
                <h5 class="brand-form-title mb-1">Thêm thương hiệu</h5>
                <div class="brand-form-subtext">Tạo mới thương hiệu trong hệ thống</div>
            </div>

            <form method="POST"
                  action="{{ route('admin.brands.store') }}"
                  enctype="multipart/form-data">
                @csrf

                <div class="brand-form-box">
                    <div class="mb-3">
                        <label class="form-label">Tên thương hiệu</label>
                        <input type="text"
                               name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               placeholder="Nhập tên thương hiệu">

                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block">Hình ảnh</label>

                        <div class="brand-upload-area">
                            <label for="imageInput" class="brand-upload-trigger">
                                <i class="bi bi-camera-fill"></i>
                                <span>Thêm hình ảnh</span>
                            </label>

                            <input type="file"
                                   name="image"
                                   accept="image/*"
                                   class="brand-upload-input @error('image') is-invalid @enderror"
                                   id="imageInput">

                            <div class="brand-preview-box" id="previewBox">
                                <img id="previewImage"
                                     src=""
                                     alt="Preview"
                                     class="brand-preview-image">

                                <div class="brand-preview-info">
                                    <div class="brand-preview-name" id="previewName"></div>

                                    <button type="button" class="brand-remove-image" id="removeImageBtn">
                                        Xóa ảnh đã chọn
                                    </button>
                                </div>
                            </div>
                        </div>

                        @error('image')
                            <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="brand-action">
                        <button class="btn btn-primary brand-btn">
                            <i class="bi bi-save me-1"></i> Lưu
                        </button>

                        <a href="{{ route('admin.brands.index') }}"
                           class="btn btn-outline-secondary brand-btn">
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('imageInput');
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
@endpush