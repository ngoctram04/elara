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
        // 🔥 LẤY DATA CHÍNH
        $data = $this->getReportData($request);

        // 🔥 RANGE
        $range = $this->getDateRange($request);

        /*
    =====================================
    KPI CHÍNH
    =====================================
    */

        $data['revenue'] = $data['finance']->revenue ?? 0;

        /*
    =====================================
    CHI PHÍ
    =====================================
    */

        $data['totalCost']     = $data['finance']->cost ?? 0;
        $data['totalDiscount'] = $data['finance']->discount_total ?? 0;

        /*
    =====================================
    SHIPPING
    =====================================
    */

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

        /*
    =====================================
    ĐƠN HÀNG
    =====================================
    */

        $data['totalOrders'] = $data['orderStats']->completed ?? 0;

        $data['cancelRate'] = $data['cancelRate'] ?? 0;

        $data['averageOrder'] = $data['totalOrders'] > 0
            ? $data['revenue'] / $data['totalOrders']
            : 0;

        /*
    =====================================
    KHO
    =====================================
    */

        // 🔥 Giá trị tồn kho hiện tại
        $data['inventoryValue'] = $data['inventory']->total_value ?? 0;

        // 🔥 Tổng vốn nhập (FIX FIELD)
        $data['totalImport'] = DB::table('stock_imports')
            ->sum(DB::raw('imported_quantity * cost_price'));

        // Hao hụt do hàng hết hạn
        $expiredLoss = DB::table('stock_imports')
        ->whereNotNull('expired_at')
        ->whereBetween('expired_at', [$range['from'], $range['to']])
        ->sum(DB::raw('expired_quantity * cost_price'));

        // Hao hụt do hàng hoàn trả bị hư / không nhập lại kho
        $refundLoss = DB::table('refund_requests')
        ->where('status', 'refunded')
        ->whereBetween('updated_at', [$range['from'], $range['to']])
        ->sum(DB::raw('COALESCE(loss_amount, 0)'));

        // Tổng hao hụt
        $data['inventoryLoss'] = $expiredLoss + $refundLoss;
        /*
    =====================================
    PROFIT
    =====================================
    */

        // ✅ Lợi nhuận bán hàng (KHÔNG tính hao hụt)
        $data['saleProfit'] =
            $data['revenue'] - $data['totalCost'];

        // ✅ Lợi nhuận thực (CÓ hao hụt)
        $data['realProfit'] =
            $data['revenue']
            - $data['totalCost']
            - $data['shippingPaidTotal']
            - $data['inventoryLoss'];

        // 🔥 GÁN PROFIT (QUAN TRỌNG: phải sau khi tính)
        $data['profit'] = $data['realProfit'];

        // 🔥 Margin
        $data['margin'] = $data['revenue'] > 0
            ? ($data['profit'] / $data['revenue']) * 100
            : 0;

        /*
    =====================================
    VIEW
    =====================================
    */

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
    /*
    =====================================
    KHOẢNG THỜI GIAN
    =====================================
    */
    private function getReportData(Request $request)
    {
        $range = $this->getDateRange($request);
        $from = $range['from'];
        $to = $range['to'];

        /*
    =====================================
    1. TÀI CHÍNH
    =====================================
    */

        // Doanh thu khách trả (tiền sản phẩm + ship khách trả)
        $revenue = DB::table('orders')
            ->where('status', Order::STATUS_COMPLETED)
            ->where('payment_status', '!=', Order::PAYMENT_REFUNDED)
            ->whereBetween('delivered_at', [$from, $to])
            ->sum(DB::raw('total + shipping_fee'));

        // Ship khách trả
        $shippingTotal = DB::table('orders')
        ->where(function ($q) {
            $q->whereIn('status', [
                Order::STATUS_COMPLETED,
                Order::STATUS_RETURNED
            ])
            ->orWhere(function ($sub) {
                $sub->where('status', Order::STATUS_CANCELLED)
                    ->whereNotNull('delivered_at');
            });
        })
        ->whereBetween('delivered_at', [$from,
            $to
        ])
        ->sum(DB::raw('COALESCE(shipping_fee, 0)'));

        // Ship thực tế phải trả cho đơn vị vận chuyển
        $shippingCostTotal = DB::table('orders')
        ->where(function ($q) {
            $q->whereIn('status', [
                Order::STATUS_COMPLETED,
                Order::STATUS_RETURNED
            ])
            ->orWhere(function ($sub) {
                $sub->where('status', Order::STATUS_CANCELLED)
                    ->whereNotNull('delivered_at');
            });
        })
        ->whereBetween('delivered_at', [$from,
            $to
        ])
        ->sum(DB::raw("
            CASE 
                WHEN shipping_cost > 0 THEN shipping_cost
                ELSE shipping_fee
            END
        "));

        // Tổng giảm giá
        $discountTotal = DB::table('orders')
        ->where('status', Order::STATUS_COMPLETED)
        ->where('payment_status', '!=', Order::PAYMENT_REFUNDED)
        ->whereBetween('delivered_at', [$from, $to])
        ->sum('discount');

        // Giá vốn CHỈ tính đơn đã hoàn thành
        $cost = DB::table('order_items as oi')
        ->join('orders as o', 'o.id', '=',
            'oi.order_id'
        )
        ->where('o.status', Order::STATUS_COMPLETED)
        ->where('o.payment_status', '!=', Order::PAYMENT_REFUNDED)
        ->whereBetween('o.delivered_at', [$from, $to])
            ->sum(DB::raw('oi.quantity * oi.cost_price'));

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

        /*
    =====================================
    OBJECT FINANCE
    =====================================
    */

        $finance = (object) [
                'revenue' => $revenue,
                'cost' => $cost,
                'profit' => $profit,

                'shipping_total' => $shippingTotal,
                'shipping_cost_total' => $shippingCostTotal,
                'shipping_paid_total' => $shipPaid,
                'shipping_debt' => $shippingDebt,

                'discount_total' => $discountTotal
            ];

        /*
    =====================================
    2. TĂNG TRƯỞNG
    =====================================
    */

        $days = $from->diffInDays($to) + 1;

        $prevFrom = (clone $from)->subDays($days);
        $prevTo = (clone $from)->subSecond();

        $previousRevenue = DB::table('orders')
        ->where('status', Order::STATUS_COMPLETED)
        ->where('payment_status',
            '!=',
            Order::PAYMENT_REFUNDED
        )
        ->whereBetween('delivered_at', [$prevFrom, $prevTo])
            ->sum(DB::raw('total + shipping_fee'));

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
            COUNT(CASE 
                WHEN created_at BETWEEN ? AND ? 
                THEN 1 
            END) as total,

            SUM(CASE 
                WHEN status = 1 
                AND created_at BETWEEN ? AND ? 
                THEN 1 ELSE 0 END) as pending,

            SUM(CASE 
                WHEN status = 2 
                AND created_at BETWEEN ? AND ? 
                THEN 1 ELSE 0 END) as shipping,

            SUM(CASE 
                WHEN status = 3 
                AND delivered_at BETWEEN ? AND ? 
                THEN 1 ELSE 0 END) as completed,

            SUM(CASE 
                WHEN status = 4 
                AND updated_at BETWEEN ? AND ? 
                THEN 1 ELSE 0 END) as cancelled,

            SUM(CASE 
                WHEN status = 5 
                AND updated_at BETWEEN ? AND ? 
                THEN 1 ELSE 0 END) as returned
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
            $to
        ])
        ->first();

        /*
    =====================================
    TỶ LỆ HUỶ CHUẨN
    =====================================
    */

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
            ->whereBetween('created_at',
                [$from, $to]
            )
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, delivered_at)) as hours')
            ->value('hours');

        /*
    =====================================
    5. DOANH THU THEO THỜI GIAN
    =====================================
    */

        $dailyRevenue = DB::table('orders')
            ->where('status', Order::STATUS_COMPLETED)
            ->where('payment_status',
                '!=',
                Order::PAYMENT_REFUNDED
            )
            ->whereBetween('delivered_at', [$from, $to])
            ->selectRaw('DATE(delivered_at) as date, SUM(total + shipping_fee) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $weeklyRevenue = DB::table('orders')
        ->where('status', Order::STATUS_COMPLETED)
        ->where('payment_status',
            '!=',
            Order::PAYMENT_REFUNDED
        )
        ->whereBetween('delivered_at', [$from, $to])
        ->selectRaw('YEARWEEK(delivered_at) as week, SUM(total + shipping_fee) as revenue')
        ->groupBy('week')
        ->orderBy('week')
        ->get();

        $monthlyRevenue = DB::table('orders')
        ->where('status', Order::STATUS_COMPLETED)
        ->where('payment_status',
            '!=',
            Order::PAYMENT_REFUNDED
        )
        ->whereBetween('delivered_at', [$from, $to])
        ->selectRaw('DATE_FORMAT(delivered_at,"%Y-%m") as month, SUM(total + shipping_fee) as revenue')
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        $yearlyRevenue = DB::table('orders')
        ->where('status', Order::STATUS_COMPLETED)
        ->where('payment_status',
            '!=',
            Order::PAYMENT_REFUNDED
        )
        ->selectRaw('YEAR(delivered_at) as year, SUM(total + shipping_fee) as revenue')
        ->groupBy('year')
        ->orderBy('year')
        ->get();

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
            ->where('o.payment_status', '!=', Order::PAYMENT_REFUNDED)
            ->whereBetween('o.delivered_at', [$from, $to])
            ->select(
                'p.name',
                DB::raw('SUM(oi.quantity) as total_sold'),
                DB::raw('SUM(oi.quantity * oi.price) as revenue'),
                DB::raw('SUM(oi.quantity * (oi.price - oi.cost_price)) as profit')
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
        ->join('users', 'users.id',
            '=',
            'orders.user_id'
        )
        ->where('orders.status', Order::STATUS_COMPLETED)
        ->where('orders.payment_status', '!=', Order::PAYMENT_REFUNDED)
        ->whereBetween('orders.delivered_at', [$from, $to])
        ->select(
            'users.name',
            DB::raw('COUNT(*) as orders'),
            DB::raw('SUM(orders.total + COALESCE(orders.shipping_fee, 0)) as spending')
        )
        ->groupBy('users.id',
            'users.name'
        )
        ->orderByDesc('spending')
        ->limit(5)
        ->get();

        /*
    =====================================
    TỒN KHO
    =====================================
    */

        // Tổng vốn nhập toàn hệ thống
        $totalImportValueAll = DB::table('stock_imports')
            ->sum(DB::raw('imported_quantity * cost_price'));

        // Tổng giá vốn đã bán toàn hệ thống
        // CHỈ trừ khi đơn đã hoàn thành
        $completedCostAll = DB::table('order_items as oi')
        ->join('orders as o', 'o.id', '=', 'oi.order_id')
        ->where('o.status', Order::STATUS_COMPLETED)
        ->where('o.payment_status', '!=', Order::PAYMENT_REFUNDED)
        ->sum(DB::raw('oi.quantity * oi.cost_price'));

        // Hao hụt do hết hạn toàn hệ thống
        $expiredLossAll = DB::table('stock_imports')
        ->whereNotNull('expired_at')
            ->sum(DB::raw('COALESCE(expired_quantity, 0) * cost_price'));

        // Hao hụt do hoàn hàng bị hư / không nhập lại kho toàn hệ thống
        $refundLossAll = DB::table('refund_requests')
        ->where('status', 'refunded')
        ->sum(DB::raw('COALESCE(loss_amount, 0)'));

        // Giá trị tồn kho hiện tại:
        // = Tổng vốn nhập - vốn đã bán hoàn tất - hao hụt
        $inventoryValueCurrent = $totalImportValueAll - $completedCostAll - $expiredLossAll - $refundLossAll;

        if ($inventoryValueCurrent < 0) {
            $inventoryValueCurrent = 0;
        }

        $inventory = (object) [
            // total_qty tạm vẫn giữ theo hệ thống hiện tại
            // nếu stock_quantity/remaining_quantity của bạn bị trừ sớm
            // thì qty có thể chưa chuẩn, nhưng total_value sẽ đúng theo nghiệp vụ hơn
            'total_qty' => DB::table('stock_imports')->sum('remaining_quantity'),
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
        ->join('users',
            'users.id',
            '=',
            'orders.user_id'
        )
        ->where('orders.status', Order::STATUS_CANCELLED)
        ->whereBetween(
            DB::raw('COALESCE(orders.updated_at, orders.created_at)'),
            [$from, $to]
        )
        ->select(
            'orders.id',
            'users.name as customer_name',
            DB::raw('orders.total + COALESCE(orders.shipping_fee,0) as total'),
            DB::raw('COALESCE(orders.updated_at, orders.created_at) as created_at')
        )
        ->orderByDesc('orders.created_at')
        ->limit(5)
        ->get();

        return [
            'from' => $range['from_date'],
            'to' => $range['to_date'],

            'finance' => $finance,
            'growth' => $growth,

            'orderStats' => $orderStats,
            'cancelRate' => $cancelRate,
            'avgProcessingTime' => $avgProcessingTime,

            'dailyRevenue' => $dailyRevenue,
            'weeklyRevenue' => $weeklyRevenue,
            'monthlyRevenue' => $monthlyRevenue,
            'yearlyRevenue' => $yearlyRevenue,

            'topProducts' => $topProducts,
            'topCustomers' => $topCustomers,

            'slowMoving' => $slowMoving,
            'mostViewed' => $mostViewed,

            'inventory' => $inventory,
            'lowStock' => $lowStock,
            'cancelList' => $cancelList
        ];
    }

    /*
    =====================================
    EXPORT PDF
    =====================================
    */
    public function exportPdf(Request $request)
    {
        // Lấy toàn bộ dữ liệu báo cáo
        $data = $this->getReportData($request);

        // Nhận ảnh biểu đồ từ Chart.js (base64)
        $data['chartImage'] = $request->input('chart_image');

        // Load view PDF
        $pdf = Pdf::loadView('admin.reports.pdf', $data)
            ->setPaper('a4', 'portrait');

        // Tên file
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
            ->where('o.payment_status', '!=', Order::PAYMENT_REFUNDED)
            ->whereBetween('o.delivered_at', [$range['from'], $range['to']])
            ->select(
                'p.name',
                DB::raw('SUM(oi.quantity) as total_sold'),
                DB::raw('SUM(oi.quantity * oi.price) as revenue')
            )
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('total_sold');

        // Tìm kiếm
        if ($keyword !== '') {
            $query->where('p.name', 'like', "%{$keyword}%");
        }

        $products = $query->paginate(20)->withQueryString();

        return view('admin.reports.products', [
            'products' => $products,
            'from' => $range['from_date'],
            'to' => $range['to_date'],
            'keyword' => $keyword
        ]);
    }

    public function customers(Request $request)
    {
        $range = $this->getDateRange($request);
        $keyword = trim((string) $request->keyword);

        $query = DB::table('orders')
        ->join('users', 'users.id', '=', 'orders.user_id')
        ->where('orders.status', Order::STATUS_COMPLETED)
            ->where('orders.payment_status', '!=', Order::PAYMENT_REFUNDED)
            ->whereBetween('orders.delivered_at', [$range['from'], $range['to']])
            ->select(
                'users.name',
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(orders.total + COALESCE(orders.shipping_fee, 0)) as spending')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('spending');

        if ($keyword !== '') {
            $query->where('users.name', 'like', "%{$keyword}%");
        }

        $customers = $query->paginate(20)->withQueryString();

        return view('admin.reports.customers', [
            'customers' => $customers,
            'from' => $range['from_date'],
            'to' => $range['to_date'],
            'keyword' => $keyword
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

        // lọc theo tên
        if ($keyword !== '') {
            $query->where('p.name', 'like', "%{$keyword}%");
        }

        // lọc theo số ngày chưa bán
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
            'keyword' => $keyword,
            'days' => $days
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
            'products' => $products,
            'keyword' => $keyword,
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
            'keyword' => $keyword
        ]);
    }

    public function payShipping(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        DB::table('shipping_payments')->insert([
            'amount' => $request->amount,
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
                DB::raw('orders.total + COALESCE(orders.shipping_fee, 0) as total'),
                DB::raw('COALESCE(orders.updated_at, orders.created_at) as cancelled_at')
            )
            ->orderByDesc(DB::raw('COALESCE(orders.updated_at, orders.created_at)'));

        // tìm kiếm theo tên khách
        if ($keyword !== '') {
            $query->where('users.name', 'like', "%{$keyword}%");
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('admin.reports.cancel_orders', [
            'orders' => $orders,
            'from' => $range['from_date'],
            'to' => $range['to_date'],
            'keyword' => $keyword
        ]);
    }
}