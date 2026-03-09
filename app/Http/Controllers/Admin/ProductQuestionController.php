<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductQuestion;
use App\Models\ProductAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductQuestionController extends Controller
{
    public function index()
    {
        $questions = ProductQuestion::with([
            'product:id,name',
            'user:id,name',
            'answers.user:id,name'
        ])
            ->latest()
            ->paginate(10);

        return view('admin.questions.index', compact('questions'));
    }

    public function answer(Request $request)
    {
        $request->validate([
            'question_id' => 'required',
            'answer' => 'required|string'
        ]);

        ProductAnswer::create([
            'question_id' => $request->question_id,
            'user_id' => Auth::id(),
            'answer' => $request->answer,
            'is_admin' => 1
        ]);

        return back()->with('success', 'Đã trả lời câu hỏi');
    }
}