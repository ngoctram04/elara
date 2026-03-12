<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Promotion;
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
    | Tìm kiếm mã đơn / tên / SĐT
    |--------------------------------
    */
        if ($request->filled('keyword')) {

            $keyword = trim($request->keyword);

            // nếu nhập dạng #59
            if (str_starts_with($keyword, '#')) {

                $id = str_replace('#', '', $keyword);

                $query->where('id', $id);
            }
            // nếu nhập số (59)
            elseif (is_numeric($keyword)) {

                $query->where('id', $keyword);
            }
            // nếu nhập chữ → tìm tên hoặc sđt
            else {

                $query->where(function ($q) use ($keyword) {

                    $q->where('receiver_name', 'like', "%{$keyword}%")
                        ->orWhere('receiver_phone', 'like', "%{$keyword}%")
                        ->orWhereHas('user', function ($u) use ($keyword) {
                            $u->where('name', 'like', "%{$keyword}%");
                        });
                });
            }
        }

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

        /*
    |--------------------------------
    | Danh sách đơn
    |--------------------------------
    */

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

                        $variant = $item->variant;

                        $before = $variant->stock_quantity;

                        // tăng số lượng đã bán
                        $variant->increment('sold_quantity', $item->quantity);

                        \App\Models\InventoryLog::create([
                            'variant_id' => $variant->id,
                            'type' => 'order', // ⚠️ sửa ở đây
                            'quantity_change' => -$item->quantity,
                            'stock_before' => $before,
                            'stock_after' => $variant->stock_quantity,
                            'reference_type' => 'order',
                            'reference_id' => $order->id
                        ]);
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
                    $pointsTotal = $user->loyalty_points;

                    if ($pointsTotal >= 10000) {
                        $user->member_level = 'diamond';
                    } elseif ($pointsTotal >= 3000) {
                        $user->member_level = 'gold';
                    } elseif ($pointsTotal >= 1000) {
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

            $order->update([
                'status' => Order::STATUS_CANCELLED,
                'cancel_reason' => $request->cancel_reason,
                'cancelled_by' => 'admin',
                'cancelled_by_user_id' => Auth::id(),
                'cancelled_at' => now()
            ]);

            DB::commit();

            return back()->with('success', 'Đã huỷ đơn hàng.');
        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', 'Huỷ đơn thất bại.');
        }
    }
}