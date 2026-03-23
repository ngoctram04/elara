@extends('layouts.admin')

@section('title', 'Danh sách khách hàng')

@section('content')
<div class="card shadow-sm border-0">
<div class="card-body">

<h5 class="fw-bold mb-1" >Danh sách khách hàng</h5>

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
<form method="GET" class="d-flex gap-2 mb-3 flex-wrap">

<input type="text"
name="keyword"
class="form-control"
style="max-width:260px"
placeholder="Tìm theo tên, mã hoặc email"
value="{{ request('keyword') }}">

{{-- HẠNG THÀNH VIÊN --}}
<select name="member_level" class="form-select" style="max-width:180px">

<option value="">Hạng thành viên</option>

<option value="bronze"
{{ request('member_level') == 'bronze' ? 'selected' : '' }}>
Đồng
</option>

<option value="silver"
{{ request('member_level') == 'silver' ? 'selected' : '' }}>
Bạc
</option>

<option value="gold"
{{ request('member_level') == 'gold' ? 'selected' : '' }}>
Vàng
</option>

<option value="diamond"
{{ request('member_level') == 'diamond' ? 'selected' : '' }}>
Kim cương
</option>

</select>

{{-- SẮP XẾP --}}
<select name="sort" class="form-select" style="max-width:200px">

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

<button type="submit"
class="btn btn-outline-primary d-flex align-items-center gap-1">

<i class="bi bi-search"></i>
Lọc

</button>

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

<th width="60">Mã</th>

<th>Họ và tên</th>

<th>Email</th>

<th width="100">Hạng</th>

<th width="150">Tổng chi tiêu</th>

<th width="140">Tổng điểm hiện tại</th>

<th width="120">Trạng thái</th>
<th width="100">Cảnh báo</th>
<th width="90" class="text-center">Chi tiết</th>

<th width="90" class="text-center">Thao tác</th>

</tr>

</thead>

<tbody>

@forelse($customers as $customer)

<tr>

<td>KH{{ str_pad($customer->id, 4, '0', STR_PAD_LEFT) }}</td>

<td>{{ $customer->name }}</td>

<td>{{ $customer->email }}</td>

{{-- HẠNG THÀNH VIÊN --}}
<td>

@switch($customer->member_level)

@case('bronze')
<span class="badge bg-secondary">Đồng</span>
@break

@case('silver')
<span class="badge bg-info text-dark">Bạc</span>
@break

@case('gold')
<span class="badge bg-warning text-dark">Vàng</span>
@break

@case('diamond')
<span class="badge bg-primary">Kim cương</span>
@break

@default
<span class="badge bg-light text-dark">Chưa có</span>

@endswitch

</td>

{{-- TỔNG CHI TIÊU --}}
<td>

{{ number_format($customer->spending ?? 0, 0, ',', '.') }} ₫

</td>

{{-- ĐIỂM --}}
<td>

{{ number_format($customer->loyalty_points) }}

</td>

{{-- TRẠNG THÁI --}}
<td>

@if($customer->is_active)

<span class="badge bg-success">Hoạt động</span>

@else

<span class="badge bg-secondary">Đã khóa</span>

@endif

</td>
<td>

@if($customer->cancel_count >= 5)

<span class="badge bg-danger">
⚠ Hủy {{ $customer->cancel_count }} đơn / 7 ngày
</span>

@elseif($customer->cancel_count >= 3)

<span class="badge bg-warning text-dark">
{{ $customer->cancel_count }} đơn hủy
</span>

@else

<span class="text-muted">-</span>

@endif

</td>
{{-- CHI TIẾT --}}
<td class="text-center">

<a href="{{ route('admin.customers.show',$customer) }}"
class="btn btn-sm btn-primary">

Xem

</a>

</td>

{{-- THAO TÁC --}}
<td class="text-center">

@if($customer->is_active)

<button class="btn btn-sm btn-warning"
data-bs-toggle="modal"
data-bs-target="#blockModal{{ $customer->id }}">

Khóa

</button>

{{-- MODAL --}}
<div class="modal fade"
id="blockModal{{ $customer->id }}"
tabindex="-1">

<div class="modal-dialog modal-dialog-centered">

<form method="POST"
action="{{ route('admin.customers.toggle-status',$customer) }}"
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

<div class="modal-body">

<p>

Bạn đang khóa tài khoản:
<strong>{{ $customer->name }}</strong>

</p>

<div class="mb-3">

<label class="form-label fw-semibold">

Lý do khóa
<span class="text-danger">*</span>

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

<form method="POST"
id="unblock-form-{{ $customer->id }}"
action="{{ route('admin.customers.toggle-status',$customer) }}"
class="d-inline">

@csrf

<button type="button"
class="btn btn-sm btn-success btn-unblock"
data-id="{{ $customer->id }}">
Mở
</button>

</form>

@endif

</td>

</tr>

@empty

<tr>

<td colspan="10" class="text-center text-muted">

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
@push('scripts')
<script>

document.querySelectorAll('.btn-unblock').forEach(btn => {

btn.addEventListener('click', function(){

let id = this.dataset.id

Swal.fire({
title: 'Mở lại tài khoản?',
text: 'Bạn có chắc muốn mở lại tài khoản này?',
icon: 'question',
showCancelButton: true,
confirmButtonColor: '#16a34a',
cancelButtonText: 'Hủy',
confirmButtonText: 'Mở tài khoản'
}).then((result) => {

if(result.isConfirmed){
document.getElementById('unblock-form-'+id).submit()
}

})

})

})

</script>
@endpush