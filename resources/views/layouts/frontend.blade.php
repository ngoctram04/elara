<!DOCTYPE html>

<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'ELARA')</title>

<meta name="csrf-token" content="{{ csrf_token() }}">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

@vite(['resources/js/app.js'])

@vite([
    'resources/js/app.js',
    'resources/css/app.css',
    'resources/css/category.css',
    'resources/css/product.css',
    'resources/css/flash-sale.css',
    'resources/css/profile.css',
    'resources/css/frontend.css',
    'resources/css/home.css',
    'resources/css/blog.css',
    'resources/css/cart.css',
])


@stack('styles')

</head>

<body>

<header class="header-box">
<div class="header-inner">

<a href="{{ route('home') }}" class="logo">ELARA</a>

<form class="search-pill position-relative" action="{{ route('shop') }}" method="GET">

<input
id="search-input"
type="text"
name="q"
value="{{ request('q') }}"
placeholder="Tìm kiếm sản phẩm..."
autocomplete="off"
>
<!-- NÚT MICRO -->
<button type="button" id="voice-btn" class="voice-btn">
<i class="bi bi-mic"></i>
</button>

<button type="submit">
<i class="bi bi-search"></i>
</button>

<div id="search-suggest-box" class="search-suggest-box"></div>

</form>
<div id="voice-popup" class="voice-popup">
    <div class="voice-box">

        <div class="voice-icon">
            <i class="bi bi-mic-fill"></i>
        </div>

        <div class="voice-text">
            Đang nghe...<br>
            Hãy nói tên sản phẩm
        </div>

    </div>
</div>
<div class="header-icons">

@auth

<div class="dropdown me-2">

    {{-- 🔔 ICON --}}
    <a href="#" class="icon-btn position-relative" data-bs-toggle="dropdown">

        <i class="bi bi-bell fs-5"></i>

        {{-- 🔴 BADGE --}}
        @php
            $unreadCount = auth()->user()->unreadNotifications()->count();
        @endphp

        @if($unreadCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif

    </a>

    {{-- 📦 DROPDOWN --}}
    <div class="dropdown-menu dropdown-menu-end p-0 shadow noti-dropdown">

        {{-- HEADER --}}
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
    <span class="fw-bold">Thông báo</span>

    <button id="markAllRead" class="btn btn-sm btn-light">
        ✓ Đã đọc tất cả
    </button>
</div>

        {{-- LIST --}}
        @php
            $notifications = auth()->user()
                ->notifications()
                ->latest()
                ->limit(100)
                ->get();
        @endphp

        @forelse($notifications as $noti)

            <a href="{{ route('notification.redirect', $noti->id) }}"
               class="dropdown-item noti-item d-flex gap-2 py-2 {{ is_null($noti->read_at) ? 'unread' : '' }}">

                {{-- ICON --}}
                <div class="noti-icon">
                    <i class="bi {{ $noti->data['icon'] ?? 'bi-bell' }} text-{{ $noti->data['color'] ?? 'secondary' }}"></i>
                </div>

                {{-- CONTENT --}}
                <div class="flex-grow-1">

                    <div class="noti-title">
                        {{ $noti->data['title'] ?? 'Thông báo' }}

                        {{-- 🔴 DOT --}}
                        @if(is_null($noti->read_at))
                            <span class="noti-dot"></span>
                        @endif
                    </div>

                    <div class="noti-message">
                        {{ $noti->data['message'] ?? '' }}
                    </div>

                    {{-- TIME --}}
                    <div class="noti-time">
                        {{ $noti->created_at->diffForHumans() }}
                    </div>

                </div>

            </a>

        @empty

            <div class="p-4 text-center text-muted">
                <i class="bi bi-bell-slash fs-4"></i><br>
                Không có thông báo
            </div>

        @endforelse

    </div>

</div>

@include('components.user-dropdown')

@else

<a href="{{ route('login') }}" class="icon-btn">
    <i class="bi bi-person"></i>
</a>

@endauth

<a href="{{ route('cart.index') }}" class="icon-btn">

<i class="bi bi-cart3"></i>

@php $cartCount = $cartCount ?? 0; @endphp

@if($cartCount > 0) <span class="cart-badge">
{{ $cartCount > 99 ? '99+' : $cartCount }} </span>
@endif

</a>

</div>
</div>
</header>

<nav class="nav-box">
<div class="nav-inner">

<div class="nav-category">

<a href="#" class="nav-category-trigger">
<i class="bi bi-list"></i> Danh mục 
</a>

@include('components.mega-menu')

</div>

<a href="{{ route('shop', ['sort' => 'newest']) }}">
Sản phẩm mới
</a>

<a href="{{ route('blogs.index') }}">
Tin tức
</a>
<a href="{{ route('policy') }}">
Chính sách
</a>
@auth <a href="{{ route('orders.history') }}">
Đơn hàng của tôi </a>
@endauth

</div>
</nav>

<main class="page-wrapper">
@yield('content')
</main>

<footer class="footer-box mt-4">
<div class="footer-inner">

<div class="row">

<!-- ELARA -->
<div class="col-lg-4 col-md-6 mb-4">
<h5 class="footer-title">ELARA</h5>

<p class="footer-desc">
    <span>ELARA là cửa hàng chuyên cung cấp</span><br>
    <span>các sản phẩm chất lượng cao với giá tốt</span><br>
    <span>và dịch vụ giao hàng nhanh chóng.</span>
</p>

<div class="footer-social">
<a href="#"><i class="bi bi-facebook"></i></a>
<a href="#"><i class="bi bi-instagram"></i></a>
<a href="#"><i class="bi bi-tiktok"></i></a>
</div>

</div>


<!-- LIÊN KẾT -->
<div class="col-lg-2 col-md-6 mb-4">

<h6 class="footer-title-sm">Liên kết</h6>

<ul class="footer-links">
<li><a href="/">Trang chủ</a></li>
<li><a href="/products">Sản phẩm</a></li>
<li><a href="/cart">Giỏ hàng</a></li>
<li><a href="/chat">Liên hệ</a></li>
</ul>

</div>


<!-- HỖ TRỢ -->
<div class="col-lg-3 col-md-6 mb-4">

<h6 class="footer-title-sm">Hỗ trợ</h6>

<ul class="footer-links">
    <li><a href="{{ url('/policy') }}">Chính sách đổi trả</a></li>
    <li><a href="{{ url('/policy') }}">Chính sách bảo mật</a></li>
    <li><a href="{{ url('/policy') }}">Điều khoản dịch vụ</a></li>
    <li><a href="{{ url('/policy') }}">Hướng dẫn mua hàng</a></li>
</ul>

</div>


<!-- LIÊN HỆ -->
<div class="col-lg-3 col-md-6 mb-4">

<h6 class="footer-title-sm">Liên hệ</h6>

<ul class="footer-info">
<li>
<i class="bi bi-geo-alt-fill"></i>
Vĩnh Long, Việt Nam
</li>

<li>
<i class="bi bi-telephone-fill"></i>
0954353423
</li>

<li>
<i class="bi bi-envelope-fill"></i>
elara.shop26@gmail.com
</li>
</ul>

</div>

</div>


<div class="footer-bottom">
© {{ date('Y') }} ELARA. All Rights Reserved.
</div>

</div>
</footer>

{{-- AI CHAT BUTTON --}}

<div class="floating-left">

<button class="float-btn ai-btn" onclick="toggleAIChat()">
<i class="bi bi-robot"></i>
</button>

</div>

{{-- CHAT NHÂN VIÊN --}}

<div class="floating-right">

<a href="/chat" class="float-btn contact-btn position-relative">

<div class="chat-float">
    <i class="bi bi-chat-dots"></i>

    <span id="chat-badge" class="chat-badge" style="display:none;">
        0
    </span>
</div>

<span id="chat-badge" class="chat-badge" style="display:none">
0
</span>

</a>

</div>

{{-- AI CHAT BOX --}}

<div id="ai-chat-box" class="ai-chat-box">

<div class="ai-header">

<span>AI tư vấn ELARA</span>

<button onclick="toggleAIChat()" class="btn-close btn-close-white"></button>

</div>

<div id="ai-messages" class="ai-body"></div>

<div class="ai-input">

<input type="text" id="ai-input" placeholder="Hỏi về mỹ phẩm...">

<button onclick="sendAI()">Gửi</button>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<x-toast />

@stack('scripts')
</body>
</html>
