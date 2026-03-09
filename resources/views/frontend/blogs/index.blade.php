@extends('layouts.frontend')

@section('title','Tin tức')

@section('content')

@php
use Illuminate\Support\Str;
@endphp

<div class="container py-2">


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
TẤT CẢ BÀI VIẾT
</span>

</nav>



{{-- Bộ lọc --}}
<div class="blog-filter mb-4">

<a href="?sort=new"
class="filter-btn {{ request('sort')=='new' || !request('sort') ? 'active' : '' }}">
Mới nhất
</a>

<a href="?sort=old"
class="filter-btn {{ request('sort')=='old' ? 'active' : '' }}">
Cũ nhất
</a>

<a href="?sort=views"
class="filter-btn {{ request('sort')=='views' ? 'active' : '' }}">
Phổ biến nhất
</a>

</div>



<div class="row g-4">

@forelse($blogs as $blog)

<div class="col-lg-4 col-md-6">

<div class="card blog-card h-100 border-0 shadow-sm">

{{-- Thumbnail --}}
<div class="blog-img">

@if($blog->thumbnail)

<img
src="{{ asset('storage/'.$blog->thumbnail) }}"
alt="{{ $blog->title }}"
class="card-img-top">

@else

<img
src="{{ asset('images/no-image.jpg') }}"
class="card-img-top">

@endif

</div>


<div class="card-body d-flex flex-column">

<h5 class="card-title mb-2">

<a
href="{{ route('blogs.show',$blog->slug) }}"
class="blog-title">

{{ $blog->title }}

</a>

</h5>



<p class="blog-meta">

<i class="bi bi-calendar"></i>
{{ $blog->created_at->format('d/m/Y') }}

<span class="mx-2">•</span>

<i class="bi bi-eye"></i>
{{ $blog->views ?? 0 }} lượt xem

</p>



<p class="card-text flex-grow-1">

{{ Str::limit($blog->excerpt ?? strip_tags($blog->content),120) }}

</p>



<a
href="{{ route('blogs.show',$blog->slug) }}"
class="btn-read">

Đọc tiếp →

</a>

</div>

</div>

</div>

@empty

<div class="col-12 text-center text-muted py-5">
Chưa có bài viết nào
</div>

@endforelse

</div>



<div class="mt-4">
{{ $blogs->links() }}
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



/* filter */

.blog-filter{
display:flex;
gap:10px;
flex-wrap:wrap;
}

.filter-btn{
padding:6px 14px;
border-radius:20px;
border:1px solid #ddd;
text-decoration:none;
color:#333;
font-size:14px;
transition:all .2s;
}

.filter-btn:hover{
background:#f5f5f5;
}

.filter-btn.active{
background:#000;
color:#fff;
border-color:#000;
}



/* blog card */

.blog-card{
border-radius:14px;
overflow:hidden;
transition:all .3s;
}

.blog-card:hover{
transform:translateY(-6px);
box-shadow:0 12px 28px rgba(0,0,0,0.1);
}



/* image */

.blog-img{
overflow:hidden;
}

.blog-img img{
height:220px;
width:100%;
object-fit:cover;
transition:transform .4s;
}

.blog-card:hover img{
transform:scale(1.06);
}



/* title */

.blog-title{
color:#222;
font-weight:600;
text-decoration:none;
line-height:1.4;
display:block;
}

.blog-title:hover{
color:#d63384;
}



/* meta */

.blog-meta{
font-size:13px;
color:#777;
margin-bottom:10px;
}



/* read more */

.btn-read{
text-decoration:none;
font-size:14px;
font-weight:500;
color:#000;
margin-top:auto;
}

.btn-read:hover{
color:#d63384;
}

</style>

@endsection