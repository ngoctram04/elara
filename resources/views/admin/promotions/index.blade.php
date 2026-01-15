@extends('layouts.admin')

@section('title', 'Quản lý khuyến mãi')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-semibold mb-0">Danh sách khuyến mãi</h5>

            <a href="{{ route('admin.promotions.choose') }}"
               class="btn btn-primary btn-sm">
                + Thêm khuyến mãi
            </a>
        </div>

        {{-- TABLE --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Tên</th>
                        <th>Loại</th>
                        <th>Giảm</th>
                        <th>Thời gian</th>
                        <th>Trạng thái</th>
                        <th>Tình trạng</th>
                        <th width="160">Hành động</th>
                    </tr>
                </thead>

                <tbody>
                @forelse ($promotions as $promo)
                    <tr>
                        <td>{{ $promo->id }}</td>

                        {{-- NAME --}}
                        <td>
                            <strong>{{ $promo->name }}</strong><br>
                            @if ($promo->type === 'order' && $promo->code)
                                <span class="badge bg-info">
                                    {{ $promo->code }}
                                </span>
                            @endif
                        </td>

                        {{-- TYPE --}}
                        <td>
                            <span class="badge bg-secondary">
                                {{ $promo->type === 'order' ? 'Đơn hàng' : 'Sản phẩm' }}
                            </span>
                        </td>

                        {{-- DISCOUNT (ONLY %) --}}
                        <td>
                            <span class="fw-semibold text-danger">
                                -{{ $promo->discount_value }}%
                            </span>
                        </td>

                        {{-- DATE --}}
                        <td>
                            {{ $promo->start_date->format('d/m/Y') }}<br>
                            → {{ $promo->end_date->format('d/m/Y') }}
                        </td>

                        {{-- ACTIVE STATUS --}}
                        <td>
                            <span class="badge {{ $promo->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $promo->is_active ? 'Đang bật' : 'Đã tắt' }}
                            </span>
                        </td>

                        {{-- TIME STATUS --}}
                        <td>
                            <span class="badge bg-{{ $promo->time_status_color }}">
                                {{ $promo->time_status_label }}
                            </span>
                        </td>

                        {{-- ACTION --}}
                        <td>
                            <a href="{{ route('admin.promotions.edit', $promo) }}"
                               class="btn btn-warning btn-sm mb-1">
                                Sửa
                            </a>

                            <form action="{{ route('admin.promotions.toggle', $promo) }}"
                                  method="POST"
                                  class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-secondary btn-sm">
                                    {{ $promo->is_active ? 'Tắt' : 'Bật' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            Chưa có khuyến mãi nào
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-3">
            {{ $promotions->links() }}
        </div>

    </div>
</div>
@endsection
