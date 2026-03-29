<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    @vite([
        'resources/css/admin.css',
        'resources/js/admin.js'
    ])

    @stack('styles')
</head>

<body>

<x-toast />

<div class="admin-wrapper">

{{-- ================= SIDEBAR ================= --}}
<aside class="sidebar" id="sidebar">

<div class="sidebar-logo">
<i class="bi bi-emoji-smile"></i>
<span>QUẢN LÝ BÁN HÀNG</span>
</div>

<ul class="sidebar-menu">

{{-- USERS --}}
<li>
<a href="{{ route('admin.customers.index') }}"
class="{{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
<i class="bi bi-people"></i>
Quản lý người dùng
</a>
</li>

{{-- CATEGORY --}}
<li>
<a href="{{ route('admin.categories.index') }}"
class="{{ request()->is('admin/categories*') ? 'active' : '' }}">
<i class="bi bi-grid"></i>
Quản lý danh mục
</a>
</li>

{{-- BRAND --}}
<li>
<a href="{{ route('admin.brands.index') }}"
class="{{ request()->is('admin/brands*') ? 'active' : '' }}">
<i class="bi bi-tags"></i>
Quản lý thương hiệu
</a>
</li>

{{-- PRODUCT --}}
<li>
<a href="{{ route('admin.products.index') }}"
class="{{ request()->is('admin/products*') ? 'active' : '' }}">
<i class="bi bi-box"></i>
Quản lý sản phẩm
</a>
</li>
{{-- REVIEWS --}}
<li>
<a href="{{ route('admin.reviews.index') }}"
class="{{ request()->is('admin/reviews*') ? 'active' : '' }}">
<i class="bi bi-star"></i>
Quản lý đánh giá
</a>
</li>
{{-- STOCK --}}
{{-- ================= INVENTORY ================= --}}
<li>

<a class="d-flex justify-content-between align-items-center
{{ request()->is('admin/stock*') || request()->is('admin/inventory*') ? 'active' : '' }}"
data-bs-toggle="collapse"
href="#inventoryMenu"
role="button">

<span>
<i class="bi bi-box-arrow-in-down"></i>
Quản lý kho
</span>

<i class="bi bi-chevron-down small"></i>

</a>

<div class="collapse
{{ request()->is('admin/stock*') || request()->is('admin/inventory*') ? 'show' : '' }}"
id="inventoryMenu">

<ul class="submenu">

<li>
<a href="{{ route('admin.stock.create') }}"
class="{{ request()->is('admin/stock-import*') ? 'active' : '' }}">
Nhập kho
</a>
</li>

<li>
<a href="{{ route('admin.inventory.logs') }}"
class="{{ request()->is('admin/inventory/logs*') ? 'active' : '' }}">
Lịch sử tồn kho
</a>
</li>

<li>
<a href="{{ route('admin.inventory.low') }}"
class="{{ request()->is('admin/inventory/low*') ? 'active' : '' }}">
Sắp hết hàng
</a>
</li>

<li>
<a href="{{ route('admin.inventory.report') }}"
class="{{ request()->is('admin/inventory/report*') ? 'active' : '' }}">
Báo cáo tồn kho
</a>
</li>
<li>
    <a href="{{ route('admin.inventory.near_expiry') }}"
class="{{ request()->is('admin/inventory/near-expiry*') ? 'active' : '' }}">
Quản lý lô hàng
</a>
</li>
</ul>

</div>

</li>

{{-- ================= ORDERS ================= --}}
<li>

<a class="d-flex justify-content-between align-items-center
{{ request()->is('admin/orders*') || request()->is('admin/refunds*') ? 'active' : '' }}"
data-bs-toggle="collapse"
href="#orderMenu"
role="button">

<span>
<i class="bi bi-cart"></i>
Quản lý đơn hàng
</span>

<i class="bi bi-chevron-down small"></i>

</a>

<div class="collapse {{ request()->is('admin/orders*') || request()->is('admin/refunds*') ? 'show' : '' }}"
id="orderMenu">

<ul class="submenu">

<li>
<a href="{{ route('admin.orders.index') }}"
class="{{ request()->is('admin/orders*') ? 'active' : '' }}">
Danh sách đơn hàng
</a>
</li>

<li>
<a href="{{ route('admin.refunds.index') }}"
class="{{ request()->is('admin/refunds*') ? 'active' : '' }}">
Yêu cầu hoàn tiền
</a>
</li>

</ul>

</div>

</li>

{{-- PROMOTION --}}
<li>
<a href="{{ route('admin.promotions.index') }}"
class="{{ request()->is('admin/promotions*') ? 'active' : '' }}">
<i class="bi bi-gift"></i>
Quản lý khuyến mãi
</a>
</li>
{{-- BLOG --}}
<li>
<a href="{{ route('admin.blogs.index') }}"
class="{{ request()->is('admin/blogs*') ? 'active' : '' }}">
<i class="bi bi-journal-text"></i>
Quản lý Blog
</a>
</li>
{{-- ================= CUSTOMER SUPPORT ================= --}}
<li>

<a class="d-flex justify-content-between align-items-center
{{ request()->is('admin/questions*') || request()->is('admin/messages*') ? 'active' : '' }}"
data-bs-toggle="collapse"
href="#supportMenu"
role="button">

<span>
<i class="bi bi-headset"></i>
Hỗ trợ khách hàng
</span>

<i class="bi bi-chevron-down small"></i>

</a>

<div class="collapse {{ request()->is('admin/questions*') || request()->is('admin/messages*') ? 'show' : '' }}"
id="supportMenu">

<ul class="submenu">

<li>
<a href="{{ route('admin.questions.index') }}"
class="{{ request()->is('admin/questions*') ? 'active' : '' }}">
Hỏi đáp sản phẩm
</a>
</li>

<li>
<a href="{{ route('admin.messages.index') }}"
class="{{ request()->is('admin/messages*') ? 'active' : '' }}">
Tin nhắn khách hàng
</a>
</li>

</ul>

</div>

</li>
{{-- REPORT --}}
<li>
<a href="{{ route('admin.reports.index') }}"
class="{{ request()->is('admin/reports*') ? 'active' : '' }}">
<i class="bi bi-bar-chart"></i>
Thống kê
</a>
</li>

</ul>

</aside>

{{-- ================= MAIN ================= --}}
<main class="main-content">

<header class="topbar d-flex align-items-center">

    {{-- MENU --}}
    <button class="btn btn-light" id="toggleSidebar">
        <i class="bi bi-list"></i>
    </button>

    {{-- RIGHT SIDE --}}
    <div class="ms-auto d-flex align-items-center gap-2">

        {{-- 🔔 NOTIFICATION --}}
        <div class="dropdown">

            <a href="#" class="btn btn-light position-relative" data-bs-toggle="dropdown">
                <i class="bi bi-bell fs-5"></i>

                @php
                    $unreadCount = auth()->user()->unreadNotifications()->count();
                @endphp

                @if($unreadCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                    </span>
                @endif
            </a>

            <div class="dropdown-menu dropdown-menu-end p-0 shadow"
                 style="width: 400px; max-height: 420px; overflow-y: auto; border-radius: 10px;">

                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
    <span class="fw-bold">Thông báo</span>

    <button id="markAllReadAdmin" class="btn btn-sm btn-light">
        ✓ Đọc tất cả
    </button>
</div>

                @php
                    $notifications = auth()->user()->notifications()->latest()->limit(100)->get();
                @endphp

                @forelse($notifications as $noti)

@php
    $isUnread = is_null($noti->read_at);
@endphp

<a href="{{ route('notification.redirect', $noti->id) }}"
   class="dropdown-item noti-item d-flex gap-3 px-3 py-3 {{ $isUnread ? 'noti-unread' : '' }}">

    {{-- ICON --}}
    <div class="noti-icon">
        <i class="bi {{ $noti->data['icon'] ?? 'bi-bell' }}"></i>
    </div>

    {{-- CONTENT --}}
    <div class="flex-grow-1">

        <div class="d-flex justify-content-between align-items-center">

            <div class="noti-title">
                {{ $noti->data['title'] ?? 'Thông báo' }}
            </div>

            {{-- 🔴 DOT chưa đọc --}}
            @if($isUnread)
                <span class="noti-dot"></span>
            @endif

        </div>

        <div class="noti-message">
            {{ $noti->data['message'] ?? '' }}
        </div>

        <div class="noti-time">
            {{ $noti->created_at->diffForHumans() }}
        </div>

    </div>

</a>

@empty

<div class="p-4 text-center text-muted">
    Không có thông báo
</div>

@endforelse

            </div>

        </div>

        {{-- 👤 USER DROPDOWN --}}
        <div class="dropdown">

            <button
                class="btn btn-light d-flex align-items-center gap-2 rounded-pill dropdown-toggle"
                data-bs-toggle="dropdown">

                @if (auth()->user()->avatar)
                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}"
                         class="rounded-circle"
                         width="32" height="32">
                @else
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                         style="width:32px;height:32px;font-size:14px">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif

                <span class="fw-semibold">
                    {{ auth()->user()->name }}
                </span>

            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow">

                <li class="dropdown-header">
                    Xin chào, <strong>{{ auth()->user()->name }}</strong>
                </li>

                <li>
                    <a class="dropdown-item" href="{{ route('admin.profile.show') }}">
                        <i class="bi bi-person me-2"></i> Xem thông tin
                    </a>
                </li>

                <li>
                    <a class="dropdown-item" href="{{ route('admin.profile.edit') }}">
                        <i class="bi bi-pencil-square me-2"></i> Chỉnh sửa thông tin
                    </a>
                </li>

                <li><hr class="dropdown-divider"></li>

                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
                        </button>
                    </form>
                </li>

            </ul>

        </div>

    </div>

</header>

<section class="content container-fluid px-4 py-3">
    @yield('content')
</section>

</main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@stack('scripts')
<script>
document.addEventListener("DOMContentLoaded", function(){

    const btn = document.getElementById("markAllReadAdmin");

    if(btn){
        btn.addEventListener("click", function(){

            fetch("{{ route('notifications.markAllRead') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    "Accept": "application/json"
                }
            })
            .then(res => res.json())
            .then(data => {

                if(data.success){

                    // ✅ 1. Ẩn badge đỏ (số lượng)
                    const badge = document.querySelector('.badge.bg-danger');
                    if(badge){
                        badge.remove();
                    }

                    // ✅ 2. Xóa dấu chấm đỏ
                    document.querySelectorAll('.noti-dot').forEach(e => e.remove());

                    // ✅ 3. Bỏ nền unread
                    document.querySelectorAll('.noti-unread').forEach(e => {
                        e.classList.remove('noti-unread');
                    });

                    // ✅ 4. Disable nút luôn (khỏi bấm lại)
                    btn.disabled = true;
                    btn.innerText = "Đã đọc";

                }

            })
            .catch(err => {
                console.error("Lỗi mark all read:", err);
            });

        });
    }

});
</script>
</body>
</html>