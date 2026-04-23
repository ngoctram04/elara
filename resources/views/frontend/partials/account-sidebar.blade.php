@php
    $user = \App\Models\User::find(auth()->id());

    $points = (int) ($user->loyalty_points ?? 0);
    $spent = (float) ($user->yearly_spent ?? 0);
    $level = strtolower($user->member_level ?? 'bronze');

    $levels = [
        'bronze'  => 0,
        'silver'  => 1000000,
        'gold'    => 3000000,
        'diamond' => 10000000,
    ];

    $levelNames = [
        'bronze'  => 'Đồng',
        'silver'  => 'Bạc',
        'gold'    => 'Vàng',
        'diamond' => 'Kim Cương',
    ];

    $colors = [
        'bronze'  => '#b7794b',
        'silver'  => '#7c8796',
        'gold'    => '#d4a017',
        'diamond' => '#36b9cc',
    ];

    $benefits = [
        'bronze' => [
            'Không có ưu đãi',
        ],
        'silver' => [
            'Giảm 5% vào ngày sinh nhật',
        ],
        'gold' => [
            'Miễn phí vận chuyển đơn từ 300.000đ',
            'Giảm 10% vào ngày sinh nhật',
        ],
        'diamond' => [
            'Miễn phí vận chuyển mọi đơn',
            'Giảm 15% vào ngày sinh nhật',
        ],
    ];

    if (!array_key_exists($level, $levels)) {
        $level = 'bronze';
    }

    $nextLevel = null;
    $nextValue = null;

    foreach ($levels as $name => $value) {
        if ($spent < $value) {
            $nextLevel = $name;
            $nextValue = $value;
            break;
        }
    }


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
        .profile-sidebar-card {
            border: 0;
            border-radius: 24px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 12px 35px rgba(25, 42, 70, 0.10);
            transition: all 0.28s ease;
        }

        .profile-sidebar-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 40px rgba(25, 42, 70, 0.14);
        }

        .profile-sidebar-top {
            height: 108px;
            position: relative;
            background:
                radial-gradient(circle at top right, rgba(255,255,255,0.55), transparent 35%),
                linear-gradient(135deg, {{ $colors[$level] }} 0%, #f8fbff 100%);
        }

        .profile-sidebar-body {
            padding: 0 20px 22px;
        }

        .profile-avatar-wrap {
            margin-top: -54px;
            position: relative;
            z-index: 2;
        }

        .profile-avatar {
            width: 104px;
            height: 104px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid #fff;
            background: #fff;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.14);
        }

        .profile-name {
            font-size: 18px;
            font-weight: 700;
            color: #1f2d3d;
            margin-top: 12px;
            margin-bottom: 2px;
        }

        .profile-subtitle {
            font-size: 13px;
            color: #7b8794;
        }

        .verify-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 12px;
            padding: 7px 12px;
            border-radius: 999px;
            background: #eefaf2;
            color: #198754;
            border: 1px solid #d7f1df;
            font-size: 12px;
            font-weight: 600;
        }

        .member-hover {
            position: relative;
            display: inline-block;
            margin-top: 14px;
        }

        .member-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            background: {{ $colors[$level] }}18;
            color: {{ $colors[$level] }};
            border: 1px solid {{ $colors[$level] }}26;
            transition: all 0.25s ease;
        }

        .member-badge:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 18px {{ $colors[$level] }}22;
        }

        .member-hover-box {
            position: absolute;
            top: calc(100% + 10px);
            left: 50%;
            transform: translateX(-50%);
            width: 250px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 16px 40px rgba(18, 38, 63, 0.14);
            border: 1px solid #edf1f5;
            padding: 14px 15px;
            display: none;
            z-index: 20;
            text-align: left;
        }

        .member-hover:hover .member-hover-box {
            display: block;
        }

        .member-hover-box::before {
            content: "";
            position: absolute;
            top: -7px;
            left: 50%;
            transform: translateX(-50%) rotate(45deg);
            width: 14px;
            height: 14px;
            background: #fff;
            border-left: 1px solid #edf1f5;
            border-top: 1px solid #edf1f5;
        }

        .member-benefit-title {
            font-size: 13px;
            font-weight: 700;
            color: #243447;
            margin-bottom: 8px;
        }

        .member-benefit-list {
            margin: 0;
            padding-left: 18px;
            color: #5c6b7a;
            font-size: 13px;
        }

        .member-benefit-list li + li {
            margin-top: 5px;
        }

        .profile-stat-box {
            margin-top: 16px;
            padding: 14px;
            border-radius: 18px;
            background: linear-gradient(180deg, #fbfcfe 0%, #f5f8fc 100%);
            border: 1px solid #edf2f7;
        }

        .profile-stat-value {
            font-size: 18px;
            font-weight: 800;
            color: #1f2d3d;
            line-height: 1.2;
        }

        .profile-stat-label {
            font-size: 12px;
            color: #7b8794;
            margin-top: 3px;
        }

        .profile-points {
            color: #8a5a32;
        }

        .level-progress-wrap {
            margin-top: 16px;
            text-align: left;
        }

        .level-progress-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            font-size: 12px;
            color: #6c7a89;
            font-weight: 600;
        }

        .level-progress {
            height: 10px;
            background: #edf1f5;
            border-radius: 999px;
            overflow: hidden;
        }

        .level-progress .progress-bar {
            border-radius: 999px;
            background: linear-gradient(90deg, {{ $colors[$level] }}, {{ $colors[$level] }}cc);
        }

        .level-note {
            margin-top: 8px;
            font-size: 12px;
            color: #7b8794;
            line-height: 1.5;
        }

        .level-note strong {
            color: #243447;
        }

        .top-level-note {
            margin-top: 14px;
            padding: 10px 12px;
            border-radius: 14px;
            background: #eefaf2;
            color: #198754;
            font-size: 13px;
            font-weight: 700;
        }

        .profile-divider {
            border: 0;
            border-top: 1px solid #eef2f6;
            margin: 18px 0 14px;
        }

        .profile-menu {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .profile-menu-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 13px;
            border-radius: 14px;
            text-decoration: none;
            color: #2d3748;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.22s ease;
            background: #fff;
        }

        .profile-menu-link i {
            width: 18px;
            text-align: center;
            font-size: 16px;
        }

        .profile-menu-link:hover {
            background: #f6faff;
            color: #0d6efd;
            transform: translateX(3px);
        }

        .profile-menu-link.active {
            background: linear-gradient(90deg, #eef5ff 0%, #f8fbff 100%);
            color: #0d6efd;
            font-weight: 700;
            border: 1px solid #dbe9ff;
        }

        .logout-btn {
            margin-top: 12px;
            width: 100%;
            border-radius: 14px;
            padding: 10px 14px;
            font-size: 14px;
            font-weight: 600;
            border: 1px solid #f3c7cc;
            background: #fff5f6;
            color: #dc3545;
            transition: all 0.25s ease;
        }

        .logout-btn:hover {
            background: #dc3545;
            color: #fff;
            border-color: #dc3545;
        }

        @media (max-width: 991.98px) {
            .member-hover-box {
                width: 230px;
            }
        }
    </style>

    <div class="card profile-sidebar-card">
        <div class="profile-sidebar-top"></div>

        <div class="profile-sidebar-body text-center">
            <div class="profile-avatar-wrap">
                <img
                    src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('images/avatar-default.png') }}"
                    alt="{{ $user->name }}"
                    class="profile-avatar"
                >
            </div>

            <div class="profile-name">{{ $user->name }}</div>
            <div class="profile-subtitle">Thành viên ELARA</div>

            @if($user->email_verified_at)
                <div class="verify-badge">
                    <i class="bi bi-patch-check-fill"></i>
                    Email đã xác thực
                </div>
            @endif

            <div class="member-hover">
                <span class="member-badge">
                    <i class="bi bi-award-fill"></i>
                    Hạng {{ $levelNames[$level] }}
                    <i class="bi bi-info-circle"></i>
                </span>

                <div class="member-hover-box">
                    <div class="member-benefit-title">
                        Quyền lợi hạng {{ $levelNames[$level] }}
                    </div>
                    <ul class="member-benefit-list">
                        @foreach($benefits[$level] as $b)
                            <li>{{ $b }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="profile-stat-box">
                <div class="profile-stat-value">
                    {{ number_format($spent, 0, ',', '.') }} đ
                </div>
                <div class="profile-stat-label">Chi tiêu năm nay</div>
            </div>

            <div class="profile-stat-box">
                <div class="profile-stat-value profile-points">
                    {{ number_format($points, 0, ',', '.') }} điểm
                </div>
                <div class="profile-stat-label">Điểm hiện có</div>
            </div>

            @if($nextLevel)
                <div class="level-progress-wrap">
                    <div class="level-progress-head">
                        <span>Tiến độ thăng hạng</span>
                        <span>{{ round($progress) }}%</span>
                    </div>

                    <div class="progress level-progress">
                        <div class="progress-bar" style="width: {{ $progress }}%;"></div>
                    </div>

                    <div class="level-note">
                        Còn <strong>{{ number_format($nextValue - $spent, 0, ',', '.') }} đ</strong>
                        để lên hạng <strong>{{ $levelNames[$nextLevel] }}</strong>
                    </div>
                </div>
            @else
                <div class="top-level-note">
                    <i class="bi bi-stars me-1"></i>
                    Bạn đang ở hạng cao nhất
                </div>
            @endif

            <hr class="profile-divider">

            <div class="profile-menu text-start">

                <a class="profile-menu-link {{ request()->routeIs('profile.index') ? 'active' : '' }}"
                   href="{{ route('profile.index') }}">
                    <i class="bi bi-person"></i>
                    <span>Thông tin tài khoản</span>
                </a>

                <a class="profile-menu-link {{ request()->routeIs('addresses.*') ? 'active' : '' }}"
                   href="{{ route('addresses.index') }}">
                    <i class="bi bi-geo-alt"></i>
                    <span>Sổ địa chỉ</span>
                </a>

                <a class="profile-menu-link {{ request()->routeIs('points.*') ? 'active' : '' }}"
                   href="{{ route('points.history') }}">
                    <i class="bi bi-star"></i>
                    <span>Lịch sử điểm</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="bi bi-box-arrow-right me-1"></i>
                        Đăng xuất
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>