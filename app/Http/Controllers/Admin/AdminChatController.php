<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;

class AdminChatController extends Controller
{

    /**
     * Danh sách cuộc trò chuyện
     */
    public function index(Request $request)
    {

        $query = ChatConversation::with([
            'user',
            'messages'
        ]);


        /*
        ============================
        TÌM KIẾM KHÁCH HÀNG
        ============================
        */

        if ($request->filled('keyword')) {

            $keyword = $request->keyword;

            $query->whereHas('user', function ($q) use ($keyword) {

                $q->where('name', 'like', "%{$keyword}%");
            });
        }


        /*
        ============================
        LỌC TIN NHẮN
        ============================
        */

        if ($request->status == 'unread') {

            $query->whereHas('messages', function ($q) {

                $q->where('sender_id', '!=', Auth::id())
                    ->where('is_read', 0);
            });
        }

        if ($request->status == 'read') {

            $query->whereDoesntHave('messages', function ($q) {

                $q->where('sender_id', '!=', Auth::id())
                    ->where('is_read', 0);
            });
        }


        /*
        ============================
        SẮP XẾP CHAT MỚI
        ============================
        */

        $conversations = $query
            ->orderBy('updated_at', 'desc')
            ->paginate(10)
            ->withQueryString();


        return view('admin.messages.index', compact('conversations'));
    }



    /**
     * Xem chi tiết chat
     */
    public function show($id)
    {

        $conversation = ChatConversation::with([
            'user',
            'messages.sender'
        ])->findOrFail($id);


        /*
        ============================
        ĐÁNH DẤU ĐÃ ĐỌC
        ============================
        */

        ChatMessage::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', 0)
            ->update([
                'is_read' => 1
            ]);


        return view('admin.messages.show', compact('conversation'));
    }



    /**
     * Admin gửi tin nhắn
     */
    public function send(Request $request, $id)
    {

        $conversation = ChatConversation::findOrFail($id);

        $message = $request->message;


        /*
        ============================
        Upload ảnh nếu có
        ============================
        */

        if ($request->hasFile('image')) {

            $file = $request->file('image');

            $path = $file->store('chat', 'public');

            $message = '/storage/' . $path;
        }


        /*
        ============================
        Không gửi nếu rỗng
        ============================
        */

        if (!$message) {
            return back();
        }


        /*
        ============================
        Lưu tin nhắn
        ============================
        */

        ChatMessage::create([

            'conversation_id' => $conversation->id,

            'sender_id' => Auth::id(),

            'message' => $message,

            'is_read' => 0

        ]);


        /*
        ============================
        Cập nhật thời gian chat
        ============================
        */

        $conversation->touch();


        return back();
    }
}