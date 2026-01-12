<x-guest-layout>
    <div class="max-w-md mx-auto bg-white p-8 rounded-2xl shadow-lg border border-blue-100">
        <h2 class="text-2xl font-bold text-center text-blue-500 mb-2">
            Đăng ký tài khoản
        </h2>

        <p class="text-sm text-center text-gray-500 mb-6">
            Sau khi đăng ký, bạn sẽ nhận được email để xác thực tài khoản
        </p>

        <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
            @csrf

            <!-- Họ và tên -->
            <div>
                <x-input-label for="name" value="Họ và tên" class="text-blue-600" />
                <x-text-input
                    id="name"
                    class="block mt-1 w-full rounded-lg border-blue-200 focus:border-blue-400 focus:ring focus:ring-blue-100"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email -->
            <div class="mt-4">
                <x-input-label for="email" value="Email" class="text-blue-600" />
                <x-text-input
                    id="email"
                    class="block mt-1 w-full rounded-lg border-blue-200 focus:border-blue-400 focus:ring focus:ring-blue-100"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                />
                <p class="text-xs text-gray-500 mt-1">
                    Email này sẽ dùng để xác thực và nhận thông báo đơn hàng
                </p>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Số điện thoại -->
            <div class="mt-4">
                <x-input-label for="phone" value="Số điện thoại" class="text-blue-600" />
                <x-text-input
                    id="phone"
                    class="block mt-1 w-full rounded-lg border-blue-200 focus:border-blue-400 focus:ring focus:ring-blue-100"
                    type="text"
                    name="phone"
                    value="{{ old('phone') }}"
                />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>

            <!-- Avatar -->
            <div class="mt-4">
                <x-input-label for="avatar" value="Ảnh đại diện" class="text-blue-600" />
                <input
                    id="avatar"
                    type="file"
                    name="avatar"
                    accept="image/*"
                    class="block w-full mt-1 text-sm text-gray-600
                           file:mr-4 file:py-2 file:px-4
                           file:rounded-lg file:border-0
                           file:text-sm file:font-semibold
                           file:bg-blue-50 file:text-blue-600
                           hover:file:bg-blue-100"
                >
                <p class="text-xs text-gray-500 mt-1">
                    JPG, PNG — tối đa 2MB
                </p>
                <x-input-error :messages="$errors->get('avatar')" class="mt-2" />
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

            <!-- Xác nhận mật khẩu -->
            <div class="mt-4">
                <x-input-label for="password_confirmation" value="Xác nhận mật khẩu" class="text-blue-600" />
                <x-text-input
                    id="password_confirmation"
                    class="block mt-1 w-full rounded-lg border-blue-200 focus:border-blue-400 focus:ring focus:ring-blue-100"
                    type="password"
                    name="password_confirmation"
                    required
                />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <!-- Nút -->
            <div class="mt-6 flex flex-col gap-3">
                <x-primary-button class="w-full justify-center bg-blue-400 hover:bg-blue-500">
                    Đăng ký
                </x-primary-button>

                <p class="text-center text-sm text-gray-600">
                    Bạn đã có tài khoản?
                    <a href="{{ route('login') }}" class="text-blue-500 hover:underline">
                        Đăng nhập
                    </a>
                </p>
            </div>
        </form>
    </div>
</x-guest-layout>
