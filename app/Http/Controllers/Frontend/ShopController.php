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
    /* ==================================================
    | MAIN SHOP PAGE
    ================================================== */
    public function index(Request $request)
    {
        $query = Product::with([
            'mainImage',
            'variants',
            'brand'
        ])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('is_active', 1);

        /* ================= SEARCH ================= */
        if ($request->filled('q')) {
            $keyword = trim($request->q);

            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('slug', 'like', "%{$keyword}%")
                    ->orWhereHas('brand', function ($b) use ($keyword) {
                        $b->where('name', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('category', function ($c) use ($keyword) {
                        $c->where('name', 'like', "%{$keyword}%");
                    });
            });

            /* ===== LƯU HISTORY USER (DATABASE) ===== */
            if (Auth::check()) {
                SearchHistory::updateOrCreate(
                    [
                        'user_id' => Auth::id(),
                        'keyword' => $keyword
                    ],
                    []
                );
            }

            /* ===== LƯU HISTORY GUEST (SESSION) ===== */
            $history = session()->get('search_history', []);
            $history = array_diff($history, [$keyword]);
            array_unshift($history, $keyword);
            $history = array_slice($history, 0, 8);
            session(['search_history' => $history]);
        }

        /* ================= FILTER ================= */
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('price')) {
            match ($request->price) {
                '0-500' => $query->whereBetween('min_price', [0, 500000]),
                '500-1000' => $query->whereBetween('min_price', [500000, 1000000]),
                '1000+' => $query->where('min_price', '>=', 1000000),
            };
        }

        if ($request->filled('brands') && is_array($request->brands)) {
            $query->whereIn('brand_id', $request->brands);
        }

        /* ================= SORT ================= */
        switch ($request->sort) {
            case 'price_asc':
                $query->orderBy('min_price');
                break;

            case 'price_desc':
                $query->orderByDesc('min_price');
                break;

            case 'bestseller':
                $query->orderByDesc('total_sold');
                break;

            case 'rating':
                $query->orderByDesc('reviews_avg_rating');
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
                    ->orderByDesc('total_sold');
                break;

            default:
                $query->orderByDesc('created_at');
        }

        /* ================= PAGINATION ================= */
        $products = $query->paginate($request->limit ?? 20)
            ->withQueryString();

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

    /* ==================================================
    | AUTOCOMPLETE
    ================================================== */
    public function suggest(Request $request)
    {
        $keyword = $request->q;

        $products = Product::when($keyword, function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%");
        })
            ->limit(5)
            ->pluck('name');

        $brands = Brand::when($keyword, function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%");
        })
            ->limit(3)
            ->pluck('name');

        return response()->json(
            $products->merge($brands)->unique()->values()
        );
    }

    /* ==================================================
    | GET SEARCH HISTORY
    ================================================== */
    public function history()
    {
        // User login → lấy DB
        if (Auth::check()) {
            return response()->json(
                SearchHistory::where('user_id', Auth::id())
                    ->latest()
                    ->limit(8)
                    ->pluck('keyword')
            );
        }

        // Guest → session
        return response()->json(
            session()->get('search_history', [])
        );
    }

    /* ==================================================
    | DELETE 1 HISTORY
    ================================================== */
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
