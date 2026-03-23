<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use App\Models\Order;
use App\Models\StockImport;
use App\Models\ProductVariant;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\RefundCompletedMail;
use App\Notifications\SystemNotification;

class RefundController extends Controller
{
    /**
     * Danh sách yêu cầu hoàn tiền
     */
    public function index(Request $request)
    {
        $query = RefundRequest::with(['user', 'media', 'order']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $numberSearch = preg_replace('/\D/', '', $search);

            $query->where(function ($q) use ($search, $numberSearch) {
                $q->orWhereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', '%' . $search . '%');
                })
                ->orWhereRaw("CONCAT('HT', LPAD(id, 5, '0')) LIKE ?", ['%' . $search . '%'])
                ->orWhereRaw("CONCAT('DH', LPAD(order_id, 5, '0')) LIKE ?", ['%' . $search . '%']);

                if ($numberSearch !== '') {
                    $q->orWhere('id', (int) $numberSearch)
                        ->orWhere('order_id', (int) $numberSearch);
                }
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $query->orderBy('created_at', $request->sort == 'old' ? 'asc' : 'desc');

        $refunds = $query->paginate(10)->withQueryString();

        return view('admin.refunds.index', compact('refunds'));
    }

    /**
     * APPROVE (chỉ duyệt)
     */
    public function approve($id)
    {
        $refund = null;

        DB::transaction(function () use ($id, &$refund) {

            $refund = RefundRequest::with('order.user')
                ->findOrFail($id);

            if ($refund->status !== 'pending') {
                throw new \Exception('Yêu cầu không hợp lệ');
            }

            $refund->update([
                'status' => 'approved'
            ]);

            $refund->order->update([
                'status' => Order::STATUS_RETURNED
            ]);
        });

        if ($refund && $refund->order->user) {
            $refund->order->user->notify(new SystemNotification([
                'title' => 'Yêu cầu hoàn tiền được chấp nhận',
                'message' => 'Đơn #' . $refund->order->id . ' đã được duyệt hoàn tiền',
                'url' => route('orders.show', $refund->order->id),
                'type' => 'refund'
            ]));
        }

        return back()->with('success', 'Đã chấp nhận yêu cầu đổi trả');
    }

    /**
     * REJECT
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_note' => 'required|string|max:1000'
        ]);

        $refund = RefundRequest::with('user', 'order')->findOrFail($id);

        if ($refund->status !== 'pending') {
            return back()->with('error', 'Không thể từ chối yêu cầu này');
        }

        $refund->update([
            'status' => 'rejected',
            'admin_note' => $request->admin_note
        ]);

        if ($refund->user) {
            $refund->user->notify(new SystemNotification([
                'title' => 'Yêu cầu hoàn tiền bị từ chối',
                'message' => $request->admin_note,
                'url' => route('orders.show', $refund->order_id),
                'type' => 'refund'
            ]));
        }

        return back()->with('success', 'Đã từ chối yêu cầu hoàn tiền');
    }

    /**
     * REFUNDED (🔥 FIX CHUẨN FEFO)
     */
    public function refunded($id)
    {
        $refund = null;

        DB::transaction(function () use ($id, &$refund) {

            // 🔥 load đầy đủ batches
            $refund = RefundRequest::with('order.items.batches', 'order.user')
                ->findOrFail($id);

            $order = $refund->order;

            // ❌ tránh chạy lại
            if ($refund->status === 'refunded') {
                return;
            }

            // ===== UPDATE STATUS =====
            $refund->update([
                'status' => 'refunded'
            ]);

            $order->update([
                'payment_status' => Order::PAYMENT_REFUNDED
            ]);

            // =========================================
            // 🔥 ROLLBACK THEO BATCH (CHUẨN NHƯ CANCEL)
            // =========================================
            foreach ($order->items as $item) {

                foreach ($item->batches as $batch) {

                    if ($batch->is_rolled_back) continue;

                    $stock = StockImport::find($batch->stock_import_id);

                    if (!$stock) continue;

                    $before = $stock->remaining_quantity;

                    // hoàn lại lô
                    $stock->increment('remaining_quantity', $batch->quantity);

                    // đánh dấu rollback
                    $batch->update([
                        'is_rolled_back' => 1
                    ]);

                    // log kho
                    InventoryLog::create([
                        'variant_id' => $item->variant_id,
                        'type' => 'cancel',
                        'quantity_change' => $batch->quantity,
                        'stock_before' => $before,
                        'stock_after' => $before + $batch->quantity,
                        'reference_type' => 'order',
                        'reference_id' => $order->id
                    ]);
                }

                // 🔥 sync lại tồn
                $total = StockImport::where('variant_id', $item->variant_id)
                    ->sum('remaining_quantity');

                ProductVariant::where('id', $item->variant_id)
                    ->update([
                        'stock_quantity' => $total
                    ]);
            }
        });

        // 🔔 NOTIFICATION
        if ($refund && $refund->order->user) {
            $refund->order->user->notify(new SystemNotification([
                'title' => 'Đã hoàn tiền',
                'message' => 'Đơn #' . $refund->order->id . ' đã được hoàn tiền thành công',
                'url' => route('orders.show', $refund->order->id),
                'type' => 'refund'
            ]));
        }

        // 📧 EMAIL
        if ($refund && $refund->order->user && $refund->order->user->email) {
            Mail::to($refund->order->user->email)
                ->send(new RefundCompletedMail(
                    $refund->order,
                    $refund->order->grand_total
                ));
        }

        return back()->with('success', 'Đã hoàn tiền và cập nhật kho chính xác');
    }
}