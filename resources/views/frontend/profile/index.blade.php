@extends('layouts.frontend')

@section('title', 'Tài khoản của bạn')

@push('styles')
<style>
    .account-page {
        background: linear-gradient(180deg, #f8fbff 0%, #fdfefe 100%);
    }

    .account-card {
        border: 0;
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 14px 40px rgba(31, 71, 136, 0.08);
        overflow: hidden;
    }

    .account-card .card-body {
        padding: 28px;
    }

    .account-section-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1d3557;
        margin-bottom: 20px;
    }

    .account-section-subtitle {
        font-size: 0.93rem;
        color: #7b8ba1;
        margin-top: -10px;
        margin-bottom: 22px;
    }

    .profile-left-box {
        background: linear-gradient(180deg, #eef7ff 0%, #f8fbff 100%);
        border: 1px solid #e4eefb;
        border-radius: 22px;
        padding: 24px 18px;
        text-align: center;
        height: 100%;
    }

    .avatar-wrap {
        position: relative;
        width: 150px;
        height: 150px;
        margin: 0 auto 14px;
    }

    .avatar-preview {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: 0 10px 26px rgba(82, 125, 191, 0.18);
        background: #fff;
    }

    .avatar-upload-btn {
        position: absolute;
        right: 6px;
        bottom: 6px;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        border: none;
        background: linear-gradient(135deg, #7db8ff, #4e8df6);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 20px rgba(78, 141, 246, 0.28);
        cursor: pointer;
        transition: 0.25s ease;
    }

    .avatar-upload-btn:hover {
        transform: translateY(-2px) scale(1.04);
    }

    .profile-name {
        font-size: 1.05rem;
        font-weight: 700;
        color: #22324d;
        margin-bottom: 6px;
    }

    .profile-email {
        font-size: 0.92rem;
        color: #7b8ba1;
        word-break: break-word;
    }

    .profile-note {
        margin-top: 10px;
        font-size: 0.83rem;
        color: #92a0b3;
    }

    .form-label {
        font-weight: 600;
        color: #355070;
        margin-bottom: 8px;
        font-size: 0.93rem;
    }

    .form-control,
    .form-select {
        border-radius: 14px !important;
        min-height: 48px;
        border: 1px solid #dbe7f5;
        box-shadow: none !important;
        background: #fff;
        color: #29415f;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.18rem rgba(13, 110, 253, 0.08) !important;
    }

    .form-control[disabled] {
        background: #f6f9fc;
        color: #7f8da1;
    }

    .password-field {
        position: relative;
    }

    .password-field .form-control {
        padding-right: 52px;
    }

    .password-field .form-control.is-invalid,
    .password-field .form-control.is-valid {
        background-image: none !important;
    }

    .password-field .toggle-password {
        position: absolute;
        top: 50%;
        right: 14px;
        transform: translateY(-50%);
        cursor: pointer;
        color: #8fa3bf;
        font-size: 18px;
        line-height: 1;
        transition: 0.2s ease;
        z-index: 10;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .password-field .toggle-password:hover {
        color: #4b8bf3;
    }

    .btn-account-primary {
        min-width: 170px;
        border: none;
        border-radius: 999px;
        padding: 12px 24px;
        font-weight: 600;
        background: linear-gradient(135deg, #73b3ff, #4b8bf3);
        color: #fff;
        box-shadow: 0 10px 20px rgba(75, 139, 243, 0.22);
        transition: 0.25s ease;
    }

    .btn-account-primary:hover {
        transform: translateY(-2px);
        color: #fff;
    }

    .btn-account-outline {
        min-width: 190px;
        border-radius: 999px;
        padding: 11px 24px;
        font-weight: 600;
        border: 1.5px solid #8ab8ff;
        color: #3973d6;
        background: #fff;
        transition: 0.25s ease;
    }

    .btn-account-outline:hover {
        background: #f3f8ff;
        color: #2b63c8;
        border-color: #6ea6fa;
    }

    .security-box {
        background: linear-gradient(180deg, #fcfdff 0%, #f7fbff 100%);
        border: 1px solid #e7eef8;
        border-radius: 20px;
        padding: 18px;
    }

    .alert {
        border: 0;
        border-radius: 16px;
    }

    @media (max-width: 991.98px) {
        .account-card .card-body {
            padding: 20px;
        }

        .profile-left-box {
            margin-bottom: 18px;
        }
    }

    @media (max-width: 767.98px) {
        .account-section-title {
            font-size: 1.05rem;
        }

        .avatar-wrap,
        .avatar-preview {
            width: 130px;
            height: 130px;
        }

        .btn-account-primary,
        .btn-account-outline {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="account-page py-4">
    <div class="container">
        <div class="row g-4">
            @include('frontend.partials.account-sidebar')

            <div class="col-lg-9">

                {{-- PROFILE --}}
                <div class="card account-card mb-4">
                    <div class="card-body">
                        <h5 class="account-section-title">Hồ sơ của tôi</h5>
                        <div class="account-section-subtitle">
                            Quản lý thông tin cá nhân để bảo mật tài khoản và nâng cao trải nghiệm mua sắm
                        </div>

                        <div class="row g-4 align-items-stretch">
                            <div class="col-md-4">
                                <div class="profile-left-box">
                                    <div class="avatar-wrap">
                                        <img
                                            id="avatarPreview"
                                            src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('images/avatar-default.png') }}"
                                            alt="Avatar"
                                            class="avatar-preview"
                                        >

                                        <form method="POST"
                                              action="{{ route('profile.avatar') }}"
                                              enctype="multipart/form-data"
                                              id="avatarForm">
                                            @csrf

                                            <input type="file"
                                                   name="avatar"
                                                   id="avatarInput"
                                                   accept="image/*"
                                                   hidden>

                                            <label for="avatarInput" class="avatar-upload-btn" title="Đổi ảnh đại diện">
                                                <i class="bi bi-camera-fill"></i>
                                            </label>
                                        </form>
                                    </div>

                                    <div class="profile-name">{{ $user->name }}</div>
                                    <div class="profile-email">{{ $user->email }}</div>
                                    <div class="profile-note">
                                        Nhấn biểu tượng máy ảnh để thay đổi ảnh đại diện
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <form method="POST" action="{{ route('profile.update') }}">
                                    @csrf
                                    @method('PATCH')

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Họ và tên</label>
                                            <input type="text"
                                                   name="name"
                                                   class="form-control @error('name') is-invalid @enderror"
                                                   value="{{ old('name', $user->name) }}">
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email"
                                                   class="form-control"
                                                   value="{{ $user->email }}"
                                                   disabled>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Số điện thoại</label>
                                            <input type="text"
                                                   name="phone"
                                                   class="form-control @error('phone') is-invalid @enderror"
                                                   value="{{ old('phone', $user->phone) }}">
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Ngày sinh</label>
                                            <input type="date"
                                                   name="date_of_birth"
                                                   class="form-control @error('date_of_birth') is-invalid @enderror"
                                                   value="{{ old('date_of_birth', optional($user->date_of_birth)->format('Y-m-d')) }}">
                                            @error('date_of_birth')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Giới tính</label>
                                            <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                                                <option value="">-- Chọn --</option>
                                                <option value="male" {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>Nam</option>
                                                <option value="female" {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>Nữ</option>
                                                <option value="other" {{ old('gender', $user->gender) == 'other' ? 'selected' : '' }}>Khác</option>
                                            </select>
                                            @error('gender')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-account-primary mt-2">
                                        Lưu thay đổi
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECURITY --}}
                <div class="card account-card">
                    <div class="card-body">
                        <h5 class="account-section-title">Đổi mật khẩu</h5>
                        <div class="account-section-subtitle">
                            Đặt mật khẩu mạnh để tăng mức độ an toàn cho tài khoản của bạn
                        </div>

                        <div class="security-box">
                            <form method="POST" action="{{ route('profile.password') }}">
                                @csrf

                                <div class="row">
                                    @foreach([
                                        'current_password' => 'Mật khẩu hiện tại',
                                        'password' => 'Mật khẩu mới',
                                        'password_confirmation' => 'Xác nhận mật khẩu mới'
                                    ] as $field => $label)
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">{{ $label }}</label>

                                            <div class="password-field">
                                                <input type="password"
                                                       id="{{ $field }}"
                                                       name="{{ $field }}"
                                                       class="form-control"
                                                       value="{{ old($field) }}">

                                                <i class="bi bi-eye toggle-password"
                                                   data-target="{{ $field }}"></i>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <button type="submit" class="btn btn-account-outline mt-2">
                                    Cập nhật mật khẩu
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('avatarInput')?.addEventListener('change', function () {
        if (this.files.length > 0) {
            const file = this.files[0];
            const reader = new FileReader();

            reader.onload = function (e) {
                document.getElementById('avatarPreview').src = e.target.result;
            };

            reader.readAsDataURL(file);
            document.getElementById('avatarForm').submit();
        }
    });

    document.querySelectorAll('.toggle-password').forEach(el => {
        el.addEventListener('click', function () {
            const input = document.getElementById(this.dataset.target);

            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                this.classList.remove('bi-eye');
                this.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                this.classList.remove('bi-eye-slash');
                this.classList.add('bi-eye');
            }
        });
    });
</script>
@endsection