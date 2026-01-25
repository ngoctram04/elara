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
     * Trang giỏ hàng
     * - GIÁ LUÔN LẤY MỚI NHẤT (final_price / price)
     */
    public function index()
    {
        // 🔥 LOGIN → LOAD DB → SYNC SESSION
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

        // ===== LOGIC CŨ =====
        $rawCart = session()->get('cart', []);
        $cart = [];
        $total = 0;

        foreach ($rawCart as $item) {
            $variant = ProductVariant::with(['product.mainImage', 'images'])
                ->find($item['variant_id']);

            if (!$variant) continue;

            $price = $variant->final_price ?? $variant->price;
            $subTotal = $price * $item['quantity'];
            $total += $subTotal;

            $cart[] = [
                'variant_id' => $variant->id,
                'product_id' => $variant->product_id,
                'name'       => $variant->product->name,
                'variant'    => $variant->displayName(),
                'price'      => $price,
                'original'   => $variant->original_price,
                'is_on_sale' => $variant->is_on_sale,
                'quantity'   => $item['quantity'],
                'sub_total'  => $subTotal,
                'image'      => $variant->images->first()->image_path
                    ?? $variant->product->mainImage->image_path
                    ?? null,
            ];
        }

        return view('frontend.cart.index', compact('cart', 'total'));
    }

    /**
     * Thêm sản phẩm (CARD + CHI TIẾT)
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
        $qty = (int) $qty;

        if ($variant->availableStock() < $qty) {
            return $this->responseError('Số lượng sản phẩm không đủ tồn kho', $request);
        }

        // ===== SESSION =====
        $cart = session()->get('cart', []);

        if (isset($cart[$variant->id])) {
            $newQty = $cart[$variant->id]['quantity'] + $qty;

            if ($variant->availableStock() < $newQty) {
                return $this->responseError(
                    'Tổng số lượng trong giỏ vượt quá tồn kho',
                    $request
                );
            }

            $cart[$variant->id]['quantity'] = $newQty;
        } else {
            $cart[$variant->id] = [
                'variant_id' => $variant->id,
                'quantity'   => $qty,
            ];
        }

        session()->put('cart', $cart);

        // ===== DB =====
        if (Auth::check()) {
            $dbCart = Cart::firstOrNew([
                'user_id'    => Auth::id(),
                'variant_id' => $variant->id,
            ]);

            $dbCart->quantity = ($dbCart->quantity ?? 0) + $qty;
            $dbCart->save();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => 'Đã thêm sản phẩm vào giỏ hàng',
                'cart_count' => collect($cart)->sum('quantity'),
            ]);
        }

        return back()->with('success', 'Đã thêm sản phẩm vào giỏ hàng');
    }

    /**
     * Cập nhật số lượng (input)
     */
    public function update(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'qty'        => 'required|integer|min:1',
        ]);

        $cart = session()->get('cart', []);

        if (!isset($cart[$request->variant_id])) return back();

        $variant = ProductVariant::findOrFail($request->variant_id);

        if ($variant->availableStock() < $request->qty) {
            return back()->withErrors(['qty' => 'Số lượng vượt quá tồn kho']);
        }

        $cart[$request->variant_id]['quantity'] = $request->qty;
        session()->put('cart', $cart);

        if (Auth::check()) {
            Cart::where('user_id', Auth::id())
                ->where('variant_id', $request->variant_id)
                ->update(['quantity' => $request->qty]);
        }

        return back()->with('success', 'Đã cập nhật giỏ hàng');
    }

    /**
     * + / − SỐ LƯỢNG (AJAX)
     */
    public function changeQty(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'type'       => 'required|in:plus,minus',
        ]);

        $cart = session()->get('cart', []);
        if (!isset($cart[$request->variant_id])) {
            return response()->json(['success' => false], 404);
        }

        $variant = ProductVariant::findOrFail($request->variant_id);
        $qty = $cart[$request->variant_id]['quantity'];

        if ($request->type === 'plus') {
            if ($variant->availableStock() <= $qty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vượt quá tồn kho'
                ], 422);
            }
            $qty++;
        } else {
            $qty--;
            if ($qty <= 0) {
                unset($cart[$request->variant_id]);
                session()->put('cart', $cart);

                if (Auth::check()) {
                    Cart::where('user_id', Auth::id())
                        ->where('variant_id', $request->variant_id)
                        ->delete();
                }

                return response()->json(['success' => true]);
            }
        }

        // SYNC
        $cart[$request->variant_id]['quantity'] = $qty;
        session()->put('cart', $cart);

        if (Auth::check()) {
            Cart::updateOrCreate(
                [
                    'user_id'    => Auth::id(),
                    'variant_id' => $request->variant_id,
                ],
                ['quantity' => $qty]
            );
        }

        return response()->json(['success' => true]);
    }

    /**
     * ĐỔI BIẾN THỂ
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
        $newVariant = ProductVariant::findOrFail($request->new_variant_id);

        if ($newVariant->availableStock() < $qty) {
            return response()->json([
                'success' => false,
                'message' => 'Biến thể mới không đủ tồn kho'
            ], 422);
        }

        // SESSION
        unset($cart[$request->old_variant_id]);

        if (isset($cart[$newVariant->id])) {
            $cart[$newVariant->id]['quantity'] += $qty;
        } else {
            $cart[$newVariant->id] = [
                'variant_id' => $newVariant->id,
                'quantity'   => $qty,
            ];
        }

        session()->put('cart', $cart);

        // DB
        if (Auth::check()) {
            Cart::where('user_id', Auth::id())
                ->where('variant_id', $request->old_variant_id)
                ->delete();

            Cart::updateOrCreate(
                [
                    'user_id'    => Auth::id(),
                    'variant_id' => $newVariant->id,
                ],
                ['quantity' => $cart[$newVariant->id]['quantity']]
            );
        }

        return response()->json(['success' => true]);
    }

    /**
     * XÓA 1 SẢN PHẨM
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

        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ');
    }

    /**
     * XÓA TOÀN BỘ GIỎ
     */
    public function clear()
    {
        session()->forget('cart');

        if (Auth::check()) {
            Cart::where('user_id', Auth::id())->delete();
        }

        return back()->with('success', 'Đã xóa toàn bộ giỏ hàng');
    }

    /* ================= HELPER ================= */
    protected function responseError(string $message, Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return back()->withErrors(['qty' => $message]);
    }
}