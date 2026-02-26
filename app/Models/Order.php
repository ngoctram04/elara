<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'user_id',

        // Tiền
        'subtotal',
        'discount',
        'shipping_fee',   // ⭐ thêm
        'total',           // doanh thu (không gồm ship)
        'grand_total',     // ⭐ thêm (khách trả)

        'promotion_code',

        // Trạng thái
        'status',

        // Người nhận
        'receiver_name',
        'receiver_phone',
        'receiver_address',
        'note',

        // Thanh toán
        'payment_method',
        'payment_status',
        'transaction_code',

        // Huỷ đơn
        'cancel_reason',
        'cancelled_by',            // admin | customer
        'cancelled_by_user_id',

        // Thời gian giao
        'delivered_at',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'discount' => 'integer',
        'shipping_fee' => 'integer',   // thêm
        'total' => 'integer',
        'grand_total' => 'integer',    // thêm
        'status' => 'integer',
        'payment_status' => 'integer',
        'delivered_at' => 'datetime',
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
        static::updating(function ($order) {

            // Kiểm tra status có thay đổi sang CANCELLED không
            if (
                $order->isDirty('status') &&
                $order->status == self::STATUS_CANCELLED
            ) {

                // Load items nếu chưa load
                $order->loadMissing('items.variant');

                /*
            |--------------------------------
            | 1. HOÀN KHO
            |--------------------------------
            */
                foreach ($order->items as $item) {
                    if ($item->variant) {
                        $item->variant->increment('stock_quantity', $item->quantity);
                    }
                }

                /*
            |--------------------------------
            | 2. VNPAY → HOÀN TIỀN
            |--------------------------------
            */
                if (
                    $order->payment_method === 'vnpay' &&
                    $order->payment_status == self::PAYMENT_PAID
                ) {
                    $order->payment_status = self::PAYMENT_REFUNDED;
                }
            }

            /*
        |--------------------------------
        | 3. COD → Khi giao xong = đã thanh toán
        |--------------------------------
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
}