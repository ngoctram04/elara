@extends('layouts.admin')

@section('title', 'Danh sách thương hiệu')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">Danh sách thương hiệu</h5>
                <small class="text-muted">Quản lý các thương hiệu trong hệ thống</small>
            </div>

            <a href="{{ route('admin.brands.create') }}"
               class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Thêm thương hiệu
            </a>
        </div>

        {{-- SEARCH + SORT --}}
        <form method="GET" class="row g-2 mb-4 align-items-center">
            <div class="col-md-4">
                <input type="text"
                       name="keyword"
                       value="{{ request('keyword') }}"
                       class="form-control form-control-sm"
                       placeholder="Tìm theo tên thương hiệu...">
            </div>

            <div class="col-md-3">
                <select name="sort" class="form-select form-select-sm">
                    <option value="">Sắp xếp theo</option>
                    <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>
                        Mới nhất
                    </option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>
                        Cũ nhất
                    </option>
                </select>
            </div>

            <div class="col-md-5 d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-search"></i> Lọc
                </button>

                <a href="{{ route('admin.brands.index') }}"
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
                        <th class="text-center" style="width:150px">Mã thương hiệu</th>
                        <th>Tên thương hiệu</th>
                        <th class="text-center" style="width:190px">Ngày tạo</th>
                        <th class="text-center" style="width:140px">Số sản phẩm</th>
                        <th class="text-center" style="width:140px">Hành động</th>
                    </tr>
                </thead>

                <tbody>
                @forelse ($brands as $brand)
                    <tr>
                        {{-- ID --}}
                        <td class="text-center text-muted fw-semibold">
                            {{ $brand->id }}
                        </td>

                        {{-- NAME --}}
                        <td class="fw-medium">
                            {{ $brand->name }}
                        </td>

                        {{-- CREATED AT --}}
                        <td class="text-center text-muted small">
                            {{ optional($brand->created_at)->format('d/m/Y H:i') }}
                        </td>

                        {{-- PRODUCT COUNT --}}
                        <td class="text-center">
                            <span class="badge rounded-pill
                                {{ ($brand->products_count ?? 0) > 0 ? 'bg-success' : 'bg-secondary' }}">
                                {{ $brand->products_count ?? 0 }}
                            </span>
                        </td>

                        {{-- ACTION --}}
                        <td class="text-center">
                            <a href="{{ route('admin.brands.edit', $brand) }}"
                               class="btn btn-sm btn-outline-secondary"
                               title="Chỉnh sửa">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <form method="POST"
                                  action="{{ route('admin.brands.destroy', $brand) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('Xóa thương hiệu này?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"
                                        title="Xóa">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            Chưa có thương hiệu nào
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection
