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

<div class="container pb-4">
    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">

            <article class="blog-detail-card">

                @if($blog->thumbnail)
                    <div class="blog-detail-thumb">
                        <img
                            src="{{ asset('storage/' . $blog->thumbnail) }}"
                            alt="{{ $blog->title }}">
                    </div>
                @endif

                <div class="blog-detail-body">
                    <h1 class="blog-title mb-3">
                        {{ $blog->title }}
                    </h1>

                    <div class="blog-meta mb-4">
                        <span class="meta-item">
                            <i class="bi bi-person-circle"></i>
                            Elara
                        </span>

                        <span class="meta-divider"></span>

                        <span class="meta-item">
                            <i class="bi bi-calendar3"></i>
                            {{ $blog->created_at->format('d/m/Y') }}
                        </span>

                        <span class="meta-divider"></span>

                        <span class="meta-item">
                            <i class="bi bi-eye"></i>
                            {{ $blog->views ?? 0 }} lượt xem
                        </span>
                    </div>

                    <div class="blog-content">
                        {!! html_entity_decode($blog->content, ENT_QUOTES | ENT_HTML5, 'UTF-8') !!}
                    </div>
                </div>
            </article>

        </div>
    </div>
</div>

<style>
    .blog-detail-card {
        background: #fff;
        border-radius: 22px;
        overflow: hidden;
        box-shadow: 0 12px 35px rgba(15, 23, 42, 0.08);
        border: 1px solid #eef2f7;
    }

    .blog-detail-thumb {
        width: 100%;
        background: #f8f9fa;
    }

    .blog-detail-thumb img {
        width: 100%;
        max-height: 440px;
        object-fit: cover;
        display: block;
    }

    .blog-detail-body {
        padding: 32px;
    }

    .blog-title {
        font-size: 34px;
        font-weight: 800;
        line-height: 1.35;
        color: #111827;
        margin: 0;
    }

    .blog-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        color: #6b7280;
        font-size: 14px;
        padding-bottom: 18px;
        border-bottom: 1px solid #eef2f7;
    }

    .meta-item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .meta-item i {
        color: #0d6efd;
    }

    .meta-divider {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: #d1d5db;
    }

    .blog-content {
        color: #374151;
        font-size: 16px;
        line-height: 1.9;
        word-break: break-word;
    }

    .blog-content h1,
    .blog-content h2,
    .blog-content h3,
    .blog-content h4,
    .blog-content h5,
    .blog-content h6 {
        color: #111827;
        font-weight: 700;
        line-height: 1.45;
        margin-top: 28px;
        margin-bottom: 14px;
    }

    .blog-content h1 {
        font-size: 30px;
    }

    .blog-content h2 {
        font-size: 26px;
    }

    .blog-content h3 {
        font-size: 22px;
    }

    .blog-content p {
        margin-bottom: 16px;
    }

    .blog-content ul,
    .blog-content ol {
        margin-bottom: 18px;
        padding-left: 22px;
    }

    .blog-content li {
        margin-bottom: 8px;
    }

    .blog-content a {
        color: #0d6efd;
        text-decoration: none;
        font-weight: 500;
    }

    .blog-content a:hover {
        color: #0b5ed7;
        text-decoration: underline;
    }

    .blog-content blockquote {
        margin: 24px 0;
        padding: 18px 20px;
        border-left: 4px solid #0d6efd;
        background: #eef5ff;
        border-radius: 10px;
        color: #4b5563;
        font-style: italic;
    }

    .blog-content img {
        max-width: 100%;
        height: auto;
        display: block;
        border-radius: 14px;
        margin: 22px auto;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    }

    .blog-content video,
    .blog-content iframe {
        max-width: 100%;
        width: 100%;
        border-radius: 14px;
        margin: 22px 0;
    }

    .blog-content table {
        width: 100%;
        border-collapse: collapse;
        margin: 24px 0;
        overflow: hidden;
        border-radius: 12px;
        background: #fff;
    }

    .blog-content table th,
    .blog-content table td {
        border: 1px solid #e5e7eb;
        padding: 12px 14px;
        text-align: left;
    }

    .blog-content table th {
        background: #f8fbff;
        color: #111827;
        font-weight: 700;
    }

    .blog-content hr {
        margin: 28px 0;
        border: 0;
        border-top: 1px solid #e5e7eb;
    }

    @media (max-width: 991.98px) {
        .blog-detail-body {
            padding: 24px;
        }

        .blog-title {
            font-size: 28px;
        }

        .blog-detail-thumb img {
            max-height: 360px;
        }
    }

    @media (max-width: 767.98px) {
        .blog-detail-card {
            border-radius: 16px;
        }

        .blog-detail-body {
            padding: 18px;
        }

        .blog-title {
            font-size: 24px;
        }

        .blog-meta {
            font-size: 13px;
            gap: 8px;
        }

        .meta-divider {
            display: none;
        }

        .blog-content {
            font-size: 15px;
            line-height: 1.8;
        }

        .blog-content h1 {
            font-size: 24px;
        }

        .blog-content h2 {
            font-size: 22px;
        }

        .blog-content h3 {
            font-size: 20px;
        }

        .blog-detail-thumb img {
            max-height: 260px;
        }
    }
</style>
@endsection