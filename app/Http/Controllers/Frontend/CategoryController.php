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
        $category = Category::where('slug', $slug)->firstOrFail();

        if ($category->parent_id) {
            $categoryIds = [$category->id];
        } else {
            $categoryIds = $category->children()
                ->pluck('id')
                ->push($category->id)
                ->toArray();
        }

        $baseQuery = Product::with([
            'variants',
            'mainImage',
            'brand',
        ])
            ->whereIn('category_id', $categoryIds)
            ->where('is_active', true)
            ->select('products.*')

            ->selectSub(function ($q) {
                $q->from('product_variants')
                    ->whereColumn('product_variants.product_id', 'products.id')
                    ->selectRaw('COALESCE(SUM(product_variants.sold_quantity), 0)');
            }, 'variants_total_sold')

            ->selectSub(function ($q) {
                $q->from('reviews')
                    ->join('order_items', 'order_items.id', '=', 'reviews.order_item_id')
                    ->join('product_variants', 'product_variants.id', '=', 'order_items.variant_id')
                    ->whereColumn('product_variants.product_id', 'products.id')
                    ->where('reviews.is_visible', 1)
                    ->selectRaw('COALESCE(AVG(reviews.rating), 0)');
            }, 'reviews_avg_rating')

            ->selectSub(function ($q) {
                $q->from('reviews')
                    ->join('order_items', 'order_items.id', '=', 'reviews.order_item_id')
                    ->join('product_variants', 'product_variants.id', '=', 'order_items.variant_id')
                    ->whereColumn('product_variants.product_id', 'products.id')
                    ->where('reviews.is_visible', 1)
                    ->selectRaw('COUNT(reviews.id)');
            }, 'reviews_count');

        if ($request->filled('price')) {
            match ($request->price) {
                '0-100'   => $baseQuery->whereBetween('min_price', [0, 100000]),
                '100-200' => $baseQuery->where('min_price', '>', 100000)
                    ->where('min_price', '<=', 200000),
                '200-300' => $baseQuery->where('min_price', '>', 200000)
                    ->where('min_price', '<=', 300000),
                '300+'    => $baseQuery->where('min_price', '>', 300000),
                default   => null,
            };
        }
        if ($request->filled('brands') && is_array($request->brands)) {
            $baseQuery->whereIn('brand_id', $request->brands);
        }

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

                $query->whereHas('promotions', function ($sub) use ($now) {
                    $sub->where('type', 'product')
                        ->where('is_active', 1)
                        ->where('start_date', '<=', $now)
                        ->where('end_date', '>=', $now);
                });

                $query->orderByDesc('variants_total_sold')
                    ->orderByDesc('id');
                break;

            default:
                $query->orderByDesc('variants_total_sold')
                    ->orderByDesc('id');
                break;
        }

        $allowedLimits = [9, 18, 36];
        $limit = (int) $request->get('limit', 9);

        if (!in_array($limit, $allowedLimits)) {
            $limit = 9;
        }

        $products = $query
            ->paginate($limit)
            ->withQueryString();

      
        $allCategories = Category::parents()
            ->with('children')
            ->orderBy('name')
            ->get();
        return view('frontend.category.show', compact(
            'category',
            'products',
            'allCategories',
            'brands'
        ));
    }
}