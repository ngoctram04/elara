<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{

    public function logs(Request $request)
    {
        $query = InventoryLog::query();

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $numberKeyword = preg_replace('/\D/', '', $keyword);

            $query->where(function ($q) use ($keyword, $numberKeyword) {
                $q->whereHas('variant.product', function ($sub) use ($keyword, $numberKeyword) {
                    $sub->where('name', 'like', "%{$keyword}%")
                        ->orWhereRaw("CONCAT('SP', LPAD(id, 5, '0')) LIKE ?", ['%' . $keyword . '%']);

                    if ($numberKeyword !== '') {
                        $sub->orWhere('id', (int) $numberKeyword);
                    }
                })
                    ->orWhereHas('variant', function ($sub) use ($keyword, $numberKeyword) {
                        $sub->whereRaw("CONCAT('BT', LPAD(id, 5, '0')) LIKE ?", ['%' . $keyword . '%']);

                        if ($numberKeyword !== '') {
                            $sub->orWhere('id', (int) $numberKeyword);
                        }
                    });
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

        $logs = $query->with([
            'variant.product:id,name',
            'variant.images:id,variant_id,image_path'
        ])
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.inventory.logs', compact('logs'));
    }

    public function report(Request $request)
    {
        $query = ProductVariant::with([
            'product:id,name',
            'images:id,variant_id,image_path'
        ]);

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $numberKeyword = preg_replace('/\D/', '', $keyword);

            $query->where(function ($q) use ($keyword, $numberKeyword) {
                $q->whereHas('product', function ($q2) use ($keyword, $numberKeyword) {
                    $q2->where('name', 'like', '%' . $keyword . '%')
                        ->orWhereRaw("CONCAT('SP', LPAD(id, 5, '0')) LIKE ?", ['%' . $keyword . '%']);

                    if ($numberKeyword !== '') {
                        $q2->orWhere('id', (int) $numberKeyword);
                    }
                })
                    ->orWhere('attribute_value', 'like', '%' . $keyword . '%')
                    ->orWhereRaw("CONCAT('BT', LPAD(id, 5, '0')) LIKE ?", ['%' . $keyword . '%']);

                if ($numberKeyword !== '') {
                    $q->orWhere('id', (int) $numberKeyword);
                }
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

        // KPI tính trên toàn bộ kết quả sau filter, không theo từng trang
        $totalVariants = (clone $query)->count();
        $totalStock = (clone $query)->sum('stock_quantity');
        $lowStockCount = (clone $query)->whereBetween('stock_quantity', [1, 5])->count(); // hoặc <= 5 tùy ý bạn
        $outOfStockCount = (clone $query)->where('stock_quantity', 0)->count();

        $query->orderBy('stock_quantity', $request->sort == 'high' ? 'desc' : 'asc');

        $variants = $query->paginate(10)->withQueryString();

        return view('admin.inventory.report', compact(
            'variants',
            'totalVariants',
            'totalStock',
            'lowStockCount',
            'outOfStockCount'
        ));
    }

    public function lowStock(Request $request)
    {
        $query = ProductVariant::with([
            'product:id,name',
            'images:id,variant_id,image_path'
        ]);

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $numberKeyword = preg_replace('/\D/', '', $keyword);

            $query->where(function ($q) use ($keyword, $numberKeyword) {
                $q->whereHas('product', function ($q2) use ($keyword, $numberKeyword) {
                    $q2->where('name', 'like', '%' . $keyword . '%')
                        ->orWhereRaw("CONCAT('SP', LPAD(id, 5, '0')) LIKE ?", ['%' . $keyword . '%']);

                    if ($numberKeyword !== '') {
                        $q2->orWhere('id', (int) $numberKeyword);
                    }
                })
                    ->orWhere('attribute_value', 'like', '%' . $keyword . '%')
                    ->orWhereRaw("CONCAT('BT', LPAD(id, 5, '0')) LIKE ?", ['%' . $keyword . '%']);

                if ($numberKeyword !== '') {
                    $q->orWhere('id', (int) $numberKeyword);
                }
            });
        }

        if ($request->sort == 'all') {
            $query->orderBy('stock_quantity', 'asc');
        } else {
            $query->where('stock_quantity', '<=', 5);
            $query->orderBy('stock_quantity', $request->sort == 'high' ? 'desc' : 'asc');
        }

        $variants = $query->paginate(10)->withQueryString();

        return view('admin.inventory.low_stock', compact('variants'));
    }

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
            ->leftJoin(
                DB::raw("
                (
                    SELECT
                        stock_import_id,
                        SUM(ABS(quantity_change)) as damaged_qty,
                        SUM(COALESCE(loss_amount, 0)) as damaged_loss
                    FROM inventory_logs
                    WHERE type = 'return_damaged'
                      AND stock_import_id IS NOT NULL
                    GROUP BY stock_import_id
                ) as rd
            "),
                'si.id',
                '=',
                'rd.stock_import_id'
            )
            ->whereNotNull('si.expiry_date');

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $numberKeyword = preg_replace('/\D/', '', $keyword);

            $query->where(function ($q) use ($keyword, $numberKeyword) {
                $q->where('p.name', 'like', "%{$keyword}%")
                    ->orWhere('si.code', 'like', "%{$keyword}%")
                    ->orWhere('si.lot_code', 'like', "%{$keyword}%")
                    ->orWhereRaw("CONCAT('SP', LPAD(p.id, 5, '0')) LIKE ?", ['%' . $keyword . '%'])
                    ->orWhereRaw("CONCAT('BT', LPAD(pv.id, 5, '0')) LIKE ?", ['%' . $keyword . '%']);

                if ($numberKeyword !== '') {
                    $q->orWhere('p.id', (int) $numberKeyword)
                    ->orWhere('pv.id', (int) $numberKeyword);
                }
            });
        }

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

        $query->orderBy(
            'si.expiry_date',
            $request->sort === 'far' ? 'desc' : 'asc'
        );

        $lots = $query->select(
            'si.id',
            'si.code',
            'si.lot_code',
            'si.variant_id',

            'si.imported_quantity',
            'si.remaining_quantity',
            'si.expired_quantity',
            'si.cost_price',

            'si.expiry_date',
            'si.expired_at',

            DB::raw('
            GREATEST(
                si.imported_quantity
                - si.remaining_quantity
                - COALESCE(si.expired_quantity, 0)
                - COALESCE(rd.damaged_qty, 0),
                0
            ) as sold_quantity
        '),

            DB::raw('
            COALESCE(si.expired_quantity, 0) + COALESCE(rd.damaged_qty, 0)
            as damaged_quantity
        '),

            DB::raw("
            CASE
                WHEN si.expired_at IS NOT NULL THEN 'expired'
                WHEN si.expiry_date <= DATE_ADD(NOW(), INTERVAL 6 MONTH) THEN 'danger'
                WHEN si.expiry_date <= DATE_ADD(NOW(), INTERVAL 7 MONTH) THEN 'sale'
                ELSE 'normal'
            END as batch_status
        "),

            DB::raw('si.imported_quantity * si.cost_price as total_cost'),
            DB::raw('si.remaining_quantity * si.cost_price as remaining_value'),
            DB::raw('COALESCE(si.expired_quantity, 0) * si.cost_price as expired_value'),
            DB::raw('COALESCE(rd.damaged_loss, 0) as damaged_loss_value'),
            DB::raw('COALESCE(si.expired_quantity, 0) * si.cost_price + COALESCE(rd.damaged_loss, 0) as total_loss_value'),

            'p.id as product_id',
            'p.name as product_name',

            'pv.id as variant_id',
            'pv.attribute_name',
            'pv.attribute_value',

            'vi.image_path'
        )
        ->paginate(10)
        ->withQueryString();

        return view('admin.inventory.near_expiry', compact('lots'));
    }
}