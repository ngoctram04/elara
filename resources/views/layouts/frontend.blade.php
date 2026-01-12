<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>ELARA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-blue-50">

<header class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <a href="/" class="text-2xl font-bold text-blue-500">ELARA</a>

        <nav class="space-x-4">
            <a href="/" class="text-gray-700 hover:text-blue-500">Trang chủ</a>

            @auth
                <span class="text-gray-600">
                    Xin chào, {{ auth()->user()->name }}
                </span>

                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button class="text-red-500 hover:underline">
                        Đăng xuất
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-blue-500">
                    Đăng nhập
                </a>
                <a href="{{ route('register') }}" class="text-blue-500">
                    Đăng ký
                </a>
            @endauth
        </nav>
    </div>
</header>

<main class="max-w-7xl mx-auto px-6 py-8">
    @yield('content')
</main>

</body>
</html>
