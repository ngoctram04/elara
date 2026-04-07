@extends('layouts.admin')

@section('title', 'Quản lý khuyến mãi')

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-body">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h5 class="mb-1">Quản lý khuyến mãi</h5>
                <small class="text-muted">
                    Quản lý các chương trình khuyến mãi và voucher đổi điểm
                </small>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.promotions.create') }}"
                   class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>
                    Thêm khuyến mãi
                </a>

                <a href="{{ route('admin.promotions.createReward') }}"
                   class="btn btn-success btn-sm">
                    <i class="bi bi-gift me-1"></i>
                    Voucher đổi điểm
                </a>
            </div>
        </div>

        {{-- ================= KHUYẾN MÃI HỆ THỐNG ================= --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h6 class="mb-1 text-primary">Khuyến mãi hệ thống</h6>
                <small class="text-muted">Danh sách khuyến mãi áp dụng cho đơn hàng hoặc sản phẩm</small>
            </div>
        </div>

        {{-- FILTER KHUYẾN MÃI --}}
        <form method="GET" class="row g-2 mb-4 align-items-center">
            <input type="hidden" name="reward_search" value="{{ request('reward_search') }}">
            <input type="hidden" name="reward_status" value="{{ request('reward_status') }}">
            <input type="hidden" name="reward_sort" value="{{ request('reward_sort', 'new') }}">

            <div class="col-md-3">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    class="form-control form-control-sm"
                    placeholder="Tìm tên hoặc mã khuyến mãi..."
                >
            </div>

            <div class="col-md-2">
                <select name="type" class="form-select form-select-sm">
                    <option value="">-- Tất cả loại --</option>
                    <option value="order" {{ request('type') == 'order' ? 'selected' : '' }}>Đơn hàng</option>
                    <option value="product" {{ request('type') == 'product' ? 'selected' : '' }}>Sản phẩm</option>
                </select>
            </div>

            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Bật / tắt --</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang bật</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Đã tắt</option>
                </select>
            </div>

            <div class="col-md-2">
                <select name="progress" class="form-select form-select-sm">
                    <option value="">-- Tình trạng --</option>
                    <option value="upcoming" {{ request('progress') == 'upcoming' ? 'selected' : '' }}>Chưa bắt đầu</option>
                    <option value="ongoing" {{ request('progress') == 'ongoing' ? 'selected' : '' }}>Đang diễn ra</option>
                    <option value="expired" {{ request('progress') == 'expired' ? 'selected' : '' }}>Đã hết hạn</option>
                </select>
            </div>

            <div class="col-md-2">
                <select name="sort" class="form-select form-select-sm">
                    <option value="new" {{ request('sort', 'new') == 'new' ? 'selected' : '' }}>Mới nhất</option>
                    <option value="old" {{ request('sort') == 'old' ? 'selected' : '' }}>Cũ nhất</option>
                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Tên A - Z</option>
                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Tên Z - A</option>
                    <option value="discount_desc" {{ request('sort') == 'discount_desc' ? 'selected' : '' }}>Giảm cao nhất</option>
                    <option value="discount_asc" {{ request('sort') == 'discount_asc' ? 'selected' : '' }}>Giảm thấp nhất</option>
                </select>
            </div>

            <div class="col-md-1 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-search me-1"></i>Lọc
                </button>
            </div>

            <div class="col-md-12">
                <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline-secondary btn-sm">
                    Đặt lại bộ lọc
                </a>
            </div>
        </form>

        <div class="table-responsive mb-5">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-center">
                    <tr>
                        <th style="width:70px">Mã</th>
                        <th class="text-start">Tên</th>
                        <th style="width:120px">Loại</th>
                        <th style="width:120px">Giảm</th>
                        <th style="width:180px">Thời gian</th>
                        <th style="width:120px">Trạng thái</th>
                        <th style="width:140px">Tình trạng</th>
                        <th style="width:160px">Hành động</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($promotions as $promo)
                        @php
                            $now = now();

                            if ($now->lt($promo->start_date)) {
                                $progressClass = 'text-secondary border bg-light';
                                $progressLabel = 'Chưa bắt đầu';
                            } elseif ($now->gt($promo->end_date)) {
                                $progressClass = 'text-dark border bg-secondary-subtle';
                                $progressLabel = 'Đã hết hạn';
                            } else {
                                $progressClass = 'text-success border bg-success-subtle';
                                $progressLabel = 'Đang diễn ra';
                            }
                        @endphp

                        <tr>
                            <td class="text-center text-muted">
                                KM{{ str_pad($promo->id, 4, '0', STR_PAD_LEFT) }}
                            </td>

                            <td class="text-start">
                                <div>{{ $promo->name }}</div>

                                @if ($promo->code)
                                    <div class="mt-1">
                                        <span class="badge text-info border bg-info-subtle">
                                            {{ $promo->code }}
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <td class="text-center">
                                <span class="badge text-secondary border bg-light">
                                    {{ $promo->type === 'order' ? 'Đơn hàng' : 'Sản phẩm' }}
                                </span>
                            </td>

                            <td class="text-center">
                                <span class="text-danger">
                                    -{{ rtrim(rtrim(number_format($promo->discount_value, 2, '.', ''), '0'), '.') }}
                                    {{ $promo->discount_type === 'percent' ? '%' : 'đ' }}
                                </span>
                            </td>

                            <td class="small text-center text-muted">
                                {{ \Carbon\Carbon::parse($promo->start_date)->format('d/m/Y') }}
                                <br>
                                →
                                {{ \Carbon\Carbon::parse($promo->end_date)->format('d/m/Y') }}
                            </td>

                            <td class="text-center">
                                @if($promo->is_active)
                                    <span class="badge text-success border bg-success-subtle">
                                        Đang bật
                                    </span>
                                @else
                                    <span class="badge text-danger border bg-danger-subtle">
                                        Đã tắt
                                    </span>
                                @endif
                            </td>

                            <td class="text-center">
                                <span class="badge {{ $progressClass }}">
                                    {{ $progressLabel }}
                                </span>
                            </td>

                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2 flex-wrap">
                                    <a href="{{ route('admin.promotions.edit', $promo->id) }}"
                                       class="btn btn-outline-warning btn-sm"
                                       title="Chỉnh sửa">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form action="{{ route('admin.promotions.toggle', $promo->id) }}"
                                          method="POST"
                                          class="d-inline">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                                            {{ $promo->is_active ? 'Tắt' : 'Bật' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Chưa có khuyến mãi nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($promotions->hasPages())
                <div class="mt-4">
                    {{ $promotions->appends(request()->query())->links('vendor.pagination.custom-blue') }}
                </div>
            @endif
        </div>

        {{-- ================= VOUCHER ĐỔI ĐIỂM ================= --}}
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h6 class="mb-1 text-success">Voucher đổi điểm</h6>
                <small class="text-muted">Danh sách voucher khách hàng có thể đổi bằng điểm</small>
            </div>
        </div>

        {{-- FILTER VOUCHER --}}
        <form method="GET" class="row g-2 mb-4 align-items-center">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="type" value="{{ request('type') }}">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <input type="hidden" name="progress" value="{{ request('progress') }}">
            <input type="hidden" name="sort" value="{{ request('sort', 'new') }}">

            <div class="col-md-4">
                <input
                    type="text"
                    name="reward_search"
                    value="{{ request('reward_search') }}"
                    class="form-control form-control-sm"
                    placeholder="Tìm tên voucher..."
                >
            </div>

            <div class="col-md-3">
                <select name="reward_status" class="form-select form-select-sm">
                    <option value="">-- Trạng thái voucher --</option>
                    <option value="active" {{ request('reward_status') == 'active' ? 'selected' : '' }}>Đang bật</option>
                    <option value="inactive" {{ request('reward_status') == 'inactive' ? 'selected' : '' }}>Đã tắt</option>
                </select>
            </div>

            <div class="col-md-3">
                <select name="reward_sort" class="form-select form-select-sm">
                    <option value="new" {{ request('reward_sort', 'new') == 'new' ? 'selected' : '' }}>Mới nhất</option>
                    <option value="old" {{ request('reward_sort') == 'old' ? 'selected' : '' }}>Cũ nhất</option>
                    <option value="points_desc" {{ request('reward_sort') == 'points_desc' ? 'selected' : '' }}>Điểm cao nhất</option>
                    <option value="points_asc" {{ request('reward_sort') == 'points_asc' ? 'selected' : '' }}>Điểm thấp nhất</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-search me-1"></i>Lọc
                </button>

                <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline-secondary btn-sm">
                    Đặt lại
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-center">
                    <tr>
                        <th class="text-start">Tên</th>
                        <th style="width:150px">Điểm cần</th>
                        <th style="width:120px">Giảm</th>
                        <th style="width:120px">Hiệu lực</th>
                        <th style="width:120px">Trạng thái</th>
                        <th style="width:140px">Hành động</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($rewards as $reward)
                        <tr>

                            <td class="text-start">
                                {{ $reward->title }}
                            </td>

                            <td class="text-center">
                                <span class="text-primary">
                                    {{ number_format($reward->points_required, 0, ',', '.') }} điểm
                                </span>
                            </td>

                            <td class="text-center">
                                <span class="text-danger">
                                    -{{ rtrim(rtrim(number_format($reward->discount_value, 2, '.', ''), '0'), '.') }}
                                    {{ $reward->discount_type === 'percent' ? '%' : 'đ' }}
                                </span>
                            </td>

                            <td class="text-center text-muted">
                                {{ $reward->valid_days }} ngày
                            </td>

                            <td class="text-center">
                                @if($reward->is_active)
                                    <span class="badge text-success border bg-success-subtle">
                                        Đang bật
                                    </span>
                                @else
                                    <span class="badge text-danger border bg-danger-subtle">
                                        Đã tắt
                                    </span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2 flex-wrap">
                                    <a href="{{ route('admin.promotions.editReward', $reward->id) }}"
                                       class="btn btn-outline-warning btn-sm"
                                       title="Chỉnh sửa voucher">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form action="{{ route('admin.promotions.toggleReward', $reward->id) }}"
                                          method="POST"
                                          class="d-inline">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                                            {{ $reward->is_active ? 'Tắt' : 'Bật' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Chưa có voucher đổi điểm
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($rewards->hasPages())
                <div class="mt-4">
                    {{ $rewards->appends(request()->query())->links('vendor.pagination.custom-blue') }}
                </div>
            @endif
        </div>

    </div>
</div>

@endsection