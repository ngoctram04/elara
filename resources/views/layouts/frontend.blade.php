<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title','ELARA')</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    {{-- CSS --}}
    @vite(['resources/css/app.css','resources/css/frontend.css'])
</head>
<body>

{{-- HEADER --}}
<header class="header-box">
    <div class="header-inner">

        <a href="{{ route('shop') }}" class="logo">ELARA</a>

        <form class="search-pill">
            <input type="text" placeholder="Tìm kiếm sản phẩm...">
            <button><i class="bi bi-search"></i></button>
        </form>

        <div class="header-icons">
            @auth
                @include('components.user-dropdown')
            @else
                <a href="{{ route('login') }}" class="icon-btn">
                    <i class="bi bi-person"></i>
                </a>
            @endauth

            <a href="#" class="icon-btn">
                <i class="bi bi-cart3"></i>
            </a>
        </div>
    </div>
</header>

{{-- NAV --}}
<nav class="nav-box">
    <div class="nav-inner">
        <a href="#"><i class="bi bi-list"></i> Danh mục sản phẩm</a>
        <a href="#">Sản phẩm mới</a>
        <a href="#">Tin tức</a>
        <a href="{{ route('profile.orders') }}">Đơn hàng của tôi</a>
    </div>
</nav>

{{-- PAGE --}}
<main class="page-wrapper">
    @yield('content')
</main>
<footer class="footer-box mt-5">
    <div class="footer-inner">
        <div class="row">

            {{-- CỘT 1 --}}
            <div class="col-md-3 mb-4">
                <h5 class="footer-title">ELARA</h5>
                <p class="footer-desc">
                    Thiên đường mỹ phẩm chính hãng giá tốt.  
                    Với hơn 100+ thương hiệu đồng hành.
                </p>

                <ul class="footer-info">
                    <li><i class="bi bi-geo-alt"></i> Vĩnh Long</li>
                    <li><i class="bi bi-envelope"></i> elara.shop26@gmail.com</li>
                    <li><i class="bi bi-telephone"></i> 0703771879</li>
                </ul>
            </div>

            {{-- CỘT 2 --}}
            <div class="col-md-3 mb-4">
                <h6 class="footer-title-sm">Truy cập nhanh</h6>
                <ul class="footer-links">
                    <li><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li><a href="{{ route('shop') }}">Sản phẩm</a></li>
                    <li><a href="#">Tin tức</a></li>
                    <li><a href="#">Giỏ hàng</a></li>
                    <li><a href="{{ route('login') }}">Đăng nhập</a></li>
                    <li><a href="{{ route('register') }}">Đăng ký</a></li>
                </ul>
            </div>

            {{-- CỘT 3 --}}
            <div class="col-md-3 mb-4">
                <h6 class="footer-title-sm">Hỗ trợ khách hàng</h6>
                <ul class="footer-links">
                    <li><a href="#">Chính sách đổi trả</a></li>
                    <li><a href="#">Chính sách bảo mật</a></li>
                    <li><a href="#">Phương thức thanh toán</a></li>
                    <li><a href="#">Câu hỏi thường gặp</a></li>
                    <li><a href="#">Hỗ trợ khách hàng</a></li>
                    <li><a href="#">Tin tức</a></li>
                </ul>
            </div>

            {{-- CỘT 4 --}}
            <div class="col-md-3 mb-4">
                <h6 class="footer-title-sm">Đăng ký nhận tin</h6>
                <form class="footer-newsletter">
                    <input type="text" placeholder="Họ và tên">
                    <input type="email" placeholder="Email của bạn">
                    <button type="submit">Đăng ký ngay</button>
                </form>
            </div>

        </div>

        <div class="footer-bottom">
            © 2026 ELARA. All Rights Reserved.
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
