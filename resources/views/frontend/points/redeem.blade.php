@extends('layouts.frontend')

@section('title', 'Đổi điểm')

@push('styles')
<style>
    .redeem-page {
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
        min-height: 100%;
    }

    .redeem-card,
    .reward-card,
    .empty-card {
        border: 0;
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 12px 30px rgba(30, 41, 59, 0.08);
    }

    .redeem-header {
        padding: 24px;
    }

    .redeem-title {
        font-size: 24px;
        font-weight: 700;
        color: #1f2d3d;
        margin-bottom: 6px;
    }

    .redeem-subtitle {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 0;
    }

    .redeem-points {
        display: inline-block;
        margin-top: 12px;
        padding: 10px 16px;
        border-radius: 999px;
        background: #eef4ff;
        color: #0d6efd;
        font-size: 14px;
        font-weight: 700;
        border: 1px solid #dbe7ff;
    }

    .history-btn {
        border-radius: 999px;
        padding: 10px 18px;
        font-size: 14px;
        font-weight: 600;
    }

    .reward-card {
        transition: 0.25s ease;
    }

    .reward-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 34px rgba(30, 41, 59, 0.1);
    }

    .reward-body {
        padding: 20px 22px;
    }

    .reward-wrap {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
        flex-wrap: wrap;
    }

    .reward-title {
        font-size: 17px;
        font-weight: 700;
        color: #1f2d3d;
        margin-bottom: 10px;
    }

    .reward-meta {
        font-size: 14px;
        color: #6b7280;
        line-height: 1.8;
    }

    .reward-meta strong {
        color: #1f2d3d;
        font-weight: 600;
    }

    .reward-action {
        min-width: 160px;
        text-align: end;
    }

    .reward-note {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 10px;
    }

    .reward-btn {
        min-width: 135px;
        border-radius: 12px;
        padding: 10px 16px;
        font-size: 14px;
        font-weight: 700;
        border: 0;
    }

    .reward-btn-success {
        background: #198754;
        color: #fff;
    }

    .reward-btn-success:hover {
        background: #157347;
        color: #fff;
    }

    .reward-btn-muted {
        background: #eef1f4;
        color: #6b7280;
    }

    .reward-status {
        display: inline-block;
        margin-bottom: 10px;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .reward-status.done {
        background: #edf8f1;
        color: #198754;
    }

    .reward-status.ready {
        background: #eef4ff;
        color: #0d6efd;
    }

    .reward-status.disabled {
        background: #f5f5f5;
        color: #8b95a1;
    }

    .empty-card {
        text-align: center;
    }

    .empty-body {
        padding: 48px 24px;
    }

    .empty-title {
        font-size: 20px;
        font-weight: 700;
        color: #1f2d3d;
        margin-bottom: 8px;
    }

    .empty-text {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 0;
    }

    @media (max-width: 767.98px) {
        .redeem-header,
        .reward-body {
            padding: 16px;
        }

        .redeem-title {
            font-size: 21px;
        }

        .reward-action {
            width: 100%;
            text-align: start;
        }

        .reward-btn {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="redeem-page">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-10">

                {{-- HEADER --}}
                <div class="redeem-card mb-4">
                    <div class="redeem-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h1 class="redeem-title">Đổi điểm lấy voucher</h1>
                            <p class="redeem-subtitle">
                                Sử dụng điểm tích lũy để đổi các voucher ưu đãi phù hợp.
                            </p>
                            <div class="redeem-points">
                                {{ number_format($user->loyalty_points ?? 0, 0, ',', '.') }} điểm
                            </div>
                        </div>

                        <a href="{{ route('points.history') }}"
                           class="btn btn-outline-secondary history-btn">
                            Lịch sử điểm
                        </a>
                    </div>
                </div>

                {{-- DANH SÁCH REWARD --}}
                @if($rewards->count())
                    @foreach($rewards as $reward)
                        @php
                            $isRedeemed = in_array($reward->id, $redeemedRewardIds);
                            $canRedeem = ($user->loyalty_points ?? 0) >= $reward->points_required;
                            $missingPoints = max(0, $reward->points_required - ($user->loyalty_points ?? 0));
                        @endphp

                        <div class="reward-card mb-3">
                            <div class="reward-body">
                                <div class="reward-wrap">
                                    <div>
                                        <div class="reward-title">{{ $reward->title }}</div>

                                        <div class="reward-meta">
                                            <div><strong>Cần:</strong> {{ number_format($reward->points_required, 0, ',', '.') }} điểm</div>

                                            @if($reward->min_order_value)
                                                <div><strong>Đơn tối thiểu:</strong> {{ number_format($reward->min_order_value, 0, ',', '.') }}đ</div>
                                            @endif

                                            <div><strong>Hạn sử dụng:</strong> {{ $reward->valid_days }} ngày</div>
                                        </div>
                                    </div>

                                    <div class="reward-action">
                                        @if($isRedeemed)
                                            <div class="reward-status done">Đã đổi</div>
                                            <div class="reward-note">Voucher này bạn đã đổi rồi.</div>
                                            <button class="reward-btn reward-btn-muted" disabled>
                                                Đã đổi
                                            </button>

                                        @elseif($canRedeem)
                                            <div class="reward-status ready">Có thể đổi</div>
                                            <div class="reward-note">Bạn đang đủ điểm để đổi voucher này.</div>

                                            <form method="POST" action="{{ route('points.redeem') }}">
                                                @csrf
                                                <input type="hidden" name="reward_id" value="{{ $reward->id }}">

                                                <button type="submit" class="reward-btn reward-btn-success">
                                                    Đổi ngay
                                                </button>
                                            </form>

                                        @else
                                            <div class="reward-status disabled">Chưa đủ điểm</div>
                                            <div class="reward-note">
                                                Bạn còn thiếu {{ number_format($missingPoints, 0, ',', '.') }} điểm.
                                            </div>
                                            <button class="reward-btn reward-btn-muted" disabled>
                                                Không đủ điểm
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="empty-card">
                        <div class="empty-body">
                            <div class="empty-title">Chưa có voucher khả dụng</div>
                            <p class="empty-text">
                                Hiện chưa có voucher đổi điểm nào khả dụng.
                            </p>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
@endsection