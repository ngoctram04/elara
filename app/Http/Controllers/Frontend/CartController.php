<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index()
    {
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

            $productIds = $variants->pluck('product_id')->unique();

            $productVariantsGroup = ProductVariant::whereIn('product_id', $productIds)
                ->get()
                ->groupBy('product_id');

            foreach ($rawCart as $variantId => $item) {
                $variant = $variants[$variantId] ?? null;

                if (!$variant) {
                    unset($rawCart[$variantId]);
                    $updatedSession = true;
                    continue;
                }

                $stock = $variant->stock_quantity;
                $quantity = min($item['quantity'], $stock);

                if ($quantity != $item['quantity']) {
                    $rawCart[$variantId]['quantity'] = $quantity;
                    $updatedSession = true;
                }

                if ($quantity <= 0) {
                    unset($rawCart[$variantId]);
                    $updatedSession = true;
                    continue;
                }

                $price = $variant->final_price ?? $variant->price;
                $originalPrice = $variant->is_on_sale ? $variant->price : null;

                $subTotal = $price * $quantity;
                $total += $subTotal;

                $productVariants = $productVariantsGroup[$variant->product_id] ?? collect();

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

            if ($updatedSession) {
                session()->put('cart', $rawCart);
            }
        }

        $promotionDiscount = session('promotion_discount', 0);

        $totalAfterPromotion = max(0, $total - $promotionDiscount);

        $birthdayDiscount = 0;
        $birthdayPercent = 0;

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user && $user->date_of_birth) {
            $birthdayDiscount = $this->getBirthdayBenefit($user, $totalAfterPromotion);

            if ($birthdayDiscount > 0) {
                $birthdayPercent = match ($user->member_level) {
                    'silver'  => 5,
                    'gold'    => 10,
                    'diamond' => 15,
                    default   => 0
                };
            }
        }

        session()->put('birthday_discount', $birthdayDiscount);       
        $finalTotal = max(0, $totalAfterPromotion - $birthdayDiscount);

        $reviewStats = DB::table('reviews')
            ->join('order_items', 'order_items.id', '=', 'reviews.order_item_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.variant_id')
            ->select(
                'product_variants.product_id',
                DB::raw('AVG(reviews.rating) as reviews_avg_rating'),
                DB::raw('COUNT(reviews.id) as reviews_count')
            )
            ->where('reviews.is_visible', 1)
            ->groupBy('product_variants.product_id');

        $suggestProducts = Product::with([
            'mainImage',
            'variants',
            'brand'
        ])
            ->leftJoinSub($reviewStats, 'review_stats', function ($join) {
                $join->on('products.id', '=', 'review_stats.product_id');
            })
            ->select(
                'products.*',
                DB::raw('COALESCE(review_stats.reviews_avg_rating, 0) as reviews_avg_rating'),
                DB::raw('COALESCE(review_stats.reviews_count, 0) as reviews_count')
            )
            ->where('products.is_active', 1)
            ->inRandomOrder()
            ->take(4)
            ->get();

        $userId = Auth::id();

        $availablePromotions = Promotion::query()
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

            ->where(function ($q) {
                $q->whereNull('usage_limit')
                    ->orWhereColumn('used_count', '<', 'usage_limit');
            })

            ->when($userId, function ($query) use ($userId) {
                $query->where(function ($q) use ($userId) {
                    $q->whereNotExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('user_point_rewards')
                            ->whereColumn('user_point_rewards.promotion_id', 'promotions.id');
                    })
                        ->orWhereExists(function ($sub) use ($userId) {
                            $sub->select(DB::raw(1))
                                ->from('user_point_rewards')
                                ->whereColumn('user_point_rewards.promotion_id', 'promotions.id')
                                ->where('user_point_rewards.user_id', $userId);
                        });
                });
            })

            ->when($userId, function ($query) use ($userId) {
                $query->whereNotExists(function ($q) use ($userId) {
                    $q->select(DB::raw(1))
                        ->from('orders')
                        ->whereColumn('orders.promotion_code', 'promotions.code')
                        ->where('orders.user_id', $userId)
                        ->where('orders.status', '!=', 4);
                });
            })

            ->orderByDesc('discount_value')
            ->get();

        return view('frontend.cart.index', [
            'cart' => $cart,
            'total' => $total,
            'promotionDiscount' => $promotionDiscount,
            'birthdayDiscount' => $birthdayDiscount,
            'birthdayPercent' => $birthdayPercent,
            'finalTotal' => $finalTotal,
            'suggestProducts' => $suggestProducts,
            'availablePromotions' => $availablePromotions,
        ]);
    }

    public function add(Request $request)
    {

        $qty = $request->input('qty', $request->input('quantity', 1));
        $request->merge(['qty' => $qty]);

        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'qty'        => 'required|integer|min:1',
        ]);


        $variantId = (int) $request->variant_id;
        $qty       = (int) $request->qty;

        $variant = ProductVariant::findOrFail($variantId);

        if ($variant->stock_quantity <= 0) {
            return $this->jsonError('Sản phẩm đã hết hàng');
        }

        $cart = session()->get('cart', []);

        $cart = collect($cart)->mapWithKeys(function ($item) {
            return [(int) $item['variant_id'] => [
                'variant_id' => (int) $item['variant_id'],
                'quantity'   => (int) $item['quantity']
            ]];
        })->toArray();

        $currentQty = $cart[$variantId]['quantity'] ?? 0;
        $newQty = $currentQty + $qty;

        if ($newQty > $variant->stock_quantity) {
            return $this->jsonError('Vượt quá tồn kho');
        }

        $cart[$variantId] = [
            'variant_id' => $variantId,
            'quantity'   => $newQty,
        ];

        session()->put('cart', $cart);

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
        $cartCount = collect($cart)->sum('quantity');

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success'    => true,
                'message'    => 'Đã thêm sản phẩm vào giỏ hàng',
                'cart_count' => $cartCount
            ]);
        }

        return back()->with('success', 'Đã thêm sản phẩm vào giỏ hàng');
    }

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

    public function changeVariant(Request $request)
    {
        $request->validate([
            'old_variant_id' => 'required|exists:product_variants,id',
            'new_variant_id' => 'required|exists:product_variants,id',
        ]);

        $cart = session()->get('cart', []);

        if (!isset($cart[$request->old_variant_id])) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại trong giỏ'
            ], 404);
        }

        $oldQty = $cart[$request->old_variant_id]['quantity'];

        $variant = ProductVariant::with(['images', 'product.mainImage'])
            ->findOrFail($request->new_variant_id);

        if ($variant->stock_quantity <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Biến thể đã hết hàng'
            ]);
        }

        $qty = min($oldQty, $variant->stock_quantity);

        unset($cart[$request->old_variant_id]);

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

        if (Auth::check()) {
            Cart::where('user_id', Auth::id())
                ->where('variant_id', $request->old_variant_id)
                ->delete();

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

        $image = $variant->images->first()->image_path
            ?? $variant->product->mainImage->image_path
            ?? null;

        $price = $variant->final_price ?? $variant->price;
        $originalPrice = $variant->is_on_sale ? $variant->price : null;

        return response()->json([
            'success'        => true,
            'new_id'         => $variant->id,
            'price'          => $price,
            'original_price' => $originalPrice,
            'stock'          => $variant->stock_quantity,
            'quantity'       => $qty,
            'variant'        => $variant->attribute_value,
            'image'          => $image
        ]);
    }

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

    public function applyPromotion(Request $request)
    {
        $request->validate([
            'code'  => 'required|string',
            'total' => 'required|numeric|min:0'
        ]);

        $promotion = Promotion::where('code', $request->code)
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

        if (!$promotion) {
            return response()->json([
                'success' => false,
                'message' => 'Mã không hợp lệ hoặc đã hết hạn'
            ]);
        }

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

        $minimum = (float) ($promotion->min_order_value ?? 0);

        if ($minimum > 0 && $total < $minimum) {
            $needMore = $minimum - $total;

            return response()->json([
                'success' => false,
                'message' => 'Đơn tối thiểu ' . number_format($minimum) . 'đ. '
                    . 'Mua thêm ' . number_format($needMore) . 'đ để áp dụng.'
            ]);
        }
        $value = (float) $promotion->discount_value;

        if ($promotion->discount_type === 'percent') {
            $discount = $total * ($value / 100);
        } else {
            $discount = $value;
        }

        if (!empty($promotion->max_discount)) {
            $discount = min($discount, $promotion->max_discount);
        }

        $discount = min($discount, $total);
        $discount = round($discount);
        $finalTotal = round($total - $discount);

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

    private function getBirthdayBenefit($user, $amount)
    {
        if (!$user || !$user->date_of_birth) {
            return 0;
        }

        $today = Carbon::today();
        $birthday = Carbon::parse($user->date_of_birth);
        if ($today->month != $birthday->month || $today->day != $birthday->day) {
            return 0;
        }

        if ((int) $user->birthday_discount_year === (int) $today->year) {
            return 0;
        }

        $percent = match ($user->member_level) {
            'silver'  => 5,
            'gold'    => 10,
            'diamond' => 15,
            default   => 0,
        };

        if ($percent <= 0) {
            return 0;
        }

        return round(($amount * $percent) / 100);
    }
}