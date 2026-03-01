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
use App\Models\Promotion;
use Illuminate\Support\Facades\Log;
use App\Models\UserAddress;
class CheckoutController extends Controller
{
    /**
     * Trang checkout
     */
    public function index()
    {
        $userId = Auth::id();
        $user = Auth::user();

        $carts = collect();
        $subtotal = 0;

        /**
         * =================================
         * 1. BUY NOW
         * =================================
         */
        if (session()->has('buy_now')) {

            $buyNow = session('buy_now');

            $variant = ProductVariant::with([
                'product:id,name',
                'mainImage'
            ])->find($buyNow['variant_id']);

            if (!$variant) {
                session()->forget('buy_now');
                return redirect()->route('cart.index');
            }

            $price = $variant->final_price ?? $variant->price;

            $carts = collect([
                (object)[
                    'variant'  => $variant,
                    'quantity' => $buyNow['quantity']
                ]
            ]);

            $subtotal = $price * $buyNow['quantity'];
        }

        /**
         * =================================
         * 2. CHECKOUT TỪ CART
         * =================================
         */
        elseif (session()->has('checkout_items')) {

            $variantIds = session('checkout_items');

            $carts = Cart::with([
                'variant:id,product_id,attribute_name,attribute_value,price',
                'variant.mainImage',
                'variant.product:id,name'
            ])
                ->where('user_id', $userId)
                ->whereIn('variant_id', $variantIds)
                ->get();

            if ($carts->isEmpty()) {
                session()->forget('checkout_items');
                return redirect()
                    ->route('cart.index')
                    ->with('error', 'Không có sản phẩm hợp lệ.');
            }

            $subtotal = $carts->sum(function ($cart) {
                $price = $cart->variant->final_price ?? $cart->variant->price;
                return $price * $cart->quantity;
            });
        }

        /**
         * =================================
         * 3. TOÀN BỘ GIỎ
         * =================================
         */
        else {

            $carts = Cart::with([
                'variant:id,product_id,attribute_name,attribute_value,price',
                'variant.mainImage',
                'variant.product:id,name'
            ])
                ->where('user_id', $userId)
                ->get();

            if ($carts->isEmpty()) {
                return redirect()
                    ->route('cart.index')
                    ->with('error', 'Giỏ hàng của bạn đang trống.');
            }

            $subtotal = $carts->sum(function ($cart) {
                $price = $cart->variant->final_price ?? $cart->variant->price;
                return $price * $cart->quantity;
            });
        }

        /**
         * =================================
         * 4. PROMOTION
         * =================================
         */
        $discount = session('promotion_discount', 0);

        // Total trước sinh nhật (QUAN TRỌNG)
        $totalBeforeBirthday = max(0, $subtotal - $discount);

        /**
         * =================================
         * 5. BIRTHDAY DISCOUNT (preview)
         * =================================
         */
        $birthdayDiscount = 0;

        if ($user) {
            $birthdayDiscount = $this->getBirthdayBenefit($user, $totalBeforeBirthday);
        }

        // Lưu để Blade hiển thị
        session()->put('birthday_discount', $birthdayDiscount);

        // Total sau sinh nhật
        $total = max(0, $totalBeforeBirthday - $birthdayDiscount);

        /**
         * =================================
         * 6. ĐỊA CHỈ
         * =================================
         */
        $addresses = UserAddress::where('user_id', $userId)
            ->orderByDesc('is_default')
            ->get();

        $defaultAddress = $addresses->first();

        /**
         * =================================
         * 7. SHIPPING
         * Freeship xét theo total TRƯỚC sinh nhật
         * =================================
         */
        $shippingFee = 0;

        if ($defaultAddress) {

            $province = mb_strtolower($defaultAddress->province);

            // Ship theo khu vực
            if ($province === 'vĩnh long') {
                $shippingFee = 15000;
            } else {

                $mienTay = [
                    'cần thơ',
                    'bến tre',
                    'trà vinh',
                    'sóc trăng',
                    'hậu giang',
                    'đồng tháp',
                    'an giang',
                    'kiên giang',
                    'cà mau',
                    'bạc liêu',
                    'tiền giang'
                ];

                $shippingFee = 35000;

                foreach ($mienTay as $t) {
                    if (str_contains($province, $t)) {
                        $shippingFee = 25000;
                        break;
                    }
                }
            }

            // Ưu đãi theo hạng (xét TRƯỚC sinh nhật)
            if ($user) {

                if ($user->member_level === 'gold' && $totalBeforeBirthday >= 300000) {
                    $shippingFee = 0;
                }

                if ($user->member_level === 'diamond') {
                    $shippingFee = 0;
                }
            }
        }

        /**
         * =================================
         * 8. GRAND TOTAL
         * =================================
         */
        $grandTotal = $total + $shippingFee;

        /**
         * =================================
         * 9. VIEW
         * =================================
         */
        return view('frontend.checkout.index', compact(
            'carts',
            'subtotal',
            'discount',
            'total',
            'shippingFee',
            'grandTotal',
            'addresses',
            'defaultAddress'
        ));
    }

    public function fromCart(Request $request)
    {
        session()->forget('buy_now');

        $variantIds = $request->variant_ids ?? [];

        if (empty($variantIds)) {
            return back()->with('error', 'Vui lòng chọn sản phẩm.');
        }

        session([
            'checkout_items' => $variantIds,
            'promotion_code' => $request->promotion_code // ⭐ QUAN TRỌNG
        ]);

        return redirect()->route('checkout.index');
    }
    public function store(Request $request)
    {
        $request->validate([
            'address_id'     => 'required|exists:user_addresses,id',
            'payment_method' => 'required|in:cod,vnpay',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $userId = $user->id;

        // =================================================
        // 1. LẤY ĐỊA CHỈ
        // =================================================
        $address = UserAddress::where('id', $request->address_id)
            ->where('user_id', $userId)
            ->first();

        if (!$address) {
            return back()->with('error', 'Địa chỉ không hợp lệ.');
        }

        $fullAddress = $address->address_detail . ', ' .
            $address->ward . ', ' .
            $address->district . ', ' .
            $address->province;

        DB::beginTransaction();

        try {

            $subtotal = 0;
            $items = collect();
            $isBuyNow = false;
            $variantIds = [];

            // =================================================
            // 2. BUY NOW
            // =================================================
            if (session()->has('buy_now')) {

                $buyNow = session('buy_now');
                $variant = ProductVariant::find($buyNow['variant_id']);

                if (!$variant) {
                    session()->forget('buy_now');
                    return back()->with('error', 'Sản phẩm không tồn tại.');
                }

                $isBuyNow = true;

                if ($variant->stock_quantity < $buyNow['quantity']) {
                    return back()->with('error', 'Sản phẩm không đủ tồn kho.');
                }

                $price = $variant->final_price ?? $variant->price;
                $subtotal = $price * $buyNow['quantity'];

                $items->push([
                    'variant'  => $variant,
                    'quantity' => $buyNow['quantity'],
                    'price'    => $price
                ]);
            }

            // =================================================
            // 3. CHECKOUT TỪ CART
            // =================================================
            if (!$isBuyNow) {

                session()->forget('buy_now');
                $variantIds = session('checkout_items', []);

                if (empty($variantIds)) {
                    DB::rollBack();
                    return back()->with('error', 'Vui lòng chọn sản phẩm.');
                }

                $carts = Cart::with('variant.product')
                ->where('user_id', $userId)
                    ->whereIn('variant_id', $variantIds)
                    ->get();

                if ($carts->isEmpty()) {
                    DB::rollBack();
                    return back()->with('error', 'Không có sản phẩm hợp lệ.');
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

                    $price = $variant->final_price ?? $variant->price;
                    $subtotal += $price * $cart->quantity;

                    $items->push([
                        'variant'  => $variant,
                        'quantity' => $cart->quantity,
                        'price'    => $price
                    ]);
                }
            }

            // =================================================
            // 4. PROMOTION
            // =================================================
            $discount = 0;
            $promotionCode = $request->promotion_code ?? session('promotion_code');
            $promotion = null;

            if ($promotionCode) {

                $promotion = Promotion::where('code', $promotionCode)
                    ->where('is_active', 1)
                    ->where('type', 'order')
                    ->where(function ($q) {
                        $q->whereNull('start_date')
                        ->orWhere('start_date', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('end_date')
                        ->orWhere('end_date', '>=', now());
                    })
                    ->first();

                if (
                    $promotion &&
                    $promotion->usage_limit !== null &&
                    $promotion->used_count >= $promotion->usage_limit
                ) {
                    $promotion = null;
                }

                if ($promotion) {

                    $minimum = (float) ($promotion->min_order_value ?? 0);

                    if ($subtotal >= $minimum) {

                        if ($promotion->discount_type === 'percent') {
                            $discount = $subtotal * ($promotion->discount_value / 100);
                        } else {
                            $discount = $promotion->discount_value;
                        }

                        if ($promotion->max_discount) {
                            $discount = min($discount, $promotion->max_discount);
                        }

                        $discount = min($discount, $subtotal);
                    } else {
                        $promotion = null;
                    }
                }
            }

            // =================================================
            // 5. TÍNH TOTAL SAU PROMOTION
            // =================================================
            $total = max(0,
                $subtotal - $discount
            );

            // =================================================
            // 6. TOTAL TRƯỚC SINH NHẬT
            // =================================================
            $totalBeforeBirthday = max(0, $subtotal - $discount);

            // =================================================
            // 7. SHIPPING (xét theo total trước sinh nhật)
            // =================================================
            $shippingFee = $this->calculateShippingFee(
                    $address->province,
                    $totalBeforeBirthday
                );

            // Membership rule
            if ($user->member_level === 'gold' && $totalBeforeBirthday >= 300000) {
                $shippingFee = 0;
            }

            if ($user->member_level === 'diamond') {
                $shippingFee = 0;
            }

            // =================================================
            // 8. BIRTHDAY DISCOUNT (giảm tiền, KHÔNG ảnh hưởng freeship)
            // =================================================
            $birthdayDiscount = $this->getBirthdayBenefit($user, $totalBeforeBirthday);

            $total = max(
                0,
                $totalBeforeBirthday - $birthdayDiscount
            );

            if ($birthdayDiscount > 0) {
                session()->put('birthday_discount', $birthdayDiscount);

                // đánh dấu đã dùng năm nay
                $user->birthday_discount_year = now()->year;
                $user->save();
            }

            // =================================================
            // 7. GRAND TOTAL
            // =================================================
            $grandTotal = $total + $shippingFee;

            // =================================================
            // 8. CREATE ORDER
            // =================================================
            $order = Order::create([
                'user_id'       => $userId,
                'subtotal'      => round($subtotal),
                'discount'      => round($discount + $birthdayDiscount),
                'shipping_fee'  => round($shippingFee),
                'total'         => round($total),
                'grand_total'   => round($grandTotal),

                'promotion_code' => $promotion ? $promotionCode : null,
                'status'         => Order::STATUS_PENDING,

                'receiver_name'    => $address->receiver_name,
                'receiver_phone'   => $address->phone,
                'receiver_address' => $fullAddress,
                'note'             => $request->note,

                'payment_method' => $request->payment_method,
                'payment_status' => Order::PAYMENT_UNPAID,
            ]);

            if ($promotion) {
                $promotion->increment('used_count');
            }

            // =================================================
            // 9. ORDER ITEMS + TRỪ KHO
            // =================================================
            foreach ($items as $item) {

                $variant = $item['variant'];

                OrderItem::create([
                    'order_id'   => $order->id,
                    'variant_id' => $variant->id,
                    'price'      => $item['price'],
                    'cost_price' => $variant->cost_price,
                    'quantity'   => $item['quantity'],
                ]);

                $variant->decrement('stock_quantity', $item['quantity']);
            }

            // =================================================
            // 10. CLEAR SESSION + CART
            // =================================================
            session()->forget([
                'buy_now',
                'checkout_items',
                'promotion_code',
                'promotion_discount',
                'promotion_name'
            ]);

            if (!$isBuyNow && !empty($variantIds)) {
                Cart::where('user_id', $userId)
                    ->whereIn('variant_id', $variantIds)
                    ->delete();
            }

            DB::commit();

            // =================================================
            // 11. REDIRECT
            // =================================================
            if ($request->payment_method === 'vnpay') {
                return $this->createVNPay($order);
            }

            return redirect()->route('checkout.success', $order->id);
        } catch (\Exception $e) {

            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }
    private function getBirthdayBenefit($user, $amount)
    {
        if (!$user->date_of_birth) return 0;

        $today = now();
        $birthday = \Carbon\Carbon::parse($user->date_of_birth);

        // Chỉ trong tháng sinh
        if ($today->month != $birthday->month) return 0;

        // Mỗi năm 1 lần
        if ($user->birthday_discount_year == $today->year) return 0;

        $percent = match ($user->member_level) {
            'silver' => 5,
            'gold' => 10,
            'diamond' => 15,
            default => 0
        };

        return ($amount * $percent) / 100;
    }
    
    public function buyNow(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'quantity'   => 'nullable|integer|min:1'
        ]);

        $variant = ProductVariant::findOrFail($request->variant_id);

        // Lấy số lượng (mặc định = 1)
        $qty = (int) ($request->quantity ?? 1);

        // Hết hàng
        if ($variant->stock_quantity <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm đã hết hàng'
            ]);
        }

        // Vượt tồn kho
        if ($qty > $variant->stock_quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Số lượng vượt quá tồn kho'
            ]);
        }

        /**
         * ==============================
         * DỌN SESSION CŨ (QUAN TRỌNG)
         * ==============================
         */
        session()->forget([
            'buy_now',
            'checkout_items',     // tránh lẫn với cart
            'promotion_code',     // tránh áp nhầm mã cũ
            'promotion_discount',
            'promotion_name'
        ]);

        /**
         * ==============================
         * LƯU BUY NOW
         * ==============================
         */
        session([
            'buy_now' => [
                'variant_id' => $variant->id,
                'quantity'   => $qty
            ]
        ]);

        return response()->json([
            'success'  => true,
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
        $vnp_Amount = $order->grand_total * 100;
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
        // Lấy đơn của chính user
        $order = Order::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        // Không tìm thấy hoặc không phải của user
        if (!$order) {
            return back()->with('error', 'Đơn hàng không tồn tại.');
        }

        // Chỉ cho huỷ khi đang xử lý (status = 1)
        if ($order->status != Order::STATUS_PENDING) {
            return back()->with('error', 'Chỉ có thể huỷ đơn đang xử lý.');
        }

        DB::beginTransaction();

        try {
            /*
        =================================================
        CHỈ CẬP NHẬT TRẠNG THÁI
        =================================================
        Model Order::booted() sẽ tự:
        - Hoàn tồn kho
        - Không tăng đã bán
        - Nếu VNPay đã thanh toán → chuyển REFUNDED
        =================================================
        */

            $order->update([
                'status' => Order::STATUS_CANCELLED,
                'cancelled_by' => 'customer',
                'cancelled_by_user_id' => Auth::id(),
                'cancelled_at' => now()
            ]);

            DB::commit();

            return back()->with('success', 'Huỷ đơn thành công.');
        } catch (\Throwable $e) {
            DB::rollBack();

            // Log nếu cần debug
            Log::error('Cancel order error: ' . $e->getMessage());

            return back()->with('error', 'Huỷ đơn thất bại, vui lòng thử lại.');
        }
    }

    private function calculateShippingFee($province, $amount)
    {
        $province = mb_strtolower(trim($province));

        if ($province === 'vĩnh long') return 15000;

        $mienTay = [
            'cần thơ',
            'bến tre',
            'trà vinh',
            'sóc trăng',
            'hậu giang',
            'đồng tháp',
            'an giang',
            'kiên giang',
            'cà mau',
            'bạc liêu',
            'tiền giang'
        ];

        if (in_array($province, $mienTay)) return 25000;

        return 35000;
    }
}