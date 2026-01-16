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
            ->where('is_active', true)
            ->where('is_featured', true)
            ->latest()
            ->take(8)
            ->get();

        /* ===============================
            SẢN PHẨM MỚI
        =============================== */
        $latestProducts = Product::with('mainImage')
            ->where('is_active', true)
            ->latest()
            ->take(8)
            ->get();

        /* ===============================
            🔥 FLASH SALE
            - Sản phẩm CÓ promotion theo sản phẩm
            - Promotion đang active
            - Trong thời gian hiệu lực
            - Không cần cột is_flash_sale
        =============================== */
        $flashSaleProducts = Product::with([
            'mainImage',
            'brand',
            'variants',
            'promotions' => function ($q) {
                $q->where('type', 'product')
                    ->where('is_active', true)
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
            }
        ])
            ->where('is_active', true)
            ->whereHas('promotions', function ($q) {
                $q->where('type', 'product')
                    ->where('is_active', true)
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
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