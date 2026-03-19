<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LỊCH SỬ THAY ĐỔI KHO
    |--------------------------------------------------------------------------
    */
    public function logs(Request $request)
    {
        $query = InventoryLog::with('variant.product');

        if ($request->filled('keyword')) {
            $query->whereHas('variant.product', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->keyword . '%');
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs = $query->latest()->paginate(10)->withQueryString();

        return view('admin.inventory.logs', compact('logs'));
    }

    /*
    |--------------------------------------------------------------------------
    | BÁO CÁO TỒN KHO
    |--------------------------------------------------------------------------
    */
    public function report(Request $request)
    {
        $query = ProductVariant::with('product:id,name');

        if ($request->keyword) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('product', function ($q2) use ($request) {
                    $q2->where('name', 'like', '%' . $request->keyword . '%');
                })
                    ->orWhere('attribute_value', 'like', '%' . $request->keyword . '%');
            });
        }

        if ($request->status == 'out') {
            $query->where('stock_quantity', 0);
        }

        if ($request->status == 'danger') {
            $query->where('stock_quantity', '<=', 2);
        }

        if ($request->status == 'low') {
            $query->whereBetween('stock_quantity', [3, 5]);
        }

        if ($request->status == 'ok') {
            $query->where('stock_quantity', '>', 5);
        }

        $query->orderBy('stock_quantity', $request->sort == 'high' ? 'desc' : 'asc');

        $variants = $query->paginate(30)->withQueryString();

        return view('admin.inventory.report', compact('variants'));
    }

    /*
    |--------------------------------------------------------------------------
    | SẢN PHẨM SẮP HẾT HÀNG
    |--------------------------------------------------------------------------
    */
    public function lowStock(Request $request)
    {
        $query = ProductVariant::with('product:id,name');

        if ($request->filled('keyword')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('product', function ($q2) use ($request) {
                    $q2->where('name', 'like', '%' . $request->keyword . '%');
                })
                    ->orWhere('attribute_value', 'like', '%' . $request->keyword . '%');
            });
        }

        if ($request->sort == 'all') {
            $query->orderBy('stock_quantity', 'asc');
        } else {
            $query->where('stock_quantity', '<=', 5);
            $query->orderBy('stock_quantity', $request->sort == 'high' ? 'desc' : 'asc');
        }

        $variants = $query->paginate(20)->withQueryString();

        return view('admin.inventory.low_stock', compact('variants'));
    }

    /*
    |--------------------------------------------------------------------------
    | LÔ SẮP HẾT HẠN
    |--------------------------------------------------------------------------
    */
    public function nearExpiry(Request $request)
    {
        $query = DB::table('stock_imports as si')
        ->join('product_variants as pv', 'si.variant_id', '=', 'pv.id')
        ->join('products as p',
            'pv.product_id',
            '=',
            'p.id'
        )

        ->leftJoin(
            DB::raw('(SELECT variant_id, MIN(image_path) as image_path FROM variant_images GROUP BY variant_id) as vi'),
            'pv.id',
            '=',
            'vi.variant_id'
        )

            ->whereNotNull('si.expiry_date');

        // =========================
        // 🔍 SEARCH
        // =========================
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);

            $query->where(function ($q) use ($keyword) {
                $q->where('p.name', 'like', "%{$keyword}%")
                    ->orWhere('si.code', 'like', "%{$keyword}%");

                if (is_numeric($keyword)) {
                    $q->orWhere('p.id', (int)$keyword);
                }
            });
        }

        // =========================
        // 🎯 FILTER
        // =========================
        if ($request->status === 'danger') {
            $query->whereNull('si.expired_at')
                ->whereDate('si.expiry_date', '<=', now()->addMonths(6));
        } elseif ($request->status === 'sale') {
            $query->whereNull('si.expired_at')
            ->whereBetween('si.expiry_date', [
                now()->addMonths(6),
                now()->addMonths(7)
            ]);
        } elseif ($request->status === 'expired') {
            $query->whereNotNull('si.expired_at');
        } elseif ($request->status === 'normal') {
            $query->whereNull('si.expired_at')
            ->whereDate('si.expiry_date', '>', now()->addMonths(7));
        }

        // =========================
        // 🔽 SORT (FEFO)
        // =========================
        $query->orderBy(
            'si.expiry_date',
            $request->sort === 'far' ? 'desc' : 'asc'
        );

        // =========================
        // 📦 SELECT (🔥 QUAN TRỌNG)
        // =========================
        $lots = $query->select(

            'si.id',
            'si.code',

            'si.imported_quantity',
            'si.remaining_quantity',
            'si.expired_quantity',
            'si.cost_price',

            'si.expiry_date',
            'si.expired_at',

            // 🔥 SOLD
            DB::raw('
            GREATEST(
                si.imported_quantity 
                - si.remaining_quantity 
                - si.expired_quantity,
                0
            ) as sold_quantity
        '),

            // 🔥 STATUS
            DB::raw("
            CASE 
                WHEN si.expired_at IS NOT NULL THEN 'expired'
                WHEN si.expiry_date <= DATE_ADD(NOW(), INTERVAL 6 MONTH) THEN 'danger'
                WHEN si.expiry_date <= DATE_ADD(NOW(), INTERVAL 7 MONTH) THEN 'sale'
                ELSE 'normal'
            END as batch_status
        "),

            // 💰 TIỀN (🔥 QUAN TRỌNG NHẤT)
            DB::raw('si.imported_quantity * si.cost_price as total_cost'),
            DB::raw('si.remaining_quantity * si.cost_price as remaining_value'),
            DB::raw('si.expired_quantity * si.cost_price as expired_value'),

            // PRODUCT
            'p.id as product_id',
            'p.name as product_name',

            // VARIANT
            'pv.attribute_name',
            'pv.attribute_value',

            // IMAGE
            'vi.image_path'
        )
        ->paginate(20)
        ->withQueryString();

        return view('admin.inventory.near_expiry', compact('lots'));
    }
}