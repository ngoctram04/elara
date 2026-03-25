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

<div class="container pb-4">

    {{-- Bộ lọc --}}
    <div class="blog-filter-wrap mb-4">
        <div class="blog-filter">
            <a href="?sort=new"
               class="filter-btn {{ request('sort') == 'new' || !request('sort') ? 'active' : '' }}">
                <i class="bi bi-stars me-1"></i>Mới nhất
            </a>

            <a href="?sort=old"
               class="filter-btn {{ request('sort') == 'old' ? 'active' : '' }}">
                <i class="bi bi-clock-history me-1"></i>Cũ nhất
            </a>

            <a href="?sort=views"
               class="filter-btn {{ request('sort') == 'views' ? 'active' : '' }}">
                <i class="bi bi-fire me-1"></i>Phổ biến nhất
            </a>
        </div>
    </div>

    {{-- Danh sách bài viết --}}
    <div class="row g-4">
        @forelse($blogs as $blog)
            @php
                $rawExcerpt = $blog->excerpt ?: strip_tags($blog->content ?? '');
                $cleanExcerpt = html_entity_decode($rawExcerpt, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            @endphp

            <div class="col-lg-4 col-md-6">
                <article class="card blog-card h-100 border-0">
                    <div class="blog-img">
                        <a href="{{ route('blogs.show', $blog->slug) }}">
                            @if($blog->thumbnail)
                                <img
                                    src="{{ asset('storage/' . $blog->thumbnail) }}"
                                    alt="{{ $blog->title }}"
                                    class="card-img-top">
                            @else
                                <img
                                    src="{{ asset('images/no-image.jpg') }}"
                                    alt="{{ $blog->title }}"
                                    class="card-img-top">
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
                            {{ Str::limit(trim($cleanExcerpt), 130) }}
                        </p>

                        <a href="{{ route('blogs.show', $blog->slug) }}" class="btn-read">
                            Đọc tiếp
                            <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="empty-blog-box text-center">
                    <div class="empty-icon mb-3">
                        <i class="bi bi-journal-x"></i>
                    </div>
                    <h5 class="mb-2">Chưa có bài viết nào</h5>
                    <p class="text-muted mb-0">
                        Hiện tại chưa có nội dung để hiển thị. Vui lòng quay lại sau.
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($blogs->hasPages())
        <div class="mt-5 blog-pagination">
            {{ $blogs->links() }}
        </div>
    @endif
</div>

<style>
    .blog-filter-wrap {
        display: flex;
        justify-content: flex-start;
        align-items: center;
    }

    .blog-filter {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filter-btn {
        display: inline-flex;
        align-items: center;
        padding: 10px 16px;
        border-radius: 999px;
        border: 1px solid #dbe7fb;
        text-decoration: none;
        color: #374151;
        font-size: 14px;
        font-weight: 500;
        background: #fff;
        transition: all 0.25s ease;
    }

    .filter-btn i {
        color: #6b7280;
        transition: 0.25s ease;
    }

    .filter-btn:hover {
        border-color: #0d6efd;
        color: #0d6efd;
        background: #eef5ff;
    }

    .filter-btn:hover i {
        color: #0d6efd;
    }

    .filter-btn.active {
        background: linear-gradient(135deg, #0d6efd, #0b5ed7);
        color: #fff;
        border-color: #0d6efd;
        box-shadow: 0 8px 18px rgba(13, 110, 253, 0.2);
    }

    .filter-btn.active i {
        color: #fff;
    }

    .blog-card {
        border-radius: 20px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
    }

    .blog-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
    }

    .blog-img {
        position: relative;
        overflow: hidden;
        background: #f8f9fa;
    }

    .blog-img img {
        width: 100%;
        height: 240px;
        object-fit: cover;
        transition: transform 0.45s ease;
        display: block;
    }

    .blog-card:hover .blog-img img {
        transform: scale(1.06);
    }

    .blog-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        font-size: 13px;
        color: #6b7280;
    }

    .meta-dot {
        color: #c0c4cc;
        margin: 0 2px;
    }

    .blog-title {
        color: #111827;
        font-weight: 700;
        text-decoration: none;
        line-height: 1.5;
        font-size: 20px;
        transition: color 0.2s ease;
        display: block;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 60px;
    }

    .blog-title:hover {
        color: #0d6efd;
    }

    .blog-excerpt {
        color: #4b5563;
        font-size: 15px;
        line-height: 1.7;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .btn-read {
        display: inline-flex;
        align-items: center;
        text-decoration: none;
        font-size: 14px;
        font-weight: 700;
        color: #111827;
        transition: all 0.2s ease;
        width: fit-content;
    }

    .btn-read i {
        transition: all 0.2s ease;
    }

    .btn-read:hover {
        color: #0d6efd;
        transform: translateX(3px);
    }

    .btn-read:hover i {
        color: #0d6efd;
    }

    .empty-blog-box {
        background: #fff;
        border: 1px dashed #d1d5db;
        border-radius: 20px;
        padding: 60px 20px;
    }

    .empty-icon {
        font-size: 48px;
        color: #0d6efd;
        line-height: 1;
    }

    .blog-pagination nav {
        display: flex;
        justify-content: center;
    }

    @media (max-width: 991.98px) {
        .blog-img img {
            height: 220px;
        }
    }

    @media (max-width: 767.98px) {
        .blog-title {
            font-size: 18px;
            min-height: auto;
        }

        .blog-img img {
            height: 210px;
        }

        .filter-btn {
            padding: 9px 14px;
            font-size: 13px;
        }
    }
</style>
@endsection