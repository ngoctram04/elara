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
            /* =============================
                ẢNH SẢN PHẨM
            ============================= */
            'images',
            'mainImage',

            /* =============================
                BIẾN THỂ + ẢNH BIẾN THỂ
                (ĐÚNG THEO MODEL)
            ============================= */
            'variants' => function ($q) {
                $q->where('is_active', 1)
                    ->where('stock', '>', 0)
                    ->with('images'); // ✅ ĐÚNG – KHÔNG PHẢI variantImages
            },

            /* =============================
                DANH MỤC – THƯƠNG HIỆU
            ============================= */
            'category',
            'brand',

            /* =============================
                KHUYẾN MÃI
            ============================= */
            'promotions' => function ($q) {
                $q->where('is_active', 1);
            },
        ])
            ->where('slug', $slug)
            ->firstOrFail();

        /* =============================
            SẢN PHẨM LIÊN QUAN
        ============================= */
        $relatedProducts = Product::with('mainImage')
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->where('is_active', 1)
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
            'variants.images', // ✅ SỬA ĐÚNG
            'category',
            'brand',
            'promotions'
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