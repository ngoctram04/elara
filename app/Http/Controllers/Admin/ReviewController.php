<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SystemNotification;

class ReviewController extends Controller
{

    public function index(Request $request)
    {
        $query = Review::with(['user', 'product'])
            ->latest();


        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $numberKeyword = preg_replace('/\D/', '', $keyword);

            $query->where(function ($q) use ($keyword, $numberKeyword) {
                $q->where('comment', 'like', '%' . $keyword . '%')
                    ->orWhereHas('user', function ($q2) use ($keyword) {
                        $q2->where('name', 'like', '%' . $keyword . '%');
                    })
                    ->orWhereHas('product', function ($q3) use ($keyword) {
                        $q3->where('name', 'like', '%' . $keyword . '%');
                    })
                    ->orWhereRaw("CONCAT('DG', LPAD(id, 5, '0')) LIKE ?", ['%' . $keyword . '%'])
                    ->orWhereRaw("CONCAT('DH', LPAD(order_id, 5, '0')) LIKE ?", ['%' . $keyword . '%']);

                if ($numberKeyword !== '') {
                    $q->orWhere('id', (int) $numberKeyword)
                        ->orWhere('order_id', (int) $numberKeyword);
                }
            });
        }


        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }


        if ($request->filled('visible')) {
            $query->where('is_visible', $request->visible);
        }


        if ($request->filled('flagged')) {
            $query->where('is_flagged', $request->flagged);
        }

        if ($request->filled('reply')) {
            if ($request->reply === 'replied') {
                $query->whereNotNull('admin_reply');
            }

            if ($request->reply === 'pending') {
                $query->whereNull('admin_reply');
            }
        }


        $reviews = $query->paginate(7)->withQueryString();

        return view('admin.reviews.index', compact('reviews'));
    }


    public function show($id)
    {
        $review = Review::with([
            'user',
            'product',
            'variant',
            'media',
        ])->findOrFail($id);

        return view('admin.reviews.show', compact('review'));
    }

    public function toggleVisibility($id)
    {
        $review = Review::findOrFail($id);

        $review->is_visible = !$review->is_visible;
        $review->save();

        return back()->with('success', 'Cập nhật trạng thái đánh giá thành công');
    }


    public function reply(Request $request, $id)
    {
        $request->validate([
            'admin_reply' => 'required|string|max:1000',
        ]);

        $review = Review::with(['user', 'product'])->findOrFail($id);

        $review->admin_reply = trim($request->admin_reply);
        $review->replied_at = now();
        $review->save();

        $user = $review->user;
        $productName = $review->product->name ?? 'Sản phẩm';

        if ($user) {
            Notification::send($user, new SystemNotification([
                'title' => 'Phản hồi đánh giá',
                'message' => 'Đánh giá của bạn về "' . $productName . '" đã được phản hồi',
                'url' => route('orders.show', $review->order_id),
                'type' => 'review_reply',
            ]));
        }

        return back()->with('success', 'Đã trả lời đánh giá');
    }
}