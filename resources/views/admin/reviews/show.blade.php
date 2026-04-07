@extends('layouts.admin')

@section('title','Chi tiết đánh giá')

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">

            {{-- HEADER --}}
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold mb-1">Chi tiết đánh giá</h4>
                    <small class="text-muted">
                        Xem và phản hồi đánh giá của khách hàng
                    </small>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    @if($review->order_id)
                        <a href="{{ route('admin.orders.show', $review->order_id) }}"
                           class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-receipt me-1"></i>
                            Xem đơn hàng
                        </a>
                    @endif

                    <a href="{{ route('admin.reviews.index') }}"
                       class="btn btn-outline-secondary btn-sm">
                        Quay lại
                    </a>
                </div>
            </div>

            <div class="row g-4">
                {{-- THÔNG TIN --}}
                <div class="col-lg-8">
                    <div class="border rounded-4 p-4 h-100">
                        <h6 class="fw-bold mb-3">Thông tin đánh giá</h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="small text-muted mb-1">Đơn hàng</div>
                                <div class="fw-semibold">
                                    @if($review->order_id)
                                        DH{{ str_pad($review->order_id, 5, '0', STR_PAD_LEFT) }}
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="small text-muted mb-1">Khách hàng</div>
                                <div class="fw-semibold">
                                    {{ $review->user->name ?? 'N/A' }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="small text-muted mb-1">Sản phẩm</div>
                                <div class="fw-semibold">
                                    {{ $review->product->name ?? 'N/A' }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="small text-muted mb-1">Phân loại</div>
                                <div class="fw-semibold">
                                    @if($review->variant)
                                        {{ $review->variant->attribute_name }}:
                                        {{ $review->variant->attribute_value }}
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="small text-muted mb-1">Ngày đánh giá</div>
                                <div class="fw-semibold">
                                    {{ $review->created_at ? $review->created_at->format('d/m/Y H:i') : 'N/A' }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="small text-muted mb-1">Số sao</div>
                                <div>
                                    <span class="text-warning">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $review->rating)
                                                <i class="bi bi-star-fill"></i>
                                            @else
                                                <i class="bi bi-star"></i>
                                            @endif
                                        @endfor
                                    </span>
                                    <span class="small text-muted ms-1">({{ $review->rating }})</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="small text-muted mb-1">Nghi vấn</div>
                                <div>
                                    @if($review->is_flagged)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2">
                                            Có nghi vấn
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border px-3 py-2">
                                            Bình thường
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="small text-muted mb-1">Trạng thái</div>
                                <div>
                                    @if($review->is_visible)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                            Hiển thị
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-dark border px-3 py-2">
                                            Đã ẩn
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="small text-muted mb-1">Thao tác</div>
                                <form action="{{ route('admin.reviews.toggle', $review->id) }}" method="POST">
                                    @csrf

                                    @if($review->is_visible)
                                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                                            Ẩn đánh giá
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-outline-success btn-sm">
                                            Hiện đánh giá
                                        </button>
                                    @endif
                                </form>
                            </div>

                            <div class="col-md-6">
                                <div class="small text-muted mb-1">Phản hồi</div>
                                <div>
                                    @if($review->admin_reply)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                            Đã phản hồi
                                        </span>
                                    @else
                                        <span class="badge bg-warning-subtle text-dark border px-3 py-2">
                                            Chưa phản hồi
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- TÓM TẮT --}}
                <div class="col-lg-4">
                    <div class="border rounded-4 p-4 bg-light-subtle h-100">
                        <h6 class="fw-bold mb-3">Tóm tắt</h6>

                        <div class="mb-3">
                            <div class="small text-muted mb-1">Khách hàng</div>
                            <div class="fw-semibold">{{ $review->user->name ?? 'N/A' }}</div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted mb-1">Sản phẩm</div>
                            <div class="fw-semibold">{{ $review->product->name ?? 'N/A' }}</div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted mb-1">Đơn hàng</div>
                            <div class="fw-semibold">
                                @if($review->order_id)
                                    DH{{ str_pad($review->order_id, 5, '0', STR_PAD_LEFT) }}
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted mb-1">Media đính kèm</div>
                            @if($review->media && $review->media->count())
                                <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2">
                                    {{ $review->media->count() }} file
                                </span>
                            @else
                                <span class="text-muted">Không có</span>
                            @endif
                        </div>

                        <div>
                            <div class="small text-muted mb-1">Ngày gửi đánh giá</div>
                            <div class="fw-semibold">
                                {{ $review->created_at ? $review->created_at->format('d/m/Y H:i') : 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            {{-- NỘI DUNG REVIEW --}}
            <div class="mb-4">
                <h6 class="fw-bold mb-2">Nội dung đánh giá</h6>
                <div class="border rounded-4 p-3 bg-light">
                    {{ $review->comment ?: 'Khách hàng không nhập nội dung đánh giá.' }}
                </div>
            </div>

            {{-- ẢNH / VIDEO REVIEW --}}
            @if($review->media && $review->media->count())
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Ảnh / Video review</h6>

                    <div class="row">
                        @foreach($review->media as $media)
                            <div class="col-md-3 col-6 mb-3">
                                @if($media->file_type === 'image')
                                    <a href="{{ asset('storage/' . $media->file_path) }}" target="_blank">
                                        <img
                                            src="{{ asset('storage/' . $media->file_path) }}"
                                            class="img-fluid rounded-4 border shadow-sm"
                                            style="width:100%; height:220px; object-fit:cover;"
                                            alt="review-image"
                                        >
                                    </a>
                                @elseif($media->file_type === 'video')
                                    <video
                                        controls
                                        class="w-100 rounded-4 border shadow-sm"
                                        style="height:220px; object-fit:cover; background:#000;"
                                    >
                                        <source src="{{ asset('storage/' . $media->file_path) }}">
                                        Trình duyệt không hỗ trợ video.
                                    </video>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <hr class="my-4">
            @endif

            {{-- ADMIN REPLY --}}
            <div>
                <h6 class="fw-bold mb-2">Trả lời từ cửa hàng</h6>

                <form action="{{ route('admin.reviews.reply', $review->id) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <textarea
                            name="admin_reply"
                            class="form-control @error('admin_reply') is-invalid @enderror"
                            rows="4"
                            placeholder="Nhập phản hồi cho khách hàng..."
                        >{{ old('admin_reply', $review->admin_reply) }}</textarea>

                        @error('admin_reply')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-1"></i>
                        Gửi phản hồi
                    </button>
                </form>

                @if($review->admin_reply)
                    <div class="mt-3 alert alert-success rounded-4">
                        <strong>Phản hồi hiện tại:</strong>
                        <p class="mb-0 mt-2">
                            {{ $review->admin_reply }}
                        </p>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection