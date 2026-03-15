@extends('layouts.admin')

@section('title','Tin nhắn khách hàng')

@section('content')

<style>

.filter-bar{
background:#fafafa;
border:1px solid #eee;
border-radius:8px;
padding:12px;
}

.unread-row{
background:#fff8e1;
}

.unread-badge{
font-size:11px;
}

.last-message{
font-size:13px;
color:#666;
}

</style>

<div class="card border-0 shadow-sm">
<div class="card-body">

{{-- HEADER --}}

<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h5 class="fw-bold mb-1">
<i class="bi bi-chat-dots"></i>
Tin nhắn khách hàng
</h5>

<small class="text-muted">
Danh sách các cuộc trò chuyện của khách hàng
</small>
</div>

</div>


{{-- FILTER --}}

<div class="filter-bar mb-3">

<form method="GET" class="row g-2">

<div class="col-md-6">

<input
type="text"
name="keyword"
class="form-control form-control-sm"
placeholder="Tìm khách hàng..."
value="{{ request('keyword') }}">

</div>

<div class="col-md-3">

<select name="status" class="form-select form-select-sm">

<option value="">Tất cả</option>

<option value="unread"
{{ request('status')=='unread'?'selected':'' }}>
Chưa đọc
</option>

<option value="read"
{{ request('status')=='read'?'selected':'' }}>
Đã đọc
</option>

</select>

</div>

<div class="col-md-3 d-flex gap-2">

<button class="btn btn-primary btn-sm">

<i class="bi bi-search"></i>
Lọc

</button>

<a href="{{ route('admin.messages.index') }}"
class="btn btn-secondary btn-sm">

<i class="bi bi-arrow-clockwise"></i>

</a>

</div>

</form>

</div>



<div class="table-responsive">

<table class="table table-hover align-middle mb-0">

<thead class="table-light">

<tr>

<th style="width:80px">STT</th>

<th>Khách hàng</th>

<th style="width:200px">Tin nhắn cuối</th>

<th style="width:160px">Thời gian</th>

<th style="width:140px">Hành động</th>

</tr>

</thead>

<tbody>

@forelse($conversations as $c)

@php

$unread = $c->messages
->where('sender_id','!=',auth()->id())
->where('is_read',0)
->count();

$lastMessage = $c->messages->last();

@endphp


<tr class="{{ $unread ? 'unread-row' : '' }}">

<td class="fw-semibold text-muted">

{{ ($conversations->currentPage() - 1) * $conversations->perPage() + $loop->iteration }}

</td>


<td>

<strong>

{{ $c->user->name ?? 'Khách' }}

</strong>

@if($unread)

<span class="badge bg-danger unread-badge ms-1">

{{ $unread }}

</span>

@endif

</td>


<td class="last-message">

@if($lastMessage)

{{ Str::limit($lastMessage->message,40) }}

@endif

</td>


<td class="text-muted">

{{ $c->updated_at->diffForHumans() }}

</td>


<td>

<a href="{{ route('admin.messages.show',$c->id) }}"
class="btn btn-sm btn-outline-primary">

<i class="bi bi-chat-dots"></i>
Xem chat

</a>

</td>

</tr>

@empty

<tr>

<td colspan="5"
class="text-center py-5 text-muted">

<i class="bi bi-chat-left-text fs-3"></i>

<div class="mt-2">

Chưa có cuộc trò chuyện

</div>

</td>

</tr>

@endforelse

</tbody>

</table>

</div>


{{-- PAGINATION --}}

@if($conversations->hasPages())

<div class="mt-4">

{{ $conversations->withQueryString()->links('pagination::bootstrap-5') }}

</div>

@endif


</div>
</div>

@endsection