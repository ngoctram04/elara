<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /*
    =====================================
    TRANG BÁO CÁO
    =====================================
    */
    public function index(Request $request)
    {
        $data = $this->getReportData($request);

        return view('admin.reports.index', $data);
    }

    /*
    =====================================
    XUẤT PDF
    =====================================
    */
    public function exportPdf(Request $request)
    {
        $data = $this->getReportData($request);

        $pdf = Pdf::loadView('admin.reports.pdf', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->download('bao-cao-' . $data['from'] . '-den-' . $data['to'] . '.pdf');
    }

    /*
    =====================================
    HÀM LẤY DỮ LIỆU (DÙNG CHUNG)
    =====================================
    */
    private function getReportData(Request $request)
    {
        // Khoảng thời gian chuẩn
        $from = $request->from
            ? $request->from . ' 00:00:00'
            : now()->startOfMonth();

        $to = $request->to
            ? $request->to . ' 23:59:59'
            : now();

        /*
        =====================================
        1. TÀI CHÍNH
        =====================================
        */
        $finance = DB::table('orders as o')
            ->join('order_items as oi', 'oi.order_id', '=', 'o.id')
            ->where('o.status', 3)
            ->whereBetween('o.created_at', [$from, $to])
            ->selectRaw('
                COALESCE(SUM(o.total),0) as revenue,
                COALESCE(SUM(o.shipping_fee),0) as shipping_total,
                COALESCE(SUM(o.discount),0) as discount_total,
                COALESCE(SUM(oi.quantity * oi.cost_price),0) as cost,
                COALESCE(SUM(o.total) - SUM(oi.quantity * oi.cost_price),0) as profit
            ')
            ->first();

        /*
        =====================================
        2. THỐNG KÊ ĐƠN
        =====================================
        */
        $orderStats = DB::table('orders')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as shipping,
                SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 4 THEN 1 ELSE 0 END) as cancelled
            ')
            ->first();

        /*
        =====================================
        3. DOANH THU NGÀY
        =====================================
        */
        $dailyRevenue = DB::table('orders')
            ->where('status', 3)
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        /*
        =====================================
        4. TOP BÁN CHẠY
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
                DB::raw('SUM(oi.quantity * oi.price) as revenue')
            )
            ->groupBy('p.name')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        /*
        =====================================
        5. TOP LỢI NHUẬN
        =====================================
        */
        $topProfitProducts = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('product_variants as pv', 'pv.id', '=', 'oi.variant_id')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->where('o.status', 3)
            ->whereBetween('o.created_at', [$from, $to])
            ->select(
                'p.name',
                DB::raw('SUM((oi.price - oi.cost_price) * oi.quantity) as profit')
            )
            ->groupBy('p.name')
            ->orderByDesc('profit')
            ->limit(10)
            ->get();

        /*
        =====================================
        6. TỒN KHO
        =====================================
        */
        $inventory = DB::table('product_variants')
            ->selectRaw('
                SUM(stock_quantity) as total_qty,
                SUM(stock_quantity * cost_price) as total_value
            ')
            ->first();

        /*
        =====================================
        7. NHẬP HÀNG
        =====================================
        */
        $importCost = DB::table('stock_imports')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('
                SUM(quantity) as total_qty,
                SUM(quantity * cost_price) as total_cost
            ')
            ->first();

        /*
        =====================================
        8. SẮP HẾT
        =====================================
        */
        $lowStock = DB::table('product_variants as pv')
            ->join('products as p', 'p.id', '=', 'pv.product_id')
            ->where('pv.stock_quantity', '<=', 5)
            ->select('p.name', 'pv.attribute_value', 'pv.stock_quantity')
            ->orderBy('pv.stock_quantity')
            ->limit(10)
            ->get();

        return [
            'from' => date('Y-m-d', strtotime($from)),
            'to' => date('Y-m-d', strtotime($to)),

            'finance' => $finance,
            'orderStats' => $orderStats,
            'dailyRevenue' => $dailyRevenue,

            'topProducts' => $topProducts,
            'topProfitProducts' => $topProfitProducts,

            'inventory' => $inventory,
            'importCost' => $importCost,
            'lowStock' => $lowStock
        ];
    }
}