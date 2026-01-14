@extends('layouts.admin')

@section('title', 'Thông tin cá nhân')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">

        {{-- HEADER PROFILE --}}
        <div class="d-flex align-items-center gap-4 mb-4">

            {{-- AVATAR --}}
            <div class="flex-shrink-0 text-center">
                @if ($admin->avatar)
                    <img
                        src="{{ asset('storage/' . $admin->avatar) }}"
                        class="rounded-circle img-thumbnail"
                        style="width:130px;height:130px;object-fit:cover;"
                        alt="Avatar"
                    >
                @else
                    <div
                        class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                        style="width:130px;height:130px;font-size:46px"
                    >
                        {{ strtoupper(substr($admin->name, 0, 1)) }}
                    </div>
                @endif
            </div>

            {{-- INFO --}}
            <div class="flex-grow-1">
                <h4 class="fw-bold mb-1">{{ $admin->name }}</h4>
                <div class="text-muted mb-2">
                    <i class="bi bi-envelope"></i> {{ $admin->email }}
                </div>

                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge bg-primary">
                        <i class="bi bi-shield-lock"></i> Admin
                    </span>

                    @if ($admin->email_verified_at)
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle"></i> Email đã xác thực
                        </span>
                    @else
                        <span class="badge bg-warning text-dark">
                            <i class="bi bi-exclamation-circle"></i> Email chưa xác thực
                        </span>
                    @endif
                </div>

                <a href="{{ route('admin.profile.edit') }}"
                   class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil-square"></i> Chỉnh sửa thông tin
                </a>
            </div>
        </div>

        <hr class="my-4">

        {{-- THÔNG TIN CHI TIẾT --}}
        <div class="row g-3">

            <div class="col-md-6">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small mb-1">
                        <i class="bi bi-telephone"></i> Số điện thoại
                    </div>
                    <div class="fw-semibold">
                        {{ $admin->phone ?? 'Chưa cập nhật' }}
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small mb-1">
                        <i class="bi bi-calendar-event"></i> Ngày tạo tài khoản
                    </div>
                    <div class="fw-semibold">
                        {{ $admin->created_at->format('d/m/Y') }}
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
