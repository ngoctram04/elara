@extends('layouts.frontend')

@section('content')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => url('/')],
    ['label' => 'Chat với nhân viên']
]" />
<div class="container">

<div class="chat-wrapper">
    <div id="chat-box"></div>

    <div id="image-preview" class="preview-box" style="display:none;">
        <img id="preview-img">
        <span onclick="removePreview()">
            <i class="bi bi-x-lg"></i>
        </span>
    </div>

    <div class="chat-input">

        <label class="file-btn">
            <i class="bi bi-image"></i>
            <input type="file" id="file" accept="image/*">
        </label>

        <input type="text"
               id="message"
               placeholder="Nhập tin nhắn...">

        <button onclick="sendMessage()">
            <i class="bi bi-send"></i>
        </button>

    </div>

</div>

</div>


<style>

.chat-wrapper{
max-width:800px;
margin:auto;
height:520px;
background:white;
border-radius:14px;
border:1px solid #e5e7eb;
display:flex;
flex-direction:column;
overflow:hidden;
}

#chat-box{
flex:1;
overflow-y:auto;
padding:20px;
background:#f4f6fb;
display:flex;
flex-direction:column;
gap:12px;
}

.message{
display:flex;
gap:8px;
max-width:70%;
}

.message-user{
align-self:flex-end;
flex-direction:row-reverse;
}

.message-admin{
align-self:flex-start;
}

.avatar{
width:34px;
height:34px;
border-radius:50%;
background:#ddd;
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
line-height:1.4;
word-break:break-word;
}

.message-user .bubble{
background:#2563eb;
color:white;
border-bottom-right-radius:4px;
}

.message-admin .bubble{
background:#e5e7eb;
border-bottom-left-radius:4px;
}

.time{
font-size:11px;
color:#999;
margin-top:2px;
}

.bubble img{
max-width:220px;
border-radius:8px;
margin-top:5px;
cursor:pointer;
}

.chat-input{
display:flex;
align-items:center;
gap:6px;
padding:10px;
border-top:1px solid #eee;
background:white;
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
padding:8px 14px;
border-radius:20px;
cursor:pointer;
display:flex;
align-items:center;
justify-content:center;
}

.file-btn{
cursor:pointer;
font-size:20px;
color:#6b7280;
display:flex;
align-items:center;
}

.file-btn:hover{
color:#2563eb;
}

.file-btn input{
display:none;
}

.preview-box{
padding:8px 20px;
background:#f9fafb;
border-top:1px solid #eee;
display:flex;
align-items:center;
gap:10px;
}

.preview-box img{
height:60px;
border-radius:6px;
}

.preview-box span{
cursor:pointer;
font-size:16px;
color:#dc3545;
display:flex;
align-items:center;
}

#chat-box::-webkit-scrollbar{
width:6px;
}

#chat-box::-webkit-scrollbar-thumb{
background:#ccc;
border-radius:3px;
}

</style>


<script>

function scrollBottom(){
const box=document.getElementById('chat-box');
box.scrollTop=box.scrollHeight;
}

function loadMessages(){

fetch('/chat/messages')
.then(res=>res.json())
.then(data=>{

let html='';
let lastDate='';

data.forEach(msg=>{

let isUser = msg.sender_id == {{ auth()->id() }};


if(msg.date !== lastDate){

html+=`
<div class="chat-date">
----- ${msg.date} -----
</div>
`;

lastDate = msg.date;

}

html+=`
<div class="message ${isUser?'message-user':'message-admin'}">

<div class="avatar">
${isUser?'U':'A'}
</div>

<div class="message-content">

<div class="sender">
${isUser ? 'Bạn' : (msg.sender_name ?? 'Admin')}
</div>

<div class="bubble">
`;



if(msg.message){

if(msg.message.match(/\.(jpg|jpeg|png|webp|gif)$/i)){

html+=`<a href="${msg.message}" target="_blank">
<img src="${msg.message}">
</a>`;

}else{

html+=msg.message;

}

}

html+=`
</div>

<div class="time">
${msg.time ?? ''}
</div>

</div>

</div>
`;

});

document.getElementById('chat-box').innerHTML=html;

scrollBottom();

});

}



document.getElementById("file").addEventListener("change",function(){

let file=this.files[0];

if(!file) return;

let reader=new FileReader();

reader.onload=function(e){

document.getElementById("preview-img").src=e.target.result;
document.getElementById("image-preview").style.display="flex";

};

reader.readAsDataURL(file);

});


function removePreview(){

document.getElementById("file").value='';
document.getElementById("image-preview").style.display="none";

}



function sendMessage(){

let message=document.getElementById('message').value;
let file=document.getElementById('file').files[0];

let formData=new FormData();

formData.append('message',message);

if(file){
formData.append('file',file);
}

fetch('/chat/send',{

method:'POST',

headers:{
'X-CSRF-TOKEN':'{{ csrf_token() }}'
},

body:formData

})
.then(()=>{

document.getElementById('message').value='';
removePreview();

loadMessages();

});

}



document.getElementById("message")
.addEventListener("keypress",function(e){

if(e.key==="Enter"){
e.preventDefault();
sendMessage();
}

});


setInterval(loadMessages,2000);

loadMessages();

</script>

@endsection