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

    /**
     * Danh sách câu hỏi
     */

    public function index(Request $request)
    {

        $query = ProductQuestion::with([
            'product:id,name',
            'product.mainImage',
            'user:id,name',
            'answers.user:id,name'
        ]);


        /*
        |------------------------------------------------
        | SEARCH
        |------------------------------------------------
        */

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


        /*
        |------------------------------------------------
        | FILTER STATUS
        |------------------------------------------------
        */

        if ($request->status == 'answered') {

            $query->has('answers');
        }

        if ($request->status == 'pending') {

            $query->doesntHave('answers');
        }


        /*
        |------------------------------------------------
        | SORT
        |------------------------------------------------
        */

        if ($request->sort == 'old') {

            $query->oldest();
        } else {

            $query->latest();
        }


        /*
        |------------------------------------------------
        | PAGINATION
        |------------------------------------------------
        */

        $questions = $query
            ->paginate(10)
            ->withQueryString();


        return view('admin.questions.index', compact('questions'));
    }



    /**
     * Admin trả lời câu hỏi
     */

    public function answer(Request $request)
    {
        $request->validate([
            'question_id' => 'required|exists:product_questions,id',
            'answer' => 'required|string|max:2000'
        ]);

        // lấy câu hỏi
        $question = ProductQuestion::with('user', 'product')->findOrFail($request->question_id);

        // tạo câu trả lời
        $answer = ProductAnswer::create([
            'question_id' => $question->id,
            'user_id' => Auth::id(),
            'answer' => $request->answer,
            'is_admin' => 1
        ]);

        // 🔔 GỬI THÔNG BÁO CHO USER
        if ($question->user) {
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
}