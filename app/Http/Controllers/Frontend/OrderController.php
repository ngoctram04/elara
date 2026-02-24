<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class OrderController extends Controller
{
    /**
     * Danh sách đơn của user
     */
    public function index()
    {
        $orders = Order::with([
            'items.variant.product',
            'items.variant.mainImage' // ảnh chính của variant
        ])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('frontend.orders.index', compact('orders'));
    }


    /**
     * Chi tiết đơn
     */
    public function show($id)
    {
        $order = Order::with([
            'items.variant.product',
            'items.variant.mainImage' // load ảnh variant
        ])
            ->where('id', $id)
            ->where('user_id', Auth::id()) // chỉ xem đơn của mình
            ->firstOrFail();

        return view('frontend.orders.show', compact('order'));
    }


    /**
     * Huỷ đơn hàng (chỉ khi status = 1)
     */
    public function cancel($id)
    {
        $order = Order::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Chỉ cho huỷ khi đang xử lý
        if ($order->status != 1) {
            return redirect()
                ->back()
                ->with('error', 'Đơn hàng này không thể huỷ.');
        }

        // Cập nhật trạng thái
        $order->update([
            'status' => 4
        ]);

        /**
         * Nếu Model Order đã có event:
         * updated -> rollback tồn kho
         * thì không cần xử lý thêm
         */

        return redirect()
            ->route('orders.show', $order->id)
            ->with('success', 'Huỷ đơn hàng thành công.');
    }
}