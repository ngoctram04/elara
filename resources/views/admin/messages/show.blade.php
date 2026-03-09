@extends('layouts.admin')

@section('title','Chat khách hàng')

@section('content')

<h4 class="mb-3">
💬 Chat với {{ $conversation->user->name ?? 'Khách' }}
</h4>

<div class="card">

{{-- CHAT BODY --}}
<div id="chat-box" class="card-body chat-body">

@foreach($conversation->messages as $msg)

@php
$isAdmin = $msg->sender_id == auth()->id();
@endphp

<div class="message-row {{ $isAdmin ? 'admin' : 'user' }}">

<div class="avatar">
{{ $isAdmin ? 'A' : 'U' }}
</div>

<div class="message-content">

<div class="sender">
{{ $isAdmin ? 'Admin' : ($conversation->user->name ?? 'Khách') }}
</div>

<div class="bubble">

@if(preg_match('/\.(jpg|jpeg|png|webp|gif)$/i',$msg->message))
<a href="{{ $msg->message }}" target="_blank">
<img src="{{ $msg->message }}">
</a>
@else
{{ $msg->message }}
@endif

</div>

<div class="time">
{{ $msg->created_at->format('d/m/Y H:i') }}
</div>

</div>

</div>

@endforeach

</div>


{{-- INPUT --}}
<div class="card-footer">

<form method="POST"
action="{{ route('admin.messages.send',$conversation->id) }}"
enctype="multipart/form-data">

@csrf

<div class="chat-input">

<label class="file-btn">
📷
<input type="file" name="image" accept="image/*">
</label>

<input
type="text"
name="message"
placeholder="Nhập tin nhắn...">

<button type="submit">Gửi</button>

</div>

</form>

</div>

</div>



<style>

.chat-body{
height:450px;
overflow-y:auto;
background:#f4f6fb;
padding:20px;
display:flex;
flex-direction:column;
gap:12px;
}

.message-row{
display:flex;
gap:10px;
max-width:70%;
}

.message-row.admin{
margin-left:auto;
flex-direction:row-reverse;
}

.avatar{
width:34px;
height:34px;
border-radius:50%;
background:#d1d5db;
display:flex;
align-items:center;
justify-content:center;
font-size:13px;
}

.message-content{
display:flex;
flex-direction:column;
}

.sender{
font-size:12px;
color:#777;
margin-bottom:2px;
}

.bubble{
padding:10px 14px;
border-radius:14px;
font-size:14px;
word-break:break-word;
}

.message-row.user .bubble{
background:#e5e7eb;
border-bottom-left-radius:4px;
}

.message-row.admin .bubble{
background:#2563eb;
color:white;
border-bottom-right-radius:4px;
}

.bubble img{
max-width:220px;
border-radius:8px;
cursor:pointer;
}

.time{
font-size:11px;
color:#999;
margin-top:2px;
}

.chat-input{
display:flex;
gap:6px;
align-items:center;
}

.chat-input input[type="text"]{
flex:1;
border:1px solid #ddd;
border-radius:20px;
padding:8px 14px;
outline:none;
}

.chat-input button{
background:#2563eb;
border:none;
color:white;
padding:8px 16px;
border-radius:20px;
cursor:pointer;
}

.file-btn{
font-size:20px;
cursor:pointer;
}

.file-btn input{
display:none;
}

.chat-body::-webkit-scrollbar{
width:6px;
}

.chat-body::-webkit-scrollbar-thumb{
background:#ccc;
border-radius:3px;
}

</style>


<script>

window.onload=function(){

let box=document.getElementById("chat-box");
box.scrollTop=box.scrollHeight;

}

</script>

@endsection