<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderCreatedMail;
use App\Mail\OrderCompletedMail;
use App\Models\Promotion;
class Order extends Model

{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'subtotal',
        'discount',
        'voucher_discount',     // thêm
        'birthday_discount',    // thêm
        'shipping_fee',
        'total',
        'grand_total',
        'promotion_code',
        'status',
        'receiver_name',
        'receiver_phone',
        'receiver_address',
        'note',
        'payment_method',
        'payment_status',
        'transaction_code',
        'cancel_reason',
        'cancelled_by',
        'cancelled_by_user_id',
        'delivered_at',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'discount' => 'integer',
        'voucher_discount' => 'integer',
        'birthday_discount' => 'integer',
        'shipping_fee' => 'integer',
        'total' => 'integer',
        'grand_total' => 'integer',
        'status' => 'integer',
        'payment_status' => 'integer',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /*
    |--------------------------------
    | ORDER STATUS
    |--------------------------------
    */
    const STATUS_PENDING    = 1; // Đang xử lý
    const STATUS_PROCESSING = 2; // Đang giao
    const STATUS_COMPLETED  = 3; // Đã giao
    const STATUS_CANCELLED  = 4; // Đã huỷ

    /*
    |--------------------------------
    | PAYMENT STATUS
    |--------------------------------
    */
    const PAYMENT_UNPAID = 0;
    const PAYMENT_PAID   = 1;
    const PAYMENT_FAILED = 2;
    const PAYMENT_REFUNDED = 3;
    /*
    |--------------------------------
    | RELATIONSHIPS
    |--------------------------------
    */

    // Người đặt
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Người huỷ
    public function cancelledByUser()
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    // Sản phẩm trong đơn
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function promotions()
    {
        return $this->hasMany(OrderPromotion::class, 'order_id');
    }

    /*
    |--------------------------------
    | ACCESSORS - ORDER STATUS
    |--------------------------------
    */
    public function getStatusNameAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING    => 'Đang xử lý',
            self::STATUS_PROCESSING => 'Đang giao',
            self::STATUS_COMPLETED  => 'Đã giao',
            self::STATUS_CANCELLED  => 'Đã huỷ',
            default => 'Không xác định',
        };
    }

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING    => 'warning',
            self::STATUS_PROCESSING => 'primary',
            self::STATUS_COMPLETED  => 'success',
            self::STATUS_CANCELLED  => 'danger',
            default => 'secondary',
        };
    }

    /*
    |--------------------------------
    | ACCESSORS - PAYMENT
    |--------------------------------
    */
    public function getPaymentMethodNameAttribute()
    {
        return match ($this->payment_method) {
            'cod'   => 'Thanh toán khi nhận hàng',
            'bank'  => 'Chuyển khoản',
            'vnpay' => 'VNPay',
            default => 'Không xác định',
        };
    }

    public function getPaymentStatusNameAttribute()
    {
        // COD: đã giao = đã thanh toán
        if ($this->payment_method === 'cod') {
            return $this->status == self::STATUS_COMPLETED
                ? 'Đã thanh toán'
                : 'Chưa thanh toán';
        }

        // VNPAY
        return match ($this->payment_status) {
            self::PAYMENT_PAID     => 'Đã thanh toán',
            self::PAYMENT_FAILED   => 'Thanh toán thất bại',
            self::PAYMENT_REFUNDED => 'Đã hoàn tiền',
            default                => 'Chưa thanh toán',
        };
    }

    public function getPaymentStatusBadgeAttribute()
    {
        // COD
        if ($this->payment_method === 'cod') {
            return $this->status == self::STATUS_COMPLETED
                ? 'success'
                : 'secondary';
        }

        // VNPAY
        return match ($this->payment_status) {
            self::PAYMENT_PAID     => 'success',
            self::PAYMENT_FAILED   => 'danger',
            self::PAYMENT_REFUNDED => 'warning',
            default                => 'secondary',
        };
    }

    /*
    |--------------------------------
    | HELPERS - ORDER LOGIC
    |--------------------------------
    */

    // Có thể huỷ không?
    public function canCancel()
    {
        return $this->status == self::STATUS_PENDING;
    }

    // Đã huỷ chưa?
    public function isCancelled()
    {
        return $this->status == self::STATUS_CANCELLED;
    }

    // Đã giao chưa?
    public function isCompleted()
    {
        return $this->status == self::STATUS_COMPLETED;
    }

    // Có thể chuyển trạng thái tiếp không? (Admin)
    public function canMoveNext()
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING
        ]);
    }

    // Tổng số lượng sản phẩm
    public function getTotalQuantityAttribute()
    {
        return $this->items->sum('quantity');
    }

    // Hiển thị người huỷ
    public function getCancelledByNameAttribute()
    {
        if (!$this->cancelled_by_user_id) {
            return null;
        }

        return $this->cancelledByUser->name ?? null;
    }

    /*
    |--------------------------------
    | SCOPES
    |--------------------------------
    */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_PAID);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_UNPAID);
    }
    protected static function booted()
    {


        /*
    |--------------------------------------------------------------------------
    | 2. KHI UPDATE ĐƠN
    |--------------------------------------------------------------------------
    */
        static::updating(function ($order) {

            /*
        |--------------------------------------------------------------------------
        | 2.1 GIAO THÀNH CÔNG → GỬI MAIL HOÀN TẤT
        |--------------------------------------------------------------------------
        */
            if (
                $order->isDirty('status') &&
                $order->status == self::STATUS_COMPLETED
            ) {

                $order->loadMissing('user');

                if ($order->user && $order->user->email) {
                    Mail::to($order->user->email)
                        ->send(new OrderCompletedMail($order));
                }
            }


            /*
        |--------------------------------------------------------------------------
        | 2.2 HUỶ ĐƠN → HOÀN KHO + HOÀN TIỀN
        |--------------------------------------------------------------------------
        */
            if (
                $order->isDirty('status') &&
                $order->status == self::STATUS_CANCELLED
            ) {

                $order->loadMissing('items.variant', 'user');

                // =========================
                // Hoàn tồn kho
                // =========================
                foreach ($order->items as $item) {
                    if ($item->variant) {
                        $item->variant->increment(
                            'stock_quantity',
                            $item->quantity
                        );
                    }
                }

                // =========================
                // Trả lại voucher
                // =========================
                $order->refundVoucher();

                // =========================
                // Trả lại birthday discount
                // =========================
                if ($order->birthday_discount > 0 && $order->user
                ) {

                    $order->user->birthday_discount_year = null;
                    $order->user->save();
                }

                // =========================
                // Hoàn tiền VNPay
                // =========================
                if (
                    $order->payment_method === 'vnpay' &&
                    $order->payment_status == self::PAYMENT_PAID
                ) {
                    $order->payment_status = self::PAYMENT_REFUNDED;
                }
            }


            /*
        |--------------------------------------------------------------------------
        | 2.3 COD → Khi giao xong = đã thanh toán
        |--------------------------------------------------------------------------
        */
            if (
                $order->payment_method === 'cod' &&
                $order->isDirty('status') &&
                $order->status == self::STATUS_COMPLETED
            ) {
                $order->payment_status = self::PAYMENT_PAID;
            }
        });
    }
    // Nếu đơn cũ chưa tách, coi toàn bộ discount là voucher
    public function getVoucherDiscountAttribute($value)
    {
        if ($value > 0) {
            return $value;
        }

        // đơn cũ
        if ($this->discount > 0 && $this->birthday_discount == 0) {
            return $this->discount;
        }

        return 0;
    }

    public function getBirthdayDiscountAttribute($value)
    {
        return $value ?? 0;
    }
    public function refundRequest()
    {
        return $this->hasOne(RefundRequest::class);
    }
    public function refundVoucher()
    {
        if ($this->promotion_code) {
            Promotion::where('code', $this->promotion_code)
                ->where('used_count', '>', 0)
                ->decrement('used_count');
        }
    }
}