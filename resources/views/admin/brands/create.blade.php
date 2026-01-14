@extends('layouts.admin')

@section('title', 'Thêm thương hiệu')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">

        <h5 class="fw-semibold mb-3">Thêm thương hiệu</h5>

        <form method="POST" action="{{ route('admin.brands.store') }}">
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

            <div class="d-flex gap-2">
                <button class="btn btn-success">
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
