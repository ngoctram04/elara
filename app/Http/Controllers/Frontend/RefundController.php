<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\RefundRequest;
use App\Models\RefundMedia;
use App\Models\User;
use App\Mail\RefundRequestMail;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class RefundController extends Controller
{
    /**
     * Kiểm tra đơn có còn trong thời hạn yêu cầu hoàn tiền hay không
     */
    protected function ensureRefundAllowed(Order $order): void
    {
        if ((int) $order->user_id !== (int) Auth::id()) {
            abort(403);
        }

        if ((int) $order->status !== (int) Order::STATUS_COMPLETED) {
            throw new \Exception('Chỉ đơn đã hoàn tất mới được yêu cầu trả hàng / hoàn tiền.');
        }

        if (!(bool) $order->customer_confirmed) {
            throw new \Exception('Bạn cần xác nhận đã nhận hàng trước khi yêu cầu trả hàng / hoàn tiền.');
        }

        if (!$order->received_at) {
            throw new \Exception('Không xác định được thời điểm nhận hàng.');
        }

        if ($order->refundRequest) {
            throw new \Exception('Đơn hàng này đã gửi yêu cầu hoàn tiền.');
        }

        $deadline = $order->received_at->copy()->addDays(3);

        if (now()->gt($deadline)) {
            throw new \Exception('Đơn hàng đã quá thời hạn 3 ngày để yêu cầu trả hàng / hoàn tiền.');
        }
    }

    /**
     * Form yêu cầu hoàn tiền
     */
    public function create(Order $order)
    {
        try {
            $order->load([
                'refundRequest',
                'items.variant.product',
                'items.variant.mainImage',
            ]);

            $this->ensureRefundAllowed($order);

            return view('frontend.refund.create', compact('order'));
        } catch (\Exception $e) {
            return redirect()
                ->route('orders.show', $order->id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Gửi yêu cầu hoàn tiền
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_id'          => 'required|exists:orders,id',
            'reason'            => 'required|string|max:1000',
            'items'             => 'required|array|min:1',
            'items.*'           => 'exists:order_items,id',
            'item_conditions'   => 'nullable|array',
            'item_conditions.*' => 'nullable|in:sealed,broken',
            'item_notes'        => 'nullable|array',
            'item_notes.*'      => 'nullable|string|max:255',
            'images.*'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'video'             => 'nullable|mimes:mp4,mov,avi|max:20480',
        ]);

        DB::beginTransaction();

        try {
            $order = Order::with([
                'items.variant.product',
                'refundRequest',
            ])->findOrFail($request->order_id);

            $this->ensureRefundAllowed($order);

            $validItemIds = $order->items->pluck('id')->map(fn($id) => (int) $id)->toArray();

            foreach ($request->items as $itemId) {
                if (!in_array((int) $itemId, $validItemIds, true)) {
                    throw new \Exception('Sản phẩm không hợp lệ.');
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Ghép lý do chung + tình trạng từng sản phẩm
            |--------------------------------------------------------------------------
            */
            $reason = trim((string) $request->reason);
            $itemDescriptions = [];

            foreach ($request->items as $itemId) {
                $itemId = (int) $itemId;
                $condition = $request->input("item_conditions.$itemId");
                $note = trim((string) $request->input("item_notes.$itemId"));

                $conditionText = match ($condition) {
                    'sealed' => 'Còn nguyên seal',
                    'broken' => 'Bị vỡ',
                    default  => 'Không xác định',
                };

                $line = "- Sản phẩm ID {$itemId}: {$conditionText}";

                if ($note !== '') {
                    $line .= " | Ghi chú: {$note}";
                }

                $itemDescriptions[] = $line;
            }

            $fullReason = $reason;

            if (!empty($itemDescriptions)) {
                $fullReason .= "\n\nChi tiết sản phẩm khách chọn:\n" . implode("\n", $itemDescriptions);
            }

            /*
            |--------------------------------------------------------------------------
            | Tạo yêu cầu hoàn tiền
            |--------------------------------------------------------------------------
            */
            $refund = RefundRequest::create([
                'order_id' => $order->id,
                'user_id'  => Auth::id(),
                'reason'   => $fullReason,
                'status'   => RefundRequest::STATUS_PENDING,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Lưu item được chọn
            |--------------------------------------------------------------------------
            */
            if (method_exists($refund, 'items')) {
                $syncData = [];

                foreach ($order->items as $item) {
                    if (!in_array((int) $item->id, array_map('intval', $request->items), true)) {
                        continue;
                    }

                    $condition = $request->input("item_conditions.{$item->id}", 'sealed');
                    $note = trim((string) $request->input("item_notes.{$item->id}"));

                    $dbCondition = match ($condition) {
                        'sealed' => 'sealed',
                        'broken' => 'damaged',
                        default  => 'sealed',
                    };

                    $syncData[$item->id] = [
                        'quantity'           => (int) ($item->quantity ?? 1),
                        'reason'             => $reason,
                        'condition_status'   => $dbCondition,
                        'is_sealed'          => $condition === 'sealed' ? 1 : 0,
                        'restockable'        => $condition === 'sealed' ? 1 : 0,
                        'returned_to_stock'  => 0,
                        'refund_amount'      => 0,
                        'unit_cost'          => 0,
                        'loss_amount'        => 0,
                        'note'               => $note !== '' ? $note : null,
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ];
                }

                $refund->items()->sync($syncData);
            }

            /*
            |--------------------------------------------------------------------------
            | Upload ảnh
            |--------------------------------------------------------------------------
            */
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    if ($image && $image->isValid()) {
                        $path = $image->store('refunds', 'public');

                        RefundMedia::create([
                            'refund_request_id' => $refund->id,
                            'file_path'         => $path,
                            'type'              => 'image',
                        ]);
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Upload video
            |--------------------------------------------------------------------------
            */
            if ($request->hasFile('video')) {
                $video = $request->file('video');

                if ($video && $video->isValid()) {
                    $path = $video->store('refunds', 'public');

                    RefundMedia::create([
                        'refund_request_id' => $refund->id,
                        'file_path'         => $path,
                        'type'              => 'video',
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Gửi email về admin
            |--------------------------------------------------------------------------
            */
            Mail::to(config('mail.from.address'))
                ->send(new RefundRequestMail($order, $refund));

            /*
            |--------------------------------------------------------------------------
            | Thông báo admin
            |--------------------------------------------------------------------------
            */
            User::where('role', 'admin')->each(function ($admin) use ($order) {
                $admin->notify(new SystemNotification([
                    'title'   => 'Yêu cầu hoàn tiền',
                    'message' => 'Đơn #' . $order->id . ' vừa gửi yêu cầu hoàn tiền',
                    'url'     => route('admin.refunds.index'),
                    'type'    => 'refund',
                ]));
            });

            DB::commit();

            return redirect()
                ->route('orders.show', $order->id)
                ->with('success', 'Yêu cầu hoàn tiền đã được gửi.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}