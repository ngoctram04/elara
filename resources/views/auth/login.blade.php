<x-guest-layout>

    <div class="max-w-md mx-auto bg-white p-8 rounded-2xl shadow-lg border border-blue-100">

        <h2 class="text-2xl font-bold text-center text-blue-500 mb-6">
            Đăng nhập
        </h2>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Email --}}
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

            {{-- Password --}}
            <div class="mt-4 relative">
                <x-input-label for="password" value="Mật khẩu" class="text-blue-600" />

                <input
                    id="password"
                    class="block mt-1 w-full pr-10 rounded-lg border-blue-200 focus:border-blue-400 focus:ring focus:ring-blue-100"
                    type="password"
                    name="password"
                    required
                />

                <button
                    type="button"
                    onclick="togglePassword()"
                    class="absolute right-3 top-9 text-gray-500 hover:text-gray-700"
                >
                    <i id="toggleIcon" class="bi bi-eye"></i>
                </button>

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            {{-- Remember --}}
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

            {{-- Button --}}
            <div class="mt-6 flex flex-col gap-3">
                <x-primary-button class="w-full justify-center bg-blue-400 hover:bg-blue-500">
                    Đăng nhập
                </x-primary-button>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       class="text-center text-sm text-blue-500 hover:underline">
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

    {{-- ===== HIỂN THỊ LỖI LOGIN BẰNG COMPONENT TOAST ===== --}}
    @if ($errors->any())
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                showToast(@json($errors->first()), 'error');
            });
        </script>
    @endif

    {{-- Script toggle password --}}
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');

            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace("bi-eye", "bi-eye-slash");
            } else {
                input.type = "password";
                icon.classList.replace("bi-eye-slash", "bi-eye");
            }
        }
    </script>

</x-guest-layout>