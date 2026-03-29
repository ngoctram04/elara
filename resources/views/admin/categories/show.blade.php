@extends('layouts.admin')

@section('title', $category->name)

@section('content')

<div class="card shadow-sm border-0">
    <div class="card-body">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-semibold mb-1">{{ $category->name }}</h5>
                <small class="text-muted">Danh sách danh mục nhỏ</small>
            </div>

            <a href="{{ route('admin.categories.create', ['parent_id' => $category->id]) }}"
               class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>
                Thêm danh mục nhỏ
            </a>
        </div>

        {{-- SEARCH + SORT --}}
        <form method="GET" class="row g-2 mb-3 align-items-center">
            <div class="col-md-5">
                <input
                    type="text"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    class="form-control form-control-sm"
                    placeholder="Tìm theo tên danh mục nhỏ hoặc mã..."
                >
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
                        <th width="100" class="text-center text-muted">Mã</th>
                        <th width="100" class="text-center">Ảnh</th>
                        <th>Tên danh mục nhỏ</th>
                        <th width="120" class="text-center">Sản phẩm</th>
                        <th width="180" class="text-center">Ngày tạo</th>
                        <th width="140" class="text-center">Hành động</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($children as $child)
                        <tr>
                            <td class="text-center text-muted fw-semibold">
                                DMC{{ str_pad($child->id, 4, '0', STR_PAD_LEFT) }}
                            </td>

                            <td class="text-center">
                                @if($child->image)
                                    <img src="{{ asset('storage/' . $child->image) }}"
                                         alt="{{ $child->name }}"
                                         class="rounded border"
                                         style="width:56px;height:56px;object-fit:contain;background:#fff;">
                                @else
                                    <div class="d-inline-flex align-items-center justify-content-center rounded border bg-light text-muted"
                                         style="width:56px;height:56px;font-size:12px;">
                                        No img
                                    </div>
                                @endif
                            </td>

                            <td class="fw-medium">
                                {{ $child->name }}
                            </td>

                            <td class="text-center fw-semibold">
                                <span class="badge {{ $child->products_count > 0 ? 'bg-primary' : 'bg-secondary' }}">
                                    {{ $child->products_count }}
                                </span>
                            </td>

                            <td class="text-center text-muted">
                                {{ optional($child->created_at)->format('d/m/Y H:i') }}
                            </td>

                            <td class="text-center">
                                <a href="{{ route('admin.categories.edit', $child) }}"
                                   class="btn btn-sm btn-outline-secondary"
                                   title="Chỉnh sửa">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <form method="POST"
                                      id="delete-category-{{ $child->id }}"
                                      action="{{ route('admin.categories.destroy', $child) }}"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')

                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger btn-delete-category"
                                            data-id="{{ $child->id }}"
                                            {{ $child->products_count > 0 ? 'disabled' : '' }}
                                            title="{{ $child->products_count > 0 ? 'Danh mục đang có sản phẩm' : 'Xóa' }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Chưa có danh mục con
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $children->links('vendor.pagination.custom-blue') }}
        </div>

        <a href="{{ route('admin.categories.index') }}"
           class="btn btn-link btn-sm text-decoration-none mt-3">
            ← Quay lại danh sách danh mục
        </a>

    </div>
</div>

@endsection

@push('scripts')
<script>
document.querySelectorAll('.btn-delete-category').forEach(function (btn) {
    btn.addEventListener('click', function () {
        if (this.hasAttribute('disabled')) return;

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