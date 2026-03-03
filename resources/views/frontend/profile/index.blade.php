@extends('layouts.frontend')

@section('title','Tài khoản của bạn')

@section('content')

<div class="container py-4">
<div class="row">
@include('frontend.partials.account-sidebar')

{{-- ================= CONTENT ================= --}}
<div class="col-md-9">

<div class="card shadow-sm border-0 rounded-3 mb-4">
<div class="card-body">

<h5 class="fw-bold mb-4">Hồ sơ của tôi</h5>

<div class="row">

<div class="col-md-4 text-center mb-3">
<div class="position-relative d-inline-block">
<img
id="avatarPreview"
src="{{ $user->avatar ? asset('storage/'.$user->avatar) : asset('images/avatar-default.png') }}"
class="rounded-circle border"
width="150" height="150"
style="object-fit:cover;"
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

<label for="avatarInput"
class="position-absolute bottom-0 end-0 bg-dark text-white rounded-circle d-flex align-items-center justify-content-center"
style="width:45px;height:45px;cursor:pointer;">
<i class="bi bi-camera-fill"></i>
</label>
</form>
</div>

<p class="text-muted mt-2 small">
Nhấn biểu tượng camera để đổi ảnh
</p>
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
class="form-control"
value="{{ old('name',$user->name) }}">
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Email</label>
<input type="email" class="form-control"
value="{{ $user->email }}" disabled>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Số điện thoại</label>
<input type="text"
name="phone"
class="form-control"
value="{{ old('phone',$user->phone) }}">
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Ngày sinh</label>
<input type="date"
name="date_of_birth"
class="form-control"
value="{{ old('date_of_birth', optional($user->date_of_birth)->format('Y-m-d')) }}">
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Giới tính</label>
<select name="gender" class="form-select">
<option value="">-- Chọn --</option>
<option value="male" {{ old('gender',$user->gender)=='male'?'selected':'' }}>Nam</option>
<option value="female" {{ old('gender',$user->gender)=='female'?'selected':'' }}>Nữ</option>
<option value="other" {{ old('gender',$user->gender)=='other'?'selected':'' }}>Khác</option>
</select>
</div>

</div>

<button class="btn btn-primary px-4">
Lưu thay đổi
</button>

</form>

</div>

</div>
</div>

</div>
{{-- ===== SECURITY ===== --}}
<div class="card shadow-sm border-0 rounded-3">
<div class="card-body">

<h5 class="fw-bold mb-4">Đổi mật khẩu</h5>

<form method="POST" action="{{ route('profile.password') }}">
@csrf

<div class="row">
@foreach([
    'current_password' => 'Mật khẩu hiện tại',
    'password' => 'Mật khẩu mới',
    'password_confirmation' => 'Xác nhận'
] as $field => $label)

<div class="col-md-4 mb-3">
    <label class="form-label">{{ $label }}</label>

    <div class="input-group">
        <input type="password"
               id="{{ $field }}"
               name="{{ $field }}"
               class="form-control @error($field) is-invalid @enderror">

        <span class="input-group-text toggle-password"
              data-target="{{ $field }}">
            <i class="bi bi-eye"></i>
        </span>
    </div>

    @error($field)
        <div class="invalid-feedback d-block">
            {{ $message }}
        </div>
    @enderror
</div>

@endforeach
</div>

<button class="btn btn-outline-primary px-4">
    Cập nhật mật khẩu
</button>

</form>

</div>
</div>
</div> {{-- col-md-9 --}}
</div> {{-- row --}}
</div> {{-- container --}}

<script>
document.getElementById('avatarInput')?.addEventListener('change', function () {
    if (this.files.length > 0) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('avatarPreview').src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);
        document.getElementById('avatarForm').submit();
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

@endsection