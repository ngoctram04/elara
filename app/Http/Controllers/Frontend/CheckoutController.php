<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;

class CheckoutController extends Controller
{
    /**
     * Trang checkout
     */
    public function index()
    {
        $carts = Cart::with('variant.product')
            ->where('user_id', Auth::id())
            ->get();

        if ($carts->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Giỏ hàng của bạn đang trống.');
        }

        $subtotal = $carts->sum(
            fn($cart) =>
            $cart->variant->price * $cart->quantity
        );

        return view('frontend.checkout.index', compact('carts', 'subtotal'));
    }

    /**
     * Đặt hàng
     */
    public function store(Request $request)
    {
        $request->validate([
            'receiver_name'    => 'required|string|max:255',
            'receiver_phone'   => 'required|string|max:20',
            'receiver_address' => 'required|string|max:500',
            'payment_method'   => 'required',
        ]);

        $userId = Auth::id();

        $carts = Cart::with('variant.product')
            ->where('user_id', $userId)
            ->get();

        if ($carts->isEmpty()) {
            return back()->with('error', 'Giỏ hàng trống.');
        }

        DB::beginTransaction();

        try {
            // 1. Kiểm tra tồn kho + tính tiền
            $subtotal = 0;

            foreach ($carts as $cart) {
                $variant = $cart->variant;

                if ($variant->stock_quantity < $cart->quantity) {
                    DB::rollBack();
                    return back()->with(
                        'error',
                        'Sản phẩm "' . $variant->product->name . '" không đủ tồn kho.'
                    );
                }

                $subtotal += $variant->price * $cart->quantity;
            }

            $discount = 0;
            $total = $subtotal - $discount;

            // 2. Tạo đơn hàng
            $order = Order::create([
                'user_id'  => $userId,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total'    => $total,
                'status'   => Order::STATUS_PENDING,

                'receiver_name'    => $request->receiver_name,
                'receiver_phone'   => $request->receiver_phone,
                'receiver_address' => $request->receiver_address,
                'note'             => $request->note,

                'payment_method' => $request->payment_method,
                'payment_status' => Order::PAYMENT_UNPAID,
            ]);

            // 3. Order items + trừ tồn
            foreach ($carts as $cart) {
                $variant = $cart->variant;

                OrderItem::create([
                    'order_id'   => $order->id,
                    'variant_id' => $variant->id,
                    'price'      => $variant->price,
                    'cost_price' => $variant->cost_price,
                    'quantity'   => $cart->quantity,
                ]);

                $variant->decrement('stock_quantity', $cart->quantity);
            }

            // 4. Xóa giỏ hàng
            Cart::where('user_id', $userId)->delete();

            DB::commit();

            /**
             * 5. Xử lý theo phương thức thanh toán
             */
            if ($request->payment_method == 'cod') {
                return redirect()->route('checkout.success', $order->id);
            }

            if ($request->payment_method == 'bank') {
                return redirect()->route('checkout.success', $order->id)
                    ->with('success', 'Vui lòng chuyển khoản để hoàn tất đơn hàng.');
            }

            if ($request->payment_method == 'vnpay') {
                return $this->createVNPay($order);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra khi đặt hàng.');
        }
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

        ksort($inputData);

        $query = "";
        $hashdata = "";

        foreach ($inputData as $key => $value) {
            $hashdata .= urlencode($key) . "=" . urlencode($value) . '&';
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $hashdata = rtrim($hashdata, '&');
        $query = rtrim($query, '&');

        $vnp_SecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

        $paymentUrl = $vnp_Url . "?" . $query . '&vnp_SecureHash=' . $vnp_SecureHash;

        return redirect($paymentUrl);
    }

    /**
     * VNPay trả kết quả
     */
    public function vnpayReturn(Request $request)
    {
        $orderId = $request->vnp_TxnRef;
        $responseCode = $request->vnp_ResponseCode;

        $order = Order::find($orderId);

        if (!$order) {
            return redirect()->route('home')
                ->with('error', 'Đơn hàng không tồn tại');
        }

        if ($responseCode == '00') {
            $order->update([
                'payment_status' => Order::PAYMENT_PAID
            ]);

            return redirect()->route('checkout.success', $order->id)
                ->with('success', 'Thanh toán VNPay thành công!');
        }

        return redirect()->route('checkout.success', $order->id)
            ->with('error', 'Thanh toán thất bại hoặc bị huỷ.');
    }

    /**
     * Trang thành công
     */
    public function success(Order $order)
    {
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