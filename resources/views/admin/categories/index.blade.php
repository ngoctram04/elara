@extends('layouts.admin')

@section('title', 'Danh sách danh mục')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">Danh sách danh mục</h5>
                <small class="text-muted">Quản lý danh mục lớn & danh mục nhỏ</small>
            </div>

            <a href="{{ route('admin.categories.create') }}"
               class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i> Thêm danh mục
            </a>
        </div>

        {{-- SEARCH + SORT --}}
        <form method="GET" class="row g-2 mb-4 align-items-center">
            <div class="col-md-4">
                <input type="text"
                       name="keyword"
                       value="{{ request('keyword') }}"
                       class="form-control form-control-sm"
                       placeholder="Tìm theo tên danh mục hoặc mã...">
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

                <a href="{{ route('admin.categories.index') }}"
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
                        <th class="text-center" style="width:150px">Mã danh mục</th>
                        <th>Tên danh mục</th>
                        <th class="text-center" style="width:190px">Danh mục con</th>
                        <th class="text-center" style="width:190px">Ngày tạo</th>
                        <th class="text-center" style="width:90px">Xem</th>
                        <th class="text-center" style="width:150px">Hành động</th>
                    </tr>
                </thead>

                <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td class="text-center text-muted fw-semibold">
                            DM{{ str_pad($category->id, 4, '0', STR_PAD_LEFT) }}
                        </td>

                        <td class="fw-medium">
                            {{ $category->name }}
                        </td>

                        <td class="text-center">
                            <span class="badge rounded-pill {{ ($category->children_count ?? 0) > 0 ? 'bg-primary' : 'bg-secondary' }}">
                                {{ $category->children_count ?? 0 }}
                            </span>
                        </td>

                        <td class="text-center text-muted small">
                            {{ optional($category->created_at)->format('d/m/Y H:i') }}
                        </td>

                        <td class="text-center">
                            <a href="{{ route('admin.categories.show', $category) }}"
                               class="btn btn-sm btn-outline-primary"
                               title="Xem danh mục con">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>

                        <td class="text-center">
                            <a href="{{ route('admin.categories.edit', $category) }}"
                               class="btn btn-sm btn-outline-secondary"
                               title="Chỉnh sửa">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <form method="POST"
                                  id="delete-category-{{ $category->id }}"
                                  action="{{ route('admin.categories.destroy', $category) }}"
                                  class="d-inline">
                                @csrf
                                @method('DELETE')

                                <button type="button"
                                        class="btn btn-sm btn-outline-danger btn-delete-category"
                                        data-id="{{ $category->id }}"
                                        title="Xóa">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            Chưa có danh mục nào
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($categories instanceof \Illuminate\Contracts\Pagination\Paginator || $categories instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            <div class="mt-4">
                {{ $categories->links('vendor.pagination.custom-blue') }}
            </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.btn-delete-category').forEach(btn => {
    btn.addEventListener('click', function () {
        let id = this.dataset.id;

        Swal.fire({
            title: 'Xóa danh mục?',
            text: 'Hành động này không thể hoàn tác',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Hủy',
            confirmButtonText: 'Xóa'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-category-' + id).submit();
            }
        });
    });
});
</script>
@endpush