@extends('layouts.frontend')

@section('title', 'Sản phẩm yêu thích')

@section('content')

<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => url('/')],
    ['label' => 'Sản phẩm yêu thích']
]" />

<div class="container pb-4">

    @if($wishlists->count() == 0)
        <div class="wishlist-empty-box text-center">
            <div class="wishlist-empty-icon mb-3">
                <i class="bi bi-heart"></i>
            </div>

            <h5 class="mb-2">Chưa có sản phẩm yêu thích</h5>
            <p class="text-muted mb-3">
                Bạn chưa thêm sản phẩm nào vào danh sách yêu thích.
            </p>

            <a href="{{ route('shop') }}" class="btn btn-primary px-4">
                Khám phá sản phẩm
            </a>
        </div>
    @else

        <div class="wishlist-grid">
            <div class="row g-4">
                @foreach($wishlists as $item)
                    @php
                        $product = $item->product;
                    @endphp

                    <div class="col-6 col-md-4 col-lg-3">
                        @include('frontend.partials.product-card-common', [
                            'product' => $product,
                            'favorites' => $favorites ?? []
                        ])
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-4 wishlist-pagination">
            {{ $wishlists->links() }}
        </div>

    @endif

</div>

<style>
    .wishlist-grid {
        margin-top: 8px;
    }

    .wishlist-empty-box {
        background: #fff;
        border: 1px dashed #d1d5db;
        border-radius: 20px;
        padding: 60px 20px;
    }

    .wishlist-empty-icon {
        font-size: 52px;
        color: #0d6efd;
        line-height: 1;
    }

    .wishlist-pagination nav {
        display: flex;
        justify-content: center;
    }

    @media (max-width: 767.98px) {
        .wishlist-empty-box {
            padding: 44px 16px;
            border-radius: 16px;
        }
    }
</style>
@endsection