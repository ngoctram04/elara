@extends('layouts.admin')

@section('title','Chat khách hàng')

@section('content')

<div class="card border-0 shadow-sm">

<div class="card-body">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h5 class="fw-bold mb-1">
<i class="bi bi-chat-dots"></i>
Chat với {{ $conversation->user->name ?? 'Khách' }}
</h5>

<small class="text-muted">
Trao đổi tin nhắn với khách hàng
</small>
</div>

</div>

<div class="chat-wrapper">


<div id="chat-box" class="chat-body">

@foreach($conversation->messages as $msg)

@php
$isAdmin = $msg->sender_id == auth()->id();
@endphp

<div class="message-row {{ $isAdmin ? 'admin' : 'user' }}">

<div class="avatar">

<i class="bi {{ $isAdmin ? 'bi-person-badge' : 'bi-person' }}"></i>

</div>

<div class="message-content">

<div class="sender">

{{ $isAdmin ? 'Admin' : ($conversation->user->name ?? 'Khách') }}

</div>

<div class="bubble">

@if(preg_match('/.(jpg|jpeg|png|webp|gif)$/i',$msg->message))

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



<div class="chat-footer">

<form method="POST"
action="{{ route('admin.messages.send',$conversation->id) }}"
enctype="multipart/form-data">

@csrf

<div class="chat-input">

<label class="file-btn">
<i class="bi bi-image"></i>
<input type="file" name="image" id="imageInput" accept="image/*">
</label>

<div id="imagePreview" class="image-preview" style="display:none">
<img id="previewImg">
<button type="button" id="removeImage">
<i class="bi bi-x-lg"></i>
</button>
</div>

<input
type="text"
name="message"
placeholder="Nhập tin nhắn...">

<button type="submit">

<i class="bi bi-send-fill"></i>

</button>

</div>

</form>

</div>

</div>

</div>

</div>

<style>
.image-preview{
position:relative;
margin-bottom:8px;
}

.image-preview img{
max-width:120px;
border-radius:8px;
border:1px solid #ddd;
}

.image-preview button{
position:absolute;
top:-6px;
right:-6px;
background:#dc3545;
border:none;
color:white;
border-radius:50%;
width:22px;
height:22px;
font-size:12px;
display:flex;
align-items:center;
justify-content:center;
cursor:pointer;
}
.chat-wrapper{
border:1px solid #eee;
border-radius:10px;
overflow:hidden;
}


.chat-body{
height:480px;
overflow-y:auto;
background:#f7f8fc;
padding:20px;
display:flex;
flex-direction:column;
gap:14px;
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
width:36px;
height:36px;
border-radius:50%;
background:#e5e7eb;
display:flex;
align-items:center;
justify-content:center;
font-size:16px;
color:#555;
}


.message-content{
display:flex;
flex-direction:column;
}


.sender{
font-size:12px;
color:#777;
margin-bottom:3px;
}


.bubble{
padding:10px 14px;
border-radius:14px;
font-size:14px;
word-break:break-word;
line-height:1.4;
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

.chat-footer{
border-top:1px solid #eee;
background:white;
padding:10px 15px;
}


.chat-input{
display:flex;
gap:8px;
align-items:center;
}

.chat-input input[type="text"]{
flex:1;
border:1px solid #ddd;
border-radius:20px;
padding:8px 14px;
outline:none;
font-size:14px;
}


.chat-input button{
background:#2563eb;
border:none;
color:white;
padding:8px 14px;
border-radius:20px;
cursor:pointer;
display:flex;
align-items:center;
justify-content:center;
}


.file-btn{
font-size:20px;
cursor:pointer;
color:#555;
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
const input = document.getElementById("imageInput");
const previewBox = document.getElementById("imagePreview");
const previewImg = document.getElementById("previewImg");
const removeBtn = document.getElementById("removeImage");

input.addEventListener("change", function(){

const file = this.files[0];

if(file){

const reader = new FileReader();

reader.onload = function(e){

previewImg.src = e.target.result;
previewBox.style.display = "inline-block";

}

reader.readAsDataURL(file);

}

});

removeBtn.addEventListener("click", function(){

input.value = "";
previewImg.src = "";
previewBox.style.display = "none";

});
</script>

@endsection