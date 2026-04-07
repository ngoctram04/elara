@extends('layouts.admin')

@section('title', 'Chi tiết khách hàng')

@section('content')
<style>
    .customer-detail-page{
        font-size:14px;
        color:#334155;
    }

    .customer-card{
        border-radius:16px;
        overflow:hidden;
        border:1px solid #edf2f7;
    }

    .customer-section-title{
        font-size:16px;
        font-weight:600;
        color:#1e293b;
        margin-bottom:14px;
    }

    .customer-main-title{
        font-size:18px;
        font-weight:600;
        color:#1e293b;
    }

    .customer-subtext{
        font-size:13px;
        color:#64748b;
    }

    .customer-info-box{
        background:#f8fafc;
        border:1px solid #e9eef5;
        border-radius:12px;
        padding:14px 16px;
        height:100%;
        transition:all .2s ease;
    }

    .customer-info-box:hover{
        background:#ffffff;
        border-color:#dbe5f0;
    }

    .customer-info-label{
        font-size:12px;
        color:#64748b;
        margin-bottom:4px;
    }

    .customer-info-value{
        font-size:14px;
        color:#1e293b;
        font-weight:500;
        word-break:break-word;
    }

    .customer-avatar{
        width:110px;
        height:110px;
        object-fit:cover;
        border-radius:50%;
        border:3px solid #fff;
        box-shadow:0 6px 18px rgba(15, 23, 42, 0.08);
    }

    .customer-name{
        font-size:17px;
        font-weight:600;
        color:#1e293b;
    }

    .status-badge,
    .member-badge{
        font-size:12px;
        font-weight:500;
        padding:6px 10px;
        border-radius:999px;
    }

    .customer-note-box{
        background:#fff7ed;
        border:1px solid #fed7aa;
        color:#9a3412;
        border-radius:12px;
        padding:12px 14px;
        font-size:13px;
        line-height:1.6;
    }

    .customer-note-box strong{
        font-weight:600;
    }

    .table-wrap{
        border:1px solid #e9eef5;
        border-radius:14px;
        overflow:hidden;
        background:#fff;
    }

    .table{
        margin-bottom:0;
    }

    .table thead th{
        background:#f8fafc;
        color:#475569;
        font-size:13px;
        font-weight:600;
        border-bottom:1px solid #e2e8f0;
        padding:13px 12px;
        white-space:nowrap;
        vertical-align:middle;
    }

    .table tbody td{
        font-size:13.5px;
        padding:13px 12px;
        border-color:#eef2f7;
        vertical-align:middle;
    }

    .table tbody tr{
        transition:all .2s ease;
    }

    .table tbody tr:hover{
        background:#f8fbff;
    }

    .review-content{
        min-width:220px;
        max-width:320px;
        white-space:normal;
        word-break:break-word;
        color:#334155;
        line-height:1.6;
    }

    .review-media-wrap{
        display:flex;
        flex-wrap:wrap;
        gap:8px;
    }

    .review-thumb{
        width:64px;
        height:64px;
        border-radius:10px;
        object-fit:cover;
        border:1px solid #e9ecef;
        background:#fff;
    }

    .review-video{
        width:150px;
        max-height:90px;
        border-radius:10px;
        border:1px solid #e9ecef;
        background:#000;
    }

    .customer-btn{
        font-size:13px;
        border-radius:10px;
        padding:7px 13px;
        font-weight:500;
    }

    .toggle-btn-wrap{
        margin-top:14px;
        text-align:center;
    }

    /* =========================
       ORDER TABLE
    ========================= */
    .order-table thead th{
        background:linear-gradient(to bottom, #f8fafc, #f1f5f9);
    }

    .order-stt{
        width:58px;
        text-align:center;
        color:#64748b;
        font-weight:600;
    }

    .order-date{
        min-width:120px;
        line-height:1.5;
    }

    .order-date .main{
        font-weight:500;
        color:#1e293b;
    }

    .order-date .sub{
        font-size:12px;
        color:#64748b;
    }

    .order-total{
        font-weight:600;
        color:#16a34a;
        white-space:nowrap;
    }

    .order-status-badge{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-width:110px;
        padding:7px 12px;
        border-radius:999px;
        font-size:12px;
        font-weight:600;
    }

    .order-status-processing{
        background:#f1f5f9;
        color:#475569;
    }

    .order-status-shipping{
        background:#e0f2fe;
        color:#0369a1;
    }

    .order-status-completed{
        background:#dcfce7;
        color:#15803d;
    }

    .order-status-cancelled{
        background:#fee2e2;
        color:#dc2626;
    }

    .order-status-returned{
        background:#fef3c7;
        color:#b45309;
    }

    .order-view-btn{
        min-width:70px;
    }

    .empty-box{
        padding:28px 12px;
        text-align:center;
        color:#94a3b8;
        font-size:13px;
    }

    @media (max-width: 768px){
        .customer-main-title{
            font-size:16px;
        }

        .customer-name{
            font-size:16px;
        }

        .customer-avatar{
            width:92px;
            height:92px;
        }

        .table thead th,
        .table tbody td{
            padding:10px;
        }

        .order-status-badge{
            min-width:unset;
            padding:6px 10px;
        }
    }
</style>

<div class="customer-detail-page">

    {{-- ================= KHÁCH HÀNG ================= --}}
    <div class="card shadow-sm border-0 mb-4 customer-card">
        <div class="card-body p-3 p-md-4">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="customer-main-title mb-1">Chi tiết khách hàng</h5>
                    <div class="customer-subtext">Thông tin tài khoản, lịch sử mua hàng và đánh giá sản phẩm</div>
                </div>

                <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-outline-secondary customer-btn">
                    Quay lại
                </a>
            </div>

            <div class="row align-items-center g-4">
                <div class="col-md-3 text-center">
                    <img
                        src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('images/default-avatar.png') }}"
                        class="customer-avatar mb-3"
                        alt="{{ $user->name }}"
                    >

                    <div class="customer-name mb-2">{{ $user->name }}</div>

                    @if($user->is_active)
                        <span class="badge bg-success-subtle text-success-emphasis border status-badge">
                            Hoạt động
                        </span>
                    @else
                        <span class="badge bg-danger-subtle text-danger-emphasis border status-badge">
                            Đang khóa
                        </span>
                    @endif
                </div>

                <div class="col-md-9">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="customer-info-box">
                                <div class="customer-info-label">Email</div>
                                <div class="customer-info-value">{{ $user->email }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="customer-info-box">
                                <div class="customer-info-label">Số điện thoại</div>
                                <div class="customer-info-value">{{ $user->phone ?? '—' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="customer-info-box">
                                <div class="customer-info-label">Ngày tham gia</div>
                                <div class="customer-info-value">{{ $user->created_at ? $user->created_at->format('d/m/Y') : '—' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="customer-info-box">
                                <div class="customer-info-label">Hạng thành viên</div>
                                <div class="customer-info-value">
                                    @switch($user->member_level)
                                        @case('bronze')
                                            <span class="badge bg-secondary member-badge">Đồng</span>
                                            @break
                                        @case('silver')
                                            <span class="badge bg-light text-dark border member-badge">Bạc</span>
                                            @break
                                        @case('gold')
                                            <span class="badge bg-warning-subtle text-dark border member-badge">Vàng</span>
                                            @break
                                        @case('diamond')
                                            <span class="badge bg-primary-subtle text-primary-emphasis border member-badge">Kim cương</span>
                                            @break
                                        @default
                                            <span class="text-muted">—</span>
                                    @endswitch
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="customer-info-box">
                                <div class="customer-info-label">Điểm hiện có</div>
                                <div class="customer-info-value">{{ number_format($user->loyalty_points ?? 0, 0, ',', '.') }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="customer-info-box">
                                <div class="customer-info-label">Tổng chi tiêu</div>
                                <div class="customer-info-value text-success">
                                    {{ number_format($totalSpent ?? 0, 0, ',', '.') }} đ
                                </div>
                            </div>
                        </div>

                        @if(!$user->is_active)
                            <div class="col-12">
                                <div class="customer-note-box">
                                    <div><strong>Trạng thái:</strong> {{ $lockStatusText ?? 'Đang khóa' }}</div>

                                    @if(!empty($user->blocked_reason))
                                        <div><strong>Lý do khóa:</strong> {{ $user->blocked_reason }}</div>
                                    @endif

                                    <div><strong>Tự động mở lại lúc:</strong> {{ $lockUntilText ?? 'Chưa xác định' }}</div>

                                    @if(!empty($remainingLockTime))
                                        <div><strong>Thời gian còn lại:</strong> {{ $remainingLockTime }}</div>
                                    @endif
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= LỊCH SỬ MUA ================= --}}
    <div class="card shadow-sm border-0 mb-4 customer-card">
        <div class="card-body p-3 p-md-4">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h6 class="customer-section-title mb-0">Lịch sử mua hàng</h6>

                @if($orders->count())
                    <div class="customer-subtext">
                        Tổng đơn hàng: <strong>{{ $orders->count() }}</strong>
                    </div>
                @endif
            </div>

            <div class="table-responsive table-wrap">
                <table class="table align-middle order-table">
                    <thead>
                        <tr>
                            <th class="text-center" width="60">STT</th>
                            <th>Mã đơn</th>
                            <th>Ngày đặt</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th class="text-center" width="100">Chi tiết</th>
                        </tr>
                    </thead>

                    <tbody id="orderTable">
                        @forelse($orders as $key => $order)
                            <tr class="{{ $key >= 5 ? 'd-none extra-order' : '' }}">
                                <td class="order-stt">{{ $key + 1 }}</td>

                                <td>
                                    <span>
                                        DH{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>

                                <td>
                                    <div class="order-date">
                                        <div class="main">{{ $order->created_at ? $order->created_at->format('d/m/Y') : '—' }}</div>
                                        <div class="sub">{{ $order->created_at ? $order->created_at->format('H:i') : '' }}</div>
                                    </div>
                                </td>

                                <td>
                                    <span class="order-total">
                                        {{ number_format($order->grand_total ?? 0, 0, ',', '.') }} đ
                                    </span>
                                </td>

                                <td>
                                    @switch((int) $order->status)
                                        @case(1)
                                            <span class="order-status-badge order-status-processing">Đang xử lý</span>
                                            @break
                                        @case(2)
                                            <span class="order-status-badge order-status-shipping">Đang giao</span>
                                            @break
                                        @case(3)
                                            <span class="order-status-badge order-status-completed">Đã giao</span>
                                            @break
                                        @case(4)
                                            <span class="order-status-badge order-status-cancelled">Đã huỷ</span>
                                            @break
                                        @case(5)
                                            <span class="order-status-badge order-status-returned">Hoàn hàng</span>
                                            @break
                                        @default
                                            <span class="order-status-badge order-status-processing">{{ $order->status }}</span>
                                    @endswitch
                                </td>

                                <td class="text-center">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary customer-btn order-view-btn">
                                        Xem
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-box">
                                    Chưa có đơn hàng nào
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($orders->count() > 5)
                <div class="toggle-btn-wrap">
                    <button class="btn btn-sm btn-outline-primary customer-btn" onclick="toggleOrders()" id="toggleOrderBtn">
                        Xem tất cả
                    </button>
                </div>
            @endif

        </div>
    </div>

    {{-- ================= ĐÁNH GIÁ ================= --}}
    <div class="card shadow-sm border-0 customer-card">
        <div class="card-body p-3 p-md-4">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h6 class="customer-section-title mb-0">Sản phẩm đã đánh giá</h6>

                @if($reviews->count())
                    <div class="customer-subtext">
                        Tổng đánh giá: <strong>{{ $reviews->count() }}</strong>
                    </div>
                @endif
            </div>

            <div class="table-responsive table-wrap">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Điểm</th>
                            <th>Nội dung</th>
                            <th>Hình ảnh / Video</th>
                            <th>Ngày</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($reviews as $key => $review)
                            <tr class="{{ $key >= 5 ? 'd-none extra-review' : '' }}">
                                <td>{{ $review->product->name ?? '—' }}</td>

                                <td style="white-space:nowrap;">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $review->rating)
                                            <span style="color:#f5b301;">★</span>
                                        @else
                                            <span style="color:#cbd5e1;">☆</span>
                                        @endif
                                    @endfor
                                </td>

                                <td class="review-content">
                                    {{ $review->comment ?? '—' }}
                                </td>

                                <td style="min-width:250px;">
                                    @if(($review->images && $review->images->count()) || $review->video)
                                        <div class="review-media-wrap">
                                            @if($review->images && $review->images->count())
                                                @foreach($review->images as $img)
                                                    <a href="{{ asset('storage/' . $img->file_path) }}" target="_blank">
                                                        <img
                                                            src="{{ asset('storage/' . $img->file_path) }}"
                                                            class="review-thumb"
                                                            alt="review-image"
                                                        >
                                                    </a>
                                                @endforeach
                                            @endif

                                            @if($review->video)
                                                <video class="review-video" controls preload="metadata">
                                                    <source src="{{ asset('storage/' . $review->video->file_path) }}">
                                                </video>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">Không có</span>
                                    @endif
                                </td>

                                <td>{{ $review->created_at ? $review->created_at->format('d/m/Y') : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-box">
                                    Chưa có đánh giá nào
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($reviews->count() > 5)
                <div class="toggle-btn-wrap">
                    <button class="btn btn-sm btn-outline-primary customer-btn" onclick="toggleReviews()" id="toggleReviewBtn">
                        Xem tất cả
                    </button>
                </div>
            @endif

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function toggleOrders() {
        const rows = document.querySelectorAll('.extra-order');
        const btn = document.getElementById('toggleOrderBtn');

        rows.forEach(row => row.classList.toggle('d-none'));

        btn.innerText = btn.innerText === 'Xem tất cả'
            ? 'Thu gọn'
            : 'Xem tất cả';
    }

    function toggleReviews() {
        const rows = document.querySelectorAll('.extra-review');
        const btn = document.getElementById('toggleReviewBtn');

        rows.forEach(row => row.classList.toggle('d-none'));

        btn.innerText = btn.innerText === 'Xem tất cả'
            ? 'Thu gọn'
            : 'Xem tất cả';
    }
</script>
@endpush