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

                {{-- AVATAR --}}
                <div class="col-lg-4 text-center">

                    <label class="fw-semibold mb-2 d-block">Ảnh đại diện</label>

                    <div class="position-relative d-inline-block">

                        {{-- PREVIEW --}}
                        @if ($admin->avatar)
                            <img
                                id="avatarPreview"
                                src="{{ asset('storage/' . $admin->avatar) }}"
                                class="rounded-circle img-thumbnail"
                                style="width:150px;height:150px;object-fit:cover;"
                            >
                        @else
                            <div
                                id="avatarPreview"
                                class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                style="width:150px;height:150px;font-size:52px"
                            >
                                {{ strtoupper(substr($admin->name, 0, 1)) }}
                            </div>
                        @endif

                        {{-- CAMERA --}}
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

                {{-- FORM --}}
                <div class="col-lg-8">

                    {{-- THÔNG TIN CƠ BẢN --}}
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Họ tên</label>
                            <input type="text"
                                   name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $admin->name) }}"
                                   required>
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

                        <div class="col-12">
                            <label class="form-label">Email</label>
                            <input type="email"
                                   class="form-control"
                                   value="{{ $admin->email }}"
                                   readonly>
                            <input type="hidden" name="email" value="{{ $admin->email }}">
                        </div>
                    </div>

                    {{-- ĐỔI MẬT KHẨU --}}
                    <div class="border-top pt-3 mt-4">
                        <h6 class="fw-semibold text-muted mb-3">
                            Đổi mật khẩu (không bắt buộc)
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Mật khẩu hiện tại</label>
                                <input type="password"
                                       name="current_password"
                                       class="form-control @error('current_password') is-invalid @enderror">
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Mật khẩu mới</label>
                                <input type="password"
                                       name="password"
                                       class="form-control @error('password') is-invalid @enderror">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nhập lại mật khẩu mới</label>
                                <input type="password"
                                       name="password_confirmation"
                                       class="form-control">
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
@endsection
