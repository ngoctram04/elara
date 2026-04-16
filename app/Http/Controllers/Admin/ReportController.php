<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\Order;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->getReportData($request);
        $data = $this->buildViewData($data);

        return view('admin.reports.index', $data);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->getReportData($request);
        $data = $this->buildViewData($data);
        $data['chartImage'] = $request->input('chart_image');

        $pdf = Pdf::loadView('admin.reports.pdf', $data)
            ->setPaper('a4', 'portrait');

        $fileName = "bao-cao-{$data['from']}-den-{$data['to']}.pdf";

        return $pdf->download($fileName);
    }

    private function deliveredStatuses(): array
    {
        return [
            Order::STATUS_COMPLETED,
            Order::STATUS_RETURNED,
        ];
    }

    private function buildViewData(array $data): array
    {
        $data['grossRevenue']      = (float) ($data['finance']->gross_revenue ?? 0);
        $data['refundTotal']       = (float) ($data['finance']->refund_total ?? 0);
        $data['netRevenue']        = (float) ($data['finance']->net_revenue ?? 0);
        $data['revenue']           = $data['netRevenue'];
        $data['paidInAdvance']     = (float) ($data['paidInAdvance'] ?? 0);
        $data['totalDiscount']     = (float) ($data['finance']->discount_total ?? 0);
        $data['shippingCollected'] = (float) ($data['finance']->shipping_total ?? 0);
        $data['shippingCostTotal'] = (float) ($data['finance']->shipping_cost_total ?? 0);

        $completed = (int) ($data['orderStats']->completed ?? 0);
        $returned  = (int) ($data['orderStats']->returned ?? 0);
        $cancelled = (int) ($data['orderStats']->cancelled ?? 0);

        $data['completedOrders'] = $completed;
        $data['returnedOrders']  = $returned;
        $data['cancelledOrders'] = $cancelled;
        $data['totalOrders'] = $completed + $returned;
        $data['cancelRate']  = (float) ($data['cancelRate'] ?? 0);

        $data['freeShippingLoss'] = max(
            0,
            $data['shippingCostTotal'] - $data['shippingCollected']
        );

        $data['shippingProfit'] = $data['shippingCollected'] - $data['shippingCostTotal'];

        $data['averageOrder'] = $data['totalOrders'] > 0
            ? $data['netRevenue'] / $data['totalOrders']
            : 0;

        $data['periodCost']   = (float) ($data['finance']->cost ?? 0);
        $data['periodLoss']   = (float) ($data['periodLoss'] ?? 0);
        $data['periodImport'] = (float) ($data['periodInventoryMetrics']->period_import ?? 0);
        $data['openingInventoryValue'] = (float) ($data['periodInventoryMetrics']->opening_inventory_value ?? 0);
        $data['closingInventoryValue'] = (float) ($data['periodInventoryMetrics']->closing_inventory_value ?? 0);

        $data['saleProfit'] = $data['netRevenue'] - $data['periodCost'];

        $data['realProfit'] =
            $data['netRevenue']
            - $data['periodCost']
            - $data['shippingCostTotal']
            - $data['periodLoss'];

        $data['profit'] = $data['realProfit'];

        $data['margin'] = $data['netRevenue'] > 0
            ? ($data['profit'] / $data['netRevenue']) * 100
            : 0;

        $data['totalImportAll']    = (float) ($data['inventoryMetrics']->total_import ?? 0);
        $data['inventoryValueNow'] = (float) ($data['inventoryMetrics']->inventory_value ?? 0);
        $data['totalCostAll']      = (float) ($data['inventoryMetrics']->sold_cost_all ?? 0);
        $data['inventoryLossAll']  = (float) ($data['inventoryMetrics']->loss_total_all ?? 0);

        $data['totalCost']      = $data['periodCost'];
        $data['inventoryLoss']  = $data['periodLoss'];
        $data['totalImport']    = $data['periodImport'];
        $data['inventoryValue'] = $data['closingInventoryValue'];

        $data['inventoryBalanceCheck'] =
            ($data['totalImportAll'] ?? 0)
            - ($data['totalCostAll'] ?? 0)
            - ($data['inventoryLossAll'] ?? 0)
            - ($data['inventoryValueNow'] ?? 0);

        $data['periodInventoryCheck'] =
            $data['openingInventoryValue']
            + $data['periodImport']
            - $data['periodCost']
            - $data['periodLoss']
            - $data['closingInventoryValue'];

        return $data;
    }

    private function getDateRange(Request $request): array
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : now()->startOfMonth();

        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : now()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [
            'from'      => $from,
            'to'        => $to,
            'from_date' => $from->format('Y-m-d'),
            'to_date'   => $to->format('Y-m-d'),
        ];
    }

    private function getOrderItemCostSubquery()
    {
        return DB::table('order_item_batches as oib')
            ->join('stock_imports as si', 'si.id', '=', 'oib.stock_import_id')
            ->selectRaw('
                oib.order_item_id,
                SUM(COALESCE(oib.quantity, 0) * COALESCE(si.cost_price, 0)) as item_cost
            ')
            ->groupBy('oib.order_item_id');
    }

    private function getRefundItemAggSubquery()
    {
        return DB::table('refund_request_items as rri')
            ->join('refund_requests as rr', 'rr.id', '=', 'rri.refund_request_id')
            ->where('rr.status', 'refunded')
            ->selectRaw('
                rri.order_item_id,
                SUM(COALESCE(rri.quantity, 0)) as refunded_qty,
                SUM(COALESCE(rri.refund_amount, 0)) as refunded_amount
            ')
            ->groupBy('rri.order_item_id');
    }

    private function getRefundReturnedCostAggSubquery()
    {
        return DB::table('refund_request_items as rri')
            ->join('refund_requests as rr', 'rr.id', '=', 'rri.refund_request_id')
            ->joinSub(
                DB::table('order_item_batches as oib')
                    ->join('stock_imports as si', 'si.id', '=', 'oib.stock_import_id')
                    ->selectRaw('
                        oib.order_item_id,
                        SUM(COALESCE(oib.quantity, 0) * COALESCE(si.cost_price, 0)) as batch_cost,
                        SUM(COALESCE(oib.quantity, 0)) as batch_qty
                    ')
                    ->groupBy('oib.order_item_id'),
                'batch_costs',
                function ($join) {
                    $join->on('batch_costs.order_item_id', '=', 'rri.order_item_id');
                }
            )
            ->where('rr.status', 'refunded')
            ->selectRaw('
                rri.order_item_id,
                SUM(
                    CASE
                        WHEN COALESCE(batch_costs.batch_qty, 0) > 0
                            THEN (COALESCE(rri.quantity, 0) / batch_costs.batch_qty) * batch_costs.batch_cost
                        ELSE 0
                    END
                ) as returned_cost
            ')
            ->groupBy('rri.order_item_id');
    }

    private function getRefundRestockCostAggSubquery()
    {
        return DB::table('refund_request_items as rri')
            ->join('refund_requests as rr', 'rr.id', '=', 'rri.refund_request_id')
            ->joinSub(
                DB::table('order_item_batches as oib')
                    ->join('stock_imports as si', 'si.id', '=', 'oib.stock_import_id')
                    ->selectRaw('
                        oib.order_item_id,
                        SUM(COALESCE(oib.quantity, 0) * COALESCE(si.cost_price, 0)) as batch_cost,
                        SUM(COALESCE(oib.quantity, 0)) as batch_qty
                    ')
                    ->groupBy('oib.order_item_id'),
                'batch_costs',
                function ($join) {
                    $join->on('batch_costs.order_item_id', '=', 'rri.order_item_id');
                }
            )
            ->where('rr.status', 'refunded')
            ->where('rri.condition_status', 'sealed')
            ->selectRaw('
                rri.order_item_id,
                SUM(
                    CASE
                        WHEN COALESCE(batch_costs.batch_qty, 0) > 0
                            THEN (COALESCE(rri.quantity, 0) / batch_costs.batch_qty) * batch_costs.batch_cost
                        ELSE 0
                    END
                ) as restock_cost
            ')
            ->groupBy('rri.order_item_id');
    }

    private function getRefundByOrderSubquery()
    {
        return DB::table('refund_requests')
            ->where('status', 'refunded')
            ->selectRaw('
                order_id,
                SUM(COALESCE(refund_total, 0)) as refund_total
            ')
            ->groupBy('order_id');
    }

    private function getInventoryMetrics(): object
    {
        $statuses = $this->deliveredStatuses();

        $totalImport = (float) DB::table('stock_imports')
            ->sum(DB::raw('COALESCE(imported_quantity, 0) * COALESCE(cost_price, 0)'));

        $completedCostAll = (float) DB::table('order_item_batches as oib')
            ->join('order_items as oi', 'oi.id', '=', 'oib.order_item_id')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('stock_imports as si', 'si.id', '=', 'oib.stock_import_id')
            ->whereIn('o.status', $statuses)
            ->sum(DB::raw('COALESCE(oib.quantity, 0) * COALESCE(si.cost_price, 0)'));

        $returnedCostAll = (float) DB::table('refund_request_items as rri')
            ->join('refund_requests as rr', 'rr.id', '=', 'rri.refund_request_id')
            ->join('order_items as oi', 'oi.id', '=', 'rri.order_item_id')
            ->joinSub(
                DB::table('order_item_batches as oib')
                    ->join('stock_imports as si', 'si.id', '=', 'oib.stock_import_id')
                    ->selectRaw('
                        oib.order_item_id,
                        SUM(COALESCE(oib.quantity, 0) * COALESCE(si.cost_price, 0)) as batch_cost,
                        SUM(COALESCE(oib.quantity, 0)) as batch_qty
                    ')
                    ->groupBy('oib.order_item_id'),
                'batch_costs',
                function ($join) {
                    $join->on('batch_costs.order_item_id', '=', 'oi.id');
                }
            )
            ->where('rr.status', 'refunded')
            ->sum(DB::raw('
                CASE
                    WHEN COALESCE(batch_costs.batch_qty, 0) > 0
                        THEN (COALESCE(rri.quantity, 0) / batch_costs.batch_qty) * batch_costs.batch_cost
                    ELSE 0
                END
            '));

        $expiredLossAll = (float) DB::table('stock_imports')
            ->whereNotNull('expired_at')
            ->sum(DB::raw('COALESCE(expired_quantity, 0) * COALESCE(cost_price, 0)'));

        $refundLossAll = (float) DB::table('refund_requests')
            ->where('status', 'refunded')
            ->sum(DB::raw('COALESCE(loss_amount, 0)'));

        $soldCostAll    = max(0, $completedCostAll - $returnedCostAll);
        $lossTotalAll   = $expiredLossAll + $refundLossAll;
        $inventoryValue = max(0, $totalImport - $soldCostAll - $lossTotalAll);

        return (object) [
            'total_import'       => $totalImport,
            'completed_cost'     => $completedCostAll,
            'returned_cost_all'  => $returnedCostAll,
            'sold_cost_all'      => $soldCostAll,
            'expired_loss_all'   => $expiredLossAll,
            'refund_loss_all'    => $refundLossAll,
            'loss_total_all'     => $lossTotalAll,
            'inventory_value'    => $inventoryValue,
            'total_qty'          => (float) DB::table('stock_imports')
                ->sum(DB::raw('COALESCE(remaining_quantity, 0)')),
            'balance_check'      => $totalImport - $soldCostAll - $lossTotalAll - $inventoryValue,
        ];
    }

    private function getInventoryValueAt(Carbon $timePoint): float
    {
        $statuses = $this->deliveredStatuses();

        $totalImportBefore = (float) DB::table('stock_imports')
            ->where('created_at', '<=', $timePoint)
            ->sum(DB::raw('COALESCE(imported_quantity, 0) * COALESCE(cost_price, 0)'));

        $completedCostBefore = (float) DB::table('order_item_batches as oib')
            ->join('order_items as oi', 'oi.id', '=', 'oib.order_item_id')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('stock_imports as si', 'si.id', '=', 'oib.stock_import_id')
            ->whereIn('o.status', $statuses)
            ->whereNotNull('o.delivered_at')
            ->where('o.delivered_at', '<=', $timePoint)
            ->sum(DB::raw('COALESCE(oib.quantity, 0) * COALESCE(si.cost_price, 0)'));

        $returnedCostBefore = (float) DB::table('refund_request_items as rri')
            ->join('refund_requests as rr', 'rr.id', '=', 'rri.refund_request_id')
            ->join('order_items as oi', 'oi.id', '=', 'rri.order_item_id')
            ->join('orders as o', 'o.id', '=', 'rr.order_id')
            ->joinSub(
                DB::table('order_item_batches as oib')
                    ->join('stock_imports as si', 'si.id', '=', 'oib.stock_import_id')
                    ->selectRaw('
                        oib.order_item_id,
                        SUM(COALESCE(oib.quantity, 0) * COALESCE(si.cost_price, 0)) as batch_cost,
                        SUM(COALESCE(oib.quantity, 0)) as batch_qty
                    ')
                    ->groupBy('oib.order_item_id'),
                'batch_costs',
                function ($join) {
                    $join->on('batch_costs.order_item_id', '=', 'oi.id');
                }
            )
            ->where('rr.status', 'refunded')
            ->whereIn('o.status', $statuses)
            ->where('rr.updated_at', '<=', $timePoint)
            ->sum(DB::raw('
                CASE
                    WHEN COALESCE(batch_costs.batch_qty, 0) > 0
                        THEN (COALESCE(rri.quantity, 0) / batch_costs.batch_qty) * batch_costs.batch_cost
                    ELSE 0
                END
            '));

        $expiredLossBefore = (float) DB::table('stock_imports')
            ->whereNotNull('expired_at')
            ->where('expired_at', '<=', $timePoint)
            ->sum(DB::raw('COALESCE(expired_quantity, 0) * COALESCE(cost_price, 0)'));

        $refundLossBefore = (float) DB::table('refund_requests')
            ->where('status', 'refunded')
            ->where('updated_at', '<=', $timePoint)
            ->sum(DB::raw('COALESCE(loss_amount, 0)'));

        $soldCostBefore = max(0, $completedCostBefore - $returnedCostBefore);
        $lossBefore = $expiredLossBefore + $refundLossBefore;

        return max(0, $totalImportBefore - $soldCostBefore - $lossBefore);
    }

    private function getPeriodInventoryMetrics($from, $to): object
    {
        $periodImport = (float) DB::table('stock_imports')
            ->whereBetween('created_at', [$from, $to])
            ->sum(DB::raw('COALESCE(imported_quantity, 0) * COALESCE(cost_price, 0)'));

        $periodExpiredLoss = (float) DB::table('stock_imports')
            ->whereNotNull('expired_at')
            ->whereBetween('expired_at', [$from, $to])
            ->sum(DB::raw('COALESCE(expired_quantity, 0) * COALESCE(cost_price, 0)'));

        $periodRefundLoss = (float) DB::table('refund_requests')
            ->where('status', 'refunded')
            ->whereBetween('updated_at', [$from, $to])
            ->sum(DB::raw('COALESCE(loss_amount, 0)'));

        $openingInventoryValue = $this->getInventoryValueAt($from->copy()->subSecond());
        $closingInventoryValue = $this->getInventoryValueAt($to);

        return (object) [
            'period_import'           => $periodImport,
            'period_loss'             => $periodExpiredLoss + $periodRefundLoss,
            'opening_inventory_value' => $openingInventoryValue,
            'closing_inventory_value' => $closingInventoryValue,
        ];
    }

    private function getReportData(Request $request): array
    {
        $statuses = $this->deliveredStatuses();

        $range = $this->getDateRange($request);
        $from  = $range['from'];
        $to    = $range['to'];

        $paidInAdvance = (float) DB::table('orders')
            ->where('payment_status', Order::PAYMENT_PAID)
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$from, $to])
            ->whereNotIn('status', array_merge($statuses, [Order::STATUS_CANCELLED]))
            ->sum(DB::raw('COALESCE(grand_total, 0)'));

        $inventoryMetrics = $this->getInventoryMetrics();
        $periodInventoryMetrics = $this->getPeriodInventoryMetrics($from, $to);

        $orderItemCostSub = $this->getOrderItemCostSubquery();
        $refundItemAggSub = $this->getRefundItemAggSubquery();
        $refundReturnedCostAggSub = $this->getRefundReturnedCostAggSubquery();
        $refundRestockCostAggSub = $this->getRefundRestockCostAggSubquery();
        $refundByOrderSub = $this->getRefundByOrderSubquery();

        $grossRevenue = (float) DB::table('orders')
            ->whereIn('status', $statuses)
            ->whereBetween('delivered_at', [$from, $to])
            ->sum(DB::raw('COALESCE(total, 0) + COALESCE(shipping_fee, 0)'));

        $refundAmountInRange = (float) DB::table('refund_requests')
            ->where('status', 'refunded')
            ->whereBetween('updated_at', [$from, $to])
            ->sum(DB::raw('COALESCE(refund_total, 0)'));

        $netRevenue = max(0, $grossRevenue - $refundAmountInRange);

        $shippingTotal = (float) DB::table('orders')
            ->where(function ($q) use ($statuses) {
                $q->whereIn('status', $statuses)
                    ->orWhere(function ($sub) {
                        $sub->where('status', Order::STATUS_CANCELLED)
                            ->whereNotNull('delivered_at');
                    });
            })
            ->whereBetween('delivered_at', [$from, $to])
            ->sum(DB::raw('COALESCE(shipping_fee, 0)'));

        $shippingCostTotal = (float) DB::table('orders')
            ->where(function ($q) use ($statuses) {
                $q->whereIn('status', $statuses)
                    ->orWhere(function ($sub) {
                        $sub->where('status', Order::STATUS_CANCELLED)
                            ->whereNotNull('delivered_at');
                    });
            })
            ->whereBetween('delivered_at', [$from, $to])
            ->sum(DB::raw("
                CASE
                    WHEN COALESCE(shipping_cost, 0) > 0 THEN shipping_cost
                    ELSE COALESCE(shipping_fee, 0)
                END
            "));

        $discountTotal = (float) DB::table('orders')
            ->whereIn('status', $statuses)
            ->whereBetween('delivered_at', [$from, $to])
            ->sum(DB::raw('COALESCE(discount, 0)'));

        $grossCost = (float) DB::table('order_item_batches as oib')
            ->join('order_items as oi', 'oi.id', '=', 'oib.order_item_id')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('stock_imports as si', 'si.id', '=', 'oib.stock_import_id')
            ->whereIn('o.status', $statuses)
            ->whereBetween('o.delivered_at', [$from, $to])
            ->sum(DB::raw('COALESCE(oib.quantity, 0) * COALESCE(si.cost_price, 0)'));

        $returnedCostInRange = (float) DB::table('refund_request_items as rri')
            ->join('refund_requests as rr', 'rr.id', '=', 'rri.refund_request_id')
            ->join('order_items as oi', 'oi.id', '=', 'rri.order_item_id')
            ->join('orders as o', 'o.id', '=', 'rr.order_id')
            ->joinSub(
                DB::table('order_item_batches as oib')
                    ->join('stock_imports as si', 'si.id', '=', 'oib.stock_import_id')
                    ->selectRaw('
                        oib.order_item_id,
                        SUM(COALESCE(oib.quantity, 0) * COALESCE(si.cost_price, 0)) as batch_cost,
                        SUM(COALESCE(oib.quantity, 0)) as batch_qty
                    ')
                    ->groupBy('oib.order_item_id'),
                'batch_costs',
                function ($join) {
                    $join->on('batch_costs.order_item_id', '=', 'oi.id');
                }
            )
            ->where('rr.status', 'refunded')
            ->whereBetween('rr.updated_at', [$from, $to])
            ->whereIn('o.status', $statuses)
            ->sum(DB::raw('
                CASE
                    WHEN COALESCE(batch_costs.batch_qty, 0) > 0
                        THEN (COALESCE(rri.quantity, 0) / batch_costs.batch_qty) * batch_costs.batch_cost
                    ELSE 0
                END
            '));

        $cost = max(0, $grossCost - $returnedCostInRange);

        $periodExpiredLoss = (float) DB::table('stock_imports')
            ->whereNotNull('expired_at')
            ->whereBetween('expired_at', [$from, $to])
            ->sum(DB::raw('COALESCE(expired_quantity, 0) * COALESCE(cost_price, 0)'));

        $periodRefundLoss = (float) DB::table('refund_requests')
            ->where('status', 'refunded')
            ->whereBetween('updated_at', [$from, $to])
            ->sum(DB::raw('COALESCE(loss_amount, 0)'));

        $finance = (object) [
            'revenue'                => $netRevenue,
            'gross_revenue'          => $grossRevenue,
            'refund_total'           => $refundAmountInRange,
            'net_revenue'            => $netRevenue,
            'cost'                   => $cost,
            'gross_cost'             => $grossCost,
            'returned_cost_in_range' => $returnedCostInRange,
            'profit'                 => $netRevenue - $cost - $shippingCostTotal - ($periodExpiredLoss + $periodRefundLoss),
            'shipping_total'         => $shippingTotal,
            'shipping_cost_total'    => $shippingCostTotal,
            'discount_total'         => $discountTotal,
        ];

        $days = $from->diffInDays($to) + 1;
        $prevFrom = (clone $from)->subDays($days);
        $prevTo   = (clone $from)->subSecond();

        $previousGrossRevenue = (float) DB::table('orders')
            ->whereIn('status', $statuses)
            ->whereBetween('delivered_at', [$prevFrom, $prevTo])
            ->sum(DB::raw('COALESCE(total, 0) + COALESCE(shipping_fee, 0)'));

        $previousRefundTotal = (float) DB::table('refund_requests')
            ->where('status', 'refunded')
            ->whereBetween('updated_at', [$prevFrom, $prevTo])
            ->sum(DB::raw('COALESCE(refund_total, 0)'));

        $previousNetRevenue = max(0, $previousGrossRevenue - $previousRefundTotal);

        $growth = $previousNetRevenue > 0
            ? (($netRevenue - $previousNetRevenue) / $previousNetRevenue) * 100
            : 0;

        $orderStats = DB::table('orders')
            ->selectRaw('
                COUNT(CASE WHEN created_at BETWEEN ? AND ? THEN 1 END) as total,
                SUM(CASE WHEN status = 1 AND created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 2 AND created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as shipping,
                SUM(CASE WHEN status = 3 AND delivered_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 4 AND cancelled_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN status = 5 AND updated_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as returned
            ', [
                $from,
                $to,
                $from,
                $to,
                $from,
                $to,
                $from,
                $to,
                $from,
                $to,
                $from,
                $to,
            ])
            ->first();

        $cancelBase = ($orderStats->completed ?? 0) + ($orderStats->cancelled ?? 0);

        $cancelRate = $cancelBase > 0
            ? (($orderStats->cancelled ?? 0) / $cancelBase) * 100
            : 0;

        $avgProcessingTime = DB::table('orders')
            ->whereIn('status', $statuses)
            ->whereNotNull('delivered_at')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, delivered_at)) as hours')
            ->value('hours');

        $dailyGrossRevenue = DB::table('orders')
            ->whereIn('status', $statuses)
            ->whereBetween('delivered_at', [$from, $to])
            ->selectRaw('
                DATE(delivered_at) as date,
                SUM(COALESCE(total, 0) + COALESCE(shipping_fee, 0)) as revenue
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $dailyRefund = DB::table('refund_requests')
            ->where('status', 'refunded')
            ->whereBetween('updated_at', [$from, $to])
            ->selectRaw('
                DATE(updated_at) as date,
                SUM(COALESCE(refund_total, 0)) as refund_total
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $weeklyRevenue = DB::table('orders')
            ->whereIn('status', $statuses)
            ->whereBetween('delivered_at', [$from, $to])
            ->selectRaw('
                YEARWEEK(delivered_at) as week,
                SUM(COALESCE(total, 0) + COALESCE(shipping_fee, 0)) as gross_revenue
            ')
            ->groupBy('week')
            ->orderBy('week')
            ->get()
            ->map(function ($row) use ($from, $to) {
                $refundInWeek = (float) DB::table('refund_requests')
                    ->where('status', 'refunded')
                    ->whereBetween('updated_at', [$from, $to])
                    ->whereRaw('YEARWEEK(updated_at) = ?', [$row->week])
                    ->sum(DB::raw('COALESCE(refund_total, 0)'));

                $row->revenue = max(0, (float) $row->gross_revenue - $refundInWeek);
                unset($row->gross_revenue);

                return $row;
            });

        $monthlyRevenue = DB::table('orders')
            ->whereIn('status', $statuses)
            ->whereBetween('delivered_at', [$from, $to])
            ->selectRaw('
                DATE_FORMAT(delivered_at, "%Y-%m") as month,
                SUM(COALESCE(total, 0) + COALESCE(shipping_fee, 0)) as gross_revenue
            ')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($row) use ($from, $to) {
                $refundInMonth = (float) DB::table('refund_requests')
                    ->where('status', 'refunded')
                    ->whereBetween('updated_at', [$from, $to])
                    ->whereRaw('DATE_FORMAT(updated_at, "%Y-%m") = ?', [$row->month])
                    ->sum(DB::raw('COALESCE(refund_total, 0)'));

                $row->revenue = max(0, (float) $row->gross_revenue - $refundInMonth);
                unset($row->gross_revenue);

                return $row;
            });

        $yearlyRevenue = DB::table('orders')
            ->whereIn('status', $statuses)
            ->whereNotNull('delivered_at')
            ->selectRaw('
                YEAR(delivered_at) as year,
                SUM(COALESCE(total, 0) + COALESCE(shipping_fee, 0)) as gross_revenue
            ')
            ->groupBy('year')
            ->orderBy('year')
            ->get()
            ->map(function ($row) {
                $refundInYear = (float) DB::table('refund_requests')
                    ->where('status', 'refunded')
                    ->whereRaw('YEAR(updated_at) = ?', [$row->year])
                    ->sum(DB::raw('COALESCE(refund_total, 0)'));

                $row->revenue = max(0, (float) $row->gross_revenue - $refundInYear);
                unset($row->gross_revenue);

                return $row;
            });

        $dailyGrossCost = DB::table('order_item_batches as oib')
            ->join('order_items as oi', 'oi.id', '=', 'oib.order_item_id')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('stock_imports as si', 'si.id', '=', 'oib.stock_import_id')
            ->whereIn('o.status', $statuses)
            ->whereBetween('o.delivered_at', [$from, $to])
            ->selectRaw('
                DATE(o.delivered_at) as date,
                SUM(COALESCE(oib.quantity, 0) * COALESCE(si.cost_price, 0)) as gross_cost
            ')
            ->groupByRaw('DATE(o.delivered_at)')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $dailyReturnedCost = DB::table('refund_request_items as rri')
            ->join('refund_requests as rr', 'rr.id', '=', 'rri.refund_request_id')
            ->join('order_items as oi', 'oi.id', '=', 'rri.order_item_id')
            ->join('orders as o', 'o.id', '=', 'rr.order_id')
            ->joinSub(
                DB::table('order_item_batches as oib')
                    ->join('stock_imports as si', 'si.id', '=', 'oib.stock_import_id')
                    ->selectRaw('
                        oib.order_item_id,
                        SUM(COALESCE(oib.quantity, 0) * COALESCE(si.cost_price, 0)) as batch_cost,
                        SUM(COALESCE(oib.quantity, 0)) as batch_qty
                    ')
                    ->groupBy('oib.order_item_id'),
                'batch_costs',
                function ($join) {
                    $join->on('batch_costs.order_item_id', '=', 'oi.id');
                }
            )
            ->where('rr.status', 'refunded')
            ->whereIn('o.status', $statuses)
            ->whereBetween('rr.updated_at', [$from, $to])
            ->selectRaw('
                DATE(rr.updated_at) as date,
                SUM(
                    CASE
                        WHEN COALESCE(batch_costs.batch_qty, 0) > 0
                            THEN (COALESCE(rri.quantity, 0) / batch_costs.batch_qty) * batch_costs.batch_cost
                        ELSE 0
                    END
                ) as returned_cost
            ')
            ->groupByRaw('DATE(rr.updated_at)')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $dailyShippingCost = DB::table('orders')
            ->where(function ($q) use ($statuses) {
                $q->whereIn('status', $statuses)
                    ->orWhere(function ($sub) {
                        $sub->where('status', Order::STATUS_CANCELLED)
                            ->whereNotNull('delivered_at');
                    });
            })
            ->whereBetween('delivered_at', [$from, $to])
            ->selectRaw('
                DATE(delivered_at) as date,
                SUM(
                    CASE
                        WHEN COALESCE(shipping_cost, 0) > 0 THEN shipping_cost
                        ELSE COALESCE(shipping_fee, 0)
                    END
                ) as shipping_cost
            ')
            ->groupByRaw('DATE(delivered_at)')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $dailyExpiredLoss = DB::table('stock_imports')
            ->whereNotNull('expired_at')
            ->whereBetween('expired_at', [$from, $to])
            ->selectRaw('
                DATE(expired_at) as date,
                SUM(COALESCE(expired_quantity, 0) * COALESCE(cost_price, 0)) as expired_loss
            ')
            ->groupByRaw('DATE(expired_at)')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $dailyRefundLoss = DB::table('refund_requests')
            ->where('status', 'refunded')
            ->whereBetween('updated_at', [$from, $to])
            ->selectRaw('
                DATE(updated_at) as date,
                SUM(COALESCE(loss_amount, 0)) as refund_loss
            ')
            ->groupByRaw('DATE(updated_at)')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chartLabels  = [];
        $chartRevenue = [];
        $chartProfit  = [];

        $currentDate = $from->copy()->startOfDay();
        $endDate     = $to->copy()->startOfDay();

        while ($currentDate->lte($endDate)) {
            $date = $currentDate->format('Y-m-d');

            $chartLabels[] = $date;

            $grossRevenueDay = (float) ($dailyGrossRevenue[$date]->revenue ?? 0);
            $refundDay       = (float) ($dailyRefund[$date]->refund_total ?? 0);
            $netRevenueDay   = max(0, $grossRevenueDay - $refundDay);

            $chartRevenue[] = $netRevenueDay;

            $grossCostDay    = (float) ($dailyGrossCost[$date]->gross_cost ?? 0);
            $returnedCostDay = (float) ($dailyReturnedCost[$date]->returned_cost ?? 0);
            $costDay         = max(0, $grossCostDay - $returnedCostDay);

            $shippingCost   = (float) ($dailyShippingCost[$date]->shipping_cost ?? 0);
            $expiredLossDay = (float) ($dailyExpiredLoss[$date]->expired_loss ?? 0);
            $refundLossDay  = (float) ($dailyRefundLoss[$date]->refund_loss ?? 0);

            $chartProfit[] =
                $netRevenueDay
                - $costDay
                - $shippingCost
                - $expiredLossDay
                - $refundLossDay;

            $currentDate->addDay();
        }

        $topProducts = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('product_variants as pv', 'pv.id', '=', 'oi.variant_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->leftJoinSub($refundItemAggSub, 'ria', function ($join) {
                $join->on('ria.order_item_id', '=', 'oi.id');
            })
            ->leftJoinSub($orderItemCostSub, 'oic', function ($join) {
                $join->on('oic.order_item_id', '=', 'oi.id');
            })
            ->leftJoinSub($refundReturnedCostAggSub, 'rrtca', function ($join) {
                $join->on('rrtca.order_item_id', '=', 'oi.id');
            })
            ->whereIn('o.status', $statuses)
            ->whereBetween('o.delivered_at', [$from, $to])
            ->select(
                'p.name',
                DB::raw('SUM(
                    CASE
                        WHEN COALESCE(oi.quantity, 0) - COALESCE(ria.refunded_qty, 0) > 0
                            THEN COALESCE(oi.quantity, 0) - COALESCE(ria.refunded_qty, 0)
                        ELSE 0
                    END
                ) as total_sold'),
                DB::raw('SUM(
                    CASE
                        WHEN (COALESCE(oi.quantity, 0) * COALESCE(oi.price, 0)) - COALESCE(ria.refunded_amount, 0) > 0
                            THEN (COALESCE(oi.quantity, 0) * COALESCE(oi.price, 0)) - COALESCE(ria.refunded_amount, 0)
                        ELSE 0
                    END
                ) as revenue'),
                DB::raw('SUM(
                    (
                        CASE
                            WHEN (COALESCE(oi.quantity, 0) * COALESCE(oi.price, 0)) - COALESCE(ria.refunded_amount, 0) > 0
                                THEN (COALESCE(oi.quantity, 0) * COALESCE(oi.price, 0)) - COALESCE(ria.refunded_amount, 0)
                            ELSE 0
                        END
                    ) -
                    (
                        CASE
                            WHEN COALESCE(oic.item_cost, 0) - COALESCE(rrtca.returned_cost, 0) > 0
                                THEN COALESCE(oic.item_cost, 0) - COALESCE(rrtca.returned_cost, 0)
                            ELSE 0
                        END
                    )
                ) as profit')
            )
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        $slowMoving = DB::table('products as p')
            ->join('product_variants as pv', 'pv.product_id', '=', 'p.id')
            ->leftJoin('order_items as oi', 'oi.variant_id', '=', 'pv.id')
            ->select(
                'p.id',
                'p.name',
                DB::raw('SUM(COALESCE(pv.stock_quantity, 0)) as stock_quantity'),
                DB::raw('MAX(oi.created_at) as last_sold')
            )
            ->groupBy('p.id', 'p.name')
            ->orderByRaw('MAX(oi.created_at) IS NULL DESC')
            ->orderByRaw('MAX(oi.created_at) ASC')
            ->limit(5)
            ->get();

        $mostViewed = DB::table('wishlists as w')
            ->join('products as p', 'p.id', '=', 'w.product_id')
            ->select(
                'p.name',
                DB::raw('COUNT(w.id) as total_wishlist')
            )
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('total_wishlist')
            ->limit(5)
            ->get();

        $topCustomers = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->leftJoinSub($refundByOrderSub, 'rbo', function ($join) {
                $join->on('rbo.order_id', '=', 'orders.id');
            })
            ->whereIn('orders.status', $statuses)
            ->whereBetween('orders.delivered_at', [$from, $to])
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(
                    CASE
                        WHEN (COALESCE(orders.total, 0) + COALESCE(orders.shipping_fee, 0)) - COALESCE(rbo.refund_total, 0) > 0
                            THEN (COALESCE(orders.total, 0) + COALESCE(orders.shipping_fee, 0)) - COALESCE(rbo.refund_total, 0)
                        ELSE 0
                    END
                ) as spending')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('spending')
            ->limit(5)
            ->get();

        $lowStock = DB::table('product_variants as pv')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->where('pv.stock_quantity', '<=', 5)
            ->select('p.name', 'pv.attribute_value', 'pv.stock_quantity')
            ->orderBy('pv.stock_quantity')
            ->limit(5)
            ->get();

        $cancelList = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->where('orders.status', Order::STATUS_CANCELLED)
            ->whereBetween(
                DB::raw('COALESCE(orders.cancelled_at, orders.updated_at, orders.created_at)'),
                [$from, $to]
            )
            ->select(
                'orders.id',
                'users.name as customer_name',
                DB::raw('COALESCE(orders.total, 0) + COALESCE(orders.shipping_fee, 0) as total'),
                DB::raw('COALESCE(orders.cancelled_at, orders.updated_at, orders.created_at) as cancelled_at')
            )
            ->orderByDesc(DB::raw('COALESCE(orders.cancelled_at, orders.updated_at, orders.created_at)'))
            ->limit(5)
            ->get();

        $refunds = DB::table('refund_requests as rr')
            ->join('orders as o', 'o.id', '=', 'rr.order_id')
            ->join('users as u', 'u.id', '=', 'o.user_id')
            ->where('rr.status', 'refunded')
            ->whereBetween('rr.updated_at', [$from, $to])
            ->select(
                'rr.id',
                'o.id as order_id',
                'u.name as customer_name',
                'rr.refund_total',
                'rr.loss_amount',
                'rr.reason',
                'rr.updated_at as refunded_at'
            )
            ->orderByDesc('rr.updated_at')
            ->limit(5)
            ->get();

        return [
            'from' => $range['from_date'],
            'to'   => $range['to_date'],

            'finance' => $finance,
            'growth'  => $growth,

            'orderStats'        => $orderStats,
            'cancelRate'        => $cancelRate,
            'avgProcessingTime' => $avgProcessingTime,

            'dailyRevenue' => collect($chartLabels)->map(function ($date, $index) use ($chartRevenue) {
                return (object) [
                    'date'    => $date,
                    'revenue' => $chartRevenue[$index] ?? 0,
                ];
            }),

            'weeklyRevenue'  => $weeklyRevenue,
            'monthlyRevenue' => $monthlyRevenue,
            'yearlyRevenue'  => $yearlyRevenue,

            'dailyProfit' => collect($chartLabels)->map(function ($date, $index) use ($chartProfit) {
                return (object) [
                    'date'   => $date,
                    'profit' => $chartProfit[$index] ?? 0,
                ];
            }),

            'chartLabels'  => $chartLabels,
            'chartRevenue' => $chartRevenue,
            'chartProfit'  => $chartProfit,

            'topProducts'  => $topProducts,
            'topCustomers' => $topCustomers,

            'slowMoving' => $slowMoving,
            'mostViewed' => $mostViewed,

            'inventory' => (object) [
                'total_qty'   => $inventoryMetrics->total_qty,
                'total_value' => $inventoryMetrics->inventory_value,
            ],

            'inventoryMetrics'       => $inventoryMetrics,
            'periodInventoryMetrics' => $periodInventoryMetrics,
            'periodLoss'             => $periodExpiredLoss + $periodRefundLoss,

            'lowStock'      => $lowStock,
            'cancelList'    => $cancelList,
            'refunds'       => $refunds,
            'paidInAdvance' => $paidInAdvance,
        ];
    }

    public function products(Request $request)
    {
        $statuses = $this->deliveredStatuses();
        $range = $this->getDateRange($request);
        $keyword = trim((string) $request->keyword);

        $refundItemAggSub = $this->getRefundItemAggSubquery();

        $query = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('product_variants as pv', 'pv.id', '=', 'oi.variant_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->leftJoinSub($refundItemAggSub, 'ria', function ($join) {
                $join->on('ria.order_item_id', '=', 'oi.id');
            })
            ->whereIn('o.status', $statuses)
            ->whereBetween('o.delivered_at', [$range['from'], $range['to']])
            ->select(
                'p.name',
                DB::raw('SUM(
                    CASE
                        WHEN COALESCE(oi.quantity, 0) - COALESCE(ria.refunded_qty, 0) > 0
                            THEN COALESCE(oi.quantity, 0) - COALESCE(ria.refunded_qty, 0)
                        ELSE 0
                    END
                ) as total_sold'),
                DB::raw('SUM(
                    CASE
                        WHEN (COALESCE(oi.quantity, 0) * COALESCE(oi.price, 0)) - COALESCE(ria.refunded_amount, 0) > 0
                            THEN (COALESCE(oi.quantity, 0) * COALESCE(oi.price, 0)) - COALESCE(ria.refunded_amount, 0)
                        ELSE 0
                    END
                ) as revenue')
            )
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('revenue');

        if ($keyword !== '') {
            $query->where('p.name', 'like', "%{$keyword}%");
        }

        $products = $query->paginate(20)->withQueryString();

        return view('admin.reports.products', [
            'products' => $products,
            'from'     => $range['from_date'],
            'to'       => $range['to_date'],
            'keyword'  => $keyword,
        ]);
    }

    public function customers(Request $request)
    {
        $statuses = $this->deliveredStatuses();
        $range = $this->getDateRange($request);
        $keyword = trim((string) $request->keyword);

        $refundByOrderSub = $this->getRefundByOrderSubquery();

        $query = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->leftJoinSub($refundByOrderSub, 'rbo', function ($join) {
                $join->on('rbo.order_id', '=', 'orders.id');
            })
            ->whereIn('orders.status', $statuses)
            ->whereBetween('orders.delivered_at', [$range['from'], $range['to']])
            ->select(
                'users.id',
                'users.name',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(
                    CASE
                        WHEN (COALESCE(orders.total, 0) + COALESCE(orders.shipping_fee, 0)) - COALESCE(rbo.refund_total, 0) > 0
                            THEN (COALESCE(orders.total, 0) + COALESCE(orders.shipping_fee, 0)) - COALESCE(rbo.refund_total, 0)
                        ELSE 0
                    END
                ) as spending')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('spending');

        if ($keyword !== '') {
            $query->where('users.name', 'like', "%{$keyword}%");
        }

        $customers = $query->paginate(20)->withQueryString();

        return view('admin.reports.customers', [
            'customers' => $customers,
            'from'      => $range['from_date'],
            'to'        => $range['to_date'],
            'keyword'   => $keyword,
        ]);
    }

    public function slowProducts(Request $request)
    {
        $keyword = trim((string) $request->keyword);
        $days = $request->days;

        $query = DB::table('products as p')
            ->join('product_variants as pv', 'pv.product_id', '=', 'p.id')
            ->leftJoin('order_items as oi', 'oi.variant_id', '=', 'pv.id')
            ->select(
                'p.id',
                'p.name',
                DB::raw('SUM(COALESCE(pv.stock_quantity, 0)) as stock_quantity'),
                DB::raw('MAX(oi.created_at) as last_sold')
            )
            ->groupBy('p.id', 'p.name');

        if ($keyword !== '') {
            $query->where('p.name', 'like', "%{$keyword}%");
        }

        if (!empty($days) && is_numeric($days)) {
            $query->havingRaw(
                'MAX(oi.created_at) IS NULL OR MAX(oi.created_at) <= ?',
                [now()->subDays((int) $days)]
            );
        }

        $query->orderByRaw('MAX(oi.created_at) IS NULL DESC')
            ->orderByRaw('MAX(oi.created_at) ASC');

        $products = $query->paginate(20)->withQueryString();

        return view('admin.reports.slow_products', [
            'products' => $products,
            'keyword'  => $keyword,
            'days'     => $days,
        ]);
    }

    public function lowStock(Request $request)
    {
        $keyword = trim((string) $request->keyword);
        $maxStock = is_numeric($request->max_stock) ? (int) $request->max_stock : 100;

        $query = DB::table('product_variants as pv')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->where('pv.stock_quantity', '<=', $maxStock)
            ->select('p.name', 'pv.attribute_value', 'pv.stock_quantity')
            ->orderBy('pv.stock_quantity');

        if ($keyword !== '') {
            $query->where('p.name', 'like', "%{$keyword}%");
        }

        $products = $query->paginate(20)->withQueryString();

        return view('admin.reports.low_stock', [
            'products'  => $products,
            'keyword'   => $keyword,
            'max_stock' => $maxStock,
        ]);
    }

    public function wishlist(Request $request)
    {
        $keyword = trim((string) $request->keyword);

        $query = DB::table('wishlists as w')
            ->join('products as p', 'p.id', '=', 'w.product_id')
            ->select(
                'p.name',
                DB::raw('COUNT(w.id) as total_wishlist')
            )
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('total_wishlist');

        if ($keyword !== '') {
            $query->where('p.name', 'like', "%{$keyword}%");
        }

        $products = $query->paginate(20)->withQueryString();

        return view('admin.reports.wishlist', [
            'products' => $products,
            'keyword'  => $keyword,
        ]);
    }

    public function cancelOrders(Request $request)
    {
        $range = $this->getDateRange($request);
        $keyword = trim((string) $request->keyword);

        $query = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->where('orders.status', Order::STATUS_CANCELLED)
            ->whereBetween(
                DB::raw('COALESCE(orders.cancelled_at, orders.updated_at, orders.created_at)'),
                [$range['from'], $range['to']]
            )
            ->select(
                'orders.id',
                'users.name as customer_name',
                DB::raw('COALESCE(orders.total, 0) + COALESCE(orders.shipping_fee, 0) as total'),
                DB::raw('COALESCE(orders.cancelled_at, orders.updated_at, orders.created_at) as cancelled_at')
            )
            ->orderByDesc(DB::raw('COALESCE(orders.cancelled_at, orders.updated_at, orders.created_at)'));

        if ($keyword !== '') {
            $query->where('users.name', 'like', "%{$keyword}%");
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('admin.reports.cancel_orders', [
            'orders'  => $orders,
            'from'    => $range['from_date'],
            'to'      => $range['to_date'],
            'keyword' => $keyword,
        ]);
    }

    public function refundOrders(Request $request)
    {
        $range = $this->getDateRange($request);
        $keyword = trim((string) $request->keyword);

        $query = DB::table('refund_requests as rr')
            ->join('orders as o', 'o.id', '=', 'rr.order_id')
            ->join('users as u', 'u.id', '=', 'o.user_id')
            ->where('rr.status', 'refunded')
            ->whereBetween('rr.updated_at', [$range['from'], $range['to']])
            ->select(
                'rr.id',
                'o.id as order_id',
                'u.name as customer_name',
                'rr.refund_total',
                'rr.loss_amount',
                'rr.reason',
                'rr.updated_at as refunded_at',
                'rr.status'
            )
            ->orderByDesc('rr.updated_at');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('u.name', 'like', "%{$keyword}%")
                    ->orWhere('o.id', 'like', "%{$keyword}%");
            });
        }

        $refunds = $query->paginate(20)->withQueryString();

        return view('admin.reports.refund_orders', [
            'refunds' => $refunds,
            'from' => $range['from_date'],
            'to' => $range['to_date'],
            'keyword' => $keyword,
        ]);
    }

    public function customerOrders(Request $request, $customerId)
    {
        $range = $this->getDateRange($request);
        $statuses = $this->deliveredStatuses();

        $orders = DB::table('orders')
            ->where('user_id', $customerId)
            ->whereIn('status', $statuses)
            ->whereBetween('delivered_at', [$range['from'], $range['to']])
            ->select(
                'id',
                'status',
                'created_at',
                'delivered_at',
                DB::raw('COALESCE(total, 0) + COALESCE(shipping_fee, 0) as total')
            )
            ->orderByDesc('delivered_at')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'created_at' => $order->created_at
                        ? Carbon::parse($order->created_at)->format('d/m/Y H:i')
                        : '',
                    'total' => number_format((int) ($order->total ?? 0)) . ' đ',
                    'status_label' => match ((int) $order->status) {
                        Order::STATUS_PENDING => '<span class="badge bg-warning text-dark">Chờ xử lý</span>',
                        Order::STATUS_PROCESSING => '<span class="badge bg-info text-dark">Đang giao</span>',
                        Order::STATUS_COMPLETED => '<span class="badge bg-success">Hoàn thành</span>',
                        Order::STATUS_CANCELLED => '<span class="badge bg-danger">Đã hủy</span>',
                        Order::STATUS_RETURNED => '<span class="badge bg-secondary">Hoàn trả</span>',
                        default => '<span class="badge bg-light text-dark">Không rõ</span>',
                    },
                ];
            })
            ->values();

        return response()->json([
            'orders' => $orders,
        ]);
    }
}