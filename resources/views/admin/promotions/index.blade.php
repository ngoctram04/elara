@extends('layouts.admin')

@section('title', 'Quản lý khuyến mãi')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0">
                <i class="bi bi-megaphone me-2"></i> Quản lý khuyến mãi
            </h5>

            <div>
                <a href="{{ route('admin.promotions.create') }}"
                   class="btn btn-primary btn-sm me-2">
                    <i class="bi bi-plus-circle"></i> Thêm khuyến mãi
                </a>

                <a href="{{ route('admin.promotions.createReward') }}"
                   class="btn btn-success btn-sm">
                    <i class="bi bi-gift"></i> Voucher đổi điểm
                </a>
            </div>
        </div>

        {{-- ================= KHUYẾN MÃI THƯỜNG ================= --}}
        <h6 class="fw-semibold mb-3 text-primary">
            <i class="bi bi-tag"></i> Khuyến mãi hệ thống
        </h6>

        <div class="table-responsive mb-5">
            <table class="table table-hover align-middle border">
                <thead class="table-light text-center">
                    <tr>
                        <th width="60">#</th>
                        <th>Tên</th>
                        <th width="120">Loại</th>
                        <th width="100">Giảm</th>
                        <th width="180">Thời gian</th>
                        <th width="120">Trạng thái</th>
                        <th width="130">Tình trạng</th>
                        <th width="150">Hành động</th>
                    </tr>
                </thead>

                <tbody>
                @forelse ($promotions as $promo)
                    <tr>
                        <td class="text-center">{{ $promo->id }}</td>

                        <td>
                            <strong>{{ $promo->name }}</strong>
                            @if ($promo->code)
                                <br>
                                <span class="badge bg-info mt-1">
                                    {{ $promo->code }}
                                </span>
                            @endif
                        </td>

                        <td class="text-center">
                            <span class="badge bg-secondary">
                                {{ $promo->type === 'order' ? 'Đơn hàng' : 'Sản phẩm' }}
                            </span>
                        </td>

                        <td class="text-center text-danger fw-semibold">
                            -{{ $promo->discount_value }}
                            {{ $promo->discount_type === 'percent' ? '%' : 'đ' }}
                        </td>

                        <td class="small text-center">
                            {{ \Carbon\Carbon::parse($promo->start_date)->format('d/m/Y') }}
                            <br>
                            →
                            {{ \Carbon\Carbon::parse($promo->end_date)->format('d/m/Y') }}
                        </td>

                        <td class="text-center">
                            <span class="badge {{ $promo->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $promo->is_active ? 'Đang bật' : 'Đã tắt' }}
                            </span>
                        </td>

                        <td class="text-center">
                            @php
                                $now = now();
                                if ($now->lt($promo->start_date)) {
                                    $color = 'secondary';
                                    $label = 'Chưa bắt đầu';
                                } elseif ($now->gt($promo->end_date)) {
                                    $color = 'dark';
                                    $label = 'Đã hết hạn';
                                } else {
                                    $color = 'success';
                                    $label = 'Đang diễn ra';
                                }
                            @endphp

                            <span class="badge bg-{{ $color }}">
                                {{ $label }}
                            </span>
                        </td>

                        <td class="text-center">
                            <a href="{{ route('admin.promotions.edit', $promo->id) }}"
                               class="btn btn-warning btn-sm mb-1">
                                <i class="bi bi-pencil-square"></i>
                            </a>

                            <form action="{{ route('admin.promotions.toggle', $promo->id) }}"
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
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-inbox"></i>
                            <br> Chưa có khuyến mãi nào
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $promotions->links() }}
            </div>
        </div>

        {{-- ================= VOUCHER ĐỔI ĐIỂM ================= --}}
        <h6 class="fw-semibold mb-3 text-success">
            <i class="bi bi-gift"></i> Voucher đổi điểm
        </h6>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th width="60">#</th>
                        <th>Tên</th>
                        <th width="150">Điểm cần</th>
                        <th width="120">Giảm</th>
                        <th width="120">Hiệu lực</th>
                    </tr>
                </thead>

                <tbody>
                @forelse ($rewards as $reward)
                    <tr>
                        <td class="text-center">{{ $reward->id }}</td>
                        <td>{{ $reward->title }}</td>
                        <td class="text-center text-primary fw-semibold">
                            {{ number_format($reward->points_required) }} điểm
                        </td>
                        <td class="text-center text-danger">
                            -{{ $reward->discount_value }}
                            {{ $reward->discount_type === 'percent' ? '%' : 'đ' }}
                        </td>
                        <td class="text-center">
                            {{ $reward->valid_days }} ngày
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="bi bi-inbox"></i>
                            <br> Chưa có voucher đổi điểm
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $rewards->links() }}
            </div>
        </div>

    </div>
</div>
@endsection