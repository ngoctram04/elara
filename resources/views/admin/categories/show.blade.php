@extends('layouts.admin')

@section('title', $category->name)

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-semibold mb-1">{{ $category->name }}</h5>
                <small class="text-muted">Danh sách danh mục con</small>
            </div>

            <a href="{{ route('admin.categories.create', ['parent_id' => $category->id]) }}"
               class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Thêm danh mục con
            </a>
        </div>

        {{-- SEARCH + SORT --}}
        <form method="GET" class="row g-2 mb-3 align-items-center">
            <div class="col-md-5">
                <input type="text"
                       name="keyword"
                       value="{{ request('keyword') }}"
                       class="form-control form-control-sm"
                       placeholder="Tìm theo tên danh mục con...">
            </div>

            <div class="col-md-3">
                <select name="sort" class="form-select form-select-sm">
                    <option value="">Sắp xếp</option>
                    <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>
                        Mới nhất
                    </option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>
                        Cũ nhất
                    </option>
                </select>
            </div>

            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-search"></i>
                </button>

                <a href="{{ route('admin.categories.show', $category) }}"
                   class="btn btn-outline-secondary btn-sm">
                    Đặt lại
                </a>
            </div>
        </form>

        {{-- TABLE --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="80" class="text-center text-muted">Mã</th>
                        <th>Tên danh mục con</th>
                        <th width="120" class="text-center">Sản phẩm</th>
                        <th width="180" class="text-center">Ngày tạo</th>
                        <th width="140" class="text-center">Hành động</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($children as $child)
                        <tr>
                            {{-- MÃ --}}
                            <td class="text-center text-muted fw-semibold">
                                {{ $child->id }}
                            </td>

                            {{-- TÊN --}}
                            <td class="fw-medium">
                                {{ $child->name }}
                            </td>

                            {{-- SỐ SẢN PHẨM --}}
                            <td class="text-center fw-semibold">
                                {{ $child->products_count }}
                            </td>

                            {{-- NGÀY TẠO --}}
                            <td class="text-center text-muted">
                                {{ optional($child->created_at)->format('d/m/Y H:i') }}
                            </td>

                            {{-- HÀNH ĐỘNG --}}
                            <td class="text-center">
                                <a href="{{ route('admin.categories.edit', $child) }}"
                                   class="btn btn-sm btn-outline-secondary"
                                   title="Chỉnh sửa">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <form method="POST"
                                      action="{{ route('admin.categories.destroy', $child) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('Xóa danh mục con này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"
                                            {{ $child->products_count > 0 ? 'disabled' : '' }}
                                            title="{{ $child->products_count > 0 ? 'Danh mục đang có sản phẩm' : 'Xóa' }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Chưa có danh mục con
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- FOOTER --}}
        <a href="{{ route('admin.categories.index') }}"
           class="btn btn-link btn-sm text-decoration-none mt-3">
            ← Quay lại danh sách danh mục
        </a>

    </div>
</div>
@endsection
