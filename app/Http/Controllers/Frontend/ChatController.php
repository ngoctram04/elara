<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Product;
use App\Models\User;
use App\Notifications\SystemNotification;

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

        if (!$conversation) return response()->json([]);

        ChatMessage::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', Auth::id())
            ->update(['is_read' => 1]);

        $messages = ChatMessage::with('sender')
            ->where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {

                $isUser = $msg->sender_id == Auth::id();

                return [
                    'id' => $msg->id,
                    'sender_id' => $msg->sender_id,
                    'sender_name' => $isUser ? 'Bạn' : ($msg->sender->name ?? 'Admin'),
                    'message' => $msg->message,
                    'time' => $msg->created_at?->format('H:i'),
                    'date' => $msg->created_at?->format('d/m/Y'),
                    'created_at' => $msg->created_at?->format('d/m/Y H:i')
                ];
            });

        return response()->json($messages);
    }

    public function send(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:2000',
            'file' => 'nullable|image|max:5120'
        ]);

        $conversation = ChatConversation::firstOrCreate([
            'user_id' => Auth::id()
        ]);

        $text = $request->message;

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('chat', 'public');
            $text = '/storage/' . $path;
        }

        if (!$text) {
            return response()->json([
                'success' => false,
                'message' => 'Tin nhắn trống'
            ]);
        }

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'message' => $text,
            'is_read' => 0
        ]);

        User::where('role', 'admin')->each(function ($admin) use ($conversation) {
            $admin->notify(new SystemNotification([
                'title' => 'Tin nhắn mới',
                'message' => 'Khách vừa gửi tin nhắn',
                'url' => route('admin.messages.show', $conversation->id),
                'type' => 'chat',
                'meta' => ['conversation_id' => $conversation->id]
            ]));
        });

        $conversation->touch();

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'sender_name' => 'Bạn',
                'message' => $message->message,
                'time' => $message->created_at->format('H:i'),
                'date' => $message->created_at->format('d/m/Y'),
                'created_at' => $message->created_at->format('d/m/Y H:i')
            ]
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | AI LOGIC (DB FIRST)
    |--------------------------------------------------------------------------
    */
    private function normalize($text)
    {
        $text = strtolower($text);

        $unicode = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
        ];

        foreach ($unicode as $nonAccent => $accent) {
            $text = preg_replace("/($accent)/i", $nonAccent, $text);
        }

        return $text;
    }

    private function askAI($message)
    {
        $msg = $this->normalize($message);
        /*
|--------------------------------------------------------------------------
| 0.1 CHU TRÌNH DƯỠNG DA
|--------------------------------------------------------------------------
*/
        if (
            str_contains($msg, 'duong da') ||
            str_contains($msg, 'skincare') ||
            str_contains($msg, 'routine') ||
            str_contains($msg, 'cham soc da')
        ) {
            return "Chu trình dưỡng da cơ bản:

Buổi sáng:
- Sữa rửa mặt
- Toner
- Serum (nếu có)
- Kem dưỡng
- Kem chống nắng

Buổi tối:
- Tẩy trang
- Sữa rửa mặt
- Toner
- Serum
- Kem dưỡng

Bạn có thể cho mình biết loại da để mình gợi ý sản phẩm phù hợp hơn.";
        }
        /*
|--------------------------------------------------------------------------
| 0.2 HẠNG THÀNH VIÊN
|--------------------------------------------------------------------------
*/
        if (
            str_contains($msg, 'hang') ||
            str_contains($msg, 'thanh vien') ||
            str_contains($msg, 'bac') ||
            str_contains($msg, 'vang') ||
            str_contains($msg, 'kim cuong') ||
            str_contains($msg, 'diem')
        ) {
            return "Chính sách hạng thành viên:

Đồng (0 điểm):
- Không có ưu đãi

Bạc (1.000 điểm):
- Giảm 5% vào ngày sinh nhật

Vàng (3.000 điểm):
- Freeship đơn từ 300.000đ
- Giảm 10% vào ngày sinh nhật

Kim cương (10.000 điểm):
- Freeship mọi đơn
- Giảm 15% vào ngày sinh nhật";
        }
        if (
            str_contains($msg, 'khuyen mai') ||
            str_contains($msg, 'giam gia') ||
            str_contains($msg, 'sale')
        ) {

            $products = Product::with('mainImage')
            ->where('is_active', 1)
            ->whereHas('promotions', function ($q) {
                $q->where('is_active', 1)
                ->where('type', 'product')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
            })
            ->limit(3)
            ->get();

            if ($products->isNotEmpty()) {
                return "Sản phẩm đang khuyến mãi:<br>" . $this->renderProducts($products);
            }

            return "Hiện tại chưa có sản phẩm khuyến mãi.

<a href='http://127.0.0.1:8000/chat'>Chat với nhân viên</a>";
        }
        /*
    |--------------------------------------------------------------------------
    | 1. XỬ LÝ SHIP
    |--------------------------------------------------------------------------
    */
        if (str_contains($msg, 'ship') || str_contains($msg, 'phi')) {

            $vung15 = ['vinh long'];

            $vung25 = [
                'can tho',
                'ben tre',
                'tra vinh',
                'soc trang',
                'hau giang',
                'dong thap',
                'an giang',
                'kien giang',
                'ca mau',
                'bac lieu',
                'tien giang'
            ];

            foreach ($vung15 as $tinh) {
                if (str_contains($msg, $tinh)) {
                    return "Phí ship về " . ucfirst($tinh) . ": 15.000đ";
                }
            }

            foreach ($vung25 as $tinh) {
                if (str_contains($msg, $tinh)) {
                    return "Phí ship về " . ucfirst($tinh) . ": 25.000đ";
                }
            }

            return "Phí ship như sau:
- Vĩnh Long: 15.000đ
- Cần Thơ, Bến Tre, Trà Vinh, Sóc Trăng, Hậu Giang, Đồng Tháp, An Giang, Kiên Giang, Cà Mau, Bạc Liêu, Tiền Giang: 25.000đ
- Khu vực khác: 35.000đ

Bạn ở tỉnh nào để mình báo chính xác hơn.";
        }

        /*
    |--------------------------------------------------------------------------
    | 2. DETECT GIÁ + INTENT
    |--------------------------------------------------------------------------
    */
        preg_match('/(\d+)/', $msg, $matches);
        $price = $matches[1] ?? null;

        // xử lý 100k => 100000
        if ($price && str_contains($msg, 'k')) {
            $price = $price * 1000;
        }

        // detect product
        $keywords = [
            'kem',
            'gel',
            'serum',
            'tay',
            'duong',
            'sua',
            'nuoc',
            'san pham',
            'sp',
            'hang',
            'do'
        ];

        $isProductSearch = false;

        foreach ($keywords as $kw) {
            if (str_contains($msg, $kw)) {
                $isProductSearch = true;
                break;
            }
        }

        // 🔥 QUAN TRỌNG: có giá là search
        if ($price) {
            $isProductSearch = true;
        }

        /*
    |--------------------------------------------------------------------------
    | 3. KHÔNG PHẢI PRODUCT → CHAT
    |--------------------------------------------------------------------------
    */
        if (!$isProductSearch) {
            return "Mình chưa hiểu rõ yêu cầu của bạn.

Bạn có thể mô tả cụ thể hơn hoặc liên hệ nhân viên để được hỗ trợ:
<a href='http://127.0.0.1:8000/chat'>Chat với nhân viên</a>";
        }

        /*
    |--------------------------------------------------------------------------
    | 4. QUERY SẢN PHẨM
    |--------------------------------------------------------------------------
    */
        $query = Product::with('mainImage')
        ->where('is_active', 1);

        // lọc giá
        if ($price) {
            if (str_contains($msg, 'duoi')) {
                $query->where('min_price', '<=', $price);
            } elseif (str_contains($msg, 'tren')) {
                $query->where('min_price', '>=', $price);
            }
        }

        // 🔥 CHỈ filter name khi KHÔNG có giá
        if (!$price) {

            $ignoreWords = ['duoi', 'tren', 'khoang', 'gia', 'bao', 'nhieu', 'co', 'gi'];

            $query->where(function ($q) use ($msg, $ignoreWords) {
                foreach (explode(' ', $msg) as $word) {
                    if (strlen($word) >= 3 && !in_array($word, $ignoreWords)) {
                        $q->orWhere('name', 'like', "%$word%");
                    }
                }
            });
        }
        $products = $query->limit(3)->get();

        if ($products->isNotEmpty()) {
            return "Mình gợi ý cho bạn:<br>" . $this->renderProducts($products);
        }

        /*
    |--------------------------------------------------------------------------
    | 5. KHÔNG CÓ → CHAT NHÂN VIÊN
    |--------------------------------------------------------------------------
    */
        return "Hiện tại mình chưa tìm thấy sản phẩm phù hợp trong hệ thống.

Bạn có thể mô tả rõ hơn hoặc liên hệ nhân viên để được hỗ trợ nhanh hơn:
<a href='http://127.0.0.1:8000/chat'>Chat với nhân viên</a>";
    }

    /*
    |--------------------------------------------------------------------------
    | RENDER PRODUCT
    |--------------------------------------------------------------------------
    */
    private function renderProducts($products)
    {
        $html = "";

        foreach ($products as $p) {
            $url = route('products.show', $p->slug);
            $img = $p->main_image_url;
            $price = number_format($p->min_price, 0, ',', '.') . "₫";

            $html .= "
            <div style='display:flex;margin-bottom:10px'>
                <img src='{$img}' width='50' height='50'
                    style='object-fit:cover;border-radius:6px;margin-right:10px'>
                <div>
                    <a href='{$url}' style='font-weight:600;color:#2c3e50'>
                        {$p->name}
                    </a>
                    <div style='color:#e74c3c;font-weight:600'>
                        {$price}
                    </div>
                </div>
            </div>
            ";
        }

        return $html;
    }

    /*
    |--------------------------------------------------------------------------
    | CALL AI (fallback)
    |--------------------------------------------------------------------------
    */
    private function callAI($message)
    {
        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => 'deepseek/deepseek-chat',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Bạn là nhân viên ELARA, trả lời ngắn gọn, KHÔNG bịa sản phẩm.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $message
                    ]
                ]
            ]);

            $data = $response->json();

            return data_get(
                $response->json(),
                'choices.0.message.content',
                'AI không phản hồi'
            );
        } catch (\Exception $e) {
            return 'AI lỗi: ' . $e->getMessage();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | API
    |--------------------------------------------------------------------------
    */
    public function sendAI(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000'
        ]);

        $reply = $this->askAI($request->message); // ✅ FIX QUAN TRỌNG

        return response()->json([
            'reply' => $reply
        ]);
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