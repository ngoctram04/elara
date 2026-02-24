<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;

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

            /* HIỂN THỊ TẤT CẢ BIẾN THỂ (kể cả hết hàng) */
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

        // ❌ BỎ đoạn này (để vẫn xem được khi tất cả hết hàng)
        // if ($product->variants->isEmpty()) {
        //     abort(404);
        // }

        /* =============================
           SẢN PHẨM LIÊN QUAN
           (chỉ lấy sản phẩm còn hàng)
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
            'relatedProducts'
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

            // HIỂN THỊ TẤT CẢ BIẾN THỂ
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