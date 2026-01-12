@extends('layouts.admin')

@section('title', 'Thông tin cá nhân')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">

        <div class="row align-items-center mb-4">
            {{-- AVATAR --}}
            <div class="col-md-3 text-center">
                @if ($admin->avatar)
                    <img
                        src="{{ asset('storage/' . $admin->avatar) }}"
                        class="rounded-circle img-thumbnail"
                        width="150"
                        height="150"
                        alt="Avatar"
                    >
                @else
                    <div
                        class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto"
                        style="width:150px;height:150px;font-size:48px"
                    >
                        {{ strtoupper(substr($admin->name, 0, 1)) }}
                    </div>
                @endif
            </div>

            {{-- THÔNG TIN CHÍNH --}}
            <div class="col-md-9">
                <h4 class="fw-bold mb-1">{{ $admin->name }}</h4>

                <p class="text-muted mb-2">{{ $admin->email }}</p>

                <div class="mb-2">
                    <span class="badge bg-primary me-2">Admin</span>

                    @if ($admin->email_verified_at)
                        <span class="badge bg-success">Email đã xác thực</span>
                    @else
                        <span class="badge bg-warning text-dark">Email chưa xác thực</span>
                    @endif
                </div>

                <a href="{{ route('admin.profile.edit') }}"
                   class="btn btn-outline-primary mt-2">
                    <i class="bi bi-pencil-square"></i> Chỉnh sửa thông tin
                </a>
            </div>
        </div>

        <hr>

        {{-- THÔNG TIN CHI TIẾT --}}
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="text-muted small">Số điện thoại</label>
                <div class="fw-semibold">
                    {{ $admin->phone ?? 'Chưa cập nhật' }}
                </div>
            </div>

            <div class="col-md-6 mb-3">
                <label class="text-muted small">Ngày tạo tài khoản</label>
                <div class="fw-semibold">
                    {{ $admin->created_at->format('d/m/Y') }}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
