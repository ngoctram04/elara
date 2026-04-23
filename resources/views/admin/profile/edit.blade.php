@extends('layouts.admin')

@section('title', 'Chỉnh sửa thông tin')

@section('content')
<style>
    .profile-edit-page{
        font-size:14px;
        color:#334155;
    }

    .profile-edit-card{
        border-radius:16px;
        overflow:hidden;
        border:1px solid #edf2f7;
    }

    .profile-edit-title{
        font-size:18px;
        font-weight:600;
        color:#1e293b;
    }

    .profile-edit-subtext{
        font-size:13px;
        color:#64748b;
    }

    .profile-avatar-card{
        background:#f8fafc;
        border:1px solid #e9eef5;
        border-radius:14px;
        padding:24px 18px;
        height:100%;
    }

    .profile-avatar-label{
        font-size:14px;
        font-weight:600;
        color:#1e293b;
        margin-bottom:14px;
    }

    .profile-avatar-wrap{
        position:relative;
        display:inline-block;
    }

    .profile-avatar-preview{
        width:150px;
        height:150px;
        object-fit:cover;
        border-radius:50%;
        border:4px solid #fff;
        box-shadow:0 8px 24px rgba(15, 23, 42, 0.10);
        background:#fff;
    }

    .profile-avatar-upload{
        position:absolute;
        right:4px;
        bottom:4px;
        width:42px;
        height:42px;
        border-radius:50%;
        background:#1e293b;
        color:#fff;
        display:flex;
        align-items:center;
        justify-content:center;
        cursor:pointer;
        box-shadow:0 8px 18px rgba(15, 23, 42, 0.18);
        transition:all .2s ease;
        border:2px solid #fff;
    }

    .profile-avatar-upload:hover{
        background:#0f172a;
        transform:translateY(-1px);
    }

    .profile-avatar-note{
        margin-top:14px;
        font-size:12px;
        color:#64748b;
        line-height:1.6;
    }

    .profile-form-card{
        background:#fff;
        border:1px solid #e9eef5;
        border-radius:14px;
        padding:20px;
        height:100%;
    }

    .profile-section-title{
        font-size:15px;
        font-weight:600;
        color:#1e293b;
        margin-bottom:16px;
    }

    .form-label{
        font-size:13px;
        font-weight:500;
        color:#334155;
        margin-bottom:7px;
    }

    .form-control,
    .form-select{
        border-radius:10px;
        border:1px solid #dbe3ee;
        padding:10px 12px;
        font-size:14px;
        color:#1e293b;
        box-shadow:none !important;
    }

    .form-control:focus,
    .form-select:focus{
        border-color:#93c5fd;
        box-shadow:0 0 0 3px rgba(59, 130, 246, 0.10) !important;
    }

    .form-control[readonly]{
        background:#f8fafc;
        color:#64748b;
    }

    .input-group-text{
        border-radius:0 10px 10px 0;
        border:1px solid #dbe3ee;
        background:#f8fafc;
        color:#64748b;
        transition:all .2s ease;
    }

    .input-group .form-control{
        border-right:none;
        border-radius:10px 0 0 10px;
    }

    .input-group .form-control:focus + .input-group-text{
        border-color:#93c5fd;
    }

    .toggle-password{
        cursor:pointer;
    }

    .toggle-password:hover{
        background:#f1f5f9;
        color:#334155;
    }

    .profile-password-box{
        margin-top:24px;
        padding-top:20px;
        border-top:1px solid #e2e8f0;
    }

    .profile-password-subtext{
        font-size:12.5px;
        color:#64748b;
        margin-top:-8px;
        margin-bottom:16px;
    }

    .profile-action{
        margin-top:24px;
        display:flex;
        gap:10px;
        flex-wrap:wrap;
    }

    .profile-btn{
        font-size:13px;
        font-weight:500;
        border-radius:10px;
        padding:9px 16px;
    }

    .invalid-feedback{
        font-size:12.5px;
    }

    .profile-error-text{
        font-size:12.5px;
        margin-top:8px;
    }

    @media (max-width: 991px){
        .profile-avatar-card,
        .profile-form-card{
            height:auto;
        }
    }

    @media (max-width: 768px){
        .profile-edit-title{
            font-size:16px;
        }

        .profile-avatar-preview{
            width:130px;
            height:130px;
        }

        .profile-avatar-upload{
            width:38px;
            height:38px;
        }

        .profile-form-card{
            padding:16px;
        }
    }
</style>

<div class="profile-edit-page">
    <div class="card shadow-sm border-0 profile-edit-card">
        <div class="card-body p-3 p-md-4">

            <div class="mb-4">
                <h5 class="profile-edit-title mb-1">Chỉnh sửa thông tin cá nhân</h5>
                <div class="profile-edit-subtext">Cập nhật hồ sơ quản trị viên và thay đổi mật khẩu nếu cần</div>
            </div>

            <form method="POST"
                  action="{{ route('admin.profile.update') }}"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-4">

                    <div class="col-lg-4">
                        <div class="profile-avatar-card text-center">
                            <div class="profile-avatar-label">Ảnh đại diện</div>

                            <div class="profile-avatar-wrap">
                                <img id="avatarPreview"
                                     src="{{ $admin->avatar ? asset('storage/' . $admin->avatar) : asset('images/avatar-default.png') }}"
                                     class="profile-avatar-preview"
                                     alt="Avatar">

                                <label for="avatarInput" class="profile-avatar-upload">
                                    <i class="bi bi-camera-fill"></i>
                                </label>

                                <input type="file"
                                       id="avatarInput"
                                       name="avatar"
                                       class="d-none"
                                       accept="image/*">
                            </div>

                            <div class="profile-avatar-note">
                                Chọn ảnh rõ mặt, kích thước vuông sẽ hiển thị đẹp hơn.
                            </div>

                            @error('avatar')
                                <div class="text-danger profile-error-text">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="profile-form-card">
                            <div class="profile-section-title">Thông tin cơ bản</div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Họ tên</label>
                                    <input type="text"
                                           name="name"
                                           class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $admin->name) }}">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Số điện thoại</label>
                                    <input type="text"
                                           name="phone"
                                           class="form-control @error('phone') is-invalid @enderror"
                                           value="{{ old('phone', $admin->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Ngày sinh</label>
                                    <input type="date"
                                           name="date_of_birth"
                                           class="form-control @error('date_of_birth') is-invalid @enderror"
                                           value="{{ old('date_of_birth', optional($admin->date_of_birth)->format('Y-m-d')) }}">
                                    @error('date_of_birth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Giới tính</label>
                                    <select name="gender"
                                            class="form-select @error('gender') is-invalid @enderror">
                                        <option value="">-- Chọn --</option>
                                        <option value="male" {{ old('gender', $admin->gender) == 'male' ? 'selected' : '' }}>Nam</option>
                                        <option value="female" {{ old('gender', $admin->gender) == 'female' ? 'selected' : '' }}>Nữ</option>
                                        <option value="other" {{ old('gender', $admin->gender) == 'other' ? 'selected' : '' }}>Khác</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Email</label>
                                    <input type="email"
                                           class="form-control"
                                           value="{{ $admin->email }}"
                                           readonly>
                                </div>
                            </div>

                            <div class="profile-password-box">
                                <div class="profile-section-title mb-2">Đổi mật khẩu</div>
                                <div class="profile-password-subtext">Không bắt buộc. Chỉ nhập khi bạn muốn thay đổi mật khẩu hiện tại.</div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Mật khẩu hiện tại</label>
                                        <div class="input-group">
                                            <input type="password"
                                                   id="current_password"
                                                   name="current_password"
                                                   class="form-control @error('current_password') is-invalid @enderror">
                                            <span class="input-group-text toggle-password" data-target="current_password">
                                                <i class="bi bi-eye"></i>
                                            </span>
                                        </div>
                                        @error('current_password')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Mật khẩu mới</label>
                                        <div class="input-group">
                                            <input type="password"
                                                   id="password"
                                                   name="password"
                                                   class="form-control @error('password') is-invalid @enderror">
                                            <span class="input-group-text toggle-password" data-target="password">
                                                <i class="bi bi-eye"></i>
                                            </span>
                                        </div>
                                        @error('password')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Nhập lại mật khẩu</label>
                                        <div class="input-group">
                                            <input type="password"
                                                   id="password_confirmation"
                                                   name="password_confirmation"
                                                   class="form-control @error('password_confirmation') is-invalid @enderror">
                                            <span class="input-group-text toggle-password" data-target="password_confirmation">
                                                <i class="bi bi-eye"></i>
                                            </span>
                                        </div>
                                        @error('password_confirmation')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="profile-action">
                                <button class="btn btn-primary profile-btn">
                                    <i class="bi bi-save me-1"></i> Lưu thay đổi
                                </button>

                                <a href="{{ route('admin.profile.show') }}" class="btn btn-outline-secondary profile-btn">
                                    Quay lại
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('avatarInput')?.addEventListener('change', function () {
    if (this.files && this.files.length > 0) {
        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('avatarPreview').src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);
    }
});

document.querySelectorAll('.toggle-password').forEach(function (el) {
    el.addEventListener('click', function () {
        const input = document.getElementById(this.dataset.target);
        const icon = this.querySelector('i');

        if (!input) return;

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });
});
</script>
@endpush
@endsection