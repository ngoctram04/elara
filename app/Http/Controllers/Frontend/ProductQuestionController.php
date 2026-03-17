<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductQuestion;
use App\Models\ProductAnswer;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Notifications\SystemNotification;

class ProductQuestionController extends Controller
{

    /**
     * Gửi câu hỏi
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'question'   => 'required|string|max:1000'
        ]);

        $question = ProductQuestion::create([
            'product_id' => $request->product_id,
            'user_id'    => Auth::id(),
            'question'   => $request->question,
            'is_active'  => 1
        ]);

        // 🔔 NOTIFY ADMIN
        User::where('role', 'admin')->each(function ($admin) use ($question) {
            $admin->notify(new SystemNotification([
                'title'   => 'Câu hỏi mới',
                'message' => 'Có câu hỏi mới từ khách hàng',
                'url'     => route('admin.questions.index'),
                'type'    => 'question'
            ]));
        });

        return back()->with('success', 'Câu hỏi đã được gửi.');
    }


    /**
     * Trả lời câu hỏi
     */
    public function answer(Request $request)
    {
        $request->validate([
            'question_id' => 'required|exists:product_questions,id',
            'answer'      => 'required|string|max:1000'
        ]);

        $question = ProductQuestion::with('user', 'product')->findOrFail($request->question_id);

        $answer = ProductAnswer::create([
            'question_id' => $question->id,
            'user_id'     => Auth::id(),
            'answer'      => $request->answer,
            'is_admin'    => Auth::user()->role === 'admin' ? 1 : 0
        ]);

        // 🔔 Nếu ADMIN trả lời → notify USER
        if ($answer->is_admin && $question->user) {

            $question->user->notify(new SystemNotification([
                'title'   => 'Câu hỏi đã được trả lời',
                'message' => 'Câu hỏi của bạn về "' . $question->product->name . '" đã được phản hồi',
                'url'     => route('products.show', $question->product->slug),
                'type'    => 'question'
            ]));
        }

        // 🔔 Nếu USER trả lời → notify ADMIN
        if (!$answer->is_admin) {

            User::where('role', 'admin')->each(function ($admin) use ($question) {
                $admin->notify(new SystemNotification([
                    'title'   => 'Khách hàng phản hồi',
                    'message' => 'Khách đã trả lời thêm câu hỏi',
                    'url'     => route('admin.questions.index'),
                    'type'    => 'question'
                ]));
            });
        }

        return back()->with('success', 'Đã trả lời câu hỏi.');
    }


    /**
     * Xóa câu hỏi
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