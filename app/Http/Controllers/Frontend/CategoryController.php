<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;
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
        | CATEGORY IDS (cha → lấy cả con)
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
        | BASE PRODUCT QUERY
        ================================================== */
        $query = Product::with([
            'variants',
            'mainImage',
            'brand'
        ])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->whereIn('category_id', $categoryIds)
            ->where('is_active', true);

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
        switch ($request->sort) {

            case 'price_asc':
                $query->orderBy('min_price');
                break;

            case 'price_desc':
                $query->orderByDesc('min_price');
                break;

            case 'newest':
                $query->latest();
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
                    });

                    $q->orWhereHas('promotions', function ($sub) use ($now) {
                        $sub->where('type', 'product')
                            ->where('is_active', 1)
                            ->where('start_date', '<=', $now)
                            ->where('end_date', '>=', $now);
                    });
                });

                $query->orderByDesc('total_sold');
                break;

            default:
                $query->orderByDesc('total_sold');
                break;
        }

        /* ==================================================
        | 🔥 BRAND DYNAMIC (QUAN TRỌNG NHẤT)
        ================================================== */
        $filteredQuery = clone $query;

        $brands = Brand::whereIn('id', function ($q) use ($filteredQuery) {
            $q->select('brand_id')
                ->fromSub(
                    $filteredQuery->select('brand_id'),
                    'filtered_products'
                );
        })
            ->orderBy('name')
            ->get();

        /* ==================================================
        | PAGINATE (SAU KHI LẤY BRAND)
        ================================================== */
        $limit = $request->limit ?? 20;

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