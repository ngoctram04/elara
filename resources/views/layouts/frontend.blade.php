<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title','ELARA')</title>

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    {{-- App CSS --}}
    @vite(['resources/css/app.css','resources/css/frontend.css'])
</head>
<body>

{{-- ================= HEADER ================= --}}
<header class="header-box">
    <div class="header-inner">

        {{-- LOGO --}}
        <a href="{{ route('home') }}" class="logo">
            ELARA
        </a>

        {{-- SEARCH --}}
        <form class="search-pill" action="#" method="GET">
            <input type="text" name="q" placeholder="Tìm kiếm sản phẩm...">
            <button type="submit">
                <i class="bi bi-search"></i>
            </button>
        </form>

        {{-- ICONS --}}
        <div class="header-icons">

            {{-- USER (ĐỨNG TRƯỚC GIỎ HÀNG) --}}
            @auth
                @include('components.user-dropdown')
            @else
                <a href="{{ route('login') }}" class="icon-btn" title="Đăng nhập">
                    <i class="bi bi-person"></i>
                </a>
            @endauth

            {{-- CART --}}
            <a href="#" class="icon-btn position-relative" title="Giỏ hàng">
                <i class="bi bi-cart3"></i>

                {{-- badge (sau này gắn số lượng) --}}
                {{-- <span class="cart-badge">2</span> --}}
            </a>
        </div>

    </div>
</header>

{{-- ================= NAV ================= --}}
<nav class="nav-box">
    <div class="nav-inner">
        <a href="#">
            <i class="bi bi-list"></i>
            Danh mục sản phẩm
        </a>
        <a href="#">Sản phẩm mới</a>
        <a href="#">Tin tức</a>
        <a href="{{ route('profile.orders') }}">Đơn hàng của tôi</a>
    </div>
</nav>

{{-- ================= PAGE ================= --}}
<main class="page-wrapper">
    @yield('content')
</main>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
