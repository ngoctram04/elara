<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class OrderController extends Controller
{
    /**
     * Danh sách đơn hàng
     */

    public function index(Request $request)
    {
        $query = Order::with(['user', 'cancelledByUser']);

        /*
    |--------------------------------
    | Lọc trạng thái đơn
    |--------------------------------
    */
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        /*
    |--------------------------------
    | Lọc trạng thái thanh toán (chuẩn)
    |--------------------------------
    */
        if ($request->filled('payment_status')) {

            switch ($request->payment_status) {

                    // =========================
                    // ĐÃ THANH TOÁN
                    // COD đã giao OR VNPAY paid
                    // =========================
                case 'paid':
                    $query->where(function ($q) {
                        $q->where(function ($sub) {
                            $sub->where('payment_method', 'vnpay')
                                ->where('payment_status', Order::PAYMENT_PAID);
                        })
                            ->orWhere(function ($sub) {
                                $sub->where('payment_method', 'cod')
                                ->where('status', Order::STATUS_COMPLETED);
                            });
                    });
                    break;

                    // =========================
                    // CHƯA THANH TOÁN
                    // COD chưa giao OR VNPAY unpaid
                    // =========================
                case 'unpaid':
                    $query->where(function ($q) {
                        $q->where(function ($sub) {
                            $sub->where('payment_method', 'cod')
                            ->where('status', '!=', Order::STATUS_COMPLETED);
                        })
                            ->orWhere(function ($sub) {
                                $sub->where('payment_method', 'vnpay')
                                    ->where('payment_status', Order::PAYMENT_UNPAID);
                            });
                    });
                    break;

                    // =========================
                    // ĐÃ HOÀN TIỀN
                    // =========================
                case 'refunded':
                    $query->where('payment_status', Order::PAYMENT_REFUNDED);
                    break;

                    // =========================
                    // THANH TOÁN THẤT BẠI
                    // =========================
                case 'failed':
                    $query->where('payment_status', Order::PAYMENT_FAILED);
                    break;
            }
        }

        $orders = $query
        ->latest()
        ->paginate(15)
        ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }
    /**
     * Chi tiết đơn hàng
     */
    public function show($id)
    {
        $order = Order::with([
            'user',
            'cancelledByUser',
            'items.variant.images',
            'items.variant.product.images'
        ])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Cập nhật trạng thái: 1 → 2 → 3
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer'
        ]);

        $order = Order::with(['items.variant', 'user'])->findOrFail($id);

        $oldStatus = $order->status;
        $newStatus = (int) $request->status;

        // Không cho sửa nếu đã huỷ hoặc đã hoàn thành
        if (in_array($oldStatus, [
            Order::STATUS_CANCELLED,
            Order::STATUS_COMPLETED
        ])) {
            return back()->with('error', 'Đơn này không thể thay đổi trạng thái.');
        }

        // Không cho chuyển ngược
        if ($newStatus <= $oldStatus) {
            return back()->with('error', 'Không thể chuyển trạng thái ngược.');
        }

        // Chỉ cho đi từng bước
        if ($newStatus - $oldStatus > 1) {
            return back()->with('error', 'Phải chuyển trạng thái theo thứ tự.');
        }

        DB::beginTransaction();

        try {

            /*
        |-----------------------------------------
        | Khi chuyển sang ĐÃ GIAO (3)
        |-----------------------------------------
        */
            if ($newStatus == Order::STATUS_COMPLETED) {

                // 1. Cộng số lượng đã bán
                foreach ($order->items as $item) {
                    if ($item->variant) {
                        $item->variant->increment('sold_quantity', $item->quantity);
                    }
                }

                $order->delivered_at = now();

                /*
            |-----------------------------------------
            | 2. CỘNG ĐIỂM THÀNH VIÊN
            |-----------------------------------------
            */
                $user = $order->user;

                if ($user) {

                    // Quy đổi: 1.000đ = 1 điểm
                    $points = floor($order->grand_total / 1000);

                    // Cộng điểm
                    $user->loyalty_points += $points;

                    // Cộng tổng chi tiêu
                    $user->total_spent += $order->grand_total;

                    /*
                |-----------------------------------------
                | 3. XÉT HẠNG THÀNH VIÊN
                |-----------------------------------------
                */
                    if ($user->total_spent >= 10000000) {
                        $user->member_level = 'diamond';
                    } elseif ($user->total_spent >= 3000000) {
                        $user->member_level = 'gold';
                    } elseif ($user->total_spent >= 1000000
                    ) {
                        $user->member_level = 'silver';
                    } else {
                        $user->member_level = 'bronze';
                    }

                    $user->save();

                    /*
                |-----------------------------------------
                | 4. LƯU LỊCH SỬ ĐIỂM
                |-----------------------------------------
                */
                    DB::table('user_point_histories')->insert([
                        'user_id' => $user->id,
                        'points' => $points,
                        'type' => 'earn',
                        'description' => 'Tích điểm từ đơn #' . $order->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            $order->status = $newStatus;
            $order->save();

            DB::commit();

            return back()->with('success', 'Cập nhật trạng thái thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Cập nhật thất bại!');
        }
    }

    /**
     * Admin huỷ đơn
     * Chỉ huỷ khi status = 1
     * Hoàn kho + hoàn tiền sẽ do Order Model xử lý
     */
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancel_reason' => 'required|string|max:500'
        ]);

        $order = Order::findOrFail($id);

        // Chỉ huỷ khi đang xử lý
        if ($order->status != Order::STATUS_PENDING) {
            return back()->with('error', 'Chỉ được huỷ khi đơn đang xử lý.');
        }

        DB::beginTransaction();

        try {

            /*
            |-----------------------------------------
            | KHÔNG hoàn kho ở đây
            | Order Model sẽ tự:
            | - Hoàn tồn kho
            | - Nếu VNPAY đã thanh toán → chuyển refunded
            |-----------------------------------------
            */
            $order->update([
                'status' => Order::STATUS_CANCELLED,
                'cancel_reason' => $request->cancel_reason,
                'cancelled_by' => 'admin',
                'cancelled_by_user_id' => Auth::id()
            ]);

            DB::commit();

            return back()->with('success', 'Đã huỷ đơn hàng.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Huỷ đơn thất bại.');
        }
    }
}