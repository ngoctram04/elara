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
            // Sản phẩm
            'items.variant.product',

            // Ảnh variant
            'items.variant.mainImage',

            // ⭐ Review của từng item
            'items.review',

            // Người huỷ (nếu có)
            'cancelledByUser'
        ])
        ->where('user_id', Auth::id())
        ->latest()
        ->paginate(10);

        return view('frontend.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with([
            // =========================
            // SẢN PHẨM
            // =========================
            'items.variant.product',

            // Ảnh chính variant
            'items.variant.mainImage',

            // =========================
            // REVIEW
            // =========================
            // Review của từng item
            'items.review',

            // Media của review (ảnh/video)
            'items.review.media',

            // =========================
            // THÔNG TIN KHÁC
            // =========================
            'cancelledByUser'
        ])
        ->where('id', $id)
        ->where('user_id', Auth::id())
        ->firstOrFail();

        return view('frontend.orders.show', compact('order'));
    }


    /**
     * Huỷ đơn hàng (chỉ khi status = pending)
     */
    public function cancel($id)
    {
        $order = Order::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Chỉ cho huỷ khi đang xử lý
        if ($order->status != Order::STATUS_PENDING) {
            return back()->with('error', 'Đơn hàng này không thể huỷ.');
        }

        DB::beginTransaction();

        try {

            // ================= XỬ LÝ THANH TOÁN =================
            $paymentStatus = $order->payment_status;

            // Nếu đã thanh toán VNPay → chuyển hoàn tiền
            if (
                $order->payment_method === 'vnpay' &&
                $order->payment_status == Order::PAYMENT_PAID
            ) {
                $paymentStatus = Order::PAYMENT_REFUNDED;
            }

            // ================= CẬP NHẬT ĐƠN =================
            // Không hoàn kho ở đây!
            // Model Order sẽ tự hoàn kho khi status = CANCELLED
            $order->update([
                'status' => Order::STATUS_CANCELLED,
                'payment_status' => $paymentStatus,
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

                // Bỏ qua nếu không còn hàng
                if (!$variant || $variant->stock_quantity <= 0) {
                    continue;
                }

                // Không vượt quá tồn kho
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