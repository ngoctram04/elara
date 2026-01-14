@extends('layouts.admin')

@section('title', 'Thêm danh mục')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">

        {{-- TIÊU ĐỀ --}}
        <h5 class="fw-semibold mb-3">
            {{ !empty($parent) ? 'Thêm danh mục con' : 'Thêm danh mục' }}
        </h5>

        {{-- THÔNG TIN DANH MỤC CHA --}}
        @if (!empty($parent))
            <div class="alert alert-info">
                <i class="bi bi-folder-fill me-1"></i>
                Danh mục cha:
                <strong>{{ $parent->name }}</strong>
            </div>
        @endif

        {{-- FORM --}}
        <form method="POST" action="{{ route('admin.categories.store') }}">
            @csrf

            {{-- TÊN DANH MỤC --}}
            <div class="mb-3">
                <label for="name" class="form-label">
                    Tên danh mục <span class="text-danger">*</span>
                </label>

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
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- PARENT_ID (ẨN – CHỈ DÙNG KHI TẠO CON) --}}
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
