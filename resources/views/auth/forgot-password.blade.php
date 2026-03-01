<x-guest-layout>

    <div class="max-w-md mx-auto bg-white p-8 rounded-2xl shadow-lg border border-blue-100">

        <h2 class="text-2xl font-bold text-center text-blue-500 mb-4">
            Quên mật khẩu
        </h2>

        <!-- Mô tả -->
        <div class="mb-4 text-sm text-gray-600">
            Quên mật khẩu? Không sao cả.  
            Nhập địa chỉ email của bạn, chúng tôi sẽ gửi liên kết để bạn đặt lại mật khẩu.
        </div>

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

    {{-- ===== TOAST HIỂN THỊ LỖI VALIDATION ===== --}}
    @if ($errors->any())
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                showToast(@json($errors->first()), 'error');
            });
        </script>
    @endif

    {{-- Thành công hoặc lỗi từ controller --}}
    @if(session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                showToast(@json(session('success')), 'success');
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                showToast(@json(session('error')), 'error');
            });
        </script>
    @endif

</x-guest-layout>