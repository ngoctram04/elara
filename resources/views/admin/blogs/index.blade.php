@extends('layouts.admin')

@section('title','Quản lý Blog')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

<h4 class="mb-0">Quản lý Blog</h4>

<div class="d-flex gap-2">

<form method="GET" class="d-flex gap-2">

<input
type="text"
name="keyword"
value="{{ request('keyword') }}"
class="form-control"
placeholder="Tìm bài viết...">

<select name="sort" class="form-select">

<option value="">Mặc định</option>

<option value="most"
{{ request('sort')=='most' ? 'selected' : '' }}>
Xem nhiều nhất
</option>

<option value="least"
{{ request('sort')=='least' ? 'selected' : '' }}>
Xem ít nhất
</option>

</select>

<button class="btn btn-dark">
<i class="bi bi-search"></i>
</button>

</form>

<a href="{{ route('admin.blogs.create') }}" class="btn btn-primary">
<i class="bi bi-plus"></i> Thêm bài viết
</a>

</div>

</div>


<div class="card shadow-sm border-0">

<div class="card-body p-0">

<div class="table-responsive">

<table class="table table-hover mb-0 align-middle">

<thead class="table-light">

<tr>

<th width="60">ID</th>
<th width="120">Ảnh</th>
<th>Tiêu đề</th>
<th width="120">Lượt xem</th>
<th width="120">Trạng thái</th>
<th width="150">Ngày tạo</th>
<th width="160">Hành động</th>

</tr>

</thead>

<tbody>

@forelse($blogs as $blog)

<tr>

<td>{{ $blog->id }}</td>

<td>

@if($blog->thumbnail)

<img
src="{{ asset('storage/'.$blog->thumbnail) }}"
width="80"
class="rounded">

@else

<span class="text-muted">Không có</span>

@endif

</td>

<td>

<strong>{{ $blog->title }}</strong>

</td>

<td>

<span class="badge bg-secondary">
{{ $blog->views }}
</span>

</td>

<td>

@if($blog->is_active)
<span class="badge bg-success">Hiển thị</span>
@else
<span class="badge bg-secondary">Đã ẩn</span>
@endif

</td>

<td>

{{ $blog->created_at?->format('d/m/Y') }}

</td>

<td>

<div class="d-flex gap-1">

<a href="{{ route('admin.blogs.edit',$blog->id) }}"
class="btn btn-sm btn-warning">

<i class="bi bi-pencil"></i>

</a>

<form
action="{{ route('admin.blogs.toggle',$blog->id) }}"
method="POST"
class="toggle-form">

@csrf

<button
type="button"
class="btn btn-sm {{ $blog->is_active ? 'btn-secondary' : 'btn-success' }} btn-toggle">

@if($blog->is_active)
<i class="bi bi-eye-slash"></i>
@else
<i class="bi bi-eye"></i>
@endif

</button>

</form>

</div>

</td>

</tr>

@empty

<tr>

<td colspan="7"
class="text-center py-4 text-muted">

Chưa có bài viết nào

</td>

</tr>

@endforelse

</tbody>

</table>

</div>

</div>

</div>


<div class="mt-3">

{{ $blogs->links() }}

</div>

@endsection


@push('scripts')

<script>

document.querySelectorAll('.btn-toggle').forEach(btn => {

btn.addEventListener('click', function(){

let form = this.closest('form');

Swal.fire({
title: 'Bạn muốn thay đổi trạng thái bài viết?',
text: "Bài viết sẽ được ẩn hoặc hiển thị",
icon: 'question',
showCancelButton: true,
confirmButtonColor: '#3085d6',
cancelButtonColor: '#6c757d',
confirmButtonText: 'Xác nhận',
cancelButtonText: 'Hủy'
}).then((result) => {

if (result.isConfirmed) {

form.submit();

}

});

});

});

</script>

@endpush