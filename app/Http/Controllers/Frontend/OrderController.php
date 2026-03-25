<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Cart;
use App\Notifications\SystemNotification;
use App\Models\User;
use App\Models\RefundRequest;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Danh sách đơn hàng
     */
    public function index(Request $request)
    {
        $query = Order::with([
            'items.variant.product',
            'items.variant.mainImage',
            'items.review',
            'cancelledByUser',
            'refundRequest'
        ])
            ->where('user_id', Auth::id())
            ->latest();

        // =========================
        // TÌM KIẾM MÃ ĐƠN
        // =========================
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $numberKeyword = preg_replace('/\D/', '', $keyword);

            $query->where(function ($q) use ($keyword, $numberKeyword) {
                if (!empty($numberKeyword)) {
                    $q->where('id', 'like', '%' . $numberKeyword . '%');
                }

                $q->orWhereRaw(
                    "CONCAT('DH', LPAD(id, 5, '0')) LIKE ?",
                    ['%' . $keyword . '%']
                );
            });
        }

        // =========================
        // LỌC TRẠNG THÁI
        // =========================
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'processing':
                    $query->where('status', 1);
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

                case 'return':
                    $query->whereHas('refundRequest');
                    break;
            }
        }

        $orders = $query->paginate(5)->withQueryString();

        return view('frontend.orders.index', compact('orders'));
    }
    public function showRefund($id)
    {
        $refund = RefundRequest::with([
            'order',
            'order.user',
            'order.items',
            'order.items.variant',
            'order.items.variant.product',
            'order.items.variant.mainImage',
            'media',
            'items',
            'items.variant',
            'items.variant.product',
            'items.variant.mainImage',
        ])
        ->where('id', $id)
        ->whereHas('order', function ($q) {
            $q->where('user_id', Auth::id());
        })
        ->firstOrFail();

        return view('frontend.refund.show', compact('refund'));
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
        $order = Order::with('items.batches', 'items.variant')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!$order->canCancel()) {
            return back()->with('error', 'Đơn hàng này không thể huỷ.');
        }

        DB::beginTransaction();

        try {
            $paymentStatus = $order->payment_status;

            if (
                $order->payment_method === 'vnpay'
                && $order->payment_status == Order::PAYMENT_PAID
            ) {
                $paymentStatus = Order::PAYMENT_REFUNDED;
            }

            $order->update([
                'status' => Order::STATUS_CANCELLED,
                'payment_status' => $paymentStatus,
                'cancel_reason' => $request->cancel_reason,
                'cancelled_by' => 'customer',
                'cancelled_by_user_id' => Auth::id(),
                'cancelled_at' => now()
            ]);

            foreach ($order->items as $item) {
                $variant = \App\Models\ProductVariant::where('id', $item->variant_id)
                    ->lockForUpdate()
                    ->first();

                if (!$variant) {
                    continue;
                }

                $before = \App\Models\StockImport::where('variant_id', $item->variant_id)
                    ->sum('remaining_quantity');

                $change = 0;

                foreach ($item->batches as $batch) {
                    if ($batch->is_rolled_back) {
                        continue;
                    }

                    $stock = \App\Models\StockImport::find($batch->stock_import_id);
                    if (!$stock) {
                        continue;
                    }

                    $stock->increment('remaining_quantity', $batch->quantity);

                    $batch->update([
                        'is_rolled_back' => 1
                    ]);

                    $change += $batch->quantity;
                }

                $after = \App\Models\StockImport::where('variant_id', $item->variant_id)
                    ->sum('remaining_quantity');

                $variant->update([
                    'stock_quantity' => $after
                ]);

                \App\Models\InventoryLog::create([
                    'variant_id' => $variant->id,
                    'type' => 'cancel',
                    'quantity_change' => $change,
                    'stock_before' => $before,
                    'stock_after' => $after,
                    'reference_type' => 'order',
                    'reference_id' => $order->id
                ]);
            }

            DB::commit();

            $order->user->notify(new SystemNotification([
                'title' => 'Bạn đã huỷ đơn',
                'message' => 'Đơn #' . $order->id . ' đã được huỷ',
                'url' => route('orders.show', $order->id),
                'type' => 'order_cancelled'
            ]));

            User::where('role', 'admin')->get()
                ->each(function ($admin) use ($order) {
                    $admin->notify(new SystemNotification([
                        'title' => 'Đơn bị huỷ',
                        'message' => 'Đơn #' . $order->id . ' đã bị khách huỷ',
                        'url' => route('admin.orders.show', $order->id),
                        'type' => 'order_cancelled'
                    ]));
                });

            return redirect()
                ->route('orders.show', $order->id)
                ->with('success', 'Huỷ đơn hàng thành công.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Cancel order error: ' . $e->getMessage());

            return back()->with('error', 'Huỷ đơn thất bại.');
        }
    }

    /**
     * Khách xác nhận đã nhận hàng
     */
    public function confirmReceived($id)
    {
        $order = Order::with('user')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($order->status != Order::STATUS_COMPLETED) {
            return back()->with('error', 'Đơn hàng chưa được giao.');
        }

        if ($order->customer_confirmed) {
            return back()->with('error', 'Bạn đã xác nhận đơn này.');
        }

        DB::beginTransaction();

        try {
            $order->update([
                'customer_confirmed' => 1,
                'received_at' => now()
            ]);

            $user = $order->user;

            $user->increment('yearly_spent', (float) $order->grand_total);

            $user->refresh();
            $user->updateMemberLevel();

            DB::commit();

            return back()->with('success', 'Đã xác nhận nhận hàng.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Confirm received error: ' . $e->getMessage(), [
                'order_id' => $id,
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Xác nhận thất bại.');
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

            Log::error('Reorder error: ' . $e->getMessage());

            return back()->with('error', 'Mua lại thất bại.');
        }
    }
}