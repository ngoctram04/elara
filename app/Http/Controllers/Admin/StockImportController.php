<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductVariant;
use App\Models\StockImport;
use App\Models\InventoryLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use App\Notifications\SystemNotification;

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
        LƯU PHIẾU NHẬP
    ======================= */
    public function store(Request $request)
    {
        $request->validate([
            'variant_id'   => 'required|array',
            'variant_id.*' => 'required|exists:product_variants,id',

            'quantity'     => 'required|array',
            'quantity.*'   => 'required|integer|min:1',

            'cost_price'   => 'required|array',
            'cost_price.*' => 'required|numeric|min:0',

            'mfg_date'     => 'nullable|array',
            'mfg_date.*'   => 'nullable|date',

            'expiry_date'   => 'nullable|array',
            'expiry_date.*' => 'nullable|date|after:today',

            'supplier'         => 'required|string|max:255',
            'supplier_phone'   => 'nullable|string|max:20',
            'supplier_address' => 'nullable|string|max:255',
            'note'             => 'nullable|string'
        ], [
            'supplier.required'      => 'Vui lòng nhập nhà cung cấp',
            'expiry_date.*.after'    => 'Hạn sử dụng phải lớn hơn ngày hôm nay',
            'variant_id.required'    => 'Vui lòng chọn ít nhất 1 biến thể',
            'variant_id.*.required'  => 'Vui lòng chọn biến thể hợp lệ',
            'variant_id.*.exists'    => 'Biến thể không tồn tại',
            'quantity.*.required'    => 'Vui lòng nhập số lượng',
            'quantity.*.integer'     => 'Số lượng phải là số nguyên',
            'quantity.*.min'         => 'Số lượng phải lớn hơn 0',
            'cost_price.*.required'  => 'Vui lòng nhập giá nhập',
            'cost_price.*.numeric'   => 'Giá nhập phải là số',
            'cost_price.*.min'       => 'Giá nhập không được âm',
        ]);

        $warnings = [];
        $code = 'NK' . now()->format('YmdHis');

        try {
            DB::transaction(function () use ($request, &$warnings, $code) {
                foreach ($request->variant_id as $index => $variantId) {
                    $qty  = (int) ($request->quantity[$index] ?? 0);
                    $cost = (float) ($request->cost_price[$index] ?? 0);

                    $mfg = $request->mfg_date[$index] ?? null;
                    $exp = $request->expiry_date[$index] ?? null;

                    /* ======================
                        VALIDATE NGÀY
                    ====================== */
                    if ($mfg && $exp) {
                        if (Carbon::parse($exp)->lte(Carbon::parse($mfg))) {
                            throw new \Exception('Hạn sử dụng phải lớn hơn ngày sản xuất');
                        }
                    }

                    /* ======================
                        CẢNH BÁO < 6 THÁNG
                    ====================== */
                    if ($exp) {
                        $today  = Carbon::today();
                        $expiry = Carbon::parse($exp);

                        if ($expiry->diffInMonths($today) <= 6) {
                            $warnings[] = "Sản phẩm ID {$variantId} có hạn sử dụng dưới 6 tháng";
                        }
                    }

                    /* ======================
                        LOCK VARIANT
                    ====================== */
                    $variant = ProductVariant::with('product')
                        ->lockForUpdate()
                        ->findOrFail($variantId);

                    /* ======================
                        GIÁ VỐN TRUNG BÌNH
                    ====================== */
                    $oldStock = $variant->stock_quantity ?? 0;
                    $oldCost  = $variant->cost_price ?? 0;

                    $newStock = $oldStock + $qty;

                    $totalOldValue = $oldStock * $oldCost;
                    $totalNewValue = $qty * $cost;

                    $avgCost = $newStock > 0
                        ? ($totalOldValue + $totalNewValue) / $newStock
                        : $cost;

                    /* ======================
                        UPDATE VARIANT
                    ====================== */
                    $variant->update([
                        'stock_quantity' => $newStock,
                        'cost_price'     => $avgCost
                    ]);

                    /* ======================
                        UPDATE PRODUCT STOCK
                    ====================== */
                    if ($variant->product) {
                        $totalStock = ProductVariant::where('product_id', $variant->product_id)
                            ->sum('stock_quantity');

                        $variant->product->update([
                            'total_stock' => $totalStock
                        ]);
                    }

                    /* ======================
                        STOCK IMPORT
                    ====================== */
                    $import = StockImport::create([
                        'variant_id'         => $variant->id,
                        'quantity'           => $qty,
                        'remaining_quantity' => $qty,
                        'imported_quantity'  => $qty,
                        'expired_quantity'   => 0,

                        'cost_price'         => $cost,
                        'manufacture_date'   => $mfg,
                        'expiry_date'        => $exp,

                        'supplier'           => $request->supplier,
                        'supplier_phone'     => $request->supplier_phone,
                        'supplier_address'   => $request->supplier_address,
                        'note'               => $request->note,

                        'code'               => $code,
                        'created_by'         => Auth::id()
                    ]);

                    // Chỉ dùng nếu bảng stock_imports có cột lot_code
                    if (isset($import->lot_code) || Schema::hasColumn('stock_imports', 'lot_code')) {
                        $import->lot_code = 'L' . $import->id;
                        $import->save();
                    }

                    /* ======================
                        INVENTORY LOG
                    ====================== */
                    InventoryLog::create([
                        'variant_id'      => $variant->id,
                        'type'            => 'import',
                        'quantity_change' => $qty,
                        'stock_before'    => $oldStock,
                        'stock_after'     => $newStock,
                        'reference_type'  => 'stock_import',
                        'reference_id'    => $import->id
                    ]);
                }
            });
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors($e->getMessage());
        }

        /* ======================
            NOTIFICATION
        ====================== */
        $totalQty = array_sum($request->quantity);

        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $admin->notify(new SystemNotification([
                'title'   => 'Nhập hàng mới',
                'message' => "Vừa nhập {$totalQty} sản phẩm (mã {$code})",
                'url'     => route('admin.stock.show', $code),
                'type'    => 'stock_import',
                'icon'    => 'bi-box-seam',
                'color'   => 'success',
            ]));
        }

        /* ======================
            RETURN
        ====================== */
        return redirect()
            ->route('admin.stock.history')
            ->with('success', 'Nhập hàng thành công')
            ->with('warning', $warnings);
    }

    /* =======================
        LỊCH SỬ NHẬP
    ======================= */
    public function history(Request $request)
    {
        $query = StockImport::query();

        if ($request->keyword) {
            $keyword = trim($request->keyword);

            $query->where(function ($q) use ($keyword) {
                $q->where('code', 'like', '%' . $keyword . '%')
                    ->orWhere('supplier', 'like', '%' . $keyword . '%')
                    ->orWhere('supplier_phone', 'like', '%' . $keyword . '%')
                    ->orWhere('supplier_address', 'like', '%' . $keyword . '%');
            });
        }

        if ($request->from && $request->to) {
            $query->whereBetween('created_at', [
                $request->from . ' 00:00:00',
                $request->to . ' 23:59:59'
            ]);
        }

        $imports = $query->select(
            'code',
            DB::raw('MAX(created_at) as created_at'),
            DB::raw('COUNT(DISTINCT variant_id) as total_items'),
            DB::raw('SUM(quantity) as total_qty'),
            DB::raw('MAX(supplier) as supplier'),
            DB::raw('MAX(supplier_phone) as supplier_phone'),
            DB::raw('MAX(supplier_address) as supplier_address')
        )
            ->groupBy('code')
            ->orderByDesc(DB::raw('MAX(created_at)'))
            ->paginate(20)
            ->withQueryString();

        return view('admin.stock_imports.history', compact('imports'));
    }

    /* =======================
        CHI TIẾT PHIẾU NHẬP
    ======================= */
    public function show($code)
    {
        $items = StockImport::with([
            'variant:id,product_id,attribute_value',
            'variant.product:id,name'
        ])
            ->where('code', $code)
            ->get();

        return view('admin.stock_imports.show', compact('items', 'code'));
    }

    /* =======================
        XUẤT PDF
    ======================= */
    public function exportPdf($code)
    {
        $items = StockImport::with([
            'variant:id,product_id,attribute_value',
            'variant.product:id,name'
        ])
            ->where('code', $code)
            ->get();

        if ($items->isEmpty()) {
            abort(404);
        }

        $pdf = Pdf::loadView('admin.stock_imports.pdf', [
            'items' => $items,
            'code'  => $code
        ]);

        return $pdf->download('phieu-nhap-' . $code . '.pdf');
    }
}