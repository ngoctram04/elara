<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Cart;
use App\Models\Promotion;
class OrderController extends Controller
{

    /**
     * Danh sách đơn hàng + lọc + tìm kiếm
     */
    public function index(Request $request)
    {
        $query = Order::with([
            'items.variant.product',
            'items.variant.mainImage',
            'items.review',
            'cancelledByUser',
            'refundRequest' // ⭐ thêm relation refund
        ])
        ->where('user_id', Auth::id())
        ->latest();

        // 🔎 Tìm kiếm mã đơn
        if ($request->filled('keyword')) {
            $query->where('id', 'like', '%' . $request->keyword . '%');
        }

        // 🧾 Lọc trạng thái
        if ($request->filled('status')) {

            switch ($request->status) {

                case 'processing':
                    $query->whereIn('status', [1, 2]);
                    break;

                case 'shipping':
                    $query->where('status', 2);
                    break;

                case 'completed':
                    $query->where('status', 3);
                    break;

                case 'cancelled':
                    $query->where('status', 4);
                    break;

                    // ⭐ ĐỔI / TRẢ
                case 'return':
                    $query->whereHas('refundRequest');
                    break;
            }
        }

        $orders = $query->paginate(10);

        return view('frontend.orders.index', compact('orders'));
    }



    /**
     * Chi tiết đơn hàng
     */
    public function show($id)
    {
        $order = Order::with([
            'items.variant.product',
            'items.variant.mainImage',
            'items.review',
            'items.review.media',
            'cancelledByUser',
            'refundRequest'
        ])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('frontend.orders.show', compact('order'));
    }



    /**
     * Huỷ đơn hàng
     */
    public function cancel(Request $request, $id)
    {
        $order = Order::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!$order->canCancel()) {
            return back()->with('error', 'Đơn hàng này không thể huỷ.');
        }

        DB::beginTransaction();

        try {

            $paymentStatus = $order->payment_status;

            /**
             * Nếu thanh toán VNPay thì chuyển sang trạng thái hoàn tiền
             */
            if (
                $order->payment_method === 'vnpay' &&
                $order->payment_status == Order::PAYMENT_PAID
            ) {
                $paymentStatus = Order::PAYMENT_REFUNDED;
            }

            /**
             * Model Order sẽ tự rollback tồn kho
             */
            $order->update([
                'status' => Order::STATUS_CANCELLED,
                'payment_status' => $paymentStatus,

                'cancel_reason' => $request->cancel_reason,
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
     * Mua lại đơn hàng
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

                // bỏ qua nếu sản phẩm không tồn tại hoặc hết hàng
                if (!$variant || $variant->stock_quantity <= 0) {
                    continue;
                }

                // không vượt quá tồn kho
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