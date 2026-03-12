<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\RefundCompletedMail;

class RefundController extends Controller
{

    /**
     * Danh sách yêu cầu hoàn tiền
     */
    public function index(Request $request)
    {
        $query = RefundRequest::with(['user', 'media', 'order']);

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('order_id', $search)
                    ->orWhereHas('user', function ($u) use ($search) {

                        $u->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER STATUS
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {

            $query->where('status', $request->status);
        }

        /*
        |--------------------------------------------------------------------------
        | SORT
        |--------------------------------------------------------------------------
        */

        if ($request->sort == 'old') {

            $query->orderBy('created_at', 'asc');
        } else {

            $query->orderBy('created_at', 'desc');
        }

        $refunds = $query->paginate(10)->withQueryString();

        return view('admin.refunds.index', compact('refunds'));
    }


    /**
     * Admin duyệt yêu cầu đổi trả
     */
    public function approve($id)
    {

        DB::transaction(function () use ($id) {

            $refund = RefundRequest::with('order.items.variant')
                ->findOrFail($id);

            $order = $refund->order;

            /*
            |--------------------------------------------------------------------------
            | Update trạng thái refund
            |--------------------------------------------------------------------------
            */

            $refund->update([
                'status' => 'approved'
            ]);

            /*
            |--------------------------------------------------------------------------
            | Chuyển đơn sang trạng thái ĐỔI TRẢ
            |--------------------------------------------------------------------------
            */

            $order->update([
                'status' => Order::STATUS_RETURNED
            ]);
        });

        return back()->with('success', 'Đã chấp nhận yêu cầu đổi trả');
    }


    /**
     * Admin từ chối yêu cầu
     */
    public function reject(Request $request, $id)
    {

        $request->validate([
            'admin_note' => 'required|string|max:1000'
        ]);

        $refund = RefundRequest::findOrFail($id);

        $refund->update([
            'status' => 'rejected',
            'admin_note' => $request->admin_note
        ]);

        return back()->with('success', 'Đã từ chối yêu cầu hoàn tiền');
    }


    /**
     * Admin xác nhận đã hoàn tiền
     */
    public function refunded($id)
    {

        DB::transaction(function () use ($id) {

            $refund = RefundRequest::with('order.user')
                ->findOrFail($id);

            $order = $refund->order;

            /*
            |--------------------------------------------------------------------------
            | Update refund status
            |--------------------------------------------------------------------------
            */

            $refund->update([
                'status' => 'refunded'
            ]);

            /*
            |--------------------------------------------------------------------------
            | Cập nhật trạng thái thanh toán
            |--------------------------------------------------------------------------
            */

            $order->update([
                'payment_status' => Order::PAYMENT_REFUNDED
            ]);

            /*
            |--------------------------------------------------------------------------
            | Gửi email thông báo
            |--------------------------------------------------------------------------
            */

            if ($order->user && $order->user->email) {

                Mail::to($order->user->email)
                    ->send(new RefundCompletedMail(
                        $order,
                        $order->grand_total
                    ));
            }
        });

        return back()->with('success', 'Đã xác nhận hoàn tiền và gửi email cho khách');
    }
}