@extends('layouts.frontend')

@section('title', 'Tin tức')

@section('content')
@php
    use Illuminate\Support\Str;
@endphp

<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => url('/')],
    ['label' => 'Tin tức', 'url' => route('blogs.index')],
    ['label' => 'Tất cả bài viết']
]" />

<div class="container pb-5 blog-page">
    @if($latestBlog || $discoverBlogs->count())
        <section class="blog-highlight-layout mb-5">
            <div class="row g-4 align-items-stretch">

                {{-- CỘT TRÁI: BÀI MỚI THÊM / MỚI CẬP NHẬT --}}
                <div class="col-lg-7">
                    @if($latestBlog)
                        @php
                            $rawExcerpt = $latestBlog->excerpt ?: strip_tags($latestBlog->content ?? '');
                            $cleanExcerpt = html_entity_decode($rawExcerpt, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        @endphp

                        <article class="main-blog-card h-100">
                            <a href="{{ route('blogs.show', $latestBlog->slug) }}" class="main-blog-image">
                                @if($latestBlog->thumbnail)
                                    <img src="{{ asset('storage/' . $latestBlog->thumbnail) }}" alt="{{ $latestBlog->title }}">
                                @else
                                    <img src="{{ asset('images/no-image.jpg') }}" alt="{{ $latestBlog->title }}">
                                @endif
                            </a>

                            <div class="main-blog-content">
                                <div class="main-blog-badge">
                                    <i class="bi bi-clock-history me-1"></i>
                                    Mới cập nhật
                                </div>

                                <h2 class="main-blog-title">
                                    <a href="{{ route('blogs.show', $latestBlog->slug) }}">
                                        {{ $latestBlog->title }}
                                    </a>
                                </h2>

                                <div class="blog-meta mb-3">
                                    <span>
                                        <i class="bi bi-calendar3 me-1"></i>
                                        {{ $latestBlog->created_at->format('d.m.Y') }}
                                    </span>
                                    <span class="meta-divider">/</span>
                                    <span>
                                        <i class="bi bi-pencil-square me-1"></i>
                                        {{ optional($latestBlog->updated_at)->format('d.m.Y') }}
                                    </span>
                                    <span class="meta-divider">/</span>
                                    <span>
                                        <i class="bi bi-eye me-1"></i>
                                        {{ $latestBlog->views ?? 0 }} lượt xem
                                    </span>
                                </div>

                                <p class="main-blog-excerpt">
                                    {{ Str::limit(trim($cleanExcerpt), 150) }}
                                </p>

                                <a href="{{ route('blogs.show', $latestBlog->slug) }}" class="btn-read">
                                    Đọc tiếp
                                    <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </article>
                    @endif
                </div>

                {{-- CỘT PHẢI: KHÁM PHÁ THÊM --}}
                <div class="col-lg-5">
                    <div class="side-blogs-wrapper">
                        <div class="side-blogs-header">
                            <h3>Khám phá thêm</h3>
                        </div>

                        <div class="side-blog-scroll">
                            @forelse($discoverBlogs as $blog)
                                @php
                                    $rawExcerpt = $blog->excerpt ?: strip_tags($blog->content ?? '');
                                    $cleanExcerpt = html_entity_decode($rawExcerpt, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                @endphp

                                <article class="side-blog-card">
                                    <a href="{{ route('blogs.show', $blog->slug) }}" class="side-blog-thumb">
                                        @if($blog->thumbnail)
                                            <img src="{{ asset('storage/' . $blog->thumbnail) }}" alt="{{ $blog->title }}">
                                        @else
                                            <img src="{{ asset('images/no-image.jpg') }}" alt="{{ $blog->title }}">
                                        @endif
                                    </a>

                                    <div class="side-blog-content">
                                        <h3 class="side-blog-title">
                                            <a href="{{ route('blogs.show', $blog->slug) }}">
                                                {{ $blog->title }}
                                            </a>
                                        </h3>

                                        <div class="blog-meta mb-2">
                                            <span>
                                                <i class="bi bi-calendar3 me-1"></i>
                                                {{ $blog->created_at->format('d.m.Y') }}
                                            </span>
                                            <span class="meta-divider">/</span>
                                            <span>
                                                <i class="bi bi-eye me-1"></i>
                                                {{ $blog->views ?? 0 }} lượt xem
                                            </span>
                                        </div>

                                        <p class="side-blog-excerpt">
                                            {{ Str::limit(trim($cleanExcerpt), 58) }}
                                        </p>
                                    </div>
                                </article>
                            @empty
                                <div class="empty-side-box">
                                    Chưa có bài viết để hiển thị.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- PHẦN DƯỚI: BÀI VIẾT VỪA XEM --}}
    @if($recentViewedBlogs->count())
        <section class="blog-grid-section">
            <div class="section-heading mb-4">
                <h2>Bài viết vừa xem</h2>
                <span></span>
            </div>

            <div class="row g-4">
                @foreach($recentViewedBlogs as $blog)
                    @php
                        $rawExcerpt = $blog->excerpt ?: strip_tags($blog->content ?? '');
                        $cleanExcerpt = html_entity_decode($rawExcerpt, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    @endphp

                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <article class="blog-card h-100 border-0">
                            <div class="blog-img">
                                <a href="{{ route('blogs.show', $blog->slug) }}">
                                    @if($blog->thumbnail)
                                        <img src="{{ asset('storage/' . $blog->thumbnail) }}" alt="{{ $blog->title }}" class="card-img-top">
                                    @else
                                        <img src="{{ asset('images/no-image.jpg') }}" alt="{{ $blog->title }}" class="card-img-top">
                                    @endif
                                </a>
                            </div>

                            <div class="card-body d-flex flex-column p-4">
                                <div class="blog-meta mb-2">
                                    <span>
                                        <i class="bi bi-calendar3 me-1"></i>
                                        {{ $blog->created_at->format('d/m/Y') }}
                                    </span>

                                    <span class="meta-dot">•</span>

                                    <span>
                                        <i class="bi bi-eye me-1"></i>
                                        {{ $blog->views ?? 0 }} lượt xem
                                    </span>
                                </div>

                                <h5 class="card-title mb-3">
                                    <a href="{{ route('blogs.show', $blog->slug) }}" class="blog-title">
                                        {{ $blog->title }}
                                    </a>
                                </h5>

                                <p class="card-text blog-excerpt flex-grow-1 mb-3">
                                    {{ Str::limit(trim($cleanExcerpt), 95) }}
                                </p>

                                <a href="{{ route('blogs.show', $blog->slug) }}" class="btn-read">
                                    Đọc tiếp
                                    <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if(!$latestBlog && !$discoverBlogs->count() && !$recentViewedBlogs->count())
        <div class="empty-blog-box text-center">
            <div class="empty-icon mb-3">
                <i class="bi bi-journal-x"></i>
            </div>
            <h5 class="mb-2">Chưa có bài viết nào</h5>
            <p class="text-muted mb-0">
                Hiện tại chưa có nội dung để hiển thị. Vui lòng quay lại sau.
            </p>
        </div>
    @endif
</div>
@endsection