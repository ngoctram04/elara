@extends('layouts.admin')

@section('title', 'Sửa voucher đổi điểm')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body">

        <h5 class="fw-bold mb-4">Sửa voucher đổi điểm</h5>

        <form method="POST"
              action="{{ route('admin.promotions.updateReward', $reward->id) }}">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Tên voucher</label>
                    <input type="text" name="name"
                           class="form-control"
                           value="{{ $reward->title }}"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Số điểm cần</label>
                    <input type="number" name="points_required"
                           class="form-control"
                           value="{{ $reward->points_required }}"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Loại giảm</label>
                    <select name="discount_type" class="form-select">
                        <option value="percent"
                            {{ $reward->discount_type == 'percent' ? 'selected' : '' }}>
                            Giảm %
                        </option>
                        <option value="fixed"
                            {{ $reward->discount_type == 'fixed' ? 'selected' : '' }}>
                            Giảm tiền
                        </option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Giá trị giảm</label>
                    <input type="number" name="discount_value"
                           class="form-control"
                           value="{{ $reward->discount_value }}"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Đơn tối thiểu</label>
                    <input type="number" name="min_order_value"
                           class="form-control"
                           value="{{ $reward->min_order_value }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label>Giảm tối đa</label>
                    <input type="number" name="max_discount"
                           class="form-control"
                           value="{{ $reward->max_discount }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label>Số ngày hiệu lực</label>
                    <input type="number" name="valid_days"
                           class="form-control"
                           value="{{ $reward->valid_days }}"
                           required>
                </div>
            </div>

            <button class="btn btn-primary">Cập nhật</button>
        </form>

    </div>
</div>
@endsection