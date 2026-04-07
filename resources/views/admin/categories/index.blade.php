@extends('layouts.admin')

@section('title', 'Danh sách danh mục')

@section('content')
<style>
    .category-page{
        font-size:14px;
        color:#334155;
    }

    .category-card{
        border-radius:16px;
        overflow:hidden;
        border:1px solid #edf2f7;
    }

    .category-title{
        font-size:18px;
        font-weight:600;
        color:#1e293b;
    }

    .category-subtext{
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

    .category-name{
        font-weight:500;
        color:#1e293b;
    }

    .children-badge{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-width:34px;
        height:28px;
        padding:0 10px;
        border-radius:999px;
        font-size:12px;
        font-weight:600;
    }

    .children-has{
        background:#dbeafe;
        color:#1d4ed8;
        border:1px solid #bfdbfe;
    }

    .children-empty{
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

    @media (max-width: 768px){
        .category-title{
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

<div class="category-page">
    <div class="card border-0 shadow-sm category-card">
        <div class="card-body p-3 p-md-4">

            {{-- HEADER --}}
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h5 class="category-title mb-1">Danh sách danh mục</h5>
                    <div class="category-subtext">Quản lý danh mục lớn và danh mục nhỏ</div>
                </div>

                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary category-btn">
                    <i class="bi bi-plus-lg me-1"></i> Thêm danh mục
                </a>
            </div>

            {{-- SEARCH + SORT --}}
            <div class="category-filter-box">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <input type="text"
                               name="keyword"
                               value="{{ request('keyword') }}"
                               class="form-control"
                               placeholder="Tìm theo tên danh mục hoặc mã...">
                    </div>

                    <div class="col-md-3">
                        <select name="sort" class="form-select">
                            <option value="">Sắp xếp theo</option>
                            <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>
                                Mới nhất
                            </option>
                            <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>
                                Cũ nhất
                            </option>
                        </select>
                    </div>

                    <div class="col-md-5 d-flex gap-2 flex-wrap">
                        <button class="btn btn-outline-primary category-btn">
                            <i class="bi bi-search me-1"></i> Lọc
                        </button>

                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary category-btn">
                            Đặt lại
                        </a>
                    </div>
                </form>
            </div>

            {{-- TABLE --}}
            <div class="table-responsive category-table-wrap">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:160px">Mã danh mục</th>
                            <th>Tên danh mục</th>
                            <th class="text-center" style="width:180px">Danh mục con</th>
                            <th class="text-center" style="width:210px">Ngày tạo</th>
                            <th class="text-center" style="width:100px">Xem</th>
                            <th class="text-center" style="width:150px">Hành động</th>
                        </tr>
                    </thead>

                    <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td class="text-center">
                                <span>
                                    DM{{ str_pad($category->id, 4, '0', STR_PAD_LEFT) }}
                                </span>
                            </td>

                            <td>
                                <div class="category-name">
                                    {{ $category->name }}
                                </div>
                            </td>

                            <td class="text-center">
                                @php
                                    $childrenCount = $category->children_count ?? 0;
                                @endphp

                                <span class="children-badge {{ $childrenCount > 0 ? 'children-has' : 'children-empty' }}">
                                    {{ $childrenCount }}
                                </span>
                            </td>

                            <td class="text-center">
                                <div class="category-date">
                                    {{ optional($category->created_at)->format('d/m/Y') ?? '—' }}
                                    <div>{{ optional($category->created_at)->format('H:i') }}</div>
                                </div>
                            </td>

                            <td class="text-center">
                                <a href="{{ route('admin.categories.show', $category) }}"
                                   class="btn btn-sm btn-outline-primary category-icon-btn"
                                   title="Xem danh mục con">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>

                            <td class="text-center">
                                <div class="category-action-group">
                                    <a href="{{ route('admin.categories.edit', $category) }}"
                                       class="btn btn-sm btn-outline-secondary category-icon-btn"
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
                                                class="btn btn-sm btn-outline-danger category-icon-btn btn-delete-category"
                                                data-id="{{ $category->id }}"
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
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.btn-delete-category').forEach(btn => {
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
</script>
@endpush