@extends('layouts.frontend')

@section('title','Sổ địa chỉ')

@section('content')

<div class="container py-4">
<div class="row">

    {{-- SIDEBAR --}}
    <div class="col-md-3 mb-4">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center">

                <img
                    src="{{ auth()->user()->avatar
                        ? asset('storage/'.auth()->user()->avatar)
                        : asset('images/avatar-default.png') }}"
                    class="rounded-circle mb-3 border"
                    width="80" height="80"
                    style="object-fit:cover;"
                >

                <h6 class="fw-semibold mb-1">{{ auth()->user()->name }}</h6>
                <small class="text-muted">Quản lý tài khoản</small>

                <hr>

                <div class="text-start small">
                    <a class="d-block py-2 text-decoration-none text-dark"
                       href="{{ route('orders.history') }}">
                        <i class="bi bi-box-seam me-2"></i> Đơn hàng
                    </a>

                    <a class="d-block py-2 text-decoration-none text-dark"
                       href="{{ route('profile.index') }}">
                        <i class="bi bi-person me-2"></i> Thông tin tài khoản
                    </a>

                    <a class="d-block py-2 fw-semibold text-primary text-decoration-none"
                       href="{{ route('addresses.index') }}">
                        <i class="bi bi-geo-alt me-2"></i> Sổ địa chỉ
                    </a>
                </div>

            </div>
        </div>
    </div>


    {{-- CONTENT --}}
    <div class="col-md-9">

        {{-- DANH SÁCH --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body">

                <h5 class="fw-bold mb-3">Danh sách địa chỉ</h5>

                @forelse($addresses as $address)
                <div class="border rounded-4 p-3 mb-3 shadow-sm">

                    <div class="row align-items-center">

                        {{-- Thông tin --}}
                        <div class="col-md-7">
                            <div class="fw-semibold">
                                {{ $address->receiver_name }}
                                <span class="text-muted ms-2">| {{ $address->phone }}</span>
                            </div>

                            <div class="text-muted small mt-1">
                                {{ $address->full_address }}
                            </div>

                            @if($address->is_default)
                                <span class="badge bg-success mt-2">
                                    Địa chỉ mặc định
                                </span>
                            @endif
                        </div>

                        {{-- Nút thao tác --}}
                        <div class="col-md-5 text-md-end mt-3 mt-md-0">

                            <div class="d-flex justify-content-md-end gap-2 flex-wrap">

                                {{-- Sửa --}}
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary"
                                        onclick="openEditModal(
                                            '{{ $address->id }}',
                                            '{{ $address->receiver_name }}',
                                            '{{ $address->phone }}',
                                            '{{ $address->province }}',
                                            '{{ $address->district }}',
                                            '{{ $address->ward }}',
                                            '{{ $address->address_detail }}',
                                            '{{ $address->is_default }}'
                                        )">
                                    Sửa
                                </button>

                                {{-- Đặt mặc định --}}
                                @if(!$address->is_default)
                                <form action="{{ route('addresses.default', $address->id) }}"
                                      method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-primary">
                                        Mặc định
                                    </button>
                                </form>
                                @endif

                                {{-- Xóa --}}
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="openDeleteModal('{{ route('addresses.destroy', $address->id) }}')">
                                    Xóa
                                </button>

                            </div>

                        </div>

                    </div>

                </div>
                @empty
                    <p class="text-muted">Bạn chưa có địa chỉ nào.</p>
                @endforelse

            </div>
        </div>


        {{-- FORM THÊM --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">

                <h5 class="fw-bold mb-3">Thêm địa chỉ mới</h5>

                <form action="{{ route('addresses.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <input class="form-control" name="receiver_name" placeholder="Họ tên người nhận">
                        </div>

                        <div class="col-md-6 mb-3">
                            <input class="form-control" name="phone" placeholder="Số điện thoại">
                        </div>

                        <div class="col-md-4 mb-3">
                            <input class="form-control" name="province" placeholder="Tỉnh / Thành">
                        </div>

                        <div class="col-md-4 mb-3">
                            <input class="form-control" name="district" placeholder="Quận / Huyện">
                        </div>

                        <div class="col-md-4 mb-3">
                            <input class="form-control" name="ward" placeholder="Phường / Xã">
                        </div>

                        <div class="col-md-12 mb-3">
                            <input class="form-control" name="address_detail" placeholder="Địa chỉ chi tiết">
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="is_default" class="form-check-input">
                        <label class="form-check-label">Đặt làm địa chỉ mặc định</label>
                    </div>

                    <button class="btn btn-primary px-4">Thêm địa chỉ</button>

                </form>

            </div>
        </div>

    </div>
</div>
</div>


{{-- MODAL XÓA --}}
<div class="modal fade" id="confirmDeleteModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-body text-center p-4">

                <h5 class="fw-bold mb-2">Xóa địa chỉ?</h5>
                <p class="text-muted mb-4">Bạn có chắc muốn xóa địa chỉ này không?</p>

                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')

                    <button type="button" class="btn btn-light px-4 me-2" data-bs-dismiss="modal">
                        Hủy
                    </button>

                    <button class="btn btn-danger px-4">
                        Xóa
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>


{{-- MODAL SỬA --}}
<div class="modal fade" id="editAddressModal">
    <div class="modal-dialog">
        <div class="modal-content rounded-4">

            <div class="modal-header">
                <h5 class="modal-title">Cập nhật địa chỉ</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="editAddressForm" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-body">

                    <input class="form-control mb-2" name="receiver_name" id="edit_name">
                    <input class="form-control mb-2" name="phone" id="edit_phone">
                    <input class="form-control mb-2" name="province" id="edit_province">
                    <input class="form-control mb-2" name="district" id="edit_district">
                    <input class="form-control mb-2" name="ward" id="edit_ward">
                    <input class="form-control mb-2" name="address_detail" id="edit_detail">

                    <div class="form-check">
                        <input type="checkbox" name="is_default" id="edit_default" class="form-check-input">
                        <label class="form-check-label">Đặt làm mặc định</label>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                    <button class="btn btn-primary">Lưu</button>
                </div>

            </form>

        </div>
    </div>
</div>


<script>
function openDeleteModal(actionUrl) {
    document.getElementById('deleteForm').action = actionUrl;
    new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
}

function openEditModal(id, name, phone, province, district, ward, detail, isDefault) {
    let form = document.getElementById('editAddressForm');
    form.action = '/addresses/' + id;

    document.getElementById('edit_name').value = name;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_province').value = province;
    document.getElementById('edit_district').value = district;
    document.getElementById('edit_ward').value = ward;
    document.getElementById('edit_detail').value = detail;
    document.getElementById('edit_default').checked = (isDefault == 1);

    new bootstrap.Modal(document.getElementById('editAddressModal')).show();
}
</script>

@endsection