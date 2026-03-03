@extends('layouts.frontend')

@section('title','Lịch sử điểm')

@section('content')

<div class="container py-4">
<div class="row">

    {{-- SIDEBAR --}}
    @include('frontend.partials.account-sidebar')

    {{-- CONTENT --}}
    <div class="col-md-9">

        {{-- ===== HEADER CARD ===== --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
            <div class="p-4"
                 style="background:linear-gradient(135deg,#f8f9fa,#ffffff);">

                <div class="d-flex justify-content-between align-items-center flex-wrap">

                    <div>
                        <h5 class="fw-bold mb-1">
                            <i class="bi bi-star-fill text-warning me-2"></i>
                            Lịch sử điểm
                        </h5>
                        <small class="text-muted">
                            Theo dõi hoạt động tích & đổi điểm của bạn
                        </small>
                    </div>

                    <a href="{{ route('points.redeem.page') }}"
                       class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-gift me-1"></i> Đổi điểm
                    </a>

                </div>

                <hr class="my-4">

                <div class="row text-center">

                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="fw-semibold text-muted small">Điểm hiện tại</div>
                        <div class="fs-4 fw-bold text-primary">
                            {{ number_format(auth()->user()->loyalty_points) }}
                        </div>
                    </div>

                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="fw-semibold text-muted small">Tổng lượt giao dịch</div>
                        <div class="fs-5 fw-semibold">
                            {{ $histories->total() }}
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="fw-semibold text-muted small">Trang hiện tại</div>
                        <div class="fs-6">
                            {{ $histories->currentPage() }}
                        </div>
                    </div>

                </div>

            </div>
        </div>

        {{-- ===== TABLE CARD ===== --}}
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">

                @if($histories->count())

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">

                            <thead style="background:#f8f9fa;">
                                <tr class="text-muted small">
                                    <th style="width:160px;">Thời gian</th>
                                    <th style="width:130px;">Loại</th>
                                    <th style="width:130px;">Điểm</th>
                                    <th>Mô tả</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach($histories as $history)
                                <tr class="border-top">

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
                                        {{ $history->description }}
                                    </td>

                                </tr>
                                @endforeach

                            </tbody>

                        </table>
                    </div>

                    {{-- PAGINATION --}}
                    <div class="p-3 border-top bg-light">
                        {{ $histories->links() }}
                    </div>

                @else

                    {{-- EMPTY STATE --}}
                    <div class="text-center py-5">

                        <div class="mb-3">
                            <i class="bi bi-star text-warning"
                               style="font-size:50px;"></i>
                        </div>

                        <h6 class="fw-semibold mb-2">
                            Chưa có lịch sử điểm
                        </h6>

                        <p class="text-muted mb-3">
                            Khi bạn mua hàng hoặc đổi quà,
                            lịch sử điểm sẽ hiển thị tại đây.
                        </p>

                        <a href="{{ route('points.redeem.page') }}"
                           class="btn btn-outline-primary rounded-pill px-4">
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