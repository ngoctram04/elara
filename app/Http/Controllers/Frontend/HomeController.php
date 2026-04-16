<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect()->route('admin.reports.index');
        }

        $now = Carbon::now();

        $reviewStatsSub = DB::table('reviews')
            ->join('order_items', 'reviews.order_item_id', '=', 'order_items.id')
            ->join('product_variants', 'order_items.variant_id', '=', 'product_variants.id')
            ->selectRaw('product_variants.product_id as product_id')
            ->selectRaw('AVG(reviews.rating) as reviews_avg_rating')
            ->selectRaw('COUNT(reviews.id) as reviews_count')
            ->groupBy('product_variants.product_id');

        $soldStatsSub = DB::table('product_variants')
            ->selectRaw('product_variants.product_id as product_id')
            ->selectRaw('SUM(product_variants.sold_quantity) as variants_sold_sum')
            ->groupBy('product_variants.product_id');

        $categories = Category::with('parent')
            ->whereNotNull('parent_id')
            ->whereNotNull('image')
            ->orderBy('name')
            ->get();

        $brands = Brand::whereNotNull('image')
            ->orderBy('name')
            ->get();

        $featuredProducts = Product::with([
            'mainImage',
            'brand',
            'variants',
        ])
            ->leftJoinSub($reviewStatsSub, 'review_stats', function ($join) {
                $join->on('products.id', '=', 'review_stats.product_id');
            })
            ->select('products.*')
            ->selectRaw('COALESCE(review_stats.reviews_avg_rating, 0) as reviews_avg_rating')
            ->selectRaw('COALESCE(review_stats.reviews_count, 0) as reviews_count')
            ->where('products.is_active', true)
            ->where('products.is_featured', true)
            ->latest('products.created_at')
            ->take(8)
            ->get();

        $latestProducts = Product::with([
            'mainImage',
            'brand',
            'variants',
        ])
            ->leftJoinSub($reviewStatsSub, 'review_stats', function ($join) {
                $join->on('products.id', '=', 'review_stats.product_id');
            })
            ->select('products.*')
            ->selectRaw('COALESCE(review_stats.reviews_avg_rating, 0) as reviews_avg_rating')
            ->selectRaw('COALESCE(review_stats.reviews_count, 0) as reviews_count')
            ->where('products.is_active', true)
            ->latest('products.created_at')
            ->take(8)
            ->get();

        $flashSaleProducts = Product::with([
            'mainImage',
            'brand',
            'variants',
            'promotions' => function ($q) use ($now) {
                $q->where('type', 'product')
                    ->where('is_active', true)
                    ->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now);
            },
        ])
            ->leftJoinSub($reviewStatsSub, 'review_stats', function ($join) {
                $join->on('products.id', '=', 'review_stats.product_id');
            })
            ->leftJoinSub($soldStatsSub, 'sold_stats', function ($join) {
                $join->on('products.id', '=', 'sold_stats.product_id');
            })
            ->select('products.*')
            ->selectRaw('COALESCE(review_stats.reviews_avg_rating, 0) as reviews_avg_rating')
            ->selectRaw('COALESCE(review_stats.reviews_count, 0) as reviews_count')
            ->selectRaw('COALESCE(sold_stats.variants_sold_sum, 0) as variants_sold_sum')
            ->where('products.is_active', true)
            ->whereHas('promotions', function ($q) use ($now) {
                $q->where('type', 'product')
                    ->where('is_active', true)
                    ->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now);
            })
            ->orderByDesc('variants_sold_sum')
            ->take(8)
            ->get();

        $flashSaleEndTime = null;

        if ($flashSaleProducts->isNotEmpty()) {
            $allActivePromotions = $flashSaleProducts->flatMap(function ($product) {
                return $product->promotions;
            });

            $flashSaleEndTime = $allActivePromotions->min('end_date');
        }

        $blogs = collect();

        if (class_exists(\App\Models\Blog::class)) {
            $blogs = \App\Models\Blog::with('author')
                ->latest()
                ->take(5)
                ->get();
        }

        return view('frontend.home', compact(
            'categories',
            'brands',
            'featuredProducts',
            'latestProducts',
            'flashSaleProducts',
            'flashSaleEndTime',
            'blogs'
        ));
    }
}