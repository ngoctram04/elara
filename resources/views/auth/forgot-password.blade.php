<x-guest-layout>
    <div class="max-w-md mx-auto bg-white p-8 rounded-2xl shadow-lg border border-blue-100">
        <!-- Thông báo -->
        <div class="mb-4 text-sm text-gray-600">
            Quên mật khẩu? Không sao cả.  
            Vui lòng nhập địa chỉ email của bạn, chúng tôi sẽ gửi một liên kết giúp bạn đặt lại mật khẩu mới.
        </div>

        <!-- Session Status -->
        <x-auth-session-status
            class="mb-4 text-blue-500 text-sm"
            :status="session('status')"
        />

        <form method="POST" action="{{ route('password.email') }}">
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

            <!-- Button -->
            <div class="mt-6">
                <x-primary-button class="w-full justify-center bg-blue-400 hover:bg-blue-500">
                    Gửi liên kết đặt lại mật khẩu
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
