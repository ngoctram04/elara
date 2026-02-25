<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Cart;

class OrderController extends Controller
{
    /**
     * Danh sách đơn của user
     */
    public function index()
    {
        $orders = Order::with([
            'items.variant.product',
            'items.variant.mainImage',
            'cancelledByUser' // NEW
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
            'items.variant.mainImage',
            'cancelledByUser' // NEW
        ])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('frontend.orders.show', compact('order'));
    }


    /**
     * Huỷ đơn hàng (chỉ khi status = 1)
     */
    public function cancel($id)
    {
        $order = Order::with('items.variant')
        ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Chỉ cho huỷ khi đang xử lý
        if ($order->status != Order::STATUS_PENDING) {
            return back()->with('error', 'Đơn hàng này không thể huỷ.');
        }

        DB::beginTransaction();

        try {

            // ================= HOÀN TỒN KHO =================
            foreach ($order->items as $item) {
                if ($item->variant) {
                    $item->variant->increment(
                        'stock_quantity',
                        $item->quantity
                    );
                }
            }

            // ================= CẬP NHẬT ĐƠN =================
            $order->update([
                'status' => Order::STATUS_CANCELLED,
                'cancelled_by' => 'customer',
                'cancelled_by_user_id' => Auth::id(),
                'cancelled_at' => now()
            ]);

            DB::commit();

            return redirect()
                ->route('orders.show', $order->id)
                ->with('success', 'Huỷ đơn hàng thành công.');
        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', 'Huỷ đơn thất bại.');
        }
    }


    /**
     * Mua lại đơn (Reorder)
     */
    public function reorder($id)
    {
        $userId = Auth::id();

        $order = Order::with('items.variant')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        DB::beginTransaction();

        try {

            foreach ($order->items as $item) {

                $variant = $item->variant;

                if (!$variant || $variant->stock_quantity <= 0) {
                    continue;
                }

                $quantity = min($item->quantity, $variant->stock_quantity);

                $cart = Cart::where('user_id', $userId)
                    ->where('variant_id', $variant->id)
                    ->first();

                if ($cart) {
                    $newQty = min(
                        $cart->quantity + $quantity,
                        $variant->stock_quantity
                    );

                    $cart->update([
                        'quantity' => $newQty
                    ]);
                } else {
                    Cart::create([
                        'user_id' => $userId,
                        'variant_id' => $variant->id,
                        'quantity' => $quantity
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('cart.index')
                ->with('success', 'Đã thêm sản phẩm vào giỏ hàng!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Mua lại thất bại.');
        }
    }
}