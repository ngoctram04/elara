<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * ============================
     * TRANG GIỎ HÀNG (TỐI ƯU)
     * ============================
     */
    public function index()
    {
        // Sync DB -> Session khi login
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
        if (empty($rawCart)) {
            return view('frontend.cart.index', [
                'cart' => [],
                'total' => 0
            ]);
        }

        // ===== LOAD ALL VARIANTS 1 LẦN (ANTI N+1)
        $variantIds = collect($rawCart)->pluck('variant_id');

        $variants = ProductVariant::with(['product.mainImage', 'images'])
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        $cart = [];
        $total = 0;
        $updatedSession = false;

        foreach ($rawCart as $item) {

            $variant = $variants[$item['variant_id']] ?? null;

            // Nếu variant bị xóa → remove khỏi cart
            if (!$variant) {
                unset($rawCart[$item['variant_id']]);
                $updatedSession = true;
                continue;
            }

            $stock = $variant->stock_quantity;
            $quantity = min($item['quantity'], $stock);

            // Nếu tồn kho giảm → tự chỉnh lại
            if ($quantity != $item['quantity']) {
                $rawCart[$variant->id]['quantity'] = $quantity;
                $updatedSession = true;
            }

            if ($quantity <= 0) {
                unset($rawCart[$variant->id]);
                $updatedSession = true;
                continue;
            }

            $price = $variant->price;
            $subTotal = $price * $quantity;
            $total += $subTotal;
            $productVariants = ProductVariant::where('product_id', $variant->product_id)->get();
            $cart[] = [
                'variant_id' => $variant->id,
                'product_id' => $variant->product_id,
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

        // Cập nhật lại session nếu có thay đổi
        if ($updatedSession) {
            session()->put('cart', $rawCart);
        }

        return view('frontend.cart.index', compact('cart', 'total'));
    }


    /**
     * ============================
     * THÊM VÀO GIỎ
     * ============================
     */

    public function add(Request $request)
    {
        // Lấy qty từ 2 nguồn (detail dùng qty, card dùng quantity)
        $qty = $request->input('qty', $request->input('quantity'));

        // Merge lại để validate
        $request->merge(['qty' => $qty]);

        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'qty'        => 'required|integer|min:1',
        ]);

        $variant = ProductVariant::findOrFail($request->variant_id);
        $qty = (int) $request->qty;

        /* =============================
        KIỂM TRA TỒN KHO
    ============================== */
        if ($variant->stock_quantity < $qty) {
            return $this->responseError('Không đủ tồn kho', $request);
        }

        /* =============================
        SESSION CART
    ============================== */
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

        /* =============================
        SYNC DB (nếu login)
    ============================== */
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

        /* =============================
        RESPONSE
    ============================== */

        // Nếu là AJAX
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => 'Đã thêm vào giỏ hàng',
                'cart_count' => $cartCount
            ]);
        }

        // Submit form bình thường
        return redirect()
            ->back()
            ->with('success', 'Đã thêm sản phẩm vào giỏ hàng');
    }

    /**
     * ============================
     * THAY ĐỔI SỐ LƯỢNG (AJAX)
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

        $qty = $cart[$request->old_variant_id]['quantity'];
        unset($cart[$request->old_variant_id]);

        if (isset($cart[$request->new_variant_id])) {
            $cart[$request->new_variant_id]['quantity'] += $qty;
        } else {
            $cart[$request->new_variant_id] = [
                'variant_id' => $request->new_variant_id,
                'quantity'   => $qty,
            ];
        }

        session()->put('cart', $cart);

        if (Auth::check()) {
            Cart::where('user_id', Auth::id())
                ->where('variant_id', $request->old_variant_id)
                ->delete();

            Cart::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'variant_id' => $request->new_variant_id,
                ],
                ['quantity' => $cart[$request->new_variant_id]['quantity']]
            );
        }

        return response()->json(['success' => true]);
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