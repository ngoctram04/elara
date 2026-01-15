<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        /* ===============================
            DANH MỤC (MENU)
        =============================== */
        $categories = Category::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        /* ===============================
            SẢN PHẨM NỔI BẬT
        =============================== */
        $featuredProducts = Product::with('mainImage')
            ->where('is_active', 1)
            ->where('is_featured', 1)
            ->latest()
            ->take(8)
            ->get();

        /* ===============================
            SẢN PHẨM MỚI
        =============================== */
        $latestProducts = Product::with('mainImage')
            ->where('is_active', 1)
            ->latest()
            ->take(8)
            ->get();

        /* ===============================
            🔥 FLASH SALE (CHUẨN LOGIC)
            - Có promotion type = flash_sale
            - Promotion đang active
            - Còn trong thời gian hiệu lực
            - Còn hàng
        =============================== */
        $flashSaleProducts = Product::where('is_active', 1)
            ->where('total_stock', '>', 0)
            ->whereHas('promotions', function ($q) {
                $q->where('type', 'flash_sale')
                    ->where('is_active', 1)
                    ->where('start_at', '<=', now())
                    ->where('end_at', '>=', now());
            })
            ->with([
                'mainImage',
                'promotions' => function ($q) {
                    $q->where('type', 'flash_sale')
                        ->where('is_active', 1)
                        ->where('start_at', '<=', now())
                        ->where('end_at', '>=', now());
                }
            ])
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