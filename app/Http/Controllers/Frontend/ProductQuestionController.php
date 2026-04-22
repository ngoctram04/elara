<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductQuestion;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Notifications\SystemNotification;

class ProductQuestionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'question'   => 'required|string|max:1000',
        ]);

        $question = ProductQuestion::create([
            'product_id' => $request->product_id,
            'user_id'    => Auth::id(),
            'question'   => $request->question,
            'is_active'  => 1,
        ]);

        User::where('role', 'admin')->each(function ($admin) {
            $admin->notify(new SystemNotification([
                'title'   => 'Câu hỏi mới',
                'message' => 'Có câu hỏi mới từ khách hàng',
                'url'     => route('admin.questions.index'),
                'type'    => 'question',
            ]));
        });

        return back()->with('success', 'Câu hỏi đã được gửi.');
    }

    public function answer(Request $request)
    {
        abort(403, 'Chỉ quản trị viên mới có quyền trả lời câu hỏi.');
    }

    public function deleteQuestion($id)
    {
        $question = ProductQuestion::findOrFail($id);

        if (Auth::user()->role !== 'admin' && $question->user_id !== Auth::id()) {
            abort(403);
        }

        $question->delete();

        return back()->with('success', 'Đã xóa câu hỏi.');
    }

    public function deleteAnswer($id)
    {
        $answer = \App\Models\ProductAnswer::findOrFail($id);

        if (Auth::user()->role !== 'admin' && $answer->user_id !== Auth::id()) {
            abort(403);
        }

        $answer->delete();

        return back()->with('success', 'Đã xóa câu trả lời.');
    }
}