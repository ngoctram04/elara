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
     * 3. LOAD VARIANTS (chỉ khi cart có item)
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

                // Nếu variant đã bị xóa
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

                // Giá (ưu tiên giá khuyến mãi)
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

            // Cập nhật lại session nếu có thay đổi
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
     * 6. RETURN VIEW
     * ====================================================== */
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
     * 2. ÉP KIỂU (QUAN TRỌNG – TRÁNH TRÙNG KEY)
     * ============================== */
        $variantId = (int) $request->variant_id;
        $qty       = (int) $request->qty;

        $variant = ProductVariant::findOrFail($variantId);

        /* ==============================
     * 3. KIỂM TRA TỒN KHO
     * ============================== */
        if ($variant->stock_quantity <= 0) {
            return $this->responseError('Sản phẩm đã hết hàng', $request);
        }

        /* ==============================
     * 4. LẤY SESSION CART
     * ============================== */
        $cart = session()->get('cart', []);

        // Đảm bảo key là integer
        $cart = collect($cart)->mapWithKeys(function ($item) {
            return [(int)$item['variant_id'] => [
                'variant_id' => (int)$item['variant_id'],
                'quantity'   => (int)$item['quantity']
            ]];
        })->toArray();

        /* ==============================
     * 5. GỘP NẾU ĐÃ CÓ
     * ============================== */
        if (isset($cart[$variantId])) {

            $newQty = $cart[$variantId]['quantity'] + $qty;

            if ($newQty > $variant->stock_quantity) {
                return $this->responseError('Vượt quá tồn kho', $request);
            }

            $cart[$variantId]['quantity'] = $newQty;
        } else {

            if ($qty > $variant->stock_quantity) {
                return $this->responseError('Không đủ tồn kho', $request);
            }

            $cart[$variantId] = [
                'variant_id' => $variantId,
                'quantity'   => $qty,
            ];
        }

        /* ==============================
     * 6. LƯU SESSION
     * ============================== */
        session()->put('cart', $cart);

        /* ==============================
     * 7. SYNC DB (nếu login)
     * ============================== */
        if (Auth::check()) {
            Cart::updateOrCreate(
                [
                    'user_id'    => Auth::id(),
                    'variant_id' => $variantId,
                ],
                [
                    'quantity' => $cart[$variantId]['quantity']
                ]
            );
        }

        /* ==============================
     * 8. ĐẾM TỔNG SỐ LƯỢNG
     * ============================== */
        $cartCount = collect($cart)->sum('quantity');

        /* ==============================
     * 9. RESPONSE
     * ============================== */
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
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message
            ], 422);
        }

        return back()->withErrors(['qty' => $message]);
    }
}