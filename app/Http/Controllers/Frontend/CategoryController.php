<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            ->withAvg('reviews', 'rating')   // sao trung bình
            ->withCount('reviews')           // số review
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
        $sort = $request->sort;

        switch ($sort) {

                // Giá thấp → cao
            case 'price_asc':
                $query->orderBy('min_price');
                break;

                // Giá cao → thấp
            case 'price_desc':
                $query->orderByDesc('min_price');
                break;

                // Mới nhất
            case 'newest':
                $query->latest();
                break;

                // Đánh giá cao
            case 'rating':
                $query->orderByDesc('reviews_avg_rating');
                break;

                // Sản phẩm đang giảm giá
            case 'discount':

                $now = Carbon::now();

                $query->where(function ($q) use ($now) {

                    // Giảm trực tiếp trên variant
                    $q->whereHas('variants', function ($sub) {
                        $sub->whereNotNull('original_price')
                            ->whereColumn('original_price', '>', 'price');
                    });

                    // Hoặc có promotion product đang active
                    $q->orWhereHas('promotions', function ($sub) use ($now) {
                        $sub->where('type', 'product')
                            ->where('is_active', 1)
                            ->where('start_date', '<=', $now)
                            ->where('end_date', '>=', $now);
                    });
                });

                // ưu tiên sản phẩm giảm nhiều bán chạy
                $query->orderByDesc('total_sold');

                break;

                // Mặc định: bán chạy
            default:
                $query->orderByDesc('total_sold');
                break;
        }

        /* ==================================================
        | PAGINATE
        ================================================== */
        $limit = $request->limit ?? 20;

        $products = $query
            ->paginate($limit)
            ->withQueryString();

        /* ==================================================
        | SIDEBAR DATA
        ================================================== */
        $allCategories = Category::parents()
            ->with('children')
            ->orderBy('name')
            ->get();

        $brands = Brand::whereHas('products', function ($q) use ($categoryIds) {
            $q->whereIn('category_id', $categoryIds)
                ->where('is_active', true);
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