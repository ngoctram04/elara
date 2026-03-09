@extends('layouts.frontend')

@section('title',$blog->title)

@section('content')

@php
use Illuminate\Support\Str;
@endphp

<div class="container py-3">


{{-- Breadcrumb --}}
<nav class="breadcrumb-custom mb-3">

<a href="{{ url('/') }}">
<i class="bi bi-house"></i> Trang chủ
</a>

<span class="mx-2">›</span>

<a href="{{ route('blogs.index') }}">
Tin tức
</a>

<span class="mx-2">›</span>

<span class="current">
{{ Str::limit($blog->title,50) }}
</span>

</nav>



<div class="row justify-content-center">

<div class="col-lg-8">


{{-- Tiêu đề --}}
<h1 class="blog-title mb-3">

{{ $blog->title }}

</h1>



{{-- meta --}}
<div class="blog-meta mb-4">

<span>

<i class="bi bi-person"></i>
Admin

</span>

<span class="mx-3">

<i class="bi bi-calendar"></i>
{{ $blog->created_at->format('d/m/Y') }}

</span>

<span>

<i class="bi bi-eye"></i>
{{ $blog->views ?? 0 }} lượt xem

</span>

</div>



{{-- Nội dung bài viết --}}
<div class="blog-content">

{!! $blog->content !!}

</div>



</div>

</div>

</div>



<style>

/* breadcrumb */

.breadcrumb-custom{
font-size:15px;
}

.breadcrumb-custom a{
color:#1a73e8;
text-decoration:none;
font-weight:500;
}

.breadcrumb-custom a:hover{
text-decoration:underline;
}

.breadcrumb-custom .current{
color:#000;
font-weight:600;
}



/* title */

.blog-title{
font-size:28px;
font-weight:700;
line-height:1.4;
}



/* meta */

.blog-meta{
font-size:14px;
color:#777;
}

.blog-meta i{
margin-right:4px;
}



/* content */

.blog-content{
font-size:16px;
line-height:1.8;
color:#333;
}

.blog-content h1,
.blog-content h2,
.blog-content h3{
margin-top:28px;
margin-bottom:14px;
font-weight:600;
}

.blog-content p{
margin-bottom:14px;
}



/* ảnh trong nội dung */

.blog-content img{
max-width:100%;
border-radius:8px;
margin:20px 0;
}



/* video */

.blog-content video{
max-width:100%;
border-radius:8px;
margin:20px 0;
}



/* bảng */

.blog-content table{
width:100%;
border-collapse:collapse;
margin:20px 0;
}

.blog-content table td,
.blog-content table th{
border:1px solid #ddd;
padding:8px;
}

</style>

@endsection