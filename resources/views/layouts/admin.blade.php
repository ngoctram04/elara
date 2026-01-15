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

    {{-- Admin CSS & JS --}}
    @vite([
        'resources/css/admin.css',
        'resources/js/admin.js'
    ])
</head>
<body>

<div class="admin-wrapper">

    {{-- ================= SIDEBAR ================= --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <i class="bi bi-emoji-smile"></i>
            <span>QUẢN LÝ BÁN HÀNG</span>
        </div>

        <ul class="sidebar-menu">

            <li>
                <a href="{{ route('admin.dashboard') }}"
                   class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Tổng quan
                </a>
            </li>

            <li class="nav-item">
    <a href="{{ route('admin.customers.index') }}"
       class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
        <i class="bi bi-people"></i>
        <span>Quản lý người dùng</span>
    </a>
</li>


            <li>
                <a href="{{ route('admin.categories.index') }}"
                   class="{{ request()->is('admin/categories*') ? 'active' : '' }}">
                    <i class="bi bi-grid"></i> Quản lý danh mục
                </a>
            </li>

            <li>
                <a href="{{ route('admin.brands.index') }}"
                   class="{{ request()->is('admin/brands*') ? 'active' : '' }}">
                    <i class="bi bi-tags"></i> Quản lý thương hiệu
                </a>
            </li>

            <li>
                <a href="{{ route('admin.products.index') }}"
                   class="{{ request()->is('admin/products*') ? 'active' : '' }}">
                    <i class="bi bi-box"></i> Quản lý sản phẩm
                </a>
            </li>

            <li>
                <a href="#"
                   class="{{ request()->is('admin/orders*') ? 'active' : '' }}">
                    <i class="bi bi-cart"></i> Quản lý đơn hàng
                </a>
            </li>

            {{-- ✅ PROMOTIONS --}}
            <li>
                <a href="{{ route('admin.promotions.index') }}"
                   class="{{ request()->is('admin/promotions*') ? 'active' : '' }}">
                    <i class="bi bi-gift"></i> Quản lý khuyến mãi
                </a>
            </li>

            <li>
                <a href="#"
                   class="{{ request()->is('admin/statistics*') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart"></i> Thống kê
                </a>
            </li>

        </ul>
    </aside>

    {{-- ================= MAIN ================= --}}
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
                    data-bs-toggle="dropdown"
                >
                    @if (auth()->user()->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}"
                             class="rounded-circle" width="32" height="32">
                    @else
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                             style="width:32px;height:32px;font-size:14px">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif

                    <span class="fw-semibold">{{ auth()->user()->name }}</span>
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
        </header>

        {{-- ================= CONTENT ================= --}}
        <section class="content container-fluid px-4 py-3">

            {{-- ALERT --}}
            @foreach (['success','info','error'] as $msg)
                @if (session($msg))
                    <div class="alert alert-{{ $msg == 'error' ? 'danger' : $msg }} alert-dismissible fade show">
                        {{ session($msg) }}
                        <button class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            @endforeach

            @yield('content')
        </section>

    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
