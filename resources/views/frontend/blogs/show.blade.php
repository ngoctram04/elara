@extends('layouts.frontend')

@section('title', $blog->title)

@section('content')
@php
    use Illuminate\Support\Str;
@endphp

<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => url('/')],
    ['label' => 'Tin tức', 'url' => route('blogs.index')],
    ['label' => Str::limit($blog->title, 55)]
]" />

<div class="container pb-4 blog-detail-page">
    <div class="blog-detail-wrap">
        <article class="blog-detail-card">

            @if($blog->thumbnail)
                <div class="blog-detail-thumb">
                    <img
                        src="{{ asset('storage/' . $blog->thumbnail) }}"
                        alt="{{ $blog->title }}">
                </div>
            @endif

            <div class="blog-detail-body">
                <h1 class="blog-detail-title mb-3">
                    {{ $blog->title }}
                </h1>

                <div class="blog-detail-meta mb-4">
                    <span class="meta-item">
                        <i class="bi bi-person-circle"></i>
                        ELARA Cosmetics
                    </span>

                    <span class="meta-divider"></span>

                    <span class="meta-item">
                        <i class="bi bi-calendar3"></i>
                        {{ \Carbon\Carbon::parse($blog->created_at)->format('d/m/Y') }}
                    </span>

                    <span class="meta-divider"></span>

                    <span class="meta-item">
                        <i class="bi bi-eye"></i>
                        {{ $blog->views ?? 0 }} lượt xem
                    </span>
                </div>

                <div class="blog-detail-content">
                    {!! html_entity_decode($blog->content, ENT_QUOTES | ENT_HTML5, 'UTF-8') !!}
                </div>
            </div>
        </article>
    </div>
</div>
@endsection