@extends('layouts.admin')

@section('title', 'Chỉnh sửa thông tin')

@section('content')

<div class="card shadow-sm">
    <div class="card-body">

    <h5 class="fw-semibold mb-4">Chỉnh sửa thông tin cá nhân</h5>

    <form method="POST"
          action="{{ route('admin.profile.update') }}"
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">

            {{-- ================= AVATAR ================= --}}
            <div class="col-lg-4 text-center">

                <label class="fw-semibold mb-2 d-block">Ảnh đại diện</label>

                <div class="position-relative d-inline-block">

                    <img id="avatarPreview"
                         src="{{ $admin->avatar ? asset('storage/'.$admin->avatar) : asset('images/avatar-default.png') }}"
                         class="rounded-circle img-thumbnail"
                         style="width:150px;height:150px;object-fit:cover;">

                    <label for="avatarInput"
                           class="position-absolute bottom-0 end-0 bg-dark text-white rounded-circle d-flex align-items-center justify-content-center shadow"
                           style="width:40px;height:40px;cursor:pointer;">
                        <i class="bi bi-camera-fill"></i>
                    </label>

                    <input type="file"
                           id="avatarInput"
                           name="avatar"
                           class="d-none"
                           accept="image/*">
                </div>

                @error('avatar')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
            </div>


            {{-- ================= FORM ================= --}}
            <div class="col-lg-8">
                <div class="row g-3">

                    {{-- Name --}}
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

                    {{-- Phone --}}
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

                    {{-- Date of birth --}}
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

                    {{-- Gender --}}
                    <div class="col-md-6">
                        <label class="form-label">Giới tính</label>
                        <select name="gender"
                                class="form-select @error('gender') is-invalid @enderror">
                            <option value="">-- Chọn --</option>
                            <option value="male" {{ old('gender',$admin->gender)=='male'?'selected':'' }}>Nam</option>
                            <option value="female" {{ old('gender',$admin->gender)=='female'?'selected':'' }}>Nữ</option>
                            <option value="other" {{ old('gender',$admin->gender)=='other'?'selected':'' }}>Khác</option>
                        </select>
                        @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email (readonly) --}}
                    <div class="col-12">
                        <label class="form-label">Email</label>
                        <input type="email"
                               class="form-control"
                               value="{{ $admin->email }}"
                               readonly>
                    </div>

                </div>


                {{-- ================= PASSWORD ================= --}}
                <div class="border-top pt-3 mt-4">
                    <h6 class="fw-semibold text-muted mb-3">
                        Đổi mật khẩu (không bắt buộc)
                    </h6>

                    <div class="row g-3">

                        {{-- Current password --}}
                        <div class="col-md-6">
                            <label class="form-label">Mật khẩu hiện tại</label>
                            <div class="input-group">
                                <input type="password"
                                       id="current_password"
                                       name="current_password"
                                       class="form-control @error('current_password') is-invalid @enderror">
                                <span class="input-group-text toggle-password"
                                      data-target="current_password"
                                      style="cursor:pointer;">
                                    <i class="bi bi-eye"></i>
                                </span>
                            </div>
                            @error('current_password')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- New password --}}
                        <div class="col-md-6">
                            <label class="form-label">Mật khẩu mới</label>
                            <div class="input-group">
                                <input type="password"
                                       id="password"
                                       name="password"
                                       class="form-control @error('password') is-invalid @enderror">
                                <span class="input-group-text toggle-password"
                                      data-target="password"
                                      style="cursor:pointer;">
                                    <i class="bi bi-eye"></i>
                                </span>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Confirm --}}
                        <div class="col-md-6">
                            <label class="form-label">Nhập lại mật khẩu</label>
                            <div class="input-group">
                                <input type="password"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       class="form-control @error('password_confirmation') is-invalid @enderror">
                                <span class="input-group-text toggle-password"
                                      data-target="password_confirmation"
                                      style="cursor:pointer;">
                                    <i class="bi bi-eye"></i>
                                </span>
                            </div>
                            @error('password_confirmation')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                    </div>
                </div>

            </div>
        </div>

        {{-- ACTION --}}
        <div class="mt-4 d-flex gap-2">
            <button class="btn btn-primary">
                <i class="bi bi-save"></i> Lưu thay đổi
            </button>
            <a href="{{ route('admin.profile.show') }}"
               class="btn btn-secondary">
                Quay lại
            </a>
        </div>

    </form>

</div>

</div>

{{-- ================= SCRIPTS ================= --}}
@push('scripts')

<script>
document.getElementById('avatarInput')?.addEventListener('change', function () {
    if (this.files.length > 0) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('avatarPreview').src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);
    }
});

document.querySelectorAll('.toggle-password').forEach(el => {
    el.addEventListener('click', function () {
        const input = document.getElementById(this.dataset.target);
        const icon = this.querySelector('i');

        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            input.type = "password";
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        }
    });
});
</script>

@endpush

@endsection
