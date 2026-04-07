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
         * 2. REVIEW BASE QUERY
         * - CHỈ LẤY REVIEW ĐANG HIỂN THỊ
         * ====================================================== */
        $visibleReviewsQuery = $product->reviews()
            ->where('is_visible', 1);

        /* ======================================================
         * 3. REVIEWS (FILTER + SORT)
         * ====================================================== */
        $reviewsQuery = (clone $visibleReviewsQuery)
            ->with([
                'user:id,name,avatar',
                'media'
            ]);

        if ($request->filled('rating') && $request->rating !== 'all') {
            $reviewsQuery->where('rating', $request->rating);
        }

        if ($request->type === 'comment') {
            $reviewsQuery->whereNotNull('comment')
                ->where('comment', '!=', '');
        }

        if ($request->type === 'media') {
            $reviewsQuery->whereHas('media');
        }

        if ($request->sort === 'old') {
            $reviewsQuery->orderBy('created_at', 'asc');
        } else {
            $reviewsQuery->orderBy('created_at', 'desc');
        }

        $reviews = $reviewsQuery->get();

        /* ======================================================
         * 4. REVIEW STATS
         * ====================================================== */
        $reviewCount = (clone $visibleReviewsQuery)->count();

        $avgRating = $reviewCount > 0
            ? round((clone $visibleReviewsQuery)->avg('rating'), 1)
            : 0;

        $ratingStats = [];

        for ($i = 1; $i <= 5; $i++) {
            $ratingStats[$i] = (clone $visibleReviewsQuery)
                ->where('rating', $i)
                ->count();
        }

        $withComment = (clone $visibleReviewsQuery)
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->count();

        $withMedia = (clone $visibleReviewsQuery)
            ->whereHas('media')
            ->count();

        /* ======================================================
         * 5. TOTAL SOLD
         * ====================================================== */
        $totalSold = $product->total_sold;

        /* ======================================================
         * 6. WISHLIST
         * ====================================================== */
        $favorites = [];

        if ($userId) {
            $favorites = Wishlist::where('user_id', $userId)
                ->pluck('product_id')
                ->toArray();
        }

        $favoritesCount = Wishlist::where('product_id', $product->id)->count();

        /* ======================================================
         * 7. RELATED PRODUCTS
         * ====================================================== */
        $relatedProducts = Product::with([
            'mainImage',
            'brand'
        ])
            ->withAvg([
                'reviews' => function ($q) {
                    $q->where('is_visible', 1);
                }
            ], 'rating')
            ->withCount([
                'reviews' => function ($q) {
                    $q->where('is_visible', 1);
                }
            ])
            ->withSum('variants as variants_sold_sum', 'sold_quantity')
            ->where('is_active', 1)
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->whereHas('variants', function ($q) {
                $q->where('stock_quantity', '>', 0)
                    ->where('is_active', 1);
            })
            ->orderByRaw("brand_id = ? DESC", [$product->brand_id])
            ->orderByDesc('variants_sold_sum')
            ->limit(8)
            ->get();

        /* ======================================================
         * 8. FALLBACK RELATED
         * ====================================================== */
        if ($relatedProducts->count() < 8) {
            $excludeIds = $relatedProducts
                ->pluck('id')
                ->push($product->id);

            $moreProducts = Product::with([
                'mainImage',
                'brand'
            ])
                ->withAvg([
                    'reviews' => function ($q) {
                        $q->where('is_visible', 1);
                    }
                ], 'rating')
                ->withCount([
                    'reviews' => function ($q) {
                        $q->where('is_visible', 1);
                    }
                ])
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
         * 9. RECENT VIEWED PRODUCTS (SESSION)
         * ====================================================== */
        $recentViewed = session()->get('recent_viewed_products', []);

        $recentViewed = array_values(array_diff($recentViewed, [$product->id]));
        array_unshift($recentViewed, $product->id);
        $recentViewed = array_slice($recentViewed, 0, 8);

        session()->put('recent_viewed_products', $recentViewed);

        $recentProducts = collect();

        $recentIdsWithoutCurrent = array_values(array_diff($recentViewed, [$product->id]));

        if (!empty($recentIdsWithoutCurrent)) {
            $recentProducts = Product::with([
                'mainImage',
                'brand'
            ])
                ->withAvg([
                    'reviews' => function ($q) {
                        $q->where('is_visible', 1);
                    }
                ], 'rating')
                ->withCount([
                    'reviews' => function ($q) {
                        $q->where('is_visible', 1);
                    }
                ])
                ->withSum('variants as variants_sold_sum', 'sold_quantity')
                ->where('is_active', 1)
                ->whereIn('id', $recentIdsWithoutCurrent)
                ->whereHas('variants', function ($q) {
                    $q->where('is_active', 1);
                })
                ->get()
                ->sortBy(function ($item) use ($recentIdsWithoutCurrent) {
                    return array_search($item->id, $recentIdsWithoutCurrent);
                })
                ->values();
        }

        /* ======================================================
         * 10. RETURN VIEW
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
            'recentProducts',
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
            ->withAvg([
                'reviews' => function ($q) {
                    $q->where('is_visible', 1);
                }
            ], 'rating')
            ->withCount([
                'reviews' => function ($q) {
                    $q->where('is_visible', 1);
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