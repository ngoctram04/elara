@extends('layouts.admin')

@section('title', 'Yêu cầu hoàn tiền')

@section('content')
@php
    use Illuminate\Support\Str;
@endphp

<div class="container-fluid">

    <h4 class="mb-4">Yêu cầu hoàn tiền</h4>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">

            {{-- FILTER --}}
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <form method="GET" class="row g-2 mb-3 align-items-center">
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
                                <option value="new" {{ request('sort') == 'new' ? 'selected' : '' }}>
                                    Mới nhất
                                </option>

                                <option value="old" {{ request('sort') == 'old' ? 'selected' : '' }}>
                                    Cũ nhất
                                </option>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-search"></i>
                                Lọc
                            </button>

                            <a href="{{ route('admin.refunds.index') }}"
                               class="btn btn-outline-secondary btn-sm">
                                Đặt lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- TABLE --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>STT</th>
                            <th>Đơn hàng</th>
                            <th>Khách hàng</th>
                            <th>Lý do</th>
                            <th>Trạng thái</th>
                            <th>Ngày gửi</th>
                            <th width="120">Chi tiết</th>
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
                                    {{ ($refunds->currentPage() - 1) * $refunds->perPage() + $loop->iteration }}
                                </td>

                                <td>
                                    <a href="{{ route('admin.orders.show', $refund->order_id) }}">
                                        DH{{ str_pad($refund->order_id, 5, '0', STR_PAD_LEFT) }}
                                    </a>
                                </td>

                                <td>{{ $refund->user->name ?? '---' }}</td>

                                <td style="max-width:250px">
                                    {{ Str::limit($mainReason, 60) }}
                                </td>

                                <td>
                                    @if($refund->status == 'pending')
                                        <span class="badge bg-warning">Chờ duyệt</span>
                                    @elseif($refund->status == 'approved')
                                        <span class="badge bg-primary">Đã duyệt</span>
                                    @elseif($refund->status == 'refunded')
                                        <span class="badge bg-success">Đã hoàn tiền</span>
                                    @else
                                        <span class="badge bg-danger">Từ chối</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $refund->created_at->format('d/m/Y H:i') }}
                                </td>

                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-dark"
                                        data-bs-toggle="modal"
                                        data-bs-target="#refundModal{{ $refund->id }}">
                                        Xem
                                    </button>
                                </td>

                                <td>
                                    @if($refund->status == 'pending')
                                        <form action="{{ route('admin.refunds.approve', $refund->id) }}"
                                              method="POST"
                                              class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-success">
                                                Duyệt
                                            </button>
                                        </form>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#rejectModal{{ $refund->id }}">
                                            Từ chối
                                        </button>
                                    @endif

                                    @if($refund->status == 'approved')
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#refundConfirmModal{{ $refund->id }}">
                                            Đã hoàn tiền
                                        </button>
                                    @endif
                                </td>
                            </tr>

                            {{-- MODAL TỪ CHỐI --}}
                            <div class="modal fade" id="rejectModal{{ $refund->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.refunds.reject', $refund->id) }}" method="POST">
                                            @csrf

                                            <div class="modal-header">
                                                <h5 class="modal-title">Từ chối yêu cầu hoàn tiền</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">
                                                <label class="form-label fw-semibold">Lý do từ chối</label>
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
                                                    class="btn btn-secondary"
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

                            {{-- MODAL XÁC NHẬN HOÀN TIỀN --}}
                            <div class="modal fade" id="refundConfirmModal{{ $refund->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.refunds.refunded', $refund->id) }}" method="POST">
                                            @csrf

                                            <div class="modal-header">
                                                <h5 class="modal-title">Xác nhận hoàn tiền</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="mb-0">
                                                    <label class="form-label fw-semibold">Ghi chú xử lý</label>
                                                    <textarea
                                                        name="admin_note"
                                                        class="form-control"
                                                        rows="4"
                                                        placeholder="Ví dụ: Đã kiểm tra hàng hoàn và xác nhận hoàn tiền cho khách..."></textarea>
                                                </div>

                                                <div class="alert alert-light border mt-3 mb-0">
                                                    <div class="small text-muted" style="line-height: 1.6;">
                                                        <strong>Lưu ý:</strong><br>
                                                        Hệ thống sẽ xử lý theo từng sản phẩm khách đã chọn:
                                                        <br>- <strong>Còn nguyên seal</strong>: hoàn kho, hoàn lô
                                                        <br>- <strong>Bị vỡ</strong>: không hoàn kho, không hoàn lô, ghi nhận hao hụt giá nhập
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button
                                                    type="button"
                                                    class="btn btn-secondary"
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

                            {{-- MODAL CHI TIẾT --}}
                            <div class="modal fade" id="refundModal{{ $refund->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                    <div class="modal-content">

                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                Chi tiết yêu cầu hoàn tiền
                                                HT{{ str_pad($refund->id, 5, '0', STR_PAD_LEFT) }}
                                            </h5>

                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">
                                            <p>
                                                <b>Đơn hàng:</b>
                                                DH{{ str_pad($refund->order_id, 5, '0', STR_PAD_LEFT) }}
                                            </p>

                                            <p>
                                                <b>Khách hàng:</b>
                                                {{ $refund->user->name ?? '---' }}
                                            </p>

                                            <p>
                                                <b>Trạng thái:</b>
                                                @if($refund->status == 'pending')
                                                    <span class="badge bg-warning">Chờ duyệt</span>
                                                @elseif($refund->status == 'approved')
                                                    <span class="badge bg-primary">Đã duyệt</span>
                                                @elseif($refund->status == 'refunded')
                                                    <span class="badge bg-success">Đã hoàn tiền</span>
                                                @else
                                                    <span class="badge bg-danger">Từ chối</span>
                                                @endif
                                            </p>

                                            {{-- LÝ DO CHÍNH --}}
                                            <div class="mb-3">
                                                <div class="fw-semibold mb-2">Lý do hoàn tiền:</div>
                                                <div class="border rounded-3 p-3 bg-light small" style="line-height:1.7;">
                                                    {{ $mainReason ?: '---' }}
                                                </div>
                                            </div>

                                            {{-- CHI TIẾT BIẾN THỂ --}}
                                            <div class="mb-3">
                                                <div class="fw-semibold mb-2">Chi tiết sản phẩm hoàn:</div>

                                                @if($refund->items->count())
                                                    <div class="border rounded-3 overflow-hidden">
                                                        @foreach($refund->items as $item)
    @php
        $variant = $item->variant;

        $image = $variant?->image_path;

        $variantCode = $variant->sku
            ?? $variant->code
            ?? $variant->variant_code
            ?? ('BT' . $variant->id);

        $rawCondition = strtolower(trim((string) ($item->pivot->condition_status ?? '')));

        $conditionText = match($rawCondition) {
            'sealed', 'intact', 'new', 'con_nguyen', 'connguyenseal' => 'Còn nguyên seal',
            'broken', 'damaged', 'vo', 'bi_vo', 'bivo' => 'Bị vỡ',
            default => 'Không xác định',
        };

        $conditionClass = match($rawCondition) {
            'sealed', 'intact', 'new', 'con_nguyen', 'connguyenseal' => 'bg-success-subtle text-success border',
            'broken', 'damaged', 'vo', 'bi_vo', 'bivo' => 'bg-danger-subtle text-danger border',
            default => 'bg-secondary',
        };
    @endphp

    <div class="d-flex gap-3 p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
        <div style="width:72px;min-width:72px;">
            @if($image)
                <img
                    src="{{ asset('storage/' . ltrim($image, '/')) }}"
                    alt="variant-image"
                    class="img-fluid rounded border"
                    style="width:72px;height:72px;object-fit:cover;"
                >
            @else
                <div class="d-flex align-items-center justify-content-center rounded border bg-light text-muted"
                     style="width:72px;height:72px;font-size:12px;">
                    No image
                </div>
            @endif
        </div>

        <div class="flex-grow-1">
            <div class="fw-semibold mb-1">
                {{ $variant->product->name ?? 'Sản phẩm' }}
            </div>

            <div class="small text-muted mb-1">
                Mã biến thể: <span class="fw-semibold text-dark">{{ $variantCode }}</span>
            </div>

            @if(!empty($variant->attribute_name) || !empty($variant->attribute_value))
                <div class="small text-muted mb-1">
                    Phân loại: {{ $variant->attribute_name }}: {{ $variant->attribute_value }}
                </div>
            @endif

            <div class="small text-muted mb-1">
                Số lượng hoàn: {{ $item->pivot->quantity ?? 1 }}
            </div>

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
        <div class="fw-semibold mb-2">Ghi chú admin:</div>

        <div class="border rounded-3 p-3 bg-light">
            <div class="d-flex flex-wrap gap-2">
                @foreach($adminNotes as $note)
                    <span class="px-3 py-2 rounded-pill border bg-white text-muted"
                          style="font-size:13px;">
                        {{ $note }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>
@endif
                                            <h6 class="mb-2">Hình ảnh / video minh chứng</h6>

                                            <div class="d-flex flex-wrap gap-2">
                                                @forelse($refund->media as $media)
                                                    @if(Str::endsWith(strtolower($media->file_path), ['jpg', 'jpeg', 'png', 'webp']))
                                                        <img
                                                            src="{{ asset('storage/' . $media->file_path) }}"
                                                            width="120"
                                                            height="120"
                                                            class="refund-preview"
                                                            style="object-fit:cover;border-radius:6px;cursor:pointer"
                                                            alt="refund-media">
                                                    @else
                                                        <video
                                                            width="200"
                                                            class="refund-preview"
                                                            style="border-radius:6px;cursor:pointer"
                                                            muted>
                                                            <source src="{{ asset('storage/' . $media->file_path) }}">
                                                        </video>
                                                    @endif
                                                @empty
                                                    <p class="text-muted mb-0">
                                                        Không có hình minh chứng
                                                    </p>
                                                @endforelse
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    Không có yêu cầu hoàn tiền
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <div class="mt-3">
        {{ $refunds->links('vendor.pagination.custom-blue') }}
    </div>

</div>

{{-- PREVIEW MEDIA MODAL --}}
<div class="modal fade" id="previewMediaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-0">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">
                <img
                    id="previewImage"
                    style="max-width:100%;max-height:75vh;display:none;border-radius:8px;"
                    alt="preview-image">

                <video
                    id="previewVideo"
                    controls
                    style="max-width:100%;max-height:75vh;display:none;border-radius:8px;">
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