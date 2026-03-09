<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductQuestion;
use App\Models\ProductAnswer;
use Illuminate\Support\Facades\Auth;

class ProductQuestionController extends Controller
{

    /**
     * Gửi câu hỏi sản phẩm
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'question' => 'required|string|max:1000'
        ]);

        ProductQuestion::create([
            'product_id' => $request->product_id,
            'user_id' => Auth::id(),
            'question' => $request->question,
            'is_active' => 1
        ]);

        return back()->with('success', 'Câu hỏi đã được gửi.');
    }


    /**
     * Trả lời câu hỏi
     */
    public function answer(Request $request)
    {
        $request->validate([
            'question_id' => 'required|exists:product_questions,id',
            'answer' => 'required|string|max:1000'
        ]);

        ProductAnswer::create([
            'question_id' => $request->question_id,
            'user_id' => Auth::id(),
            'answer' => $request->answer,
            'is_admin' => Auth::user()->role === 'admin' ? 1 : 0
        ]);

        return back()->with('success', 'Đã trả lời câu hỏi.');
    }


    /**
     * Xóa câu hỏi (admin hoặc người hỏi)
     */
    public function deleteQuestion($id)
    {
        $question = ProductQuestion::findOrFail($id);

        if (Auth::user()->role !== 'admin' && $question->user_id !== Auth::id()) {
            abort(403);
        }

        $question->delete();

        return back()->with('success', 'Đã xóa câu hỏi.');
    }


    /**
     * Xóa câu trả lời
     */
    public function deleteAnswer($id)
    {
        $answer = ProductAnswer::findOrFail($id);

        if (Auth::user()->role !== 'admin' && $answer->user_id !== Auth::id()) {
            abort(403);
        }

        $answer->delete();

        return back()->with('success', 'Đã xóa câu trả lời.');
    }
}