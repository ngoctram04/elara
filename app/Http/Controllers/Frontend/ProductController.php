<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function show(string $slug, Request $request)
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

            // ⭐ THÊM Q&A
            'questions' => function ($q) {
                $q->where('is_active', 1)
                ->latest()
                    ->with([
                        'user:id,name',
                        'answers.user:id,name'
                    ]);
            }

        ])
        ->where('slug', $slug)
        ->where('is_active', 1)
        ->firstOrFail();


        /* ======================================================
     * 2. REVIEWS (FILTER + SORT + PAGINATION)
     * ====================================================== */

        $reviewsQuery = $product->reviews()
            ->with([
                'user:id,name,avatar',
                'media'
            ]);

        /* ===== FILTER RATING ===== */
        if ($request->rating && $request->rating !== 'all') {
            $reviewsQuery->where('rating', $request->rating);
        }

        /* ===== FILTER COMMENT ===== */
        if ($request->type === 'comment') {
            $reviewsQuery->whereNotNull('comment');
        }

        /* ===== FILTER MEDIA ===== */
        if ($request->type === 'media') {
            $reviewsQuery->whereHas('media');
        }

        /* ===== SORT ===== */
        if ($request->sort === 'old') {
            $reviewsQuery->orderBy('created_at', 'asc');
        } else {
            $reviewsQuery->orderBy('created_at', 'desc');
        }

        $reviews = $reviewsQuery->paginate(5);


        /* ======================================================
     * 3. REVIEW STATS
     * ====================================================== */

        $reviewCount = $product->reviews()->count();

        $avgRating = $reviewCount
            ? round($product->reviews()->avg('rating'), 1)
            : 0;

        $ratingStats = [];

        for ($i = 1; $i <= 5; $i++) {
            $ratingStats[$i] = $product->reviews()
                ->where('rating', $i)
                ->count();
        }

        $withComment = $product->reviews()
            ->whereNotNull('comment')
            ->count();

        $withMedia = $product->reviews()
            ->whereHas('media')
            ->count();


        /* ======================================================
     * 4. TOTAL SOLD
     * ====================================================== */

        $totalSold = $product->variants->sum('sold_quantity');


        /* ======================================================
     * 5. WISHLIST
     * ====================================================== */

        $favorites = [];

        if ($userId) {
            $favorites = Wishlist::where('user_id', $userId)
                ->pluck('product_id')
                ->toArray();
        }

        $favoritesCount = Wishlist::where('product_id', $product->id)->count();


        /* ======================================================
     * 6. RELATED PRODUCTS
     * ====================================================== */

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
            ->orderByRaw("brand_id = ? DESC", [$product->brand_id])
            ->orderByDesc('total_sold')
            ->limit(8)
            ->get();


        /* ======================================================
     * 7. FALLBACK RELATED
     * ====================================================== */

        if ($relatedProducts->count() < 8) {

            $excludeIds = $relatedProducts
                ->pluck('id')
                ->push($product->id);

            $moreProducts = Product::with([
                'mainImage',
                'brand'
            ])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->where('is_active', 1)
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
     * 8. RETURN VIEW
     * ====================================================== */

        return view('frontend.detail', compact(
            'product',
            'reviews',
            'reviewCount',
            'avgRating',
            'ratingStats',
            'withComment',
            'withMedia',
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