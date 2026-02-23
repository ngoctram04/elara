<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class OrderController extends Controller
{
    /**
     * Danh sách đơn của user
     */
    public function index()
    {
        $orders = Order::with('items.variant.product')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('frontend.orders.index', compact('orders'));
    }

    /**
     * Chi tiết đơn
     */
    public function show($id)
    {
        $order = Order::with('items.variant.product')
            ->where('id', $id)
            ->where('user_id', Auth::id()) // chỉ xem đơn của mình
            ->firstOrFail();

        return view('frontend.orders.show', compact('order'));
    }
}