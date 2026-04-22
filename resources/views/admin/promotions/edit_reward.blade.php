@extends('layouts.admin')

@section('title', 'Sửa voucher đổi điểm')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0">Sửa voucher đổi điểm</h5>
            <a href="{{ route('admin.promotions.index', ['tab' => 'rewards']) }}"
               class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ route('admin.promotions.updateReward', $reward->id) }}">
            @csrf
            @method('PUT')

            <div class="row">

                {{-- Tên --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tên voucher</label>
                    <input type="text"
                           name="name"
                           class="form-control"
                           value="{{ old('name', $reward->title) }}"
                           required>
                </div>

                {{-- Điểm --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Số điểm cần</label>
                    <input type="number"
                           name="points_required"
                           class="form-control"
                           value="{{ old('points_required', $reward->points_required) }}"
                           min="1"
                           required>
                </div>

                {{-- Hạng thành viên --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Hạng thành viên áp dụng</label>
                    <select name="member_level" class="form-select" required>
                        <option value="all" {{ old('member_level', $reward->member_level) == 'all' ? 'selected' : '' }}>Tất cả</option>
                        <option value="bronze" {{ old('member_level', $reward->member_level) == 'bronze' ? 'selected' : '' }}>Đồng</option>
                        <option value="silver" {{ old('member_level', $reward->member_level) == 'silver' ? 'selected' : '' }}>Bạc</option>
                        <option value="gold" {{ old('member_level', $reward->member_level) == 'gold' ? 'selected' : '' }}>Vàng</option>
                        <option value="diamond" {{ old('member_level', $reward->member_level) == 'diamond' ? 'selected' : '' }}>Kim cương</option>
                    </select>
                </div>

                {{-- Loại giảm --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Loại giảm</label>
                    <select name="discount_type" class="form-select">
                        <option value="percent" {{ old('discount_type', $reward->discount_type) == 'percent' ? 'selected' : '' }}>
                            Giảm %
                        </option>
                        <option value="fixed" {{ old('discount_type', $reward->discount_type) == 'fixed' ? 'selected' : '' }}>
                            Giảm tiền
                        </option>
                    </select>
                </div>

                {{-- Giá trị --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Giá trị giảm</label>
                    <input type="number"
                           name="discount_value"
                           class="form-control"
                           value="{{ old('discount_value', $reward->discount_value) }}"
                           min="1"
                           required>
                </div>

                {{-- Đơn tối thiểu --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Đơn tối thiểu</label>
                    <input type="number"
                           name="min_order_value"
                           class="form-control"
                           value="{{ old('min_order_value', $reward->min_order_value) }}"
                           min="0">
                </div>

                {{-- Giảm tối đa --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Giảm tối đa</label>
                    <input type="number"
                           name="max_discount"
                           class="form-control"
                           value="{{ old('max_discount', $reward->max_discount) }}"
                           min="0">
                </div>

                {{-- Ngày hiệu lực --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Số ngày hiệu lực sau khi đổi</label>
                    <input type="number"
                           name="valid_days"
                           class="form-control"
                           value="{{ old('valid_days', $reward->valid_days) }}"
                           min="1"
                           required>
                </div>

                {{-- Bắt đầu --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bắt đầu cho đổi</label>
                    <input type="datetime-local"
                           name="redeem_start_at"
                           class="form-control"
                           value="{{ old('redeem_start_at', $reward->redeem_start_at ? $reward->redeem_start_at->format('Y-m-d\TH:i') : '') }}">
                </div>

                {{-- Kết thúc --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kết thúc cho đổi</label>
                    <input type="datetime-local"
                           name="redeem_end_at"
                           class="form-control"
                           value="{{ old('redeem_end_at', $reward->redeem_end_at ? $reward->redeem_end_at->format('Y-m-d\TH:i') : '') }}">
                </div>

            </div>

            <button class="btn btn-primary">Cập nhật</button>

        </form>

    </div>
</div>
@endsection