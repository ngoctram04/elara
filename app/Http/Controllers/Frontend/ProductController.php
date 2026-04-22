<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function show(string $slug, Request $request)
    {
        $userId = Auth::id();

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

        $product = Product::with([
            'images',
            'mainImage',

            'variants' => function ($q) {
                $q->orderBy('id')
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
        $visibleReviewsQuery = Review::query()
            ->join('order_items', 'order_items.id', '=', 'reviews.order_item_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.variant_id')
            ->where('product_variants.product_id', $product->id)
            ->where('reviews.is_visible', 1)
            ->select('reviews.*');

        $reviewsQuery = (clone $visibleReviewsQuery)
            ->with([
                'orderItem.order.user',
                'media'
            ]);

        if ($request->filled('rating') && $request->rating !== 'all') {
            $reviewsQuery->where('reviews.rating', $request->rating);
        }

        if ($request->type === 'comment') {
            $reviewsQuery->whereNotNull('reviews.comment')
                ->where('reviews.comment', '!=', '');
        }

        if ($request->type === 'media') {
            $reviewsQuery->whereHas('media');
        }

        if ($request->sort === 'old') {
            $reviewsQuery->orderBy('reviews.created_at', 'asc');
        } else {
            $reviewsQuery->orderBy('reviews.created_at', 'desc');
        }

        $reviews = $reviewsQuery->get();
        $reviewCount = (clone $visibleReviewsQuery)->count();

        $avgRating = $reviewCount > 0
            ? round((clone $visibleReviewsQuery)->avg('reviews.rating'), 1)
            : 0;

        $ratingStats = [];

        for ($i = 1; $i <= 5; $i++) {
            $ratingStats[$i] = (clone $visibleReviewsQuery)
                ->where('reviews.rating', $i)
                ->count();
        }

        $withComment = (clone $visibleReviewsQuery)
            ->whereNotNull('reviews.comment')
            ->where('reviews.comment', '!=', '')
            ->count();

        $withMedia = (clone $visibleReviewsQuery)
            ->whereHas('media')
            ->count();

        $totalSold = $product->total_sold;
        $favorites = [];

        if ($userId) {
            $favorites = Wishlist::where('user_id', $userId)
                ->pluck('product_id')
                ->toArray();
        }

        $favoritesCount = Wishlist::where('product_id', $product->id)->count();
        $relatedProducts = Product::query()
            ->with([
                'mainImage',
                'brand'
            ])
            ->leftJoinSub($reviewStats, 'review_stats', function ($join) {
                $join->on('products.id', '=', 'review_stats.product_id');
            })
            ->select('products.*')
            ->selectRaw('COALESCE(review_stats.reviews_avg_rating, 0) as reviews_avg_rating')
            ->selectRaw('COALESCE(review_stats.reviews_count, 0) as reviews_count')
            ->withSum('variants as variants_sold_sum', 'sold_quantity')
            ->where('products.is_active', 1)
            ->where('products.id', '!=', $product->id)
            ->where('products.category_id', $product->category_id)
            ->whereHas('variants', function ($q) {
                $q->where('stock_quantity', '>', 0)
                    ->where('is_active', 1);
            })
            ->orderByRaw("products.brand_id = ? DESC", [$product->brand_id])
            ->orderByDesc('variants_sold_sum')
            ->limit(8)
            ->get();
        if ($relatedProducts->count() < 8) {
            $excludeIds = $relatedProducts
                ->pluck('id')
                ->push($product->id);

            $moreProducts = Product::query()
                ->with([
                    'mainImage',
                    'brand'
                ])
                ->leftJoinSub($reviewStats, 'review_stats', function ($join) {
                    $join->on('products.id', '=', 'review_stats.product_id');
                })
                ->select('products.*')
                ->selectRaw('COALESCE(review_stats.reviews_avg_rating, 0) as reviews_avg_rating')
                ->selectRaw('COALESCE(review_stats.reviews_count, 0) as reviews_count')
                ->where('products.is_active', 1)
                ->where('products.category_id', $product->category_id)
                ->whereNotIn('products.id', $excludeIds)
                ->whereHas('variants', function ($q) {
                    $q->where('stock_quantity', '>', 0)
                        ->where('is_active', 1);
                })
                ->inRandomOrder()
                ->limit(8 - $relatedProducts->count())
                ->get();

            $relatedProducts = $relatedProducts->merge($moreProducts);
        }

        $recentViewed = session()->get('recent_viewed_products', []);

        $recentViewed = array_values(array_diff($recentViewed, [$product->id]));
        array_unshift($recentViewed, $product->id);
        $recentViewed = array_slice($recentViewed, 0, 8);

        session()->put('recent_viewed_products', $recentViewed);

        $recentProducts = collect();

        $recentIdsWithoutCurrent = array_values(array_diff($recentViewed, [$product->id]));

        if (!empty($recentIdsWithoutCurrent)) {
            $recentProducts = Product::query()
                ->with([
                    'mainImage',
                    'brand'
                ])
                ->leftJoinSub($reviewStats, 'review_stats', function ($join) {
                    $join->on('products.id', '=', 'review_stats.product_id');
                })
                ->select('products.*')
                ->selectRaw('COALESCE(review_stats.reviews_avg_rating, 0) as reviews_avg_rating')
                ->selectRaw('COALESCE(review_stats.reviews_count, 0) as reviews_count')
                ->withSum('variants as variants_sold_sum', 'sold_quantity')
                ->where('products.is_active', 1)
                ->whereIn('products.id', $recentIdsWithoutCurrent)
                ->whereHas('variants', function ($q) {
                    $q->where('is_active', 1);
                })
                ->get()
                ->sortBy(function ($item) use ($recentIdsWithoutCurrent) {
                    return array_search($item->id, $recentIdsWithoutCurrent);
                })
                ->values();
        }
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

        $product = Product::query()
            ->with([
                'images',
                'mainImage',
                'variants' => function ($q) {
                    $q->orderBy('id')
                        ->with('images');
                },
                'category',
                'brand',
                'promotions' => function ($q) {
                    $q->where('is_active', 1);
                }
            ])
            ->leftJoinSub($reviewStats, 'review_stats', function ($join) {
                $join->on('products.id', '=', 'review_stats.product_id');
            })
            ->select('products.*')
            ->selectRaw('COALESCE(review_stats.reviews_avg_rating, 0) as reviews_avg_rating')
            ->selectRaw('COALESCE(review_stats.reviews_count, 0) as reviews_count')
            ->where('products.id', $id)
            ->where('products.is_active', 1)
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data'   => $product
        ]);
    }
}