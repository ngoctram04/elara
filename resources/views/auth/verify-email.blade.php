<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Cảm ơn bạn đã đăng ký!  
        Trước khi bắt đầu, vui lòng xác thực địa chỉ email bằng cách nhấp vào liên kết chúng tôi vừa gửi đến email của bạn.  
        Nếu bạn chưa nhận được email, chúng tôi sẽ gửi lại cho bạn.
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            Một liên kết xác thực mới đã được gửi đến địa chỉ email bạn đã đăng ký.
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <x-primary-button>
                Gửi lại email xác thực
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button
                type="submit"
                class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md
                       focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                Đăng xuất
            </button>
        </form>
    </div>
</x-guest-layout>
