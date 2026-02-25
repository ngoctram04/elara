<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
class CheckoutController extends Controller
{
    /**
     * Trang checkout
     */

    public function index()
    {
        /**
         * =================================
         * ƯU TIÊN BUY NOW
         * =================================
         * Chỉ dùng khi session buy_now tồn tại
         */
        if (session()->has('buy_now')) {

            $buyNow = session('buy_now');

            $variant = ProductVariant::with([
                'product:id,name',
                'mainImage'
            ])->find($buyNow['variant_id']);

            // Nếu sản phẩm không còn → xóa session
            if (!$variant) {
                session()->forget('buy_now');
                return redirect()->route('cart.index');
            }

            $carts = collect([
                (object)[
                    'variant'  => $variant,
                    'quantity' => $buyNow['quantity']
                ]
            ]);

            $subtotal = $variant->price * $buyNow['quantity'];

            return view('frontend.checkout.index', compact('carts', 'subtotal'));
        }

        /**
         * =================================
         * CHECKOUT TỪ GIỎ HÀNG
         * =================================
         */

        $carts = Cart::with([
            'variant:id,product_id,attribute_name,attribute_value,price',
            'variant.mainImage',
            'variant.product:id,name'
        ])
            ->where('user_id', Auth::id())
            ->get();

        if ($carts->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        $subtotal = $carts->sum(function ($cart) {
            return $cart->variant->price * $cart->quantity;
        });

        return view('frontend.checkout.index', compact('carts', 'subtotal'));
    }
    public function fromCart()
    {
        session()->forget('buy_now');
        return redirect()->route('checkout.index');
    }
    public function store(Request $request)
    {
        $request->validate([
            'receiver_name'    => 'required|string|max:255',
            'receiver_phone'   => 'required|string|max:20',
            'receiver_address' => 'required|string|max:500',
            'payment_method'   => 'required|in:cod,vnpay',
        ]);

        $userId = Auth::id();

        DB::beginTransaction();

        try {

            $subtotal = 0;
            $items = collect();
            $isBuyNow = false;

            /**
             * =================================================
             * XÁC ĐỊNH NGUỒN CHECKOUT
             * =================================================
             */

            // Nếu session buy_now tồn tại → dùng mua ngay
            if (session()->has('buy_now')) {

                $buyNow = session('buy_now');

                $variant = ProductVariant::find($buyNow['variant_id']);

                // Nếu variant không tồn tại → bỏ session
                if (!$variant) {
                    session()->forget('buy_now');
                } else {
                    $isBuyNow = true;

                    if ($variant->stock_quantity < $buyNow['quantity']) {
                        DB::rollBack();
                        return back()->with('error', 'Sản phẩm không đủ tồn kho.');
                    }

                    $subtotal = $variant->price * $buyNow['quantity'];

                    $items->push([
                        'variant'  => $variant,
                        'quantity' => $buyNow['quantity']
                    ]);
                }
            }

            /**
             * =================================================
             * NẾU KHÔNG PHẢI BUY NOW → LẤY TỪ GIỎ HÀNG
             * =================================================
             */
            if (!$isBuyNow) {

                // Đảm bảo không dùng session cũ
                session()->forget('buy_now');

                $carts = Cart::with('variant.product')
                ->where('user_id', $userId)
                    ->get();

                if ($carts->isEmpty()) {
                    DB::rollBack();
                    return back()->with('error', 'Giỏ hàng trống.');
                }

                foreach ($carts as $cart) {

                    $variant = $cart->variant;

                    if (!$variant) continue;

                    if ($variant->stock_quantity < $cart->quantity) {
                        DB::rollBack();
                        return back()->with(
                            'error',
                            'Sản phẩm "' . $variant->product->name . '" không đủ tồn kho.'
                        );
                    }

                    $subtotal += $variant->price * $cart->quantity;

                    $items->push([
                        'variant'  => $variant,
                        'quantity' => $cart->quantity
                    ]);
                }
            }

            /**
             * =================================================
             * TẠO ORDER
             * =================================================
             */
            $order = Order::create([
                'user_id'  => $userId,
                'subtotal' => $subtotal,
                'discount' => 0,
                'total'    => $subtotal,
                'status'   => Order::STATUS_PENDING,

                'receiver_name'    => $request->receiver_name,
                'receiver_phone'   => $request->receiver_phone,
                'receiver_address' => $request->receiver_address,
                'note'             => $request->note,

                'payment_method' => $request->payment_method,
                'payment_status' => Order::PAYMENT_UNPAID,
            ]);

            /**
             * =================================================
             * ORDER ITEMS + TRỪ KHO
             * =================================================
             */
            foreach ($items as $item) {

                $variant = $item['variant'];
                $qty = $item['quantity'];

                OrderItem::create([
                    'order_id'   => $order->id,
                    'variant_id' => $variant->id,
                    'price'      => $variant->price,
                    'cost_price' => $variant->cost_price,
                    'quantity'   => $qty,
                ]);

                // COD trừ kho ngay
                if ($request->payment_method === 'cod') {
                    $variant->decrement('stock_quantity', $qty);
                }
            }

            /**
             * =================================================
             * DỌN DỮ LIỆU
             * =================================================
             */
            if ($isBuyNow) {
                session()->forget('buy_now');
            } else {
                Cart::where('user_id', $userId)->delete();
            }

            DB::commit();

            /**
             * =================================================
             * REDIRECT
             * =================================================
             */
            if ($request->payment_method === 'vnpay') {
                return $this->createVNPay($order);
            }

            return redirect()->route('checkout.success', $order->id);
        } catch (\Exception $e) {

            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
    
    public function buyNow(Request $request)
    {
        $variant = ProductVariant::findOrFail($request->variant_id);

        if ($variant->stock_quantity <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm đã hết hàng'
            ]);
        }

        // Xóa giỏ nếu cần (tránh nhầm)
        session()->forget('buy_now');

        session([
            'buy_now' => [
                'variant_id' => $variant->id,
                'quantity'   => 1
            ]
        ]);

        return response()->json([
            'success' => true,
            'redirect' => route('checkout.index')
        ]);
    }
    /**
     * Tạo link thanh toán VNPay
     */
    private function createVNPay($order)
    {
        $vnp_TmnCode = config('vnpay.tmn_code');
        $vnp_HashSecret = config('vnpay.hash_secret');
        $vnp_Url = config('vnpay.url');
        $vnp_Returnurl = config('vnpay.return_url');

        $vnp_TxnRef = $order->id;
        $vnp_OrderInfo = "Thanh toan don hang #" . $order->id;
        $vnp_Amount = $order->total * 100;
        $vnp_IpAddr = request()->ip();

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => "vn",
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => "billpayment",
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        ];

        // Sắp xếp theo key
        ksort($inputData);

        $query = '';
        $hashdata = '';

        foreach ($inputData as $key => $value) {
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
            $hashdata .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        // Xóa dấu & cuối
        $query = rtrim($query, '&');
        $hashdata = rtrim($hashdata, '&');

        // Tạo chữ ký
        $vnp_SecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

        $paymentUrl = $vnp_Url . "?" . $query . '&vnp_SecureHash=' . $vnp_SecureHash;

        return redirect()->away($paymentUrl);
    }

    public function vnpayReturn(Request $request)
    {
        $vnp_HashSecret = config('vnpay.hash_secret');

        // ================= LẤY DỮ LIỆU =================
        $inputData = $request->all();

        if (!isset($inputData['vnp_SecureHash'])) {
            return redirect()->route('home')
                ->with('error', 'Thiếu dữ liệu VNPay');
        }

        $vnp_SecureHash = $inputData['vnp_SecureHash'];

        // Xóa hash khỏi dữ liệu để tạo lại chữ ký
        unset($inputData['vnp_SecureHash']);
        unset($inputData['vnp_SecureHashType']);

        // ================= TẠO HASH ĐÚNG CHUẨN VNPAY =================
        ksort($inputData);

        $hashData = '';
        $i = 0;

        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&';
            }
            $hashData .= urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        // ================= KIỂM TRA CHỮ KÝ =================
        if ($secureHash !== $vnp_SecureHash) {
            return redirect()->route('home')
                ->with('error', 'Chữ ký VNPay không hợp lệ');
        }

        // ================= LẤY THÔNG TIN =================
        $orderId       = $request->vnp_TxnRef;
        $responseCode  = $request->vnp_ResponseCode;
        $transactionNo = $request->vnp_TransactionNo ?? null;

        $order = Order::with('items.variant')->find($orderId);

        if (!$order) {
            return redirect()->route('home')
                ->with('error', 'Đơn hàng không tồn tại');
        }

        // ================= THANH TOÁN THÀNH CÔNG =================
        if ($responseCode === '00') {

            DB::beginTransaction();

            try {
                // Tránh xử lý lại nếu đã thanh toán trước đó
                if ($order->payment_status != Order::PAYMENT_PAID) {

                    foreach ($order->items as $item) {
                        if ($item->variant) {
                            $item->variant->decrement('stock_quantity', $item->quantity);
                        }
                    }

                    $order->update([
                        'payment_status'   => Order::PAYMENT_PAID,
                        'payment_method'   => 'vnpay',
                    ]);
                }

                DB::commit();

                return redirect()
                    ->route('checkout.success', $order->id)
                    ->with('success', 'Thanh toán VNPay thành công!');
            } catch (\Exception $e) {

                DB::rollBack();

                return redirect()
                    ->route('checkout.success', $order->id)
                    ->with('error', 'Lỗi khi cập nhật đơn hàng');
            }
        }

        // ================= THANH TOÁN THẤT BẠI =================
        DB::beginTransaction();

        try {
            $order->update([
                'payment_status' => Order::PAYMENT_FAILED
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }

        return redirect()
            ->route('checkout.success', $order->id)
            ->with('error', 'Thanh toán thất bại hoặc bị huỷ.');
    }

    public function success(Order $order)
    {
        // Bảo mật: chỉ chủ đơn được xem
        if ($order->user_id != Auth::id()) {
            abort(403);
        }

        return view('frontend.checkout.success', compact('order'));
    }

    /**
     * Huỷ đơn
     */
    public function cancel($id)
    {
        $order = Order::with('items.variant')
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($order->status != Order::STATUS_PENDING) {
            return back()->with('error', 'Không thể huỷ đơn này.');
        }

        DB::beginTransaction();

        try {
            foreach ($order->items as $item) {
                $item->variant->increment('stock_quantity', $item->quantity);
            }

            $order->update([
                'status' => Order::STATUS_CANCELLED
            ]);

            DB::commit();

            return back()->with('success', 'Đã huỷ đơn hàng.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Huỷ đơn thất bại.');
        }
    }
}