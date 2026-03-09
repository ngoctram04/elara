@extends('layouts.admin')

@section('title','Tin nhắn khách hàng')

@section('content')

<h4 class="mb-3">Tin nhắn khách hàng</h4>

<div class="card">
<div class="card-body">

<table class="table table-bordered align-middle">

<thead>
<tr>
<th>ID</th>
<th>Khách hàng</th>
<th>Thời gian</th>
<th></th>
</tr>
</thead>

<tbody>

@forelse($conversations as $c)

<tr>

<td>{{ $c->id }}</td>

<td>
{{ $c->user->name ?? 'Khách' }}
</td>

<td>
{{ $c->updated_at->diffForHumans() }}
</td>

<td>

<a href="{{ route('admin.messages.show',$c->id) }}"
class="btn btn-sm btn-primary">

Xem chat

</a>

</td>

</tr>

@empty

<tr>
<td colspan="4" class="text-center text-muted">
Chưa có cuộc trò chuyện
</td>
</tr>

@endforelse

</tbody>

</table>

</div>
</div>

@endsection