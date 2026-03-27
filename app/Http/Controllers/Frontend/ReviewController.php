<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Review;
use App\Models\ReviewMedia;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Services\ContentFilterService;

class ReviewController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | FORM ĐÁNH GIÁ TẤT CẢ SẢN PHẨM TRONG 1 ĐƠN
    |--------------------------------------------------------------------------
    */
    public function create($orderId)
    {
        $order = Order::with([
            'items.variant.product',
            'items.variant.mainImage',
            'items.review',
        ])
            ->where('id', $orderId)
            ->where('user_id', Auth::id())
            ->where('status', Order::STATUS_COMPLETED)
            ->firstOrFail();

        $reviewableItems = $order->items->filter(function ($item) {
            return !$item->review;
        });

        if ($reviewableItems->isEmpty()) {
            return redirect()
                ->route('orders.show', $order->id)
                ->with('error', 'Tất cả sản phẩm trong đơn này đã được đánh giá.');
        }

        return view('frontend.reviews.create', compact('order', 'reviewableItems'));
    }

    /*
    |--------------------------------------------------------------------------
    | LƯU ĐÁNH GIÁ NHIỀU SẢN PHẨM TRONG 1 LẦN SUBMIT
    |--------------------------------------------------------------------------
    */
    public function store(Request $request, $orderId)
    {
        $order = Order::with([
            'items.variant.product',
            'items.review',
        ])
            ->where('id', $orderId)
            ->where('user_id', Auth::id())
            ->where('status', Order::STATUS_COMPLETED)
            ->firstOrFail();

        try {
            $validated = $request->validate([
                'reviews' => 'required|array',
                'reviews.*.rating' => 'nullable|integer|min:1|max:5',
                'reviews.*.comment' => 'nullable|string|max:1000',
                'reviews.*.images' => 'nullable|array|max:5',
                'reviews.*.images.*' => 'nullable|image|max:2048',
                'reviews.*.video' => 'nullable|mimes:mp4,mov,avi|max:10240',
            ], [
                'reviews.required' => 'Không có dữ liệu đánh giá.',
                'reviews.array' => 'Dữ liệu đánh giá không hợp lệ.',

                'reviews.*.rating.integer' => 'Số sao đánh giá không hợp lệ.',
                'reviews.*.rating.min' => 'Số sao tối thiểu là 1.',
                'reviews.*.rating.max' => 'Số sao tối đa là 5.',

                'reviews.*.comment.max' => 'Nội dung đánh giá tối đa 1000 ký tự.',

                'reviews.*.images.array' => 'Dữ liệu ảnh không hợp lệ.',
                'reviews.*.images.max' => 'Mỗi sản phẩm chỉ được tải tối đa 5 ảnh.',
                'reviews.*.images.*.image' => 'Tệp tải lên phải là hình ảnh.',
                'reviews.*.images.*.max' => 'Mỗi ảnh tối đa 2MB.',

                'reviews.*.video.mimes' => 'Video phải có định dạng mp4, mov hoặc avi.',
                'reviews.*.video.max' => 'Video tối đa 10MB.',
            ]);
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Dữ liệu không hợp lệ.',
                    'errors'  => $e->errors(),
                ], 422);
            }

            throw $e;
        }

        $reviewsInput = $validated['reviews'] ?? [];

        if (empty($reviewsInput)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Không có dữ liệu đánh giá.',
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', 'Không có dữ liệu đánh giá.');
        }

        DB::beginTransaction();

        try {
            $createdReviews = [];
            $reviewedProductNames = [];
            $hasAtLeastOneReview = false;

            foreach ($reviewsInput as $orderItemId => $data) {
                $orderItem = $order->items->firstWhere('id', (int) $orderItemId);

                if (!$orderItem) {
                    continue;
                }

                if ($orderItem->review) {
                    continue;
                }

                $rating = isset($data['rating']) && $data['rating'] !== ''
                    ? (int) $data['rating']
                    : null;

                $comment = isset($data['comment'])
                    ? trim($data['comment'])
                    : null;

                $hasImages = $request->hasFile("reviews.$orderItemId.images");
                $hasVideo = $request->hasFile("reviews.$orderItemId.video");

                // Nếu block này chưa nhập gì thì bỏ qua
                if (!$rating && empty($comment) && !$hasImages && !$hasVideo) {
                    continue;
                }

                // Nếu đã nhập gì đó thì bắt buộc phải có rating
                if (!$rating) {
                    throw ValidationException::withMessages([
                        "reviews.$orderItemId.rating" => 'Vui lòng chọn số sao cho sản phẩm này.',
                    ]);
                }

                // Lọc comment
                if (!empty($comment)) {
                    $filterResult = app(ContentFilterService::class)->filter($comment);

                    if (!empty($filterResult['blocked'])) {
                        throw ValidationException::withMessages([
                            "reviews.$orderItemId.comment" => 'Vui lòng không sử dụng từ ngữ không phù hợp trong đánh giá.',
                        ]);
                    }

                    $comment = $filterResult['text'] ?? $comment;
                }

                $productId = optional($orderItem->variant)->product_id;

                if (!$productId) {
                    continue;
                }

                $review = Review::create([
                    'user_id'       => Auth::id(),
                    'order_id'      => $order->id,
                    'order_item_id' => $orderItem->id,
                    'product_id'    => $productId,
                    'variant_id'    => $orderItem->variant_id,
                    'rating'        => $rating,
                    'comment'       => $comment,
                    'is_visible'    => 1,
                ]);

                // Upload ảnh
                if ($request->hasFile("reviews.$orderItemId.images")) {
                    foreach ($request->file("reviews.$orderItemId.images") as $image) {
                        $path = $image->store('reviews', 'public');

                        ReviewMedia::create([
                            'review_id' => $review->id,
                            'file_path' => $path,
                            'file_type' => 'image',
                        ]);
                    }
                }

                // Upload video
                if ($request->hasFile("reviews.$orderItemId.video")) {
                    $video = $request->file("reviews.$orderItemId.video");
                    $path = $video->store('reviews', 'public');

                    ReviewMedia::create([
                        'review_id' => $review->id,
                        'file_path' => $path,
                        'file_type' => 'video',
                    ]);
                }

                $createdReviews[] = $review;
                $reviewedProductNames[] = optional($orderItem->variant->product)->name ?? 'Sản phẩm';
                $hasAtLeastOneReview = true;
            }

            if (!$hasAtLeastOneReview) {
                DB::rollBack();

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Bạn chưa nhập đánh giá cho sản phẩm nào.',
                    ], 422);
                }

                return back()
                    ->withInput()
                    ->with('error', 'Bạn chưa nhập đánh giá cho sản phẩm nào.');
            }

            DB::commit();

            // Notification user
            $user = Auth::user();
            if ($user) {
                Notification::send($user, new SystemNotification([
                    'title'   => 'Đánh giá thành công',
                    'message' => 'Bạn đã gửi đánh giá cho ' . count($createdReviews) . ' sản phẩm trong đơn hàng DH' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                    'url'     => route('orders.show', $order->id),
                    'type'    => 'review',
                ]));
            }

            // Notification admin
            $admins = User::where('role', 'admin')
                ->where('is_active', 1)
                ->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new SystemNotification([
                    'title'   => 'Có đánh giá mới',
                    'message' => 'Khách hàng vừa gửi ' . count($createdReviews) . ' đánh giá mới cho đơn hàng DH' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                    'url'     => route('admin.reviews.index'),
                    'type'    => 'review',
                ]));
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message'  => 'Đánh giá thành công!',
                    'redirect' => route('orders.show', $order->id),
                ]);
            }

            return redirect()
                ->route('orders.show', $order->id)
                ->with('success', 'Gửi đánh giá thành công!');
        } catch (ValidationException $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Dữ liệu không hợp lệ.',
                    'errors'  => $e->errors(),
                ], 422);
            }

            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Store multiple reviews error: ' . $e->getMessage(), [
                'order_id' => $orderId,
                'user_id' => Auth::id(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Có lỗi xảy ra khi gửi đánh giá.',
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi gửi đánh giá.');
        }
    }
}