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

    private function buildViewData(array $data): array
    {
        /*
        |--------------------------------------------------------------------------
        | KPI THEO KỲ LỌC
        |--------------------------------------------------------------------------
        */
        $data['revenue']           = (float) ($data['finance']->revenue ?? 0);
        $data['totalDiscount']     = (float) ($data['finance']->discount_total ?? 0);
        $data['shippingCollected'] = (float) ($data['finance']->shipping_total ?? 0);
        $data['shippingCostTotal'] = (float) ($data['finance']->shipping_cost_total ?? 0);
        $data['totalOrders']       = (int) ($data['orderStats']->completed ?? 0);
        $data['cancelRate']        = (float) ($data['cancelRate'] ?? 0);

        $data['freeShippingLoss'] = max(
            0,
            $data['shippingCostTotal'] - $data['shippingCollected']
        );

        $data['shippingProfit'] = $data['shippingCollected'] - $data['shippingCostTotal'];

        $data['averageOrder'] = $data['totalOrders'] > 0
            ? $data['revenue'] / $data['totalOrders']
            : 0;

        /*
        |--------------------------------------------------------------------------
        | SỐ THEO KỲ
        |--------------------------------------------------------------------------
        */
        $data['periodCost']   = (float) ($data['finance']->cost ?? 0);
        $data['periodLoss']   = (float) ($data['periodLoss'] ?? 0);
        $data['periodImport'] = (float) ($data['periodInventoryMetrics']->period_import ?? 0);
        $data['openingInventoryValue'] = (float) ($data['periodInventoryMetrics']->opening_inventory_value ?? 0);
        $data['closingInventoryValue'] = (float) ($data['periodInventoryMetrics']->closing_inventory_value ?? 0);

        /*
        |--------------------------------------------------------------------------
        | LỢI NHUẬN THEO KỲ
        |--------------------------------------------------------------------------
        */
        $data['saleProfit'] = $data['revenue'] - $data['periodCost'];

        $data['realProfit'] =
            $data['revenue']
            - $data['periodCost']
            - $data['shippingCostTotal']
            - $data['periodLoss'];

        $data['profit'] = $data['realProfit'];

        $data['margin'] = $data['revenue'] > 0
            ? ($data['profit'] / $data['revenue']) * 100
            : 0;

        /*
        |--------------------------------------------------------------------------
        | SỐ HIỆN TẠI / LŨY KẾ
        |--------------------------------------------------------------------------
        */
        $data['totalImportAll']    = (float) ($data['inventoryMetrics']->total_import ?? 0);
        $data['inventoryValueNow'] = (float) ($data['inventoryMetrics']->inventory_value ?? 0);
        $data['totalCostAll']      = (float) ($data['inventoryMetrics']->sold_cost_all ?? 0);
        $data['inventoryLossAll']  = (float) ($data['inventoryMetrics']->loss_total_all ?? 0);

        /*
        |--------------------------------------------------------------------------
        | BIẾN HIỂN THỊ TRÊN DASHBOARD
        |--------------------------------------------------------------------------
        */
        $data['totalCost']      = $data['periodCost'];
        $data['inventoryLoss']  = $data['periodLoss'];
        $data['totalImport']    = $data['periodImport'];
        $data['inventoryValue'] = $data['closingInventoryValue'];

        /*
        |--------------------------------------------------------------------------
        | CHECK CÂN KHO TOÀN HỆ THỐNG
        |--------------------------------------------------------------------------
        */
        $data['inventoryBalanceCheck'] =
            ($data['totalImportAll'] ?? 0)
            - ($data['totalCostAll'] ?? 0)
            - ($data['inventoryLossAll'] ?? 0)
            - ($data['inventoryValueNow'] ?? 0);

        /*
        |--------------------------------------------------------------------------
        | CHECK BIẾN ĐỘNG KHO THEO KỲ
        |--------------------------------------------------------------------------
        | Tồn đầu + Nhập - Bán - Hao hụt = Tồn cuối
        |--------------------------------------------------------------------------
        */
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

    /**
     * SỐ LIỆU KHO / VỐN TOÀN BỘ HỆ THỐNG
     */
    private function getInventoryMetrics(): object
    {
        $totalImport = (float) DB::table('stock_imports')
            ->sum(DB::raw('COALESCE(imported_quantity, 0) * COALESCE(cost_price, 0)'));

        $completedCostAll = (float) DB::table('order_item_batches as oib')
            ->join('order_items as oi', 'oi.id', '=', 'oib.order_item_id')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('stock_imports as si', 'si.id', '=', 'oib.stock_import_id')
            ->where('o.status', Order::STATUS_COMPLETED)
            ->sum(DB::raw('COALESCE(oib.quantity, 0) * COALESCE(si.cost_price, 0)'));

        $restockCostAll = (float) DB::table('refund_request_items as rri')
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
            ->where('rri.condition_status', 'sealed')
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

        $soldCostAll    = max(0, $completedCostAll - $restockCostAll);
        $lossTotalAll   = $expiredLossAll + $refundLossAll;
        $inventoryValue = max(0, $totalImport - $soldCostAll - $lossTotalAll);

        return (object) [
            'total_import'      => $totalImport,
            'completed_cost'    => $completedCostAll,
            'restock_cost'      => $restockCostAll,
            'sold_cost_all'     => $soldCostAll,
            'expired_loss_all'  => $expiredLossAll,
            'refund_loss_all'   => $refundLossAll,
            'loss_total_all'    => $lossTotalAll,
            'inventory_value'   => $inventoryValue,
            'total_qty'         => (float) DB::table('stock_imports')
                ->sum(DB::raw('COALESCE(remaining_quantity, 0)')),
            'balance_check'     => $totalImport - $soldCostAll - $lossTotalAll - $inventoryValue,
        ];
    }

    /**
     * Tính giá trị tồn kho tại 1 thời điểm
     * Giá trị tồn = tổng nhập trước mốc - tổng bán trước mốc - tổng hao hụt trước mốc
     */
    private function getInventoryValueAt(Carbon $timePoint): float
    {
        $totalImportBefore = (float) DB::table('stock_imports')
        ->where('created_at', '<=', $timePoint)
            ->sum(DB::raw('COALESCE(imported_quantity, 0) * COALESCE(cost_price, 0)'));

        $completedCostBefore = (float) DB::table('order_item_batches as oib')
        ->join('order_items as oi', 'oi.id', '=', 'oib.order_item_id')
        ->join('orders as o', 'o.id', '=', 'oi.order_id')
        ->join('stock_imports as si', 'si.id', '=', 'oib.stock_import_id')
        ->where('o.status', Order::STATUS_COMPLETED)
            ->whereNotNull('o.delivered_at')
            ->where('o.delivered_at', '<=', $timePoint)
            ->sum(DB::raw('COALESCE(oib.quantity, 0) * COALESCE(si.cost_price, 0)'));

        $restockCostBefore = (float) DB::table('refund_request_items as rri')
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
            ->where('o.status', Order::STATUS_COMPLETED)
            ->where('rri.condition_status', 'sealed')
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

        $soldCostBefore = max(0, $completedCostBefore - $restockCostBefore);
        $lossBefore = $expiredLossBefore + $refundLossBefore;

        return max(0, $totalImportBefore - $soldCostBefore - $lossBefore);
    }

    /**
     * SỐ NHẬP / HAO HỤT / TỒN ĐẦU KỲ / TỒN CUỐI KỲ
     */
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
        $range = $this->getDateRange($request);
        $from  = $range['from'];
        $to    = $range['to'];

        $inventoryMetrics = $this->getInventoryMetrics();
        $periodInventoryMetrics = $this->getPeriodInventoryMetrics($from, $to);

        $refundSummarySub = DB::table('refund_requests')
            ->selectRaw('
                order_id,
                SUM(COALESCE(refund_total, 0)) as refund_total,
                SUM(COALESCE(loss_amount, 0)) as refund_loss
            ')
            ->where('status', 'refunded')
            ->whereBetween('updated_at', [$from, $to])
            ->groupBy('order_id');

        /*
        =====================================
        1. TÀI CHÍNH THEO KỲ
        =====================================
        */
        $grossRevenue = (float) DB::table('orders')
            ->where('status', Order::STATUS_COMPLETED)
            ->whereBetween('delivered_at', [$from, $to])
            ->sum(DB::raw('COALESCE(total, 0)'));

        $refundAmountInRange = (float) DB::table('refund_requests')
            ->where('status', 'refunded')
            ->whereBetween('updated_at', [$from, $to])
            ->sum(DB::raw('COALESCE(refund_total, 0)'));

        $revenue = max(0, $grossRevenue - $refundAmountInRange);

        $shippingTotal = (float) DB::table('orders')
            ->where(function ($q) {
                $q->whereIn('status', [
                    Order::STATUS_COMPLETED,
                    Order::STATUS_RETURNED,
                ])->orWhere(function ($sub) {
                    $sub->where('status', Order::STATUS_CANCELLED)
                        ->whereNotNull('delivered_at');
                });
            })
            ->whereBetween('delivered_at', [$from, $to])
            ->sum(DB::raw('COALESCE(shipping_fee, 0)'));

        $shippingCostTotal = (float) DB::table('orders')
            ->where(function ($q) {
                $q->whereIn('status', [
                    Order::STATUS_COMPLETED,
                    Order::STATUS_RETURNED,
                ])->orWhere(function ($sub) {
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
            ->where('status', Order::STATUS_COMPLETED)
            ->whereBetween('delivered_at', [$from, $to])
            ->sum(DB::raw('COALESCE(discount, 0)'));

        /*
        =====================================
        GIÁ VỐN ĐÃ BÁN THEO KỲ
        =====================================
        */
        $grossCost = (float) DB::table('order_item_batches as oib')
            ->join('order_items as oi', 'oi.id', '=', 'oib.order_item_id')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('stock_imports as si', 'si.id', '=', 'oib.stock_import_id')
            ->where('o.status', Order::STATUS_COMPLETED)
            ->whereBetween('o.delivered_at', [$from, $to])
            ->sum(DB::raw('COALESCE(oib.quantity, 0) * COALESCE(si.cost_price, 0)'));

        $restockCostInRange = (float) DB::table('refund_request_items as rri')
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
            ->where('o.status', Order::STATUS_COMPLETED)
            ->where('rri.condition_status', 'sealed')
            ->sum(DB::raw('
                CASE
                    WHEN COALESCE(batch_costs.batch_qty, 0) > 0
                        THEN (COALESCE(rri.quantity, 0) / batch_costs.batch_qty) * batch_costs.batch_cost
                    ELSE 0
                END
            '));

        $cost = max(0, $grossCost - $restockCostInRange);

        $finance = (object) [
            'revenue'               => $revenue,
            'gross_revenue'         => $grossRevenue,
            'refund_total'          => $refundAmountInRange,
            'cost'                  => $cost,
            'gross_cost'            => $grossCost,
            'restock_cost_in_range' => $restockCostInRange,
            'profit'                => $revenue - $cost - $shippingCostTotal,
            'shipping_total'        => $shippingTotal,
            'shipping_cost_total'   => $shippingCostTotal,
            'discount_total'        => $discountTotal,
        ];

        /*
        =====================================
        2. TĂNG TRƯỞNG
        =====================================
        */
        $days = $from->diffInDays($to) + 1;
        $prevFrom = (clone $from)->subDays($days);
        $prevTo   = (clone $from)->subSecond();

        $previousGrossRevenue = (float) DB::table('orders')
            ->where('status', Order::STATUS_COMPLETED)
            ->whereBetween('delivered_at', [$prevFrom, $prevTo])
            ->sum(DB::raw('COALESCE(total, 0)'));

        $previousRefundAmount = (float) DB::table('refund_requests')
            ->where('status', 'refunded')
            ->whereBetween('updated_at', [$prevFrom, $prevTo])
            ->sum(DB::raw('COALESCE(refund_total, 0)'));

        $previousRevenue = max(0, $previousGrossRevenue - $previousRefundAmount);

        $growth = $previousRevenue > 0
            ? (($revenue - $previousRevenue) / $previousRevenue) * 100
            : 0;

        /*
        =====================================
        3. THỐNG KÊ ĐƠN
        =====================================
        */
        $orderStats = DB::table('orders')
            ->selectRaw('
                COUNT(CASE WHEN created_at BETWEEN ? AND ? THEN 1 END) as total,
                SUM(CASE WHEN status = 1 AND created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 2 AND created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as shipping,
                SUM(CASE WHEN status = 3 AND delivered_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 4 AND updated_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as cancelled,
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

        $validTotal =
            ($orderStats->completed ?? 0)
            + ($orderStats->cancelled ?? 0)
            + ($orderStats->returned ?? 0);

        $cancelRate = $validTotal > 0
            ? ((($orderStats->cancelled ?? 0) + ($orderStats->returned ?? 0)) / $validTotal) * 100
            : 0;

        /*
        =====================================
        4. THỜI GIAN XỬ LÝ TB
        =====================================
        */
        $avgProcessingTime = DB::table('orders')
            ->where('status', Order::STATUS_COMPLETED)
            ->whereNotNull('delivered_at')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, delivered_at)) as hours')
            ->value('hours');

        /*
        =====================================
        5. DOANH THU / LỢI NHUẬN CHART
        =====================================
        */
        $dailyGrossRevenue = DB::table('orders')
        ->where('status', Order::STATUS_COMPLETED)
        ->whereBetween('delivered_at', [$from, $to])
        ->selectRaw('DATE(delivered_at) as date, SUM(COALESCE(total, 0)) as revenue')
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->keyBy('date');

        $dailyRefund = DB::table('refund_requests')
        ->where('status', 'refunded')
        ->whereBetween('updated_at', [$from, $to])
        ->selectRaw('DATE(updated_at) as date, SUM(COALESCE(refund_total, 0)) as refund_total')
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->keyBy('date');

        $weeklyRevenue = DB::table('orders')
        ->where('status', Order::STATUS_COMPLETED)
        ->whereBetween('delivered_at', [$from, $to])
        ->selectRaw('YEARWEEK(delivered_at) as week, SUM(COALESCE(total, 0)) as gross_revenue')
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
        ->where('status', Order::STATUS_COMPLETED)
        ->whereBetween('delivered_at', [$from, $to])
        ->selectRaw('DATE_FORMAT(delivered_at, "%Y-%m") as month, SUM(COALESCE(total, 0)) as gross_revenue')
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
        ->where('status', Order::STATUS_COMPLETED)
        ->selectRaw('YEAR(delivered_at) as year, SUM(COALESCE(total, 0)) as gross_revenue')
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
        ->where('o.status', Order::STATUS_COMPLETED)
        ->whereBetween('o.delivered_at', [$from, $to])
        ->selectRaw('
        DATE(o.delivered_at) as date,
        SUM(COALESCE(oib.quantity, 0) * COALESCE(si.cost_price, 0)) as gross_cost
    ')
            ->groupByRaw('DATE(o.delivered_at)')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $dailyRefundRestock = DB::table('refund_request_items as rri')
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
            ->where('o.status', Order::STATUS_COMPLETED)
            ->where('rri.condition_status', 'sealed')
            ->whereBetween('rr.updated_at', [$from, $to])
            ->selectRaw('
        DATE(rr.updated_at) as date,
        SUM(
            CASE
                WHEN COALESCE(batch_costs.batch_qty, 0) > 0
                    THEN (COALESCE(rri.quantity, 0) / batch_costs.batch_qty) * batch_costs.batch_cost
                ELSE 0
            END
        ) as restock_cost
    ')
            ->groupByRaw('DATE(rr.updated_at)')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $dailyShippingCost = DB::table('orders')
        ->where(function ($q) {
            $q->whereIn('status', [
                Order::STATUS_COMPLETED,
                Order::STATUS_RETURNED,
            ])->orWhere(function ($sub) {
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
            $revenueDay      = max(0, $grossRevenueDay - $refundDay);

            $chartRevenue[] = $revenueDay;

            $grossCostDay   = (float) ($dailyGrossCost[$date]->gross_cost ?? 0);
            $restockCost    = (float) ($dailyRefundRestock[$date]->restock_cost ?? 0);
            $costDay        = max(0, $grossCostDay - $restockCost);

            $shippingCost   = (float) ($dailyShippingCost[$date]->shipping_cost ?? 0);
            $expiredLossDay = (float) ($dailyExpiredLoss[$date]->expired_loss ?? 0);
            $refundLossDay  = (float) ($dailyRefundLoss[$date]->refund_loss ?? 0);

            $chartProfit[] =
            $revenueDay
            - $costDay
                - $shippingCost
                - $expiredLossDay
                - $refundLossDay;

            $currentDate->addDay();
        }

        /*
        =====================================
        TOP SẢN PHẨM / KHÁCH HÀNG
        =====================================
        */
        $topProducts = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('product_variants as pv', 'pv.id', '=', 'oi.variant_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->where('o.status', Order::STATUS_COMPLETED)
            ->whereBetween('o.delivered_at', [$from, $to])
            ->select(
                'p.name',
                DB::raw('SUM(COALESCE(oi.quantity, 0)) as total_sold'),
                DB::raw('SUM(COALESCE(oi.quantity, 0) * COALESCE(oi.price, 0)) as revenue'),
                DB::raw('SUM(COALESCE(oi.quantity, 0) * (COALESCE(oi.price, 0) - COALESCE(oi.cost_price, 0))) as profit')
            )
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $slowMoving = DB::table('product_variants as pv')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->leftJoin('order_items as oi', 'oi.variant_id', '=', 'pv.id')
            ->select(
                'p.name',
                'pv.stock_quantity',
                DB::raw('MAX(oi.created_at) as last_sold')
            )
            ->groupBy('pv.id', 'p.name', 'pv.stock_quantity')
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
            ->where('orders.status', Order::STATUS_COMPLETED)
            ->whereBetween('orders.delivered_at', [$from, $to])
            ->select(
                'users.name',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(COALESCE(orders.total, 0)) as spending')
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
                DB::raw('COALESCE(orders.updated_at, orders.created_at)'),
                [$from, $to]
            )
            ->select(
                'orders.id',
                'users.name as customer_name',
                DB::raw('COALESCE(orders.total, 0) + COALESCE(orders.shipping_fee, 0) as total'),
                DB::raw('COALESCE(orders.updated_at, orders.created_at) as cancelled_at')
            )
            ->orderByDesc(DB::raw('COALESCE(orders.updated_at, orders.created_at)'))
            ->limit(5)
            ->get();

        /*
        =====================================
        HAO HỤT THEO KỲ
        =====================================
        */
        $periodExpiredLoss = (float) DB::table('stock_imports')
            ->whereNotNull('expired_at')
            ->whereBetween('expired_at', [$from, $to])
            ->sum(DB::raw('COALESCE(expired_quantity, 0) * COALESCE(cost_price, 0)'));

        $periodRefundLoss = (float) DB::table('refund_requests')
            ->where('status', 'refunded')
            ->whereBetween('updated_at', [$from, $to])
            ->sum(DB::raw('COALESCE(loss_amount, 0)'));

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

            'lowStock'   => $lowStock,
            'cancelList' => $cancelList,
        ];
    }

    public function products(Request $request)
    {
        $range = $this->getDateRange($request);
        $keyword = trim((string) $request->keyword);

        $query = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('product_variants as pv', 'pv.id', '=', 'oi.variant_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->where('o.status', Order::STATUS_COMPLETED)
            ->whereBetween('o.delivered_at', [$range['from'], $range['to']])
            ->select(
                'p.name',
                DB::raw('SUM(COALESCE(oi.quantity, 0)) as total_sold'),
                DB::raw('SUM(COALESCE(oi.quantity, 0) * COALESCE(oi.price, 0)) as revenue')
            )
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('total_sold');

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
        $range = $this->getDateRange($request);
        $keyword = trim((string) $request->keyword);

        $query = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->where('orders.status', Order::STATUS_COMPLETED)
            ->whereBetween('orders.delivered_at', [$range['from'], $range['to']])
            ->select(
                'users.name',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(COALESCE(orders.total, 0)) as spending')
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

        $query = DB::table('product_variants as pv')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->leftJoin('order_items as oi', 'oi.variant_id', '=', 'pv.id')
            ->select(
                'p.name',
                'pv.stock_quantity',
                DB::raw('MAX(oi.created_at) as last_sold')
            )
            ->groupBy('pv.id', 'p.name', 'pv.stock_quantity');

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
                DB::raw('COALESCE(orders.updated_at, orders.created_at)'),
                [$range['from'], $range['to']]
            )
            ->select(
                'orders.id',
                'users.name as customer_name',
                DB::raw('COALESCE(orders.total, 0) + COALESCE(orders.shipping_fee, 0) as total'),
                DB::raw('COALESCE(orders.updated_at, orders.created_at) as cancelled_at')
            )
            ->orderByDesc(DB::raw('COALESCE(orders.updated_at, orders.created_at)'));

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
}