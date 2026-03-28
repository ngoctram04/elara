@php
    $user = auth()->user();

    $points = $user->loyalty_points ?? 0;
    $spent = (float) ($user->yearly_spent_calculated ?? 0);
    $level = strtolower($user->member_level ?? 'bronze');

    /*
    |--------------------------------------------------------------------------
    | MỐC HẠNG THEO CHI TIÊU
    |--------------------------------------------------------------------------
    */
    $levels = [
        'bronze'   => 0,
        'silver'   => 1000000,
        'gold'     => 3000000,
        'diamond' => 10000000,
    ];

    $levelNames = [
        'bronze'   => 'Đồng',
        'silver'   => 'Bạc',
        'gold'     => 'Vàng',
        'diamond' => 'Kim Cương',
    ];

    $colors = [
        'bronze'   => '#cd7f32',
        'silver'   => '#6c757d',
        'gold'     => '#f1c40f',
        'diamond' => '#0dcaf0',
    ];

    $benefits = [
        'bronze' => [
            'Không có ưu đãi',
        ],
        'silver' => [
            'Giảm 5% vào ngày sinh nhật',
        ],
        'gold' => [
            'Miễn phí vận chuyển đơn trên 300.000đ',
            'Giảm 10% vào ngày sinh nhật',
        ],
        'diamond' => [
            'Miễn phí vận chuyển mọi đơn',
            'Giảm 15% vào ngày sinh nhật',
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | TÌM HẠNG TIẾP THEO
    |--------------------------------------------------------------------------
    */
    $nextLevel = null;
    $nextValue = null;

    foreach ($levels as $name => $value) {
        if ($spent < $value) {
            $nextLevel = $name;
            $nextValue = $value;
            break;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TÍNH PROGRESS THEO CHI TIÊU
    |--------------------------------------------------------------------------
    */
    $progress = 100;

    if ($nextLevel) {
        $currentValue = $levels[$level] ?? 0;
        $range = $nextValue - $currentValue;
        $progress = $range > 0 ? (($spent - $currentValue) / $range) * 100 : 100;
        $progress = max(0, min(100, $progress));
    }
@endphp

<div class="col-md-3 mb-4">

<style>
.profile-card{border-radius:18px;overflow:hidden;transition:0.3s;}
.profile-card:hover{transform:translateY(-2px);}
.profile-avatar{border:4px solid #fff;box-shadow:0 6px 18px rgba(0,0,0,0.15);}
.member-badge{border-radius:20px;font-size:13px;font-weight:600;}
.profile-menu a{border-radius:10px;padding:8px 10px;transition:0.2s;}
.profile-menu a:hover{background:#f5f7fa;color:#0d6efd !important;padding-left:14px;}
.progress{background:#eee;border-radius:20px;}
.progress-bar{border-radius:20px;}
.member-hover{position:relative;display:inline-block;cursor:pointer;}
.member-hover-box{
    position:absolute;
    top:110%;
    left:50%;
    transform:translateX(-50%);
    background:#fff;
    border-radius:10px;
    box-shadow:0 6px 18px rgba(0,0,0,0.1);
    padding:10px 12px;
    font-size:13px;
    width:220px;
    display:none;
    z-index:10;
}
.member-hover:hover .member-hover-box{display:block;}
</style>

<div class="card profile-card border-0 shadow">
    <div style="background:linear-gradient(135deg, {{ $colors[$level] }}, #ffffff); height:90px;"></div>

    <div class="card-body text-center pt-0">

        <img
            src="{{ $user->avatar ? asset('storage/'.$user->avatar) : asset('images/avatar-default.png') }}"
            class="rounded-circle profile-avatar"
            width="95"
            height="95"
            style="object-fit:cover; margin-top:-48px;"
        >

        <h6 class="mt-2 mb-0 fw-bold">{{ $user->name }}</h6>
        <small class="text-muted">Thành viên ELARA</small>

        @if($user->email_verified_at)
            <div class="mt-2">
                <span class="badge bg-success-subtle text-success border">
                    <i class="bi bi-check-circle me-1"></i> Email đã xác thực
                </span>
            </div>
        @endif

        <div class="mt-2 member-hover">
            <span class="member-badge px-3 py-1"
                  style="background: {{ $colors[$level] }}20; color: {{ $colors[$level] }};">
                Hạng {{ $levelNames[$level] }}
                <i class="bi bi-info-circle ms-1"></i>
            </span>

            <div class="member-hover-box text-start">
                <div class="fw-semibold mb-1">
                    Quyền lợi hạng {{ $levelNames[$level] }}
                </div>
                <ul class="mb-0 ps-3">
                    @foreach($benefits[$level] as $b)
                        <li>{{ $b }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="mt-2 fw-semibold">
            {{ number_format($spent, 0, ',', '.') }} đ
        </div>
        <small class="text-muted d-block">
            Chi tiêu năm nay
        </small>

        <div class="mt-2 fw-semibold" style="color:#8b5e34;">
            {{ number_format($points, 0, ',', '.') }} điểm
        </div>
        <small class="text-muted d-block">
            Điểm hiện có
        </small>

        @if($nextLevel)
            <div class="mt-2">
                <div class="progress" style="height:8px;">
                    <div class="progress-bar"
                         style="width: {{ $progress }}%; background: {{ $colors[$level] }};">
                    </div>
                </div>
                <small class="text-muted">
                    Còn {{ number_format($nextValue - $spent, 0, ',', '.') }} đ để lên {{ $levelNames[$nextLevel] }}
                </small>
            </div>
        @else
            <small class="text-success d-block mt-2">
                Hạng cao nhất
            </small>
        @endif

        <hr>

        <div class="profile-menu text-start small">
            <a class="d-block text-decoration-none {{ request()->routeIs('orders.*') ? 'fw-semibold text-primary' : 'text-dark' }}"
               href="{{ route('orders.history') }}">
                <i class="bi bi-box-seam me-2"></i> Đơn hàng của tôi
            </a>

            <a class="d-block text-decoration-none {{ request()->routeIs('profile.index') ? 'fw-semibold text-primary' : 'text-dark' }}"
               href="{{ route('profile.index') }}">
                <i class="bi bi-person me-2"></i> Thông tin tài khoản
            </a>

            <a class="d-block text-decoration-none {{ request()->routeIs('addresses.*') ? 'fw-semibold text-primary' : 'text-dark' }}"
               href="{{ route('addresses.index') }}">
                <i class="bi bi-geo-alt me-2"></i> Sổ địa chỉ
            </a>

            <a class="d-block text-decoration-none {{ request()->routeIs('points.*') ? 'fw-semibold text-primary' : 'text-dark' }}"
               href="{{ route('points.history') }}">
                <i class="bi bi-star me-2"></i> Lịch sử điểm
            </a>

            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button class="btn btn-outline-danger w-100 btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i> Đăng xuất
                </button>
            </form>
        </div>
    </div>
</div>
</div>