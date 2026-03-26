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
use App\Notifications\SystemNotification;

class RefundController extends Controller
{
    /**
     * Danh sách yêu cầu hoàn tiền
     */
    public function index(Request $request)
    {
        $query = RefundRequest::with([
            'user',
            'media',
            'order',
            'items.variant.product',
            'items.variant.mainImage',
        ]);

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

        $query->orderBy('created_at', $request->sort === 'old' ? 'asc' : 'desc');

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

        return back()->with('success', 'Đã chấp nhận yêu cầu hoàn tiền');
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
            'admin_note' => trim($request->admin_note),
        ]);

        if ($refund->user) {
            $refund->user->notify(new SystemNotification([
                'title'   => 'Yêu cầu hoàn tiền bị từ chối',
                'message' => trim($request->admin_note),
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
        $totalRefund = 0;

        DB::transaction(function () use (
            $request,
            $id,
            &$refund,
            &$totalLoss,
            &$restockQty,
            &$damagedQty,
            &$totalRefund
        ) {
            $refund = RefundRequest::with([
                'order.user',
                'items.variant.product',
                'items.variant.mainImage',
                'items.batches',
            ])->findOrFail($id);

            $order = $refund->order;

            if (!$order) {
                throw new \Exception('Không tìm thấy đơn hàng');
            }

            if ($refund->status !== 'approved') {
                throw new \Exception('Chỉ có thể xác nhận hoàn tiền khi yêu cầu đã được duyệt');
            }

            $manualNote = trim((string) $request->input('admin_note'));

            /*
            |--------------------------------------------------------------------------
            | 1. Cập nhật refund ban đầu
            |--------------------------------------------------------------------------
            */
            $refund->update([
                'status'      => 'refunded',
                'admin_note'  => $manualNote ?: $refund->admin_note,
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
            | 3. Xử lý từng item
            |--------------------------------------------------------------------------
            */
            foreach ($refund->items as $item) {
                $variantId = $item->variant_id;
                $price = (float) ($item->price ?? 0);
                $qty = (int) ($item->pivot->quantity ?? 1);
                $refundAmount = $price * $qty;

                // lưu tiền hoàn cho item
                $item->pivot->refund_amount = $refundAmount;
                $item->pivot->save();

                $totalRefund += $refundAmount;

                $conditionStatus = $item->pivot->condition_status ?? 'sealed';
                $isRestockable = $this->shouldRestock($conditionStatus);

                if ($isRestockable) {
                    foreach ($item->batches as $batch) {
                        if ((int) $batch->is_rolled_back === 1) {
                            continue;
                        }

                        $stock = StockImport::find($batch->stock_import_id);
                        if (!$stock) {
                            continue;
                        }

                        $batchQty = (int) $batch->quantity;
                        $before = (int) $stock->remaining_quantity;
                        $after = $before + $batchQty;

                        $stock->increment('remaining_quantity', $batchQty);

                        $batch->update([
                            'is_rolled_back' => 1,
                        ]);

                        InventoryLog::create([
                            'variant_id'      => $variantId,
                            'stock_import_id' => $batch->stock_import_id,
                            'type'            => 'return_restock',
                            'quantity_change' => $batchQty,
                            'stock_before'    => $before,
                            'stock_after'     => $after,
                            'unit_cost'       => (float) ($stock->cost_price ?? 0),
                            'loss_amount'     => 0,
                            'reference_type'  => 'refund',
                            'reference_id'    => $refund->id,
                            'note'            => 'Hoàn kho do khách trả hàng còn nguyên seal',
                        ]);

                        $restockQty += $batchQty;
                    }
                } else {
                    foreach ($item->batches as $batch) {
                        $stock = StockImport::find($batch->stock_import_id);
                        if (!$stock) {
                            continue;
                        }

                        $batchQty = (int) $batch->quantity;
                        $unitCost = (float) ($stock->cost_price ?? 0);
                        $loss = $unitCost * $batchQty;

                        $totalLoss += $loss;
                        $damagedQty += $batchQty;

                        InventoryLog::create([
                            'variant_id'      => $variantId,
                            'stock_import_id' => $batch->stock_import_id,
                            'type'            => 'return_damaged',
                            'quantity_change' => $batchQty,
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

                // đồng bộ tổng tồn của biến thể
                $totalStock = (int) StockImport::where('variant_id', $variantId)
                    ->sum('remaining_quantity');

                ProductVariant::where('id', $variantId)->update([
                    'stock_quantity' => $totalStock,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | 4. Ghi tổng note + hao hụt + số lượng
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
                $refund->admin_note,
                implode(' | ', $extraNote),
            ])));

            $refund->update([
                'admin_note'        => $mergedNote ?: null,
                'loss_amount'       => $totalLoss,
                'restock_total_qty' => $restockQty,
                'damaged_total_qty' => $damagedQty,
                'refund_total'      => $totalRefund,
            ]);
        });

        if ($refund && $refund->order && $refund->order->user) {
            $refund->order->user->notify(new SystemNotification([
                'title'   => 'Đã hoàn tiền đơn hàng',
                'message' => 'Yêu cầu hoàn tiền cho đơn #' . $refund->order->id . ' đã được xử lý hoàn tất',
                'url'     => route('orders.show', $refund->order->id),
                'type'    => 'refund',
            ]));
        }

        return back()->with('success', 'Đã hoàn tiền và xử lý tồn kho theo từng sản phẩm');
    }

    /**
     * Xác định có được hoàn kho không
     */
    private function shouldRestock(?string $condition): bool
    {
        return $condition === 'sealed';
    }
}