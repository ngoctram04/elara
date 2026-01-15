<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Promotion;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // ===============================
        // DANH MỤC (MENU)
        // ===============================
        $categories = Category::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        // ===============================
        // SẢN PHẨM NỔI BẬT / MỚI
        // ===============================
        $featuredProducts = Product::with('images')
            ->where('is_active', 1)
            ->where('is_featured', 1)
            ->latest()
            ->take(8)
            ->get();

        $latestProducts = Product::with('images')
            ->where('is_active', 1)
            ->latest()
            ->take(8)
            ->get();

        // ===============================
        // FLASH SALE (có thể gắn promotion)
        // ===============================
        $flashSaleProducts = Product::with('images')
            ->where('is_active', 1)
            ->where('total_stock', '>', 0)
            ->orderByDesc('total_sold')
            ->take(8)
            ->get();

        // ===============================
        // VIEW
        // ===============================
        return view('frontend.home', compact(
            'categories',
            'featuredProducts',
            'latestProducts',
            'flashSaleProducts'
        ));
    }
}