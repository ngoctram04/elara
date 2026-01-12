<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Admin CSS & JS (VITE) --}}
    @vite([
        'resources/css/admin.css',
        'resources/js/admin.js'
    ])
</head>
<body>

<div class="admin-wrapper">

    {{-- SIDEBAR --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <i class="bi bi-emoji-smile"></i>
            <span>QUẢN LÝ BÁN HÀNG</span>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-speedometer2"></i> Tổng quan
                </a>
            </li>
            <li><a href="#"><i class="bi bi-person"></i> Quản lý người dùng</a></li>
            <li><a href="#"><i class="bi bi-grid"></i> Quản lý danh mục</a></li>
            <li><a href="#"><i class="bi bi-tags"></i> Quản lý thương hiệu</a></li>
            <li><a href="#"><i class="bi bi-box"></i> Quản lý sản phẩm</a></li>
            <li><a href="#"><i class="bi bi-cart"></i> Quản lý đơn hàng</a></li>
            <li><a href="#"><i class="bi bi-gift"></i> Quản lý khuyến mãi</a></li>
            <li><a href="#"><i class="bi bi-bar-chart"></i> Thống kê</a></li>
        </ul>
    </aside>

    {{-- MAIN --}}
    <main class="main-content">

        {{-- TOPBAR --}}
        <header class="topbar d-flex align-items-center">
            <button class="btn btn-light d-md-none" id="toggleSidebar">
                <i class="bi bi-list"></i>
            </button>

            {{-- ADMIN DROPDOWN --}}
            <div class="dropdown ms-auto">
                <button
                    class="btn btn-light d-flex align-items-center gap-2 rounded-pill dropdown-toggle"
                    type="button"
                    id="adminDropdown"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >
                    {{-- AVATAR --}}
                    @if (auth()->user()->avatar)
                        <img
                            src="{{ asset('storage/' . auth()->user()->avatar) }}"
                            class="rounded-circle"
                            width="32"
                            height="32"
                            alt="Avatar"
                        >
                    @else
                        <div
                            class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                            style="width:32px;height:32px;font-size:14px"
                        >
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif

                    <span class="fw-semibold">
                        {{ auth()->user()->name ?? 'Admin' }}
                    </span>
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow"
                    aria-labelledby="adminDropdown"
                >
                    <li class="dropdown-header">
                        Xin chào, <strong>{{ auth()->user()->name ?? 'Admin' }}</strong>
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
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </header>

        {{-- CONTENT --}}
        <section class="content">
            @yield('content')
        </section>
    </main>

</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
