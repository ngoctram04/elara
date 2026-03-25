@extends('layouts.frontend')

@section('title', 'Lịch sử điểm')

@section('content')

<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => url('/')],
    ['label' => 'Lịch sử điểm']
]" />

<div class="container pb-4">
    <div class="row g-4">

        {{-- SIDEBAR --}}
        @include('frontend.partials.account-sidebar')

        {{-- CONTENT --}}
        <div class="col-md-9">

            {{-- HEADER CARD --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden point-header-card">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                        <div>
                            <h5 class="fw-bold mb-1 d-flex align-items-center">
                                <i class="bi bi-star-fill text-warning me-2"></i>
                                Lịch sử điểm
                            </h5>
                            <small class="text-muted">
                                Theo dõi hoạt động tích & đổi điểm của bạn
                            </small>
                        </div>

                        <a href="{{ route('points.redeem.page') }}"
                           class="btn btn-primary rounded-pill px-4">
                            <i class="bi bi-gift me-1"></i>
                            Đổi điểm
                        </a>

                    </div>

                    <hr class="my-4">

                    <div class="row text-center g-3">

                        <div class="col-md-4">
                            <div class="point-stat-box">
                                <div class="fw-semibold text-muted small">
                                    <i class="bi bi-wallet2 me-1"></i>
                                    Điểm hiện tại
                                </div>
                                <div class="fs-4 fw-bold text-primary">
                                    {{ number_format(auth()->user()->loyalty_points) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="point-stat-box">
                                <div class="fw-semibold text-muted small">
                                    <i class="bi bi-clock-history me-1"></i>
                                    Tổng lượt giao dịch
                                </div>
                                <div class="fs-5 fw-semibold">
                                    {{ $histories->total() }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="point-stat-box">
                                <div class="fw-semibold text-muted small">
                                    <i class="bi bi-file-earmark-text me-1"></i>
                                    Trang hiện tại
                                </div>
                                <div class="fs-6 fw-semibold">
                                    {{ $histories->currentPage() }}
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- TABLE CARD --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0">

                    @if($histories->count())

                        <div class="table-responsive">
                            <table class="table align-middle mb-0 point-history-table">

                                <thead>
                                    <tr class="text-muted small">
                                        <th style="width:160px;">Thời gian</th>
                                        <th style="width:130px;">Loại</th>
                                        <th style="width:130px;">Điểm</th>
                                        <th>Mô tả</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($histories as $history)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">
                                                    {{ $history->created_at->format('d/m/Y') }}
                                                </div>
                                                <small class="text-muted">
                                                    {{ $history->created_at->format('H:i') }}
                                                </small>
                                            </td>

                                            <td>
                                                @if($history->type === 'earn')
                                                    <span class="badge rounded-pill bg-success-subtle text-success border px-3 py-2">
                                                        <i class="bi bi-arrow-up-circle me-1"></i>
                                                        Tích điểm
                                                    </span>
                                                @else
                                                    <span class="badge rounded-pill bg-danger-subtle text-danger border px-3 py-2">
                                                        <i class="bi bi-arrow-down-circle me-1"></i>
                                                        Đổi điểm
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="fw-bold {{ $history->points > 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $history->points > 0 ? '+' : '' }}
                                                {{ number_format($history->points) }}
                                            </td>

                                            <td class="text-muted">
    {{ preg_replace_callback('/#(\d+)/', function ($matches) {
        return 'DH' . str_pad($matches[1], 5, '0', STR_PAD_LEFT);
    }, $history->description) }}
</td>
                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>
                        </div>

                        {{-- PAGINATION --}}
                        <div class="point-pagination-footer justify-content-center">
    {{ $histories->withQueryString()->links('vendor.pagination.custom-blue') }}
</div>

                    @else

                        {{-- EMPTY STATE --}}
                        <div class="text-center py-5 px-3">

                            <div class="mb-3">
                                <i class="bi bi-star text-warning" style="font-size:50px;"></i>
                            </div>

                            <h6 class="fw-semibold mb-2">
                                Chưa có lịch sử điểm
                            </h6>

                            <p class="text-muted mb-3">
                                Khi bạn mua hàng hoặc đổi quà, lịch sử điểm sẽ hiển thị tại đây.
                            </p>

                            <a href="{{ route('points.redeem.page') }}"
                               class="btn btn-outline-primary rounded-pill px-4">
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
@push('styles')
<style>
    .point-header-card{
        background: linear-gradient(135deg, #f8f9fa, #ffffff);
    }

    .point-stat-box{
        background:#fff;
        border:1px solid #eef2f7;
        border-radius:16px;
        padding:16px;
        height:100%;
    }

    .point-history-table thead th{
        background:#f8f9fa;
        border-bottom:1px solid #eef2f7;
        padding:16px;
        font-weight:700;
        white-space:nowrap;
    }

    .point-history-table tbody td{
        padding:16px;
        border-top:1px solid #f1f5f9;
    }

    .point-history-table tbody tr:hover{
        background:#fcfdff;
    }

    .point-pagination-footer{
        display:flex;
        align-items:center;
        justify-content:center;
        padding:18px 20px;
        border-top:1px solid #eef2f7;
        background:#fbfcfe;
    }

    .point-pagination-footer .custom-pagination-wrap{
        margin:0;
    }

    .custom-pagination-wrap{
        display:flex;
        justify-content:center;
    }

    .custom-pagination{
        display:flex;
        align-items:center;
        justify-content:center;
        gap:18px;
        list-style:none;
        padding:0;
        margin:0;
        flex-wrap:wrap;
    }

    .custom-pagination li{
        margin:0;
        padding:0;
    }

    .custom-pagination li a,
    .custom-pagination li span{
        display:flex;
        align-items:center;
        justify-content:center;
        min-width:52px;
        height:52px;
        padding:0 12px;
        border-radius:999px;
        text-decoration:none;
        font-size:18px;
        font-weight:600;
        border:none;
        background:transparent;
        color:#2563eb;
        transition:all 0.2s ease;
    }

    .custom-pagination li a:hover{
        background:#dbeafe;
        color:#1d4ed8;
    }

    .custom-pagination li.active span{
        background:#2563eb;
        color:#fff;
        box-shadow:0 10px 20px rgba(37, 99, 235, 0.22);
    }

    .custom-pagination li.dots span{
        min-width:auto;
        height:52px;
        padding:0 4px;
        color:#374151;
        background:transparent;
        border:none;
    }

    .custom-pagination li.disabled span{
        color:#9ca3af;
        background:transparent;
        border:none;
    }

    .custom-pagination li.arrow a,
    .custom-pagination li.arrow span{
        font-size:22px;
        min-width:40px;
    }

    @media (max-width: 767.98px){
        .point-history-table thead th,
        .point-history-table tbody td{
            padding:12px;
        }

        .point-pagination-footer{
            padding:14px;
        }

        .custom-pagination{
            gap:12px;
        }

        .custom-pagination li a,
        .custom-pagination li span{
            min-width:42px;
            height:42px;
            font-size:15px;
        }

        .custom-pagination li.dots span{
            height:42px;
        }

        .custom-pagination li.arrow a,
        .custom-pagination li.arrow span{
            font-size:18px;
            min-width:32px;
        }
    }
</style>
@endpush
