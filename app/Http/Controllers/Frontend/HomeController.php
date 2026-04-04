<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        /* =====================================================
           ADMIN -> chuyển về dashboard
        ===================================================== */
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect()->route('admin.reports.index');
        }

        $now = Carbon::now();

        /* ===============================
           DANH MỤC NHỎ (CHỈ LẤY DANH MỤC CON CÓ ẢNH)
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
        $featuredProducts = Product::with([
            'mainImage',
            'brand',
            'variants',
        ])
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
        $latestProducts = Product::with([
            'mainImage',
            'brand',
            'variants',
        ])
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
            'promotions' => function ($q) use ($now) {
                $q->where('type', 'product')
                    ->where('is_active', true)
                    ->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now);
            },
        ])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->withSum('variants as variants_sold_sum', 'sold_quantity')
            ->where('is_active', true)
            ->whereHas('promotions', function ($q) use ($now) {
                $q->where('type', 'product')
                    ->where('is_active', true)
                    ->where('start_date', '<=', $now)
                    ->where('end_date', '>=', $now);
            })
            ->orderByDesc('variants_sold_sum')
            ->take(8)
            ->get();

        /* ===============================
           THỜI GIAN KẾT THÚC FLASH SALE
           Lấy mốc kết thúc sớm nhất trong các promotion đang áp dụng
        =============================== */
        $flashSaleEndTime = null;

        if ($flashSaleProducts->isNotEmpty()) {
            $allActivePromotions = $flashSaleProducts->flatMap(function ($product) {
                return $product->promotions;
            });

            $flashSaleEndTime = $allActivePromotions->min('end_date');
        }

        /*
        // Nếu muốn lấy trực tiếp từ bảng promotions thay vì theo sản phẩm đang hiển thị,
        // thì dùng đoạn này thay cho block bên trên:

        $flashSaleEndTime = Promotion::query()
            ->where('type', 'product')
            ->where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->min('end_date');
        */

        /* ===============================
           BLOG
        =============================== */
        $blogs = collect();

        if (class_exists(\App\Models\Blog::class)) {
            $blogs = \App\Models\Blog::with('author')
                ->latest()
                ->take(5)
                ->get();
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
            'flashSaleEndTime',
            'blogs'
        ));
    }
}