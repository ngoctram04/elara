@extends('layouts.frontend')

@section('title','Tài khoản của bạn')

@section('content')

<div class="profile-wrapper">

    {{-- TITLE --}}
    <h2 class="profile-title">
        Tài khoản của bạn
    </h2>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="profile-layout">

        {{-- ================= SIDEBAR ================= --}}
        <aside class="profile-sidebar">
            <h6 class="sidebar-title">TÀI KHOẢN</h6>

            <a href="{{ route('profile.orders') }}">
                <i class="bi bi-box-seam"></i>
                Đơn hàng của tôi
            </a>

            <a class="active" href="{{ route('profile.index') }}">
                <i class="bi bi-person"></i>
                Thông tin tài khoản
            </a>

            <a href="{{ route('profile.address') }}">
                <i class="bi bi-geo-alt"></i>
                Địa chỉ giao hàng
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="bi bi-box-arrow-right"></i>
                    Đăng xuất
                </button>
            </form>
        </aside>

        {{-- ================= CONTENT ================= --}}
        <div class="profile-content">

            {{-- ===== THÔNG TIN TÀI KHOẢN ===== --}}
            <section class="profile-card">
                <h5 class="card-title">Thông tin tài khoản</h5>

                {{-- AVATAR --}}
                <div class="avatar-row">

                    <div class="avatar-box">
                        <img
                            src="{{ $user->avatar
                                ? asset('storage/'.$user->avatar)
                                : asset('images/avatar-default.png') }}"
                            class="avatar-img"
                            alt="Avatar">

                        {{-- CAMERA ICON --}}
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

                            <label for="avatarInput" class="avatar-camera">
                                <i class="bi bi-camera-fill"></i>
                            </label>
                        </form>
                    </div>

                    @error('avatar')
                        <small class="text-danger mt-2 d-block">
                            {{ $message }}
                        </small>
                    @enderror
                </div>

                {{-- FORM UPDATE INFO --}}
                <form method="POST" action="{{ route('profile.update') }}" class="profile-form">
                    @csrf
                    @method('PATCH')

                    <div class="form-group">
                        <label>Họ và tên</label>
                        <input type="text"
                               name="name"
                               value="{{ old('name', $user->name) }}">
                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email"
                               value="{{ $user->email }}"
                               disabled>
                        <small class="text-muted">
                            Email không thể thay đổi
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="text"
                               name="phone"
                               value="{{ old('phone', $user->phone) }}">
                        @error('phone')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <button type="submit" class="btn-update">
                        Lưu thay đổi
                    </button>
                </form>
            </section>

            {{-- ===== ĐỔI MẬT KHẨU ===== --}}
            <section class="profile-card mt-4">
                <h5 class="card-title">Bảo mật</h5>

                <form method="POST" action="{{ route('profile.password') }}" class="profile-form">
                    @csrf

                    <div class="form-group">
                        <label>Mật khẩu hiện tại</label>
                        <input type="password" name="current_password">
                        @error('current_password')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Mật khẩu mới</label>
                        <input type="password" name="password">
                        @error('password')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Xác nhận mật khẩu mới</label>
                        <input type="password" name="password_confirmation">
                    </div>

                    <button type="submit" class="btn-update">
                        Đổi mật khẩu
                    </button>
                </form>
            </section>

        </div>
    </div>
</div>

{{-- AUTO SUBMIT AVATAR --}}
<script>
    document.getElementById('avatarInput')?.addEventListener('change', function () {
        if (this.files.length > 0) {
            document.getElementById('avatarForm').submit();
        }
    });
</script>

@endsection
