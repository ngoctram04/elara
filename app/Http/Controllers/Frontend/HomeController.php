<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        /* =====================================================
            🔒 ADMIN → dashboard
        ===================================================== */
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect()->route('admin.reports.index');
        }

        $now = Carbon::now();

        /* ===============================
            DANH MỤC NHỎ (CÓ ẢNH)
        =============================== */
        $categories = Category::with('parent')
            ->whereNotNull('parent_id')
            ->whereNotNull('image')
            ->orderBy('name')
            ->get();

        /* ===============================
            THƯƠNG HIỆU
        =============================== */
        $brands = Brand::whereNotNull('image')
            ->orderBy('name')
            ->get();

        /* ===============================
            SẢN PHẨM NỔI BẬT
        =============================== */
        $featuredProducts = Product::with(['mainImage', 'brand'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('is_active', true)
            ->where('is_featured', true)
            ->latest()
            ->take(8)
            ->get();

        /* ===============================
            SẢN PHẨM MỚI
        =============================== */
        $latestProducts = Product::with(['mainImage', 'brand'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('is_active', true)
            ->latest()
            ->take(8)
            ->get();

        /* ===============================
            FLASH SALE
        =============================== */
        $flashSaleProducts = Product::with([
            'mainImage',
            'brand',
            'variants',
            'promotions'
        ])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('is_active', true)
            ->whereHas('promotions', function ($q) use ($now) {
                $q->where('type', 'product')
                    ->where('is_active', true)
                    ->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now);
            })
            ->orderByDesc('total_sold')
            ->take(8)
            ->get();

        /* ===============================
            BLOG (AN TOÀN - KHÔNG LỖI)
        =============================== */
        $blogs = collect();

        if (class_exists(\App\Models\Blog::class)) {
            $blogs = \App\Models\Blog::latest()->take(5)->get();
        }

        /* ===============================
            VIEW
        =============================== */
        return view('frontend.home', compact(
            'categories',
            'brands',
            'featuredProducts',
            'latestProducts',
            'flashSaleProducts',
            'blogs'
        ));
    }
}