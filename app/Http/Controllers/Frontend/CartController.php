<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * ============================
     * TRANG GIỎ HÀNG (A++)
     * ============================
     */
    public function index()
    {
        /* ======================================
     * 1. SYNC DB -> SESSION (nếu login)
     * ====================================== */
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

        $rawCart = session()->get('cart', []);
        $cart = [];
        $total = 0;
        $updatedSession = false;

        /* ======================================
     * 2. CHỈ LOAD DB KHI CART CÓ ITEM
     * ====================================== */
        if (!empty($rawCart)) {

            // Lấy tất cả variant trong cart
            $variantIds = collect($rawCart)->pluck('variant_id');

            $variants = ProductVariant::with([
                'product.mainImage',
                'product',
                'images'
            ])
                ->whereIn('id', $variantIds)
                ->get()
                ->keyBy('id');

            // Load tất cả variants theo product (để dropdown đổi biến thể)
            $productIds = $variants->pluck('product_id')->unique();

            $productVariantsGroup = ProductVariant::whereIn('product_id', $productIds)
                ->get()
                ->groupBy('product_id');

            /* ======================================
         * 3. BUILD CART
         * ====================================== */
            foreach ($rawCart as $item) {

                $variant = $variants[$item['variant_id']] ?? null;

                // Variant bị xóa
                if (!$variant) {
                    unset($rawCart[$item['variant_id']]);
                    $updatedSession = true;
                    continue;
                }

                $stock = $variant->stock_quantity;
                $quantity = min($item['quantity'], $stock);

                // Nếu tồn kho giảm
                if ($quantity != $item['quantity']) {
                    $rawCart[$variant->id]['quantity'] = $quantity;
                    $updatedSession = true;
                }

                // Hết hàng
                if ($quantity <= 0) {
                    unset($rawCart[$variant->id]);
                    $updatedSession = true;
                    continue;
                }

                $price = $variant->price;
                $subTotal = $price * $quantity;
                $total += $subTotal;

                $productVariants = $productVariantsGroup[$variant->product_id] ?? collect();

                $cart[] = [
                    'variant_id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'slug'       => $variant->product->slug,
                    'name'       => $variant->product->name,
                    'variant'    => $variant->attribute_value,
                    'price'      => $price,
                    'quantity'   => $quantity,
                    'sub_total'  => $subTotal,
                    'stock'      => $stock,
                    'variants'   => $productVariants,

                    'image' => $variant->images->first()->image_path
                        ?? $variant->product->mainImage->image_path
                        ?? null,
                ];
            }

            // Update lại session nếu có thay đổi
            if ($updatedSession) {
                session()->put('cart', $rawCart);
            }
        }

        /* ======================================
     * 4. SẢN PHẨM GỢI Ý (LUÔN CÓ)
     * ====================================== */
        $suggestProducts = Product::with('mainImage')
        ->where('is_active', 1)
        ->inRandomOrder()
            ->take(4)
            ->get();

        /* ======================================
     * 5. RETURN VIEW
     * ====================================== */
        return view('frontend.cart.index', [
            'cart' => $cart,
            'total' => $total,
            'suggestProducts' => $suggestProducts
        ]);
    }


    /**
     * ============================
     * THÊM VÀO GIỎ
     * ============================
     */
    public function add(Request $request)
    {
        $qty = $request->input('qty', $request->input('quantity'));

        $request->merge(['qty' => $qty]);

        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'qty'        => 'required|integer|min:1',
        ]);

        $variant = ProductVariant::findOrFail($request->variant_id);
        $qty = (int) $request->qty;

        if ($variant->stock_quantity < $qty) {
            return $this->responseError('Không đủ tồn kho', $request);
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$variant->id])) {

            $newQty = $cart[$variant->id]['quantity'] + $qty;

            if ($variant->stock_quantity < $newQty) {
                return $this->responseError('Vượt quá tồn kho', $request);
            }

            $cart[$variant->id]['quantity'] = $newQty;

        } else {

            $cart[$variant->id] = [
                'variant_id' => $variant->id,
                'quantity'   => $qty,
            ];
        }

        session()->put('cart', $cart);

        // Sync DB
        if (Auth::check()) {
            Cart::updateOrCreate(
                [
                    'user_id'    => Auth::id(),
                    'variant_id' => $variant->id,
                ],
                [
                    'quantity' => $cart[$variant->id]['quantity']
                ]
            );
        }

        $cartCount = collect($cart)->sum('quantity');

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => 'Đã thêm vào giỏ hàng',
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

        if (!isset($cart[$request->old_variant_id])) {
            return response()->json(['success' => false], 404);
        }

        $oldQty = $cart[$request->old_variant_id]['quantity'];

        // Lấy variant mới
        $variant = ProductVariant::findOrFail($request->new_variant_id);

        /* ===== FIX QUAN TRỌNG =====
       Không cho vượt tồn kho
    */
        $qty = min($oldQty, $variant->stock_quantity);

        // Xóa variant cũ
        unset($cart[$request->old_variant_id]);

        // Nếu variant mới đã tồn tại → cộng nhưng vẫn giới hạn stock
        if (isset($cart[$request->new_variant_id])) {
            $qty = min(
                $cart[$request->new_variant_id]['quantity'] + $qty,
                $variant->stock_quantity
            );
        }

        $cart[$request->new_variant_id] = [
            'variant_id' => $request->new_variant_id,
            'quantity'   => $qty,
        ];

        session()->put('cart', $cart);

        /* ===== SYNC DB ===== */
        if (Auth::check()) {
            Cart::where('user_id', Auth::id())
                ->where('variant_id', $request->old_variant_id)
                ->delete();

            Cart::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'variant_id' => $request->new_variant_id,
                ],
                ['quantity' => $qty]
            );
        }
// Load ảnh
$variant->load(['images', 'product.mainImage']);

$image = $variant->images->first()->image_path
    ?? $variant->product->mainImage->image_path
    ?? null;

return response()->json([
    'success' => true,
    'price'   => $variant->price,
    'stock'   => $variant->stock_quantity,
    'quantity'=> $qty,
    'variant' => $variant->attribute_value,
    'image'   => $image
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
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message
            ], 422);
        }

        return back()->withErrors(['qty' => $message]);
    }
}