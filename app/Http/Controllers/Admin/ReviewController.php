<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{

    /**
     * Danh sách đánh giá
     */
    public function index(Request $request)
    {

        $query = Review::with(['user', 'product'])
            ->latest();


        /**
         * 🔎 TÌM KIẾM
         */
        if ($request->filled('keyword')) {

            $keyword = $request->keyword;

            $query->where(function ($q) use ($keyword) {

                $q->where('comment', 'like', '%' . $keyword . '%')

                    ->orWhereHas('user', function ($q2) use ($keyword) {
                        $q2->where('name', 'like', '%' . $keyword . '%');
                    })

                    ->orWhereHas('product', function ($q3) use ($keyword) {
                        $q3->where('name', 'like', '%' . $keyword . '%');
                    });
            });
        }


        /**
         * ⭐ LỌC SỐ SAO
         */
        if ($request->filled('rating')) {

            $query->where('rating', $request->rating);
        }


        /**
         * 👁 LỌC TRẠNG THÁI HIỂN THỊ
         */
        if ($request->filled('visible')) {

            $query->where('is_visible', $request->visible);
        }


        /**
         * 💬 LỌC ĐÃ TRẢ LỜI / CHƯA
         */
        if ($request->filled('reply')) {

            if ($request->reply == 'replied') {

                $query->whereNotNull('admin_reply');
            }

            if ($request->reply == 'pending') {

                $query->whereNull('admin_reply');
            }
        }


        /**
         * 📄 PHÂN TRANG
         */
        $reviews = $query->paginate(10)->withQueryString();


        return view('admin.reviews.index', compact('reviews'));
    }



    /**
     * Chi tiết đánh giá
     */
    public function show($id)
    {

        $review = Review::with([
            'user',
            'product',
            'variant',
            'media'
        ])->findOrFail($id);


        return view('admin.reviews.show', compact('review'));
    }



    /**
     * Ẩn / hiện đánh giá
     */
    public function toggleVisibility($id)
    {

        $review = Review::findOrFail($id);

        $review->is_visible = !$review->is_visible;

        $review->save();

        return back()->with('success', 'Cập nhật trạng thái đánh giá thành công');
    }



    /**
     * Trả lời đánh giá
     */
    public function reply(Request $request, $id)
    {

        $request->validate([

            'admin_reply' => 'required|string|max:1000'

        ]);


        $review = Review::findOrFail($id);


        $review->admin_reply = $request->admin_reply;

        $review->replied_at = now();

        $review->save();


        return back()->with('success', 'Đã trả lời đánh giá');
    }
}