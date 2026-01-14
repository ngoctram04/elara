<x-guest-layout>
    <div class="max-w-md mx-auto bg-white p-8 rounded-2xl shadow-lg border border-blue-100">
        <h2 class="text-2xl font-bold text-center text-blue-500 mb-6">
            Đặt lại mật khẩu
        </h2>

        <form method="POST" action="{{ route('password.store') }}">
            @csrf

            <!-- Token reset -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <!-- Email -->
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
                    autocomplete="username"
                />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Mật khẩu mới -->
            <div class="mt-4">
                <x-input-label for="password" value="Mật khẩu mới" class="text-blue-600" />
                <x-text-input
                    id="password"
                    class="block mt-1 w-full rounded-lg border-blue-200 focus:border-blue-400 focus:ring focus:ring-blue-100"
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password"
                />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Xác nhận mật khẩu -->
            <div class="mt-4">
                <x-input-label
                    for="password_confirmation"
                    value="Xác nhận mật khẩu"
                    class="text-blue-600"
                />
                <x-text-input
                    id="password_confirmation"
                    class="block mt-1 w-full rounded-lg border-blue-200 focus:border-blue-400 focus:ring focus:ring-blue-100"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <!-- Button -->
            <div class="mt-6">
                <x-primary-button class="w-full justify-center bg-blue-400 hover:bg-blue-500">
                    Đặt lại mật khẩu
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
