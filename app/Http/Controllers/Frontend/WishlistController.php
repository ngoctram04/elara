<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * ==============================
     * Danh sách sản phẩm yêu thích
     * URL: /wishlist
     * ==============================
     */
    public function index()
    {
        $userId = Auth::id();

        // Load đầy đủ để tránh N+1
        $wishlists = Wishlist::with([
            'product.brand',
            'product.mainImage',
            'product.variants'
        ])
            ->where('user_id', $userId)
            ->latest()
            ->paginate(12);

        // Danh sách product_id đã thích (để tô tim)
        $favorites = Wishlist::where('user_id', $userId)
            ->pluck('product_id')
            ->toArray();

        return view('frontend.wishlist.index', compact(
            'wishlists',
            'favorites'
        ));
    }


    /**
     * ==============================
     * Toggle Wishlist (AJAX)
     * Route: POST /wishlist/toggle
     * ==============================
     */
    public function toggle(Request $request)
    {
        // Chưa đăng nhập
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập'
            ]);
        }

        // Validate
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $userId    = Auth::id();
        $productId = (int) $request->product_id;

        // Kiểm tra đã tồn tại chưa
        $wishlist = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        // ===== Remove =====
        if ($wishlist) {
            $wishlist->delete();
            $favorited = false;
            $message   = 'Đã bỏ khỏi yêu thích';
        }
        // ===== Add =====
        else {
            Wishlist::create([
                'user_id'    => $userId,
                'product_id' => $productId
            ]);

            $favorited = true;
            $message   = 'Đã thêm vào yêu thích';
        }

        // Tổng số lượt thích
        $count = Wishlist::where('product_id', $productId)->count();

        // Format hiển thị (1,2k)
        $formattedCount = $count;
        if ($count >= 1000) {
            $formattedCount = str_replace('.', ',', round($count / 1000, 1)) . 'k';
        }

        return response()->json([
            'success'   => true,
            'favorited' => $favorited,
            'count'     => $formattedCount,
            'raw_count' => $count,
            'message'   => $message
        ]);
    }
}