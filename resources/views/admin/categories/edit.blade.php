@extends('layouts.admin')

@section('title', 'Chỉnh sửa danh mục')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">

        {{-- TIÊU ĐỀ --}}
        <h5 class="fw-semibold mb-3">
            Chỉnh sửa danh mục
        </h5>

        {{-- FORM --}}
        <form method="POST"
              action="{{ route('admin.categories.update', $category) }}">
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
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

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
