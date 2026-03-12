<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
class ReportController extends Controller
{

    public function index(Request $request)
    {
        $data = $this->getReportData($request);

        /*
    =====================================
    KPI CHÍNH
    =====================================
    */

        // Doanh thu khách thanh toán
        $data['revenue'] = $data['finance']->revenue ?? 0;

        // Lợi nhuận thực (đã trừ vốn + ship shop trả)
        $data['profit'] = $data['finance']->profit ?? 0;

        // Biên lợi nhuận %
        $data['margin'] = $data['revenue'] > 0
        ? ($data['profit'] / $data['revenue']) * 100
            : 0;


        /*
    =====================================
    ĐƠN HÀNG
    =====================================
    */

        // Tổng đơn hoàn thành
        $data['totalOrders'] = $data['orderStats']->completed ?? 0;

        // Tỷ lệ huỷ (để Blade không lỗi)
        $data['cancelRate'] = $data['cancelRate'] ?? 0;

        // Giá trị đơn trung bình (AOV)
        $data['averageOrder'] = $data['totalOrders'] > 0
        ? $data['revenue'] / $data['totalOrders']
        : 0;


        /*
=====================================
SHIPPING (QUAN TRỌNG)
=====================================
*/

        // Khách trả phí ship
        $data['shippingCollected'] = $data['finance']->shipping_total ?? 0;

        // Shop phải trả cho đơn vị vận chuyển
        $data['shippingPaid'] = $data['finance']->shipping_cost_total ?? 0;

        // Nợ ship còn lại
        $data['shippingDebt'] = $data['finance']->shipping_debt ?? 0;
        $data['shippingPaidTotal'] = $data['finance']->shipping_paid_total ?? 0;
        // Chi phí freeship shop chịu
        $data['freeShippingLoss'] = max(
            0,
            $data['shippingPaid'] - $data['shippingCollected']
        );

        // Lãi / lỗ vận chuyển
        $data['shippingProfit'] =
        $data['shippingCollected'] - $data['shippingPaid'];
        /*
    =====================================
    CHI PHÍ
    =====================================
    */

        // Tổng vốn hàng đã bán
        $data['totalCost'] = $data['finance']->cost ?? 0;

        // Tổng giảm giá (voucher + sinh nhật)
        $data['totalDiscount'] = $data['finance']->discount_total ?? 0;


        /*
    =====================================
    KHO
    =====================================
    */

        // Giá trị tồn kho hiện tại
        $data['inventoryValue'] = $data['inventory']->total_value ?? 0;

        // Tổng vốn nhập (toàn thời gian)
        $data['totalImport'] = DB::table('stock_imports')
        ->sum(DB::raw('quantity * cost_price'));

        // Tổng hao hụt kho (hàng hết hạn, hư, thất thoát)
        $data['inventoryLoss'] = max(
            0,
            $data['totalImport'] - ($data['totalCost'] + $data['inventoryValue'])
        );
        /*
    =====================================
    TRẢ VIEW
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

        // Doanh thu khách trả (tiền sản phẩm)
        $revenue = DB::table('orders')
        ->where('status', 3)
            ->whereBetween('created_at', [$from, $to])
            ->sum(DB::raw('total + shipping_fee'));

        // Ship khách trả
        $shippingTotal = DB::table('orders')
        ->where(function ($q) {
            $q->where('status', 3)
            ->orWhere(function ($sub) {
                $sub->where('status', 4)
                    ->whereNotNull('delivered_at');
            });
        })
        ->whereBetween('created_at', [$from, $to])
        ->sum(DB::raw('COALESCE(shipping_fee,0)'));

        // Ship thực tế phải trả cho đơn vị vận chuyển
        $shippingCostTotal = DB::table('orders')
        ->where(function ($q) {
            $q->where('status', 3)
            ->orWhere(function ($sub) {
                $sub->where('status', 4)
                    ->whereNotNull('delivered_at');
            });
        })
        ->whereBetween('created_at', [$from, $to])
        ->sum(DB::raw('
CASE 
WHEN shipping_cost > 0 THEN shipping_cost
ELSE shipping_fee
END
'));
        // Tổng giảm giá
        $discountTotal = DB::table('orders')
        ->where('status', 3)
            ->whereBetween('created_at', [$from, $to])
            ->sum('discount');

        // Giá vốn
        $cost = DB::table('order_items as oi')
        ->join('orders as o', 'o.id', '=', 'oi.order_id')
        ->where('o.status', 3)
            ->whereBetween('o.created_at', [$from, $to])
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

        // Lợi nhuận thực
        $profit = $revenue - $cost - $shipPaid;

        // Nợ ship còn lại
        $shippingDebt = max(0, $shippingCostTotal - $shipPaid);

        /*
    =====================================
    OBJECT FINANCE
    =====================================
    */

        $finance = (object)[
                'revenue' => $revenue,
                'cost' => $cost,
                'profit' => $profit,

                // ship
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
        ->where('status', 3)
        ->whereBetween('created_at', [$prevFrom, $prevTo])
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
        ->whereBetween('created_at', [$from, $to])
        ->selectRaw('
            COUNT(*) as total,
            SUM(status = 1) as pending,
            SUM(status = 2) as shipping,
            SUM(status = 3) as completed,
            SUM(status = 4) as cancelled
        ')
        ->first();

        $cancelRate = $orderStats->total > 0
        ? ($orderStats->cancelled / $orderStats->total) * 100
        : 0;

        /*
    =====================================
    4. THỜI GIAN XỬ LÝ TB
    =====================================
    */

        $avgProcessingTime = DB::table('orders')
            ->where('status', 3)
            ->whereNotNull('delivered_at')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, delivered_at)) as hours')
            ->value('hours');

        /*
    =====================================
    5. DOANH THU THEO THỜI GIAN
    =====================================
    */

        $dailyRevenue = DB::table('orders')
            ->where('status', 3)
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as date, SUM(total + shipping_fee) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $weeklyRevenue = DB::table('orders')
        ->where('status', 3)
        ->whereBetween('created_at', [$from, $to])
            ->selectRaw('YEARWEEK(created_at) as week, SUM(total + shipping_fee) as revenue')
            ->groupBy('week')
            ->orderBy('week')
            ->get();

        $monthlyRevenue = DB::table('orders')
        ->where('status', 3)
        ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE_FORMAT(created_at,"%Y-%m") as month, SUM(total + shipping_fee) as revenue')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $yearlyRevenue = DB::table('orders')
        ->where('status', 3)
        ->selectRaw('YEAR(created_at) as year, SUM(total + shipping_fee) as revenue')
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
            ->where('o.status', 3)
            ->whereBetween('o.created_at', [$from, $to])
            ->select(
                'p.name',
                DB::raw('SUM(oi.quantity) as total_sold'),
                DB::raw('SUM(oi.quantity * oi.price) as revenue'),
                DB::raw('SUM(oi.quantity * (oi.price - oi.cost_price)) as profit')
            )
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('total_sold')
            ->limit(10)
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
            ->limit(10)
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
            ->limit(10)
            ->get();

        /*
    =====================================
    TOP KHÁCH HÀNG
    =====================================
    */

        $topCustomers = DB::table('orders')
        ->join('users', 'users.id', '=', 'orders.user_id')
        ->where('orders.status', 3)
        ->whereBetween('orders.created_at', [$from, $to])
        ->select(
            'users.name',
            DB::raw('COUNT(*) as orders'),
            DB::raw('SUM(total + shipping_fee) as spending')
        )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('spending')
            ->limit(10)
            ->get();

        /*
    =====================================
    TỒN KHO
    =====================================
    */

        $inventory = DB::table('product_variants')
        ->selectRaw('
            SUM(stock_quantity) as total_qty,
            SUM(stock_quantity * cost_price) as total_value
        ')
        ->first();

        $lowStock = DB::table('product_variants as pv')
        ->join('products as p', 'p.id', '=', 'pv.product_id')
        ->where('pv.stock_quantity', '<=', 5)
        ->select('p.name', 'pv.attribute_value', 'pv.stock_quantity')
        ->orderBy('pv.stock_quantity')
        ->limit(10)
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
            'lowStock' => $lowStock
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
        $keyword = $request->keyword;

        $query = DB::table('order_items as oi')
        ->join('orders as o', 'o.id', '=', 'oi.order_id')
        ->join('product_variants as pv', 'pv.id', '=', 'oi.variant_id')
        ->join('products as p', 'p.id', '=', 'pv.product_id')
        ->where('o.status', 3)
        ->whereBetween('o.created_at', [$range['from'], $range['to']])
        ->select(
            'p.name',
            DB::raw('SUM(oi.quantity) as total_sold'),
            DB::raw('SUM(oi.quantity * oi.price) as revenue')
        )
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('total_sold');

        // Tìm kiếm
        if ($keyword) {
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
        $keyword = $request->keyword;

        $query = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->where('orders.status', 3)
            ->whereBetween('orders.created_at', [$range['from'], $range['to']])
            ->select(
                'users.name',
                DB::raw('COUNT(*) as orders'),
            DB::raw('SUM(total + shipping_fee) as spending')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('spending');

        if ($keyword) {
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
        $keyword = $request->keyword;

        $query = DB::table('product_variants as pv')
        ->join('products as p', 'p.id', '=', 'pv.product_id')
        ->leftJoin('order_items as oi', 'oi.variant_id', '=', 'pv.id')
            ->select(
                'p.name',
                'pv.stock_quantity',
                DB::raw('MAX(oi.created_at) as last_sold')
            )
            ->groupBy('pv.id', 'p.name', 'pv.stock_quantity')
            ->orderByRaw('MAX(oi.created_at) IS NULL DESC')
            ->orderByRaw('MAX(oi.created_at) ASC');

        if ($keyword) {
            $query->where('p.name', 'like', "%{$keyword}%");
        }

        $products = $query->paginate(20)->withQueryString();

        return view('admin.reports.slow_products', [
            'products' => $products,
            'keyword' => $keyword
        ]);
    }

    public function lowStock(Request $request)
    {
        $keyword = $request->keyword;

        $query = DB::table('product_variants as pv')
        ->join('products as p', 'p.id', '=', 'pv.product_id')
        ->where('pv.stock_quantity', '<=', 5)
            ->select('p.name', 'pv.attribute_value', 'pv.stock_quantity')
            ->orderBy('pv.stock_quantity');

        if ($keyword) {
            $query->where('p.name', 'like', "%{$keyword}%");
        }

        $products = $query->paginate(20)->withQueryString();

        return view('admin.reports.low_stock', [
            'products' => $products,
            'keyword' => $keyword
        ]);
    }

    public function wishlist(Request $request)
    {
        $keyword = $request->keyword;

        $query = DB::table('wishlists as w')
        ->join('products as p', 'p.id', '=', 'w.product_id')
        ->select(
            'p.name',
            DB::raw('COUNT(w.id) as total_wishlist')
        )
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('total_wishlist');

        if ($keyword) {
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
        DB::table('shipping_payments')->insert([
            'amount' => $request->amount,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return back()->with('success', 'Đã thanh toán tiền ship');
    }
    
}