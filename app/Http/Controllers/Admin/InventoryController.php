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
            ->paginate(30)
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
        $query = ProductVariant::with('product');

        if ($request->filled('keyword')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->keyword . '%');
            });
        }

        if ($request->filled('low_stock')) {
            $query->where('stock_quantity', '<=', 5);
        }

        $variants = $query
            ->orderBy('stock_quantity', 'asc')
            ->paginate(30)
            ->withQueryString();

        return view('admin.inventory.report', compact('variants'));
    }


    /*
    |--------------------------------------------------------------------------
    | SẢN PHẨM SẮP HẾT HÀNG
    |--------------------------------------------------------------------------
    */

    public function lowStock()
    {
        $variants = ProductVariant::with('product')
            ->where('stock_quantity', '<=', 5)
            ->orderBy('stock_quantity', 'asc')
            ->paginate(20);

        return view('admin.inventory.low_stock', compact('variants'));
    }


    /*
    |--------------------------------------------------------------------------
    | LÔ SẮP HẾT HẠN
    |--------------------------------------------------------------------------
    */

    public function nearExpiry()
    {

        /*
        --------------------------------------------------
        1. TỰ HUỶ LÔ ≤ 6 THÁNG
        --------------------------------------------------
        */

        $expiredLots = DB::table('stock_imports')
            ->whereDate('expiry_date', '<=', now()->addMonths(6))
            ->get();

        foreach ($expiredLots as $lot) {

            $variant = ProductVariant::find($lot->variant_id);
            if (!$variant) continue;

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
                ->delete();
        }


        /*
        --------------------------------------------------
        2. LẤY LÔ ≤ 7 THÁNG (CHO ADMIN SALE)
        --------------------------------------------------
        */

        $lots = DB::table('stock_imports')
            ->join('product_variants', 'stock_imports.variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->leftJoin('variant_images', 'product_variants.id', '=', 'variant_images.variant_id')

            ->whereDate('expiry_date', '<=', now()->addMonths(7))

            ->select(
                'stock_imports.*',
                'products.name as product_name',
                'product_variants.attribute_name',
                'product_variants.attribute_value',
                'variant_images.image_path'
            )

            ->orderBy('expiry_date', 'asc')
            ->paginate(20);


        return view('admin.inventory.near_expiry', compact('lots'));
    }
}