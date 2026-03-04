<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
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
     * Chấp nhận hoàn tiền
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
            | Hoàn tồn kho
            |--------------------------------------------------------------------------
            */

            foreach ($order->items as $item) {

                $variant = $item->variant;

                if (!$variant) continue;

                $variant->stock_quantity += $item->quantity;
                $variant->sold_quantity -= $item->quantity;

                if ($variant->sold_quantity < 0) {
                    $variant->sold_quantity = 0;
                }

                $variant->save();
            }


            /*
            |--------------------------------------------------------------------------
            | Huỷ đơn
            |--------------------------------------------------------------------------
            */

            $order->update([
                'status' => 4
            ]);
        });

        return back()->with('success', 'Đã chấp nhận hoàn tiền');
    }



    /**
     * Từ chối hoàn tiền
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
     * Xác nhận đã hoàn tiền
     */
    public function refunded($id)
    {
        $refund = RefundRequest::with('order.user')->findOrFail($id);

        $refund->update([
            'status' => 'refunded'
        ]);

        $order = $refund->order;

        Mail::to($order->user->email)
            ->send(new RefundCompletedMail(
                $order,
                $order->grand_total
            ));

        return back()->with('success', 'Đã xác nhận hoàn tiền và gửi email cho khách');
    }
}