<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ShopController extends Controller
{
    public function index()
    {
        // 🔥 Flash Sale (tạm thời lấy sản phẩm mới)
        $flashSaleProducts = Product::where('is_active', 1)
            ->latest()
            ->take(4)
            ->get();

        // ⭐ Sản phẩm nổi bật
        $featuredProducts = Product::where('is_active', 1)
            ->where('is_featured', 1)
            ->latest()
            ->take(4)
            ->get();

        // 🆕 Sản phẩm mới
        $latestProducts = Product::where('is_active', 1)
            ->latest()
            ->take(4)
            ->get();

        // 🔥🔥 SẢN PHẨM BÁN CHẠY (DÙNG CỘT total_sold)
        $bestSellerProducts = Product::where('is_active', 1)
            ->orderByDesc('total_sold')
            ->take(4)
            ->get();

        return view('frontend.home', compact(
            'flashSaleProducts',
            'featuredProducts',
            'latestProducts',
            'bestSellerProducts'
        ));
    }
}