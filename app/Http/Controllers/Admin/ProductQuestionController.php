<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductQuestion;
use App\Models\ProductAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\SystemNotification;

class ProductQuestionController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductQuestion::with([
            'product:id,name,slug',
            'product.mainImage',
            'user:id,name',
            'answers.user:id,name'
        ]);

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);

            $query->where(function ($q) use ($keyword) {
                $q->where('question', 'like', "%{$keyword}%")
                    ->orWhereHas('product', function ($p) use ($keyword) {
                        $p->where('name', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('user', function ($u) use ($keyword) {
                        $u->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($request->status === 'answered') {
            $query->has('answers');
        }

        if ($request->status === 'pending') {
            $query->doesntHave('answers');
        }

        if ($request->visibility === 'visible') {
            $query->where('is_active', 1);
        }

        if ($request->visibility === 'hidden') {
            $query->where('is_active', 0);
        }

        if ($request->sort === 'old') {
            $query->oldest();
        } else {
            $query->latest();
        }

        $questions = $query
            ->paginate(10)
            ->withQueryString();

        return view('admin.questions.index', compact('questions'));
    }

    public function answer(Request $request)
    {
        $request->validate([
            'question_id' => 'required|exists:product_questions,id',
            'answer' => 'required|string|max:2000'
        ], [
            'answer.required' => 'Vui lòng nhập nội dung trả lời',
            'answer.max' => 'Nội dung trả lời tối đa 2000 ký tự',
        ]);

        $question = ProductQuestion::with('user', 'product')->findOrFail($request->question_id);

        if (!$question->is_active) {
            return back()->with('error', 'Không thể trả lời câu hỏi đang bị ẩn');
        }

        ProductAnswer::create([
            'question_id' => $question->id,
            'user_id' => Auth::id(),
            'answer' => $request->answer,
            'is_admin' => 1
        ]);

        if ($question->user && $question->product) {
            $question->user->notify(new SystemNotification([
                'title' => 'Câu hỏi đã được trả lời',
                'message' => 'Câu hỏi của bạn về "' . $question->product->name . '" đã được phản hồi',
                'url' => route('products.show', $question->product->slug),
                'type' => 'question',
                'meta' => [
                    'question_id' => $question->id
                ]
            ]));
        }

        return back()->with('success', 'Đã trả lời câu hỏi');
    }

    public function toggleStatus($id)
    {
        $question = ProductQuestion::findOrFail($id);

        $question->is_active = !$question->is_active;
        $question->save();

        return back()->with(
            'success',
            $question->is_active
                ? 'Đã hiển thị câu hỏi'
                : 'Đã ẩn câu hỏi'
        );
    }
}