@extends('layouts.admin')

@section('title','Quản lý đánh giá')

@section('content')
@php
    use Illuminate\Support\Str;
@endphp

<div class="container-fluid">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">

            {{-- HEADER --}}
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold mb-1">Quản lý đánh giá</h4>
                    <small class="text-muted">
                        Quản lý các đánh giá của khách hàng
                    </small>
                </div>
            </div>

            {{-- BỘ LỌC --}}
            <div class="border rounded-4 p-3 bg-light-subtle mb-4">
    <form method="GET" class="row g-3 align-items-end">

        <div class="col-lg-3 col-md-6">
            <label class="form-label small fw-semibold text-muted">Từ khóa</label>
            <input
                type="text"
                name="keyword"
                class="form-control"
                placeholder="Mã đơn hàng, khách hàng hoặc sản phẩm..."
                value="{{ request('keyword') }}"
            >
        </div>

        <div class="col-lg-2 col-md-6">
            <label class="form-label small fw-semibold text-muted">Số sao</label>
            <select name="rating" class="form-select">
                <option value="">Tất cả sao</option>
                <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 sao</option>
                <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 sao</option>
                <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 sao</option>
                <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 sao</option>
                <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 sao</option>
            </select>
        </div>

        <div class="col-lg-2 col-md-6">
            <label class="form-label small fw-semibold text-muted">Trạng thái</label>
            <select name="visible" class="form-select">
                <option value="">Tất cả</option>
                <option value="1" {{ request('visible') === '1' ? 'selected' : '' }}>Hiển thị</option>
                <option value="0" {{ request('visible') === '0' ? 'selected' : '' }}>Đã ẩn</option>
            </select>
        </div>

        <div class="col-lg-2 col-md-6">
            <label class="form-label small fw-semibold text-muted">Phản hồi</label>
            <select name="reply" class="form-select">
                <option value="">Tất cả</option>
                <option value="replied" {{ request('reply') === 'replied' ? 'selected' : '' }}>Đã trả lời</option>
                <option value="pending" {{ request('reply') === 'pending' ? 'selected' : '' }}>Chưa trả lời</option>
            </select>
        </div>

        <div class="col-lg-2 col-md-6">
            <label class="form-label small fw-semibold text-muted">Nghi vấn</label>
            <select name="flagged" class="form-select">
                <option value="">Tất cả</option>
                <option value="1" {{ request('flagged') === '1' ? 'selected' : '' }}>Có nghi vấn</option>
                <option value="0" {{ request('flagged') === '0' ? 'selected' : '' }}>Bình thường</option>
            </select>
        </div>

        <div class="col-lg-1 col-md-12">
    <label class="form-label small fw-semibold text-muted d-block opacity-0">Action</label>

    <div class="d-flex gap-2 justify-content-end">

        {{-- LỌC --}}
        <button type="submit"
                class="btn btn-primary d-flex align-items-center justify-content-center"
                style="width:42px; height:42px;">
            <i class="bi bi-search"></i>
        </button>

        {{-- RESET --}}
        <a href="{{ route('admin.reviews.index') }}"
           class="btn btn-outline-secondary d-flex align-items-center justify-content-center"
           style="width:42px; height:42px;">
            <i class="bi bi-arrow-clockwise"></i>
        </a>

    </div>
</div>

    </form>
</div>

            {{-- TABLE --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-center align-middle">
                            <th width="100">Mã đơn</th>
                            <th width="160">Khách hàng</th>
                            <th>Sản phẩm</th>
                            <th width="140">Số sao</th>
                            <th width="200">Nội dung</th>
                            <th width="100">Ảnh/video</th>
                            <th width="100">Nghi vấn</th>
                            <th width="100">Phản hồi</th>
                            <th width="100">Trạng thái</th>
                            <th width="180">Hành động</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($reviews as $review)
                            <tr>
                                {{-- ĐƠN HÀNG --}}
                                <td class="text-center fw-semibold text-muted">
                                    @if($review->order_id)
                                        DH{{ str_pad($review->order_id, 5, '0', STR_PAD_LEFT) }}
                                    @else
                                        N/A
                                    @endif
                                </td>

                                {{-- KHÁCH --}}
                                <td>
                                    <div class="fw-semibold">
                                        {{ $review->user->name ?? 'N/A' }}
                                    </div>
                                </td>

                                {{-- SẢN PHẨM --}}
                                <td>
                                    <div class="fw-semibold">
                                        {{ $review->product->name ?? 'N/A' }}
                                    </div>

                                    @if($review->variant)
                                        <div class="small text-muted mt-1">
                                            {{ $review->variant->attribute_name }}:
                                            {{ $review->variant->attribute_value }}
                                        </div>
                                    @endif
                                </td>

                                {{-- SỐ SAO --}}
                                <td class="text-center">
                                    <div class="text-warning">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                <i class="bi bi-star-fill"></i>
                                            @else
                                                <i class="bi bi-star"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <small class="text-muted">({{ $review->rating }})</small>
                                </td>

                                {{-- NỘI DUNG --}}
                                <td>
                                    <div class="small text-dark">
                                        {{ $review->comment ? Str::limit($review->comment, 70) : 'Không có nội dung' }}
                                    </div>
                                </td>

                                {{-- MEDIA --}}
                                <td class="text-center">
                                    @if($review->media && $review->media->count())
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2">
                                            {{ $review->media->count() }} file
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                {{-- NGHI VẤN --}}
                                <td class="text-center">
                                    @if($review->is_flagged)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2">
                                            Nghi vấn
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border px-3 py-2">
                                            Bình thường
                                        </span>
                                    @endif
                                </td>

                                {{-- PHẢN HỒI --}}
                                <td class="text-center">
                                    @if($review->admin_reply)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                            Đã trả lời
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-dark border px-3 py-2">
                                            Chưa trả lời
                                        </span>
                                    @endif
                                </td>

                                {{-- TRẠNG THÁI --}}
                                <td class="text-center">
                                    @if($review->is_visible)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                            Hiển thị
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-dark border px-3 py-2">
                                            Đã ẩn
                                        </span>
                                    @endif
                                </td>

                                {{-- HÀNH ĐỘNG --}}
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                                        <a href="{{ route('admin.reviews.show', $review->id) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            Chi tiết
                                        </a>

                                        <form action="{{ route('admin.reviews.toggle', $review->id) }}"
                                              method="POST"
                                              class="d-inline">
                                            @csrf

                                            @if($review->is_visible)
                                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                    Ẩn
                                                </button>
                                            @else
                                                <button type="submit" class="btn btn-sm btn-outline-success">
                                                    Hiện
                                                </button>
                                            @endif
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-5">
                                    Không có đánh giá
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="mt-4 d-flex justify-content-end">
                {{ $reviews->links('vendor.pagination.custom-blue') }}
            </div>

        </div>
    </div>
</div>
@endsection