<x-guest-layout>

<div class="max-w-4xl mx-auto bg-white p-10 rounded-2xl shadow-xl border border-blue-100">

    <h2 class="text-2xl font-bold text-center text-blue-500 mb-1">
        Tạo tài khoản ELARA
    </h2>
    <p class="text-sm text-center text-gray-500 mb-8">
        Điền thông tin bên dưới để bắt đầu mua sắm
    </p>

<form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Họ tên --}}
    <div>
        <x-input-label value="Họ và tên" class="text-blue-600"/>
        <x-text-input name="name"
            value="{{ old('name') }}"
            class="mt-1 w-full rounded-lg border-blue-200 focus:ring-blue-100"/>

        @error('name')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- SĐT --}}
    <div>
        <x-input-label value="Số điện thoại" class="text-blue-600"/>
        <x-text-input name="phone"
            value="{{ old('phone') }}"
            class="mt-1 w-full rounded-lg border-blue-200 focus:ring-blue-100"/>
    </div>

    {{-- Ngày sinh --}}
    <div>
        <x-input-label value="Ngày sinh" class="text-blue-600"/>
        <input type="date" name="date_of_birth"
            value="{{ old('date_of_birth') }}"
            class="mt-1 w-full rounded-lg border-blue-200 focus:ring-blue-100">
    </div>

    {{-- Giới tính --}}
    <div>
        <x-input-label value="Giới tính" class="text-blue-600"/>
        <select name="gender"
            class="mt-1 w-full rounded-lg border-blue-200 focus:ring-blue-100">
            <option value="">-- Chọn --</option>
            <option value="male" {{ old('gender')=='male'?'selected':'' }}>Nam</option>
            <option value="female" {{ old('gender')=='female'?'selected':'' }}>Nữ</option>
            <option value="other" {{ old('gender')=='other'?'selected':'' }}>Khác</option>
        </select>
    </div>

    {{-- Email --}}
    <div class="md:col-span-2">
        <x-input-label value="Email" class="text-blue-600"/>

        <x-text-input type="email" name="email"
            value="{{ old('email') }}"
            class="mt-1 w-full rounded-lg border-blue-200 focus:ring-blue-100"/>

        @error('email')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Avatar --}}
    <div class="md:col-span-2">
        <x-input-label value="Ảnh đại diện" class="text-blue-600"/>

        <div class="flex items-center gap-4 mt-1">
            <img id="avatarPreview"
                 src="{{ asset('images/avatar-default.png') }}"
                 class="w-20 h-20 rounded-full object-cover border">

            <input type="file"
                   name="avatar"
                   accept="image/*"
                   onchange="previewAvatar(event)"
                   class="text-sm text-gray-600">
        </div>
    </div>

    {{-- Password --}}
    <div class="relative">
        <x-input-label value="Mật khẩu" class="text-blue-600"/>

        <input id="password"
            type="password"
            name="password"
            class="mt-1 w-full pr-10 rounded-lg border-blue-200 focus:ring-blue-100"
            required>

        <button type="button"
            onclick="togglePassword('password', this)"
            class="absolute right-3 top-9 text-gray-500 hover:text-gray-700">
            <i class="bi bi-eye"></i>
        </button>

        @error('password')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Confirm --}}
    <div class="relative">
        <x-input-label value="Xác nhận mật khẩu" class="text-blue-600"/>

        <input id="password_confirmation"
            type="password"
            name="password_confirmation"
            class="mt-1 w-full pr-10 rounded-lg border-blue-200 focus:ring-blue-100"
            required>

        <button type="button"
            onclick="togglePassword('password_confirmation', this)"
            class="absolute right-3 top-9 text-gray-500 hover:text-gray-700">
            <i class="bi bi-eye"></i>
        </button>

        @error('password_confirmation')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

</div>

<div class="mt-8">
    <x-primary-button class="w-full justify-center bg-blue-400 hover:bg-blue-500 py-3">
        Đăng ký tài khoản
    </x-primary-button>
</div>

<p class="text-center text-sm text-gray-600 mt-4">
    Đã có tài khoản?
    <a href="{{ route('login') }}" class="text-blue-500 hover:underline">
        Đăng nhập
    </a>
</p>

</form>
</div>

{{-- Toast chỉ cho lỗi KHÔNG thuộc các field chính --}}
@if ($errors->any()
    && !$errors->has('name')
    && !$errors->has('email')
    && !$errors->has('password')
    && !$errors->has('password_confirmation'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    showToast(@json($errors->first()), 'error');
});
</script>
@endif

<script>
function previewAvatar(event) {
    const reader = new FileReader();
    reader.onload = function(){
        document.getElementById('avatarPreview').src = reader.result;
    }
    if (event.target.files[0]) {
        reader.readAsDataURL(event.target.files[0]);
    }
}

function togglePassword(id, btn) {
    const input = document.getElementById(id);
    const icon = btn.querySelector('i');

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