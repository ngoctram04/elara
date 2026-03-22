<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CategoryController extends Controller
{
    public function show(Request $request, string $slug)
    {
        /* ==================================================
        | CATEGORY HIỆN TẠI
        ================================================== */
        $category = Category::where('slug', $slug)->firstOrFail();

        /* ==================================================
        | CATEGORY IDS (cha -> lấy cả con)
        ================================================== */
        if ($category->parent_id) {
            $categoryIds = [$category->id];
        } else {
            $categoryIds = $category->children()
                ->pluck('id')
                ->push($category->id)
                ->toArray();
        }

        /* ==================================================
        | BASE QUERY: CHỈ FILTER, CHƯA SORT
        ================================================== */
        $baseQuery = Product::with([
            'variants',
            'mainImage',
            'brand',
        ])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->withSum('variants as variants_total_sold', 'sold_quantity')
            ->whereIn('category_id', $categoryIds)
            ->where('is_active', true);

        /* ==================================================
        | PRICE FILTER
        ================================================== */
        if ($request->filled('price')) {
            match ($request->price) {
                '0-500'    => $baseQuery->whereBetween('min_price', [0, 500000]),
                '500-1000' => $baseQuery->whereBetween('min_price', [500000, 1000000]),
                '1000+'    => $baseQuery->where('min_price', '>=', 1000000),
                default    => null,
            };
        }

        /* ==================================================
        | BRAND FILTER
        ================================================== */
        if ($request->filled('brands') && is_array($request->brands)) {
            $baseQuery->whereIn('brand_id', $request->brands);
        }

        /* ==================================================
        | LẤY BRAND DYNAMIC TỪ QUERY CHƯA SORT
        ================================================== */
        $filteredQuery = clone $baseQuery;

        $brands = Brand::whereIn('id', function ($q) use ($filteredQuery) {
            $q->select('brand_id')
                ->fromSub(
                    $filteredQuery->select('brand_id')->distinct(),
                    'filtered_products'
                );
        })
            ->orderBy('name')
            ->get();

        /* ==================================================
        | QUERY CHÍNH: CLONE RA RỒI MỚI SORT
        ================================================== */
        $query = clone $baseQuery;

        switch ($request->sort) {
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
                    });

                    $q->orWhereHas('promotions', function ($sub) use ($now) {
                        $sub->where('type', 'product')
                            ->where('is_active', 1)
                            ->where('start_date', '<=', $now)
                            ->where('end_date', '>=', $now);
                    });
                });

                $query->orderByDesc('variants_total_sold')
                    ->orderByDesc('id');
                break;

            default:
                $query->orderByDesc('variants_total_sold')
                    ->orderByDesc('id');
                break;
        }

        /* ==================================================
        | PAGINATE
        ================================================== */
        $limit = (int) $request->get('limit', 20);

        $products = $query
            ->paginate($limit)
            ->withQueryString();

        /* ==================================================
        | SIDEBAR CATEGORY
        ================================================== */
        $allCategories = Category::parents()
            ->with('children')
            ->orderBy('name')
            ->get();

        /* ==================================================
        | VIEW
        ================================================== */
        return view('frontend.category.show', compact(
            'category',
            'products',
            'allCategories',
            'brands'
        ));
    }
}