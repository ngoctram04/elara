<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\RefundCompletedMail;
use App\Notifications\SystemNotification;

class RefundController extends Controller
{

    /**
     * Danh sách yêu cầu hoàn tiền
     */
    public function index(Request $request)
    {
        $query = RefundRequest::with(['user', 'media', 'order']);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('order_id', $search)
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->sort == 'old') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $refunds = $query->paginate(10)->withQueryString();

        return view('admin.refunds.index', compact('refunds'));
    }


    /**
     * APPROVE
     */
    public function approve($id)
    {
        $refund = null;

        DB::transaction(function () use ($id, &$refund) {

            $refund = RefundRequest::with('order.user')
                ->findOrFail($id);

            $refund->update([
                'status' => 'approved'
            ]);

            $refund->order->update([
                'status' => Order::STATUS_RETURNED
            ]);
        });

        // 🔔 NOTIFY
        if ($refund && $refund->order->user) {
            $refund->order->user->notify(new SystemNotification([
                'title' => 'Yêu cầu hoàn tiền được chấp nhận',
                'message' => 'Đơn #' . $refund->order->id . ' đã được duyệt hoàn tiền',
                'url' => route('orders.show', $refund->order->id),
                'type' => 'refund'
            ]));
        }

        return back()->with('success', 'Đã chấp nhận yêu cầu đổi trả');
    }


    /**
     * REJECT
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_note' => 'required|string|max:1000'
        ]);

        $refund = RefundRequest::with('user', 'order')->findOrFail($id);

        $refund->update([
            'status' => 'rejected',
            'admin_note' => $request->admin_note
        ]);

        // 🔔 NOTIFY
        if ($refund->user) {
            $refund->user->notify(new SystemNotification([
                'title' => 'Yêu cầu hoàn tiền bị từ chối',
                'message' => $request->admin_note,
                'url' => route('orders.show', $refund->order_id),
                'type' => 'refund'
            ]));
        }

        return back()->with('success', 'Đã từ chối yêu cầu hoàn tiền');
    }


    /**
     * REFUNDED
     */
    public function refunded($id)
    {
        $refund = null;

        DB::transaction(function () use ($id, &$refund) {

            $refund = RefundRequest::with('order.user')
                ->findOrFail($id);

            $order = $refund->order;

            $refund->update([
                'status' => 'refunded'
            ]);

            $order->update([
                'payment_status' => Order::PAYMENT_REFUNDED
            ]);
        });

        // 🔔 NOTIFICATION
        if ($refund && $refund->order->user) {
            $refund->order->user->notify(new SystemNotification([
                'title' => 'Đã hoàn tiền',
                'message' => 'Đơn #' . $refund->order->id . ' đã được hoàn tiền thành công',
                'url' => route('orders.show', $refund->order->id),
                'type' => 'refund'
            ]));
        }

        // 📧 EMAIL
        if ($refund && $refund->order->user && $refund->order->user->email) {
            Mail::to($refund->order->user->email)
                ->send(new RefundCompletedMail(
                    $refund->order,
                    $refund->order->grand_total
                ));
        }

        return back()->with('success', 'Đã hoàn tiền và gửi thông báo cho khách');
    }
}