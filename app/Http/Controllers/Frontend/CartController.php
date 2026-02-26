<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Models\Promotion;
class CartController extends Controller
{
    /**
     * ============================
     * TRANG GIỎ HÀNG (A++)
     * ============================
     */
    public function index()
    {
        /* ======================================================
     * 1. SYNC DB -> SESSION (nếu user đã đăng nhập)
     * ====================================================== */
        if (Auth::check()) {

            $dbItems = Cart::where('user_id', Auth::id())->get();

            $sessionCart = [];

            foreach ($dbItems as $item) {
                $sessionCart[$item->variant_id] = [
                    'variant_id' => $item->variant_id,
                    'quantity'   => $item->quantity,
                ];
            }

            session()->put('cart', $sessionCart);
        }

        /* ======================================================
     * 2. LẤY CART TỪ SESSION
     * ====================================================== */
        $rawCart = session()->get('cart', []);
        $cart = [];
        $total = 0;
        $updatedSession = false;

        /* ======================================================
     * 3. LOAD VARIANTS
     * ====================================================== */
        if (!empty($rawCart)) {

            $variantIds = collect($rawCart)->pluck('variant_id');

            $variants = ProductVariant::with([
                'product.mainImage',
                'product',
                'images',
            ])
                ->whereIn('id', $variantIds)
                ->get()
                ->keyBy('id');

            // Load tất cả variant theo product (để đổi biến thể)
            $productIds = $variants->pluck('product_id')->unique();

            $productVariantsGroup = ProductVariant::whereIn('product_id', $productIds)
                ->get()
                ->groupBy('product_id');

            /* ======================================================
         * 4. BUILD CART DATA
         * ====================================================== */
            foreach ($rawCart as $variantId => $item) {

                $variant = $variants[$variantId] ?? null;

                // Variant bị xóa
                if (!$variant) {
                    unset($rawCart[$variantId]);
                    $updatedSession = true;
                    continue;
                }

                $stock = $variant->stock_quantity;
                $quantity = min($item['quantity'], $stock);

                // Nếu tồn kho giảm
                if ($quantity != $item['quantity']) {
                    $rawCart[$variantId]['quantity'] = $quantity;
                    $updatedSession = true;
                }

                // Hết hàng
                if ($quantity <= 0) {
                    unset($rawCart[$variantId]);
                    $updatedSession = true;
                    continue;
                }

                // Giá
                $price = $variant->final_price ?? $variant->price;
                $originalPrice = $variant->price;

                $subTotal = $price * $quantity;
                $total += $subTotal;

                $productVariants = $productVariantsGroup[$variant->product_id] ?? collect();

                // Ảnh ưu tiên: variant -> product
                $image =
                    optional($variant->images->first())->image_path
                    ?? optional($variant->product->mainImage)->image_path
                    ?? null;

                $cart[] = [
                    'variant_id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'slug'       => $variant->product->slug,
                    'name'       => $variant->product->name,
                    'variant'    => $variant->attribute_value,

                    'price'          => $price,
                    'original_price' => $originalPrice,

                    'quantity'  => $quantity,
                    'sub_total' => $subTotal,
                    'stock'     => $stock,

                    'variants' => $productVariants,
                    'image'    => $image,
                ];
            }

            // Update session nếu có thay đổi
            if ($updatedSession) {
                session()->put('cart', $rawCart);
            }
        }

        /* ======================================================
     * 5. SẢN PHẨM GỢI Ý
     * ====================================================== */
        $suggestProducts = Product::with('mainImage')
        ->where('is_active', 1)
        ->inRandomOrder()
            ->take(4)
            ->get();

        /* ======================================================
     * 6. VOUCHER KHẢ DỤNG (QUAN TRỌNG)
     * ====================================================== */
        $availablePromotions = Promotion::where('is_active', 1)
        ->where('type', 'order') // chỉ voucher đơn hàng
        ->where(function ($q) {
            $q->whereNull('start_date')
            ->orWhere('start_date', '<=', now());
        })
            ->where(function ($q) {
                $q->whereNull('end_date')
                ->orWhere('end_date', '>=', now());
            })
            ->orderByDesc('discount_value')
            ->get();

        /* ======================================================
     * 7. RETURN VIEW
     * ====================================================== */
        return view('frontend.cart.index', [
            'cart' => $cart,
            'total' => $total,
            'suggestProducts' => $suggestProducts,
            'availablePromotions' => $availablePromotions, // ⭐ thêm dòng này
        ]);
    }


    /**
     * ============================
     * THÊM VÀO GIỎ
     * ============================
     */
    public function add(Request $request)
    {
        /* ==============================
     * 1. LẤY SỐ LƯỢNG (tương thích mọi form)
     * ============================== */
        $qty = $request->input('qty', $request->input('quantity', 1));
        $request->merge(['qty' => $qty]);

        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'qty'        => 'required|integer|min:1',
        ]);

        /* ==============================
     * 2. ÉP KIỂU
     * ============================== */
        $variantId = (int) $request->variant_id;
        $qty       = (int) $request->qty;

        $variant = ProductVariant::findOrFail($variantId);

        /* ==============================
     * 3. KIỂM TRA TỒN KHO
     * ============================== */
        if ($variant->stock_quantity <= 0) {
            return $this->jsonError('Sản phẩm đã hết hàng');
        }

        /* ==============================
     * 4. LẤY SESSION CART
     * ============================== */
        $cart = session()->get('cart', []);

        // Chuẩn hóa key = variant_id (int)
        $cart = collect($cart)->mapWithKeys(function ($item) {
            return [(int)$item['variant_id'] => [
                'variant_id' => (int)$item['variant_id'],
                'quantity'   => (int)$item['quantity']
            ]];
        })->toArray();

        /* ==============================
     * 5. THÊM / GỘP SẢN PHẨM
     * ============================== */
        $currentQty = $cart[$variantId]['quantity'] ?? 0;
        $newQty = $currentQty + $qty;

        if ($newQty > $variant->stock_quantity) {
            return $this->jsonError('Vượt quá tồn kho');
        }

        $cart[$variantId] = [
            'variant_id' => $variantId,
            'quantity'   => $newQty,
        ];

        /* ==============================
     * 6. LƯU SESSION
     * ============================== */
        session()->put('cart', $cart);

        /* ==============================
     * 7. SYNC DB (nếu đăng nhập)
     * ============================== */
        if (Auth::check()) {
            Cart::updateOrCreate(
                [
                    'user_id'    => Auth::id(),
                    'variant_id' => $variantId,
                ],
                [
                    'quantity' => $newQty
                ]
            );
        }

        /* ==============================
     * 8. ĐẾM TỔNG SỐ LƯỢNG
     * ============================== */
        $cartCount = collect($cart)->sum('quantity');

        /* ==============================
     * 9. RESPONSE (LUÔN JSON cho AJAX)
     * ============================== */
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => 'Đã thêm sản phẩm vào giỏ hàng',
                'cart_count' => $cartCount
            ]);
        }

        return back()->with('success', 'Đã thêm sản phẩm vào giỏ hàng');
    }


    /**
     * ============================
     * ĐỔI SỐ LƯỢNG
     * ============================
     */
    public function changeQty(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $variant = ProductVariant::findOrFail($request->variant_id);

        if ($variant->stock_quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Vượt quá tồn kho'
            ], 422);
        }

        $cart = session()->get('cart', []);
        $cart[$variant->id]['quantity'] = $request->quantity;
        session()->put('cart', $cart);

        if (Auth::check()) {
            Cart::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'variant_id' => $variant->id,
                ],
                ['quantity' => $request->quantity]
            );
        }

        return response()->json(['success' => true]);
    }


    /**
     * ============================
     * ĐỔI BIẾN THỂ
     * ============================
     */
    public function changeVariant(Request $request)
    {
        $request->validate([
            'old_variant_id' => 'required|exists:product_variants,id',
            'new_variant_id' => 'required|exists:product_variants,id',
        ]);

        $cart = session()->get('cart', []);

        /* ==========================
       KIỂM TRA TỒN TẠI
    ========================== */
        if (!isset($cart[$request->old_variant_id])) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại trong giỏ'
            ], 404);
        }

        $oldQty = $cart[$request->old_variant_id]['quantity'];

        /* ==========================
       LOAD VARIANT
    ========================== */
        $variant = ProductVariant::with(['images', 'product.mainImage'])
        ->findOrFail($request->new_variant_id);

        if ($variant->stock_quantity <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Biến thể đã hết hàng'
            ]);
        }

        /* ==========================
       TÍNH SỐ LƯỢNG MỚI
    ========================== */
        $qty = min($oldQty, $variant->stock_quantity);

        // Xóa variant cũ
        unset($cart[$request->old_variant_id]);

        // Nếu variant mới đã tồn tại → gộp
        if (isset($cart[$request->new_variant_id])) {
            $qty = min(
                $cart[$request->new_variant_id]['quantity'] + $qty,
                $variant->stock_quantity
            );
        }

        // Lưu lại session
        $cart[$request->new_variant_id] = [
            'variant_id' => $request->new_variant_id,
            'quantity'   => $qty,
        ];

        session()->put('cart', $cart);

        /* ==========================
       SYNC DB
    ========================== */
        if (Auth::check()) {

            // Xóa variant cũ
            Cart::where('user_id', Auth::id())
                ->where('variant_id', $request->old_variant_id)
                ->delete();

            // Cập nhật variant mới
            Cart::updateOrCreate(
                [
                    'user_id'    => Auth::id(),
                    'variant_id' => $request->new_variant_id,
                ],
                [
                    'quantity' => $qty
                ]
            );
        }

        /* ==========================
       DỮ LIỆU TRẢ VỀ AJAX
    ========================== */
        $image = $variant->images->first()->image_path
            ?? $variant->product->mainImage->image_path
            ?? null;

        $price = $variant->final_price ?? $variant->price;
        $originalPrice = $variant->price;

        return response()->json([
            'success'        => true,
            'new_id'         => $variant->id,          // quan trọng để update row
            'price'          => $price,
            'original_price' => $originalPrice,        // ⭐ để hiển thị giá gốc
            'stock'          => $variant->stock_quantity,
            'quantity'       => $qty,
            'variant'        => $variant->attribute_value,
            'image'          => $image
        ]);
    }


    /**
     * ============================
     * XÓA ITEM
     * ============================
     */
    public function remove($variantId)
    {
        $cart = session()->get('cart', []);
        unset($cart[$variantId]);
        session()->put('cart', $cart);

        if (Auth::check()) {
            Cart::where('user_id', Auth::id())
                ->where('variant_id', $variantId)
                ->delete();
        }

        return response()->json(['success' => true]);
    }


    /**
     * ============================
     * XÓA TOÀN BỘ
     * ============================
     */
    public function clear()
    {
        session()->forget('cart');

        if (Auth::check()) {
            Cart::where('user_id', Auth::id())->delete();
        }

        return back();
    }


    protected function responseError(string $message, Request $request)
    {
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message
            ]);
        }

        return back()->withErrors(['qty' => $message]);
    }
    /* ============================
 * ÁP DỤNG VOUCHER
 * ============================ */
    public function applyPromotion(Request $request)
    {
        $request->validate([
            'code'  => 'required|string',
            'total' => 'required|numeric|min:0'
        ]);

        $promotion = Promotion::where('code',
            $request->code
        )
        ->where('is_active', 1)
            ->where('type', 'order')
            ->where(function ($q) {
                $q->whereNull('start_date')
                ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->first();

        // ===== Không tồn tại / hết hạn =====
        if (!$promotion) {
            return response()->json([
                'success' => false,
                'message' => 'Mã không hợp lệ hoặc đã hết hạn'
            ]);
        }

        /**
         * ======================================
         * CHẶN KHI HẾT LƯỢT (QUAN TRỌNG)
         * ======================================
         */
        if (
            $promotion->usage_limit !== null &&
            $promotion->used_count >= $promotion->usage_limit
        ) {

            return response()->json([
                'success' => false,
                'message' => 'Mã đã hết lượt sử dụng'
            ]);
        }

        $total = (float) $request->total;

        /* ===== Đơn tối thiểu ===== */
        $minimum = (float) ($promotion->min_order_value ?? 0);

        if ($minimum > 0 && $total < $minimum) {
            $needMore = $minimum - $total;

            return response()->json([
                'success' => false,
                'message' => 'Đơn tối thiểu ' . number_format($minimum) . 'đ. '
                . 'Mua thêm ' . number_format($needMore) . 'đ để áp dụng.'
            ]);
        }

        /* ===== Tính giảm ===== */
        $value = (float) $promotion->discount_value;

        if ($promotion->discount_type === 'percent') {
            $discount = $total * ($value / 100);
        } else {
            $discount = $value;
        }

        /* ===== Giảm tối đa ===== */
        if (!empty($promotion->max_discount)) {
            $discount = min($discount, $promotion->max_discount);
        }

        $discount = min($discount, $total);
        $discount = round($discount);
        $finalTotal = round($total - $discount);

        /**
         * ======================================
         * LƯU SESSION ĐỂ CHECKOUT DÙNG
         * ======================================
         */
        session([
            'promotion_code'     => $promotion->code,
            'promotion_discount' => $discount,
            'promotion_name'     => $promotion->name,
        ]);

        return response()->json([
            'success'     => true,
            'name'        => $promotion->name,
            'code'        => $promotion->code,
            'discount'    => $discount,
            'final_total' => $finalTotal
        ]);
    }
    protected function jsonError(string $message)
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ]);
    }
}