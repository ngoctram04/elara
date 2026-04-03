<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use App\Models\Order;
use App\Models\StockImport;
use App\Models\ProductVariant;
use App\Models\InventoryLog;
use App\Models\UserPointHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

        $sort = $request->get('sort', 'new');

        if ($sort === 'old') {
            $query->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc');
        } else {
            $query->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');
        }

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

            if ($refund->status !== RefundRequest::STATUS_PENDING) {
                throw new \Exception('Yêu cầu không hợp lệ');
            }

            $refund->update([
                'status' => RefundRequest::STATUS_APPROVED,
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

        if ($refund->status !== RefundRequest::STATUS_PENDING) {
            return back()->with('error', 'Không thể từ chối yêu cầu này');
        }

        $refund->update([
            'status'     => RefundRequest::STATUS_REJECTED,
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
     * sealed  => hoàn kho + trừ đã bán + hoàn tiền
     * damaged => không hoàn kho + vẫn trừ đã bán + ghi nhận hao hụt
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

        // Tổng tiền sản phẩm hoàn
        $totalRefundProductAmount = 0;

        // Ship bị khấu trừ khi hoàn
        $shippingDeduction = 0;

        // Tiền hoàn thực tế cuối cùng
        $finalRefundAmount = 0;

        DB::beginTransaction();

        try {
            $refund = RefundRequest::with([
                'order.user',
                'order.items',
                'items.variant.product',
                'items.variant.mainImage',
                'items.batches',
            ])->findOrFail($id);

            $order = $refund->order;

            if (!$order) {
                throw new \Exception('Không tìm thấy đơn hàng');
            }

            if ($refund->status !== RefundRequest::STATUS_APPROVED) {
                throw new \Exception('Chỉ có thể xác nhận hoàn tiền khi yêu cầu đã được duyệt');
            }

            $manualNote = trim((string) $request->input('admin_note'));

            /*
            |--------------------------------------------------------------------------
            | 1. Cập nhật refund ban đầu
            |--------------------------------------------------------------------------
            */
            $refund->update([
                'status'      => RefundRequest::STATUS_REFUNDED,
                'admin_note'  => $manualNote !== '' ? $manualNote : $refund->admin_note,
                'loss_amount' => 0,
            ]);

            /*
            |--------------------------------------------------------------------------
            | 2. Xử lý từng item hoàn
            |--------------------------------------------------------------------------
            */
            foreach ($refund->items as $item) {
                $variantId = (int) ($item->pivot->variant_id ?: $item->variant_id);
                $qty = max(1, (int) ($item->pivot->quantity ?? 1));

                // Giá sản phẩm của item, không phải tiền ship
                $unitPrice = (float) ($item->price ?? 0);
                $refundAmount = $unitPrice * $qty;

                $conditionStatus = $item->pivot->condition_status ?? 'sealed';
                $isRestockable = $this->shouldRestock($conditionStatus);

                $pivotUnitCost = 0;
                $pivotLoss = 0;
                $returnedToStock = 0;
                $pivotStockImportId = null;

                // Chỉ cộng tiền sản phẩm hoàn
                $totalRefundProductAmount += $refundAmount;

                if ($isRestockable) {
                    $stock = StockImport::where('variant_id', $variantId)
                        ->orderBy('id', 'desc')
                        ->first();

                    if ($stock) {
                        $batchQty = $qty;

                        $before = (int) $stock->remaining_quantity;
                        $after = $before + $batchQty;
                        $unitCost = (float) ($stock->cost_price ?? 0);

                        $stock->increment('remaining_quantity', $batchQty);

                        $pivotStockImportId = $stock->id;
                        $pivotUnitCost = $unitCost;
                        $returnedToStock = 1;
                        $restockQty += $batchQty;

                        InventoryLog::create([
                            'variant_id'      => $variantId,
                            'stock_import_id' => $stock->id,
                            'type'            => 'return_restock',
                            'quantity_change' => $batchQty,
                            'stock_before'    => $before,
                            'stock_after'     => $after,
                            'unit_cost'       => $unitCost,
                            'loss_amount'     => 0,
                            'reference_type'  => 'refund',
                            'reference_id'    => $refund->id,
                            'note'            => 'Hoàn kho do khách trả hàng còn nguyên seal',
                        ]);
                    }
                } else {
                    $stock = StockImport::where('variant_id', $variantId)
                        ->orderBy('id', 'desc')
                        ->first();

                    if ($stock) {
                        $batchQty = $qty;
                        $unitCost = (float) ($stock->cost_price ?? 0);
                        $loss = $unitCost * $batchQty;

                        $pivotStockImportId = $stock->id;
                        $pivotUnitCost = $unitCost;
                        $pivotLoss = $loss;
                        $returnedToStock = 0;

                        $totalLoss += $loss;
                        $damagedQty += $batchQty;

                        InventoryLog::create([
                            'variant_id'      => $variantId,
                            'stock_import_id' => $stock->id,
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

                $item->pivot->refund_amount = $refundAmount;
                $item->pivot->unit_cost = $pivotUnitCost;
                $item->pivot->loss_amount = $pivotLoss;
                $item->pivot->returned_to_stock = $returnedToStock;
                $item->pivot->stock_import_id = $pivotStockImportId;
                $item->pivot->save();

                $totalStock = (int) StockImport::where('variant_id', $variantId)
                    ->sum('remaining_quantity');

                $variant = ProductVariant::lockForUpdate()->find($variantId);

                if ($variant) {
                    $currentSold = (int) ($variant->sold_quantity ?? 0);
                    $newSold = max(0, $currentSold - $qty);

                    $variant->update([
                        'stock_quantity' => $totalStock,
                        'sold_quantity'  => $newSold,
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 3. Trừ điểm + chi tiêu user theo TIỀN SẢN PHẨM hoàn
            |    Không tính ship
            |--------------------------------------------------------------------------
            */
            $user = $order->user;

            if ($user && $totalRefundProductAmount > 0) {
                $minusPoints = (int) floor($totalRefundProductAmount / 1000);

                $user->loyalty_points = max(
                    0,
                    (int) ($user->loyalty_points ?? 0) - $minusPoints
                );

                $user->total_spent = max(
                    0,
                    (float) ($user->total_spent ?? 0) - $totalRefundProductAmount
                );

                $user->yearly_spent = max(
                    0,
                    (float) ($user->yearly_spent ?? 0) - $totalRefundProductAmount
                );

                if (method_exists($user, 'updateMemberLevel')) {
                    $user->updateMemberLevel();
                }

                $user->save();

                if ($minusPoints > 0) {
                    UserPointHistory::create([
                        'user_id'     => $user->id,
                        'points'      => -$minusPoints,
                        'type'        => 'refund',
                        'description' => 'Trừ điểm do hoàn tiền sản phẩm đơn #' . $order->id,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 4. Tính khấu trừ ship
            |--------------------------------------------------------------------------
            | - Nếu khách đã trả ship: không trừ nữa
            | - Nếu free ship: trừ shipping_cost của chính đơn đó
            */
            $shippingDeduction = $this->getRefundShippingDeduction($order);
            $finalRefundAmount = max(0, $totalRefundProductAmount - $shippingDeduction);

            /*
            |--------------------------------------------------------------------------
            | 5. Cập nhật trạng thái đơn hàng
            |--------------------------------------------------------------------------
            */
            $isFullRefund = $this->isFullRefundOrder($order, $refund);

            if ($isFullRefund) {
                $order->update([
                    'status'         => Order::STATUS_RETURNED,
                    'payment_status' => Order::PAYMENT_REFUNDED,
                ]);
            } else {
                $order->update([
                    'status' => Order::STATUS_COMPLETED,
                ]);

                if ($order->payment_method === 'vnpay') {
                    $order->update([
                        'payment_status' => Order::PAYMENT_REFUNDED,
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 6. Ghi note + cập nhật tổng
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

            if ($totalRefundProductAmount > 0) {
                $extraNote[] = 'Tổng tiền hoàn sản phẩm: ' . number_format($totalRefundProductAmount, 0, ',', '.') . 'đ';
            }

            if ($shippingDeduction > 0) {
                $extraNote[] = 'Khấu trừ phí vận chuyển: ' . number_format($shippingDeduction, 0, ',', '.') . 'đ';
            }

            $extraNote[] = 'Tiền hoàn thực tế: ' . number_format($finalRefundAmount, 0, ',', '.') . 'đ';

            if (!$isFullRefund) {
                $extraNote[] = 'Hoàn một phần đơn hàng';
            } else {
                $extraNote[] = 'Hoàn toàn bộ đơn hàng';
            }

            $mergedNote = trim(implode(' | ', array_filter([
                $refund->admin_note,
                implode(' | ', $extraNote),
            ])));

            $refund->update([
                'admin_note'        => $mergedNote !== '' ? $mergedNote : null,
                'loss_amount'       => $totalLoss,
                'restock_total_qty' => $restockQty,
                'damaged_total_qty' => $damagedQty,
                'refund_total'      => $finalRefundAmount,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Refund completed failed: ' . $e->getMessage(), [
                'refund_id' => $id,
            ]);

            return back()->with('error', $e->getMessage());
        }

        if ($refund && $refund->order && $refund->order->user) {
            $refund->order->user->notify(new SystemNotification([
                'title'   => 'Đã hoàn tiền đơn hàng',
                'message' => 'Yêu cầu hoàn tiền cho đơn #' . $refund->order->id . ' đã được xử lý hoàn tất',
                'url'     => route('orders.show', $refund->order->id),
                'type'    => 'refund',
            ]));
        }

        return back()->with('success', 'Đã hoàn tiền và cập nhật tồn kho / đã bán');
    }

    /**
     * Xác định có được hoàn kho không
     */
    private function shouldRestock(?string $condition): bool
    {
        return $condition === 'sealed';
    }

    /**
     * Kiểm tra refund hiện tại có phải hoàn toàn bộ đơn hay không
     */
    private function isFullRefundOrder(Order $order, RefundRequest $refund): bool
    {
        $order->loadMissing('items');
        $refund->loadMissing('items');

        $refundQtyMap = [];

        foreach ($refund->items as $refundItem) {
            $orderItemId = (int) $refundItem->id;
            $refundQtyMap[$orderItemId] = (int) ($refundItem->pivot->quantity ?? 0);
        }

        foreach ($order->items as $orderItem) {
            $orderItemId = (int) $orderItem->id;
            $orderedQty = (int) ($orderItem->quantity ?? 0);
            $refundedQty = (int) ($refundQtyMap[$orderItemId] ?? 0);

            if ($refundedQty < $orderedQty) {
                return false;
            }
        }

        return true;
    }

    /**
     * Tính phần ship cần khấu trừ khi hoàn tiền
     */
    private function getRefundShippingDeduction(Order $order): int
    {
        // Khách đã trả ship rồi thì không trừ nữa
        if ((int) ($order->shipping_fee ?? 0) > 0) {
            return 0;
        }

        // Free ship thì trừ ship thực tế của đơn
        return max(0, (int) ($order->shipping_cost ?? 0));
    }
}