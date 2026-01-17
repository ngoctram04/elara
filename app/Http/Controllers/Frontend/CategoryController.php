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
        | CATEGORY HIỆN TẠI (DÙNG SLUG – KHÔNG DÙNG ID)
        ================================================== */
        $category = Category::where('slug', $slug)->firstOrFail();

        /* ==================================================
        | CATEGORY IDS DÙNG CHO QUERY
        | - CHA  → lấy cha + toàn bộ con
        | - CON  → chỉ lấy chính nó
        ================================================== */
        if ($category->parent_id) {
            // Category con
            $categoryIds = [$category->id];
        } else {
            // Category cha
            $categoryIds = $category->children()
                ->pluck('id')
                ->push($category->id)
                ->toArray();
        }

        /* ==================================================
        | BASE PRODUCT QUERY
        ================================================== */
        $query = Product::whereIn('category_id', $categoryIds);

        /* ==================================================
        | PRICE FILTER
        ================================================== */
        if ($request->filled('price')) {
            match ($request->price) {
                '0-500' =>
                $query->whereBetween('min_price', [0, 500_000]),

                '500-1000' =>
                $query->whereBetween('min_price', [500_000, 1_000_000]),

                '1000+' =>
                $query->where('min_price', '>=', 1_000_000),

                default => null,
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
            'price_asc'  => $query->orderBy('min_price', 'asc'),
            'price_desc' => $query->orderBy('min_price', 'desc'),
            'newest'     => $query->orderByDesc('created_at'),
            default      => $query->orderByDesc('total_sold'), // 🔥 bán chạy
        };

        /* ==================================================
        | PAGINATE
        ================================================== */
        $products = $query
            ->paginate(20)
            ->withQueryString();

        /* ==================================================
        | SIDEBAR DATA
        ================================================== */

        // Category accordion (cha + con)
        $allCategories = Category::parents()
            ->with('children')
            ->orderBy('name')
            ->get();

        // 🔥 Brand chỉ lấy những brand CÓ sản phẩm trong category hiện tại
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