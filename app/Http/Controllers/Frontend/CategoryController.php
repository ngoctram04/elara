<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function show(Request $request, string $slug)
    {
        /* ==================================================
        | CATEGORY HIỆN TẠI
        ================================================== */
        $category = Category::where('slug', $slug)->firstOrFail();

        /* ==================================================
        | CATEGORY IDS
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
        | BASE PRODUCT QUERY (QUAN TRỌNG)
        ================================================== */
        $query = Product::with([
            'variants',
            'mainImage',
            'brand'
        ])
            ->withAvg('reviews', 'rating')   // ⭐ trung bình
            ->withCount('reviews')           // ⭐ số lượt đánh giá
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
        match ($request->sort) {
            'price_asc'  => $query->orderBy('min_price'),
            'price_desc' => $query->orderByDesc('min_price'),
            'newest'     => $query->latest(),
            default      => $query->orderByDesc('total_sold'),
        };

        /* ==================================================
        | PAGINATE
        ================================================== */
        $products = $query->paginate(20)->withQueryString();

        /* ==================================================
        | SIDEBAR DATA
        ================================================== */

        $allCategories = Category::parents()
            ->with('children')
            ->orderBy('name')
            ->get();

        $brands = Brand::whereHas('products', function ($q) use ($categoryIds) {
            $q->whereIn('category_id', $categoryIds);
        })
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