@extends('layouts.frontend')

@section('title', 'Lịch sử điểm')

@push('styles')
<style>
    .points-page {
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
        min-height: 100%;
    }

    .points-summary-card,
    .points-table-card {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 14px 35px rgba(30, 41, 59, 0.08);
    }

    .points-summary-card {
        background:
            radial-gradient(circle at top right, rgba(255, 255, 255, 0.65), transparent 28%),
            linear-gradient(135deg, #eef5ff 0%, #ffffff 55%, #f8fbff 100%);
    }

    .points-summary-body {
        padding: 24px;
    }

    .points-header-title {
        font-size: 24px;
        font-weight: 800;
        color: #1f2d3d;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .points-header-title i {
        color: #f4b400;
        font-size: 22px;
    }

    .points-header-subtitle {
        font-size: 14px;
        color: #7b8794;
        margin-bottom: 0;
    }

    .points-redeem-btn {
        border-radius: 999px;
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 700;
        box-shadow: 0 10px 20px rgba(13, 110, 253, 0.14);
    }

    .points-divider {
        border: 0;
        border-top: 1px solid rgba(226, 232, 240, 0.9);
        margin: 20px 0;
    }

    .point-stat-box {
        background: rgba(255, 255, 255, 0.86);
        border: 1px solid #eaf0f6;
        border-radius: 20px;
        padding: 18px 16px;
        height: 100%;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
        transition: all 0.25s ease;
    }

    .point-stat-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
    }

    .point-stat-label {
        font-size: 13px;
        font-weight: 600;
        color: #7b8794;
        margin-bottom: 8px;
    }

    .point-stat-value {
        font-size: 28px;
        font-weight: 800;
        color: #1f2d3d;
        line-height: 1.2;
    }

    .point-stat-value.primary {
        color: #0d6efd;
    }

    .points-table-header {
        padding: 22px 24px 16px;
        border-bottom: 1px solid #eef2f7;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    }

    .points-table-title {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: #1f2d3d;
    }

    .points-table-note {
        margin: 6px 0 0;
        font-size: 14px;
        color: #7b8794;
    }

    .point-history-table {
        margin-bottom: 0;
    }

    .point-history-table thead th {
        background: #f8fbff;
        border-bottom: 1px solid #eef2f7;
        padding: 16px 18px;
        font-size: 13px;
        font-weight: 700;
        color: #667085;
        text-transform: uppercase;
        letter-spacing: 0.2px;
        white-space: nowrap;
    }

    .point-history-table tbody td {
        padding: 18px;
        border-top: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .point-history-table tbody tr {
        transition: all 0.2s ease;
    }

    .point-history-table tbody tr:hover {
        background: #fcfdff;
    }

    .point-time-date {
        font-weight: 700;
        color: #1f2d3d;
        margin-bottom: 2px;
    }

    .point-time-hour {
        font-size: 13px;
        color: #98a2b3;
    }

    .point-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 8px 14px;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .point-type-badge.earn {
        background: #ecfdf3;
        color: #198754;
        border-color: #d7f2e3;
    }

    .point-type-badge.redeem {
        background: #fff5f6;
        color: #dc3545;
        border-color: #f8d4d8;
    }

    .point-value {
        font-size: 16px;
        font-weight: 800;
        white-space: nowrap;
    }

    .point-value.positive {
        color: #198754;
    }

    .point-value.negative {
        color: #dc3545;
    }

    .point-description {
        color: #5f6c7b;
        line-height: 1.6;
    }

    .point-pagination-footer {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 18px 20px;
        border-top: 1px solid #eef2f7;
        background: #fbfcfe;
    }

    .point-pagination-footer .custom-pagination-wrap {
        margin: 0;
    }

    .custom-pagination-wrap {
        display: flex;
        justify-content: center;
    }

    .custom-pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 14px;
        list-style: none;
        padding: 0;
        margin: 0;
        flex-wrap: wrap;
    }

    .custom-pagination li {
        margin: 0;
        padding: 0;
    }

    .custom-pagination li a,
    .custom-pagination li span {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 46px;
        height: 46px;
        padding: 0 12px;
        border-radius: 999px;
        text-decoration: none;
        font-size: 16px;
        font-weight: 600;
        border: none;
        background: transparent;
        color: #2563eb;
        transition: all 0.2s ease;
    }

    .custom-pagination li a:hover {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .custom-pagination li.active span {
        background: #2563eb;
        color: #fff;
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.22);
    }

    .custom-pagination li.dots span {
        min-width: auto;
        height: 46px;
        padding: 0 4px;
        color: #6b7280;
        background: transparent;
    }

    .custom-pagination li.disabled span {
        color: #c0c7d1;
        background: transparent;
    }

    .custom-pagination li.arrow a,
    .custom-pagination li.arrow span {
        min-width: 38px;
        font-size: 20px;
    }

    .points-empty {
        text-align: center;
        padding: 56px 24px;
    }

    .points-empty-icon {
        width: 88px;
        height: 88px;
        margin: 0 auto 18px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #fff7da, #fffdf2);
        color: #f0b100;
        font-size: 38px;
        box-shadow: 0 10px 25px rgba(244, 180, 0, 0.12);
    }

    .points-empty-title {
        font-size: 20px;
        font-weight: 700;
        color: #1f2d3d;
        margin-bottom: 8px;
    }

    .points-empty-text {
        font-size: 14px;
        color: #7b8794;
        margin-bottom: 20px;
        line-height: 1.7;
    }

    .points-empty-btn {
        border-radius: 999px;
        padding: 10px 22px;
        font-weight: 700;
    }

    @media (max-width: 767.98px) {
        .points-summary-body,
        .points-table-header {
            padding-left: 16px;
            padding-right: 16px;
        }

        .point-history-table thead th,
        .point-history-table tbody td {
            padding: 12px;
        }

        .point-stat-value {
            font-size: 24px;
        }

        .point-pagination-footer {
            padding: 14px;
        }

        .custom-pagination {
            gap: 10px;
        }

        .custom-pagination li a,
        .custom-pagination li span {
            min-width: 40px;
            height: 40px;
            font-size: 14px;
        }

        .custom-pagination li.arrow a,
        .custom-pagination li.arrow span {
            min-width: 30px;
            font-size: 18px;
        }
    }
</style>
@endpush

@section('content')

<div class="points-page">
    <div class="container pb-4">
        <div class="row g-4">

            {{-- SIDEBAR --}}
            @include('frontend.partials.account-sidebar')

            {{-- CONTENT --}}
            <div class="col-md-9">

                {{-- HEADER CARD --}}
                <div class="points-summary-card mb-4">
                    <div class="points-summary-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <h1 class="points-header-title">
                                    Lịch sử điểm
                                </h1>
                                <p class="points-header-subtitle">
                                    Theo dõi quá trình tích điểm và đổi điểm của bạn một cách rõ ràng, nhanh chóng.
                                </p>
                            </div>

                            <a href="{{ route('points.redeem.page') }}"
                               class="btn btn-primary points-redeem-btn">
                                <i class="bi bi-gift me-1"></i>
                                Đổi điểm
                            </a>
                        </div>

                        <hr class="points-divider">

                        <div class="row text-center g-3">
                            <div class="col-md-4">
                                <div class="point-stat-box">
                                    <div class="point-stat-label">
                                        <i class="bi bi-wallet2 me-1"></i>
                                        Điểm hiện tại
                                    </div>
                                    <div class="point-stat-value primary">
                                        {{ number_format(auth()->user()->loyalty_points ?? 0, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="point-stat-box">
                                    <div class="point-stat-label">
                                        <i class="bi bi-clock-history me-1"></i>
                                        Tổng lượt giao dịch
                                    </div>
                                    <div class="point-stat-value">
                                        {{ number_format($histories->total(), 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="point-stat-box">
                                    <div class="point-stat-label">
                                        <i class="bi bi-file-earmark-text me-1"></i>
                                        Trang hiện tại
                                    </div>
                                    <div class="point-stat-value">
                                        {{ $histories->currentPage() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TABLE CARD --}}
                <div class="points-table-card">
                    <div class="points-table-header">
                        <h5 class="points-table-title">Danh sách giao dịch điểm</h5>
                        <p class="points-table-note">
                            Mỗi lần cộng hoặc trừ điểm sẽ được lưu lại tại đây để bạn tiện theo dõi.
                        </p>
                    </div>

                    @if($histories->count())
                        <div class="table-responsive">
                            <table class="table align-middle point-history-table">
                                <thead>
                                    <tr>
                                        <th style="width: 160px;">Thời gian</th>
                                        <th style="width: 150px;">Loại</th>
                                        <th style="width: 120px;">Điểm</th>
                                        <th>Mô tả</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($histories as $history)
                                        <tr>
                                            <td>
                                                <div class="point-time-date">
                                                    {{ $history->created_at->format('d/m/Y') }}
                                                </div>
                                                <div class="point-time-hour">
                                                    {{ $history->created_at->format('H:i') }}
                                                </div>
                                            </td>

                                            <td>
                                                @if($history->type === 'earn')
                                                    <span class="point-type-badge earn">
                                                        <i class="bi bi-arrow-up-circle"></i>
                                                        Tích điểm
                                                    </span>
                                                @else
                                                    <span class="point-type-badge redeem">
                                                        <i class="bi bi-arrow-down-circle"></i>
                                                        Đổi điểm
                                                    </span>
                                                @endif
                                            </td>

                                            <td>
                                                <div class="point-value {{ $history->points > 0 ? 'positive' : 'negative' }}">
                                                    {{ $history->points > 0 ? '+' : '' }}{{ number_format($history->points, 0, ',', '.') }}
                                                </div>
                                            </td>

                                            <td class="point-description">
                                                {{ preg_replace_callback('/#(\d+)/', function ($matches) {
                                                    return 'DH' . str_pad($matches[1], 5, '0', STR_PAD_LEFT);
                                                }, $history->description) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="point-pagination-footer">
                            {{ $histories->withQueryString()->links('vendor.pagination.custom-blue') }}
                        </div>
                    @else
                        <div class="points-empty">
                            <div class="points-empty-icon">
                                <i class="bi bi-star"></i>
                            </div>

                            <div class="points-empty-title">
                                Chưa có lịch sử điểm
                            </div>

                            <div class="points-empty-text">
                                Khi bạn mua hàng hoặc đổi quà, lịch sử điểm sẽ hiển thị tại đây để bạn tiện theo dõi.
                            </div>

                            <a href="{{ route('points.redeem.page') }}"
                               class="btn btn-outline-primary points-empty-btn">
                                <i class="bi bi-gift me-1"></i>
                                Đổi điểm ngay
                            </a>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>

@endsection