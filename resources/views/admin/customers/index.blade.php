@extends('layouts.admin')

@section('title', 'Danh sách khách hàng')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body">

        {{-- HEADER --}}
        <h5 class="fw-semibold mb-3">Danh sách khách hàng</h5>

        {{-- ALERT --}}

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- FILTER --}}
<form method="GET" class="d-flex gap-2 mb-3">

    {{-- TÌM KIẾM --}}
    <input type="text"
           name="keyword"
           class="form-control"
           style="max-width: 320px"
           placeholder="Tìm theo tên, email, SĐT..."
           value="{{ request('keyword') }}">

    {{-- SẮP XẾP --}}
    <select name="sort" class="form-select" style="max-width: 200px">
        <option value="">Sắp xếp theo</option>
        <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>
            Mới nhất
        </option>
        <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>
            Cũ nhất
        </option>
        <option value="active" {{ request('sort') === 'active' ? 'selected' : '' }}>
            Hoạt động
        </option>
        <option value="blocked" {{ request('sort') === 'blocked' ? 'selected' : '' }}>
            Đã khóa
        </option>
    </select>

    {{-- NÚT LỌC --}}
    <button type="submit" class="btn btn-outline-primary d-flex align-items-center gap-1">
        <i class="bi bi-search"></i>
        Lọc
    </button>

    {{-- ĐẶT LẠI --}}
    <a href="{{ route('admin.customers.index') }}"
       class="btn btn-outline-secondary">
        Đặt lại
    </a>
</form>


        {{-- TABLE --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                <tr>
                    <th width="60">#</th>
                    <th>Họ và tên</th>
                    <th>Email</th>
                    <th width="130">Trạng thái</th>
                    <th width="100" class="text-center">Chi tiết</th>
                    <th width="150" class="text-center">Thao tác</th>
                </tr>
                </thead>
                <tbody>
                @forelse($customers as $key => $customer)
                    <tr>
                        <td>{{ $customers->firstItem() + $key }}</td>

                        <td>{{ $customer->name }}</td>

                        <td>{{ $customer->email }}</td>

                        <td>
                            @if($customer->is_active)
                                <span class="badge bg-success">Hoạt động</span>
                            @else
                                <span class="badge bg-secondary">Đã khóa</span>
                            @endif
                        </td>

                        {{-- CHI TIẾT --}}
                        <td class="text-center">
                            <a href="{{ route('admin.customers.show', $customer) }}"
                               class="btn btn-sm btn-primary">
                                Xem
                            </a>
                        </td>

                        {{-- THAO TÁC --}}
                        <td class="text-center">

                            {{-- ĐANG HOẠT ĐỘNG → KHÓA --}}
                            @if($customer->is_active)
                                <button class="btn btn-sm btn-warning"
                                        data-bs-toggle="modal"
                                        data-bs-target="#blockModal{{ $customer->id }}">
                                    Khóa
                                </button>

                                {{-- MODAL KHÓA --}}
                                <div class="modal fade"
                                     id="blockModal{{ $customer->id }}"
                                     tabindex="-1"
                                     aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <form method="POST"
                                              action="{{ route('admin.customers.toggle-status', $customer) }}"
                                              class="modal-content">
                                            @csrf

                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    Khóa tài khoản
                                                </h5>
                                                <button type="button"
                                                        class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body text-start">
                                                <p class="mb-2">
                                                    Bạn đang khóa tài khoản:
                                                    <strong>{{ $customer->name }}</strong>
                                                </p>

                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">
                                                        Lý do khóa <span class="text-danger">*</span>
                                                    </label>
                                                    <textarea name="blocked_reason"
                                                              class="form-control"
                                                              rows="3"
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

                            @else
                                {{-- ĐANG BỊ KHÓA → MỞ --}}
                                <form method="POST"
                                      action="{{ route('admin.customers.toggle-status', $customer) }}"
                                      class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-success"
                                            onclick="return confirm('Bạn có chắc muốn mở lại tài khoản này?')">
                                        Mở
                                    </button>
                                </form>
                            @endif

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            Không có khách hàng
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-3">
            {{ $customers->links() }}
        </div>

    </div>
</div>
@endsection
