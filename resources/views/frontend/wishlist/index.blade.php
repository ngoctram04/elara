@extends('layouts.frontend')

@section('title', 'Sản phẩm yêu thích')

@section('content')
<div class="container py-4">

    <h4 class="mb-4">
        <i class="bi bi-heart-fill text-danger me-2"></i>
        Sản phẩm yêu thích
    </h4>

    @if($wishlists->count() == 0)
        <div class="alert alert-info">
            Bạn chưa có sản phẩm yêu thích.
        </div>
    @else

    <div class="row">
        @foreach($wishlists as $item)
            @php
                $product = $item->product;
            @endphp

            {{-- DÙNG PARTIAL CHUẨN CỦA HỆ THỐNG --}}
            @include('frontend.partials.product-card-common', [
                'product' => $product,
                'favorites' => $favorites ?? []
            ])
        @endforeach
    </div>

    <div class="mt-4">
        {{ $wishlists->links() }}
    </div>

    @endif

</div>
@endsection