<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        /* =====================================================
            🔒 Nếu là ADMIN → chuyển về admin dashboard
        ===================================================== */
        if (Auth::check() && Auth::user()->role === 'admin') {
            // Route admin chính của bạn
            return redirect()->route('admin.reports.index');
        }

        $now = Carbon::now();

        /* ===============================
            DANH MỤC
        =============================== */
        $categories = Category::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        /* ===============================
            ⭐ SẢN PHẨM NỔI BẬT
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
            🆕 SẢN PHẨM MỚI
        =============================== */
        $latestProducts = Product::with(['mainImage', 'brand'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        /* ===============================
            🔥 FLASH SALE
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
            VIEW
        =============================== */
        return view('frontend.home', compact(
            'categories',
            'featuredProducts',
            'latestProducts',
            'flashSaleProducts'
        ));
    }
}