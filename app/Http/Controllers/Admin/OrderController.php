<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Promotion;
use App\Notifications\SystemNotification;
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

            // nhập dạng #59
            if (str_starts_with($keyword, '#')) {

                $id = str_replace('#', '', $keyword);

                $query->where('id', $id);
            }

            // nhập số 59
            elseif (is_numeric($keyword)) {

                $query->where('id', $keyword);
            }

            // nhập chữ → tìm tên / sđt
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
    | Lọc trạng thái thanh toán
    |--------------------------------
    */

        if ($request->filled('payment_status')) {

            switch ($request->payment_status) {

                    /*
            =========================
            ĐÃ THANH TOÁN
            COD đã giao OR VNPAY paid
            =========================
            */
                case 'paid':

                    $query->where(function ($q) {

                        // VNPAY đã thanh toán
                        $q->where(function ($sub) {

                            $sub->where('payment_method', 'vnpay')
                            ->where('payment_status', Order::PAYMENT_PAID);
                        })

                            // COD đã giao
                            ->orWhere(function ($sub) {

                                $sub->where('payment_method', 'cod')
                                    ->where('status', Order::STATUS_COMPLETED);
                            });
                    });

                    break;


                    /*
            =========================
            CHƯA THANH TOÁN
            COD chưa giao OR VNPAY unpaid
            =========================
            */

                case 'unpaid':

                    $query->where(function ($q) {

                        // COD chưa giao
                        $q->where(function ($sub) {

                            $sub->where('payment_method', 'cod')
                                ->where('status', '!=', Order::STATUS_COMPLETED);
                        })

                            // VNPAY chưa thanh toán
                            ->orWhere(function ($sub) {

                                $sub->where('payment_method', 'vnpay')
                                ->where('payment_status', Order::PAYMENT_UNPAID);
                            });
                    });

                    break;


                    /*
            =========================
            ĐÃ HOÀN TIỀN
            =========================
            */

                case 'refunded':

                    $query->where('payment_status', Order::PAYMENT_REFUNDED);

                    break;


                    /*
            =========================
            THANH TOÁN THẤT BẠI
            =========================
            */

                case 'failed':

                    $query->where('payment_status', Order::PAYMENT_FAILED);

                    break;
            }
        }


        /*
    |--------------------------------
    | Sắp xếp
    |--------------------------------
    */

        if ($request->sort == 'oldest') {

            $query->oldest();
        } else {

            $query->latest();
        }


        /*
    |--------------------------------
    | Danh sách đơn
    |--------------------------------
    */

        $orders = $query
            ->paginate(7)
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
     * Cập nhật trạng thái:
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer'
        ]);

        $order = Order::with(['items.variant', 'user'])->findOrFail($id);

        $oldStatus = $order->status;
        $newStatus = (int)$request->status;

        // Đơn đã huỷ
        if ($oldStatus == Order::STATUS_CANCELLED) {
            return back()->with('error', 'Đơn đã huỷ không thể thay đổi.');
        }

        // Không cho chuyển ngược
        if ($newStatus <= $oldStatus) {
            return back()->with('error', 'Không thể chuyển trạng thái ngược.');
        }

        // Không cho nhảy trạng thái
        if ($newStatus - $oldStatus > 1) {
            return back()->with('error', 'Phải chuyển trạng thái theo thứ tự.');
        }

        DB::beginTransaction();

        try {

            /*
        |--------------------------------------------------
        | 1 → 2 (bắt đầu giao)
        |--------------------------------------------------
        */
            /*
|--------------------------------------------------
| 1 → 2 (bắt đầu giao)
|--------------------------------------------------
*/
            // ❌ KHÔNG LÀM GÌ Ở ĐÂY
            // (đã trừ kho ở checkout rồi, không tăng sold ở bước này)



            /*
|--------------------------------------------------
| 2 → 3 (đã giao)
|--------------------------------------------------
*/
            if ($oldStatus == Order::STATUS_PROCESSING && $newStatus == Order::STATUS_COMPLETED) {

                $order->delivered_at = now();

                /*
    ==========================================
    ✅ TĂNG SỐ LƯỢNG ĐÃ BÁN (ĐÚNG CHỖ)
    ==========================================
    */
                foreach ($order->items as $item) {

                    if ($item->variant) {

                        $variant = $item->variant;

                        $variant->increment('sold_quantity', $item->quantity);
                    }
                }

                /*
    ==========================================
    ✅ CỘNG ĐIỂM KHÁCH HÀNG
    ==========================================
    */
                $user = $order->user;

                if ($user) {

                    // 1.000đ = 1 điểm
                    $points = floor($order->grand_total / 1000);

                    $user->loyalty_points += $points;

                    /*
        ======================================
        CẬP NHẬT HẠNG
        ======================================
        */
                    if ($user->loyalty_points >= 10000) {
                        $user->member_level = 'diamond';
                    } elseif ($user->loyalty_points >= 3000) {
                        $user->member_level = 'gold';
                    } elseif ($user->loyalty_points >= 1000) {
                        $user->member_level = 'silver';
                    } else {
                        $user->member_level = 'bronze';
                    }

                    $user->save();

                    /*
        ======================================
        LƯU LỊCH SỬ ĐIỂM
        ======================================
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
            // 🔔 THÔNG BÁO USER
            if ($order->user) {

                $title = 'Cập nhật đơn hàng';
                $message = 'Đơn #' . $order->id . ' đã được cập nhật';

                if ($newStatus == Order::STATUS_PROCESSING) {
                    $title = 'Đơn đang được giao';
                    $message = 'Đơn #' . $order->id . ' đang được giao đến bạn';
                }

                if ($newStatus == Order::STATUS_COMPLETED) {
                    $title = 'Đơn đã giao thành công';
                    $message = 'Đơn #' . $order->id . ' đã được giao thành công. Vui lòng xác nhận đã nhận hàng';
                }

                $order->user->notify(new SystemNotification([
                    'title' => $title,
                    'message' => $message,
                    'url' => route('orders.show', $order->id),
                    'type' => 'order_completed'
                ]));
            }
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
            $order->load('items.batches');

            foreach ($order->items as $item) {

                foreach ($item->batches as $batch) {

                    // ❌ tránh rollback 2 lần
                    if ($batch->is_rolled_back) continue;

                    $stock = \App\Models\StockImport::find($batch->stock_import_id);

                    if (!$stock) continue;

                    $before = $stock->remaining_quantity;

                    // ✅ hoàn lại lô
                    $stock->increment('remaining_quantity', $batch->quantity);

                    // ✅ đánh dấu rollback
                    $batch->update([
                        'is_rolled_back' => 1
                    ]);

                    // ✅ log (nếu bạn dùng log)
                    \App\Models\InventoryLog::create([
                        'variant_id' => $item->variant_id,
                        'type' => 'cancel',
                        'quantity_change' => $batch->quantity,
                        'stock_before' => $before,
                        'stock_after' => $before + $batch->quantity,
                        'reference_type' => 'order',
                        'reference_id' => $order->id
                    ]);
                }

                // 🔥 SYNC lại tồn variant (QUAN TRỌNG)
                $total = \App\Models\StockImport::where('variant_id', $item->variant_id)
                ->sum('remaining_quantity');

                \App\Models\ProductVariant::where('id', $item->variant_id)
                ->update([
                    'stock_quantity' => $total
                ]);
            }

            DB::commit();

            // 🔔 GỬI THÔNG BÁO CHO USER
            if ($order->user) {
                $order->user->notify(new SystemNotification([
                    'title' => 'Đơn hàng bị huỷ',
                    'message' => 'Đơn #' . $order->id . ' đã bị huỷ bởi cửa hàng',
                    'url' => route('orders.show', $order->id),
                    'type' => 'order_cancelled',
                    'meta' => [
                        'reason' => $request->cancel_reason
                    ]
                ]));
            }

            return back()->with('success', 'Đã huỷ đơn hàng.');
        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', 'Huỷ đơn thất bại.');
        }
    }
} 