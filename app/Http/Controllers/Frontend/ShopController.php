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
        /* ==================================================
        | BASE QUERY: CHỈ FILTER, CHƯA SORT
        ================================================== */
        $baseQuery = Product::with([
            'mainImage',
            'variants',
            'brand',
            'category'
        ])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->withSum('variants as variants_total_sold', 'sold_quantity')
            ->where('is_active', 1);

        /* ================= SEARCH ================= */
        if ($request->filled('q')) {
            $keyword = trim($request->q);

            $baseQuery->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%")

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

        /* ================= FILTER ================= */
        if ($request->filled('category')) {
            $baseQuery->where('category_id', $request->category);
        }

        if ($request->filled('price')) {
            match ($request->price) {
                '0-100'   => $baseQuery->whereBetween('min_price', [0, 100000]),
                '100-200' => $baseQuery->where('min_price', '>', 100000)->where('min_price', '<=', 200000),
                '200-300' => $baseQuery->where('min_price', '>', 200000)->where('min_price', '<=', 300000),
                '300+'    => $baseQuery->where('min_price', '>', 300000),
                default   => null,
            };
        }

        if ($request->filled('brands') && is_array($request->brands)) {
            $baseQuery->whereIn('brand_id', $request->brands);
        }

        /* ==================================================
        | QUERY CHÍNH: CLONE RA RỒI MỚI SORT
        ================================================== */
        $query = clone $baseQuery;

        switch ($request->sort) {
            case 'bestseller':
            case 'best_selling':
                $query->orderByDesc('variants_total_sold')
                    ->orderByDesc('id');
                break;

            case 'price_asc':
                $query->orderBy('min_price')
                    ->orderByDesc('id');
                break;

            case 'price_desc':
                $query->orderByDesc('min_price')
                    ->orderByDesc('id');
                break;

            case 'newest':
                $query->orderByDesc('created_at')
                    ->orderByDesc('id');
                break;

            case 'rating':
                $query->orderByDesc('reviews_avg_rating')
                    ->orderByDesc('id');
                break;

            case 'discount':
                $now = Carbon::now();

                $query->where(function ($q) use ($now) {
                    $q->whereHas('variants', function ($sub) {
                        $sub->whereNotNull('original_price')
                            ->whereColumn('original_price', '>', 'price');
                    })
                        ->orWhereHas('promotions', function ($sub) use ($now) {
                            $sub->where('type', 'product')
                                ->where('is_active', 1)
                                ->where('start_date', '<=', $now)
                                ->where('end_date', '>=', $now);
                        });
                })
                    ->orderByDesc('variants_total_sold')
                    ->orderByDesc('id');
                break;

            default:
                $query->orderByDesc('created_at')
                    ->orderByDesc('id');
                break;
        }

        /* ================= PAGINATION ================= */
        $allowedLimits = [9, 18, 36];
        $limit = (int) $request->get('limit', 9);

        if (!in_array($limit, $allowedLimits)) {
            $limit = 9;
        }

        $products = $query->paginate($limit)->withQueryString();

        /* ================= SIDEBAR ================= */
        $categories = Category::whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get();

        $brands = Brand::whereHas('products', function ($q) {
            $q->where('is_active', 1);
        })
            ->orderBy('name')
            ->get();

        /* ================= POPULAR KEYWORDS ================= */
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