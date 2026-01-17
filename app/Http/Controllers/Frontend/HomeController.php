<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        $now = Carbon::now(); // ✅ cố định thời điểm cho toàn bộ request

        /* ===============================
            DANH MỤC (MENU / MEGA MENU)
        =============================== */
        $categories = Category::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        /* ===============================
            ⭐ SẢN PHẨM NỔI BẬT
        =============================== */
        $featuredProducts = Product::with('mainImage')
            ->where('is_active', true)
            ->where('is_featured', true)
            ->latest()
            ->take(8)
            ->get();

        /* ===============================
            🆕 SẢN PHẨM MỚI
        =============================== */
        $latestProducts = Product::with('mainImage')
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        /* ===============================
            🔥 FLASH SALE (FIXED)
            - Chỉ filter ở whereHas
            - with() chỉ để load quan hệ
            - Dùng $now để tránh lệch giây
        =============================== */
        $flashSaleProducts = Product::with([
            'mainImage',
            'brand',
            'variants',
            'promotions'
        ])
            ->where('is_active', true)
            ->whereHas('promotions', function ($q) use ($now) {
                $q->where('type', 'product') // hoặc 'flash_sale' nếu bạn tách riêng
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