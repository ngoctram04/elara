<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'ELARA')</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    {{-- FRONTEND CSS --}}
    @vite([
        'resources/css/app.css',
        'resources/css/frontend.css',
        'resources/css/category.css',
        'resources/css/product.css',
        'resources/css/flash-sale.css',
        'resources/css/profile.css',
    ])

    @stack('styles')

    {{-- ================= CENTER NOTIFY STYLE ================= --}}
    <style>
        .center-notify {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.25);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: .25s;
            z-index: 99999;
        }
        .center-notify.show {
            opacity: 1;
            pointer-events: auto;
        }
        .center-notify-box {
            background: #16a34a;
            color: #fff;
            padding: 18px 28px;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 20px 40px rgba(0,0,0,.25);
            transform: scale(.9);
            transition: .25s;
        }
        .center-notify.show .center-notify-box {
            transform: scale(1);
        }
        .center-notify.error .center-notify-box {
            background: #dc2626;
        }
    </style>
</head>
<body>

{{-- ================= CENTER NOTIFICATION ================= --}}
<div id="center-notify" class="center-notify">
    <div class="center-notify-box" id="center-notify-box">
        <i class="bi bi-check-circle-fill"></i>
        <span id="center-notify-text">Thông báo</span>
    </div>
</div>

{{-- ================= HEADER ================= --}}
<header class="header-box">
    <div class="header-inner">

        <a href="{{ route('home') }}" class="logo">ELARA</a>

        <form class="search-pill" action="{{ route('shop') }}" method="GET">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm kiếm sản phẩm...">
            <button type="submit"><i class="bi bi-search"></i></button>
        </form>

        <div class="header-icons">
            @auth
                @include('components.user-dropdown')
            @else
                <a href="{{ route('login') }}" class="icon-btn">
                    <i class="bi bi-person"></i>
                </a>
            @endauth

            <a href="{{ route('cart.index') }}" class="icon-btn">
                <i class="bi bi-cart3"></i>
            </a>
        </div>
    </div>
</header>

{{-- ================= NAV ================= --}}
<nav class="nav-box">
    <div class="nav-inner">
        <div class="nav-category">
            <a href="#" class="nav-category-trigger">
                <i class="bi bi-list"></i> Danh mục sản phẩm
            </a>
            @include('components.mega-menu')
        </div>

        <a href="{{ route('shop', ['sort' => 'newest']) }}">Sản phẩm mới</a>
        <a href="#">Tin tức</a>
        <a href="#">Tra cứu đơn hàng</a>
        @auth
            <a href="{{ route('profile.orders') }}">Đơn hàng của tôi</a>
        @endauth
    </div>
</nav>

{{-- ================= MAIN ================= --}}
<main class="page-wrapper">
    @yield('content')
</main>

{{-- ================= FOOTER ================= --}}
<footer class="footer-box mt-2">
    <div class="footer-inner">
        <div class="row">
            <div class="col-md-3 mb-4">
                <h5 class="footer-title">ELARA</h5>
                <p class="footer-desc">
                    Thiên đường mỹ phẩm chính hãng giá tốt.<br>
                    Với hơn 100+ thương hiệu đồng hành.
                </p>
            </div>
        </div>
        <div class="footer-bottom">
            © {{ date('Y') }} ELARA. All Rights Reserved.
        </div>
    </div>
</footer>

{{-- BOOTSTRAP JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- ================= CENTER NOTIFY SCRIPT ================= --}}
<script>
function showCenterNotify(message, type = 'success') {
    const wrap = document.getElementById('center-notify');
    const text = document.getElementById('center-notify-text');
    const icon = wrap.querySelector('i');

    text.textContent = message;
    wrap.classList.remove('error');

    if (type === 'error') {
        wrap.classList.add('error');
        icon.className = 'bi bi-x-circle-fill';
    } else {
        icon.className = 'bi bi-check-circle-fill';
    }

    wrap.classList.add('show');
    setTimeout(() => wrap.classList.remove('show'), 2000);
}
</script>

@stack('scripts')

</body>
</html>
