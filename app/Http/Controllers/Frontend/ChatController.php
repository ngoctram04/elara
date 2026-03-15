<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

class ChatController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Trang chat nhân viên
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $conversation = ChatConversation::firstOrCreate([
            'user_id' => Auth::id()
        ]);

        return view('frontend.chat.index', compact('conversation'));
    }

    /*
    |--------------------------------------------------------------------------
    | Lấy danh sách tin nhắn
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | Gửi tin nhắn cho nhân viên
    |--------------------------------------------------------------------------
    */
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:2000',
            'file' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120'
        ]);

        $conversation = ChatConversation::firstOrCreate([
            'user_id' => Auth::id()
        ]);

        $text = $request->message;

        if ($request->hasFile('file')) {

            $file = $request->file('file');
            $path = $file->store('chat', 'public');

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
| AI CHAT LOGIC
|--------------------------------------------------------------------------
*/

    private function askAI($message)
    {
        $msg = strtolower(trim($message));

        /*
|--------------------------------------------------------------------------
| FAQ
|--------------------------------------------------------------------------
*/

        if (
            str_contains($msg, 'phí ship') ||
            str_contains($msg, 'giá ship') ||
            str_contains($msg, 'ship bao nhiêu')
        ) {
            return "🚚 Phí ship ELARA khoảng 15.000đ - 35.000đ tùy khu vực.";
        }

        if (
            str_contains($msg, 'giao hàng') ||
            str_contains($msg, 'bao lâu nhận') ||
            str_contains($msg, 'mấy ngày tới')
        ) {
            return "🚚 ELARA giao hàng toàn quốc từ 2-4 ngày.";
        }

        if (str_contains($msg, 'đổi trả')) {
            return "🔄 ELARA hỗ trợ đổi trả trong 7 ngày nếu sản phẩm lỗi.";
        }

        if (str_contains($msg, 'thanh toán')) {
            return "💳 ELARA hỗ trợ COD, chuyển khoản và VNPay.";
        }

        /*
|--------------------------------------------------------------------------
| ROUTINE SKINCARE
|--------------------------------------------------------------------------
*/

        if (str_contains($msg, 'routine')) {

            return "

Routine skincare cơ bản:

1️⃣ Sữa rửa mặt  
2️⃣ Toner  
3️⃣ Serum  
4️⃣ Kem dưỡng  
5️⃣ Kem chống nắng

";
        }

        /*
|--------------------------------------------------------------------------
| TƯ VẤN DA
|--------------------------------------------------------------------------
*/

        if (
            str_contains($msg, 'da dầu')
        ) {
            return $this->suggestProductsByCategory('cham-soc-da-mat');
        }

        if (
            str_contains($msg, 'da khô')
        ) {
            return $this->suggestProductsByCategory('cham-soc-da-mat');
        }

        if (
            str_contains($msg, 'mụn')
        ) {
            return $this->suggestProductsByCategory('cham-soc-da-mat');
        }

        if (
            str_contains($msg, 'tẩy trang')
        ) {
            return $this->suggestProductsByCategory('tay-trang');
        }

        /*
|--------------------------------------------------------------------------
| TÌM THEO BRAND
|--------------------------------------------------------------------------
*/

        $brand = Brand::where('name', 'like', '%' . $msg . '%')->first();

        if ($brand) {
            return $this->suggestProductsByBrand($brand->id);
        }

        /*
|--------------------------------------------------------------------------
| TÌM THEO CATEGORY
|--------------------------------------------------------------------------
*/

        $category = Category::where('name', 'like', '%' . $msg . '%')->first();

        if ($category) {
            return $this->suggestProductsByCategorySlug($category->slug);
        }

        /*
|--------------------------------------------------------------------------
| SEARCH PRODUCT
|--------------------------------------------------------------------------
*/

        return $this->suggestProductsByKeyword($msg);
    }


    /*
|--------------------------------------------------------------------------
| SEARCH KEYWORD
|--------------------------------------------------------------------------
*/

    private function suggestProductsByKeyword($keyword)
    {

        $products = Product::with('mainImage')
            ->where('is_active', 1)
            ->where(function ($q) use ($keyword) {

                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%');
            })
            ->limit(3)
            ->get();

        return $this->renderProducts($products);
    }


    /*
|--------------------------------------------------------------------------
| CATEGORY
|--------------------------------------------------------------------------
*/

    private function suggestProductsByCategorySlug($slug)
    {

        $category = Category::where('slug', $slug)->first();

        if (!$category) return "Không tìm thấy sản phẩm.";

        $products = Product::with('mainImage')
            ->where('category_id', $category->id)
            ->limit(3)
            ->get();

        return $this->renderProducts($products);
    }

    private function suggestProductsByCategory($slug)
    {

        $category = Category::where('slug', $slug)->first();

        if (!$category) return "Không tìm thấy sản phẩm.";

        $products = Product::with('mainImage')
            ->where('category_id', $category->id)
            ->limit(3)
            ->get();

        return $this->renderProducts($products);
    }


    /*
|--------------------------------------------------------------------------
| BRAND
|--------------------------------------------------------------------------
*/

    private function suggestProductsByBrand($brand_id)
    {

        $products = Product::with('mainImage')
            ->where('brand_id', $brand_id)
            ->limit(3)
            ->get();

        return $this->renderProducts($products);
    }


    /*
|--------------------------------------------------------------------------
| RENDER PRODUCT
|--------------------------------------------------------------------------
*/

    private function renderProducts($products)
    {

        if ($products->isEmpty()) {

            return "
Xin lỗi, tôi chưa tìm thấy sản phẩm phù hợp.

👉 <a href='/chat'>Chat với nhân viên</a>
";
        }

        $reply = "ELARA gợi ý cho bạn:<br><br>";

        foreach ($products as $p) {

            $url = route('products.show', $p->slug);

            $img = $p->main_image_url;

            $price = number_format($p->min_price, 0, ',', '.') . "₫";

            $reply .= "

<div style='display:flex;margin-bottom:10px'>

<img src='{$img}'
width='50'
height='50'
style='object-fit:cover;border-radius:6px;margin-right:10px'>

<div>

<a href='{$url}'
style='font-weight:600;color:#2c3e50'>

{$p->name}

</a>

<div style='color:#e74c3c;font-weight:600'>
{$price}
</div>

</div>

</div>

";
        }

        return $reply;
    }


    /*
|--------------------------------------------------------------------------
| API AI CHAT
|--------------------------------------------------------------------------
*/

    public function sendAI(Request $request)
    {

        $request->validate([
            'message' => 'required|string|max:2000'
        ]);

        $reply = $this->askAI($request->message);

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
            ->where('sender_id', '!=', Auth::id()) // admin gửi
            ->where('is_read', 0)
            ->count();

        return response()->json([
            'count' => $count
        ]);
    }
}