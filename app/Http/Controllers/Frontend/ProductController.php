<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Trang chi tiết sản phẩm
     * URL: /product/{slug}
     */
    public function show(string $slug)
    {
        $product = Product::with([
            /* ẢNH */
            'images',
            'mainImage',

            /* BIẾN THỂ */
            'variants' => function ($q) {
                $q->where('is_active', 1)
                    ->orderBy('id')
                    ->with('images');
            },

            /* THÔNG TIN */
            'category',
            'brand',

            /* KHUYẾN MÃI */
            'promotions' => function ($q) {
                $q->where('is_active', 1);
            },
        ])
            ->where('slug', $slug)
            ->where('is_active', 1)
            ->firstOrFail();


        /* =============================
           WISHLIST
        ============================= */

        // Các sản phẩm user đã thích
        $favorites = [];
        if (Auth::check()) {
            $favorites = Wishlist::where('user_id', Auth::id())
                ->pluck('product_id')
                ->toArray();
        }

        // Tổng số lượt thích của sản phẩm này
        $favoritesCount = Wishlist::where('product_id', $product->id)->count();


        /* =============================
           SẢN PHẨM LIÊN QUAN
        ============================= */
        $relatedProducts = Product::with('mainImage')
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->where('is_active', 1)
            ->whereHas('variants', function ($q) {
                $q->where('stock_quantity', '>', 0)
                    ->where('is_active', 1);
            })
            ->latest()
            ->limit(8)
            ->get();


        return view('frontend.detail', compact(
            'product',
            'relatedProducts',
            'favorites',
            'favoritesCount'
        ));
    }


    /**
     * Xem nhanh – dùng cho modal AJAX
     */
    public function quickView(int $id)
    {
        $product = Product::with([
            'images',
            'mainImage',

            'variants' => function ($q) {
                $q->where('is_active', 1)
                    ->orderBy('id')
                    ->with('images');
            },

            'category',
            'brand',

            'promotions' => function ($q) {
                $q->where('is_active', 1);
            }
        ])
            ->where('id', $id)
            ->where('is_active', 1)
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data'   => $product
        ]);
    }
}
