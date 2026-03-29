@extends('layouts.admin')

@section('title', 'Chỉnh sửa danh mục')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">

        <h5 class="fw-semibold mb-3">
            Chỉnh sửa danh mục
        </h5>

        <form method="POST"
              action="{{ route('admin.categories.update', $category) }}"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- TÊN DANH MỤC --}}
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

            {{-- ẢNH: CHỈ CHO DANH MỤC NHỎ --}}
            @if($category->parent_id)
                <div class="mb-3">
                    <label for="image" class="form-label">Hình ảnh danh mục nhỏ</label>

                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept="image/*"
                        class="form-control @error('image') is-invalid @enderror"
                    >

                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <small class="text-muted d-block mt-1">
                        Chỉ áp dụng cho danh mục nhỏ. Hỗ trợ: jpg, jpeg, png, webp. Tối đa 2MB.
                    </small>

                    @if($category->image)
                        <div class="mt-3" id="currentImageBox">
                            <div class="small text-muted mb-2">Ảnh hiện tại:</div>
                            <img src="{{ asset('storage/' . $category->image) }}"
                                 alt="{{ $category->name }}"
                                 class="rounded border"
                                 style="width:100px;height:100px;object-fit:contain;background:#fff;">
                        </div>
                    @endif

                    <div class="mt-3 d-none" id="previewWrapper">
                        <div class="small text-muted mb-2">Ảnh mới:</div>
                        <img id="previewImage"
                             src=""
                             alt="Preview"
                             class="rounded border"
                             style="width:100px;height:100px;object-fit:contain;background:#fff;">
                    </div>
                </div>
            @endif

            {{-- ACTION --}}
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Cập nhật
                </button>

                <a href="{{ $category->parent_id
                            ? route('admin.categories.show', $category->parent_id)
                            : route('admin.categories.index') }}"
                   class="btn btn-secondary">
                    Quay lại
                </a>
            </div>

        </form>

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

    if (!imageInput) return;

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
});
</script>
@endif
@endpush