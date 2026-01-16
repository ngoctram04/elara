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
            'discount_value' => 'required|integer|min:1|max:100',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',

            // ORDER ONLY
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

        if (
            $request->type === 'product'
            && $this->hasActiveProductConflict($request)
        ) {
            return back()
                ->withErrors([
                    'products' => 'Một số sản phẩm / biến thể đang có khuyến mãi khác đang diễn ra'
                ])
                ->withInput();
        }

        DB::transaction(function () use ($request) {

            $promotion = Promotion::create([
                'code'            => $request->type === 'order'
                    ? strtoupper($request->code)
                    : null,
                'name'            => $request->name,
                'type'            => $request->type,
                'discount_type'   => 'percent',
                'discount_value'  => $request->discount_value,
                'min_order_value' => $request->min_order_value,
                'max_discount'    => $request->max_discount,
                'usage_limit'     => $request->usage_limit,
                'start_date'      => $request->start_date,
                'end_date'        => $request->end_date,
                'is_active'       => $request->boolean('is_active'),
            ]);

            if ($promotion->type === 'product') {
                foreach ($request->products ?? [] as $productId => $variantIds) {
                    foreach ($variantIds as $variantId) {
                        PromotionProduct::create([
                            'promotion_id' => $promotion->id,
                            'product_id'   => $productId,
                            'variant_id'   => $variantId,
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
        EDIT (CHUẨN – KHÔNG REDIRECT)
    ========================================================= */
    public function edit(Promotion $promotion)
    {
        if ($promotion->type === 'product') {

            $products = Product::with('variants')->get();
            $selected = $promotion->promotionProducts;

            return view('admin.promotions.edit_product', compact(
                'promotion',
                'products',
                'selected'
            ));
        }

        return view('admin.promotions.edit_order', compact('promotion'));
    }

    /* =========================================================
        UPDATE
    ========================================================= */
    public function update(Request $request, Promotion $promotion)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'discount_value'  => 'required|integer|min:1|max:100',
            'start_date'      => 'required|date',
            'end_date'        => 'required|date|after_or_equal:start_date',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount'    => 'nullable|numeric|min:0',
            'usage_limit'     => 'nullable|integer|min:1',
        ]);

        if (
            $promotion->type === 'product'
            && $this->hasActiveProductConflict($request, $promotion)
        ) {
            return back()
                ->withErrors([
                    'products' => 'Một số sản phẩm / biến thể đang có khuyến mãi khác đang diễn ra'
                ])
                ->withInput();
        }

        DB::transaction(function () use ($request, $promotion) {

            $promotion->update([
                'name'            => $request->name,
                'discount_type'   => 'percent',
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
                            'variant_id'   => $variantId,
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
        CHECK TRÙNG BIẾN THỂ ĐANG KHUYẾN MÃI
    ========================================================= */
    private function hasActiveProductConflict(Request $request, Promotion $ignore = null): bool
    {
        if (empty($request->products)) {
            return false;
        }

        $variantIds = collect($request->products)->flatten()->filter();

        return PromotionProduct::whereIn('variant_id', $variantIds)
            ->whereHas('promotion', function ($q) use ($ignore) {
                $q->where('type', 'product')
                    ->where('is_active', true)
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());

                if ($ignore) {
                    $q->where('id', '!=', $ignore->id);
                }
            })
            ->exists();
    }
}