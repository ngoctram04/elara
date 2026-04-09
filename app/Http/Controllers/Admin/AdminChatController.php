<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;
use App\Notifications\SystemNotification;
class AdminChatController extends Controller
{

    public function index(Request $request)
    {

        $query = ChatConversation::with([
            'user',
            'messages'
        ]);


        if ($request->filled('keyword')) {

            $keyword = $request->keyword;

            $query->whereHas('user', function ($q) use ($keyword) {

                $q->where('name', 'like', "%{$keyword}%");
            });
        }


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

        $conversations = $query
            ->orderBy('updated_at', 'desc')
            ->paginate(10)
            ->withQueryString();


        return view('admin.messages.index', compact('conversations'));
    }


    public function show($id)
    {

        $conversation = ChatConversation::with([
            'user',
            'messages.sender'
        ])->findOrFail($id);


        ChatMessage::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', 0)
            ->update([
                'is_read' => 1
            ]);


        return view('admin.messages.show', compact('conversation'));
    }

    public function send(Request $request, $id)
    {
        $conversation = ChatConversation::findOrFail($id);

        if (is_null($conversation->admin_id)) {
            $conversation->admin_id = Auth::id();
            $conversation->save();
        }

        $message = $request->message;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('chat', 'public');
            $message = '/storage/' . $path;
        }

        if (!$message) {
            return back();
        }

        $msg = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => Auth::id(),
            'message'         => $message,
            'is_read'         => 0
        ]);
        $conversation->user->notify(new SystemNotification([
            'title'   => 'Tin nhắn mới từ shop',
            'message' => 'Bạn có tin nhắn mới từ nhân viên',
            'url'     => route('chat.index'),
            'type'    => 'chat',
            'meta'    => [
                'conversation_id' => $conversation->id
            ]
        ]));

        $conversation->touch();

        return back();
    }
}