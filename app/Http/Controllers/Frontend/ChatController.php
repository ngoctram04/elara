<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Services\AI\ChatbotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        $conversation = ChatConversation::firstOrCreate([
            'user_id' => Auth::id()
        ]);

        return view('frontend.chat.index', compact('conversation'));
    }

    public function messages()
    {
        $conversation = ChatConversation::where('user_id', Auth::id())->first();

        if (!$conversation) {
            return response()->json([]);
        }

        ChatMessage::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', Auth::id())
            ->update(['is_read' => 1]);

        $messages = ChatMessage::with('sender')
            ->where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                $isUser = (int) $msg->sender_id === (int) Auth::id();

                return [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => $isUser ? 'Bạn' : ($msg->sender->name ?? 'Admin'),
                    'message' => $msg->message,
                    'time' => optional($msg->created_at)->format('H:i'),
                    'date' => optional($msg->created_at)->format('d/m/Y'),
                    'created_at' => optional($msg->created_at)->format('d/m/Y H:i'),
                ];
            });

        return response()->json($messages);
    }

    public function send(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:2000',
            'file' => 'nullable|image|max:5120',
        ]);

        $conversation = ChatConversation::firstOrCreate([
            'user_id' => Auth::id()
        ]);

        $createdMessages = [];

        // lưu text nếu có
        if ($request->filled('message')) {
            $createdMessages[] = ChatMessage::create([
                'conversation_id' => $conversation->id,
                'sender_id' => Auth::id(),
                'message' => trim($request->message),
                'is_read' => 0,
            ]);
        }

        // lưu ảnh nếu có
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('chat', 'public');

            $createdMessages[] = ChatMessage::create([
                'conversation_id' => $conversation->id,
                'sender_id' => Auth::id(),
                'message' => '/storage/' . $path,
                'is_read' => 0,
            ]);
        }

        // nếu không có gì thì báo lỗi
        if (empty($createdMessages)) {
            return response()->json([
                'success' => false,
                'message' => 'Tin nhắn trống',
            ], 422);
        }

        User::where('role', 'admin')->each(function ($admin) use ($conversation) {
            $admin->notify(new SystemNotification([
                'title' => 'Tin nhắn mới',
                'message' => 'Khách vừa gửi tin nhắn',
                'url' => route('admin.messages.show', $conversation->id),
                'type' => 'chat',
                'meta' => ['conversation_id' => $conversation->id],
            ]));
        });

        $conversation->touch();

        return response()->json([
            'success' => true,
        ]);
    }

    public function sendAI(Request $request, ChatbotService $chatbotService)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $result = $chatbotService->reply($request->message);

        return response()->json($result);
    }

    public function unreadCount()
    {
        $conversation = ChatConversation::where('user_id', Auth::id())->first();

        if (!$conversation) {
            return response()->json(['count' => 0]);
        }

        $count = ChatMessage::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', 0)
            ->count();

        return response()->json([
            'count' => $count
        ]);
    }
}