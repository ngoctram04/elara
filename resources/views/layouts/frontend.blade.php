<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ELARA')</title>

    {{-- CSRF cho Ajax --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
@vite(['resources/js/app.js'])
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

<header class="header-box">
    <div class="header-inner">

        <a href="{{ route('home') }}" class="logo">ELARA</a>

        {{-- SEARCH --}}
        <form class="search-pill position-relative" action="{{ route('shop') }}" method="GET">
            <input
                id="search-input"
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Tìm kiếm sản phẩm..."
                autocomplete="off"
            >
            <button type="submit">
                <i class="bi bi-search"></i>
            </button>

            <div id="search-suggest-box" class="search-suggest-box"></div>
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

<main class="page-wrapper">
    @yield('content')
</main>

<footer class="footer-box mt-2">
    <div class="footer-inner">
        <div class="footer-bottom">
            © {{ date('Y') }} ELARA. All Rights Reserved.
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<x-toast />
@stack('scripts')


{{-- ================= SEARCH SCRIPT ================= --}}
<script>
document.addEventListener("DOMContentLoaded", function () {

    const input = document.getElementById("search-input");
    const box = document.getElementById("search-suggest-box");
    const form = input.closest("form");
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    if (!input) return;

    /* ================= HISTORY ================= */
    function loadHistory() {
        fetch('/search/history')
            .then(res => res.json())
            .then(data => {
                box.innerHTML = "";

                if (data.length === 0) {
                    box.style.display = "none";
                    return;
                }

                data.forEach(item => {
                    let row = document.createElement("div");
                    row.className = "search-history-item";

                    row.innerHTML = `
                        <span class="history-left">
                            <i class="bi bi-clock-history"></i> ${item}
                        </span>
                        <span class="history-delete">&times;</span>
                    `;

                    // Click → search
                    row.querySelector('.history-left').onclick = function () {
                        input.value = item;
                        form.submit();
                    };

                    // Xóa
                    row.querySelector('.history-delete').onclick = function(e) {
                        e.stopPropagation();

                        fetch('/search/history/delete', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf
                            },
                            body: JSON.stringify({ keyword: item })
                        }).then(() => loadHistory());
                    };

                    box.appendChild(row);
                });

                box.style.display = "block";
            });
    }

    /* ================= SUGGEST ================= */
    function loadSuggest() {
        let keyword = input.value.trim();

        if (!keyword) {
            loadHistory();
            return;
        }

        fetch(`/search/suggest?q=${keyword}`)
            .then(res => res.json())
            .then(data => {
                box.innerHTML = "";

                if (data.length === 0) {
                    box.style.display = "none";
                    return;
                }

                data.forEach(item => {
                    let div = document.createElement("div");
                    div.className = "search-suggest-item";
                    div.innerText = item;

                    div.onclick = function () {
                        input.value = item;
                        form.submit();
                    };

                    box.appendChild(div);
                });

                box.style.display = "block";
            });
    }

    input.addEventListener("focus", loadHistory);
    input.addEventListener("input", loadSuggest);

    document.addEventListener("click", function(e){
        if (!input.contains(e.target) && !box.contains(e.target)) {
            box.style.display = "none";
        }
    });

});
</script>

</body>
</html>