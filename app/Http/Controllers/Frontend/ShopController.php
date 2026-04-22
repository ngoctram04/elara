<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\SearchHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ShopController extends Controller
{
    public function index(Request $request)
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

        $baseQuery = Product::query()
            ->with([
                'mainImage',
                'variants',
                'brand',
                'category'
            ])
            ->select('products.*')
            ->withSum('variants as variants_total_sold', 'sold_quantity')
            ->leftJoinSub($reviewStats, 'review_stats', function ($join) {
                $join->on('products.id', '=', 'review_stats.product_id');
            })
            ->selectRaw('COALESCE(review_stats.reviews_avg_rating, 0) as reviews_avg_rating')
            ->selectRaw('COALESCE(review_stats.reviews_count, 0) as reviews_count')
            ->where('products.is_active', 1);

        if ($request->filled('q')) {
            $keyword = trim($request->q);

            $baseQuery->where(function ($q) use ($keyword) {
                $q->where('products.name', 'like', "%{$keyword}%")
                    ->orWhere('products.slug', 'like', "%{$keyword}%")
                    ->orWhereHas('brand', function ($b) use ($keyword) {
                        $b->where('name', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('category', function ($c) use ($keyword) {
                        $c->where('name', 'like', "%{$keyword}%")
                            ->orWhereHas('parent', function ($parent) use ($keyword) {
                                $parent->where('name', 'like', "%{$keyword}%");
                            });
                    });
            });

            if (Auth::check()) {
                SearchHistory::updateOrCreate(
                    [
                        'user_id' => Auth::id(),
                        'keyword' => $keyword
                    ],
                    []
                );
            }

            $history = session()->get('search_history', []);
            $history = array_diff($history, [$keyword]);
            array_unshift($history, $keyword);
            $history = array_slice($history, 0, 8);
            session(['search_history' => $history]);
        }
        if ($request->filled('category')) {
            $categoryId = (int) $request->category;

            $selectedCategory = Category::with('children')->find($categoryId);

            if ($selectedCategory) {
                $categoryIds = [$selectedCategory->id];

                if ($selectedCategory->children->isNotEmpty()) {
                    $categoryIds = array_merge(
                        $categoryIds,
                        $selectedCategory->children->pluck('id')->toArray()
                    );
                }

                $baseQuery->whereIn('products.category_id', $categoryIds);
            }
        }

        if ($request->filled('price')) {
            match ($request->price) {
                '0-100'   => $baseQuery->whereBetween('products.min_price', [0, 100000]),
                '100-200' => $baseQuery->where('products.min_price', '>', 100000)->where('products.min_price', '<=', 200000),
                '200-300' => $baseQuery->where('products.min_price', '>', 200000)->where('products.min_price', '<=', 300000),
                '300+'    => $baseQuery->where('products.min_price', '>', 300000),
                default   => null,
            };
        }

        if ($request->filled('brand')) {
            $baseQuery->where('products.brand_id', $request->brand);
        }

        if ($request->filled('brands') && is_array($request->brands)) {
            $brandIds = array_filter($request->brands, fn($id) => !empty($id));
            if (!empty($brandIds)) {
                $baseQuery->whereIn('products.brand_id', $brandIds);
            }
        }

        $query = clone $baseQuery;

        switch ($request->sort) {
            case 'bestseller':
            case 'best_selling':
                $query->orderByDesc('variants_total_sold')
                    ->orderByDesc('products.id');
                break;

            case 'price_asc':
                $query->orderBy('products.min_price')
                    ->orderByDesc('products.id');
                break;

            case 'price_desc':
                $query->orderByDesc('products.min_price')
                    ->orderByDesc('products.id');
                break;

            case 'newest':
                $query->orderByDesc('products.created_at')
                    ->orderByDesc('products.id');
                break;

            case 'rating':
                $query->orderByDesc('reviews_avg_rating')
                    ->orderByDesc('products.id');
                break;

            case 'discount':
                $now = Carbon::now();

                $query->whereHas('promotions', function ($sub) use ($now) {
                    $sub->where('type', 'product')
                        ->where('is_active', 1)
                        ->where('start_date', '<=', $now)
                        ->where('end_date', '>=', $now);
                })
                    ->orderByDesc('variants_total_sold')
                    ->orderByDesc('products.id');
                break;

            default:
                $query->orderByDesc('products.created_at')
                    ->orderByDesc('products.id');
                break;
        }

        $allowedLimits = [9, 18, 36];
        $limit = (int) $request->get('limit', 9);

        if (!in_array($limit, $allowedLimits)) {
            $limit = 9;
        }

        $products = $query->paginate($limit)->withQueryString();

        $categories = Category::whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get();

        $brandsQuery = Brand::query()
            ->whereHas('products', function ($q) use ($request) {
                $q->where('products.is_active', 1);

                if ($request->filled('q')) {
                    $keyword = trim($request->q);

                    $q->where(function ($sub) use ($keyword) {
                        $sub->where('products.name', 'like', "%{$keyword}%")
                            ->orWhere('products.slug', 'like', "%{$keyword}%")
                            ->orWhereHas('category', function ($c) use ($keyword) {
                                $c->where('name', 'like', "%{$keyword}%")
                                    ->orWhereHas('parent', function ($parent) use ($keyword) {
                                        $parent->where('name', 'like', "%{$keyword}%");
                                    });
                            });
                    });
                }

                if ($request->filled('category')) {
                    $categoryId = (int) $request->category;

                    $selectedCategory = Category::with('children')->find($categoryId);

                    if ($selectedCategory) {
                        $categoryIds = [$selectedCategory->id];

                        if ($selectedCategory->children->isNotEmpty()) {
                            $categoryIds = array_merge(
                                $categoryIds,
                                $selectedCategory->children->pluck('id')->toArray()
                            );
                        }

                        $q->whereIn('products.category_id', $categoryIds);
                    }
                }

                if ($request->filled('price')) {
                    match ($request->price) {
                        '0-100'   => $q->whereBetween('products.min_price', [0, 100000]),
                        '100-200' => $q->where('products.min_price', '>', 100000)->where('products.min_price', '<=', 200000),
                        '200-300' => $q->where('products.min_price', '>', 200000)->where('products.min_price', '<=', 300000),
                        '300+'    => $q->where('products.min_price', '>', 300000),
                        default   => null,
                    };
                }
            })
            ->orderBy('name');

        $brands = $brandsQuery->get();

        $popularKeywords = SearchHistory::select('keyword', DB::raw('COUNT(*) as total'))
            ->groupBy('keyword')
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('keyword');

        return view('frontend.shop.index', compact(
            'products',
            'brands',
            'categories',
            'popularKeywords'
        ));
    }

    public function suggest(Request $request)
    {
        $keyword = trim($request->q);

        if (!$keyword) {
            return response()->json([]);
        }

        $products = Product::where('name', 'like', "%{$keyword}%")
            ->limit(5)
            ->pluck('name');

        $brands = Brand::where('name', 'like', "%{$keyword}%")
            ->limit(3)
            ->pluck('name');

        $categories = Category::where('name', 'like', "%{$keyword}%")
            ->limit(5)
            ->pluck('name');

        return response()->json(
            $products
                ->merge($brands)
                ->merge($categories)
                ->unique()
                ->values()
        );
    }

    public function history()
    {
        if (Auth::check()) {
            return response()->json(
                SearchHistory::where('user_id', Auth::id())
                    ->latest()
                    ->limit(8)
                    ->pluck('keyword')
            );
        }

        return response()->json(
            session()->get('search_history', [])
        );
    }

    public function deleteHistory(Request $request)
    {
        $keyword = $request->keyword;

        if (Auth::check()) {
            SearchHistory::where('user_id', Auth::id())
                ->where('keyword', $keyword)
                ->delete();
        }

        $history = session()->get('search_history', []);
        $history = array_values(array_diff($history, [$keyword]));
        session(['search_history' => $history]);

        return response()->json(['success' => true]);
    }
}