@extends('layouts.admin')

@section('title','Yêu cầu hoàn tiền')

@section('content')

<div class="container-fluid">

<h4 class="mb-4">Yêu cầu hoàn tiền</h4>

<div class="card shadow-sm border-0">
<div class="card-body p-0">
<div class="card shadow-sm border-0 mb-3">
<div class="card-body">

<form method="GET" class="row g-2 mb-3 align-items-center">

<div class="col-md-4">

<input
type="text"
name="search"
class="form-control form-control-sm"
placeholder="Tìm mã đơn hoặc khách hàng..."
value="{{ request('search') }}">

</div>

<div class="col-md-3">

<select name="status" class="form-select form-select-sm">

<option value="">-- Tất cả trạng thái --</option>

<option value="pending"
{{ request('status')=='pending' ? 'selected':'' }}>
Chờ duyệt
</option>

<option value="approved"
{{ request('status')=='approved' ? 'selected':'' }}>
Đã duyệt
</option>

<option value="refunded"
{{ request('status')=='refunded' ? 'selected':'' }}>
Đã hoàn tiền
</option>

<option value="rejected"
{{ request('status')=='rejected' ? 'selected':'' }}>
Từ chối
</option>

</select>

</div>

<div class="col-md-3">

<select name="sort" class="form-select form-select-sm">

<option value="new"
{{ request('sort')=='new' ? 'selected':'' }}>
Mới nhất
</option>

<option value="old"
{{ request('sort')=='old' ? 'selected':'' }}>
Cũ nhất
</option>

</select>

</div>

<div class="col-md-2 d-flex gap-2">

<button class="btn btn-outline-primary btn-sm">
<i class="bi bi-search"></i>
Lọc
</button>

<a href="{{ route('admin.refunds.index') }}"
class="btn btn-outline-secondary btn-sm">
Đặt lại
</a>

</div>

</div>

</form>

</div>
</div>
<table class="table table-hover align-middle mb-0">

<thead class="table-light">
<tr>
<th>STT</th>
<th>Đơn hàng</th>
<th>Khách hàng</th>
<th>Lý do</th>
<th>Trạng thái</th>
<th>Ngày gửi</th>
<th width="120">Chi tiết</th>
<th width="200">Hành động</th>
</tr>
</thead>

<tbody>

@forelse($refunds as $refund)

<tr>

<td>
{{ ($refunds->currentPage() - 1) * $refunds->perPage() + $loop->iteration }}
</td>

<td>
    <a href="{{ route('admin.orders.show',$refund->order_id) }}">
        DH{{ str_pad($refund->order_id, 5, '0', STR_PAD_LEFT) }}
    </a>
</td>

<td>{{ $refund->user->name }}</td>

<td style="max-width:250px">
{{ Str::limit($refund->reason,60) }}
</td>

<td>

@if($refund->status == 'pending')
<span class="badge bg-warning">Chờ duyệt</span>

@elseif($refund->status == 'approved')
<span class="badge bg-primary">Đã duyệt</span>

@elseif($refund->status == 'refunded')
<span class="badge bg-success">Đã hoàn tiền</span>

@else
<span class="badge bg-danger">Từ chối</span>
@endif

</td>

<td>
{{ $refund->created_at->format('d/m/Y H:i') }}
</td>

<td>

<button
class="btn btn-sm btn-dark"
data-bs-toggle="modal"
data-bs-target="#refundModal{{ $refund->id }}"
>
Xem
</button>

</td>

<td>

@if($refund->status == 'pending')

<form action="{{ route('admin.refunds.approve',$refund->id) }}"
method="POST"
class="d-inline">
@csrf
<button class="btn btn-sm btn-success">
Duyệt
</button>
</form>

<button
class="btn btn-sm btn-danger"
data-bs-toggle="modal"
data-bs-target="#rejectModal{{ $refund->id }}">
Từ chối
</button>

@endif


@if($refund->status == 'approved')

<form action="{{ route('admin.refunds.refunded',$refund->id) }}"
method="POST">
@csrf
<button class="btn btn-sm btn-primary">
Đã hoàn tiền
</button>
</form>

@endif

</td>

</tr>

{{-- MODAL TỪ CHỐI --}}
<div class="modal fade" id="rejectModal{{ $refund->id }}" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">

<form action="{{ route('admin.refunds.reject',$refund->id) }}" method="POST">
@csrf

<div class="modal-header">
<h5 class="modal-title">Từ chối yêu cầu hoàn tiền</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<label class="form-label fw-semibold">Lý do từ chối</label>

<textarea
name="admin_note"
class="form-control"
rows="4"
required
placeholder="Nhập lý do từ chối..."></textarea>

</div>

<div class="modal-footer">

<button
type="button"
class="btn btn-secondary"
data-bs-dismiss="modal">
Hủy
</button>

<button
type="submit"
class="btn btn-danger">
Xác nhận từ chối
</button>

</div>

</form>

</div>
</div>
</div>
{{-- ================= MODAL CHI TIẾT ================= --}}

<div class="modal fade"
id="refundModal{{ $refund->id }}"
tabindex="-1">

<div class="modal-dialog modal-lg">

<div class="modal-content">

<div class="modal-header">
<h5 class="modal-title">
    Chi tiết yêu cầu hoàn tiền
    HT{{ str_pad($refund->id, 5, '0', STR_PAD_LEFT) }}
</h5>

<button class="btn-close"
data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<p>
<b>Đơn hàng:</b>
DH{{ str_pad($refund->order_id, 5, '0', STR_PAD_LEFT) }}
</p>

<p>
<b>Khách hàng:</b>
{{ $refund->user->name }}
</p>

<p><b>Lý do hoàn tiền:</b></p>

<div class="border rounded p-3 mb-3 bg-light">
{{ $refund->reason }}
</div>

<h6 class="mb-2">
Hình ảnh / video minh chứng
</h6>

<div class="d-flex flex-wrap gap-2">
@forelse($refund->media as $media)

@if(Str::endsWith($media->file_path,['jpg','jpeg','png','webp']))

<img
src="{{ asset('storage/'.$media->file_path) }}"
width="120"
height="120"
class="refund-preview"
style="object-fit:cover;border-radius:6px;cursor:pointer"
>

@else

<video
width="200"
class="refund-preview"
style="border-radius:6px;cursor:pointer">

<source src="{{ asset('storage/'.$media->file_path) }}">
</video>

@endif

@empty

<p class="text-muted">
Không có hình minh chứng
</p>

@endforelse

</div>

</div>

</div>
</div>
</div>

@empty

<tr>
<td colspan="8" class="text-center py-4">
Không có yêu cầu hoàn tiền
</td>
</tr>

@endforelse

</tbody>

</table>

</div>
</div>

<div class="mt-3">
{{ $refunds->links() }}
</div>

</div>
{{-- PREVIEW MEDIA MODAL --}}
<div class="modal fade" id="previewMediaModal" tabindex="-1">
<div class="modal-dialog modal-xl modal-dialog-centered">
<div class="modal-content bg-dark border-0">

<div class="modal-header border-0">
<button class="btn-close btn-close-white"
data-bs-dismiss="modal"></button>
</div>

<div class="modal-body text-center">

<img id="previewImage"
style="max-width:100%;max-height:75vh;display:none;border-radius:8px;">

<video id="previewVideo"
controls
style="max-width:100%;max-height:75vh;display:none;border-radius:8px">
</video>

</div>

</div>
</div>
</div>
<script>

document.querySelectorAll('.refund-preview').forEach(el => {

el.addEventListener('click', function(){

const img = document.getElementById('previewImage');
const video = document.getElementById('previewVideo');

img.style.display = 'none';
video.style.display = 'none';

if(this.tagName === 'IMG'){

img.src = this.src;
img.style.display = 'block';

}else{

let source = this.querySelector('source');

if(source){
video.src = source.src;
}

video.style.display = 'block';

}

let modal = new bootstrap.Modal(
document.getElementById('previewMediaModal')
);

modal.show();

});

});

</script>
@endsection