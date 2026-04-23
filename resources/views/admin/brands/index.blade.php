@extends('layouts.admin')

@section('title', 'Danh sách thương hiệu')

@section('content')
<style>
    .brand-page{
        font-size:14px;
        color:#334155;
    }

    .brand-card{
        border-radius:16px;
        overflow:hidden;
        border:1px solid #edf2f7;
    }

    .brand-title{
        font-size:18px;
        font-weight:600;
        color:#1e293b;
    }

    .brand-subtext{
        font-size:13px;
        color:#64748b;
    }

    .brand-btn{
        font-size:13px;
        font-weight:500;
        border-radius:10px;
        padding:8px 14px;
    }

    .brand-filter-box{
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

    .brand-table-wrap{
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

    .brand-code{
        color:#64748b;
        font-weight:600;
        font-size:13px;
    }

    .brand-image{
        width:56px;
        height:56px;
        object-fit:contain;
        background:#fff;
        border:1px solid #e2e8f0;
        border-radius:12px;
        padding:4px;
    }

    .brand-image-empty{
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

    .brand-name{
        font-weight:500;
        color:#1e293b;
        line-height:1.5;
    }

    .brand-date{
        color:#64748b;
        font-size:13px;
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
        background:#dcfce7;
        color:#15803d;
        border:1px solid #bbf7d0;
    }

    .product-empty{
        background:#e2e8f0;
        color:#475569;
        border:1px solid #cbd5e1;
    }

    .brand-action-group{
        display:flex;
        align-items:center;
        justify-content:center;
        gap:6px;
        flex-wrap:wrap;
    }

    .brand-icon-btn{
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
        .brand-title{
            font-size:16px;
        }

        .brand-filter-box{
            padding:12px;
        }

        .table thead th,
        .table tbody td{
            padding:10px;
        }
    }
</style>

<div class="brand-page">
    <div class="card border-0 shadow-sm brand-card">
        <div class="card-body p-3 p-md-4">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h5 class="brand-title mb-1">Danh sách thương hiệu</h5>
                    <div class="brand-subtext">Quản lý các thương hiệu trong hệ thống</div>
                </div>

                <a href="{{ route('admin.brands.create') }}" class="btn btn-primary brand-btn">
                    <i class="bi bi-plus-lg me-1"></i>
                    Thêm thương hiệu
                </a>
            </div>

            <div class="brand-filter-box">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <input
                            type="text"
                            name="keyword"
                            value="{{ request('keyword') }}"
                            class="form-control"
                            placeholder="Tìm theo tên thương hiệu hoặc mã..."
                        >
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
                        <button class="btn btn-outline-primary brand-btn">
                            <i class="bi bi-search me-1"></i>
                            Lọc
                        </button>

                        <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary brand-btn">
                            Đặt lại
                        </a>
                    </div>
                </form>
            </div>

            <div class="table-responsive brand-table-wrap">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:180px">Mã thương hiệu</th>
                            <th class="text-center" style="width:130px">Ảnh</th>
                            <th>Tên thương hiệu</th>
                            <th class="text-center" style="width:190px">Ngày tạo</th>
                            <th class="text-center" style="width:150px">Số sản phẩm</th>
                            <th class="text-center" style="width:170px">Hành động</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($brands as $brand)
                            <tr>
                                <td class="text-center">
                                    <span class="brand-code">
                                        TH{{ str_pad($brand->id, 4, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    @if($brand->image)
                                        <img src="{{ asset('storage/' . $brand->image) }}"
                                             alt="{{ $brand->name }}"
                                             class="brand-image">
                                    @else
                                        <div class="brand-image-empty">
                                            No img
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <div class="brand-name">
                                        {{ $brand->name }}
                                    </div>
                                </td>

                                <td class="text-center">
                                    <div class="brand-date">
                                        {{ optional($brand->created_at)->format('d/m/Y') ?? '—' }}
                                        <div>{{ optional($brand->created_at)->format('H:i') }}</div>
                                    </div>
                                </td>

                                <td class="text-center">
                                    @php
                                        $productsCount = $brand->products_count ?? 0;
                                    @endphp

                                    <span class="product-badge {{ $productsCount > 0 ? 'product-has' : 'product-empty' }}">
                                        {{ $productsCount }}
                                    </span>
                                </td>

                                <td class="text-center">
                                    <div class="brand-action-group">
                                        <a href="{{ route('admin.brands.edit', $brand) }}"
                                           class="btn btn-sm btn-outline-secondary brand-icon-btn"
                                           title="Chỉnh sửa">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <form method="POST"
                                              id="delete-brand-{{ $brand->id }}"
                                              action="{{ route('admin.brands.destroy', $brand) }}"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')

                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger brand-icon-btn btn-delete-brand"
                                                    data-id="{{ $brand->id }}"
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
                                    Chưa có thương hiệu nào
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($brands instanceof \Illuminate\Contracts\Pagination\Paginator || $brands instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                <div class="mt-4">
                    {{ $brands->links('vendor.pagination.custom-blue') }}
                </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.btn-delete-brand').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;

        Swal.fire({
            title: 'Xóa thương hiệu?',
            text: 'Hành động này không thể hoàn tác',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Hủy',
            confirmButtonText: 'Xóa'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-brand-' + id).submit();
            }
        });
    });
});
</script>
@endpush