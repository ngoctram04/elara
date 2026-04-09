@extends('layouts.admin')
@section('title', 'Tạo voucher đổi điểm')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0">Tạo voucher đổi điểm</h5>
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

        <form method="POST" action="{{ route('admin.promotions.storeReward') }}">
            @csrf

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">Tên voucher</label>
                    <input type="text"
                           name="name"
                           class="form-control"
                           value="{{ old('name') }}"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Số điểm cần</label>
                    <input type="number"
                           name="points_required"
                           class="form-control"
                           value="{{ old('points_required') }}"
                           min="1"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Loại giảm</label>
                    <select name="discount_type" class="form-select">
                        <option value="percent" {{ old('discount_type') == 'percent' ? 'selected' : '' }}>Giảm %</option>
                        <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>Giảm tiền</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Giá trị giảm</label>
                    <input type="number"
                           name="discount_value"
                           class="form-control"
                           value="{{ old('discount_value') }}"
                           min="1"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Đơn tối thiểu</label>
                    <input type="number"
                           name="min_order_value"
                           class="form-control"
                           value="{{ old('min_order_value') }}"
                           min="0">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Giảm tối đa</label>
                    <input type="number"
                           name="max_discount"
                           class="form-control"
                           value="{{ old('max_discount') }}"
                           min="0">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Số ngày hiệu lực sau khi đổi</label>
                    <input type="number"
                           name="valid_days"
                           class="form-control"
                           value="{{ old('valid_days', 30) }}"
                           min="1"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Bắt đầu cho đổi</label>
                    <input type="datetime-local"
                           name="redeem_start_at"
                           class="form-control"
                           value="{{ old('redeem_start_at') }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Kết thúc cho đổi</label>
                    <input type="datetime-local"
                           name="redeem_end_at"
                           class="form-control"
                           value="{{ old('redeem_end_at') }}">
                </div>

            </div>

            <button type="submit" class="btn btn-primary">
                Tạo voucher
            </button>

        </form>
    </div>
</div>
@endsection