@extends('layouts.admin')

@section('title','Tin nhắn khách hàng')

@section('content')

<div class="card border-0 shadow-sm">
<div class="card-body">

{{-- HEADER --}}

<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h5 class="fw-bold mb-1">
Tin nhắn khách hàng
</h5>

<small class="text-muted">
Danh sách các cuộc trò chuyện của khách hàng
</small>
</div>

</div>

<div class="table-responsive">

<table class="table table-hover align-middle mb-0">

<thead class="table-light">

<tr>

<th style="width:80px">
ID
</th>

<th>
Khách hàng
</th>

<th style="width:200px">
Thời gian
</th>

<th style="width:140px">
Hành động
</th>

</tr>

</thead>

<tbody>

@forelse($conversations as $c)

<tr>

<td class="text-muted fw-semibold">

#{{ $c->id }}

</td>

<td>

<strong>
{{ $c->user->name ?? 'Khách' }}
</strong>

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

<td colspan="4"
class="text-center py-4 text-muted">

<i class="bi bi-chat-left-text"></i>

<br>

Chưa có cuộc trò chuyện

</td>

</tr>

@endforelse

</tbody>

</table>

</div>

{{-- PAGINATION --}}
@if($conversations->hasPages())

<div class="mt-4">

{{ $conversations->links('pagination::bootstrap-5') }}

</div>

@endif

</div>
</div>

@endsection
