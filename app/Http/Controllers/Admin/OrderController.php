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
            $numberKeyword = preg_replace('/\D/', '', $keyword);

            $query->where(function ($q) use ($keyword, $numberKeyword) {
                $q->where('receiver_name', 'like', "%{$keyword}%")
                ->orWhereHas('user', function ($u) use ($keyword) {
                    $u->where('name', 'like', "%{$keyword}%");
                })
                ->orWhereRaw("CONCAT('DH', LPAD(id, 5, '0')) LIKE ?", ['%' . $keyword . '%']);

                if ($numberKeyword !== '') {
                    $q->orWhere('id', (int) $numberKeyword);
                }
            });
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
            'items' => function ($q) {
                $q->with([
                    'variant.images',
                    'variant.product.images',
                    'batches.stockImport'
                ]);
            }
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
        if ($newStatus == Order::STATUS_COMPLETED && !$request->hasFile('delivery_proof') && !$order->delivery_image) {
            return back()->with('error', 'Phải upload ảnh khi xác nhận đã giao');
        }
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

                // ✅ upload ảnh trước
                if ($request->hasFile('delivery_proof')) {
                    $path = $request->file('delivery_proof')->store('delivery', 'public');
                    $order->delivery_image = $path;
                }

                $order->delivered_at = now();

                /*
    ==========================================
    ✅ TĂNG SỐ LƯỢNG ĐÃ BÁN
    ==========================================
    */
                foreach ($order->items as $item) {
                    if ($item->variant) {
                        $item->variant->increment('sold_quantity', $item->quantity);
                    }
                }

                /*
    ==========================================
    ✅ CỘNG ĐIỂM
    ==========================================
    */
                $user = $order->user;

                if ($user) {
                    $points = floor($order->grand_total / 1000);

                    $user->loyalty_points += $points;

                    if ($user->loyalty_points >= 10000
                    ) {
                        $user->member_level = 'diamond';
                    } elseif ($user->loyalty_points >= 3000) {
                        $user->member_level = 'gold';
                    } elseif ($user->loyalty_points >= 1000) {
                        $user->member_level = 'silver';
                    } else {
                        $user->member_level = 'bronze';
                    }

                    $user->save();

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

        $order = Order::with('items.batches')->findOrFail($id);

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

            foreach ($order->items as $item) {

                // 🔒 lock variant
                $variant = \App\Models\ProductVariant::where('id', $item->variant_id)
                    ->lockForUpdate()
                    ->first();

                if (!$variant) continue;

                // ✅ TỒN TRƯỚC (REALTIME từ batch)
                $before = \App\Models\StockImport::where('variant_id', $item->variant_id)
                    ->sum('remaining_quantity');

                $change = 0;

                foreach ($item->batches as $batch) {

                    if ($batch->is_rolled_back) continue;

                    $stock = \App\Models\StockImport::find($batch->stock_import_id);
                    if (!$stock) continue;

                    // ✅ hoàn theo lô
                    $stock->increment('remaining_quantity', $batch->quantity);

                    $batch->update([
                        'returned_quantity' => $batch->quantity,
                        'is_rolled_back'    => 1,
                    ]);

                    // ✅ cộng change
                    $change += $batch->quantity;
                }

                // ✅ TỒN SAU (REALTIME)
                $after = \App\Models\StockImport::where('variant_id', $item->variant_id)
                    ->sum('remaining_quantity');

                // 🔄 sync lại variant
                $variant->update([
                    'stock_quantity' => $after
                ]);

                // ✅ LOG CHUẨN
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

            // 🔔 notify user
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