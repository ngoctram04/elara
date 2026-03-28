@ -1,209 +0,0 @@
@extends('layouts.frontend')

@section('title','Hạng thành viên')

@section('content')

<div class="container py-4">
<div class="row">
@include('frontend.partials.account-sidebar')

    {{-- ===== SIDEBAR ===== --}}
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center">

                <img
                    src="{{ auth()->user()->avatar
                        ? asset('storage/'.auth()->user()->avatar)
                        : asset('images/avatar-default.png') }}"
                    class="rounded-circle mb-3 border"
                    width="80" height="80"
                    style="object-fit:cover;"
                >

                <h6 class="fw-semibold mb-1">{{ auth()->user()->name }}</h6>
                <small class="text-muted">Quản lý tài khoản</small>

                <hr>

                <div class="text-start small">
                    <a class="d-block py-2 text-decoration-none text-dark"
                       href="{{ route('orders.history') }}">
                        <i class="bi bi-box-seam me-2"></i> Đơn hàng
                    </a>

                    <a class="d-block py-2 text-decoration-none text-dark"
                       href="{{ route('profile.index') }}">
                        <i class="bi bi-person me-2"></i> Thông tin tài khoản
                    </a>

                    <a class="d-block py-2 text-decoration-none text-dark"
                       href="{{ route('addresses.index') }}">
                        <i class="bi bi-geo-alt me-2"></i> Sổ địa chỉ
                    </a>

                    <a class="d-block py-2 fw-semibold text-primary text-decoration-none"
                       href="{{ route('profile.membership') }}">
                        <i class="bi bi-award me-2"></i> Hạng thành viên
                    </a>
                </div>

            </div>
        </div>
    </div>


    {{-- ===== CONTENT ===== --}}
    <div class="col-md-9">

        @php
            $levels = [
                'bronze' => ['name' => 'Đồng', 'min' => 0],
                'silver' => ['name' => 'Bạc', 'min' => 1000000],
                'gold' => ['name' => 'Vàng', 'min' => 5000000],
                'diamond' => ['name' => 'Kim Cương', 'min' => 20000000],
            ];

            $currentLevel = $user->member_level;
            $currentSpent = $user->total_spent;

            // Xác định hạng tiếp theo
            $nextLevel = null;
            foreach ($levels as $key => $level) {
                if ($level['min'] > $currentSpent) {
                    $nextLevel = $level;
                    break;
                }
            }

            if ($nextLevel) {
                $progress = ($currentSpent / $nextLevel['min']) * 100;
            } else {
                $progress = 100;
            }
        @endphp


        {{-- ===== THÔNG TIN HẠNG ===== --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body">

                <h5 class="fw-bold mb-3">Hạng thành viên</h5>

                <div class="row text-center mb-3">
                    <div class="col-md-4">
                        <div class="fw-bold text-primary fs-5">
                            {{ ucfirst($levels[$currentLevel]['name']) }}
                        </div>
                        <small class="text-muted">Hạng hiện tại</small>
                    </div>

                    <div class="col-md-4">
                        <div class="fw-bold fs-5">
                            {{ number_format($user->loyalty_points) }}
                        </div>
                        <small class="text-muted">Điểm tích lũy</small>
                    </div>

                    <div class="col-md-4">
                        <div class="fw-bold fs-5">
                            {{ number_format($user->total_spent) }}đ
                        </div>
                        <small class="text-muted">Tổng chi tiêu</small>
                    </div>
                </div>

                {{-- Progress lên hạng --}}
                @if($nextLevel)
                    <div class="mb-2 small text-muted">
                        Còn {{ number_format($nextLevel['min'] - $currentSpent) }}đ để đạt hạng
                        <b>{{ $nextLevel['name'] }}</b>
                    </div>

                    <div class="progress" style="height:10px;">
                        <div class="progress-bar bg-primary"
                             style="width: {{ min($progress,100) }}%"></div>
                    </div>
                @else
                    <div class="alert alert-success mt-2 mb-0">
                        Bạn đã đạt hạng cao nhất 🎉
                    </div>
                @endif

            </div>
        </div>


        {{-- ===== ƯU ĐÃI THEO HẠNG ===== --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body">

                <h5 class="fw-bold mb-3">Ưu đãi của bạn</h5>

                @switch($currentLevel)
                    @case('bronze')
                        <ul class="mb-0">
                            <li>Tích điểm 1% mỗi đơn hàng</li>
                        </ul>
                        @break

                    @case('silver')
                        <ul class="mb-0">
                            <li>Tích điểm 2% mỗi đơn hàng</li>
                            <li>Ưu tiên hỗ trợ khách hàng</li>
                        </ul>
                        @break

                    @case('gold')
                        <ul class="mb-0">
                            <li>Tích điểm 3% mỗi đơn hàng</li>
                            <li>Miễn phí vận chuyển đơn từ 300k</li>
                        </ul>
                        @break

                    @case('diamond')
                        <ul class="mb-0">
                            <li>Tích điểm 5% mỗi đơn hàng</li>
                            <li>Miễn phí vận chuyển mọi đơn</li>
                            <li>Ưu đãi độc quyền</li>
                        </ul>
                        @break
                @endswitch

            </div>
        </div>


        {{-- ===== LỊCH SỬ ĐIỂM ===== --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">

                <h5 class="fw-bold mb-3">Lịch sử tích điểm</h5>

                @forelse($histories as $history)
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <div>
                            <div class="fw-semibold">
                                {{ $history->description }}
                            </div>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($history->created_at)->format('d/m/Y H:i') }}
                            </small>
                        </div>

                        <div class="fw-bold text-success">
                            +{{ $history->points }}
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">Chưa có lịch sử điểm.</p>
                @endforelse

            </div>
        </div>

    </div>
</div>
</div>

@endsection