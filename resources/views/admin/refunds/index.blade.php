@extends('layouts.admin')

@section('title', 'Yêu cầu hoàn tiền')

@section('content')
@php
    use Illuminate\Support\Str;
@endphp

<div class="card border-0 shadow-sm">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="mb-1">Yêu cầu hoàn tiền</h5>
                <small class="text-muted">
                    Danh sách các yêu cầu hoàn tiền từ khách hàng
                </small>
            </div>
        </div>

        <form method="GET" class="row g-2 mb-4 align-items-center">
            <div class="col-md-4">
                <input
                    type="text"
                    name="search"
                    class="form-control form-control-sm"
                    placeholder="Tìm mã đơn hoặc khách hàng..."
                    value="{{ request('search') }}"
                >
            </div>

            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                        Chờ duyệt
                    </option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>
                        Đã duyệt
                    </option>
                    <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>
                        Đã hoàn tiền
                    </option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>
                        Từ chối
                    </option>
                </select>
            </div>

            <div class="col-md-3">
                <select name="sort" class="form-select form-select-sm">
                    <option value="new" {{ request('sort', 'new') == 'new' ? 'selected' : '' }}>
                        Mới nhất
                    </option>
                    <option value="old" {{ request('sort') == 'old' ? 'selected' : '' }}>
                        Cũ nhất
                    </option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm" type="submit">
                    <i class="bi bi-search me-1"></i>Lọc
                </button>

                <a href="{{ route('admin.refunds.index') }}" class="btn btn-outline-secondary btn-sm">
                    Đặt lại
                </a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Đơn hàng</th>
                        <th>Khách hàng</th>
                        <th>Lý do</th>
                        <th>Trạng thái</th>
                        <th>Ngày gửi</th>
                        <th width="110">Chi tiết</th>
                        <th width="220">Hành động</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($refunds as $refund)
                        @php
                            $reasonRaw = trim((string) $refund->reason);
                            $parts = explode('Chi tiết sản phẩm khách chọn:', $reasonRaw);
                            $mainReason = trim($parts[0] ?? $reasonRaw);
                        @endphp

                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $refund->order_id) }}"
                                   class="text-decoration-none fw-medium">
                                    DH{{ str_pad($refund->order_id, 5, '0', STR_PAD_LEFT) }}
                                </a>
                            </td>

                            <td>{{ $refund->order->user->name ?? '---' }}</td>

                            <td style="max-width:260px;">
                                <span class="text-muted">
                                    {{ Str::limit($mainReason, 60) }}
                                </span>
                            </td>

                            <td>
                                @if($refund->status == 'pending')
                                    <span class="badge text-dark border bg-warning-subtle">Chờ duyệt</span>
                                @elseif($refund->status == 'approved')
                                    <span class="badge text-primary border bg-primary-subtle">Đã duyệt</span>
                                @elseif($refund->status == 'refunded')
                                    <span class="badge text-success border bg-success-subtle">Đã hoàn tiền</span>
                                @else
                                    <span class="badge text-danger border bg-danger-subtle">Từ chối</span>
                                @endif
                            </td>

                            <td>
                                <span class="text-muted">
                                    {{ optional($refund->created_at)->format('d/m/Y H:i') ?? '---' }}
                                </span>
                            </td>

                            <td>
                                <button
                                    type="button"
                                    class="btn btn-outline-dark btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#refundModal{{ $refund->id }}">
                                    Xem
                                </button>
                            </td>

                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    @if($refund->status == 'pending')
                                        <form action="{{ route('admin.refunds.approve', $refund->id) }}"
                                              method="POST"
                                              class="d-inline">
                                            @csrf
                                            <button class="btn btn-success btn-sm" type="submit">
                                                Duyệt
                                            </button>
                                        </form>

                                        <button
                                            type="button"
                                            class="btn btn-danger btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#rejectModal{{ $refund->id }}">
                                            Từ chối
                                        </button>
                                    @endif

                                    @if($refund->status == 'approved')
                                        <button
                                            type="button"
                                            class="btn btn-primary btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#refundConfirmModal{{ $refund->id }}">
                                            Đã hoàn tiền
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade" id="rejectModal{{ $refund->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content border-0 shadow-sm">
                                    <form action="{{ route('admin.refunds.reject', $refund->id) }}" method="POST">
                                        @csrf

                                        <div class="modal-header">
                                            <h5 class="modal-title">Từ chối yêu cầu hoàn tiền</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">
                                            <label class="form-label">Lý do từ chối</label>
                                            <textarea
                                                name="admin_note"
                                                class="form-control"
                                                rows="4"
                                                required
                                                placeholder="Nhập lý do từ chối..."></textarea>
                                        </div>

                                        <div class="modal-footer">
                                            <button
                                                type="button"
                                                class="btn btn-light border"
                                                data-bs-dismiss="modal">
                                                Hủy
                                            </button>

                                            <button
                                                type="submit"
                                                class="btn btn-danger">
                                                Xác nhận từ chối
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="refundConfirmModal{{ $refund->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content border-0 shadow-sm">
                                    <form action="{{ route('admin.refunds.refunded', $refund->id) }}" method="POST">
                                        @csrf

                                        <div class="modal-header">
                                            <h5 class="modal-title">Xác nhận hoàn tiền</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Ghi chú xử lý</label>
                                                <textarea
                                                    name="admin_note"
                                                    class="form-control"
                                                    rows="4"
                                                    placeholder="Ví dụ: Đã kiểm tra hàng hoàn và xác nhận hoàn tiền cho khách..."></textarea>
                                            </div>

                                            <div class="rounded-3 border bg-light p-3">
                                                <div class="small text-muted" style="line-height:1.7;">
                                                    <div class="mb-1 text-dark">Lưu ý</div>
                                                    - Còn nguyên seal: hoàn kho, hoàn lô<br>
                                                    - Bị vỡ / hư hỏng / đã mở: không hoàn kho, ghi nhận hao hụt giá nhập
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button
                                                type="button"
                                                class="btn btn-light border"
                                                data-bs-dismiss="modal">
                                                Hủy
                                            </button>

                                            <button
                                                type="submit"
                                                class="btn btn-primary">
                                                Xác nhận đã hoàn tiền
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="refundModal{{ $refund->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                <div class="modal-content border-0 shadow-sm">

                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            Chi tiết yêu cầu hoàn tiền - HT{{ str_pad($refund->id, 5, '0', STR_PAD_LEFT) }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-6">
                                                <div class="small text-muted mb-1">Đơn hàng</div>
                                                <div>DH{{ str_pad($refund->order_id, 5, '0', STR_PAD_LEFT) }}</div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="small text-muted mb-1">Khách hàng</div>
                                                <div>{{ $refund->order->user->name ?? '---' }}</div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="small text-muted mb-1">Trạng thái</div>
                                                <div>
                                                    @if($refund->status == 'pending')
                                                        <span class="badge text-dark border bg-warning-subtle">Chờ duyệt</span>
                                                    @elseif($refund->status == 'approved')
                                                        <span class="badge text-primary border bg-primary-subtle">Đã duyệt</span>
                                                    @elseif($refund->status == 'refunded')
                                                        <span class="badge text-success border bg-success-subtle">Đã hoàn tiền</span>
                                                    @else
                                                        <span class="badge text-danger border bg-danger-subtle">Từ chối</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="small text-muted mb-1">Ngày gửi</div>
                                                <div>{{ optional($refund->created_at)->format('d/m/Y H:i') ?? '---' }}</div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="mb-2">Lý do hoàn tiền</div>
                                            <div class="border rounded-3 p-3 bg-light small text-muted" style="line-height:1.7;">
                                                {{ $mainReason ?: '---' }}
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="mb-2">Chi tiết sản phẩm hoàn</div>

                                            @if($refund->items->count())
                                                <div class="border rounded-3 overflow-hidden">
                                                    @foreach($refund->items as $item)
                                                        @php
                                                            $variant = $item->variant;
                                                            $product = $variant?->product;

                                                            $image = null;

                                                            if (!empty($variant?->mainImage?->image_path)) {
                                                                $image = $variant->mainImage->image_path;
                                                            } elseif (!empty($variant?->image_path)) {
                                                                $image = $variant->image_path;
                                                            } elseif (!empty($product?->mainImage?->image_path)) {
                                                                $image = $product->mainImage->image_path;
                                                            }

                                                            $variantCode = $variant ? ('BT' . str_pad($variant->id, 5, '0', STR_PAD_LEFT)) : '---';
                                                            $rawCondition = strtolower(trim((string) ($item->pivot->condition_status ?? '')));

                                                            $conditionText = match($rawCondition) {
                                                                'sealed', 'intact', 'new', 'con_nguyen', 'connguyenseal' => 'Còn nguyên seal',
                                                                'opened', 'da_mo', 'damo' => 'Đã mở',
                                                                'broken', 'damaged', 'vo', 'bi_vo', 'bivo' => 'Bị vỡ / hư hỏng',
                                                                'expired', 'het_han', 'hethan' => 'Hết hạn',
                                                                default => 'Không xác định',
                                                            };

                                                            $conditionClass = match($rawCondition) {
                                                                'sealed', 'intact', 'new', 'con_nguyen', 'connguyenseal' => 'text-success border bg-success-subtle',
                                                                'opened', 'da_mo', 'damo' => 'text-warning border bg-warning-subtle',
                                                                'broken', 'damaged', 'vo', 'bi_vo', 'bivo' => 'text-danger border bg-danger-subtle',
                                                                'expired', 'het_han', 'hethan' => 'text-secondary border bg-secondary-subtle',
                                                                default => 'text-secondary border bg-light',
                                                            };
                                                        @endphp

                                                        <div class="d-flex gap-3 p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                                            <div style="width:72px; min-width:72px;">
                                                                @if($image)
                                                                    <img
                                                                        src="{{ asset('storage/' . ltrim($image, '/')) }}"
                                                                        alt="variant-image"
                                                                        class="img-fluid rounded border"
                                                                        style="width:72px; height:72px; object-fit:cover;"
                                                                        onerror="this.onerror=null;this.src='{{ asset('images/no-image.png') }}';"
                                                                    >
                                                                @else
                                                                    <div class="d-flex align-items-center justify-content-center rounded border bg-light text-muted"
                                                                         style="width:72px; height:72px; font-size:12px;">
                                                                        No image
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            <div class="flex-grow-1">
                                                                <div class="mb-1">
                                                                    {{ $product->name ?? 'Sản phẩm' }}
                                                                </div>

                                                                <div class="small text-muted mb-1">
                                                                    Mã biến thể:
                                                                    <span class="text-dark">{{ $variantCode }}</span>
                                                                </div>

                                                                @if(!empty($variant?->attribute_name) || !empty($variant?->attribute_value))
                                                                    <div class="small text-muted mb-1">
                                                                        Phân loại:
                                                                        {{ $variant->attribute_name ?? '' }}
                                                                        @if(!empty($variant?->attribute_name) && !empty($variant?->attribute_value))
                                                                            :
                                                                        @endif
                                                                        {{ $variant->attribute_value ?? '' }}
                                                                    </div>
                                                                @endif

                                                                <div class="small text-muted mb-1">
                                                                    Số lượng hoàn: {{ $item->pivot->quantity ?? 1 }}
                                                                </div>

                                                                @if(!empty($item->pivot->reason))
                                                                    <div class="small text-muted mb-1">
                                                                        Lý do sản phẩm: {{ $item->pivot->reason }}
                                                                    </div>
                                                                @endif

                                                                <div class="small">
                                                                    Tình trạng:
                                                                    <span class="badge {{ $conditionClass }}">{{ $conditionText }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="border rounded-3 p-3 bg-light small text-muted">
                                                    Không có dữ liệu sản phẩm hoàn
                                                </div>
                                            @endif
                                        </div>

                                        @if(!empty($refund->admin_note))
                                            @php
                                                $adminNotes = array_filter(array_map('trim', explode('|', $refund->admin_note)));
                                            @endphp

                                            <div class="mb-3">
                                                <div class="mb-2">Ghi chú admin</div>

                                                <div class="border rounded-3 p-3 bg-light">
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach($adminNotes as $note)
                                                            <span class="px-3 py-2 rounded-pill border bg-white text-muted" style="font-size:13px;">
                                                                {{ $note }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="mb-2">Hình ảnh / video minh chứng</div>

                                        <div class="d-flex flex-wrap gap-2">
                                            @forelse($refund->media as $media)
                                                @php
                                                    $filePath = $media->file_path ?? '';
                                                    $lowerPath = strtolower($filePath);
                                                    $isImage = Str::endsWith($lowerPath, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                                                    $mediaUrl = asset('storage/' . ltrim($filePath, '/'));
                                                @endphp

                                                @if($isImage)
                                                    <img
                                                        src="{{ $mediaUrl }}"
                                                        width="120"
                                                        height="120"
                                                        class="refund-preview border"
                                                        style="object-fit:cover;border-radius:8px;cursor:pointer"
                                                        alt="refund-media"
                                                        onerror="this.onerror=null;this.src='{{ asset('images/no-image.png') }}';"
                                                    >
                                                @else
                                                    <video
                                                        width="200"
                                                        class="refund-preview border"
                                                        style="border-radius:8px;cursor:pointer"
                                                        muted>
                                                        <source src="{{ $mediaUrl }}">
                                                    </video>
                                                @endif
                                            @empty
                                                <div class="text-muted small">Không có hình minh chứng</div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Không có yêu cầu hoàn tiền
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $refunds->appends(request()->query())->links('vendor.pagination.custom-blue') }}
        </div>
    </div>
</div>

<div class="modal fade" id="previewMediaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-0">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">
                <img
                    id="previewImage"
                    style="max-width:100%; max-height:75vh; display:none; border-radius:8px;"
                    alt="preview-image">

                <video
                    id="previewVideo"
                    controls
                    style="max-width:100%; max-height:75vh; display:none; border-radius:8px;">
                </video>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.refund-preview').forEach(function (el) {
    el.addEventListener('click', function () {
        const img = document.getElementById('previewImage');
        const video = document.getElementById('previewVideo');

        img.style.display = 'none';
        video.style.display = 'none';
        img.removeAttribute('src');
        video.pause();
        video.removeAttribute('src');
        video.load();

        if (this.tagName === 'IMG') {
            img.src = this.src;
            img.style.display = 'block';
        } else {
            const source = this.querySelector('source');
            if (source) {
                video.src = source.src;
                video.style.display = 'block';
            }
        }

        const modal = new bootstrap.Modal(document.getElementById('previewMediaModal'));
        modal.show();
    });
});
</script>
@endsection