<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

use App\Models\OrderItem;
use App\Models\Review;
use App\Models\ReviewMedia;
use App\Models\User;
use App\Notifications\SystemNotification;

class ReviewController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | FORM ĐÁNH GIÁ
    |--------------------------------------------------------------------------
    */
    public function create($orderItemId)
    {
        $orderItem = OrderItem::with([
            'variant.product',
            'order',
            'review'
        ])
            ->where('id', $orderItemId)
            ->whereHas('order', function ($q) {
                $q->where('user_id', Auth::id())
                    ->where('status', 3);
            })
            ->firstOrFail();

        if ($orderItem->review) {
            return redirect()
                ->route('orders.show', $orderItem->order_id)
                ->with('error', 'Sản phẩm này đã được đánh giá.');
        }

        return view('frontend.reviews.create', compact('orderItem'));
    }

    /*
    |--------------------------------------------------------------------------
    | LƯU ĐÁNH GIÁ
    |--------------------------------------------------------------------------
    */
    public function store(Request $request, $orderItemId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|max:2048',
            'video' => 'nullable|mimes:mp4,mov,avi|max:10240'
        ]);

        $orderItem = OrderItem::with([
            'order',
            'variant.product',
            'review'
        ])
            ->where('id', $orderItemId)
            ->whereHas('order', function ($q) {
                $q->where('user_id', Auth::id())
                    ->where('status', 3);
            })
            ->firstOrFail();

        if ($orderItem->review) {
            return back()->with('error', 'Bạn đã đánh giá sản phẩm này.');
        }

        DB::beginTransaction();

        try {

            // =========================
            // CREATE REVIEW
            // =========================
            $review = Review::create([
                'user_id' => Auth::id(),
                'order_id' => $orderItem->order_id,
                'order_item_id' => $orderItem->id,
                'product_id' => $orderItem->variant->product_id,
                'variant_id' => $orderItem->variant_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
                'is_visible' => 1
            ]);

            // =========================
            // UPLOAD ẢNH
            // =========================
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('reviews', 'public');

                    ReviewMedia::create([
                        'review_id' => $review->id,
                        'file_path' => $path,
                        'file_type' => 'image'
                    ]);
                }
            }

            // =========================
            // UPLOAD VIDEO
            // =========================
            if ($request->hasFile('video')) {
                $path = $request->file('video')->store('reviews', 'public');

                ReviewMedia::create([
                    'review_id' => $review->id,
                    'file_path' => $path,
                    'file_type' => 'video'
                ]);
            }

            DB::commit();

            // =========================
            // NOTIFICATION
            // =========================
            $user = Auth::user();
            $productName = $orderItem->variant->product->name ?? 'Sản phẩm';

            // 🔔 USER
            if ($user) {
                Notification::send($user, new SystemNotification([
                    'title' => 'Đánh giá thành công',
                    'message' => 'Bạn đã đánh giá sản phẩm "' . $productName . '"',
                    'url' => route('orders.show', $orderItem->order->id),
                    'type' => 'review',
                ]));
            }

            // 🔔 ADMIN (KHÔNG CÒN LỖI IDE)
            $admins = User::where('role', 'admin')
                ->where('is_active', 1)
                ->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new SystemNotification([
                    'title' => 'Có đánh giá mới',
                    'message' => 'Sản phẩm "' . $productName . '" vừa được đánh giá',
                    'url' => route('admin.reviews.show', $review->id),
                    'type' => 'review',
                ]));
            }

            return redirect()
                ->route('orders.show', $orderItem->order_id)
                ->with('success', 'Đánh giá thành công!');
        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', 'Có lỗi xảy ra khi gửi đánh giá.');
        }
    }
}