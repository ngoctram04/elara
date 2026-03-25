@extends('layouts.frontend')
@section('title','Lịch sử đơn hàng')

@section('content')

<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => url('/')],
    ['label' => 'Lịch sử đơn hàng']
]" />

<style>
.custom-pagination-wrap{
    display:flex;
    justify-content:center;
    margin-top:24px;
}

.custom-pagination{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:12px;
    list-style:none;
    padding:0;
    margin:0;
    flex-wrap:wrap;
}

.custom-pagination li{
    margin:0;
    padding:0;
}

.custom-pagination li a,
.custom-pagination li span{
    display:flex;
    align-items:center;
    justify-content:center;
    min-width:42px;
    height:42px;
    padding:0 12px;
    border-radius:999px;
    text-decoration:none;
    font-size:16px;
    font-weight:600;
    border:none;
    background:transparent;
    color:#2563eb;
    transition:all 0.2s ease;
}

.custom-pagination li a:hover{
    background:#dbeafe;
    color:#1d4ed8;
}

.custom-pagination li.active span{
    background:#2563eb;
    color:#fff;
    box-shadow:0 8px 18px rgba(37, 99, 235, 0.22);
}

.custom-pagination li.dots span{
    min-width:auto;
    height:42px;
    padding:0 4px;
    color:#374151;
    background:transparent;
}

.custom-pagination li.disabled span{
    color:#9ca3af;
    background:transparent;
}

.custom-pagination li.arrow a,
.custom-pagination li.arrow span{
    font-size:20px;
}
body{background:#f5f6fa;}

.order-box{
    background:#fff;
    border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,.05);
    margin-bottom:20px;
}

.order-header{
    padding:12px 16px;
    border-bottom:1px solid #f0f0f0;
    display:flex;
    justify-content:space-between;
    align-items:center;
    font-size:14px;
}

.order-status{font-weight:600;}

.status-1{ color:#f39c12; }
.status-2{ color:#3498db; }
.status-3{ color:#2ecc71; }
.status-4{ color:#e74c3c; }

.order-item{
    display:flex;
    gap:12px;
    padding:14px 16px;
    border-bottom:1px solid #f7f7f7;
}

.order-item:last-child{border-bottom:none;}

.order-img{
    width:70px;
    height:70px;
    border-radius:6px;
    object-fit:cover;
    border:1px solid #eee;
}

.order-name{font-weight:600;margin-bottom:4px;}
.order-variant{font-size:13px;color:#888;}

.order-price{
    margin-left:auto;
    text-align:right;
    font-weight:600;
}

.order-footer{
    padding:14px 16px;
    border-top:1px solid #f0f0f0;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.order-total{
    font-size:18px;
    font-weight:700;
    color:#ee4d2d;
}

.btn-action{
    border-radius:6px;
    font-size:13px;
    padding:6px 14px;
    margin-left:6px;
}
.order-tabs .tab{
padding:6px 16px;
border-radius:20px;
background:#f1f5f9;
text-decoration:none;
color:#334155;
font-size:14px;
transition:.2s;
}

.order-tabs .tab:hover{
background:#e2e8f0;
}

.order-tabs .tab.active{
background:#dbeafe;
color:#2563eb;
font-weight:600;
}

.search-box .input-group-text{
background:#fff;
border-right:0;
}

.search-box .form-control{
border-left:0;
}

.btn-search{
background:#2563eb;
color:#fff;
border-radius:8px;
padding:6px 16px;
}

.btn-search:hover{
background:#1d4ed8;
color:#fff;
}

.btn-reset{
border:1px solid #cbd5e1;
border-radius:8px;
padding:6px 16px;
color:#475569;
}
.order-expand-wrap{
    border-bottom:1px solid #f7f7f7;
}

.btn-toggle-items{
    border:none;
    background:transparent;
    color:#2563eb;
    font-size:14px;
    font-weight:600;
    padding:6px 12px;
    cursor:pointer;
    transition:.2s;
}

.btn-toggle-items:hover{
    color:#1d4ed8;
    text-decoration:underline;
}
</style>

<div class="container py-4">

<h4 class="fw-bold mb-4">Lịch sử đơn hàng</h4>
<div class="order-filter card border-0 shadow-sm mb-4">
<div class="card-body">

<form method="GET" id="orderSearchForm">

<div class="row align-items-center g-3">

{{-- SEARCH --}}
<div class="col-md-5">
    <div class="input-group search-box">
        <span class="input-group-text">
            <i class="bi bi-search"></i>
        </span>

        <input
            type="text"
            name="keyword"
            value="{{ request('keyword') }}"
            class="form-control"
            placeholder="Nhập mã đơn..."
            oninput="autoSearch()">
    </div>
</div>

{{-- TABS --}}
<div class="col-md-7">
    <div class="order-tabs d-flex flex-wrap gap-2 justify-content-md-end">

        <a href="{{ route('orders.history') }}"
           class="tab {{ request('status')==''?'active':'' }}">
            Tất cả
        </a>

        <a href="{{ route('orders.history',['status'=>'processing']) }}"
           class="tab {{ request('status')=='processing'?'active':'' }}">
            Đang xử lý
        </a>

        <a href="{{ route('orders.history',['status'=>'shipping']) }}"
           class="tab {{ request('status')=='shipping'?'active':'' }}">
            Đang giao
        </a>

        <a href="{{ route('orders.history',['status'=>'completed']) }}"
           class="tab {{ request('status')=='completed'?'active':'' }}">
            Đã giao
        </a>

        <a href="{{ route('orders.history',['status'=>'cancelled']) }}"
           class="tab {{ request('status')=='cancelled'?'active':'' }}">
            Đã huỷ
        </a>

        <a href="{{ route('orders.history',['status'=>'return']) }}"
           class="tab {{ request('status')=='return'?'active':'' }}">
            Trả hàng / Hoàn tiền
        </a>

    </div>
</div>

</div>
</form>

</div>
</div>



@forelse($orders as $order)

@php
    // Tổng khách phải trả (ưu tiên grand_total)
    $payAmount = $order->grand_total
        ?? ($order->total + ($order->shipping_fee ?? 0));
@endphp

<div class="order-box">

    {{-- HEADER --}}
    <div class="order-header">
        <div>
            Mã đơn: <b>DH{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</b> |
            {{ $order->created_at->format('d/m/Y H:i') }}

            @if($order->isCompleted() && $order->delivered_at)
                <br>
                <small class="text-muted">
                    Đã giao: {{ $order->delivered_at->format('d/m/Y H:i') }}
                </small>
            @endif

            @if($order->isCancelled())
                <br>
                <small class="text-danger">
                    Huỷ bởi:
                    {{ $order->cancelled_by == 'admin' ? 'Admin' : 'Khách' }}
                </small>
            @endif
        </div>

        <div class="order-status">

@if($order->status == 1)
<span class="status-1">Đang xử lý</span>

@elseif($order->status == 2)
<span class="status-2">Đang giao</span>

@elseif($order->status == 3 && !$order->customer_confirmed)
<span class="status-2">Đã giao - chờ xác nhận</span>

@elseif($order->status == 3 && $order->customer_confirmed)
<span class="status-3">Hoàn tất</span>

@elseif($order->status == 4)
<span class="status-4">Đã huỷ</span>
@endif

</div>
    </div>

@foreach($order->items as $index => $item)
    @php
        $variant = $item->variant;
        $product = $variant->product ?? null;

        $image = optional($variant->mainImage)->image_path
            ?? optional($product->mainImage)->image_path;

        $imageUrl = $image
            ? asset('storage/'.$image)
            : asset('images/no-image.png');
    @endphp

    <div class="order-item {{ $index >= 1 ? 'extra-item d-none' : '' }}">
        <img src="{{ $imageUrl }}" class="order-img">

        <div style="flex:1">
            <div class="order-name">
                {{ $product->name ?? 'Sản phẩm' }}
            </div>

            <div class="order-variant">
                {{ $variant->attribute_value ?? '' }}
                x{{ $item->quantity }}
            </div>

            @if($order->status == 3 && $order->customer_confirmed)
                @if(!$item->review)
                    <a href="{{ route('reviews.create', $item->id) }}"
                       class="btn btn-warning btn-sm mt-2">
                        Đánh giá
                    </a>
                @else
                    <span class="badge bg-success mt-2">
                        Đã đánh giá
                    </span>
                @endif
            @endif
        </div>

        <div class="order-price">
            {{ number_format($item->price) }}đ
        </div>
    </div>
@endforeach

@if($order->items->count() > 1)
    <div class="order-expand-wrap text-center py-2">
        <button type="button"
                class="btn-toggle-items"
                data-more="Xem thêm {{ $order->items->count() - 1 }} sản phẩm"
                data-less="Thu gọn">
            Xem thêm {{ $order->items->count() - 1 }} sản phẩm
        </button>
    </div>
@endif

    {{-- FOOTER --}}
    <div class="order-footer">

        <div>
            Thanh toán: <b>{{ $order->payment_method_name }}</b>
{{-- Sinh nhật --}}
@if($order->birthday_discount > 0)
    <div class="text-success">
        Ưu đãi sinh nhật:
        -{{ number_format($order->birthday_discount) }}đ
    </div>
@endif
            {{-- Voucher --}}
@if($order->voucher_discount > 0)
    <div class="text-success">
        Voucher:
        -{{ number_format($order->voucher_discount) }}đ
    </div>
@endif



            @if($order->shipping_fee > 0)
                <br>
                <small class="text-muted">
                    Phí ship: {{ number_format($order->shipping_fee) }}đ
                </small>
            @endif
        </div>

        <div class="text-end">
            Tổng thanh toán:
            <span class="order-total">
                {{ number_format($payAmount) }}đ
            </span>

            <div class="mt-2">

                <a href="{{ route('orders.show',$order->id) }}"
                   class="btn btn-outline-secondary btn-sm btn-action">
                    Chi tiết
                </a>
                @if($order->refundRequest)
    <a href="{{ route('refund.show', $order->refundRequest->id) }}"
       class="btn btn-outline-danger btn-sm btn-action">
        Xem phiếu hoàn tiền
    </a>
@endif
                {{-- KHÁCH XÁC NHẬN ĐÃ NHẬN HÀNG --}}
@if($order->status == 3 && !$order->customer_confirmed)
<form action="{{ route('orders.confirmReceived',$order->id) }}"
      method="POST"
      class="d-inline">
@csrf

<button type="submit"
        class="btn btn-success btn-sm btn-action btn-confirm">
    Đã nhận hàng
</button>

</form>

@endif
                @if($order->canCancel())
                    <form action="{{ route('orders.cancel',$order->id) }}"
      method="POST"
      class="d-inline cancel-form">
    @csrf
    @method('PUT')

    <input type="hidden" name="cancel_reason" class="cancel-reason">

    <button type="button"
            class="btn btn-outline-danger btn-sm btn-action btn-cancel">
        Huỷ đơn
    </button>
</form>
                @endif

                @if($order->isCompleted() || $order->isCancelled())
                    <form action="{{ route('orders.reorder',$order->id) }}"
                          method="POST"
                          style="display:inline;">
                        @csrf
                        <button type="submit"
                                class="btn btn-outline-primary btn-sm btn-action">
                            Mua lại
                        </button>
                    </form>
                @endif
                {{-- YÊU CẦU TRẢ HÀNG / HOÀN TIỀN --}}
                @if($order->isCompleted() && !$order->refundRequest)

<button type="button"
        class="btn btn-outline-danger btn-sm btn-action btn-refund"
        data-url="{{ route('refund.create',$order->id) }}">
   Trả hàng / Hoàn tiền
</button>

@endif
@if($order->refundRequest)

    @if($order->refundRequest->status == 'pending')

        <span class="badge bg-warning">
            Đang chờ xử lý hoàn tiền
        </span>

    @elseif($order->refundRequest->status == 'approved')

        <span class="badge bg-primary">
            Yêu cầu hoàn tiền đã được duyệt
        </span>

    @elseif($order->refundRequest->status == 'refunded')

        <span class="badge bg-success">
            Đã hoàn tiền
        </span>

    @elseif($order->refundRequest->status == 'rejected')

        <span class="badge bg-danger">
            Yêu cầu hoàn tiền bị từ chối
        </span>

        @if($order->refundRequest->admin_note)
        <div class="text-danger small mt-1">
            Lý do: {{ $order->refundRequest->admin_note }}
        </div>
        @endif

    @endif

@endif
            </div>
        </div>

    </div>

</div>

@empty
<div class="alert alert-info">
    Bạn chưa có đơn hàng nào.
</div>
@endforelse

<div class="mt-4">
    {{ $orders->withQueryString()->links('vendor.pagination.custom-blue') }}
</div>

</div>
@endsection
@push('scripts')

{{-- SweetAlert --}}

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

// =============================
// AUTO SEARCH
// =============================
let searchTimer;

function autoSearch(){
    clearTimeout(searchTimer);

    searchTimer = setTimeout(function(){
        document.getElementById('orderSearchForm').submit();
    },500);
}


// =============================
// XÁC NHẬN HUỶ ĐƠN
// =============================
document.querySelectorAll('.btn-cancel').forEach(function(button){

button.addEventListener('click', function () {

let form = this.closest('.cancel-form');
let input = form.querySelector('.cancel-reason');

Swal.fire({
title: 'Huỷ đơn hàng',
input: 'textarea',
inputLabel: 'Lý do huỷ đơn',
inputPlaceholder: 'Ví dụ: đặt nhầm, muốn đổi sản phẩm...',
icon: 'warning',
showCancelButton: true,
confirmButtonText: 'Xác nhận huỷ',
cancelButtonText: 'Không',
confirmButtonColor: '#e74c3c',
cancelButtonColor: '#6c757d',
reverseButtons: true,
inputValidator: (value) => {
    if (!value) {
        return 'Bạn cần nhập lý do huỷ!';
    }
}

}).then((result) => {

if (result.isConfirmed) {

input.value = result.value;

form.submit();

}

});

});

});


// =============================
// XÁC NHẬN ĐÃ NHẬN HÀNG
// =============================
document.querySelectorAll('.btn-confirm').forEach(function(btn){

btn.addEventListener('click',function(e){

e.preventDefault();

let form = this.closest('form');

Swal.fire({
title: 'Xác nhận đã nhận hàng?',
text: 'Sau khi xác nhận, đơn hàng sẽ hoàn tất.',
icon: 'question',
showCancelButton: true,
confirmButtonText: 'Đã nhận',
cancelButtonText: 'Chưa',
confirmButtonColor: '#2ecc71',
cancelButtonColor: '#6c757d'
}).then((result)=>{

if(result.isConfirmed){
form.submit();
}

});

});

});

</script>
<script>
// =============================
// POPUP ĐIỀU KHOẢN TRẢ HÀNG
// =============================
document.addEventListener('DOMContentLoaded', function () {

    const refundButtons = document.querySelectorAll('.btn-refund');

    refundButtons.forEach(function (btn) {

        btn.addEventListener('click', function () {

            const url = this.getAttribute('data-url');

            if (!url) return;

            Swal.fire({
                title: 'Điều khoản trả hàng / hoàn tiền',
                html: `
    <div style="text-align:left;font-size:14px;line-height:1.6">
        <p><b>Vui lòng đọc kỹ trước khi tiếp tục:</b></p>

        <p>• Chỉ áp dụng cho đơn hàng đã giao thành công</p>

        <p>• Khách hàng có thể gửi yêu cầu trả hàng / hoàn tiền khi sản phẩm có vấn đề hoặc không đúng như mô tả</p>

        <p>• Nếu sản phẩm còn nguyên seal, shop sẽ kiểm tra để xem xét nhập lại kho theo quy định</p>

        <p>• Nếu sản phẩm bị vỡ, hư hỏng hoặc không đủ điều kiện nhập lại kho, shop vẫn xem xét hoàn tiền theo chính sách nhưng sẽ không hoàn kho</p>

        <p>• Vui lòng cung cấp hình ảnh/video rõ ràng để xác minh tình trạng sản phẩm</p>

        <p>• Shop sẽ kiểm tra và phản hồi kết quả trong thời gian sớm nhất</p>

        <p style="margin-top:10px;color:#666">
            Khi nhấn "Đồng ý & Tiếp tục", bạn xác nhận thông tin cung cấp là chính xác.
        </p>
    </div>
`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Đồng ý & Tiếp tục',
                cancelButtonText: 'Không đồng ý',
                confirmButtonColor: '#1677a0',
                cancelButtonColor: '#6c757d',
                allowOutsideClick: false,
                reverseButtons: true
            }).then((result) => {

                if (result.isConfirmed) {
                    window.location.href = url;
                }

            });

        });

    });

});

</script>
{{-- THÔNG BÁO SUCCESS --}}
@if(session('success'))

<script>
Swal.fire({
icon: 'success',
title: 'Thành công',
text: '{{ session('success') }}',
confirmButtonColor: '#3085d6'
});
</script>

@endif

{{-- THÔNG BÁO ERROR --}}
@if(session('error'))
<script>
Swal.fire({
    icon: 'error',
    title: 'Lỗi',
    text: '{{ session('error') }}',
    confirmButtonColor: '#d33'
});
</script>
@endif

<script>
document.querySelectorAll('.btn-toggle-items').forEach(function(button){
    button.addEventListener('click', function(){
        const orderBox = this.closest('.order-box');
        const extraItems = orderBox.querySelectorAll('.extra-item');
        const isHidden = Array.from(extraItems).some(item => item.classList.contains('d-none'));

        if (isHidden) {
            extraItems.forEach(item => item.classList.remove('d-none'));
            this.textContent = this.dataset.less;
        } else {
            extraItems.forEach(item => item.classList.add('d-none'));
            this.textContent = this.dataset.more;
        }
    });
});
</script>

@endpush
