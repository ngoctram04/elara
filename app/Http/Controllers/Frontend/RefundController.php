<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\RefundRequest;
use App\Models\RefundMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\RefundRequestMail;

class RefundController extends Controller
{

    /**
     * Form yêu cầu hoàn tiền
     */
    public function create(Order $order)
    {
        // kiểm tra đúng chủ đơn
        if ($order->user_id != Auth::id()) {
            abort(403);
        }

        // chỉ đơn đã giao mới hoàn tiền
        if ($order->status != 3) {
            return back()->with('error', 'Chỉ đơn đã giao mới được yêu cầu hoàn tiền');
        }

        // kiểm tra đã gửi yêu cầu chưa
        if ($order->refundRequest) {
            return back()->with('error', 'Đơn hàng này đã gửi yêu cầu hoàn tiền');
        }

        return view('frontend.refund.create', compact('order'));
    }



    /**
     * Gửi yêu cầu hoàn tiền
     */
    public function store(Request $request)
    {

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'reason'   => 'required|string|max:1000',

            // ảnh
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',

            // video
            'video' => 'nullable|mimes:mp4,mov,avi|max:20480'
        ]);


        DB::beginTransaction();

        try {

            $order = Order::with('user')->findOrFail($request->order_id);

            if ($order->user_id != Auth::id()) {
                abort(403);
            }


            /*
            |------------------------------------
            | Tạo yêu cầu hoàn tiền
            |------------------------------------
            */

            $refund = RefundRequest::create([
                'order_id' => $order->id,
                'user_id'  => Auth::id(),
                'reason'   => $request->reason,
                'status'   => 'pending'
            ]);



            /*
            |------------------------------------
            | Upload ảnh
            |------------------------------------
            */

            if ($request->hasFile('images')) {

                foreach ($request->file('images') as $image) {

                    if ($image->isValid()) {

                        $path = $image->store('refunds', 'public');

                        RefundMedia::create([
                            'refund_request_id' => $refund->id,
                            'file_path' => $path,
                            'type' => 'image'
                        ]);
                    }
                }
            }



            /*
            |------------------------------------
            | Upload video
            |------------------------------------
            */

            if ($request->hasFile('video')) {

                $video = $request->file('video');

                if ($video->isValid()) {

                    $path = $video->store('refunds', 'public');

                    RefundMedia::create([
                        'refund_request_id' => $refund->id,
                        'file_path' => $path,
                        'type' => 'video'
                    ]);
                }
            }



            /*
            |------------------------------------
            | Gửi email admin
            |------------------------------------
            */

            Mail::to(config('mail.from.address'))
                ->send(new RefundRequestMail($order, $refund));


            DB::commit();


            return redirect()
                ->route('orders.show', $order->id)
                ->with('success', 'Yêu cầu hoàn tiền đã được gửi');
        } catch (\Exception $e) {

            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }
}