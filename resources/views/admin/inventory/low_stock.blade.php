@extends('layouts.admin')

@section('title','Sản phẩm sắp hết hàng')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">Sản phẩm sắp hết hàng</h5>
                <small class="text-muted">
                    Danh sách các biến thể có tồn kho thấp
                </small>
            </div>

            <span class="badge bg-danger">
                {{ $variants->total() }} biến thể
            </span>
        </div>

        <form method="GET" class="row g-2 mb-4">
            <div class="col-md-4">
                <input
                    type="text"
                    name="keyword"
                    value="{{ request('keyword') }}"
                    class="form-control form-control-sm"
                    placeholder="Tìm tên sản phẩm, mã SP, mã biến thể..."
                >
            </div>

            <div class="col-md-3">
                <select name="sort" class="form-select form-select-sm">
                    <option value="">Tồn kho thấp → cao</option>

                    <option value="high" {{ request('sort') == 'high' ? 'selected' : '' }}>
                        Tồn kho cao → thấp
                    </option>

                    <option value="all" {{ request('sort') == 'all' ? 'selected' : '' }}>
                        Hiển thị tất cả
                    </option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-search"></i>
                    Lọc
                </button>

                <a href="{{ route('admin.inventory.low') }}"
                   class="btn btn-outline-secondary btn-sm">
                    Đặt lại
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">
                    <tr>
                        <th style="width:90px" class="text-center">Ảnh</th>
                        <th>Sản phẩm</th>
                        <th style="width:160px">Mã biến thể</th>
                        <th>Biến thể</th>
                        <th style="width:120px" class="text-center">Tồn kho</th>
                        <th style="width:140px" class="text-center">Trạng thái</th>
                        <th style="width:150px" class="text-center">Hành động</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($variants as $v)
                        <tr>

                            <td class="text-center">
                                @php
                                    $img = $v->images->first()->image_path ?? null;
                                @endphp

                                @if($img)
                                    <img
                                        src="{{ asset('storage/' . $img) }}"
                                        width="55"
                                        height="55"
                                        class="rounded border"
                                        style="object-fit:cover"
                                        alt="{{ $v->product->name ?? 'Biến thể sản phẩm' }}"
                                    >
                                @else
                                    <div
                                        class="bg-light border rounded d-inline-flex align-items-center justify-content-center"
                                        style="width:55px;height:55px;"
                                    >
                                        <i class="bi bi-image text-muted"></i>
                                    </div>
                                @endif
                            </td>

                            <td>
                                <div class="fw-medium">
                                    {{ $v->product->name ?? '-' }}
                                </div>

                                <small class="text-muted">
                                    @if($v->product)
                                        SP{{ str_pad($v->product->id, 5, '0', STR_PAD_LEFT) }}
                                    @else
                                        -
                                    @endif
                                </small>
                            </td>

                            <td class="text-muted fw-semibold">
                                BT{{ str_pad($v->id, 5, '0', STR_PAD_LEFT) }}
                            </td>

                            <td>
                                <div class="fw-medium">
                                    {{ $v->attribute_value ?? '-' }}
                                </div>

                                @if($v->attribute_name)
                                    <small class="text-muted">
                                        {{ $v->attribute_name }}
                                    </small>
                                @endif
                            </td>

                            <td class="text-center">
                                <span class="badge
                                    @if($v->stock_quantity <= 2)
                                        bg-danger
                                    @elseif($v->stock_quantity <= 5)
                                        bg-warning text-dark
                                    @else
                                        bg-success
                                    @endif
                                ">
                                    {{ $v->stock_quantity }}
                                </span>
                            </td>

                            <td class="text-center">
                                @if($v->stock_quantity <= 2)
                                    <span class="badge bg-danger">
                                        Nguy hiểm
                                    </span>
                                @elseif($v->stock_quantity <= 5)
                                    <span class="badge bg-warning text-dark">
                                        Sắp hết
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        Ổn định
                                    </span>
                                @endif
                            </td>

                            <td class="text-center">
                                <a href="{{ route('admin.stock.create', ['variant' => $v->id]) }}"
                                   class="btn btn-sm btn-primary">
                                    <i class="bi bi-box-arrow-in-down me-1"></i>
                                    Nhập thêm
                                </a>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox me-1"></i>
                                Không có sản phẩm sắp hết hàng
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        @if($variants->hasPages())
            <div class="mt-4">
                {{ $variants->links('vendor.pagination.custom-blue') }}
            </div>
        @endif

    </div>
</div>

@endsection