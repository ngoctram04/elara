@extends('layouts.admin')

@section('title', $category->name)

@section('content')
<style>
    .category-detail-page{
        font-size:14px;
        color:#334155;
    }

    .category-detail-card{
        border-radius:16px;
        overflow:hidden;
        border:1px solid #edf2f7;
    }

    .category-detail-title{
        font-size:18px;
        font-weight:600;
        color:#1e293b;
    }

    .category-detail-subtext{
        font-size:13px;
        color:#64748b;
    }

    .category-btn{
        font-size:13px;
        font-weight:500;
        border-radius:10px;
        padding:8px 14px;
    }

    .category-filter-box{
        background:#f8fafc;
        border:1px solid #e9eef5;
        border-radius:14px;
        padding:14px;
        margin-bottom:18px;
    }

    .form-control,
    .form-select{
        border-radius:10px;
        border:1px solid #dbe3ee;
        font-size:14px;
        color:#1e293b;
        padding:10px 12px;
        box-shadow:none !important;
    }

    .form-control:focus,
    .form-select:focus{
        border-color:#93c5fd;
        box-shadow:0 0 0 3px rgba(59, 130, 246, 0.10) !important;
    }

    .category-table-wrap{
        border:1px solid #e9eef5;
        border-radius:14px;
        overflow:hidden;
        background:#fff;
    }

    .table{
        margin-bottom:0;
    }

    .table thead th{
        background:linear-gradient(to bottom, #f8fafc, #f1f5f9);
        color:#475569;
        font-size:13px;
        font-weight:600;
        border-bottom:1px solid #e2e8f0;
        padding:13px 12px;
        white-space:nowrap;
        vertical-align:middle;
    }

    .table tbody td{
        font-size:13.5px;
        padding:13px 12px;
        border-color:#eef2f7;
        vertical-align:middle;
    }

    .table tbody tr{
        transition:all .2s ease;
    }

    .table tbody tr:hover{
        background:#f8fbff;
    }

    .category-code{
        color:#64748b;
        font-weight:600;
        font-size:13px;
    }

    .child-image{
        width:56px;
        height:56px;
        object-fit:contain;
        background:#fff;
        border:1px solid #e2e8f0;
        border-radius:12px;
        padding:4px;
    }

    .child-image-empty{
        width:56px;
        height:56px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border:1px solid #e2e8f0;
        border-radius:12px;
        background:#f8fafc;
        color:#94a3b8;
        font-size:11px;
        font-weight:500;
    }

    .category-name{
        font-weight:500;
        color:#1e293b;
        line-height:1.5;
    }

    .product-badge{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-width:36px;
        height:28px;
        padding:0 10px;
        border-radius:999px;
        font-size:12px;
        font-weight:600;
    }

    .product-has{
        background:#dbeafe;
        color:#1d4ed8;
        border:1px solid #bfdbfe;
    }

    .product-empty{
        background:#e2e8f0;
        color:#475569;
        border:1px solid #cbd5e1;
    }

    .category-date{
        color:#64748b;
        font-size:13px;
        line-height:1.5;
    }

    .category-action-group{
        display:flex;
        align-items:center;
        justify-content:center;
        gap:6px;
        flex-wrap:wrap;
    }

    .category-icon-btn{
        width:34px;
        height:34px;
        border-radius:10px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding:0;
    }

    .empty-box{
        padding:28px 12px;
        text-align:center;
        color:#94a3b8;
        font-size:13px;
    }

    .back-link{
        font-size:13px;
        font-weight:500;
        color:#64748b;
    }

    .back-link:hover{
        color:#1d4ed8;
    }

    @media (max-width: 768px){
        .category-detail-title{
            font-size:16px;
        }

        .category-filter-box{
            padding:12px;
        }

        .table thead th,
        .table tbody td{
            padding:10px;
        }
    }
</style>

<div class="category-detail-page">
    <div class="card shadow-sm border-0 category-detail-card">
        <div class="card-body p-3 p-md-4">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h5 class="category-detail-title mb-1">{{ $category->name }}</h5>
                    <div class="category-detail-subtext">Danh sách danh mục nhỏ</div>
                </div>

                <a href="{{ route('admin.categories.create', ['parent_id' => $category->id]) }}"
                   class="btn btn-primary category-btn">
                    <i class="bi bi-plus-lg me-1"></i>
                    Thêm danh mục nhỏ
                </a>
            </div>

            <div class="category-filter-box">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <input
                            type="text"
                            name="keyword"
                            value="{{ request('keyword') }}"
                            class="form-control"
                            placeholder="Tìm theo tên danh mục nhỏ hoặc mã..."
                        >
                    </div>

                    <div class="col-md-3">
                        <select name="sort" class="form-select">
                            <option value="">Sắp xếp</option>
                            <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>
                                Mới nhất
                            </option>
                            <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>
                                Cũ nhất
                            </option>
                        </select>
                    </div>

                    <div class="col-md-4 d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-outline-primary category-btn">
                            <i class="bi bi-search me-1"></i> Lọc
                        </button>

                        <a href="{{ route('admin.categories.show', $category) }}"
                           class="btn btn-outline-secondary category-btn">
                            Đặt lại
                        </a>
                    </div>
                </form>
            </div>

            <div class="table-responsive category-table-wrap">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th width="150" class="text-center">Mã</th>
                            <th width="150" class="text-center">Ảnh</th>
                            <th>Tên danh mục nhỏ</th>
                            <th width="120" class="text-center">Sản phẩm</th>
                            <th width="280" class="text-center">Ngày tạo</th>
                            <th width="140" class="text-center">Hành động</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($children as $child)
                            <tr>
                                <td class="text-center">
                                    <span class="category-code">
                                        DMC{{ str_pad($child->id, 4, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    @if($child->image)
                                        <img src="{{ asset('storage/' . $child->image) }}"
                                             alt="{{ $child->name }}"
                                             class="child-image">
                                    @else
                                        <div class="child-image-empty">
                                            No img
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <div class="category-name">
                                        {{ $child->name }}
                                    </div>
                                </td>

                                <td class="text-center">
                                    <span class="product-badge {{ $child->products_count > 0 ? 'product-has' : 'product-empty' }}">
                                        {{ $child->products_count }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <div class="category-date">
                                        {{ optional($child->created_at)->format('d/m/Y') ?? '—' }}
                                        <div>{{ optional($child->created_at)->format('H:i') }}</div>
                                    </div>
                                </td>

                                <td class="text-center">
                                    <div class="category-action-group">
                                        <a href="{{ route('admin.categories.edit', $child) }}"
                                           class="btn btn-sm btn-outline-secondary category-icon-btn"
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
                                                    class="btn btn-sm btn-outline-danger category-icon-btn btn-delete-category"
                                                    data-id="{{ $child->id }}"
                                                    data-products="{{ $child->products_count }}"
                                                    title="Xóa">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-box">
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
               class="btn btn-link btn-sm text-decoration-none mt-3 px-0 back-link">
                ← Quay lại danh sách danh mục
            </a>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-delete-category').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;

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
});
</script>
@endpush