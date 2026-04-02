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
        $range = $this->getDateRange($request);

        $data['revenue'] = $data['finance']->revenue ?? 0;

        $data['totalCost']     = $data['finance']->cost ?? 0;
        $data['totalDiscount'] = $data['finance']->discount_total ?? 0;

        $data['shippingCollected'] = $data['finance']->shipping_total ?? 0;
        $data['shippingPaid']      = $data['finance']->shipping_cost_total ?? 0;

        $data['shippingDebt']      = $data['finance']->shipping_debt ?? 0;
        $data['shippingPaidTotal'] = $data['finance']->shipping_paid_total ?? 0;

        $data['freeShippingLoss'] = max(
            0,
            $data['shippingPaid'] - $data['shippingCollected']
        );

        $data['shippingProfit'] =
            $data['shippingCollected'] - $data['shippingPaid'];

        $data['totalOrders'] = $data['orderStats']->completed ?? 0;
        $data['cancelRate'] = $data['cancelRate'] ?? 0;

        $data['averageOrder'] = $data['totalOrders'] > 0
            ? $data['revenue'] / $data['totalOrders']
            : 0;

        $data['inventoryValue'] = $data['inventory']->total_value ?? 0;

        $data['totalImport'] = DB::table('stock_imports')
            ->sum(DB::raw('COALESCE(imported_quantity, 0) * COALESCE(cost_price, 0)'));

        $expiredLoss = DB::table('stock_imports')
            ->whereNotNull('expired_at')
            ->whereBetween('expired_at', [$range['from'], $range['to']])
            ->sum(DB::raw('COALESCE(expired_quantity, 0) * COALESCE(cost_price, 0)'));

        $refundLoss = DB::table('refund_requests')
            ->where('status', 'refunded')
            ->whereBetween('updated_at', [$range['from'], $range['to']])
            ->sum(DB::raw('COALESCE(loss_amount, 0)'));

        $data['inventoryLoss'] = $expiredLoss + $refundLoss;

        $data['saleProfit'] = $data['revenue'] - $data['totalCost'];

        $data['realProfit'] =
            $data['revenue']
            - $data['totalCost']
            - $data['shippingPaidTotal']
            - $data['inventoryLoss'];

        $data['profit'] = $data['realProfit'];

        $data['margin'] = $data['revenue'] > 0
            ? ($data['profit'] / $data['revenue']) * 100
            : 0;

        return view('admin.reports.index', $data);
    }

    private function getDateRange(Request $request)
    {
        $from = $request->from
            ? Carbon::parse($request->from)->startOfDay()
            : now()->startOfMonth();

        $to = $request->to
            ? Carbon::parse($request->to)->endOfDay()
            : now()->endOfDay();

        return [
            'from' => $from,
            'to' => $to,
            'from_date' => $from->format('Y-m-d'),
            'to_date' => $to->format('Y-m-d'),
        ];
    }

    private function getReportData(Request $request)
    {
        $range = $this->getDateRange($request);
        $from = $range['from'];
        $to = $range['to'];

        /*
        =====================================
        REFUND SUBQUERY
        =====================================
        | refund_total: tổng tiền hoàn
        | refund_cost : toàn bộ giá vốn của hàng đã hoàn
        | restock_cost: phần giá vốn quay về kho (sealed)
        =====================================
        */
        $refundSummarySub = DB::table('refund_requests')
            ->selectRaw('
                order_id,
                SUM(COALESCE(refund_total, 0)) as refund_total,
                SUM(COALESCE(loss_amount, 0)) as refund_loss
            ')
            ->where('status', 'refunded')
            ->groupBy('order_id');

        $refundItemCostAllSub = DB::table('refund_request_items as rri')
            ->join('refund_requests as rr', 'rr.id', '=', 'rri.refund_request_id')
            ->join('order_items as oi', 'oi.id', '=', 'rri.order_item_id')
            ->selectRaw('
                rr.order_id,
                SUM(COALESCE(rri.quantity, 0) * COALESCE(oi.cost_price, 0)) as refund_cost
            ')
            ->where('rr.status', 'refunded')
            ->groupBy('rr.order_id');

        $refundItemRestockSub = DB::table('refund_request_items as rri')
            ->join('refund_requests as rr', 'rr.id', '=', 'rri.refund_request_id')
            ->join('order_items as oi', 'oi.id', '=', 'rri.order_item_id')
            ->selectRaw('
                rr.order_id,
                SUM(
                    CASE
                        WHEN rri.condition_status = "sealed"
                        THEN COALESCE(rri.quantity, 0) * COALESCE(oi.cost_price, 0)
                        ELSE 0
                    END
                ) as restock_cost
            ')
            ->where('rr.status', 'refunded')
            ->groupBy('rr.order_id');

        /*
        =====================================
        1. TÀI CHÍNH
        =====================================
        */

        // Doanh thu gốc của đơn đã giao
        $grossRevenue = DB::table('orders')
            ->where('status', Order::STATUS_COMPLETED)
            ->whereBetween('delivered_at', [$from, $to])
            ->sum(DB::raw('COALESCE(total, 0) + COALESCE(shipping_fee, 0)'));

        // Tổng tiền hoàn trong kỳ
        $refundAmountInRange = DB::table('refund_requests')
            ->where('status', 'refunded')
            ->whereBetween('updated_at', [$from, $to])
            ->sum(DB::raw('COALESCE(refund_total, 0)'));

        // Doanh thu thuần
        $revenue = max(0, $grossRevenue - $refundAmountInRange);

        // Ship khách trả
        $shippingTotal = DB::table('orders')
            ->where(function ($q) {
                $q->whereIn('status', [
                    Order::STATUS_COMPLETED,
                    Order::STATUS_RETURNED,
                ])
                    ->orWhere(function ($sub) {
                        $sub->where('status', Order::STATUS_CANCELLED)
                            ->whereNotNull('delivered_at');
                    });
            })
            ->whereBetween('delivered_at', [$from, $to])
            ->sum(DB::raw('COALESCE(shipping_fee, 0)'));

        // Ship thực tế phải trả
        $shippingCostTotal = DB::table('orders')
            ->where(function ($q) {
                $q->whereIn('status', [
                    Order::STATUS_COMPLETED,
                    Order::STATUS_RETURNED,
                ])
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

        // Tổng giảm giá
        $discountTotal = DB::table('orders')
            ->where('status', Order::STATUS_COMPLETED)
            ->whereBetween('delivered_at', [$from, $to])
            ->sum(DB::raw('COALESCE(discount, 0)'));

        // Giá vốn gốc của đơn đã giao
        $grossCost = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->where('o.status', Order::STATUS_COMPLETED)
            ->whereBetween('o.delivered_at', [$from, $to])
            ->sum(DB::raw('COALESCE(oi.quantity, 0) * COALESCE(oi.cost_price, 0)'));

        // Trừ toàn bộ giá vốn của hàng đã hoàn trong kỳ
        $refundCostInRange = DB::table('refund_request_items as rri')
            ->join('refund_requests as rr', 'rr.id', '=', 'rri.refund_request_id')
            ->join('order_items as oi', 'oi.id', '=', 'rri.order_item_id')
            ->join('orders as o', 'o.id', '=', 'rr.order_id')
            ->where('rr.status', 'refunded')
            ->whereBetween('rr.updated_at', [$from, $to])
            ->where('o.status', Order::STATUS_COMPLETED)
            ->sum(DB::raw('COALESCE(rri.quantity, 0) * COALESCE(oi.cost_price, 0)'));

        // Tổng vốn đã bán thuần
        $cost = max(0, $grossCost - $refundCostInRange);

        /*
        =====================================
        SHIP ĐÃ TRẢ
        =====================================
        */
        $shipPaid = DB::table('shipping_payments')
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount');

        /*
        =====================================
        LỢI NHUẬN
        =====================================
        */
        $profit = $revenue - $cost - $shipPaid;
        $shippingDebt = max(0, $shippingCostTotal - $shipPaid);

        $finance = (object) [
            'revenue'             => $revenue,
            'gross_revenue'       => $grossRevenue,
            'refund_total'        => $refundAmountInRange,

            'cost'                => $cost,
            'gross_cost'          => $grossCost,
            'refund_cost'         => $refundCostInRange,

            'profit'              => $profit,
            'shipping_total'      => $shippingTotal,
            'shipping_cost_total' => $shippingCostTotal,
            'shipping_paid_total' => $shipPaid,
            'shipping_debt'       => $shippingDebt,
            'discount_total'      => $discountTotal,
        ];

        /*
        =====================================
        2. TĂNG TRƯỞNG
        =====================================
        */
        $days = $from->diffInDays($to) + 1;

        $prevFrom = (clone $from)->subDays($days);
        $prevTo = (clone $from)->subSecond();

        $previousGrossRevenue = DB::table('orders')
            ->where('status', Order::STATUS_COMPLETED)
            ->whereBetween('delivered_at', [$prevFrom, $prevTo])
            ->sum(DB::raw('COALESCE(total, 0) + COALESCE(shipping_fee, 0)'));

        $previousRefundAmount = DB::table('refund_requests')
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
            ($s = $orderStats->completed ?? 0)
            + ($c = $orderStats->cancelled ?? 0)
            + ($r = $orderStats->returned ?? 0);

        $cancelRate = $validTotal > 0
            ? (($c + $r) / $validTotal) * 100
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
        5. DOANH THU THEO THỜI GIAN
        =====================================
        */
        $dailyGrossRevenue = DB::table('orders')
            ->where('status', Order::STATUS_COMPLETED)
            ->whereBetween('delivered_at', [$from, $to])
            ->selectRaw('DATE(delivered_at) as date, SUM(COALESCE(total, 0) + COALESCE(shipping_fee, 0)) as revenue')
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
            ->selectRaw('YEARWEEK(delivered_at) as week, SUM(COALESCE(total, 0) + COALESCE(shipping_fee, 0)) as gross_revenue')
            ->groupBy('week')
            ->orderBy('week')
            ->get()
            ->map(function ($row) use ($from, $to) {
                $refundInWeek = DB::table('refund_requests')
                    ->where('status', 'refunded')
                    ->whereBetween('updated_at', [$from, $to])
                    ->whereRaw('YEARWEEK(updated_at) = ?', [$row->week])
                    ->sum(DB::raw('COALESCE(refund_total, 0)'));

                $row->revenue = max(0, (float) $row->gross_revenue - (float) $refundInWeek);
                unset($row->gross_revenue);

                return $row;
            });

        $monthlyRevenue = DB::table('orders')
            ->where('status', Order::STATUS_COMPLETED)
            ->whereBetween('delivered_at', [$from, $to])
            ->selectRaw('DATE_FORMAT(delivered_at, "%Y-%m") as month, SUM(COALESCE(total, 0) + COALESCE(shipping_fee, 0)) as gross_revenue')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($row) use ($from, $to) {
                $refundInMonth = DB::table('refund_requests')
                    ->where('status', 'refunded')
                    ->whereBetween('updated_at', [$from, $to])
                    ->whereRaw('DATE_FORMAT(updated_at, "%Y-%m") = ?', [$row->month])
                    ->sum(DB::raw('COALESCE(refund_total, 0)'));

                $row->revenue = max(0, (float) $row->gross_revenue - (float) $refundInMonth);
                unset($row->gross_revenue);

                return $row;
            });

        $yearlyRevenue = DB::table('orders')
            ->where('status', Order::STATUS_COMPLETED)
            ->selectRaw('YEAR(delivered_at) as year, SUM(COALESCE(total, 0) + COALESCE(shipping_fee, 0)) as gross_revenue')
            ->groupBy('year')
            ->orderBy('year')
            ->get()
            ->map(function ($row) {
                $refundInYear = DB::table('refund_requests')
                    ->where('status', 'refunded')
                    ->whereRaw('YEAR(updated_at) = ?', [$row->year])
                    ->sum(DB::raw('COALESCE(refund_total, 0)'));

                $row->revenue = max(0, (float) $row->gross_revenue - (float) $refundInYear);
                unset($row->gross_revenue);

                return $row;
            });

        /*
        =====================================
        5.1 LỢI NHUẬN THEO NGÀY
        =====================================
        */
        $orderCostSub = DB::table('order_items')
            ->selectRaw('order_id, SUM(COALESCE(quantity, 0) * COALESCE(cost_price, 0)) as total_cost')
            ->groupBy('order_id');

        $dailyOrderProfit = DB::table('orders as o')
            ->leftJoinSub($orderCostSub, 'costs', function ($join) {
                $join->on('costs.order_id', '=', 'o.id');
            })
            ->leftJoinSub($refundSummarySub, 'refunds', function ($join) {
                $join->on('refunds.order_id', '=', 'o.id');
            })
            ->leftJoinSub($refundItemCostAllSub, 'refund_costs_all', function ($join) {
                $join->on('refund_costs_all.order_id', '=', 'o.id');
            })
            ->where('o.status', Order::STATUS_COMPLETED)
            ->whereBetween('o.delivered_at', [$from, $to])
            ->selectRaw('
                DATE(o.delivered_at) as date,
                SUM(
                    (
                        COALESCE(o.total, 0)
                        + COALESCE(o.shipping_fee, 0)
                        - COALESCE(refunds.refund_total, 0)
                    )
                    -
                    (
                        COALESCE(costs.total_cost, 0)
                        - COALESCE(refund_costs_all.refund_cost, 0)
                    )
                ) as profit
            ')
            ->groupByRaw('DATE(o.delivered_at)')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $dailyShippingPaid = DB::table('shipping_payments')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as date, SUM(amount) as paid')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        /*
        =====================================
        5.2 CHUẨN HOÁ LABELS CHART
        =====================================
        */
        $chartLabels = [];
        $chartRevenue = [];
        $chartProfit = [];

        $currentDate = $from->copy()->startOfDay();
        $endDate = $to->copy()->startOfDay();

        while ($currentDate->lte($endDate)) {
            $date = $currentDate->format('Y-m-d');

            $chartLabels[] = $date;

            $gross = (float) ($dailyGrossRevenue[$date]->revenue ?? 0);
            $refund = (float) ($dailyRefund[$date]->refund_total ?? 0);
            $chartRevenue[] = max(0, $gross - $refund);

            $orderProfit = (float) ($dailyOrderProfit[$date]->profit ?? 0);
            $shippingPaid = (float) ($dailyShippingPaid[$date]->paid ?? 0);
            $chartProfit[] = $orderProfit - $shippingPaid;

            $currentDate->addDay();
        }

        /*
        =====================================
        TOP SẢN PHẨM
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

        /*
        =====================================
        SẢN PHẨM TỒN LÂU
        =====================================
        */
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

        /*
        =====================================
        SẢN PHẨM ĐƯỢC QUAN TÂM
        =====================================
        */
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

        /*
        =====================================
        TOP KHÁCH HÀNG
        =====================================
        */
        $topCustomers = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->where('orders.status', Order::STATUS_COMPLETED)
            ->whereBetween('orders.delivered_at', [$from, $to])
            ->select(
                'users.name',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(COALESCE(orders.total, 0) + COALESCE(orders.shipping_fee, 0)) as spending')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('spending')
            ->limit(5)
            ->get();

        /*
        =====================================
        TỒN KHO
        =====================================
        */
        $totalImportValueAll = DB::table('stock_imports')
            ->sum(DB::raw('COALESCE(imported_quantity, 0) * COALESCE(cost_price, 0)'));

        $completedCostAll = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->where('o.status', Order::STATUS_COMPLETED)
            ->sum(DB::raw('COALESCE(oi.quantity, 0) * COALESCE(oi.cost_price, 0)'));

        // Phần giá vốn quay lại kho chỉ tính sealed
        $restockCostAll = DB::table('refund_request_items as rri')
            ->join('refund_requests as rr', 'rr.id', '=', 'rri.refund_request_id')
            ->join('order_items as oi', 'oi.id', '=', 'rri.order_item_id')
            ->where('rr.status', 'refunded')
            ->where('rri.condition_status', 'sealed')
            ->sum(DB::raw('COALESCE(rri.quantity, 0) * COALESCE(oi.cost_price, 0)'));

        $expiredLossAll = DB::table('stock_imports')
            ->whereNotNull('expired_at')
            ->sum(DB::raw('COALESCE(expired_quantity, 0) * COALESCE(cost_price, 0)'));

        $refundLossAll = DB::table('refund_requests')
            ->where('status', 'refunded')
            ->sum(DB::raw('COALESCE(loss_amount, 0)'));

        $inventoryValueCurrent =
            $totalImportValueAll
            - ($completedCostAll - $restockCostAll)
            - $expiredLossAll
            - $refundLossAll;

        if ($inventoryValueCurrent < 0) {
            $inventoryValueCurrent = 0;
        }

        $inventory = (object) [
            'total_qty'   => DB::table('stock_imports')->sum('remaining_quantity'),
            'total_value' => $inventoryValueCurrent,
        ];

        $lowStock = DB::table('product_variants as pv')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->where('pv.stock_quantity', '<=', 5)
            ->select('p.name', 'pv.attribute_value', 'pv.stock_quantity')
            ->orderBy('pv.stock_quantity')
            ->limit(5)
            ->get();

        /*
        =====================================
        BOM HÀNG
        =====================================
        */
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

            'inventory'  => $inventory,
            'lowStock'   => $lowStock,
            'cancelList' => $cancelList,
        ];
    }

    public function exportPdf(Request $request)
    {
        $data = $this->getReportData($request);
        $data['chartImage'] = $request->input('chart_image');

        $pdf = Pdf::loadView('admin.reports.pdf', $data)
            ->setPaper('a4', 'portrait');

        $fileName = "bao-cao-{$data['from']}-den-{$data['to']}.pdf";

        return $pdf->download($fileName);
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
            'keyword'  => $keyword
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
                DB::raw('SUM(COALESCE(orders.total, 0) + COALESCE(orders.shipping_fee, 0)) as spending')
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
            'keyword'   => $keyword
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
            'days'     => $days
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
            'max_stock' => $maxStock
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
            'keyword'  => $keyword
        ]);
    }

    public function payShipping(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        DB::table('shipping_payments')->insert([
            'amount'     => $request->amount,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return back()->with('success', 'Đã thanh toán tiền ship');
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
            'keyword' => $keyword
        ]);
    }
}