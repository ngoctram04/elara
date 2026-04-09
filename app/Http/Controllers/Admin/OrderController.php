<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\StockImport;
use App\Models\InventoryLog;
use App\Notifications\SystemNotification;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        $query = Order::with(['user', 'cancelledByUser']);

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

        if ($request->filled('status')) {
            $query->where('status', (int) $request->status);
        }

        if ($request->filled('payment_status')) {
            switch ($request->payment_status) {
                case 'paid':
                    $query->where(function ($q) {
                        $q->where(function ($sub) {
                            $sub->where('payment_method', 'vnpay')
                                ->where('payment_status', Order::PAYMENT_PAID);
                        })->orWhere(function ($sub) {
                            $sub->where('payment_method', 'cod')
                                ->where('status', Order::STATUS_COMPLETED);
                        });
                    });
                    break;

                case 'unpaid':
                    $query->where(function ($q) {
                        $q->where(function ($sub) {
                            $sub->where('payment_method', 'cod')
                                ->where('status', '!=', Order::STATUS_COMPLETED);
                        })->orWhere(function ($sub) {
                            $sub->where('payment_method', 'vnpay')
                                ->where('payment_status', Order::PAYMENT_UNPAID);
                        });
                    });
                    break;

                case 'refunded':
                    $query->where('payment_status', Order::PAYMENT_REFUNDED);
                    break;

                case 'failed':
                    $query->where('payment_status', Order::PAYMENT_FAILED);
                    break;
            }
        }

        if ($request->sort === 'oldest') {
            $query->oldest();
        } else {
            $query->latest();
        }

        $orders = $query->paginate(7)->withQueryString();

        $pendingCount = Order::where('status', Order::STATUS_PENDING)->count();
        $processingCount = Order::where('status', Order::STATUS_PROCESSING)->count();
        $completedCount = Order::where('status', Order::STATUS_COMPLETED)->count();
        $cancelledCount = Order::where('status', Order::STATUS_CANCELLED)->count();
        $returnedCount = Order::where('status', Order::STATUS_RETURNED)->count();

        return view('admin.orders.index', compact(
            'orders',
            'pendingCount',
            'processingCount',
            'completedCount',
            'cancelledCount',
            'returnedCount'
        ));
    }

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
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer'
        ]);

        $order = Order::with(['items.variant', 'user'])->findOrFail($id);

        $oldStatus = (int) $order->status;
        $newStatus = (int) $request->status;

        if ($oldStatus == Order::STATUS_CANCELLED) {
            return back()->with('error', 'Đơn đã huỷ, không thể thay đổi trạng thái.');
        }

        if ($oldStatus == Order::STATUS_RETURNED) {
            return back()->with('error', 'Đơn đã trả hàng, không thể thay đổi trạng thái.');
        }

        if ($newStatus == Order::STATUS_CANCELLED) {
            return back()->with('error', 'Muốn huỷ đơn hãy dùng chức năng huỷ đơn riêng.');
        }

        if ($newStatus == Order::STATUS_RETURNED) {
            return back()->with('error', 'Trạng thái trả hàng nên được xử lý ở chức năng hoàn/trả riêng.');
        }

        if ($newStatus <= $oldStatus) {
            return back()->with('error', 'Không thể chuyển trạng thái ngược hoặc giữ nguyên.');
        }

        if (($newStatus - $oldStatus) > 1) {
            return back()->with('error', 'Phải chuyển trạng thái đúng theo thứ tự.');
        }

        if (
            $newStatus == Order::STATUS_COMPLETED &&
            !$request->hasFile('delivery_proof') &&
            !$order->delivery_image
        ) {
            return back()->with('error', 'Phải upload ảnh giao hàng khi xác nhận đã giao.');
        }

        DB::beginTransaction();

        try {

            if (
                $oldStatus == Order::STATUS_PROCESSING &&
                $newStatus == Order::STATUS_COMPLETED
            ) {
                if ($request->hasFile('delivery_proof')) {
                    $path = $request->file('delivery_proof')->store('delivery', 'public');
                    $order->delivery_image = $path;
                }

                $order->delivered_at = now();
                $order->customer_confirmed = false;
                $order->received_at = null;

                foreach ($order->items as $item) {
                    if ($item->variant) {
                        $item->variant->increment('sold_quantity', (int) $item->quantity);
                    }
                }

                $user = $order->user;

                if ($user) {
                    $productAmount = (float) ($order->total ?? 0);
                    $points = (int) floor($productAmount / 1000);

                    $currentYear = (int) now()->year;
                    $membershipYear = (int) ($user->membership_year ?? 0);

                    if ($membershipYear !== $currentYear) {
                        $user->yearly_spent = 0;
                        $user->membership_year = $currentYear;
                    }

                    $user->loyalty_points = (int) ($user->loyalty_points ?? 0) + $points;
                    $user->total_spent = (float) ($user->total_spent ?? 0) + $productAmount;
                    $user->yearly_spent = (float) ($user->yearly_spent ?? 0) + $productAmount;

                    if (method_exists($user, 'updateMemberLevel')) {
                        $user->updateMemberLevel();
                    } else {
                        $yearlySpent = (float) ($user->yearly_spent ?? 0);

                        if ($yearlySpent >= 10000000) {
                            $user->member_level = 'diamond';
                        } elseif ($yearlySpent >= 3000000) {
                            $user->member_level = 'gold';
                        } elseif ($yearlySpent >= 1000000) {
                            $user->member_level = 'silver';
                        } else {
                            $user->member_level = 'bronze';
                        }
                    }

                    $user->save();

                    if ($points > 0) {
                        DB::table('user_point_histories')->insert([
                            'user_id'     => $user->id,
                            'points'      => $points,
                            'type'        => 'earn',
                            'description' => 'Tích điểm từ tiền sản phẩm đơn #' . $order->id,
                            'created_at'  => now(),
                            'updated_at'  => now()
                        ]);
                    }
                }
            }

            $order->status = $newStatus;
            $order->save();

            if ($order->user) {
                $title = 'Cập nhật đơn hàng';
                $message = 'Đơn #' . $order->id . ' đã được cập nhật trạng thái.';
                $type = 'order_updated';

                if ($newStatus == Order::STATUS_PENDING) {
                    $title = 'Đơn hàng đang được xử lý';
                    $message = 'Đơn #' . $order->id . ' đang được cửa hàng xử lý.';
                    $type = 'order_processing';
                } elseif ($newStatus == Order::STATUS_PROCESSING) {
                    $title = 'Đơn hàng đang được giao';
                    $message = 'Đơn #' . $order->id . ' đang được giao đến bạn.';
                    $type = 'order_shipping';
                } elseif ($newStatus == Order::STATUS_COMPLETED) {
                    $title = 'Đơn hàng đã giao thành công';
                    $message = 'Đơn #' . $order->id . ' đã được giao thành công. Vui lòng xác nhận đã nhận hàng.';
                    $type = 'order_completed';
                }

                $order->user->notify(new SystemNotification([
                    'title'   => $title,
                    'message' => $message,
                    'url'     => route('orders.show', $order->id),
                    'type'    => $type
                ]));
            }

            DB::commit();

            return back()->with('success', 'Cập nhật trạng thái thành công!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Update status failed: ' . $e->getMessage(), [
                'order_id'   => $id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            return back()->with('error', 'Cập nhật trạng thái thất bại!');
        }
    }


    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancel_reason' => 'required|string|max:500'
        ]);

        $order = Order::with(['items.batches', 'user'])->findOrFail($id);

        if ($order->status != Order::STATUS_PENDING) {
            return back()->with('error', 'Chỉ được huỷ khi đơn đang xử lý.');
        }

        DB::beginTransaction();

        try {
            $order->update([
                'status'               => Order::STATUS_CANCELLED,
                'cancel_reason'        => $request->cancel_reason,
                'cancelled_by'         => 'admin',
                'cancelled_by_user_id' => Auth::id(),
                'cancelled_at'         => now()
            ]);

            foreach ($order->items as $item) {
                $variant = ProductVariant::where('id', $item->variant_id)
                    ->lockForUpdate()
                    ->first();

                if (!$variant) {
                    continue;
                }

                $before = StockImport::where('variant_id', $item->variant_id)
                    ->sum('remaining_quantity');

                $change = 0;

                foreach ($item->batches as $batch) {
                    if ($batch->is_rolled_back) {
                        continue;
                    }

                    $stock = StockImport::find($batch->stock_import_id);
                    if (!$stock) {
                        continue;
                    }

                    $stock->increment('remaining_quantity', $batch->quantity);

                    $batch->update([
                        'returned_quantity' => $batch->quantity,
                        'is_rolled_back'    => 1,
                    ]);

                    $change += $batch->quantity;
                }

                $after = StockImport::where('variant_id', $item->variant_id)
                    ->sum('remaining_quantity');

                $variant->update([
                    'stock_quantity' => $after
                ]);

                InventoryLog::create([
                    'variant_id'      => $variant->id,
                    'type'            => 'cancel',
                    'quantity_change' => $change,
                    'stock_before'    => $before,
                    'stock_after'     => $after,
                    'reference_type'  => 'order',
                    'reference_id'    => $order->id
                ]);
            }

            DB::commit();

            if ($order->user) {
                $order->user->notify(new SystemNotification([
                    'title'   => 'Đơn hàng bị huỷ',
                    'message' => 'Đơn #' . $order->id . ' đã bị huỷ bởi cửa hàng.',
                    'url'     => route('orders.show', $order->id),
                    'type'    => 'order_cancelled',
                    'meta'    => [
                        'reason' => $request->cancel_reason
                    ]
                ]));
            }

            return back()->with('success', 'Đã huỷ đơn hàng.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Admin cancel order failed: ' . $e->getMessage(), [
                'order_id' => $id,
                'admin_id' => Auth::id(),
            ]);

            return back()->with('error', 'Huỷ đơn thất bại.');
        }
    }
}