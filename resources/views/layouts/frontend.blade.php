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


</head>
<body>

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
            @php $cartCount = $cartCount ?? 0; @endphp
            @if($cartCount > 0)
                <span class="cart-badge">
                    {{ $cartCount > 99 ? '99+' : $cartCount }}
                </span>
            @endif
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

    @auth
        <a href="{{ route('orders.history') }}">Đơn hàng của tôi</a>
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
        <div class="footer-bottom">
            © {{ date('Y') }} ELARA. All Rights Reserved.
        </div>
    </div>
</footer>

{{-- Bootstrap JS --}}

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')

{{-- ================= GLOBAL TOAST (dùng chung toàn site) ================= --}} <x-toast />

</body>
</html>
