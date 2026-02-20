<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ProductVariant;
use App\Models\StockImport;

class StockImportController extends Controller
{
    /* =======================
        FORM NHẬP HÀNG
    ======================= */
    public function create()
    {
        $variants = ProductVariant::with('product:id,name')
            ->select(
                'id',
                'product_id',
                'attribute_value',
                'stock_quantity',
                'cost_price'
            )
            ->orderByDesc('id')
            ->get();

        return view('admin.stock_imports.create', compact('variants'));
    }

    /* =======================
        LƯU NHẬP HÀNG
    ======================= */
    public function store(Request $request)
    {
        $data = $request->validate([
            'variant_id'  => 'required|exists:product_variants,id',
            'quantity'    => 'required|integer|min:1',
            'cost_price'  => 'required|numeric|min:0',
            'expiry_date' => 'nullable|date|after:today'
        ]);

        DB::transaction(function () use ($data) {

            // Khóa variant tránh nhập đồng thời
            $variant = ProductVariant::with('product')
                ->lockForUpdate()
                ->findOrFail($data['variant_id']);

            /* ======================
                TÍNH GIÁ VỐN TRUNG BÌNH
            ====================== */
            $oldStock = $variant->stock_quantity ?? 0;
            $oldCost  = $variant->cost_price ?? 0;

            $newQty   = $data['quantity'];
            $newCost  = $data['cost_price'];

            $newStock = $oldStock + $newQty;

            $totalOldValue = $oldStock * $oldCost;
            $totalNewValue = $newQty * $newCost;

            $avgCost = $newStock > 0
                ? ($totalOldValue + $totalNewValue) / $newStock
                : $newCost;

            /* ======================
                CẬP NHẬT VARIANT
            ====================== */
            $variant->stock_quantity = $newStock;
            $variant->cost_price     = $avgCost;
            $variant->save();

            /* ======================
                CẬP NHẬT TOTAL STOCK PRODUCT
            ====================== */
            if ($variant->product) {
                $totalStock = ProductVariant::where('product_id', $variant->product_id)
                    ->sum('stock_quantity');

                $variant->product->update([
                    'total_stock' => $totalStock
                ]);
            }

            /* ======================
                LƯU LỊCH SỬ NHẬP
            ====================== */
            StockImport::create([
                'variant_id'  => $variant->id,
                'quantity'    => $newQty,
                'cost_price'  => $newCost,
                'expiry_date' => $data['expiry_date'] ?? null
            ]);
        });

        return redirect()
            ->back()
            ->with('success', 'Nhập hàng thành công');
    }

    /* =======================
        LỊCH SỬ NHẬP
    ======================= */
    public function history()
    {
        $imports = StockImport::with([
            'variant:id,product_id,attribute_value',
            'variant.product:id,name'
        ])
            ->latest()
            ->paginate(20);

        return view('admin.stock_imports.history', compact('imports'));
    }
}