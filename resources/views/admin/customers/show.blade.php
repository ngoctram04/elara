@extends('layouts.admin')

@section('title', 'Chi tiết khách hàng')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body">

        {{-- HEADER --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-semibold mb-0">Chi tiết khách hàng</h5>
            <a href="{{ route('admin.customers.index') }}"
               class="btn btn-sm btn-secondary">
                ← Quay lại
            </a>
        </div>

        {{-- ALERT --}}
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- INFO --}}
        <div class="row align-items-start">
            {{-- AVATAR + STATUS --}}
            <div class="col-md-4 text-center">
                <img src="{{ $user->avatar
                        ? asset('storage/' . $user->avatar)
                        : asset('images/default-avatar.png') }}"
                     class="rounded-circle mb-3 border"
                     width="120"
                     height="120"
                     alt="Avatar">

                <h6 class="fw-semibold mb-1">
                    {{ $user->name }}
                </h6>

                @if($user->is_active)
                    <span class="badge bg-success">Hoạt động</span>
                @else
                    <span class="badge bg-danger">Đã khóa</span>
                @endif
            </div>

            {{-- THÔNG TIN --}}
            <div class="col-md-8">
                <table class="table table-borderless mb-3">
                    <tr>
                        <th width="160">Email</th>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <th>Số điện thoại</th>
                        <td>{{ $user->phone ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Vai trò</th>
                        <td>
                            <span class="badge bg-info text-dark">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Ngày tạo</th>
                        <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                    </tr>

                    {{-- LÝ DO KHÓA --}}
                    @if(!$user->is_active && $user->blocked_reason)
                        <tr>
                            <th>Lý do khóa</th>
                            <td class="text-danger fw-semibold">
                                {{ $user->blocked_reason }}
                            </td>
                        </tr>
                    @endif
                </table>

                {{-- ACTION --}}
                <div class="mt-2">
                    @if($user->is_active)
                        {{-- KHÓA --}}
                        <button class="btn btn-warning"
                                data-bs-toggle="modal"
                                data-bs-target="#blockUserModal">
                            Khóa tài khoản
                        </button>
                    @else
                        {{-- MỞ --}}
                        <form method="POST"
                              action="{{ route('admin.customers.toggle-status', $user) }}"
                              class="d-inline">
                            @csrf
                            <button class="btn btn-success"
                                    onclick="return confirm('Bạn có chắc muốn mở lại tài khoản này?')">
                                Mở tài khoản
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

{{-- MODAL KHÓA --}}
@if($user->is_active)
<div class="modal fade" id="blockUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST"
              action="{{ route('admin.customers.toggle-status', $user) }}"
              class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Khóa tài khoản</h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-start">
                <p class="mb-2">
                    Bạn đang khóa tài khoản:
                    <strong>{{ $user->name }}</strong>
                </p>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Lý do khóa <span class="text-danger">*</span>
                    </label>
                    <textarea name="blocked_reason"
                              class="form-control"
                              rows="4"
                              required
                              placeholder="Nhập lý do khóa tài khoản..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Hủy
                </button>
                <button class="btn btn-warning">
                    Xác nhận khóa
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
