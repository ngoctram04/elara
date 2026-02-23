<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;

class OrderController extends Controller
{
    /**
     * Danh sách đơn hàng
     */
    public function index(Request $request)
    {
        $query = Order::with('user');

        // Lọc theo trạng thái (nếu có)
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(15);

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Chi tiết đơn hàng
     */
    public function show($id)
    {
        $order = Order::with([
            'user',
            'items.variant.product'
        ])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Cập nhật trạng thái đơn
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer'
        ]);

        $order = Order::with('items.variant')->findOrFail($id);

        $oldStatus = $order->status;
        $newStatus = (int)$request->status;

        // Nếu không thay đổi thì bỏ qua
        if ($oldStatus == $newStatus) {
            return back()->with('info', 'Không có thay đổi.');
        }

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------
            | TRẠNG THÁI LOGIC
            | 1: Pending (đã trừ tồn)
            | 2: Đang giao
            | 3: Hoàn thành → cộng đã bán
            | 4: Huỷ → hoàn tồn (nếu trước đó chưa huỷ)
            |--------------------------------------------------
            */

            // Hoàn tồn khi chuyển sang huỷ
            if (
                $newStatus == Order::STATUS_CANCELLED &&
                $oldStatus != Order::STATUS_CANCELLED
            ) {

                foreach ($order->items as $item) {
                    $variant = $item->variant;
                    $variant->increment('stock_quantity', $item->quantity);
                }
            }

            // Cộng đã bán khi hoàn thành
            if (
                $newStatus == Order::STATUS_COMPLETED &&
                $oldStatus != Order::STATUS_COMPLETED
            ) {

                foreach ($order->items as $item) {
                    $variant = $item->variant;

                    // nếu có cột sold_quantity
                    if (isset($variant->sold_quantity)) {
                        $variant->increment('sold_quantity', $item->quantity);
                    }
                }
            }

            $order->update([
                'status' => $newStatus
            ]);

            DB::commit();

            return back()->with('success', 'Cập nhật trạng thái thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Cập nhật thất bại!');
        }
    }
}