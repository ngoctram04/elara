@extends('layouts.frontend')

@section('title','Đổi điểm')

@section('content')

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">

            {{-- HEADER --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>
                        <h5 class="fw-bold mb-1">
                            <i class="bi bi-gift text-primary me-2"></i>
                            Đổi điểm lấy voucher
                        </h5>
                        <small class="text-muted">
                            Tổng điểm hiện tại:
                            <span class="fw-semibold text-primary">
                                {{ number_format($user->loyalty_points) }} điểm
                            </span>
                        </small>
                    </div>

                    <a href="{{ route('points.history') }}"
                       class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-clock-history me-1"></i> Lịch sử điểm
                    </a>

                </div>
            </div>


            {{-- DANH SÁCH REWARD --}}
            @if($rewards->count())

                @foreach($rewards as $reward)

                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body d-flex justify-content-between align-items-center">

                        <div>
                            <h6 class="fw-bold mb-1">
                                {{ $reward->title }}
                            </h6>

                            <small class="text-muted d-block">
                                Cần {{ number_format($reward->points_required) }} điểm
                            </small>

                            @if($reward->min_order_value)
                                <small class="text-muted d-block">
                                    Đơn tối thiểu {{ number_format($reward->min_order_value) }}đ
                                </small>
                            @endif

                            <small class="text-muted d-block">
                                Hạn sử dụng: {{ $reward->valid_days }} ngày
                            </small>
                        </div>

                        <div class="text-end">

                            {{-- ✅ ĐÃ ĐỔI --}}
                            @if(in_array($reward->id, $redeemedRewardIds))
                                <button class="btn btn-secondary btn-sm px-4" disabled>
                                    Đã đổi
                                </button>

                            {{-- ✅ ĐỦ ĐIỂM --}}
                            @elseif($user->loyalty_points >= $reward->points_required)

                                <form method="POST" action="{{ route('points.redeem') }}">
                                    @csrf
                                    <input type="hidden" name="reward_id" value="{{ $reward->id }}">

                                    <button class="btn btn-success btn-sm px-4">
                                        Đổi
                                    </button>
                                </form>

                            {{-- ❌ KHÔNG ĐỦ ĐIỂM --}}
                            @else

                                <button class="btn btn-secondary btn-sm px-4" disabled>
                                    Không đủ điểm
                                </button>

                            @endif

                        </div>

                    </div>
                </div>

                @endforeach

            @else

                <div class="card shadow-sm border-0">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-gift text-muted" style="font-size:40px;"></i>
                        <p class="mt-3 text-muted">
                            Hiện chưa có voucher đổi điểm nào khả dụng.
                        </p>
                    </div>
                </div>

            @endif

        </div>
    </div>
</div>

@endsection