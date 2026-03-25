@extends('layouts.frontend')

@section('title', 'Chi tiết phiếu hoàn tiền')

@section('content')
<div class="container py-4">

    <h4 class="mb-3">Phiếu yêu cầu hoàn tiền</h4>

    <div class="card shadow-sm">
        <div class="card-body">

            <p><b>Mã phiếu:</b> RH{{ str_pad($refund->id, 5, '0', STR_PAD_LEFT) }}</p>

            <p>
                <b>Đơn hàng:</b>
                <a href="{{ route('orders.show', $refund->order_id) }}">
                    #{{ $refund->order_id }}
                </a>
            </p>

            <p><b>Lý do:</b> {{ $refund->reason }}</p>

            <p><b>Tình trạng hàng:</b> {{ $refund->condition }}</p>

            <p><b>Số tiền hoàn:</b> {{ number_format($refund->amount) }}₫</p>

            <p><b>Trạng thái:</b>
                @if($refund->status == 'pending')
                    <span class="badge bg-warning">Chờ duyệt</span>
                @elseif($refund->status == 'approved')
                    <span class="badge bg-info">Đã duyệt</span>
                @elseif($refund->status == 'rejected')
                    <span class="badge bg-danger">Từ chối</span>
                @elseif($refund->status == 'refunded')
                    <span class="badge bg-success">Đã hoàn tiền</span>
                @endif
            </p>

            <p><b>Ngày gửi:</b> {{ $refund->created_at->format('d/m/Y H:i') }}</p>

            {{-- Ảnh --}}
            @if($refund->images)
                <div class="mt-3">
                    <b>Ảnh đính kèm:</b><br>
                    @foreach(json_decode($refund->images) as $img)
                        <img src="{{ asset('storage/'.$img) }}"
                             width="120"
                             class="me-2 mb-2 border rounded">
                    @endforeach
                </div>
            @endif

        </div>
    </div>

</div>
@endsection