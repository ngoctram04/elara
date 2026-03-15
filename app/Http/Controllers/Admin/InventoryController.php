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

        $logs = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

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


        if ($request->sort == 'high') {
            $query->orderBy('stock_quantity', 'desc');
        } else {
            $query->orderBy('stock_quantity', 'asc');
        }


        $variants = $query
        ->paginate(30)
            ->withQueryString();

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

        // SEARCH
        if ($request->filled('keyword')) {

            $query->where(function ($q) use ($request) {

                $q->whereHas('product', function ($q2) use ($request) {
                    $q2->where('name', 'like', '%' . $request->keyword . '%');
                })
                ->orWhere('attribute_value', 'like', '%' . $request->keyword . '%');
            });
        }

        // SORT
        if ($request->sort == 'all') {

            $query->orderBy('stock_quantity', 'asc');
        } else {

            $query->where('stock_quantity', '<=', 5);

            if ($request->sort == 'high'
            ) {
                $query->orderBy('stock_quantity', 'desc');
            } else {
                $query->orderBy('stock_quantity', 'asc');
            }
        }

        $variants = $query
        ->paginate(20)
        ->withQueryString();

        return view('admin.inventory.low_stock', compact('variants'));
    }


    /*
    |--------------------------------------------------------------------------
    | LÔ SẮP HẾT HẠN
    |--------------------------------------------------------------------------
    */
    public function nearExpiry(Request $request)
    {

        /*
    --------------------------------------------------
    1. TỰ HUỶ LÔ ≤ 6 THÁNG
    --------------------------------------------------
    */

        DB::beginTransaction();

        try {

            $expiredLots = DB::table('stock_imports')
            ->whereDate('expiry_date', '<=', now()->addMonths(6))
            ->whereNull('expired_at')
                ->get();

            foreach ($expiredLots as $lot) {

                $variant = ProductVariant::find($lot->variant_id);

                if (!$variant) {
                    continue;
                }

                $before = $variant->stock_quantity;

                $destroyQty = min($variant->stock_quantity, $lot->quantity);

                if ($destroyQty > 0) {

                    $variant->decrement('stock_quantity', $destroyQty);

                    InventoryLog::create([
                        'variant_id' => $variant->id,
                        'type' => 'adjust',
                        'quantity_change' => -$destroyQty,
                        'stock_before' => $before,
                        'stock_after' => $variant->stock_quantity,
                        'reference_type' => 'expired'
                    ]);
                }

                DB::table('stock_imports')
                ->where('id', $lot->id)
                ->update([
                    'expired_at' => now()
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();
        }


        /*
    --------------------------------------------------
    2. QUERY LÔ ≤ 7 THÁNG
    --------------------------------------------------
    */

        $query = DB::table('stock_imports')
            ->join('product_variants', 'stock_imports.variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=',
                'products.id'
            )

            ->leftJoin(
                DB::raw('(SELECT variant_id, MIN(image_path) as image_path FROM variant_images GROUP BY variant_id) as variant_images'),
                'product_variants.id',
                '=',
                'variant_images.variant_id'
            )

            ->whereDate('stock_imports.expiry_date', '<=', now()->addMonths(7));


        /*
    --------------------------------------------------
    TÌM KIẾM
    --------------------------------------------------
    */

        if ($request->keyword) {

            $query->where('products.name', 'like', '%' . $request->keyword . '%');
        }


        /*
    --------------------------------------------------
    LỌC TRẠNG THÁI
    --------------------------------------------------
    */

        if ($request->status == 'expired') {

            $query->whereNotNull('stock_imports.expired_at');
        }

        if ($request->status == 'danger') {

            $query->whereDate('stock_imports.expiry_date', '<=', now()->addMonths(6))
                ->whereNull('stock_imports.expired_at');
        }

        if ($request->status == 'sale') {

            $query->whereBetween('stock_imports.expiry_date', [now(), now()->addMonths(7)])
                ->whereNull('stock_imports.expired_at');
        }


        /*
    --------------------------------------------------
    SẮP XẾP
    --------------------------------------------------
    */

        if ($request->sort == 'far') {

            $query->orderBy('stock_imports.expiry_date', 'desc');
        } else {

            $query->orderBy('stock_imports.expiry_date', 'asc');
        }


        /*
    --------------------------------------------------
    SELECT DATA
    --------------------------------------------------
    */

        $lots = $query
        ->select(
            'stock_imports.id',
            'stock_imports.variant_id',
            'stock_imports.quantity',
            'stock_imports.expiry_date',
            'stock_imports.expired_at',

            'products.name as product_name',

            'product_variants.attribute_name',
            'product_variants.attribute_value',

            'variant_images.image_path'
        )
        ->paginate(20)
        ->withQueryString();


        return view('admin.inventory.near_expiry', compact('lots'));
    }
    
}