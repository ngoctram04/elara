<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function show(string $slug)
    {
        $userId = Auth::id();

        /* ======================================================
         * 1. LOAD PRODUCT
         * ====================================================== */
        $product = Product::with([
            'images',
            'mainImage',
            'variants' => function ($q) {
                $q->where('is_active', 1)
                    ->orderBy('id')
                    ->with('images');
            },
            'category',
            'brand',
            'promotions' => function ($q) {
                $q->where('is_active', 1);
            },
            'reviews' => function ($q) {
                $q->latest()
                    ->with([
                        'user:id,name,avatar',
                        'media'
                    ]);
            }
        ])
            ->where('slug', $slug)
            ->where('is_active', 1)
            ->firstOrFail();


        /* ======================================================
         * 2. REVIEW DATA
         * ====================================================== */
        $reviews = $product->reviews;
        $reviewsCount = $reviews->count();
        $avgRating = $reviewsCount > 0
            ? round($reviews->avg('rating'), 1)
            : 0;


        /* ======================================================
         * 3. TOTAL SOLD
         * ====================================================== */
        $totalSold = $product->variants->sum('sold_quantity');


        /* ======================================================
         * 4. WISHLIST
         * ====================================================== */
        $favorites = [];
        if ($userId) {
            $favorites = Wishlist::where('user_id', $userId)
                ->pluck('product_id')
                ->toArray();
        }

        $favoritesCount = Wishlist::where('product_id', $product->id)->count();


        /* ======================================================
         * 5. RELATED PRODUCTS (CHUẨN – LUÔN CÙNG CATEGORY)
         * ====================================================== */

        // Lấy sản phẩm cùng category
        $relatedProducts = Product::with([
            'mainImage',
            'brand'
        ])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->withSum('variants as total_sold', 'sold_quantity')
            ->where('is_active', 1)
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->whereHas('variants', function ($q) {
                $q->where('stock_quantity', '>', 0)
                    ->where('is_active', 1);
            })

            // Ưu tiên cùng brand trước
            ->orderByRaw("brand_id = ? DESC", [$product->brand_id])

            // Sau đó theo bán chạy
            ->orderByDesc('total_sold')

            ->limit(8)
            ->get();


        /* ======================================================
         * 6. FALLBACK (nếu chưa đủ 8 → random TRONG CÙNG CATEGORY)
         * ====================================================== */
        if ($relatedProducts->count() < 8) {

            $excludeIds = $relatedProducts->pluck('id')->push($product->id);

            $moreProducts = Product::with([
                'mainImage',
                'brand'
            ])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('is_active', 1)

                // ⭐ vẫn giữ cùng category
                ->where('category_id', $product->category_id)

                ->whereNotIn('id', $excludeIds)
                ->whereHas('variants', function ($q) {
                    $q->where('stock_quantity', '>', 0)
                        ->where('is_active', 1);
                })
                ->inRandomOrder()
                ->limit(8 - $relatedProducts->count())
                ->get();

            $relatedProducts = $relatedProducts->merge($moreProducts);
        }


        /* ======================================================
         * 7. RETURN VIEW
         * ====================================================== */
        return view('frontend.detail', compact(
            'product',
            'reviews',
            'reviewsCount',
            'avgRating',
            'relatedProducts',
            'favorites',
            'favoritesCount',
            'totalSold'
        ));
    }


    public function quickView(int $id)
    {
        $product = Product::with([
            'images',
            'mainImage',
            'variants' => function ($q) {
                $q->where('is_active', 1)
                    ->orderBy('id')
                    ->with('images');
            },
            'category',
            'brand',
            'promotions' => function ($q) {
                $q->where('is_active', 1);
            }
        ])
            ->where('id', $id)
            ->where('is_active', 1)
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data'   => $product
        ]);
    }
}