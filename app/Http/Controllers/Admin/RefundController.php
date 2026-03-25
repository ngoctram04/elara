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
                $q->whereHas('user', function ($u) use ($search) {
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
     * Duyệt yêu cầu hoàn tiền
     */
    public function approve($id)
    {
        $refund = null;

        DB::transaction(function () use ($id, &$refund) {
            $refund = RefundRequest::with('order.user')->findOrFail($id);

            if ($refund->status !== 'pending') {
                throw new \Exception('Yêu cầu không hợp lệ');
            }

            $refund->update([
                'status' => 'approved',
            ]);
        });

        if ($refund && $refund->order && $refund->order->user) {
            $refund->order->user->notify(new SystemNotification([
                'title'   => 'Yêu cầu hoàn tiền được chấp nhận',
                'message' => 'Đơn #' . $refund->order->id . ' đã được duyệt hoàn tiền',
                'url'     => route('orders.show', $refund->order->id),
                'type'    => 'refund',
            ]));
        }

        return back()->with('success', 'Đã chấp nhận yêu cầu đổi trả');
    }

    /**
     * Từ chối yêu cầu
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_note' => 'required|string|max:1000',
        ]);

        $refund = RefundRequest::with('user', 'order')->findOrFail($id);

        if ($refund->status !== 'pending') {
            return back()->with('error', 'Không thể từ chối yêu cầu này');
        }

        $refund->update([
            'status'     => 'rejected',
            'admin_note' => $request->admin_note,
        ]);

        if ($refund->user) {
            $refund->user->notify(new SystemNotification([
                'title'   => 'Yêu cầu hoàn tiền bị từ chối',
                'message' => $request->admin_note,
                'url'     => route('orders.show', $refund->order_id),
                'type'    => 'refund',
            ]));
        }

        return back()->with('success', 'Đã từ chối yêu cầu hoàn tiền');
    }

    /**
     * Xác nhận đã hoàn tiền
     *
     * sealed => hoàn kho + hoàn lô + hoàn tiền
     * broken => không hoàn kho, không hoàn lô, hoàn tiền + ghi nhận hao hụt giá nhập
     */
    public function refunded(Request $request, $id)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $refund = null;
        $totalLoss = 0;
        $restockQty = 0;
        $damagedQty = 0;

        DB::transaction(function () use ($request, $id, &$refund, &$totalLoss, &$restockQty, &$damagedQty) {
            $refund = RefundRequest::with([
                'items.batches',
                'order.user',
            ])->findOrFail($id);

            $order = $refund->order;

            if (!$order) {
                throw new \Exception('Không tìm thấy đơn hàng');
            }

            if ($refund->status === 'refunded') {
                return;
            }

            $finalNote = trim(implode(' | ', array_filter([
                $refund->admin_note,
                $request->input('admin_note'),
            ])));

            /*
        |--------------------------------------------------------------------------
        | 1. Cập nhật refund ban đầu
        |--------------------------------------------------------------------------
        */
            $refund->update([
                'status'      => 'refunded',
                'admin_note'  => $finalNote ?: null,
                'loss_amount' => 0,
            ]);

            /*
        |--------------------------------------------------------------------------
        | 2. Cập nhật order
        |--------------------------------------------------------------------------
        */
            $order->update([
                'status'         => Order::STATUS_RETURNED,
                'payment_status' => Order::PAYMENT_REFUNDED,
            ]);

            /*
        |--------------------------------------------------------------------------
        | 3. Xử lý theo từng item khách đã chọn hoàn tiền
        |--------------------------------------------------------------------------
        */
            foreach ($refund->items as $item) {
                $conditionStatus = $item->pivot->condition_status ?? 'sealed';

                $isRestockable = $conditionStatus === 'sealed';

                if ($isRestockable) {
                    foreach ($item->batches as $batch) {
                        if ($batch->is_rolled_back) {
                            continue;
                        }

                        $stock = StockImport::find($batch->stock_import_id);

                        if (!$stock) {
                            continue;
                        }

                        $before = (int) $stock->remaining_quantity;
                        $after  = $before + (int) $batch->quantity;

                        $stock->increment('remaining_quantity', $batch->quantity);

                        $batch->update([
                            'is_rolled_back' => 1,
                        ]);

                        InventoryLog::create([
                            'variant_id'      => $item->variant_id,
                            'stock_import_id' => $batch->stock_import_id,
                            'type'            => 'return_restock',
                            'quantity_change' => (int) $batch->quantity,
                            'stock_before'    => $before,
                            'stock_after'     => $after,
                            'unit_cost'       => (float) ($stock->cost_price ?? 0),
                            'loss_amount'     => 0,
                            'reference_type'  => 'refund',
                            'reference_id'    => $refund->id,
                            'note'            => 'Hoàn kho do khách trả hàng còn nguyên seal',
                        ]);

                        $restockQty += (int) $batch->quantity;
                    }
                } else {
                    foreach ($item->batches as $batch) {
                        $stock = StockImport::find($batch->stock_import_id);

                        if (!$stock) {
                            continue;
                        }

                        $unitCost = (float) ($stock->cost_price ?? 0);
                        $loss     = $unitCost * (int) $batch->quantity;

                        $totalLoss += $loss;
                        $damagedQty += (int) $batch->quantity;

                        InventoryLog::create([
                            'variant_id'      => $item->variant_id,
                            'stock_import_id' => $batch->stock_import_id,
                            'type'            => 'return_damaged',
                            'quantity_change' => (int) $batch->quantity,
                            'stock_before'    => (int) $stock->remaining_quantity,
                            'stock_after'     => (int) $stock->remaining_quantity,
                            'unit_cost'       => $unitCost,
                            'loss_amount'     => $loss,
                            'reference_type'  => 'refund',
                            'reference_id'    => $refund->id,
                            'note'            => 'Khách trả hàng bị vỡ, không nhập lại kho',
                        ]);
                    }
                }

                $total = StockImport::where('variant_id', $item->variant_id)
                    ->sum('remaining_quantity');

                ProductVariant::where('id', $item->variant_id)
                    ->update([
                        'stock_quantity' => $total,
                    ]);
            }

            /*
        |--------------------------------------------------------------------------
        | 4. Cập nhật tổng hao hụt / số lượng
        |--------------------------------------------------------------------------
        */
            $extraNote = [];

            if ($restockQty > 0) {
                $extraNote[] = "Hoàn kho: {$restockQty} sản phẩm";
            }

            if ($damagedQty > 0) {
                $extraNote[] = "Không hoàn kho: {$damagedQty} sản phẩm";
            }

            if ($totalLoss > 0) {
                $extraNote[] = 'Hao hụt giá nhập: ' . number_format($totalLoss, 0, ',', '.') . 'đ';
            }

            $mergedNote = trim(implode(' | ', array_filter([
                $finalNote,
                implode(' | ', $extraNote),
            ])));

            $refund->update([
                'admin_note'         => $mergedNote ?: null,
                'loss_amount'        => $totalLoss,
                'restock_total_qty'  => $restockQty,
                'damaged_total_qty'  => $damagedQty,
            ]);
        });

        /*
    |--------------------------------------------------------------------------
    | 5. Notification
    |--------------------------------------------------------------------------
    */
        if ($refund && $refund->order && $refund->order->user) {
            $message = 'Đơn #' . $refund->order->id . ' đã được hoàn tiền thành công';

            if (($refund->restock_total_qty ?? 0) > 0 && ($refund->damaged_total_qty ?? 0) > 0) {
                $message .= ', một phần hàng đã hoàn kho và một phần không nhập lại kho';
            } elseif (($refund->restock_total_qty ?? 0) > 0) {
                $message .= ' và hàng đã được hoàn lại kho';
            } else {
                $message .= ' và hàng không được nhập lại kho';
            }

            $refund->order->user->notify(new SystemNotification([
                'title'   => 'Đã hoàn tiền',
                'message' => $message,
                'url'     => route('orders.show', $refund->order->id),
                'type'    => 'refund',
            ]));
        }

        /*
    |--------------------------------------------------------------------------
    | 6. Email
    |--------------------------------------------------------------------------
    */
        if ($refund && $refund->order && $refund->order->user && $refund->order->user->email) {
            Mail::to($refund->order->user->email)
                ->send(new RefundCompletedMail(
                    $refund->order,
                    $refund->order->grand_total
                ));
        }

        return back()->with('success', 'Đã hoàn tiền và xử lý tồn kho theo từng sản phẩm');
    }

    /**
     * Xác định có được hoàn kho không
     */
    private function shouldRestock(string $condition): bool
    {
        return $condition === 'sealed';
    }

    /**
     * Ghép ghi chú admin để lưu lại tình trạng xử lý
     */
    private function buildAdminNote(?string $oldNote, ?string $newNote, string $condition, bool $shouldRestock): string
    {
        $conditionText = match ($condition) {
            'sealed' => 'Hàng còn nguyên seal',
            'broken' => 'Hàng bị vỡ',
            default  => 'Không xác định',
        };

        $systemNote = $shouldRestock
            ? $conditionText . ' - hoàn kho, hoàn lô'
            : $conditionText . ' - không hoàn kho, không hoàn lô, ghi nhận hao hụt giá nhập';

        $parts = array_filter([
            $oldNote,
            $newNote,
            $systemNote,
        ]);

        return implode(' | ', $parts);
    }
}