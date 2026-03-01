<x-guest-layout>

    <div class="max-w-md mx-auto bg-white p-8 rounded-2xl shadow-lg border border-blue-100">
        <h2 class="text-2xl font-bold text-center text-blue-500 mb-6">
            Đặt lại mật khẩu
        </h2>

        <form method="POST" action="{{ route('password.store') }}">
            @csrf

            {{-- Token --}}
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            {{-- Email --}}
            <div>
                <x-input-label for="email" value="Email" class="text-blue-600" />
                <x-text-input
                    id="email"
                    class="block mt-1 w-full rounded-lg border-blue-200 focus:border-blue-400 focus:ring focus:ring-blue-100"
                    type="email"
                    name="email"
                    :value="old('email', $request->email)"
                    required
                    autofocus
                />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            {{-- Mật khẩu mới --}}
            <div class="mt-4">
                <x-input-label for="password" value="Mật khẩu mới" class="text-blue-600" />

                <div class="relative mt-1">
                    <input
                        id="password"
                        class="w-full rounded-lg border-blue-200 focus:border-blue-400 focus:ring focus:ring-blue-100 pr-10"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                    />

                    <i class="bi bi-eye absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer text-gray-500"
                       onclick="togglePassword('password', this)"></i>
                </div>

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            {{-- Xác nhận mật khẩu --}}
            <div class="mt-4">
                <x-input-label for="password_confirmation"
                               value="Xác nhận mật khẩu"
                               class="text-blue-600" />

                <div class="relative mt-1">
                    <input
                        id="password_confirmation"
                        class="w-full rounded-lg border-blue-200 focus:border-blue-400 focus:ring focus:ring-blue-100 pr-10"
                        type="password"
                        name="password_confirmation"
                        required
                    />

                    <i class="bi bi-eye absolute right-3 top-1/2 -translate-y-1/2 cursor-pointer text-gray-500"
                       onclick="togglePassword('password_confirmation', this)"></i>
                </div>

                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            {{-- Button --}}
            <div class="mt-6">
                <x-primary-button class="w-full justify-center bg-blue-400 hover:bg-blue-500">
                    Đặt lại mật khẩu
                </x-primary-button>
            </div>
        </form>
    </div>

    {{-- ===== HIỂN THỊ TOAST (dùng components.toast) ===== --}}
    @if ($errors->any())
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                showToast(@json($errors->first()), 'error');
            });
        </script>
    @endif

    {{-- Script toggle password --}}
    <script>
        function togglePassword(fieldId, icon) {
            const input = document.getElementById(fieldId);

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