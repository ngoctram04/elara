@extends('layouts.admin')

@section('title', 'Quản lý khuyến mãi')

@section('content')
@php
    use Carbon\Carbon;

    $now = now();

    $totalPromotions = $promotions instanceof \Illuminate\Pagination\AbstractPaginator ? $promotions->total() : $promotions->count();
    $totalRewards = $rewards instanceof \Illuminate\Pagination\AbstractPaginator ? $rewards->total() : $rewards->count();

    $activePromoCount = collect($promotions instanceof \Illuminate\Pagination\AbstractPaginator ? $promotions->items() : $promotions)
        ->where('is_active', 1)
        ->count();

    $activeRewardCount = collect($rewards instanceof \Illuminate\Pagination\AbstractPaginator ? $rewards->items() : $rewards)
        ->where('is_active', 1)
        ->count();

    $tab = request('tab', 'promotions');
@endphp

<style>
    .promotion-page .main-card {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        overflow: hidden;
        background: #fff;
    }

    .promotion-page .page-title {
        font-size: 22px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 4px;
    }

    .promotion-page .page-subtitle {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 0;
    }

    .promotion-page .summary-card {
        border: 1px solid #eef2f7;
        border-radius: 16px;
        background: #fff;
        padding: 16px;
        height: 100%;
        transition: 0.2s ease;
    }

    .promotion-page .summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
    }

    .promotion-page .summary-label {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 8px;
    }

    .promotion-page .summary-value {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
        line-height: 1;
    }

    .promotion-page .summary-note {
        font-size: 12px;
        color: #9ca3af;
        margin-top: 6px;
    }

    .promotion-page .filter-card,
    .promotion-page .table-card {
        border: 1px solid #eef2f7;
        border-radius: 16px;
        background: #fff;
    }

    .promotion-page .filter-card {
        padding: 16px;
    }

    .promotion-page .table-card {
        overflow: hidden;
    }

    .promotion-page .nav-tabs {
        border-bottom: 1px solid #e5e7eb;
        gap: 8px;
    }

    .promotion-page .nav-tabs .nav-link {
        border: 0;
        color: #6b7280;
        font-weight: 600;
        border-radius: 12px 12px 0 0;
        padding: 12px 16px;
    }

    .promotion-page .nav-tabs .nav-link.active {
        background: #eff6ff;
        color: #2563eb;
        border-bottom: 2px solid #2563eb;
    }

    .promotion-page .section-title {
        font-size: 16px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 4px;
    }

    .promotion-page .section-subtitle {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 0;
    }

    .promotion-page .form-control,
    .promotion-page .form-select {
        border-radius: 12px;
        min-height: 44px;
        border-color: #dbe2ea;
    }

    .promotion-page .filter-row {
        align-items: center;
    }

    .promotion-page .filter-actions {
        display: flex;
        gap: 8px;
        align-items: center;
        justify-content: flex-end;
    }

    .promotion-page .icon-btn {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        font-size: 18px;
    }

    .promotion-page .table thead th {
        font-size: 13px;
        font-weight: 700;
        color: #374151;
        white-space: nowrap;
        vertical-align: middle;
    }

    .promotion-page .table tbody td {
        font-size: 14px;
        vertical-align: middle;
    }

    .promotion-page .promo-name,
    .promotion-page .reward-title {
        font-weight: 600;
        color: #111827;
    }

    .promotion-page .sub-text {
        font-size: 12px;
        color: #6b7280;
    }

    .promotion-page .badge-soft {
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 600;
    }

    .promotion-page .discount-text {
        font-weight: 700;
        color: #dc2626;
    }

    .promotion-page .code-badge {
        display: inline-block;
        font-size: 12px;
        font-weight: 600;
        color: #0c4a6e;
        background: #e0f2fe;
        border: 1px solid #bae6fd;
        border-radius: 999px;
        padding: 4px 10px;
        margin-top: 6px;
    }

    .promotion-page .action-group {
        display: flex;
        justify-content: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .promotion-page .empty-state {
        padding: 36px 16px;
        text-align: center;
        color: #6b7280;
    }

    .promotion-page .tab-pane-inner {
        padding-top: 20px;
    }

    .promotion-page .mini-stat {
        font-size: 12px;
        color: #6b7280;
    }

    @media (max-width: 1199.98px) {
        .promotion-page .filter-actions {
            justify-content: flex-start;
        }
    }

    @media (max-width: 991.98px) {
        .promotion-page .filter-actions {
            width: 100%;
        }
    }

    @media (max-width: 768px) {
        .promotion-page .page-title {
            font-size: 20px;
        }

        .promotion-page .summary-value {
            font-size: 20px;
        }
    }
</style>

<div class="promotion-page">
    <div class="card main-card">
        <div class="card-body p-4">

            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                <div>
                    <h4 class="page-title">Quản lý khuyến mãi</h4>
                    <p class="page-subtitle">
                        Theo dõi khuyến mãi hệ thống và voucher đổi điểm dành cho khách hàng
                    </p>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Thêm khuyến mãi
                    </a>

                    <a href="{{ route('admin.promotions.createReward') }}" class="btn btn-success">
                        <i class="bi bi-gift me-1"></i> Tạo voucher đổi điểm
                    </a>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="summary-card">
                        <div class="summary-label">Tổng khuyến mãi hệ thống</div>
                        <div class="summary-value">{{ number_format($totalPromotions) }}</div>
                        <div class="summary-note">Bao gồm tất cả chương trình giảm giá</div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="summary-card">
                        <div class="summary-label">Khuyến mãi đang bật</div>
                        <div class="summary-value text-primary">{{ number_format($activePromoCount) }}</div>
                        <div class="summary-note">Đang cho phép áp dụng</div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="summary-card">
                        <div class="summary-label">Voucher đổi điểm</div>
                        <div class="summary-value text-success">{{ number_format($totalRewards) }}</div>
                        <div class="summary-note">Mẫu voucher khách có thể đổi</div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="summary-card">
                        <div class="summary-label">Voucher đang bật</div>
                        <div class="summary-value text-warning">{{ number_format($activeRewardCount) }}</div>
                        <div class="summary-note">Sẵn sàng cho khách đổi điểm</div>
                    </div>
                </div>
            </div>

            <ul class="nav nav-tabs mb-0" id="promotionTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $tab === 'promotions' ? 'active' : '' }}"
                            id="promotions-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#promotions-pane"
                            type="button"
                            role="tab"
                            aria-controls="promotions-pane"
                            aria-selected="{{ $tab === 'promotions' ? 'true' : 'false' }}">
                        <i class="bi bi-tags me-1"></i> Khuyến mãi hệ thống
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $tab === 'rewards' ? 'active' : '' }}"
                            id="rewards-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#rewards-pane"
                            type="button"
                            role="tab"
                            aria-controls="rewards-pane"
                            aria-selected="{{ $tab === 'rewards' ? 'true' : 'false' }}">
                        <i class="bi bi-gift me-1"></i> Voucher đổi điểm
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade {{ $tab === 'promotions' ? 'show active' : '' }}"
                     id="promotions-pane"
                     role="tabpanel"
                     aria-labelledby="promotions-tab">
                    <div class="tab-pane-inner">
                        <div class="filter-card mb-3">
                            <div class="mb-3">
                                <h6 class="section-title">Bộ lọc khuyến mãi hệ thống</h6>
                                <p class="section-subtitle">Lọc theo tên, loại, trạng thái hoạt động và thời gian áp dụng</p>
                            </div>

                            <form method="GET">
                                <input type="hidden" name="tab" value="promotions">
                                <input type="hidden" name="reward_search" value="{{ request('reward_search') }}">
                                <input type="hidden" name="reward_status" value="{{ request('reward_status') }}">
                                <input type="hidden" name="reward_progress" value="{{ request('reward_progress') }}">
                                <input type="hidden" name="reward_sort" value="{{ request('reward_sort', 'new') }}">

                                <div class="row g-2 filter-row">
                                    <div class="col-xl-3 col-lg-6">
                                        <input type="text"
                                               name="search"
                                               value="{{ request('search') }}"
                                               class="form-control"
                                               placeholder="Tìm tên hoặc mã khuyến mãi...">
                                    </div>

                                    <div class="col-xl-2 col-lg-6">
                                        <select name="type" class="form-select">
                                            <option value="">-- Tất cả loại --</option>
                                            <option value="order" {{ request('type') == 'order' ? 'selected' : '' }}>Đơn hàng</option>
                                            <option value="product" {{ request('type') == 'product' ? 'selected' : '' }}>Sản phẩm</option>
                                        </select>
                                    </div>

                                    <div class="col-xl-2 col-lg-6">
                                        <select name="status" class="form-select">
                                            <option value="">-- Bật / tắt --</option>
                                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang bật</option>
                                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Đã tắt</option>
                                        </select>
                                    </div>

                                    <div class="col-xl-2 col-lg-6">
                                        <select name="progress" class="form-select">
                                            <option value="">-- Tình trạng --</option>
                                            <option value="upcoming" {{ request('progress') == 'upcoming' ? 'selected' : '' }}>Chưa bắt đầu</option>
                                            <option value="ongoing" {{ request('progress') == 'ongoing' ? 'selected' : '' }}>Đang diễn ra</option>
                                            <option value="expired" {{ request('progress') == 'expired' ? 'selected' : '' }}>Đã hết hạn</option>
                                        </select>
                                    </div>

                                    <div class="col-xl-2 col-lg-6">
                                        <select name="sort" class="form-select">
                                            <option value="new" {{ request('sort', 'new') == 'new' ? 'selected' : '' }}>Mới nhất</option>
                                            <option value="old" {{ request('sort') == 'old' ? 'selected' : '' }}>Cũ nhất</option>
                                            <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Tên A - Z</option>
                                            <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Tên Z - A</option>
                                            <option value="discount_desc" {{ request('sort') == 'discount_desc' ? 'selected' : '' }}>Giảm cao nhất</option>
                                            <option value="discount_asc" {{ request('sort') == 'discount_asc' ? 'selected' : '' }}>Giảm thấp nhất</option>
                                        </select>
                                    </div>

                                    <div class="col-xl-1 col-lg-12">
                                        <div class="filter-actions">
                                            <button type="submit"
                                                    class="btn btn-outline-primary icon-btn"
                                                    title="Lọc">
                                                <i class="bi bi-search"></i>
                                            </button>

                                            <a href="{{ route('admin.promotions.index', ['tab' => 'promotions']) }}"
                                               class="btn btn-outline-secondary icon-btn"
                                               title="Đặt lại">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="table-card">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th style="width: 80px;">Mã</th>
                                            <th class="text-start">Tên khuyến mãi</th>
                                            <th style="width: 120px;">Loại</th>
                                            <th style="width: 130px;">Mức giảm</th>
                                            <th style="width: 170px;">Thời gian</th>
                                            <th style="width: 120px;">Bật / tắt</th>
                                            <th style="width: 140px;">Tiến trình</th>
                                            <th style="width: 160px;">Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($promotions as $promo)
                                            @php
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
                                                    <div class="promo-name">{{ $promo->name }}</div>
                                                    @if ($promo->code)
                                                        <div class="code-badge">{{ $promo->code }}</div>
                                                    @endif
                                                </td>

                                                <td class="text-center">
                                                    <span class="badge badge-soft text-secondary border bg-light">
                                                        {{ $promo->type === 'order' ? 'Đơn hàng' : 'Sản phẩm' }}
                                                    </span>
                                                </td>

                                                <td class="text-center">
                                                    <span class="discount-text">
                                                        -{{ rtrim(rtrim(number_format($promo->discount_value, 2, '.', ''), '0'), '.') }}
                                                        {{ $promo->discount_type === 'percent' ? '%' : 'đ' }}
                                                    </span>
                                                </td>

                                                <td class="text-center">
                                                    <div class="sub-text">
                                                        {{ Carbon::parse($promo->start_date)->format('d/m/Y') }}
                                                        <br>
                                                        đến
                                                        <br>
                                                        {{ Carbon::parse($promo->end_date)->format('d/m/Y') }}
                                                    </div>
                                                </td>

                                                <td class="text-center">
                                                    @if($promo->is_active)
                                                        <span class="badge badge-soft text-success border bg-success-subtle">
                                                            Đang bật
                                                        </span>
                                                    @else
                                                        <span class="badge badge-soft text-danger border bg-danger-subtle">
                                                            Đã tắt
                                                        </span>
                                                    @endif
                                                </td>

                                                <td class="text-center">
                                                    <span class="badge badge-soft {{ $progressClass }}">
                                                        {{ $progressLabel }}
                                                    </span>
                                                </td>

                                                <td class="text-center">
                                                    <div class="action-group">
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
                                                <td colspan="8">
                                                    <div class="empty-state">
                                                        Chưa có khuyến mãi hệ thống nào
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @if($promotions->hasPages())
                            <div class="mt-4">
                                {{ $promotions->appends(array_merge(request()->query(), ['tab' => 'promotions']))->links('vendor.pagination.custom-blue') }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="tab-pane fade {{ $tab === 'rewards' ? 'show active' : '' }}"
                     id="rewards-pane"
                     role="tabpanel"
                     aria-labelledby="rewards-tab">
                    <div class="tab-pane-inner">
                        <div class="filter-card mb-3">
                            <div class="mb-3">
                                <h6 class="section-title">Bộ lọc voucher đổi điểm</h6>
                                <p class="section-subtitle">Quản lý các mẫu voucher khách hàng có thể đổi bằng điểm thành viên</p>
                            </div>

                            <form method="GET">
                                <input type="hidden" name="tab" value="rewards">
                                <input type="hidden" name="search" value="{{ request('search') }}">
                                <input type="hidden" name="type" value="{{ request('type') }}">
                                <input type="hidden" name="status" value="{{ request('status') }}">
                                <input type="hidden" name="progress" value="{{ request('progress') }}">
                                <input type="hidden" name="sort" value="{{ request('sort', 'new') }}">

                                <div class="row g-2 filter-row">
                                    <div class="col-xl-3 col-lg-6">
                                        <input type="text"
                                               name="reward_search"
                                               value="{{ request('reward_search') }}"
                                               class="form-control"
                                               placeholder="Tìm tên voucher...">
                                    </div>

                                    <div class="col-xl-3 col-lg-6">
                                        <select name="reward_status" class="form-select">
                                            <option value="">-- Trạng thái voucher --</option>
                                            <option value="active" {{ request('reward_status') == 'active' ? 'selected' : '' }}>Đang bật</option>
                                            <option value="inactive" {{ request('reward_status') == 'inactive' ? 'selected' : '' }}>Đã tắt</option>
                                        </select>
                                    </div>

                                    <div class="col-xl-2 col-lg-6">
                                        <select name="reward_progress" class="form-select">
                                            <option value="">-- Tiến trình đổi --</option>
                                            <option value="upcoming" {{ request('reward_progress') == 'upcoming' ? 'selected' : '' }}>Chưa bắt đầu</option>
                                            <option value="ongoing" {{ request('reward_progress') == 'ongoing' ? 'selected' : '' }}>Đang đổi</option>
                                            <option value="expired" {{ request('reward_progress') == 'expired' ? 'selected' : '' }}>Đã hết hạn</option>
                                        </select>
                                    </div>

                                    <div class="col-xl-2 col-lg-6">
                                        <select name="reward_sort" class="form-select">
                                            <option value="new" {{ request('reward_sort', 'new') == 'new' ? 'selected' : '' }}>Mới nhất</option>
                                            <option value="old" {{ request('reward_sort') == 'old' ? 'selected' : '' }}>Cũ nhất</option>
                                            <option value="points_desc" {{ request('reward_sort') == 'points_desc' ? 'selected' : '' }}>Điểm cao nhất</option>
                                            <option value="points_asc" {{ request('reward_sort') == 'points_asc' ? 'selected' : '' }}>Điểm thấp nhất</option>
                                        </select>
                                    </div>

                                    <div class="col-xl-2 col-lg-12">
                                        <div class="filter-actions">
                                            <button type="submit"
                                                    class="btn btn-outline-success icon-btn"
                                                    title="Lọc">
                                                <i class="bi bi-search"></i>
                                            </button>

                                            <a href="{{ route('admin.promotions.index', ['tab' => 'rewards']) }}"
                                               class="btn btn-outline-secondary icon-btn"
                                               title="Đặt lại">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="table-card">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th class="text-start">Tên voucher</th>
                                            <th style="width: 150px;">Điểm cần</th>
                                            <th style="width: 120px;">Mức giảm</th>
                                            <th style="width: 170px;">Thời gian đổi</th>
                                            <th style="width: 120px;">Hiệu lực</th>
                                            <th style="width: 130px;">Bật / tắt</th>
                                            <th style="width: 140px;">Tiến trình</th>
                                            <th style="width: 140px;">Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($rewards as $reward)
                                            @php
                                                if ($reward->redeem_start_at && $now->lt($reward->redeem_start_at)) {
                                                    $rewardProgressClass = 'text-secondary border bg-light';
                                                    $rewardProgressLabel = 'Chưa bắt đầu';
                                                } elseif ($reward->redeem_end_at && $now->gt($reward->redeem_end_at)) {
                                                    $rewardProgressClass = 'text-dark border bg-secondary-subtle';
                                                    $rewardProgressLabel = 'Đã hết hạn';
                                                } else {
                                                    $rewardProgressClass = 'text-success border bg-success-subtle';
                                                    $rewardProgressLabel = 'Đang đổi';
                                                }
                                            @endphp

                                            <tr>
                                                <td class="text-start">
                                                    <div class="reward-title">{{ $reward->title }}</div>
                                                    <div class="mini-stat">
                                                        Voucher mẫu dùng để khách đổi điểm
                                                    </div>
                                                </td>

                                                <td class="text-center">
                                                    <span class="text-primary fw-semibold">
                                                        {{ number_format($reward->points_required, 0, ',', '.') }} điểm
                                                    </span>
                                                </td>

                                                <td class="text-center">
                                                    <span class="discount-text">
                                                        -{{ rtrim(rtrim(number_format($reward->discount_value, 2, '.', ''), '0'), '.') }}
                                                        {{ $reward->discount_type === 'percent' ? '%' : 'đ' }}
                                                    </span>
                                                </td>

                                                <td class="text-center">
                                                    <div class="sub-text">
                                                        {{ $reward->redeem_start_at ? $reward->redeem_start_at->format('d/m/Y H:i') : 'Không giới hạn' }}
                                                        <br>
                                                        đến
                                                        <br>
                                                        {{ $reward->redeem_end_at ? $reward->redeem_end_at->format('d/m/Y H:i') : 'Không giới hạn' }}
                                                    </div>
                                                </td>

                                                <td class="text-center text-muted">
                                                    {{ $reward->valid_days }} ngày
                                                </td>

                                                <td class="text-center">
                                                    @if($reward->is_active)
                                                        <span class="badge badge-soft text-success border bg-success-subtle">
                                                            Đang bật
                                                        </span>
                                                    @else
                                                        <span class="badge badge-soft text-danger border bg-danger-subtle">
                                                            Đã tắt
                                                        </span>
                                                    @endif
                                                </td>

                                                <td class="text-center">
                                                    <span class="badge badge-soft {{ $rewardProgressClass }}">
                                                        {{ $rewardProgressLabel }}
                                                    </span>
                                                </td>

                                                <td class="text-center">
                                                    <div class="action-group">
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
                                                <td colspan="8">
                                                    <div class="empty-state">
                                                        Chưa có voucher đổi điểm nào
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @if($rewards->hasPages())
                            <div class="mt-4">
                                {{ $rewards->appends(array_merge(request()->query(), ['tab' => 'rewards']))->links('vendor.pagination.custom-blue') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabButtons = document.querySelectorAll('#promotionTabs .nav-link');

        tabButtons.forEach(button => {
            button.addEventListener('shown.bs.tab', function (event) {
                const targetId = event.target.getAttribute('data-bs-target');
                let tabValue = 'promotions';

                if (targetId === '#rewards-pane') tabValue = 'rewards';

                const url = new URL(window.location.href);
                url.searchParams.set('tab', tabValue);
                window.history.replaceState({}, '', url);
            });
        });
    });
</script>
@endsection