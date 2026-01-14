@extends('layouts.admin')

@section('title','Chi tiết sản phẩm')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between mb-3">
            <h5 class="fw-semibold mb-0">Chi tiết sản phẩm</h5>

            <a href="{{ route('admin.products.edit', $product) }}"
               class="btn btn-warning btn-sm text-white">
                <i class="bi bi-pencil"></i> Chỉnh sửa
            </a>
        </div>

        <div class="row">
            {{-- LEFT --}}
            <div class="col-md-6">
                <p><strong>Tên sản phẩm:</strong> {{ $product->name }}</p>

                <p>
                    <strong>Danh mục:</strong>
                    {{ $product->category?->parent?->name }}
                    @if($product->category?->parent)
                        →
                    @endif
                    {{ $product->category?->name }}
                </p>

                <p><strong>Thương hiệu:</strong> {{ $product->brand?->name }}</p>

                <p>
                    <strong>Trạng thái:</strong>
                    @if($product->is_active)
                        <span class="badge bg-success">Còn hàng</span>
                    @else
                        <span class="badge bg-secondary">Ẩn</span>
                    @endif
                </p>

                <p>
                    <strong>Nổi bật:</strong>
                    {{ $product->is_featured ? 'Có' : 'Không' }}
                </p>
            </div>

            {{-- RIGHT --}}
            <div class="col-md-6">
                <strong>Ảnh sản phẩm:</strong>
                <div class="d-flex gap-2 flex-wrap mt-2">
                    @forelse($product->images as $img)
                        <img src="{{ $img->url }}"
                             width="90"
                             class="rounded border">
                    @empty
                        <span class="text-muted">Chưa có ảnh</span>
                    @endforelse
                </div>
            </div>
        </div>

        <hr>

        {{-- ================= BIẾN THỂ ================= --}}
        @php
            $attributeName = $product->variants->first()?->attribute_name;
        @endphp

        <h6 class="fw-semibold text-primary">
            Biến thể
            @if($attributeName)
                <span class="text-muted">
                    ({{ $attributeName }})
                </span>
            @endif
        </h6>

        <div class="table-responsive mt-3">
            <table class="table table-bordered align-middle">
                <thead class="table-light text-center">
                <tr>
                    <th width="60">#</th>
                    <th>Giá trị</th>
                    <th width="120">Giá</th>
                    <th width="100">Tồn kho</th>
                    <th width="100">Đã bán</th>
                    <th width="100">Ảnh</th>
                </tr>
                </thead>
                <tbody>
                @forelse($product->variants as $index => $variant)
                    <tr class="text-center">
                        <td>{{ $index + 1 }}</td>

                        <td class="fw-semibold">
                            {{ $variant->attribute_value }}
                        </td>

                        <td class="text-end">
                            {{ number_format($variant->price) }}đ
                        </td>

                        <td>{{ $variant->stock }}</td>

                        <td>{{ $variant->sold_quantity }}</td>

                        <td>
                            @if($variant->images->first())
                                <img src="{{ $variant->images->first()->url }}"
                                     width="60"
                                     class="rounded border">
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6"
                            class="text-center text-muted py-4">
                            Chưa có biến thể nào
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <a href="{{ route('admin.products.index') }}"
               class="btn btn-secondary btn-sm">
                Quay lại danh sách
            </a>
        </div>

    </div>
</div>
@endsection
