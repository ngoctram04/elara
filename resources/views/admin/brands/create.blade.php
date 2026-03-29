@extends('layouts.admin')

@section('title', 'Thêm thương hiệu')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">

        <h5 class="fw-semibold mb-3">Thêm thương hiệu</h5>

        <form method="POST"
              action="{{ route('admin.brands.store') }}"
              enctype="multipart/form-data">
            @csrf

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
                <label class="form-label">Hình ảnh</label>
                <input type="file"
                       name="image"
                       accept="image/*"
                       class="form-control @error('image') is-invalid @enderror"
                       id="imageInput">

                @error('image')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror

                <small class="text-muted d-block mt-1">
                    Hỗ trợ: jpg, jpeg, png, webp. Tối đa 2MB.
                </small>

                <div class="mt-3 d-none" id="previewWrapper">
                    <img id="previewImage"
                         src=""
                         alt="Preview"
                         class="rounded border"
                         style="width:100px;height:100px;object-fit:contain;background:#fff;">
                </div>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm">
                    <i class="bi bi-save"></i> Lưu
                </button>

                <a href="{{ route('admin.brands.index') }}"
                   class="btn btn-secondary">
                    Quay lại
                </a>
            </div>
        </form>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('imageInput');
    const previewWrapper = document.getElementById('previewWrapper');
    const previewImage = document.getElementById('previewImage');

    if (imageInput) {
        imageInput.addEventListener('change', function (e) {
            const file = e.target.files[0];

            if (!file) {
                previewImage.src = '';
                previewWrapper.classList.add('d-none');
                return;
            }

            previewImage.src = URL.createObjectURL(file);
            previewWrapper.classList.remove('d-none');
        });
    }
});
</script>
@endpush