@extends('layouts.frontend')

@section('title', 'Sổ địa chỉ')

@section('content')
<style>
    .address-page {
        background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
        min-height: 100%;
    }

    .address-main-card,
    .address-form-card {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 14px 35px rgba(30, 41, 59, 0.08);
    }

    .address-card-header {
        padding: 22px 24px 14px;
        border-bottom: 1px solid #eff3f8;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    }

    .address-card-header h5 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: #1f2d3d;
    }

    .address-card-header p {
        margin: 6px 0 0;
        color: #7b8794;
        font-size: 14px;
    }

    .address-card-body {
        padding: 22px 24px 24px;
    }

    .address-item {
        border: 1px solid #e9eef5;
        border-radius: 22px;
        padding: 18px;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
        transition: all 0.25s ease;
    }

    .address-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
        border-color: #dbe8f7;
    }

    .address-user-line {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 8px;
    }

    .address-user-name {
        font-size: 16px;
        font-weight: 700;
        color: #1f2d3d;
    }

    .address-user-phone {
        color: #6b7280;
        font-size: 14px;
    }

    .address-full {
        color: #596579;
        font-size: 14px;
        line-height: 1.6;
    }

    .default-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 12px;
        padding: 7px 12px;
        border-radius: 999px;
        background: #ecfdf3;
        color: #198754;
        border: 1px solid #d6f5e2;
        font-size: 12px;
        font-weight: 700;
    }

    .address-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 10px;
    }

    .btn-address {
        border-radius: 12px;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.22s ease;
    }

    .btn-address:hover {
        transform: translateY(-1px);
    }

    .btn-soft-primary {
        background: #eef5ff;
        color: #0d6efd;
        border: 1px solid #dbe9ff;
    }

    .btn-soft-primary:hover {
        background: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
    }

    .btn-soft-secondary {
        background: #f6f8fb;
        color: #475467;
        border: 1px solid #e6ebf2;
    }

    .btn-soft-secondary:hover {
        background: #e9eef5;
        color: #1f2d3d;
        border-color: #d8e0ea;
    }

    .btn-soft-danger {
        background: #fff5f6;
        color: #dc3545;
        border: 1px solid #f7cfd4;
    }

    .btn-soft-danger:hover {
        background: #dc3545;
        color: #fff;
        border-color: #dc3545;
    }

    .empty-address {
        text-align: center;
        padding: 32px 18px;
        border: 1px dashed #dbe3ee;
        border-radius: 20px;
        background: #fafcff;
        color: #7b8794;
    }

    .empty-address i {
        font-size: 34px;
        color: #b8c4d3;
        display: block;
        margin-bottom: 10px;
    }

    .form-title {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: #1f2d3d;
    }

    .form-subtitle {
        margin-top: 6px;
        color: #7b8794;
        font-size: 14px;
    }

    .address-label {
        font-size: 14px;
        font-weight: 600;
        color: #344054;
        margin-bottom: 8px;
    }

    .address-input {
        border-radius: 14px;
        border: 1px solid #e4eaf2;
        min-height: 48px;
        padding: 12px 14px;
        font-size: 14px;
        box-shadow: none !important;
        transition: all 0.22s ease;
    }

    .address-input:focus {
        border-color: #9ec5fe;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.08) !important;
    }

    .address-check {
        padding: 12px 14px;
        border: 1px solid #edf2f7;
        border-radius: 16px;
        background: #fafcff;
    }

    .address-check .form-check-input {
        margin-top: 0.2rem;
    }

    .address-submit-btn {
        min-width: 170px;
        border-radius: 14px;
        padding: 11px 18px;
        font-weight: 700;
        font-size: 14px;
        box-shadow: 0 10px 20px rgba(13, 110, 253, 0.14);
    }

    .modal-content.address-modal {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.18);
    }

    .address-modal .modal-header {
        border-bottom: 1px solid #eef2f6;
        padding: 18px 22px;
        background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    }

    .address-modal .modal-title {
        font-size: 18px;
        font-weight: 700;
        color: #1f2d3d;
    }

    .address-modal .modal-body {
        padding: 20px 22px;
    }

    .address-modal .modal-footer {
        border-top: 1px solid #eef2f6;
        padding: 16px 22px 20px;
    }

    .delete-modal-icon {
        width: 70px;
        height: 70px;
        margin: 0 auto 14px;
        border-radius: 50%;
        background: #fff5f6;
        color: #dc3545;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
    }

    .delete-modal-title {
        font-size: 20px;
        font-weight: 700;
        color: #1f2d3d;
        margin-bottom: 8px;
    }

    .delete-modal-text {
        font-size: 14px;
        color: #7b8794;
        margin-bottom: 0;
        line-height: 1.6;
    }

    @media (max-width: 767.98px) {
        .address-card-header,
        .address-card-body {
            padding-left: 16px;
            padding-right: 16px;
        }

        .address-item {
            padding: 15px;
        }

        .address-actions {
            justify-content: flex-start;
            margin-top: 14px;
        }
    }
</style>

<div class="address-page">
    <div class="container py-4">
        <div class="row">
            {{-- SIDEBAR --}}
            @include('frontend.partials.account-sidebar')

            {{-- CONTENT --}}
            <div class="col-md-9">
                {{-- DANH SÁCH --}}
                <div class="address-main-card mb-4">
                    <div class="address-card-header">
                        <h5>Danh sách địa chỉ</h5>
                        <p>Quản lý địa chỉ giao hàng để đặt hàng nhanh hơn và thuận tiện hơn.</p>
                    </div>

                    <div class="address-card-body">
                        @forelse($addresses as $address)
                            <div class="address-item mb-3">
                                <div class="row align-items-center">
                                    <div class="col-md-7">
                                        <div class="address-user-line">
                                            <span class="address-user-name">{{ $address->receiver_name }}</span>
                                            <span class="address-user-phone">| {{ $address->phone }}</span>
                                        </div>

                                        <div class="address-full">
                                            {{ $address->full_address }}
                                        </div>

                                        @if($address->is_default)
                                            <span class="default-badge">
                                                <i class="bi bi-check-circle-fill"></i>
                                                Địa chỉ mặc định
                                            </span>
                                        @endif
                                    </div>

                                    <div class="col-md-5 mt-3 mt-md-0">
                                        <div class="address-actions">
                                            {{-- Sửa --}}
                                            <button type="button"
                                                    class="btn btn-address btn-soft-secondary"
                                                    onclick="openEditModal(
                                                        '{{ $address->id }}',
                                                        @js($address->receiver_name),
                                                        @js($address->phone),
                                                        @js($address->province),
                                                        @js($address->district),
                                                        @js($address->ward),
                                                        @js($address->address_detail),
                                                        '{{ $address->is_default ? 1 : 0 }}'
                                                    )">
                                                <i class="bi bi-pencil-square me-1"></i>
                                                Sửa
                                            </button>

                                            {{-- Đặt mặc định --}}
                                            @if(!$address->is_default)
                                                <form action="{{ route('addresses.default', $address->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-address btn-soft-primary">
                                                        <i class="bi bi-star me-1"></i>
                                                        Mặc định
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Xóa --}}
                                            <button type="button"
                                                    class="btn btn-address btn-soft-danger"
                                                    onclick="openDeleteModal('{{ route('addresses.destroy', $address->id) }}')">
                                                <i class="bi bi-trash3 me-1"></i>
                                                Xóa
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-address">
                                <i class="bi bi-geo-alt"></i>
                                <div class="fw-semibold mb-1">Bạn chưa có địa chỉ nào</div>
                                <div class="small">Hãy thêm địa chỉ mới để việc đặt hàng trở nên nhanh hơn.</div>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- FORM THÊM --}}
                <div class="address-form-card">
                    <div class="address-card-header">
                        <h5 class="form-title">Thêm địa chỉ mới</h5>
                        <p class="form-subtitle">Điền đầy đủ thông tin để lưu địa chỉ giao hàng của bạn.</p>
                    </div>

                    <div class="address-card-body">
                        <form action="{{ route('addresses.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="address-label">Họ tên người nhận</label>
                                    <input type="text"
                                           class="form-control address-input"
                                           name="receiver_name"
                                           placeholder="Nhập họ tên người nhận"
                                           value="{{ old('receiver_name') }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="address-label">Số điện thoại</label>
                                    <input type="text"
                                           class="form-control address-input"
                                           name="phone"
                                           placeholder="Nhập số điện thoại"
                                           value="{{ old('phone') }}">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="address-label">Tỉnh / Thành phố</label>
                                    <input type="text"
                                           class="form-control address-input"
                                           name="province"
                                           placeholder="Ví dụ: Cần Thơ"
                                           value="{{ old('province') }}">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="address-label">Quận / Huyện</label>
                                    <input type="text"
                                           class="form-control address-input"
                                           name="district"
                                           placeholder="Ví dụ: Ninh Kiều"
                                           value="{{ old('district') }}">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="address-label">Phường / Xã</label>
                                    <input type="text"
                                           class="form-control address-input"
                                           name="ward"
                                           placeholder="Ví dụ: An Khánh"
                                           value="{{ old('ward') }}">
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="address-label">Địa chỉ chi tiết</label>
                                    <input type="text"
                                           class="form-control address-input"
                                           name="address_detail"
                                           placeholder="Số nhà, tên đường..."
                                           value="{{ old('address_detail') }}">
                                </div>
                            </div>

                            <div class="address-check mb-3">
                                <div class="form-check mb-0">
                                    <input type="checkbox"
                                           name="is_default"
                                           class="form-check-input"
                                           id="is_default"
                                           {{ old('is_default') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_default">
                                        Đặt làm địa chỉ mặc định
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary address-submit-btn">
                                <i class="bi bi-plus-circle me-1"></i>
                                Thêm địa chỉ
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL XÓA --}}
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content address-modal">
            <div class="modal-body text-center p-4 p-md-5">
                <div class="delete-modal-icon">
                    <i class="bi bi-trash3"></i>
                </div>

                <div class="delete-modal-title">Xóa địa chỉ?</div>
                <p class="delete-modal-text">
                    Bạn có chắc muốn xóa địa chỉ này không? Thao tác này sẽ không thể hoàn tác.
                </p>

                <form id="deleteForm" method="POST" class="mt-4">
                    @csrf
                    @method('DELETE')

                    <button type="button"
                            class="btn btn-light px-4 me-2 rounded-3"
                            data-bs-dismiss="modal">
                        Hủy
                    </button>

                    <button type="submit" class="btn btn-danger px-4 rounded-3">
                        Xóa địa chỉ
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- MODAL SỬA --}}
<div class="modal fade" id="editAddressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content address-modal">
            <div class="modal-header">
                <h5 class="modal-title">Cập nhật địa chỉ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <form id="editAddressForm" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="address-label">Họ tên người nhận</label>
                        <input type="text" class="form-control address-input" name="receiver_name" id="edit_name" placeholder="Nhập họ tên người nhận">
                    </div>

                    <div class="mb-3">
                        <label class="address-label">Số điện thoại</label>
                        <input type="text" class="form-control address-input" name="phone" id="edit_phone" placeholder="Nhập số điện thoại">
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="address-label">Tỉnh / Thành</label>
                            <input type="text" class="form-control address-input" name="province" id="edit_province" placeholder="Tỉnh / Thành">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="address-label">Quận / Huyện</label>
                            <input type="text" class="form-control address-input" name="district" id="edit_district" placeholder="Quận / Huyện">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="address-label">Phường / Xã</label>
                            <input type="text" class="form-control address-input" name="ward" id="edit_ward" placeholder="Phường / Xã">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="address-label">Địa chỉ chi tiết</label>
                        <input type="text" class="form-control address-input" name="address_detail" id="edit_detail" placeholder="Số nhà, tên đường...">
                    </div>

                    <div class="address-check">
                        <div class="form-check mb-0">
                            <input type="checkbox" name="is_default" id="edit_default" class="form-check-input">
                            <label class="form-check-label" for="edit_default">
                                Đặt làm địa chỉ mặc định
                            </label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">
                        Hủy
                    </button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4">
                        Lưu thay đổi
                    </button>
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
        const form = document.getElementById('editAddressForm');

        form.action = '/addresses/' + id;

        document.getElementById('edit_name').value = name ?? '';
        document.getElementById('edit_phone').value = phone ?? '';
        document.getElementById('edit_province').value = province ?? '';
        document.getElementById('edit_district').value = district ?? '';
        document.getElementById('edit_ward').value = ward ?? '';
        document.getElementById('edit_detail').value = detail ?? '';
        document.getElementById('edit_default').checked = Number(isDefault) === 1;

        new bootstrap.Modal(document.getElementById('editAddressModal')).show();
    }
</script>
@endsection