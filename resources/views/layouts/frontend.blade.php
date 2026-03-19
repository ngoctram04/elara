<!DOCTYPE html>

<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', 'ELARA')</title>

<meta name="csrf-token" content="{{ csrf_token() }}">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

@vite(['resources/js/app.js'])

@vite([
'resources/css/app.css',
'resources/css/frontend.css',
'resources/css/category.css',
'resources/css/product.css',
'resources/css/flash-sale.css',
'resources/css/profile.css',
])

<style>
/* badge tin nhắn chưa đọc */

.chat-badge{
position:absolute;
top:-4px;
right:-4px;
background:#dc3545;
color:white;
font-size:11px;
padding:3px 6px;
border-radius:50%;
font-weight:600;
line-height:1;
min-width:18px;
text-align:center;
}
/* floating buttons */

.floating-right{
position:fixed;
bottom:20px;
right:20px;
z-index:999;
}

.floating-left{
position:fixed;
bottom:20px;
left:20px;
z-index:999;
}

.float-btn{
width:55px;
height:55px;
border-radius:50%;
border:none;
color:white;
font-size:22px;
display:flex;
align-items:center;
justify-content:center;
}

.contact-btn{
background:#0d6efd;
}

.ai-btn{
background:#7ec8ea;
}

/* AI chat box */

.ai-chat-box{
position:fixed;
bottom:90px;
left:20px;
width:320px;
height:420px;
background:white;
border-radius:12px;
box-shadow:0 5px 20px rgba(0,0,0,0.2);
display:none;
flex-direction:column;
z-index:999;
}

.ai-header{
background:#7ec8ea;
color:white;
padding:10px;
display:flex;
justify-content:space-between;
align-items:center;
}

.ai-body{
flex:1;
padding:10px;
overflow-y:auto;
font-size:14px;
}

.ai-input{
display:flex;
border-top:1px solid #eee;
}

.ai-input input{
flex:1;
border:none;
padding:10px;
outline:none;
}

.ai-input button{
border:none;
background:#7ec8ea;
color:white;
padding:10px 15px;
}

</style>

@stack('styles')

</head>

<body>

<header class="header-box">
<div class="header-inner">

<a href="{{ route('home') }}" class="logo">ELARA</a>

<form class="search-pill position-relative" action="{{ route('shop') }}" method="GET">

<input
id="search-input"
type="text"
name="q"
value="{{ request('q') }}"
placeholder="Tìm kiếm sản phẩm..."
autocomplete="off"
>

<!-- NÚT MICRO -->
<button type="button" id="voice-btn" class="voice-btn">
<i class="bi bi-mic"></i>
</button>

<button type="submit">
<i class="bi bi-search"></i>
</button>

<div id="search-suggest-box" class="search-suggest-box"></div>

</form>
<div id="voice-popup" class="voice-popup">
    <div class="voice-box">

        <div class="voice-icon">
            <i class="bi bi-mic-fill"></i>
        </div>

        <div class="voice-text">
            Đang nghe...<br>
            Hãy nói tên sản phẩm
        </div>

    </div>
</div>
<div class="header-icons">

@auth

<div class="dropdown me-2">

    {{-- 🔔 ICON --}}
    <a href="#" class="icon-btn position-relative" data-bs-toggle="dropdown">

        <i class="bi bi-bell fs-5"></i>

        {{-- 🔴 BADGE --}}
        @php
            $unreadCount = auth()->user()->unreadNotifications()->count();
        @endphp

        @if($unreadCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif

    </a>

    {{-- 📦 DROPDOWN --}}
    <div class="dropdown-menu dropdown-menu-end p-0 shadow noti-dropdown">

        {{-- HEADER --}}
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
    <span class="fw-bold">Thông báo</span>

    <button id="markAllRead" class="btn btn-sm btn-light">
        ✓ Đã đọc tất cả
    </button>
</div>

        {{-- LIST --}}
        @php
            $notifications = auth()->user()
                ->notifications()
                ->latest()
                ->limit(100)
                ->get();
        @endphp

        @forelse($notifications as $noti)

            <a href="{{ route('notification.redirect', $noti->id) }}"
               class="dropdown-item noti-item d-flex gap-2 py-2 {{ is_null($noti->read_at) ? 'unread' : '' }}">

                {{-- ICON --}}
                <div class="noti-icon">
                    <i class="bi {{ $noti->data['icon'] ?? 'bi-bell' }} text-{{ $noti->data['color'] ?? 'secondary' }}"></i>
                </div>

                {{-- CONTENT --}}
                <div class="flex-grow-1">

                    <div class="noti-title">
                        {{ $noti->data['title'] ?? 'Thông báo' }}

                        {{-- 🔴 DOT --}}
                        @if(is_null($noti->read_at))
                            <span class="noti-dot"></span>
                        @endif
                    </div>

                    <div class="noti-message">
                        {{ $noti->data['message'] ?? '' }}
                    </div>

                    {{-- TIME --}}
                    <div class="noti-time">
                        {{ $noti->created_at->diffForHumans() }}
                    </div>

                </div>

            </a>

        @empty

            <div class="p-4 text-center text-muted">
                <i class="bi bi-bell-slash fs-4"></i><br>
                Không có thông báo
            </div>

        @endforelse

    </div>

</div>

@include('components.user-dropdown')

@else

<a href="{{ route('login') }}" class="icon-btn">
    <i class="bi bi-person"></i>
</a>

@endauth

<a href="{{ route('cart.index') }}" class="icon-btn">

<i class="bi bi-cart3"></i>

@php $cartCount = $cartCount ?? 0; @endphp

@if($cartCount > 0) <span class="cart-badge">
{{ $cartCount > 99 ? '99+' : $cartCount }} </span>
@endif

</a>

</div>
</div>
</header>

<nav class="nav-box">
<div class="nav-inner">

<div class="nav-category">

<a href="#" class="nav-category-trigger">
<i class="bi bi-list"></i> Danh mục 
</a>

@include('components.mega-menu')

</div>

<a href="{{ route('shop', ['sort' => 'newest']) }}">
Sản phẩm mới
</a>

<a href="{{ route('blogs.index') }}">
Tin tức
</a>
<a href="{{ route('policy') }}">
Chính sách
</a>
@auth <a href="{{ route('orders.history') }}">
Đơn hàng của tôi </a>
@endauth

</div>
</nav>

<main class="page-wrapper">
@yield('content')
</main>

<footer class="footer-box mt-4">
<div class="footer-inner">

<div class="row">

<!-- ELARA -->
<div class="col-lg-4 col-md-6 mb-4">
<h5 class="footer-title">ELARA</h5>

<p class="footer-desc">
    <span>ELARA là cửa hàng chuyên cung cấp</span><br>
    <span>các sản phẩm chất lượng cao với giá tốt</span><br>
    <span>và dịch vụ giao hàng nhanh chóng.</span>
</p>

<div class="footer-social">
<a href="#"><i class="bi bi-facebook"></i></a>
<a href="#"><i class="bi bi-instagram"></i></a>
<a href="#"><i class="bi bi-tiktok"></i></a>
</div>

</div>


<!-- LIÊN KẾT -->
<div class="col-lg-2 col-md-6 mb-4">

<h6 class="footer-title-sm">Liên kết</h6>

<ul class="footer-links">
<li><a href="/">Trang chủ</a></li>
<li><a href="/products">Sản phẩm</a></li>
<li><a href="/cart">Giỏ hàng</a></li>
<li><a href="/chat">Liên hệ</a></li>
</ul>

</div>


<!-- HỖ TRỢ -->
<div class="col-lg-3 col-md-6 mb-4">

<h6 class="footer-title-sm">Hỗ trợ</h6>

<ul class="footer-links">
<li><a href="#">Chính sách đổi trả</a></li>
<li><a href="#">Chính sách bảo mật</a></li>
<li><a href="#">Điều khoản dịch vụ</a></li>
<li><a href="#">Hướng dẫn mua hàng</a></li>
</ul>

</div>


<!-- LIÊN HỆ -->
<div class="col-lg-3 col-md-6 mb-4">

<h6 class="footer-title-sm">Liên hệ</h6>

<ul class="footer-info">
<li>
<i class="bi bi-geo-alt-fill"></i>
Vĩnh Long, Việt Nam
</li>

<li>
<i class="bi bi-telephone-fill"></i>
0954353423
</li>

<li>
<i class="bi bi-envelope-fill"></i>
elara.shop26@gmail.com
</li>
</ul>

</div>

</div>


<div class="footer-bottom">
© {{ date('Y') }} ELARA. All Rights Reserved.
</div>

</div>
</footer>

{{-- AI CHAT BUTTON --}}

<div class="floating-left">

<button class="float-btn ai-btn" onclick="toggleAIChat()">
<i class="bi bi-robot"></i>
</button>

</div>

{{-- CHAT NHÂN VIÊN --}}

<div class="floating-right">

<a href="/chat" class="float-btn contact-btn position-relative">

<i class="bi bi-chat-dots"></i>

<span id="chat-badge" class="chat-badge" style="display:none">
0
</span>

</a>

</div>

{{-- AI CHAT BOX --}}

<div id="ai-chat-box" class="ai-chat-box">

<div class="ai-header">

<span>AI tư vấn ELARA</span>

<button onclick="toggleAIChat()" class="btn-close btn-close-white"></button>

</div>

<div id="ai-messages" class="ai-body"></div>

<div class="ai-input">

<input type="text" id="ai-input" placeholder="Hỏi về mỹ phẩm...">

<button onclick="sendAI()">Gửi</button>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<x-toast />

@stack('scripts')


<script>

/* ==============================
   MỞ / ĐÓNG AI CHAT
============================== */

function toggleAIChat(){

let box = document.getElementById("ai-chat-box");
let chat = document.getElementById("ai-messages");

if(box.style.display === "flex"){

    box.style.display = "none";

}else{

    box.style.display = "flex";

    /* lời chào lần đầu */

    if(chat.innerHTML.trim() === ""){
        chat.innerHTML = `
        <div><b>AI:</b> Xin chào! Tôi là trợ lý ELARA. 
        Bạn cần tư vấn mỹ phẩm gì?</div>
        `;
    }

}

}


/* ==============================
   GỬI CÂU HỎI AI
============================== */

function sendAI(){

let input = document.getElementById("ai-input");
let msg = input.value.trim();

if(!msg) return;

let chat = document.getElementById("ai-messages");

/* hiển thị tin nhắn user */

chat.innerHTML += `<div><b>Bạn:</b> ${msg}</div>`;

input.value = "";

/* auto scroll */

chat.scrollTop = chat.scrollHeight;


/* loading AI */

let loading = document.createElement("div");
loading.innerHTML = "<b>AI:</b> Đang tư vấn...";
chat.appendChild(loading);

chat.scrollTop = chat.scrollHeight;


/* gửi request */

fetch('/ai-chat/send',{

method:'POST',

headers:{
'Content-Type':'application/json',
'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content
},

body:JSON.stringify({
message: msg
})

})
.then(res => res.json())
.then(data => {

loading.remove();

/* hiển thị trả lời AI */

chat.innerHTML += `<div class="ai-msg"><b>AI:</b> ${data.reply}</div>`;


/* auto scroll */

chat.scrollTop = chat.scrollHeight;

})
.catch(() => {

loading.innerHTML = "<b>AI:</b> Chatbot đang lỗi.";

});

}

/* ==============================
   CHAT NHÂN VIÊN - UNREAD BADGE
============================== */

@auth

function loadUnreadChat(){

fetch('{{ route("chat.unreadCount") }}')
.then(res => res.json())
.then(data => {

let badge = document.getElementById("chat-badge");

if(!badge) return;

if(data.count > 0){

badge.innerText = data.count;
badge.style.display = "block";

}else{

badge.style.display = "none";

}

});

}

/* load lần đầu */

loadUnreadChat();

/* refresh mỗi 5s */

setInterval(loadUnreadChat,5000);

@endauth
/* ==============================
SEARCH AUTOCOMPLETE
============================== */

const input = document.getElementById("search-input");
const box = document.getElementById("search-suggest-box");

if(input){

input.addEventListener("focus", loadHistory);

input.addEventListener("input", function(){

let q = this.value.trim();

if(q.length === 0){
loadHistory();
return;
}

fetch(`/search/suggest?q=${encodeURIComponent(q)}`)
.then(res => res.json())
.then(data => {

box.innerHTML = "";

if(data.length === 0){
box.style.display = "none";
return;
}

data.forEach(item => {

box.innerHTML += `
<div class="search-history-item suggest-item">
${item}
</div>
`;

});

box.style.display = "block";

});

});

}

/* ==============================
LOAD HISTORY
============================== */
function loadHistory(){

fetch("/search/history")
.then(res => res.json())
.then(data => {

box.innerHTML = "";

if(!data.length){
box.style.display = "none";
return;
}

/* lọc trùng */

[...new Set(data)].forEach(item => {

box.innerHTML += `
<div class="search-history-item history-row">

    <div class="history-left suggest-item">
        <i class="bi bi-clock"></i>
        <span>${item}</span>
    </div>

    <span class="delete-history" data-key="${item}">
        <i class="bi bi-x"></i>
    </span>

</div>
`;

});

box.style.display = "block";

});

}
/* ==============================
DELETE SEARCH HISTORY
============================== */

document.addEventListener("click", function(e){

const btn = e.target.closest(".delete-history");

if(!btn) return;

e.stopPropagation(); // tránh trigger suggest

const key = btn.dataset.key;

/* lấy dòng cần xóa */

const row = btn.closest(".history-row");

if(row){

/* animation nhẹ */

row.style.transition = "all .2s ease";
row.style.opacity = "0";
row.style.transform = "translateX(10px)";

setTimeout(()=>{
row.remove();
},200);

}

/* gọi API xóa */

fetch("/search/history/delete",{
method:"POST",
headers:{
"Content-Type":"application/json",
"X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').content
},
body:JSON.stringify({ keyword:key })
});

});
/* ==============================
VOICE SEARCH
============================== */

const voiceBtn = document.getElementById("voice-btn");
const voicePopup = document.getElementById("voice-popup");

if(voiceBtn){

voiceBtn.addEventListener("click", () => {

const SpeechRecognition =
window.SpeechRecognition || window.webkitSpeechRecognition;

if(!SpeechRecognition){
alert("Trình duyệt không hỗ trợ tìm kiếm giọng nói");
return;
}

const recognition = new SpeechRecognition();

recognition.lang = "vi-VN";
recognition.interimResults = false;

recognition.start();

/* hiện popup */

voicePopup.style.display = "flex";

/* đổi icon */

voiceBtn.innerHTML = '<i class="bi bi-mic-fill text-danger"></i>';

/* tự ngắt sau 5s */

let silenceTimer = setTimeout(()=>{
recognition.stop();
},5000);


recognition.onresult = function(event){

clearTimeout(silenceTimer);

let text = event.results[0][0].transcript;

input.value = text;

voicePopup.style.display = "none";

input.form.submit();

};


recognition.onend = function(){

voicePopup.style.display = "none";

voiceBtn.innerHTML = '<i class="bi bi-mic"></i>';

};

});

}
/* ==============================
CLICK SUGGEST ITEM
============================== */

document.addEventListener("click", function(e){

const item = e.target.closest(".suggest-item");

if(item){

const keyword = item.innerText.trim();

input.value = keyword;

input.form.submit();

return;

}

if(!e.target.closest(".search-pill")){
box.style.display = "none";
}
const markAllBtn = document.getElementById("markAllRead");

if(markAllBtn){
    markAllBtn.addEventListener("click", function(){

        fetch("{{ route('notifications.markAllRead') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                location.reload();
            }
        });

    });
}
});
</script>
</body>
</html>
