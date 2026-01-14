<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\PromotionProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    /* =========================================================
        LIST
    ========================================================= */
    public function index()
    {
        $promotions = Promotion::orderByDesc('id')->paginate(10);
        return view('admin.promotions.index', compact('promotions'));
    }

    /* =========================================================
        CHOOSE TYPE
    ========================================================= */
    public function chooseType()
    {
        return view('admin.promotions.choose');
    }

    /* =========================================================
        CREATE – PRODUCT
    ========================================================= */
    public function createProduct()
    {
        $products = Product::with('variants')->get();
        return view('admin.promotions.create_product', compact('products'));
    }

    /* =========================================================
        CREATE – ORDER
    ========================================================= */
    public function createOrder()
    {
        return view('admin.promotions.create_order');
    }

    /* =========================================================
        STORE
    ========================================================= */
    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'type'           => 'required|in:product,order',
            'discount_type'  => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',

            // ✅ order only – UNIQUE CODE
            'code' => [
                'nullable',
                'required_if:type,order',
                'string',
                'max:50',
                function ($attr, $value, $fail) {
                    if (
                        Promotion::where('type', 'order')
                        ->where('code', $value)
                        ->exists()
                    ) {
                        $fail('Mã giảm giá đã tồn tại.');
                    }
                },
            ],

            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount'    => 'nullable|numeric|min:0',
            'usage_limit'     => 'nullable|integer|min:1',
        ]);

        // ❗ CHỈ PRODUCT PROMOTION mới check trùng thời gian
        if (
            $request->type === 'product'
            && $this->hasTimeConflict($request)
        ) {
            return back()
                ->withErrors([
                    'start_date' => 'Thời gian khuyến mãi bị trùng với khuyến mãi sản phẩm khác'
                ])
                ->withInput();
        }

        DB::transaction(function () use ($request) {

            $promotion = Promotion::create([
                'code'            => $request->type === 'order' ? $request->code : null,
                'name'            => $request->name,
                'type'            => $request->type,
                'discount_type'   => $request->discount_type,
                'discount_value'  => $request->discount_value,
                'min_order_value' => $request->min_order_value,
                'max_discount'    => $request->max_discount,
                'usage_limit'     => $request->usage_limit,
                'start_date'      => $request->start_date,
                'end_date'        => $request->end_date,
                'is_active'       => $request->boolean('is_active'),
            ]);

            // product promotion → bảng trung gian
            if ($promotion->type === 'product') {
                foreach ($request->products ?? [] as $productId => $variantIds) {
                    foreach ($variantIds as $variantId) {
                        PromotionProduct::create([
                            'promotion_id' => $promotion->id,
                            'product_id'   => $productId,
                            'variant_id'   => $variantId ?: null,
                        ]);
                    }
                }
            }
        });

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Tạo khuyến mãi thành công');
    }

    /* =========================================================
        EDIT REDIRECT
    ========================================================= */
    public function edit(Promotion $promotion)
    {
        return $promotion->type === 'product'
            ? redirect()->route('admin.promotions.edit.product', $promotion)
            : redirect()->route('admin.promotions.edit.order', $promotion);
    }

    /* =========================================================
        EDIT – PRODUCT
    ========================================================= */
    public function editProduct(Promotion $promotion)
    {
        abort_if($promotion->type !== 'product', 404);

        $products = Product::with('variants')->get();
        $selected = $promotion->promotionProducts;

        return view('admin.promotions.edit_product', compact(
            'promotion',
            'products',
            'selected'
        ));
    }

    /* =========================================================
        EDIT – ORDER
    ========================================================= */
    public function editOrder(Promotion $promotion)
    {
        abort_if($promotion->type !== 'order', 404);

        return view('admin.promotions.edit_order', compact('promotion'));
    }

    /* =========================================================
        UPDATE
    ========================================================= */
    public function update(Request $request, Promotion $promotion)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'discount_type'  => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount'   => 'nullable|numeric|min:0',
            'usage_limit'    => 'nullable|integer|min:1',
        ]);

        // ❗ CHỈ PRODUCT PROMOTION mới check trùng
        if (
            $promotion->type === 'product'
            && $this->hasTimeConflict($request, $promotion)
        ) {
            return back()
                ->withErrors([
                    'start_date' => 'Thời gian khuyến mãi bị trùng với khuyến mãi sản phẩm khác'
                ])
                ->withInput();
        }

        DB::transaction(function () use ($request, $promotion) {

            $promotion->update([
                'name'            => $request->name,
                'discount_type'   => $request->discount_type,
                'discount_value'  => $request->discount_value,
                'min_order_value' => $request->min_order_value,
                'max_discount'    => $request->max_discount,
                'usage_limit'     => $request->usage_limit,
                'start_date'      => $request->start_date,
                'end_date'        => $request->end_date,
                'is_active'       => $request->boolean('is_active'),
            ]);

            if ($promotion->type === 'product') {
                PromotionProduct::where('promotion_id', $promotion->id)->delete();

                foreach ($request->products ?? [] as $productId => $variantIds) {
                    foreach ($variantIds as $variantId) {
                        PromotionProduct::create([
                            'promotion_id' => $promotion->id,
                            'product_id'   => $productId,
                            'variant_id'   => $variantId ?: null,
                        ]);
                    }
                }
            }
        });

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Cập nhật khuyến mãi thành công');
    }

    /* =========================================================
        TOGGLE ACTIVE
    ========================================================= */
    public function toggle(Promotion $promotion)
    {
        $promotion->update([
            'is_active' => !$promotion->is_active
        ]);

        return back()->with('success', 'Đã cập nhật trạng thái');
    }

    /* =========================================================
        CHECK TIME CONFLICT (PRODUCT ONLY)
    ========================================================= */
    private function hasTimeConflict(Request $request, Promotion $ignore = null): bool
    {
        return Promotion::where('type', 'product')
            ->when($ignore, fn($q) => $q->where('id', '!=', $ignore->id))
            ->where(function ($q) use ($request) {
                $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                    ->orWhere(function ($q2) use ($request) {
                        $q2->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                    });
            })
            ->exists();
    }
}