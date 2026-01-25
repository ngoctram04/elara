<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductVariant;

class CartController extends Controller
{
    /**
     * Trang giỏ hàng
     * - GIÁ LUÔN LẤY MỚI NHẤT (final_price / price)
     */
    public function index()
    {
        $rawCart = session()->get('cart', []);
        $cart = [];
        $total = 0;

        foreach ($rawCart as $item) {
            $variant = ProductVariant::with(['product.mainImage', 'images'])
                ->find($item['variant_id']);

            if (!$variant) {
                continue;
            }

            // 🔥 GIÁ LUÔN LINH ĐỘNG
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
     * Thêm sản phẩm vào giỏ hàng
     * - DÙNG CHUNG cho:
     *   + AJAX (card / flash sale)
     *   + FORM (trang chi tiết)
     */
    public function add(Request $request)
    {
        /* ===============================
         | 1️⃣ CHUẨN HÓA QTY
         | - hỗ trợ qty & quantity
         =============================== */
        $qty = $request->input('qty', $request->input('quantity'));

        $request->merge([
            'qty' => $qty
        ]);

        /* ===============================
         | 2️⃣ VALIDATE
         =============================== */
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'qty'        => 'required|integer|min:1',
        ]);

        /** @var ProductVariant $variant */
        $variant = ProductVariant::findOrFail($request->variant_id);

        $qty = (int) $request->qty;

        /* ===============================
         | 3️⃣ CHECK TỒN KHO
         =============================== */
        if ($variant->availableStock() < $qty) {
            return $this->responseError(
                'Số lượng sản phẩm không đủ tồn kho',
                $request
            );
        }

        /* ===============================
         | 4️⃣ LẤY GIỎ HÀNG (RAW)
         =============================== */
        $cart = session()->get('cart', []);

        /* ===============================
         | 5️⃣ CỘNG / THÊM MỚI
         | - CHỈ LƯU variant_id + quantity
         =============================== */
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

        /* ===============================
         | 6️⃣ RESPONSE
         =============================== */
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
     * Cập nhật số lượng
     */
    public function update(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'qty'        => 'required|integer|min:1',
        ]);

        $cart = session()->get('cart', []);

        if (!isset($cart[$request->variant_id])) {
            return back();
        }

        $variant = ProductVariant::findOrFail($request->variant_id);

        if ($variant->availableStock() < $request->qty) {
            return back()->withErrors([
                'qty' => 'Số lượng vượt quá tồn kho',
            ]);
        }

        $cart[$request->variant_id]['quantity'] = $request->qty;
        session()->put('cart', $cart);

        return back()->with('success', 'Đã cập nhật giỏ hàng');
    }

    /**
     * Xóa 1 sản phẩm
     */
    public function remove($variantId)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$variantId])) {
            unset($cart[$variantId]);
            session()->put('cart', $cart);
        }

        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ');
    }

    /**
     * Xóa toàn bộ giỏ hàng
     */
    public function clear()
    {
        session()->forget('cart');
        return back()->with('success', 'Đã xóa toàn bộ giỏ hàng');
    }

    /* =====================================================
     | HELPER: RESPONSE ERROR (AJAX / FORM)
     ===================================================== */
    protected function responseError(string $message, Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return back()->withErrors([
            'qty' => $message,
        ]);
    }
}