<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WishlistController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $reviewStats = DB::table('reviews')
            ->join('order_items', 'order_items.id', '=', 'reviews.order_item_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.variant_id')
            ->where('reviews.is_visible', 1)
            ->groupBy('product_variants.product_id')
            ->selectRaw('
                product_variants.product_id,
                AVG(reviews.rating) as reviews_avg_rating,
                COUNT(reviews.id) as reviews_count
            ');

        $wishlists = Wishlist::with([
            'product' => function ($q) use ($reviewStats) {
                $q->leftJoinSub($reviewStats, 'review_stats', function ($join) {
                    $join->on('products.id', '=', 'review_stats.product_id');
                })
                    ->select(
                        'products.*',
                        DB::raw('COALESCE(review_stats.reviews_avg_rating, 0) as reviews_avg_rating'),
                        DB::raw('COALESCE(review_stats.reviews_count, 0) as reviews_count')
                    )
                    ->with([
                        'brand',
                        'mainImage',
                        'variants'
                    ]);
            }
        ])
            ->where('user_id', $userId)
            ->latest()
            ->paginate(12);

        $favorites = Wishlist::where('user_id', $userId)
            ->pluck('product_id')
            ->toArray();

        return view('frontend.wishlist.index', compact(
            'wishlists',
            'favorites'
        ));
    }

    public function toggle(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập'
            ]);
        }

        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $userId    = Auth::id();
        $productId = (int) $request->product_id;

        $wishlist = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            $favorited = false;
            $message   = 'Đã bỏ khỏi yêu thích';
        } else {
            Wishlist::create([
                'user_id'    => $userId,
                'product_id' => $productId
            ]);

            $favorited = true;
            $message   = 'Đã thêm vào yêu thích';
        }

        $count = Wishlist::where('product_id', $productId)->count();

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