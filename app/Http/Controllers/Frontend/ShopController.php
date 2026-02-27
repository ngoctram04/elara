<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        /* ==================================================
        | BASE QUERY
        ================================================== */
        $query = Product::with([
            'mainImage',
            'variants',
            'brand'
        ])
            ->withAvg('reviews', 'rating')   // ⭐ trung bình
            ->withCount('reviews')           // ⭐ số lượt đánh giá
            ->where('is_active', 1);

        /* ==================================================
        | SEARCH
        ================================================== */
        if ($request->filled('q')) {
            $keyword = $request->q;

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
        }

        /* ==================================================
        | CATEGORY FILTER
        ================================================== */
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        /* ==================================================
        | PRICE FILTER
        ================================================== */
        if ($request->filled('price')) {
            match ($request->price) {
                '0-500' =>
                $query->whereBetween('min_price', [0, 500000]),

                '500-1000' =>
                $query->whereBetween('min_price', [500000, 1000000]),

                '1000+' =>
                $query->where('min_price', '>=', 1000000),
            };
        }

        /* ==================================================
        | BRAND FILTER
        ================================================== */
        if ($request->filled('brands') && is_array($request->brands)) {
            $query->whereIn('brand_id', $request->brands);
        }

        /* ==================================================
        | SORT
        ================================================== */
        $sort = $request->sort;

        switch ($sort) {

                // Giá thấp → cao
            case 'price_asc':
                $query->orderBy('min_price', 'asc');
                break;

                // Giá cao → thấp
            case 'price_desc':
                $query->orderBy('min_price', 'desc');
                break;

                // Bán chạy
            case 'bestseller':
                $query->orderByDesc('total_sold');
                break;

                // Đánh giá cao
            case 'rating':
                $query->orderByDesc('reviews_avg_rating');
                break;

                // Đang giảm giá
            case 'discount':

                $now = Carbon::now();

                $query->where(function ($q) use ($now) {

                    // Giảm trực tiếp trên variant
                    $q->whereHas('variants', function ($sub) {
                        $sub->whereNotNull('original_price')
                            ->whereColumn('original_price', '>', 'price');
                    });

                    // Hoặc có promotion product
                    $q->orWhereHas('promotions', function ($sub) use ($now) {
                        $sub->where('type', 'product')
                            ->where('is_active', 1)
                            ->where('start_date', '<=', $now)
                            ->where('end_date', '>=', $now);
                    });
                });

                // sắp theo bán chạy trong nhóm giảm giá
                $query->orderByDesc('total_sold');
                break;

                // Mặc định: Mới nhất
            default:
                $query->orderByDesc('created_at');
        }

        /* ==================================================
        | PAGINATION
        ================================================== */
        $limit = $request->limit ?? 20;

        $products = $query
            ->paginate($limit)
            ->withQueryString();

        /* ==================================================
        | SIDEBAR DATA
        ================================================== */
        $categories = Category::whereNull('parent_id')
            ->with('children')
            ->orderBy('name')
            ->get();

        $brands = Brand::whereHas('products', function ($q) {
            $q->where('is_active', 1);
        })
            ->orderBy('name')
            ->get();

        /* ==================================================
        | VIEW
        ================================================== */
        return view('frontend.shop.index', compact(
            'products',
            'brands',
            'categories'
        ));
    }
}