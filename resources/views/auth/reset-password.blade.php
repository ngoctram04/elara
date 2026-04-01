<x-guest-layout>
    @push('styles')
        <style>
            .auth-page {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 32px 16px;
                background:
                    radial-gradient(circle at top left, rgba(170, 210, 232, 0.22), transparent 28%),
                    radial-gradient(circle at bottom right, rgba(194, 225, 242, 0.26), transparent 30%),
                    linear-gradient(180deg, #f5f8fb 0%, #eef5fa 100%);
                font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }

            .auth-card {
                width: 100%;
                max-width: 460px;
                background: rgba(255, 255, 255, 0.97);
                border: 1px solid rgba(182, 214, 231, 0.95);
                border-radius: 28px;
                padding: 32px 28px 26px;
                box-shadow: 0 16px 40px rgba(84, 125, 154, 0.12);
                backdrop-filter: blur(10px);
            }

            .auth-logo {
                width: 64px;
                height: 64px;
                margin: 0 auto 14px;
                border-radius: 18px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #d8ebf6 0%, #9fcde4 100%);
                color: #3d6e8e;
                box-shadow: 0 10px 24px rgba(121, 175, 206, 0.22);
                font-size: 28px;
            }

            .auth-title {
                font-size: 28px;
                font-weight: 800;
                color: #34546d;
                text-align: center;
                margin-bottom: 6px;
                line-height: 1.2;
                letter-spacing: -0.3px;
            }

            .auth-subtitle {
                text-align: center;
                font-size: 14px;
                line-height: 1.6;
                color: #7b8ea1;
                margin-bottom: 24px;
            }

            .auth-form-group {
                margin-bottom: 18px;
            }

            .auth-label {
                display: block;
                margin-bottom: 8px;
                font-size: 14px;
                font-weight: 700;
                color: #537792;
            }

            .auth-input {
                width: 100%;
                height: 50px;
                border-radius: 16px;
                border: 1px solid #d4e5ef;
                background: #f8fbfd;
                padding: 0 15px;
                font-size: 14px;
                color: #31475b;
                transition: all 0.25s ease;
                outline: none;
            }

            .auth-input:focus {
                border-color: #8fc1dc;
                background: #ffffff;
                box-shadow: 0 0 0 4px rgba(143, 193, 220, 0.18);
            }

            .auth-input::placeholder {
                color: #9aa9b7;
            }

            .auth-password-group {
                position: relative;
            }

            .auth-password-group .auth-input {
                padding-right: 48px;
            }

            .auth-toggle-password {
                position: absolute;
                top: 50%;
                right: 14px;
                transform: translateY(-50%);
                border: none;
                background: transparent;
                color: #7b8ea1;
                font-size: 18px;
                padding: 0;
                cursor: pointer;
                transition: color 0.2s ease;
            }

            .auth-toggle-password:hover {
                color: #4f7593;
            }

            .auth-btn {
                width: 100%;
                height: 52px;
                border: none;
                border-radius: 16px;
                background: linear-gradient(135deg, #93c9e4 0%, #73b7db 100%);
                color: #ffffff;
                font-size: 15px;
                font-weight: 700;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                box-shadow: 0 12px 26px rgba(115, 183, 219, 0.26);
                transition: all 0.25s ease;
                cursor: pointer;
            }

            .auth-btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 16px 30px rgba(115, 183, 219, 0.32);
                filter: brightness(1.02);
            }

            .auth-footer {
                text-align: center;
                font-size: 14px;
                color: #7b8ea1;
                margin-top: 18px;
                margin-bottom: 0;
                line-height: 1.6;
            }

            .auth-link {
                font-size: 14px;
                font-weight: 600;
                color: #6daed2;
                text-decoration: none;
                transition: color 0.2s ease;
            }

            .auth-link:hover {
                color: #4e96be;
                text-decoration: underline;
            }

            .auth-error {
                margin-top: 8px;
                font-size: 13px;
            }

            @media (max-width: 576px) {
                .auth-card {
                    padding: 26px 18px 22px;
                    border-radius: 22px;
                }

                .auth-title {
                    font-size: 24px;
                }
            }
        </style>
    @endpush

    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-logo">
                <i class="bi bi-shield-lock"></i>
            </div>

            <h2 class="auth-title">Đặt lại mật khẩu</h2>
            <p class="auth-subtitle">
                Tạo mật khẩu mới để tiếp tục đăng nhập và sử dụng tài khoản của bạn
            </p>

            <form method="POST" action="{{ route('password.store') }}">
                @csrf

                {{-- Token --}}
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                {{-- Email --}}
                <div class="auth-form-group">
                    <label for="email" class="auth-label">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email', $request->email) }}"
                        required
                        autofocus
                        placeholder="Nhập email của bạn"
                        class="auth-input"
                    >
                    <x-input-error :messages="$errors->get('email')" class="auth-error" />
                </div>

                {{-- Mật khẩu mới --}}
                <div class="auth-form-group">
                    <label for="password" class="auth-label">Mật khẩu mới</label>

                    <div class="auth-password-group">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            placeholder="Nhập mật khẩu mới"
                            class="auth-input"
                        >

                        <button
                            type="button"
                            onclick="togglePassword('password', this)"
                            class="auth-toggle-password"
                            aria-label="Ẩn hiện mật khẩu"
                        >
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>

                    <x-input-error :messages="$errors->get('password')" class="auth-error" />
                </div>

                {{-- Xác nhận mật khẩu --}}
                <div class="auth-form-group">
                    <label for="password_confirmation" class="auth-label">Xác nhận mật khẩu</label>

                    <div class="auth-password-group">
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Nhập lại mật khẩu mới"
                            class="auth-input"
                        >

                        <button
                            type="button"
                            onclick="togglePassword('password_confirmation', this)"
                            class="auth-toggle-password"
                            aria-label="Ẩn hiện mật khẩu"
                        >
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>

                    <x-input-error :messages="$errors->get('password_confirmation')" class="auth-error" />
                </div>

                {{-- Button --}}
                <div class="mt-2">
                    <button type="submit" class="auth-btn">
                        <i class="bi bi-arrow-repeat"></i>
                        Đặt lại mật khẩu
                    </button>
                </div>

                <p class="auth-footer">
                    Quay lại trang
                    <a href="{{ route('login') }}" class="auth-link">đăng nhập</a>
                </p>
            </form>
        </div>
    </div>

    @if ($errors->any())
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    if (typeof window.showToast === 'function') {
                        window.showToast(@json($errors->first()), 'error');
                    }
                });
            </script>
        @endpush
    @endif

    @push('scripts')
        <script>
            function togglePassword(fieldId, button) {
                const input = document.getElementById(fieldId);
                const icon = button.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            }
        </script>
    @endpush
</x-guest-layout>