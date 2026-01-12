<x-guest-layout>
    <div class="max-w-md mx-auto bg-white p-8 rounded-2xl shadow-lg border border-blue-100">
        <!-- Thông báo session -->
        <x-auth-session-status class="mb-4 text-blue-500" :status="session('status')" />

        <h2 class="text-2xl font-bold text-center text-blue-500 mb-6">
            Đăng nhập
        </h2>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email -->
            <div>
                <x-input-label for="email" value="Email" class="text-blue-600" />
                <x-text-input
                    id="email"
                    class="block mt-1 w-full rounded-lg border-blue-200 focus:border-blue-400 focus:ring focus:ring-blue-100"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autofocus
                />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Mật khẩu -->
            <div class="mt-4">
                <x-input-label for="password" value="Mật khẩu" class="text-blue-600" />
                <x-text-input
                    id="password"
                    class="block mt-1 w-full rounded-lg border-blue-200 focus:border-blue-400 focus:ring focus:ring-blue-100"
                    type="password"
                    name="password"
                    required
                />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Ghi nhớ đăng nhập -->
            <div class="flex items-center mt-4">
                <input
                    id="remember_me"
                    type="checkbox"
                    class="rounded border-blue-300 text-blue-500 focus:ring-blue-200"
                    name="remember"
                >
                <label for="remember_me" class="ms-2 text-sm text-gray-600">
                    Ghi nhớ đăng nhập
                </label>
            </div>

            <!-- Nút + link -->
            <div class="mt-6 flex flex-col gap-3">
                <x-primary-button class="w-full justify-center bg-blue-400 hover:bg-blue-500">
                    Đăng nhập
                </x-primary-button>

                @if (Route::has('password.request'))
                    <a
                        href="{{ route('password.request') }}"
                        class="text-center text-sm text-blue-500 hover:underline"
                    >
                        Quên mật khẩu?
                    </a>
                @endif

                <p class="text-center text-sm text-gray-600">
                    Bạn chưa có tài khoản?
                    <a href="{{ route('register') }}" class="text-blue-500 hover:underline">
                        Đăng ký
                    </a>
                </p>
            </div>
        </form>
    </div>
</x-guest-layout>
