@extends('layouts.admin')

@section('title', 'Thêm danh mục')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">

        {{-- TIÊU ĐỀ --}}
        <h5 class="fw-semibold mb-3">
            {{ !empty($parent) ? 'Thêm danh mục nhỏ' : 'Thêm danh mục' }}
        </h5>

        {{-- THÔNG TIN DANH MỤC CHA --}}
        @if (!empty($parent))
            <div class="alert alert-info">
                <i class="bi bi-folder-fill me-1"></i>
                Danh mục:
                <strong>{{ $parent->name }}</strong>
            </div>
        @endif

        {{-- FORM --}}
        <form method="POST"
              action="{{ route('admin.categories.store') }}"
              enctype="multipart/form-data">
            @csrf

            {{-- TÊN DANH MỤC --}}
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

            {{-- ẢNH: CHỈ CHO DANH MỤC NHỎ --}}
            @if (!empty($parent))
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

                    <div class="mt-3 d-none" id="previewWrapper">
                        <img id="previewImage"
                             src=""
                             alt="Preview"
                             class="rounded border"
                             style="width:100px;height:100px;object-fit:contain;background:#fff;">
                    </div>
                </div>
            @endif

            {{-- PARENT_ID --}}
            @if (!empty($parent))
                <input type="hidden" name="parent_id" value="{{ $parent->id }}">
            @endif

            {{-- ACTION --}}
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Lưu
                </button>

                <a href="{{ !empty($parent)
                            ? route('admin.categories.show', $parent->id)
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
@if (!empty($parent))
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