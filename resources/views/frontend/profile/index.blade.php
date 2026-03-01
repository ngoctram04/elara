@extends('layouts.frontend')

@section('title','Tài khoản của bạn')

@section('content')

<div class="container py-4">
<div class="row">

{{-- ================= SIDEBAR ================= --}}
<div class="col-md-3 mb-4">

<style>
.profile-card{
    border-radius:18px;
    overflow:hidden;
    transition:0.3s;
}
.profile-card:hover{
    transform:translateY(-2px);
}
.profile-avatar{
    border:4px solid #fff;
    box-shadow:0 6px 18px rgba(0,0,0,0.15);
}
.member-badge{
    border-radius:20px;
    font-size:13px;
    font-weight:600;
}
.profile-menu a{
    border-radius:10px;
    padding:8px 10px;
    transition:0.2s;
}
.profile-menu a:hover{
    background:#f5f7fa;
    color:#0d6efd !important;
    padding-left:14px;
}
.progress{
    background:#eee;
    border-radius:20px;
}
.progress-bar{
    border-radius:20px;
}
</style>
@php
$level = $user->member_level ?? 'bronze';
$points = $user->loyalty_points ?? 0;
$spent = $user->total_spent ?? 0;

/*
|--------------------------------
| MỐC HẠNG MỚI
|--------------------------------
*/
$levels = [
    'bronze' => 0,
    'silver' => 1000000,
    'gold' => 3000000,
    'diamond' => 10000000,
];

$levelNames = [
    'bronze' => 'Đồng',
    'silver' => 'Bạc',
    'gold' => 'Vàng',
    'diamond' => 'Kim cương',
];

$colors = [
    'bronze' => '#cd7f32',
    'silver' => '#6c757d',
    'gold' => '#f1c40f',
    'diamond' => '#0d6efd',
];

/*
|--------------------------------
| TÍNH LEVEL TIẾP THEO
|--------------------------------
*/
$nextLevel = null;
$nextValue = null;

foreach ($levels as $name => $value) {
    if ($spent < $value) {
        $nextLevel = $name;
        $nextValue = $value;
        break;
    }
}

/*
|--------------------------------
| TÍNH PROGRESS
|--------------------------------
*/
$progress = 100;

if ($nextLevel) {
    $currentValue = $levels[$level] ?? 0;
    $progress = (($spent - $currentValue) / ($nextValue - $currentValue)) * 100;
    $progress = max(0, min(100, $progress));
}
@endphp

<div class="card profile-card border-0 shadow">

<div style="background:linear-gradient(135deg, {{ $colors[$level] }}, #ffffff); height:90px;"></div>

<div class="card-body text-center pt-0">

<img
    src="{{ $user->avatar ? asset('storage/'.$user->avatar) : asset('images/avatar-default.png') }}"
    class="rounded-circle profile-avatar"
    width="95" height="95"
    style="object-fit:cover; margin-top:-48px;"
>

<h6 class="mt-2 mb-0 fw-bold">{{ $user->name }}</h6>
<small class="text-muted">Thành viên ELARA</small>

@if($user->email_verified_at)
<div class="mt-2">
    <span class="badge bg-success-subtle text-success border">
        <i class="bi bi-check-circle me-1"></i> Email đã xác thực
    </span>
</div>
@endif

<div class="mt-2">
    <span class="member-badge px-3 py-1"
          style="background: {{ $colors[$level] }}20; color: {{ $colors[$level] }};">
        Hạng {{ $levelNames[$level] }}
    </span>
</div>

<div class="mt-2 fw-semibold">
    {{ number_format($points) }} điểm
</div>

@if($nextLevel)
<div class="mt-2">
    <div class="progress" style="height:8px;">
        <div class="progress-bar"
             style="width: {{ $progress }}%; background: {{ $colors[$level] }};">
        </div>
    </div>
    <small class="text-muted">
        Còn {{ number_format($nextValue - $spent) }}đ để lên {{ $levelNames[$nextLevel] }}
    </small>
</div>
@else
<small class="text-success d-block mt-2">
    Hạng cao nhất
</small>
@endif

<hr>

<div class="profile-menu text-start small">

<a class="d-block text-decoration-none text-dark"
   href="{{ route('orders.history') }}">
    <i class="bi bi-box-seam me-2"></i> Đơn hàng của tôi
</a>

<a class="d-block text-decoration-none text-dark fw-semibold"
   href="{{ route('profile.index') }}">
    <i class="bi bi-person me-2"></i> Thông tin tài khoản
</a>

<a class="d-block text-decoration-none text-dark"
   href="{{ route('addresses.index') }}">
    <i class="bi bi-geo-alt me-2"></i> Sổ địa chỉ
</a>

<a class="d-block text-decoration-none text-dark"
   href="{{ route('membership') }}">
    <i class="bi bi-award me-2"></i> Hạng thành viên
</a>

<form method="POST" action="{{ route('logout') }}" class="mt-3">
@csrf
<button class="btn btn-outline-danger w-100 btn-sm">
    <i class="bi bi-box-arrow-right me-1"></i> Đăng xuất
</button>
</form>

</div>
</div>
</div>
</div>

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