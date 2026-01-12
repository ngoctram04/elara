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

            <div class="row">
                {{-- AVATAR --}}
                <div class="col-md-4 text-center mb-4">
                    <label class="fw-semibold mb-2 d-block">Ảnh đại diện</label>

                    @if ($admin->avatar)
                        <img
                            src="{{ asset('storage/' . $admin->avatar) }}"
                            class="rounded-circle img-thumbnail mb-3"
                            width="150"
                            height="150"
                            alt="Avatar"
                        >
                    @else
                        <div
                            class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3"
                            style="width:150px;height:150px;font-size:48px"
                        >
                            {{ strtoupper(substr($admin->name, 0, 1)) }}
                        </div>
                    @endif

                    <input type="file"
                           name="avatar"
                           class="form-control"
                           accept="image/*">
                </div>

                {{-- THÔNG TIN --}}
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Họ tên</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               value="{{ old('name', $admin->name) }}"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email"
                               name="email"
                               class="form-control"
                               value="{{ old('email', $admin->email) }}"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mật khẩu mới (nếu đổi)</label>
                        <input type="password"
                               name="password"
                               class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nhập lại mật khẩu</label>
                        <input type="password"
                               name="password_confirmation"
                               class="form-control">
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-primary">
                    <i class="bi bi-save"></i> Lưu thay đổi
                </button>

                <a href="{{ route('admin.profile.show') }}"
                   class="btn btn-secondary ms-2">
                    Quay lại
                </a>
            </div>
        </form>

    </div>
</div>
@endsection
