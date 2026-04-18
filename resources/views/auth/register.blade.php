<x-guest-layout>
    @push('styles')
        <style>
            .register-page {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 36px 16px;
                background:
                    radial-gradient(circle at top left, rgba(170, 210, 232, 0.20), transparent 28%),
                    radial-gradient(circle at bottom right, rgba(194, 225, 242, 0.24), transparent 30%),
                    linear-gradient(180deg, #f5f8fb 0%, #eef5fa 100%);
                font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }

            .register-card {
                width: 100%;
                max-width: 820px;
                background: rgba(255, 255, 255, 0.97);
                border: 1px solid rgba(182, 214, 231, 0.95);
                border-radius: 28px;
                padding: 30px 28px 26px;
                box-shadow: 0 16px 40px rgba(84, 125, 154, 0.12);
                backdrop-filter: blur(10px);
            }

            .register-header {
                text-align: center;
                margin-bottom: 24px;
            }

            .register-logo {
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

            .register-title {
                font-size: 28px;
                font-weight: 800;
                color: #34546d;
                margin-bottom: 6px;
                letter-spacing: -0.3px;
            }

            .register-subtitle {
                font-size: 14px;
                line-height: 1.6;
                color: #7b8ea1;
                margin-bottom: 0;
            }

            .register-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 18px 20px;
            }

            .full-col {
                grid-column: 1 / -1;
            }

            .field-group {
                position: relative;
            }

            .field-label {
                display: block;
                margin-bottom: 8px;
                font-size: 14px;
                font-weight: 700;
                color: #537792;
            }

            .field-input,
            .field-select,
            .field-date {
                width: 100%;
                height: 50px;
                border-radius: 16px;
                border: 1px solid #d4e5ef;
                background: #f8fbfd;
                padding: 0 15px;
                font-size: 14px;
                color: #31475b;
                outline: none;
                transition: all 0.25s ease;
            }

            .field-input:focus,
            .field-select:focus,
            .field-date:focus {
                border-color: #8fc1dc;
                background: #ffffff;
                box-shadow: 0 0 0 4px rgba(143, 193, 220, 0.18);
            }

            .field-input::placeholder {
                color: #9aa9b7;
            }

            .password-wrap {
                position: relative;
            }

            .password-wrap .field-input {
                padding-right: 46px;
            }

            .toggle-password-btn {
                position: absolute;
                right: 14px;
                top: 50%;
                transform: translateY(-50%);
                border: none;
                background: transparent;
                color: #7b8ea1;
                font-size: 18px;
                cursor: pointer;
                padding: 0;
                transition: color 0.2s ease;
            }

            .toggle-password-btn:hover {
                color: #4f7593;
            }

            .avatar-box {
                display: flex;
                align-items: center;
                gap: 18px;
                padding: 16px;
                border: 1px dashed #c8deeb;
                border-radius: 20px;
                background: linear-gradient(180deg, #f9fcfe 0%, #f3f9fc 100%);
            }

            .avatar-preview-wrap {
                flex-shrink: 0;
                position: relative;
            }

            .avatar-preview {
                width: 84px;
                height: 84px;
                border-radius: 50%;
                object-fit: cover;
                border: 3px solid #ffffff;
                box-shadow: 0 8px 20px rgba(112, 159, 187, 0.18);
                background: #eaf4f9;
            }

            .avatar-badge {
                position: absolute;
                right: -2px;
                bottom: -2px;
                width: 28px;
                height: 28px;
                border-radius: 50%;
                background: linear-gradient(135deg, #93c9e4 0%, #73b7db 100%);
                color: #fff;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 3px solid #fff;
                font-size: 13px;
                box-shadow: 0 6px 12px rgba(115, 183, 219, 0.22);
            }

            .avatar-content {
                flex: 1;
                min-width: 0;
            }

            .avatar-title {
                font-size: 14px;
                font-weight: 700;
                color: #48657d;
                margin-bottom: 4px;
            }

            .avatar-note {
                font-size: 13px;
                line-height: 1.5;
                color: #8b9bab;
                margin-bottom: 10px;
            }

            .avatar-upload-label {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 10px 16px;
                border-radius: 12px;
                background: #edf7fc;
                color: #5c8cac;
                font-size: 13px;
                font-weight: 700;
                cursor: pointer;
                transition: all 0.2s ease;
                border: 1px solid #d4e8f2;
            }

            .avatar-upload-label:hover {
                background: #e5f2f9;
                color: #476f8a;
            }

            .avatar-upload-input {
                display: none;
            }

            .field-error {
                margin-top: 8px;
                font-size: 13px;
                color: #ef4444;
            }

            .register-submit {
                margin-top: 24px;
            }

            .register-btn {
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

            .register-btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 16px 30px rgba(115, 183, 219, 0.32);
                filter: brightness(1.02);
            }

            .register-footer {
                text-align: center;
                font-size: 14px;
                color: #7b8ea1;
                margin-top: 16px;
                margin-bottom: 0;
            }

            .register-link {
                font-weight: 600;
                color: #6daed2;
                text-decoration: none;
                transition: color 0.2s ease;
            }

            .register-link:hover {
                color: #4e96be;
                text-decoration: underline;
            }

            @media (max-width: 768px) {
                .register-card {
                    max-width: 560px;
                    padding: 24px 18px 22px;
                    border-radius: 22px;
                }

                .register-grid {
                    grid-template-columns: 1fr;
                    gap: 16px;
                }

                .full-col {
                    grid-column: auto;
                }

                .register-title {
                    font-size: 24px;
                }

                .avatar-box {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .avatar-content {
                    width: 100%;
                }
            }
        </style>
    @endpush

    <div class="register-page">
        <div class="register-card">
            <div class="register-header">
                <div class="register-logo">
                    <i class="bi bi-person-plus"></i>
                </div>
                <h2 class="register-title">Tạo tài khoản ELARA</h2>
                <p class="register-subtitle">
                    Điền thông tin bên dưới để bắt đầu mua sắm
                </p>
            </div>

            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                @csrf

                <div class="register-grid">
                    <div class="field-group">
                        <label class="field-label" for="name">Họ và tên</label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            class="field-input"
                            placeholder="Nhập họ và tên"
                            required
                        >
                        @error('name')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="phone">Số điện thoại</label>
                        <input
                            id="phone"
                            type="text"
                            name="phone"
                            value="{{ old('phone') }}"
                            class="field-input"
                            placeholder="Nhập số điện thoại"
                            required
                            maxlength="10"
                            inputmode="numeric"
                            pattern="0[0-9]{9}"
                            title="Số điện thoại phải gồm 10 số và bắt đầu bằng 0"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                        >
                        @error('phone')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="date_of_birth">Ngày sinh</label>
                        <input
                            id="date_of_birth"
                            type="date"
                            name="date_of_birth"
                            value="{{ old('date_of_birth') }}"
                            class="field-date"
                            required
                            max="{{ now()->subYears(13)->format('Y-m-d') }}"
                        >
                        @error('date_of_birth')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="gender">Giới tính</label>
                        <select id="gender" name="gender" class="field-select">
                            <option value="">-- Chọn --</option>
                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Nam</option>
                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Nữ</option>
                            <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Khác</option>
                        </select>
                        @error('gender')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field-group full-col">
                        <label class="field-label" for="email">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="field-input"
                            placeholder="Nhập email của bạn"
                            required
                        >
                        @error('email')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field-group full-col">
                        <label class="field-label">Ảnh đại diện</label>

                        <div class="avatar-box">
                            <div class="avatar-preview-wrap">
                                <img
                                    id="avatarPreview"
                                    src="{{ asset('images/avatar-default.png') }}"
                                    alt="Avatar preview"
                                    class="avatar-preview"
                                >
                                <span class="avatar-badge">
                                    <i class="bi bi-camera-fill"></i>
                                </span>
                            </div>

                            <div class="avatar-content">
                                <div class="avatar-title">Chọn ảnh đại diện</div>
                                <div class="avatar-note">
                                    Hỗ trợ ảnh JPG, PNG hoặc WEBP. Nên chọn ảnh rõ mặt, nền sáng để hiển thị đẹp hơn.
                                </div>

                                <label for="avatar" class="avatar-upload-label">
                                    <i class="bi bi-upload"></i>
                                    Tải ảnh lên
                                </label>
                                <input
                                    id="avatar"
                                    type="file"
                                    name="avatar"
                                    accept="image/*"
                                    onchange="previewAvatar(event)"
                                    class="avatar-upload-input"
                                >
                                @error('avatar')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="password">Mật khẩu</label>
                        <div class="password-wrap">
                            <input
                                id="password"
                                type="password"
                                name="password"
                                class="field-input"
                                placeholder="Nhập mật khẩu"
                                required
                            >
                            <button
                                type="button"
                                onclick="togglePassword('password', this)"
                                class="toggle-password-btn"
                                aria-label="Ẩn hiện mật khẩu"
                            >
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field-group">
                        <label class="field-label" for="password_confirmation">Xác nhận mật khẩu</label>
                        <div class="password-wrap">
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                class="field-input"
                                placeholder="Nhập lại mật khẩu"
                                required
                            >
                            <button
                                type="button"
                                onclick="togglePassword('password_confirmation', this)"
                                class="toggle-password-btn"
                                aria-label="Ẩn hiện mật khẩu"
                            >
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="register-submit">
                    <button type="submit" class="register-btn">
                        <i class="bi bi-person-check"></i>
                        Đăng ký tài khoản
                    </button>
                </div>

                <p class="register-footer">
                    Đã có tài khoản?
                    <a href="{{ route('login') }}" class="register-link">Đăng nhập</a>
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
            function previewAvatar(event) {
                const file = event.target.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function () {
                    document.getElementById('avatarPreview').src = reader.result;
                };
                reader.readAsDataURL(file);
            }

            function togglePassword(id, btn) {
                const input = document.getElementById(id);
                const icon = btn.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('bi-eye', 'bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('bi-eye-slash', 'bi-eye');
                }
            }
        </script>
    @endpush
</x-guest-layout>